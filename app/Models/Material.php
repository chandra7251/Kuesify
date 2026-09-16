<?php

declare(strict_types=1);

namespace App\Models;

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['organization_id', 'creator_id', 'disk', 'path', 'original_name', 'mime_type', 'size', 'page_count', 'status', 'extracted_text', 'failure_reason'])]
class Material extends Model
{
    use BelongsToTenant;

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'creator_id');
    }
}
