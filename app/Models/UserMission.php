<?php

namespace App\Models;

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['organization_id', 'user_id', 'mission_id', 'period_key', 'progress', 'completed_at'])]
class UserMission extends Model
{
    use BelongsToTenant;

    protected function casts(): array
    {
        return ['completed_at' => 'datetime'];
    }

    public function mission(): BelongsTo
    {
        return $this->belongsTo(Mission::class);
    }
}