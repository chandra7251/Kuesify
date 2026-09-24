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
            ->where('title', 'Laporan Global')
            ->where('summary.isGlobal', true)
        );
    }

    public function test_super_admin_can_store_and_destroy_categories(): void
    {
        $admin = $this->createSuperAdmin();

        $storeResponse = $this->actingAs($admin)
            ->withSession(['active_role' => 'super_admin'])
            ->post(route('platform.admin.categories.store'), [
                'name' => 'Fisika Kuantum',
            ]);
        $storeResponse->assertRedirect();
        $this->assertDatabaseHas('categories', ['name' => 'Fisika Kuantum']);

        $category = Category::where('name', 'Fisika Kuantum')->firstOrFail();

        $destroyResponse = $this->actingAs($admin)
            ->withSession(['active_role' => 'super_admin'])
            ->delete(route('platform.admin.categories.destroy', $category));
        $destroyResponse->assertRedirect();
        $this->assertDatabaseMissing('categories', ['name' => 'Fisika Kuantum']);
    }

    public function test_super_admin_can_update_maintenance_settings(): void
    {
        $admin = $this->createSuperAdmin();

        $response = $this->actingAs($admin)
            ->withSession(['active_role' => 'super_admin'])
            ->post(route('platform.admin.settings.maintenance'), [
                'status' => 'Platform sedang peningkatan server',
                'jadwal_rutin' => 'Minggu pukul 03.00 WIB',
                'mode' => 'Maintenance Terjadwal',
            ]);

        $response->assertRedirect();
    }

    public function test_super_admin_can_update_ai_and_audio_settings(): void
    {
        $admin = $this->createSuperAdmin();

        $aiRes = $this->actingAs($admin)
            ->withSession(['active_role' => 'super_admin'])
            ->post(route('platform.admin.settings.ai'), [
                'weekly_creator_limit' => 25,
                'ai_model' => 'Gemini 1.5 Pro',
            ]);
        $aiRes->assertRedirect();

        $audioRes = $this->actingAs($admin)
            ->withSession(['active_role' => 'super_admin'])
            ->post(route('platform.admin.settings.audio'), [
                'bgm_lobby' => 'Synthwave Chill 90s',
                'sfx_correct' => 'Crystal Chime High',
                'sfx_wrong' => 'Muted Buzzer Low',
                'default_volume' => '85%',
            ]);
        $audioRes->assertRedirect();
    }
}
