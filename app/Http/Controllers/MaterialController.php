<?php

namespace App\Http\Controllers;

use App\Jobs\ExtractMaterial;
use App\Models\Material;
use App\Models\Quiz;
use App\Support\TenantContext;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class MaterialController extends Controller
{
    public function store(Request $request): RedirectResponse
    {
        Gate::authorize('create', Quiz::class);
        $data = $request->validate(['file' => ['required', 'file', 'mimes:pdf,ppt,pptx', 'max:25600']]);
        $file = $data['file'];
        if (! $this->hasExpectedSignature($file)) {
            return back()->withErrors(['file' => 'File signature does not match the selected format.']);
        }
        $path = $file->store('materials/'.app(TenantContext::class)->id(), 'local');
        $material = Material::create([
            'organization_id' => app(TenantContext::class)->id(),
            'creator_id' => $request->user()->id,
            'disk' => 'local',
            'path' => $path,
            'original_name' => $file->getClientOriginalName(),
            'mime_type' => $file->getMimeType(),
            'size' => $file->getSize(),
            'status' => 'uploaded',
        ]);

        ExtractMaterial::dispatch($material);

        return back();
    }

    public function download(Request $request, int $material): StreamedResponse
    {
        Gate::authorize('create', Quiz::class);
        $material = Material::findOrFail($material);

        return Storage::disk($material->disk)->download($material->path, $material->original_name);
    }

    private function hasExpectedSignature(\Illuminate\Http\UploadedFile $file): bool
    {
        $extension = strtolower($file->getClientOriginalExtension());
        $path = $file->getRealPath();
        $header = (string) file_get_contents($path, false, null, 0, 8);
        if ($extension === 'pdf') {
            return str_starts_with($header, '%PDF-');
        }
        if ($extension === 'ppt') {
            return $header === hex2bin('D0CF11E0A1B11AE1');
        }
        if ($extension === 'pptx') {
            $archive = new \ZipArchive;
            $valid = $archive->open($path) === true && $archive->locateName('[Content_Types].xml') !== false && $archive->locateName('ppt/presentation.xml') !== false;
            $archive->close();

            return $valid;
        }

        return false;
    }
}
