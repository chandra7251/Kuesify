<?php

declare(strict_types=1);

namespace App\Models;

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['organization_id', 'creator_id', 'disk', 'path', 'original_name', 'mime_type', 'size', 'page_count', 'status', 'visibility', 'version', 'extracted_text', 'failure_reason'])]
class Material extends Model
{
    use BelongsToTenant;

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'creator_id');
    }

    public function progresses(): HasMany
    {
        return $this->hasMany(MaterialProgress::class);
    }

    public function notes(): HasMany
    {
        return $this->hasMany(MaterialNote::class);
    }

    public function checks(): HasMany
    {
        return $this->hasMany(MaterialCheck::class);
    }
}
