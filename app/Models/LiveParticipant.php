<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['alias', 'reconnect_token', 'score', 'kicked_at'])]
class LiveParticipant extends Model
{
    protected function casts(): array
    {
        return ['kicked_at' => 'datetime'];
    }
    public function session(): BelongsTo
    {
        return $this->belongsTo(LiveSession::class, 'live_session_id');
    }

    public function answers(): HasMany
    {
        return $this->hasMany(LiveAnswer::class);
    }
}
