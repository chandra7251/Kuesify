<?php

declare(strict_types=1);

use App\Services\ReverbHealthService;

it('reports online or offline with proper payload keys', function () {
    $service = new ReverbHealthService();
    $health = $service->check();

    expect($health)->toHaveKeys(['reverb_status', 'reverb_host', 'reverb_latency_ms']);
    expect(in_array($health['reverb_status'], ['online', 'offline'], true))->toBeTrue();
});

it('reports offline when reverb host is unreachable', function () {
    config()->set('reverb.servers.reverb.hostname', '127.0.0.1');
    config()->set('reverb.servers.reverb.port', 59999);

    $service = new ReverbHealthService();
    $health = $service->check();

    expect($health['reverb_status'])->toBe('offline');
    expect($health['reverb_latency_ms'])->toBeNull();
});
