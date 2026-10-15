<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\PlatformActivityLog;
use App\Models\PlatformCrashLog;

class CleanupPlatformLogs extends Command
{
    protected $signature = 'platform:cleanup-logs {--days=7 : Delete logs older than N days}';

    protected $description = 'Delete old platform activity and crash logs to free disk/RAM';

    public function handle(): int
    {
        $days = (int) $this->option('days');

        $cutoff = now()->subDays($days);

        $activityDeleted = PlatformActivityLog::where('created_at', '<', $cutoff)->delete();
        $crashDeleted = PlatformCrashLog::where('created_at', '<', $cutoff)->delete();

        $this->info("Deleted {$activityDeleted} activity logs and {$crashDeleted} crash logs (older than {$days} days).");

        return self::SUCCESS;
    }
}
