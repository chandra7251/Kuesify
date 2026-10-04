<?php

use App\Models\AttemptAnswer;
use App\Models\Material;
use App\Models\MaterialCheck;
use App\Models\MaterialNote;
use App\Models\Organization;
use App\Models\Question;
use App\Models\Quiz;
use App\Models\QuizAttempt;
use App\Models\User;
use App\Support\TenantContext;

afterEach(fn () => app(TenantContext::class)->clear());

it('shows a tenant-scoped question bank and exports only active organization questions', function () {
    $organization = Organization::factory()->create();
    $creator = User::factory()->create();
    $organization->members()->attach($creator, ['role' => 'creator']);
    app(TenantContext::class)->set($organization);
    Question::create(['organization_id' => $organization->id, 'creator_id' => $creator->id, 'type' => 'true_false', 'prompt' => 'Visible', 'correct_answer' => 'true']);
    app(TenantContext::class)->clear();

    $this->actingAs($creator)->post(route('organizations.switch', $organization));
    $this->get(route('questions.index'))->assertOk()->assertInertia(fn ($page) => $page->component('Workspace')->has('items.data', 1));
    $this->get(route('questions.export'))->assertOk()->assertHeader('content-type', 'text/csv; charset=UTF-8');
    $xlsx = $this->get(route('questions.export', ['format' => 'xlsx']))
        ->assertOk()
        ->assertHeader('content-type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');

    expect($xlsx->streamedContent())->toStartWith('PK')->toContain('xl/worksheets/sheet1.xml', 'Visible');
});

it('lets creator open reports but blocks participant and super admin opens platform admin', function () {
    $organization = Organization::factory()->create();
    $creator = User::factory()->create();
    $participant = User::factory()->create();
    $superAdmin = User::factory()->create();
    $organization->members()->attach($creator, ['role' => 'creator']);
    $organization->members()->attach($participant, ['role' => 'participant']);
    $organization->members()->attach($superAdmin, ['role' => 'super_admin']);
    app(TenantContext::class)->set($organization);
    Quiz::create(['organization_id' => $organization->id, 'creator_id' => $creator->id, 'title' => 'Laporan', 'status' => 'published']);
    app(TenantContext::class)->clear();

    $this->actingAs($creator)->post(route('organizations.switch', $organization));
    $this->get(route('reports.index'))->assertOk();

    $this->actingAs($participant)->post(route('organizations.switch', $organization));
    $this->get(route('reports.index'))->assertForbidden();

    $this->actingAs($superAdmin)->post(route('organizations.switch', $organization));
    $this->get(route('platform.admin'))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('superadmin/platform')
            ->has('summary.health.reverb_status')
            ->has('summary.health.reverb_host')
        );
});

it('renders reports correctly with questions and attempt statistics', function () {
    $organization = Organization::factory()->create();
    $creator = User::factory()->create();
    $participant = User::factory()->create();
    $organization->members()->attach($creator, ['role' => 'creator']);
    $organization->members()->attach($participant, ['role' => 'participant']);

    app(TenantContext::class)->set($organization);
    $quiz = Quiz::create(['organization_id' => $organization->id, 'creator_id' => $creator->id, 'title' => 'Quiz Analisis', 'status' => 'published']);
    $question = Question::create([
        'organization_id' => $organization->id,
        'creator_id' => $creator->id,
        'type' => 'true_false',
        'prompt' => 'Apakah bumi bulat?',
        'correct_answer' => 'true',
        'points' => 10,
    ]);
    $quiz->questions()->attach($question->id, ['position' => 1]);

    $attempt = QuizAttempt::create([
        'organization_id' => $organization->id,
        'quiz_id' => $quiz->id,
        'participant_id' => $participant->id,
        'status' => 'completed',
        'score' => 100,
        'started_at' => now()->subMinutes(5),
        'completed_at' => now(),
    ]);

    AttemptAnswer::create([
        'quiz_attempt_id' => $attempt->id,
        'question_id' => $question->id,
        'answer' => 'true',
        'is_correct' => true,
        'points_awarded' => 10,
    ]);
    app(TenantContext::class)->clear();

    $this->actingAs($creator)->post(route('organizations.switch', $organization));
    $this->get(route('reports.index'))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('Workspace')
            ->where('summary.completionCount', 1)
            ->where('summary.questions.0.id', $question->id)
            ->where('summary.questions.0.correct_rate', 100)
        );
});

