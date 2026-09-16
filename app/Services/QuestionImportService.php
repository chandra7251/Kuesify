<?php

namespace App\Services;

use App\Http\Requests\StoreQuestionRequest;
use App\Models\Question;
use App\Models\Tag;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use RuntimeException;
use ZipArchive;

class QuestionImportService
{
    public function preview(UploadedFile $file, array $mapping = []): array
    {
        $rows = $this->rows($file, $mapping);
        $valid = [];
        $errors = [];
        foreach ($rows as $index => $row) {
            $validator = Validator::make($row, (new StoreQuestionRequest)->rules());
            if ($validator->fails()) {
                $errors[] = ['row' => $index + 2, 'errors' => $validator->errors()->toArray()];
            } else {
                $valid[] = $validator->validated();
            }
        }

        return ['rows' => $valid, 'errors' => $errors];
    }

    public function import(UploadedFile $file, User $creator, int $organizationId, array $mapping = []): int
    {
        $preview = $this->preview($file, $mapping);
        if ($preview['errors']) {
            throw new RuntimeException(json_encode(['invalid_rows' => count($preview['errors']), 'errors' => $preview['errors']]));
        }

        DB::transaction(function () use ($preview, $creator, $organizationId): void {
            foreach ($preview['rows'] as $data) {
                $question = Question::create([
                    'organization_id' => $organizationId, 'creator_id' => $creator->id, 'type' => $data['type'], 'prompt' => $data['prompt'],
                    'options' => $data['options'] ?? null, 'correct_answer' => $data['correct_answer'] ?? null, 'points' => $data['points'] ?? 1000,
                ]);
                $tagIds = collect($data['tags'] ?? [])->map(fn (string $name) => Tag::firstOrCreate(['organization_id' => $organizationId, 'name' => trim($name)])->id);
                $question->tags()->sync($tagIds);
            }
        });

        return count($preview['rows']);
    }

    private function rows(UploadedFile $file, array $mapping): array
    {
        $rows = strtolower($file->getClientOriginalExtension()) === 'xlsx' ? $this->xlsxRows($file->getPathname()) : $this->csvRows($file->getPathname());
        if (! $rows) {
            return [];
        }
        $headers = array_map(fn ($value) => trim((string) $value), array_shift($rows));
        return array_values(array_filter(array_map(function (array $values) use ($headers, $mapping): array {
            $source = array_combine($headers, array_pad($values, count($headers), '')) ?: [];
            $row = [];
            foreach ($source as $header => $value) {
                $target = $mapping[$header] ?? $header;
                $row[$target] = trim((string) $value);
            }
            $row['options'] = isset($row['options']) && $row['options'] !== '' ? array_values(array_filter(array_map('trim', explode('|', $row['options'])))) : null;
            $row['tags'] = isset($row['tags']) && $row['tags'] !== '' ? array_values(array_filter(array_map('trim', explode('|', $row['tags'])))) : [];
            $row['points'] = $row['points'] ?? 1000;
            return $row;
        }, $rows), fn (array $row) => implode('', array_map(fn ($value) => is_array($value) ? implode('', $value) : $value, $row)) !== ''));
    }

    private function csvRows(string $path): array
    {
        $handle = fopen($path, 'rb');
        $rows = [];
        while (($row = fgetcsv($handle)) !== false) {
            $rows[] = $row;
        }
        fclose($handle);
        return $rows;
    }

    private function xlsxRows(string $path): array
    {
        $zip = new ZipArchive;
        if ($zip->open($path) !== true) {
            throw new RuntimeException('Invalid XLSX archive.');
        }
        $shared = simplexml_load_string($zip->getFromName('xl/sharedStrings.xml') ?: '<sst/>');
        $strings = array_map(fn ($item) => (string) $item->t, iterator_to_array($shared->si ?? []));
        $sheet = simplexml_load_string($zip->getFromName('xl/worksheets/sheet1.xml') ?: '<worksheet/>');
        $rows = [];
        foreach ($sheet->sheetData->row ?? [] as $xmlRow) {
            $row = [];
            foreach ($xmlRow->c as $cell) {
                $value = (string) $cell->v;
                $row[] = (string) (($cell['t'] ?? null) === 's' ? ($strings[(int) $value] ?? '') : $value);
            }
            $rows[] = $row;
        }
        $zip->close();
        return $rows;
    }
}
