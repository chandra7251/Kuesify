<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\OrganizationController;
use App\Http\Controllers\QuizController;
use App\Http\Controllers\QuestionController;
use App\Http\Controllers\LiveSessionController;
use App\Http\Controllers\MaterialController;
use App\Http\Controllers\AiGenerationController;
use App\Http\Controllers\AiQuestionDraftController;
use App\Http\Controllers\QuizAttemptController;
use App\Http\Controllers\OrganizationMemberController;
use App\Http\Controllers\GroupController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ProfileAvatarController;
use App\Http\Controllers\WorkspaceController;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    return Inertia::render('Welcome', [
        'canLogin' => Route::has('login'),
        'canRegister' => Route::has('register'),
        'laravelVersion' => Application::VERSION,
        'phpVersion' => PHP_VERSION,
    ]);
});

Route::get('/dashboard', DashboardController::class)->middleware(['auth', 'verified', 'organization.context'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/choose-avatar', [ProfileAvatarController::class, 'create'])->name('avatars.create');
    Route::post('/choose-avatar', [ProfileAvatarController::class, 'store'])->name('avatars.store');
    Route::post('/organizations/{organization}/switch', [OrganizationController::class, 'switch'])->name('organizations.switch');
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

Route::post('/quizzes', [QuizController::class, 'store'])
    ->middleware(['auth', 'organization.context'])
    ->name('quizzes.store');
Route::patch('/quizzes/{quiz}', [QuizController::class, 'update'])
    ->middleware(['auth', 'organization.context'])
    ->name('quizzes.update');
Route::put('/quizzes/{quiz}/questions', [QuizController::class, 'syncQuestions'])
    ->middleware(['auth', 'organization.context'])
    ->name('quizzes.questions.sync');
Route::post('/quizzes/{quiz}/publish', [QuizController::class, 'publish'])
    ->middleware(['auth', 'organization.context'])
    ->name('quizzes.publish');
Route::post('/quizzes/{quiz}/clone', [QuizController::class, 'clone'])
    ->middleware(['auth', 'organization.context'])
    ->name('quizzes.clone');
Route::post('/quizzes/{quiz}/archive', [QuizController::class, 'archive'])
    ->middleware(['auth', 'organization.context'])
    ->name('quizzes.archive');
Route::post('/quizzes/{quiz}/cover', [QuizController::class, 'cover'])
    ->middleware(['auth', 'organization.context', 'throttle:10,1'])
    ->name('quizzes.cover');

Route::middleware(['auth', 'organization.context'])->group(function () {
    Route::get('/questions', [WorkspaceController::class, 'questions'])->name('questions.index');
    Route::get('/questions/export', [WorkspaceController::class, 'exportQuestions'])->name('questions.export');
    Route::get('/quizzes', [WorkspaceController::class, 'quizzes'])->name('quizzes.index');
    Route::get('/live-sessions', [WorkspaceController::class, 'live'])->name('live-sessions.index');
    Route::get('/attempts', [WorkspaceController::class, 'attempts'])->name('attempts.index');
    Route::get('/attempts/export', [WorkspaceController::class, 'exportAttempts'])->name('attempts.export');
    Route::get('/attempts/{attempt}/play', [QuizAttemptController::class, 'play'])->name('attempts.play');
    Route::get('/organization', [WorkspaceController::class, 'organization'])->name('organization.index');
    Route::get('/reports', [WorkspaceController::class, 'reports'])->name('reports.index');
    Route::get('/admin', [WorkspaceController::class, 'admin'])->name('platform.admin');
    Route::get('/materials', [WorkspaceController::class, 'materials'])->name('materials.index');
    Route::get('/reports/export', [WorkspaceController::class, 'exportReport'])->name('reports.export');
});

Route::post('/questions', [QuestionController::class, 'store'])
    ->middleware(['auth', 'organization.context'])
    ->name('questions.store');
Route::post('/questions/import/preview', [QuestionController::class, 'previewImport'])
    ->middleware(['auth', 'organization.context', 'throttle:10,1'])
    ->name('questions.import.preview');
Route::post('/questions/import', [QuestionController::class, 'import'])
    ->middleware(['auth', 'organization.context', 'throttle:10,1'])
    ->name('questions.import.store');
Route::patch('/questions/{question}', [QuestionController::class, 'update'])
    ->middleware(['auth', 'organization.context'])
    ->name('questions.update');
Route::delete('/questions/{question}', [QuestionController::class, 'destroy'])
    ->middleware(['auth', 'organization.context'])
    ->name('questions.destroy');

Route::post('/live-sessions', [LiveSessionController::class, 'store'])
    ->middleware(['auth', 'organization.context'])
    ->name('live-sessions.store');
Route::post('/materials', [MaterialController::class, 'store'])
    ->middleware(['auth', 'organization.context', 'throttle:10,1'])
    ->name('materials.store');
Route::get('/materials/{material}/download', [MaterialController::class, 'download'])
    ->middleware(['auth', 'organization.context'])
    ->name('materials.download');
Route::post('/materials/{material}/ai-generations', [AiGenerationController::class, 'store'])
    ->middleware(['auth', 'organization.context', 'throttle:10,1'])
    ->name('ai-generations.store');
Route::post('/ai-generations/{generation}/retry', [AiGenerationController::class, 'retry'])
    ->middleware(['auth', 'organization.context', 'throttle:10,1'])
    ->name('ai-generations.retry');
Route::post('/ai-drafts/{draft}/approve', [AiQuestionDraftController::class, 'approve'])
    ->middleware(['auth', 'organization.context'])
    ->name('ai-drafts.approve');
Route::patch('/ai-drafts/{draft}', [AiQuestionDraftController::class, 'update'])
    ->middleware(['auth', 'organization.context'])
    ->name('ai-drafts.update');
Route::post('/ai-drafts/{draft}/reject', [AiQuestionDraftController::class, 'reject'])
    ->middleware(['auth', 'organization.context'])
    ->name('ai-drafts.reject');
Route::post('/quizzes/{quiz}/attempts', [QuizAttemptController::class, 'store'])
    ->middleware(['auth', 'organization.context'])
    ->name('attempts.store');
Route::post('/organization/members', [OrganizationMemberController::class, 'store'])
    ->middleware(['auth', 'organization.context'])
    ->name('organization.members.store');
Route::patch('/organization/members/{user}/disable', [OrganizationMemberController::class, 'disable'])
    ->middleware(['auth', 'organization.context'])
    ->name('organization.members.disable');
Route::post('/organization/groups', [GroupController::class, 'store'])
    ->middleware(['auth', 'organization.context'])
    ->name('organization.groups.store');
Route::put('/attempts/{attempt}/questions/{question}', [QuizAttemptController::class, 'upsertAnswer'])
    ->middleware(['auth', 'organization.context'])
    ->name('attempts.answers.upsert');
Route::post('/attempts/{attempt}/submit', [QuizAttemptController::class, 'submit'])
    ->middleware(['auth', 'organization.context'])
    ->name('attempts.submit');
Route::post('/attempts/{attempt}/answers/{answer}/grade', [QuizAttemptController::class, 'grade'])
    ->middleware(['auth', 'organization.context'])
    ->name('attempts.answers.grade');
Route::post('/live-sessions/join', [LiveSessionController::class, 'join'])->name('live-sessions.join');
Route::get('/live-sessions/{session}/reconnect-qr', [LiveSessionController::class, 'reconnectUrl'])->name('live-sessions.reconnect-qr');
Route::get('/join', [LiveSessionController::class, 'joinPage'])->name('live-sessions.join-page');
Route::get('/live-sessions/{session}/play', [LiveSessionController::class, 'play'])->name('live-sessions.play');
Route::post('/live-sessions/{session}/start', [LiveSessionController::class, 'start'])
    ->middleware(['auth', 'organization.context'])
    ->name('live-sessions.start');
Route::post('/live-sessions/{session}/lock', [LiveSessionController::class, 'lock'])
    ->middleware(['auth', 'organization.context'])
    ->name('live-sessions.lock');
Route::post('/live-sessions/{session}/next', [LiveSessionController::class, 'next'])
    ->middleware(['auth', 'organization.context'])
    ->name('live-sessions.next');
Route::post('/live-sessions/{session}/end', [LiveSessionController::class, 'end'])
    ->middleware(['auth', 'organization.context'])
    ->name('live-sessions.end');
Route::post('/live-sessions/{session}/participants/{participant}/kick', [LiveSessionController::class, 'kick'])
    ->middleware(['auth', 'organization.context'])
    ->name('live-sessions.kick');
Route::post('/live-sessions/{session}/reconnect', [LiveSessionController::class, 'reconnect'])
    ->middleware('throttle:10,1')
    ->name('live-sessions.reconnect');
Route::post('/live-sessions/{session}/answers', [LiveSessionController::class, 'answer'])
    ->name('live-sessions.answers.store');

require __DIR__.'/auth.php';

