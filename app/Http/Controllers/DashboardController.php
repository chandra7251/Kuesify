<?php

namespace App\Http\Controllers;

use App\Models\LiveSession;
use App\Models\Organization;
use App\Models\Question;
use App\Models\Quiz;
use App\Models\QuizAttempt;
use App\Models\UserProgress;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    public function __invoke(Request $request): Response
    {
        $organization = Organization::findOrFail($request->session()->get('organization_id'));
        $progress = UserProgress::where('user_id', $request->user()->id)->first();
        $badges = DB::table('badge_awards')
            ->join('badges', 'badge_awards.badge_id', '=', 'badges.id')
            ->where('badge_awards.organization_id', $organization->id)
            ->where('badge_awards.user_id', $request->user()->id)
            ->select('badges.key', 'badges.name')
            ->get()->toArray();

        return Inertia::render('Dashboard', [
            'organization' => ['id' => $organization->id, 'name' => $organization->name, 'role' => $organization->roleFor($request->user())],
            'stats' => [
                'quizzes' => Quiz::count(), 'questions' => Question::count(), 'liveSessions' => LiveSession::whereIn('status', ['lobby', 'live'])->count(),
                'attempts' => QuizAttempt::where('participant_id', $request->user()->id)->count(), 'xp' => $progress?->xp ?? 0, 'streak' => $progress?->streak ?? 0,
                'level' => $progress?->level ?? 1,
            ],
            'recentQuizzes' => Quiz::latest()->take(8)->get(['id', 'title', 'status', 'updated_at']),
            'badges' => $badges,
        ]);
    }
}
