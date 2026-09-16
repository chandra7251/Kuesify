<?php

namespace App\Events;

use App\Models\LiveSession;
use App\Services\LiveSessionPresenter;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class LiveSessionStateChanged implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(public LiveSession $session)
    {
    }

    public function broadcastOn(): array
    {
        return [new Channel('live-session.'.$this->session->broadcast_token)];
    }

    public function broadcastAs(): string
    {
        return 'live.session.updated';
    }

    public function broadcastWith(): array
    {
        return app(LiveSessionPresenter::class)->present($this->session);
    }
}