it('blocks participants from question bank pages and exports', function () {
    $organization = Organization::factory()->create();
    $participant = User::factory()->create();
    $creator = User::factory()->create();
    $organization->members()->attach($participant, ['role' => 'participant']);
    $organization->members()->attach($creator, ['role' => 'creator']);

    app(TenantContext::class)->set($organization);
    Question::create([
        'organization_id' => $organization->id,
        'creator_id' => $creator->id,
        'type' => 'true_false',
        'prompt' => 'Jawaban tidak boleh bocor',
        'correct_answer' => 'true',
        'points' => 10,
    ]);
    app(TenantContext::class)->clear();

    $this->actingAs($participant)->post(route('organizations.switch', $organization));

    $this->get(route('questions.index'))->assertForbidden();
    $this->get(route('questions.export'))->assertForbidden();
});

it('lets participants browse and filter published quiz catalog', function () {
    $organization = Organization::factory()->create();
    $participant = User::factory()->create();
    $creator = User::factory()->create();
    $organization->members()->attach($participant, ['role' => 'participant']);
    $organization->members()->attach($creator, ['role' => 'creator']);

    $math = \App\Models\Category::create(['name' => 'Matematika', 'slug' => 'matematika']);
    $science = \App\Models\Category::create(['name' => 'Sains', 'slug' => 'sains']);

    app(TenantContext::class)->set($organization);
    $algebra = Quiz::create([
        'organization_id' => $organization->id,
        'creator_id' => $creator->id,
        'category_id' => $math->id,
        'title' => 'Aljabar Dasar',
        'description' => 'Latihan persamaan linear',
        'status' => 'published',
    ]);
    Quiz::create([
        'organization_id' => $organization->id,
        'creator_id' => $creator->id,
        'category_id' => $science->id,
        'title' => 'Biologi Sel',
        'description' => 'Latihan organel',
        'status' => 'published',
    ]);
    Quiz::create([
        'organization_id' => $organization->id,
        'creator_id' => $creator->id,
        'category_id' => $math->id,
        'title' => 'Draft Rahasia',
        'status' => 'draft',
    ]);
    app(TenantContext::class)->clear();

    $this->actingAs($participant)->post(route('organizations.switch', $organization));

    $this->get(route('participant.quizzes.index', ['search' => 'Aljabar', 'category' => $math->id]))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('participant/quizzes')
            ->where('quizzes.data.0.id', $algebra->id)
            ->where('quizzes.data.0.title', 'Aljabar Dasar')
            ->missing('quizzes.data.1')
        );
});

it('blocks creators from participant quiz catalog', function () {
    $organization = Organization::factory()->create();
    $creator = User::factory()->create();
    $organization->members()->attach($creator, ['role' => 'creator']);

    $this->actingAs($creator)->post(route('organizations.switch', $organization));

    $this->get(route('participant.quizzes.index'))->assertForbidden();
});

it('lets participants browse organization and public materials only', function () {
    $organization = Organization::factory()->create();
    $otherOrganization = Organization::factory()->create();
    $participant = User::factory()->create();
    $creator = User::factory()->create();
    $otherCreator = User::factory()->create();
    $organization->members()->attach($participant, ['role' => 'participant']);
    $organization->members()->attach($creator, ['role' => 'creator']);
    $otherOrganization->members()->attach($otherCreator, ['role' => 'creator']);

    $organizationMaterial = Material::create([
        'organization_id' => $organization->id,
        'creator_id' => $creator->id,
        'disk' => 'local',
        'path' => 'materials/'.$organization->id.'/aljabar.pdf',
        'original_name' => 'Aljabar Organisasi.pdf',
        'mime_type' => 'application/pdf',
        'size' => 1200,
        'status' => 'extracted',
        'visibility' => 'organization',
        'extracted_text' => 'Materi persamaan linear',
    ]);
    $publicMaterial = Material::create([
        'organization_id' => $otherOrganization->id,
        'creator_id' => $otherCreator->id,
        'disk' => 'local',
        'path' => 'materials/'.$otherOrganization->id.'/newton.pdf',
        'original_name' => 'Public Newton.pdf',
        'mime_type' => 'application/pdf',
        'size' => 2400,
        'status' => 'extracted',
        'visibility' => 'public',
        'extracted_text' => 'Materi hukum Newton',
    ]);
    Material::create([
        'organization_id' => $otherOrganization->id,
        'creator_id' => $otherCreator->id,
        'disk' => 'local',
        'path' => 'materials/'.$otherOrganization->id.'/private.pdf',
        'original_name' => 'Private Luar Org.pdf',
        'mime_type' => 'application/pdf',
        'size' => 3600,
        'status' => 'extracted',
        'visibility' => 'organization',
        'extracted_text' => 'Materi internal organisasi lain',
    ]);

    $this->actingAs($participant)->post(route('organizations.switch', $organization));

    $this->get(route('participant.materials.index'))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('participant/materials')
            ->where('materials.total', 2)
        );

    $this->get(route('participant.materials.index', ['scope' => 'organization']))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->where('materials.total', 1)
            ->where('materials.data.0.id', $organizationMaterial->id)
            ->where('materials.data.0.version', 1)
            ->has('materials.data.0.updated_at')
        );

    $this->get(route('participant.materials.index', ['scope' => 'public', 'search' => 'Newton']))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->where('materials.total', 1)
            ->where('materials.data.0.id', $publicMaterial->id)
        );
});

