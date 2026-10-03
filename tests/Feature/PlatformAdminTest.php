<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Organization;
use App\Models\Quiz;
use App\Models\QuizAttempt;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PlatformAdminTest extends TestCase
{
    use RefreshDatabase;

    private function createSuperAdmin(): User
    {
        $user = User::factory()->create();
        $org = Organization::factory()->create();
        $user->organizations()->attach($org->id, ['role' => 'super_admin']);

        return $user;
    }

    public function test_super_admin_can_access_global_reports(): void
    {
        $admin = $this->createSuperAdmin();
        $org = Organization::first();
        $quiz = Quiz::create([
            'organization_id' => $org->id,
            'creator_id' => $admin->id,
            'title' => 'Kuis Global Test',
            'status' => 'published',
        ]);
        QuizAttempt::create([
            'organization_id' => $org->id,
            'quiz_id' => $quiz->id,
            'participant_id' => $admin->id,
            'status' => 'completed',
            'score' => 85,
        ]);

        $response = $this->actingAs($admin)
            ->withSession(['active_role' => 'super_admin'])
            ->get(route('reports.index'));

        $response->assertOk();
        $response->assertInertia(fn ($page) => $page
            ->component('Workspace')
            ->where('title', 'Laporan')
        );
    }

    public function test_super_admin_can_view_platform_sections(): void
    {
        $admin = $this->createSuperAdmin();

        $response = $this->actingAs($admin)
            ->withSession(['active_role' => 'super_admin'])
            ->get(route('superadmin.section', 'categories'));

        $response->assertOk();
    }
}
