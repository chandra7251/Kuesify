<?php

namespace App\Http\Controllers;

use App\Models\LiveSession;
use App\Models\LiveParticipant;
use App\Models\Question;
use App\Models\Quiz;
use App\Events\LiveSessionStateChanged;
use App\Services\LiveSessionPresenter;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;
use Inertia\Response;

class LiveSessionController extends Controller
{
    public function store(Request $request): RedirectResponse
    {
        Gate::authorize('create', Quiz::class);
        $data = $request->validate([
            'quiz_id' => ['required', 'integer'],
            'question_duration' => ['nullable', 'integer', 'min:5', 'max:300'],
            'speed_multiplier' => ['nullable', 'integer', 'min:0', 'max:1000'],
        ]);
        $quiz = Quiz::findOrFail($data['quiz_id']);

        $session = LiveSession::open($quiz, $request->user(), $data['question_duration'] ?? 30, $data['speed_multiplier'] ?? 10);
        LiveSessionStateChanged::dispatch($session);

        return redirect()->route('live-sessions.play', $session);
    }

    public function join(Request $request): RedirectResponse
    {
        $data = $request->validate(['pin' => ['required', 'digits:6'], 'alias' => ['required', 'string', 'max:40']]);
        $session = LiveSession::withoutGlobalScopes()->where('pin', $data['pin'])->whereIn('status', ['lobby', 'live'])->firstOrFail();
        $participant = $session->joinGuest($data['alias']);

        $request->session()->put('live_participant', ['session_id' => $session->id, 'participant_id' => $participant->id, 'token' => $participant->reconnect_token]);

        return redirect()->route('live-sessions.play', $session);
    }

    public function start(Request $request, int $session): RedirectResponse
    {
        $session = LiveSession::findOrFail($session);
        Gate::authorize('update', $session->quiz);
        $session->start();
        LiveSessionStateChanged::dispatch($session);

        return back();
    }

    public function lock(Request $request, int $session): RedirectResponse
    {
        $session = LiveSession::findOrFail($session);
        Gate::authorize('update', $session->quiz);
        $session->lockLobby();
        LiveSessionStateChanged::dispatch($session);

        return back();
    }

    public function next(Request $request, int $session): RedirectResponse
    {
        $session = LiveSession::findOrFail($session);
        Gate::authorize('update', $session->quiz);
        $session->nextQuestion();
        LiveSessionStateChanged::dispatch($session);

        return back();
    }

    public function end(Request $request, int $session): RedirectResponse
    {
        $session = LiveSession::findOrFail($session);
        Gate::authorize('update', $session->quiz);
        $session->end();
        LiveSessionStateChanged::dispatch($session);

        return back();
    }

    public function kick(Request $request, int $session, int $participant): RedirectResponse
    {
        $session = LiveSession::findOrFail($session);
        Gate::authorize('update', $session->quiz);
        $session->kick($session->participants()->findOrFail($participant));
        LiveSessionStateChanged::dispatch($session);

        return redirect()->route('live-sessions.play', $session);
    }

    public function reconnect(Request $request, int $session): RedirectResponse
    {
        $data = $request->validate(['token' => ['required', 'uuid']]);
        $session = LiveSession::withoutGlobalScopes()->findOrFail($session);
        $participant = $session->participants()->where('reconnect_token', $data['token'])->firstOrFail();
        $request->session()->put('live_participant', ['session_id' => $session->id, 'participant_id' => $participant->id, 'token' => $participant->reconnect_token]);

        return back();
    }

    public function answer(Request $request, int $session): RedirectResponse
    {
        $session = LiveSession::withoutGlobalScopes()->findOrFail($session);
        $identity = $request->session()->get('live_participant');
        abort_unless($identity && $identity['session_id'] === $session->id, 403);
        $participant = LiveParticipant::whereKey($identity['participant_id'] ?? 0)->where('live_session_id', $session->id)->firstOrFail();
        abort_unless(hash_equals($participant->reconnect_token, $identity['token']) && ! $participant->kicked_at, 403);
        $data = $request->validate(['answer' => ['required', 'string', 'max:4000']]);
        $question = Question::withoutGlobalScopes()->findOrFail($session->current_question_id);
        abort_unless($question->organization_id === $session->organization_id, 404);
        $session->submit($participant, $question, $data['answer']);
        LiveSessionStateChanged::dispatch($session);

        return back();
    }

    public function joinPage(): Response
    {
        return Inertia::render('LiveJoin');
    }

    public function play(Request $request, int $session, LiveSessionPresenter $presenter): Response
    {
        $session = LiveSession::withoutGlobalScopes()->findOrFail($session);
        $identity = $request->session()->get('live_participant');
        $isGuest = $identity && $identity['session_id'] === $session->id && $session->participants()->whereKey($identity['participant_id'])->where('reconnect_token', $identity['token'])->exists();
        $isHost = $request->user()?->id === $session->host_id;
        abort_unless($isGuest || $isHost, 403);

        return Inertia::render('LivePlay', ['session' => $presenter->present($session), 'isHost' => $isHost]);
    }

    public function reconnectUrl(Request $request, int $session): \Illuminate\Http\JsonResponse
    {
        $session = LiveSession::withoutGlobalScopes()->findOrFail($session);
        $identity = $request->session()->get('live_participant');
        abort_unless($identity && $identity['session_id'] === $session->id, 403);
        $url = url()->signedRoute('live-sessions.reconnect', ['session' => $session->id])
            . '?token=' . urlencode($identity['token']);

        return response()->json(['url' => $url]);
    }
}
