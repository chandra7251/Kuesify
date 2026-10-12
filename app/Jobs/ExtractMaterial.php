<?php

namespace App\Jobs;

use App\Models\Material;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Storage;
use ZipArchive;

class ExtractMaterial implements ShouldQueue
{
    use Queueable;

    public int $tries = 3;
    public array $backoff = [10, 60, 300];

    public function __construct(public Material $material)
    {
    }

    public function handle(): void
    {
        $organization = \App\Models\Organization::find($this->material->organization_id);
        if ($organization) {
            app(\App\Support\TenantContext::class)->set($organization);
        }

        try {
            $this->material->update(['status' => 'extracting']);
            $path = Storage::disk($this->material->disk)->path($this->material->path);
            $pages = $this->material->mime_type === 'application/pdf'
                ? $this->pdfPageCount($path)
                : $this->powerPointSlideCount($path);

            if ($pages > 50) {
                $this->material->update(['status' => 'failed', 'page_count' => $pages, 'failure_reason' => 'Material exceeds 50 pages or slides.']);

                return;
            }

            $extractedText = $this->material->mime_type === 'application/pdf'
                ? $this->extractPdfText($path)
                : $this->extractPowerPointText($path);

            if ($extractedText === '') {
                $extractedText = pathinfo($this->material->original_name, PATHINFO_FILENAME);
            }

            $this->material->update([
                'status' => 'extracted',
                'page_count' => $pages,
                'extracted_text' => $extractedText,
            ]);
        } finally {
            app(\App\Support\TenantContext::class)->clear();
        }
    }

    private function pdfPageCount(string $path): int
    {
        $content = (string) file_get_contents($path);

        $count = preg_match_all('/\/Type\s*\/Page\b/', $content);
        if ($count > 0) {
            return $count;
        }

        if (preg_match('/\/Type\s*\/Pages\b.*?\/Count\s+(\d+)/s', $content, $match)) {
            return (int) $match[1];
        }

        if (preg_match_all('/stream[\r\n]+(.*?)[\r\n]+endstream/s', $content, $streamMatches)) {
            foreach ($streamMatches[1] as $stream) {
                $decompressed = @gzuncompress($stream);
                if (! $decompressed) {
                    continue;
                }

                if (preg_match('/\/Type\s*\/Pages\b.*?\/Count\s+(\d+)/s', $decompressed, $match)) {
                    return (int) $match[1];
                }

                $pageMatches = preg_match_all('/\/Type\s*\/Page\b/', $decompressed);
                if ($pageMatches > 0) {
                    return $pageMatches;
                }
            }
        }

        return max(1, $count);
    }

    private function powerPointSlideCount(string $path): int
    {
        $archive = new ZipArchive;

        if ($archive->open($path) !== true) {
            return 0;
        }

        $count = 0;
        for ($index = 0; $index < $archive->numFiles; $index++) {
            $name = $archive->getNameIndex($index);
            $count += (int) preg_match('#^ppt/slides/slide\d+\.xml$#', $name);
        }
        $archive->close();

        return $count;
    }

    private function extractPowerPointText(string $path): string
    {
        $archive = new ZipArchive;

        if ($archive->open($path) !== true) {
            return '';
        }

        $slideTexts = [];
        for ($index = 0; $index < $archive->numFiles; $index++) {
            $name = $archive->getNameIndex($index);
            if (preg_match('#^ppt/slides/slide(\d+)\.xml$#', $name, $matches)) {
                $slideNumber = (int) $matches[1];
                $xml = (string) $archive->getFromIndex($index);
                preg_match_all('/<a:t[^>]*>(.*?)<\/a:t>/s', $xml, $textMatches);
                $cleanTexts = array_map(fn ($t) => html_entity_decode(strip_tags((string) $t), ENT_QUOTES | ENT_XML1, 'UTF-8'), $textMatches[1] ?? []);
                $slideTexts[$slideNumber] = trim(implode(' ', array_filter($cleanTexts, fn ($t) => trim((string) $t) !== '')));
            }
        }
        $archive->close();

        ksort($slideTexts);

        return $this->cleanUtf8(implode("\n\n", array_filter($slideTexts, fn ($t) => $t !== '')));
    }

    private function extractPdfText(string $path): string
    {
        $content = (string) file_get_contents($path);
        $extracted = '';

        if (preg_match_all('/stream[\r\n]+(.*?)[\r\n]+endstream/s', $content, $streamMatches)) {
            foreach ($streamMatches[1] as $stream) {
                $data = @gzuncompress($stream);
                if ($data === false) {
                    continue;
                }

                if (preg_match_all('/(?:\b|^)BT[\s\r\n]+(.*?)[\s\r\n]+ET(?:\b|$)/s', $data, $btMatches)) {
                    foreach ($btMatches[1] as $btBlock) {
                        if (preg_match_all('/\((.*?)\)\s*T[j*]/s', $btBlock, $textMatches)) {
                            $extracted .= ' '.implode(' ', $textMatches[1]);
                        } elseif (preg_match_all('/\[(.*?)\]\s*TJ/s', $btBlock, $arrayMatches)) {
                            foreach ($arrayMatches[1] as $arrayContent) {
                                if (preg_match_all('/\((.*?)\)/s', $arrayContent, $innerTexts)) {
                                    $extracted .= ' '.implode(' ', $innerTexts[1]);
                                }
                            }
                        }
                    }
                }
            }
        }

        return $this->cleanUtf8($extracted);
    }

    private function cleanUtf8(string $text): string
    {
        if ($text === '') {
            return '';
        }

        $text = str_replace("\0", '', $text);
        $text = mb_convert_encoding($text, 'UTF-8', 'UTF-8');
        if (function_exists('iconv')) {
            $text = @iconv('UTF-8', 'UTF-8//IGNORE', $text) ?: $text;
        }

        $text = (string) preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]/', ' ', $text);
        $text = (string) preg_replace('/[^\P{C}\t\n\r]/u', '', $text);

        return trim((string) preg_replace('/\s+/', ' ', $text));
    }
}
