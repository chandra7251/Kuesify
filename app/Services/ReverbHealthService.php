<?php

declare(strict_types=1);

namespace App\Services;

class ReverbHealthService
{
    public function check(): array
    {
        $rawHost = config('reverb.servers.reverb.hostname') ?: config('reverb.servers.reverb.host', '127.0.0.1');
        $port = (int) config('reverb.servers.reverb.port', 8080);
        $timeout = 1.0;

        // Pada Windows, localhost dapat resolve ke ::1 sedangkan server listen di IPv4 127.0.0.1
        $candidates = ($rawHost === 'localhost' || $rawHost === '0.0.0.0')
            ? ['127.0.0.1', $rawHost]
            : [$rawHost, '127.0.0.1'];

        $status = 'offline';
        $latencyMs = null;
        $connectedHost = $rawHost;

        $start = hrtime(true);
        foreach ($candidates as $host) {
            $fp = @fsockopen($host, $port, $errno, $errstr, $timeout);
            if ($fp) {
                $latencyMs = round((hrtime(true) - $start) / 1e6, 1);
                fclose($fp);
                $status = 'online';
                $connectedHost = $host;
                break;
            }
        }

        return [
            'reverb_status' => $status,
            'reverb_host' => $rawHost.':'.$port,
            'reverb_latency_ms' => $latencyMs,
        ];
    }
}
