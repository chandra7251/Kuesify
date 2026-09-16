<?php

use App\Models\Organization;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Storage;

it('rejects material uploads outside PDF and PowerPoint formats', function () {
    $creator = User::factory()->create();
    $organization = Organization::factory()->create();
    $organization->members()->attach($creator, ['role' => 'creator']);
    $this->actingAs($creator)->post(route('organizations.switch', $organization));

    $this->post(route('materials.store'), ['file' => UploadedFile::fake()->create('notes.txt', 10, 'text/plain')])
        ->assertSessionHasErrors('file');
});

it('stores valid material and queues AI extraction for creator review', function () {
    Bus::fake();
    Storage::fake('local');
    $creator = User::factory()->create();
    $organization = Organization::factory()->create();
    $organization->members()->attach($creator, ['role' => 'creator']);
    $this->actingAs($creator)->post(route('organizations.switch', $organization));

    $this->post(route('materials.store'), ['file' => UploadedFile::fake()->createWithContent('lesson.pdf', "%PDF-1.7\n")])
        ->assertRedirect()
        ->assertSessionHasNoErrors();

    $this->assertDatabaseHas('materials', ['organization_id' => $organization->id, 'original_name' => 'lesson.pdf', 'status' => 'uploaded']);
    Bus::assertDispatched(\App\Jobs\ExtractMaterial::class);
});

it('rejects extension spoofing and permits creator to download own tenant material', function () {
    Storage::fake('local');
    $creator = User::factory()->create();
    $organization = Organization::factory()->create();
    $organization->members()->attach($creator, ['role' => 'creator']);
    $this->actingAs($creator)->post(route('organizations.switch', $organization));

    $this->post(route('materials.store'), ['file' => UploadedFile::fake()->createWithContent('spoof.pdf', 'not a PDF')])->assertSessionHasErrors('file');
    Storage::disk('local')->put('materials/'.$organization->id.'/lesson.pdf', '%PDF-1.7');
    $material = \App\Models\Material::create(['organization_id' => $organization->id, 'creator_id' => $creator->id, 'disk' => 'local', 'path' => 'materials/'.$organization->id.'/lesson.pdf', 'original_name' => 'lesson.pdf', 'mime_type' => 'application/pdf', 'size' => 8, 'status' => 'extracted']);

    $this->get(route('materials.download', $material))->assertOk();
});
