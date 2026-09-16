<?php

use App\Models\Organization;
use App\Models\User;
use Illuminate\Http\UploadedFile;

function creatorInOrganization(): array
{
    $creator = User::factory()->create();
    $organization = Organization::factory()->create();
    $organization->members()->attach($creator, ['role' => 'creator']);

    return [$creator, $organization];
}

it('validates multiple choice options before saving a question', function () {
    [$creator, $organization] = creatorInOrganization();
    $this->actingAs($creator)->post(route('organizations.switch', $organization));

    $this->post(route('questions.store'), [
        'type' => 'multiple_choice',
        'prompt' => 'Pilih',
        'options' => ['satu'],
        'correct_answer' => 'satu',
    ])->assertSessionHasErrors('options');
});

it('stores a validated reusable question in active organization', function () {
    [$creator, $organization] = creatorInOrganization();
    $this->actingAs($creator)->post(route('organizations.switch', $organization));

    $this->post(route('questions.store'), [
        'type' => 'multiple_choice',
        'prompt' => 'Ibu kota Indonesia?',
        'options' => ['Jakarta', 'Bandung'],
        'correct_answer' => 'Jakarta',
        'points' => 1000,
        'tags' => ['Geografi'],
    ])->assertRedirect()->assertSessionHasNoErrors();

    $this->assertDatabaseHas('questions', ['organization_id' => $organization->id, 'prompt' => 'Ibu kota Indonesia?']);
    $this->assertDatabaseHas('tags', ['organization_id' => $organization->id, 'name' => 'Geografi']);
});

it('previews mapped CSV rows and rolls back whole import when any row is invalid', function () {
    [$creator, $organization] = creatorInOrganization();
    $this->actingAs($creator)->post(route('organizations.switch', $organization));
    $valid = UploadedFile::fake()->createWithContent('questions.csv', "question,answer,type\nLangit biru?,true,true_false\n");

    $this->post(route('questions.import.preview'), ['file' => $valid, 'mapping' => ['question' => 'prompt', 'answer' => 'correct_answer', 'type' => 'type']], ['Accept' => 'application/json'])
        ->assertOk()->assertJsonPath('valid_rows', 1)->assertJsonPath('invalid_rows', 0);

    $invalid = UploadedFile::fake()->createWithContent('questions.csv', "prompt,type,correct_answer\nValid,true_false,true\nRusak,multiple_choice,jawaban\n");
    $this->post(route('questions.import.store'), ['file' => $invalid], ['Accept' => 'application/json'])
        ->assertStatus(422)->assertJsonPath('invalid_rows', 1);
    $this->assertDatabaseCount('questions', 0);
});

it('imports all valid CSV rows transactionally into active organization', function () {
    [$creator, $organization] = creatorInOrganization();
    $this->actingAs($creator)->post(route('organizations.switch', $organization));
    $file = UploadedFile::fake()->createWithContent('questions.csv', "prompt,type,options,correct_answer,points,tags\nIbu kota?,multiple_choice,Jakarta|Bandung,Jakarta,500,geografi|indonesia\n");

    $this->post(route('questions.import.store'), ['file' => $file], ['Accept' => 'application/json'])
        ->assertOk()->assertJsonPath('imported', 1);

    $this->assertDatabaseHas('questions', ['organization_id' => $organization->id, 'prompt' => 'Ibu kota?', 'points' => 500]);
    $this->assertDatabaseHas('tags', ['organization_id' => $organization->id, 'name' => 'geografi']);
});
