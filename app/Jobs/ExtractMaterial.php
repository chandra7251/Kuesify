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
                ? preg_match_all('/\/Type\s*\/Page\b/', (string) file_get_contents($path))
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

        return trim(implode("\n\n", array_filter($slideTexts, fn ($t) => $t !== '')));
    }

    private function extractPdfText(string $path): string
    {
        $content = (string) file_get_contents($path);
        $extracted = '';

        if (preg_match_all('/stream[\r\n]+(.*?)[\r\n]+endstream/s', $content, $streamMatches)) {
            foreach ($streamMatches[1] as $stream) {
                $data = @gzuncompress($stream) ?: $stream;
                if (preg_match_all('/\((.*?)\)\s*T[j*]/s', $data, $textMatches)) {
                    $extracted .= ' '.implode(' ', $textMatches[1]);
                } elseif (preg_match_all('/\[(.*?)\]\s*TJ/s', $data, $arrayMatches)) {
                    foreach ($arrayMatches[1] as $arrayContent) {
                        if (preg_match_all('/\((.*?)\)/s', $arrayContent, $innerTexts)) {
                            $extracted .= ' '.implode(' ', $innerTexts[1]);
                        }
                    }
                }
            }
        }

        return trim((string) preg_replace('/\s+/', ' ', $extracted));
    }
}
