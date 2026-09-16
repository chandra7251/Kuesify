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
        $this->material->update(['status' => 'extracting']);
        $path = Storage::disk($this->material->disk)->path($this->material->path);
        $pages = $this->material->mime_type === 'application/pdf'
            ? preg_match_all('/\/Type\s*\/Page\b/', (string) file_get_contents($path))
            : $this->powerPointSlideCount($path);

        if ($pages > 50) {
            $this->material->update(['status' => 'failed', 'page_count' => $pages, 'failure_reason' => 'Material exceeds 50 pages or slides.']);

            return;
        }

        $this->material->update(['status' => 'extracted', 'page_count' => $pages, 'extracted_text' => '']);
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
}
