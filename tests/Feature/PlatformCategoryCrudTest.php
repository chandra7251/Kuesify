<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Organization;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PlatformCategoryCrudTest extends TestCase
{
    use RefreshDatabase;

    public function test_super_admin_can_create_update_and_delete_category(): void
    {
        $superAdmin = User::factory()->create();
        $org = Organization::factory()->create();
        $org->users()->attach($superAdmin->id, ['role' => 'super_admin', 'is_active' => true]);

        // Create
        $response = $this->actingAs($superAdmin)
            ->withSession(['active_organization_id' => $org->id, 'active_role' => 'super_admin'])
            ->post(route('superadmin.categories.store'), [
                'name' => 'Fisika Kuantum',
                'theme_key' => 'physics',
            ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('categories', [
            'name' => 'Fisika Kuantum',
            'theme_key' => 'physics',
        ]);

        $category = Category::where('name', 'Fisika Kuantum')->first();

        // Update
        $response = $this->actingAs($superAdmin)
            ->withSession(['active_organization_id' => $org->id, 'active_role' => 'super_admin'])
            ->put(route('superadmin.categories.update', $category->id), [
                'name' => 'Fisika Terapan',
                'theme_key' => 'applied_physics',
            ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('categories', [
            'id' => $category->id,
            'name' => 'Fisika Terapan',
            'theme_key' => 'applied_physics',
        ]);

        // Delete
        $response = $this->actingAs($superAdmin)
            ->withSession(['active_organization_id' => $org->id, 'active_role' => 'super_admin'])
            ->delete(route('superadmin.categories.destroy', $category->id));

        $response->assertRedirect();
        $this->assertDatabaseMissing('categories', [
            'id' => $category->id,
        ]);
    }
}
