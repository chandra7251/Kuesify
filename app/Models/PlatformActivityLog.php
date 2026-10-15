<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PlatformActivityLog extends Model
{
    protected $fillable = [
        'user_id',
        'request_id',
        'method',
        'url',
        'status_code',
        'duration_ms',
        'ip',
        'user_agent',
        'request_body',
    ];

    protected $casts = [
        'request_body' => 'array',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function isSuccess(): bool
    {
        return $this->status_code < 400;
    }

    public function isClientError(): bool
    {
        return $this->status_code >= 400 && $this->status_code < 500;
    }

    public function isServerError(): bool
    {
        return $this->status_code >= 500;
    }
}
