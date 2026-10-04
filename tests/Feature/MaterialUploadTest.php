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

it('stores public visibility when creator shares material with all participants', function () {
    Bus::fake();
    Storage::fake('local');
    $creator = User::factory()->create();
    $organization = Organization::factory()->create();
    $organization->members()->attach($creator, ['role' => 'creator']);
    $this->actingAs($creator)->post(route('organizations.switch', $organization));

    $this->post(route('materials.store'), [
        'file' => UploadedFile::fake()->createWithContent('public-lesson.pdf', "%PDF-1.7\n"),
        'visibility' => 'public',
    ])->assertRedirect()->assertSessionHasNoErrors();

    $this->assertDatabaseHas('materials', [
        'organization_id' => $organization->id,
        'original_name' => 'public-lesson.pdf',
        'visibility' => 'public',
    ]);
});

it('rejects extension spoofing and permits creator to download own tenant material', function () {
    Storage::fake('local');
    $creator = User::factory()->create();
    $organization = Organization::factory()->create();
    $organization->members()->attach($creator, ['role' => 'creator']);
    $this->actingAs($creator)->post(route('organizations.switch', $organization));

    $this->post(route('materials.store'), ['file' => UploadedFile::fake()->createWithContent('spoof.pdf', 'not a PDF')])
        ->assertSessionHasErrors(['file' => 'Isi file tidak sesuai dengan ekstensinya. Unggah PDF/PPT/PPTX asli, bukan file yang hanya diganti nama.']);
    Storage::disk('local')->put('materials/'.$organization->id.'/lesson.pdf', '%PDF-1.7');
    $material = \App\Models\Material::create(['organization_id' => $organization->id, 'creator_id' => $creator->id, 'disk' => 'local', 'path' => 'materials/'.$organization->id.'/lesson.pdf', 'original_name' => 'lesson.pdf', 'mime_type' => 'application/pdf', 'size' => 8, 'status' => 'extracted']);

    $this->get(route('materials.download', $material))->assertOk();
});

it('lets participants download visible materials and blocks other organization private materials', function () {
    Storage::fake('local');
    $organization = Organization::factory()->create();
    $otherOrganization = Organization::factory()->create();
    $participant = User::factory()->create();
    $creator = User::factory()->create();
    $otherCreator = User::factory()->create();
    $organization->members()->attach($participant, ['role' => 'participant']);
    $organization->members()->attach($creator, ['role' => 'creator']);
    $otherOrganization->members()->attach($otherCreator, ['role' => 'creator']);
    $this->actingAs($participant)->post(route('organizations.switch', $organization));

    Storage::disk('local')->put('materials/'.$organization->id.'/visible.pdf', '%PDF-1.7');
    Storage::disk('local')->put('materials/'.$otherOrganization->id.'/hidden.pdf', '%PDF-1.7');
    Storage::disk('local')->put('materials/'.$otherOrganization->id.'/public.pdf', '%PDF-1.7');

    $organizationMaterial = \App\Models\Material::create(['organization_id' => $organization->id, 'creator_id' => $creator->id, 'disk' => 'local', 'path' => 'materials/'.$organization->id.'/visible.pdf', 'original_name' => 'visible.pdf', 'mime_type' => 'application/pdf', 'size' => 8, 'status' => 'extracted', 'visibility' => 'organization']);
    $privateOtherMaterial = \App\Models\Material::create(['organization_id' => $otherOrganization->id, 'creator_id' => $otherCreator->id, 'disk' => 'local', 'path' => 'materials/'.$otherOrganization->id.'/hidden.pdf', 'original_name' => 'hidden.pdf', 'mime_type' => 'application/pdf', 'size' => 8, 'status' => 'extracted', 'visibility' => 'organization']);
    $publicOtherMaterial = \App\Models\Material::create(['organization_id' => $otherOrganization->id, 'creator_id' => $otherCreator->id, 'disk' => 'local', 'path' => 'materials/'.$otherOrganization->id.'/public.pdf', 'original_name' => 'public.pdf', 'mime_type' => 'application/pdf', 'size' => 8, 'status' => 'extracted', 'visibility' => 'public']);

    $this->get(route('materials.download', $organizationMaterial))->assertOk();
    $this->get(route('materials.download', $publicOtherMaterial))->assertOk();
    $this->get(route('materials.download', $privateOtherMaterial))->assertForbidden();
});

it('blocks a creator from downloading another organization material by id', function () {
    Storage::fake('local');
    $organization = Organization::factory()->create();
    $otherOrganization = Organization::factory()->create();
    $creator = User::factory()->create();
    $otherCreator = User::factory()->create();
    $organization->members()->attach($creator, ['role' => 'creator']);
    $otherOrganization->members()->attach($otherCreator, ['role' => 'creator']);
    $this->actingAs($creator)->post(route('organizations.switch', $organization));

    Storage::disk('local')->put('materials/'.$otherOrganization->id.'/other.pdf', '%PDF-1.7');
    $material = \App\Models\Material::create([
        'organization_id' => $otherOrganization->id,
        'creator_id' => $otherCreator->id,
        'disk' => 'local',
        'path' => 'materials/'.$otherOrganization->id.'/other.pdf',
        'original_name' => 'other.pdf',
        'mime_type' => 'application/pdf',
        'size' => 8,
        'status' => 'uploaded',
        'visibility' => 'organization',
    ]);

    $this->get(route('materials.download', $material))->assertForbidden();
});
