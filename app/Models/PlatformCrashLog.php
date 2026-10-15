<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PlatformCrashLog extends Model
{
    protected $fillable = [
        'user_id',
        'request_id',
        'method',
        'url',
        'status_code',
        'message',
        'trace',
        'request_body',
        'ip',
        'user_agent',
    ];

    protected $casts = [
        'request_body' => 'array',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function isServerError(): bool
    {
        return $this->status_code >= 500;
    }

    /**
     * Get trace trimmed to N lines max for display.
     */
    public function trimmedTrace(int $maxLines = 10): string
    {
        if (! $this->trace) {
            return '';
        }

        $lines = explode("\n", $this->trace);

        if (count($lines) <= $maxLines) {
            return $this->trace;
        }

        return implode("\n", array_slice($lines, 0, $maxLines))
            . "\n... (" . (count($lines) - $maxLines) . ' more lines truncated)';
    }
}