it('blocks creators from participant material library', function () {
    $organization = Organization::factory()->create();
    $creator = User::factory()->create();
    $organization->members()->attach($creator, ['role' => 'creator']);

    $this->actingAs($creator)->post(route('organizations.switch', $organization));

    $this->get(route('participant.materials.index'))->assertForbidden();
});

it('lets participants mark visible materials as read', function () {
    $organization = Organization::factory()->create();
    $otherOrganization = Organization::factory()->create();
    $participant = User::factory()->create();
    $creator = User::factory()->create();
    $otherCreator = User::factory()->create();
    $organization->members()->attach($participant, ['role' => 'participant']);
    $organization->members()->attach($creator, ['role' => 'creator']);
    $otherOrganization->members()->attach($otherCreator, ['role' => 'creator']);

    $material = Material::create([
        'organization_id' => $organization->id,
        'creator_id' => $creator->id,
        'disk' => 'local',
        'path' => 'materials/'.$organization->id.'/read.pdf',
        'original_name' => 'Read Me.pdf',
        'mime_type' => 'application/pdf',
        'size' => 1200,
        'status' => 'extracted',
        'visibility' => 'organization',
    ]);
    $hiddenMaterial = Material::create([
        'organization_id' => $otherOrganization->id,
        'creator_id' => $otherCreator->id,
        'disk' => 'local',
        'path' => 'materials/'.$otherOrganization->id.'/hidden.pdf',
        'original_name' => 'Hidden.pdf',
        'mime_type' => 'application/pdf',
        'size' => 1200,
        'status' => 'extracted',
        'visibility' => 'organization',
    ]);

    $this->actingAs($participant)->post(route('organizations.switch', $organization));

    $this->get(route('participant.materials.index', ['search' => 'Read']))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->where('materials.data.0.id', $material->id)
            ->where('materials.data.0.read_count', 0)
        );

    $this->post(route('participant.materials.read', $material))->assertRedirect();
    $this->assertDatabaseHas('material_progresses', [
        'organization_id' => $organization->id,
        'material_id' => $material->id,
        'user_id' => $participant->id,
    ]);

    $this->get(route('participant.materials.index', ['search' => 'Read']))
        ->assertOk()
        ->assertInertia(fn ($page) => $page->where('materials.data.0.read_count', 1));

    $this->post(route('participant.materials.read', $hiddenMaterial))->assertNotFound();
});

it('lets participants save and delete private material notes', function () {
    $organization = Organization::factory()->create();
    $participant = User::factory()->create();
    $creator = User::factory()->create();
    $organization->members()->attach($participant, ['role' => 'participant']);
    $organization->members()->attach($creator, ['role' => 'creator']);
    $material = Material::create([
        'organization_id' => $organization->id,
        'creator_id' => $creator->id,
        'disk' => 'local',
        'path' => 'materials/'.$organization->id.'/notes.pdf',
        'original_name' => 'Notes.pdf',
        'mime_type' => 'application/pdf',
        'size' => 1200,
        'status' => 'extracted',
        'visibility' => 'organization',
    ]);

    $this->actingAs($participant)->post(route('organizations.switch', $organization));

    $this->put(route('participant.materials.note.save', $material), ['body' => 'Ringkasan bab satu'])
        ->assertRedirect();
    $this->assertDatabaseHas('material_notes', [
        'organization_id' => $organization->id,
        'material_id' => $material->id,
        'user_id' => $participant->id,
        'body' => 'Ringkasan bab satu',
    ]);

    $this->put(route('participant.materials.note.save', $material), ['body' => 'Catatan diperbarui'])
        ->assertRedirect();
    expect(MaterialNote::withoutGlobalScopes()->where('material_id', $material->id)->where('user_id', $participant->id)->value('body'))
        ->toBe('Catatan diperbarui');

    $this->delete(route('participant.materials.note.delete', $material))->assertRedirect();
    $this->assertDatabaseMissing('material_notes', ['material_id' => $material->id, 'user_id' => $participant->id]);
});

