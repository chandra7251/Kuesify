<?php

use App\Models\Organization;
use App\Models\Question;
use App\Models\Tag;
use App\Models\User;
use App\Support\TenantContext;

afterEach(fn () => app(TenantContext::class)->clear());

it('stores flexible tenant tags on reusable questions', function () {
    $organization = Organization::factory()->create();
    $creator = User::factory()->create();
    app(TenantContext::class)->set($organization);

    $tag = Tag::create(['organization_id' => $organization->id, 'name' => 'Matematika']);
    $question = Question::create([
        'organization_id' => $organization->id,
        'creator_id' => $creator->id,
        'type' => 'multiple_choice',
        'prompt' => '2 + 2 = ?',
        'options' => ['2', '3', '4', '5'],
        'correct_answer' => '4',
    ]);
    $question->tags()->attach($tag);

    expect($question->fresh()->tags->pluck('name')->all())->toBe(['Matematika']);
});

it('never exposes tags from another tenant', function () {
    $first = Organization::factory()->create();
    $second = Organization::factory()->create();
    Tag::withoutGlobalScopes()->create(['organization_id' => $first->id, 'name' => 'A']);
    Tag::withoutGlobalScopes()->create(['organization_id' => $second->id, 'name' => 'B']);

    app(TenantContext::class)->set($first);

    expect(Tag::pluck('name')->all())->toBe(['A']);
});