it('lets participants open a print-friendly visible material page with private note', function () {
    $organization = Organization::factory()->create();
    $otherOrganization = Organization::factory()->create();
    $participant = User::factory()->create();
    $creator = User::factory()->create();
    $otherCreator = User::factory()->create();
    $organization->members()->attach($participant, ['role' => 'participant']);
    $organization->members()->attach($creator, ['role' => 'creator']);
    $otherOrganization->members()->attach($otherCreator, ['role' => 'creator']);

    $material = Material::create([
        'organization_id' => $organization->id,
        'creator_id' => $creator->id,
        'disk' => 'local',
        'path' => 'materials/'.$organization->id.'/print.pdf',
        'original_name' => 'Printable.pdf',
        'mime_type' => 'application/pdf',
        'size' => 1200,
        'status' => 'extracted',
        'visibility' => 'organization',
        'extracted_text' => 'Isi materi untuk dicetak',
    ]);
    $hiddenMaterial = Material::create([
        'organization_id' => $otherOrganization->id,
        'creator_id' => $otherCreator->id,
        'disk' => 'local',
        'path' => 'materials/'.$otherOrganization->id.'/hidden-print.pdf',
        'original_name' => 'Hidden Print.pdf',
        'mime_type' => 'application/pdf',
        'size' => 1200,
        'status' => 'extracted',
        'visibility' => 'organization',
        'extracted_text' => 'Tidak boleh terlihat',
    ]);

    MaterialNote::create([
        'organization_id' => $organization->id,
        'material_id' => $material->id,
        'user_id' => $participant->id,
        'body' => 'Catatan ikut dicetak',
    ]);

    $this->actingAs($participant)->post(route('organizations.switch', $organization));

    $this->get(route('participant.materials.print', $material))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('participant/material-print')
            ->where('material.id', $material->id)
            ->where('material.version', 1)
            ->has('material.updated_at')
            ->where('material.extracted_text', 'Isi materi untuk dicetak')
            ->where('note', 'Catatan ikut dicetak')
        );

    $this->get(route('participant.materials.print', $hiddenMaterial))->assertNotFound();
});

it('lets participants save check-understanding answers for visible materials', function () {
    $organization = Organization::factory()->create();
    $participant = User::factory()->create();
    $creator = User::factory()->create();
    $organization->members()->attach($participant, ['role' => 'participant']);
    $organization->members()->attach($creator, ['role' => 'creator']);
    $material = Material::create([
        'organization_id' => $organization->id,
        'creator_id' => $creator->id,
        'disk' => 'local',
        'path' => 'materials/'.$organization->id.'/check.pdf',
        'original_name' => 'Check.pdf',
        'mime_type' => 'application/pdf',
        'size' => 1200,
        'status' => 'extracted',
        'visibility' => 'organization',
        'extracted_text' => 'Materi untuk cek pemahaman',
    ]);

    $this->actingAs($participant)->post(route('organizations.switch', $organization));

    $this->put(route('participant.materials.check.save', $material), [
        'answer' => 'Saya paham konsep utamanya',
        'confidence' => 4,
    ])->assertRedirect();
    $this->assertDatabaseHas('material_checks', [
        'organization_id' => $organization->id,
        'material_id' => $material->id,
        'user_id' => $participant->id,
        'answer' => 'Saya paham konsep utamanya',
        'confidence' => 4,
    ]);

    $this->put(route('participant.materials.check.save', $material), [
        'answer' => 'Jawaban diperbarui',
        'confidence' => 5,
    ])->assertRedirect();
    expect(MaterialCheck::withoutGlobalScopes()->where('material_id', $material->id)->where('user_id', $participant->id)->value('answer'))
        ->toBe('Jawaban diperbarui');
});

it('lets participants browse badges catalog and blocks creators', function () {
    $organization = Organization::factory()->create();
    $participant = User::factory()->create();
    $creator = User::factory()->create();
    $organization->members()->attach($participant, ['role' => 'participant']);
    $organization->members()->attach($creator, ['role' => 'creator']);

    $this->actingAs($participant)->post(route('organizations.switch', $organization));
    $this->get(route('participant.badges.index'))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('participant/badges')
            ->has('badges')
            ->has('stats.earnedCount')
            ->has('stats.totalCount')
        );

    $this->actingAs($creator)->post(route('organizations.switch', $organization));
    $this->get(route('participant.badges.index'))->assertForbidden();
});
