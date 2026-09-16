<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Organization;
use App\Models\Question;
use App\Models\Quiz;
use App\Support\TenantContext;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();

        $organization = Organization::firstOrCreate(['slug' => 'kuesify-demo'], ['name' => 'Kuesify Demo', 'timezone' => 'Asia/Jakarta']);
        foreach ([
            ['name' => 'Demo Participant', 'email' => 'participant@kuesify.test', 'role' => 'participant'],
            ['name' => 'Demo Creator', 'email' => 'creator@kuesify.test', 'role' => 'creator'],
            ['name' => 'Demo Organization Admin', 'email' => 'admin@kuesify.test', 'role' => 'organization_admin'],
            ['name' => 'Demo Super Admin', 'email' => 'superadmin@kuesify.test', 'role' => 'super_admin'],
        ] as $account) {
            $user = User::firstOrCreate(['email' => $account['email']], ['name' => $account['name'], 'password' => 'password', 'avatar_key' => 'book']);
            if ($user->email_verified_at === null) {
                $user->forceFill(['email_verified_at' => now()])->save();
            }
            $organization->members()->syncWithoutDetaching([$user->id => ['role' => $account['role'], 'is_active' => true]]);
        }

        $creator = User::where('email', 'creator@kuesify.test')->firstOrFail();
        app(TenantContext::class)->set($organization);

        $producer = Question::firstOrCreate(
            ['organization_id' => $organization->id, 'prompt' => 'Makhluk hidup yang membuat makanan sendiri disebut?'],
            ['creator_id' => $creator->id, 'type' => 'multiple_choice', 'options' => ['Produsen', 'Konsumen', 'Pengurai'], 'correct_answer' => 'Produsen', 'points' => 100],
        );
        $evaporation = Question::firstOrCreate(
            ['organization_id' => $organization->id, 'prompt' => 'Air menguap karena panas matahari.'],
            ['creator_id' => $creator->id, 'type' => 'true_false', 'options' => ['true', 'false'], 'correct_answer' => 'true', 'points' => 100],
        );
        $essay = Question::firstOrCreate(
            ['organization_id' => $organization->id, 'prompt' => 'Jelaskan satu cara menghemat air di rumah.'],
            ['creator_id' => $creator->id, 'type' => 'essay', 'points' => 100],
        );
        $liveQuiz = Quiz::firstOrCreate(
            ['organization_id' => $organization->id, 'title' => 'Kuis Pemanasan'],
            ['creator_id' => $creator->id, 'status' => 'published', 'visibility' => 'organization', 'description' => 'Demo soal objektif untuk mode mandiri dan live.'],
        );
        $liveQuiz->questions()->syncWithoutDetaching([$producer->id => ['position' => 1], $evaporation->id => ['position' => 2]]);
        $homeworkQuiz = Quiz::firstOrCreate(
            ['organization_id' => $organization->id, 'title' => 'Refleksi Lingkungan'],
            ['creator_id' => $creator->id, 'status' => 'published', 'visibility' => 'organization', 'description' => 'Demo essay dengan penilaian manual.'],
        );
        $homeworkQuiz->questions()->syncWithoutDetaching([$essay->id => ['position' => 1]]);

        app(TenantContext::class)->clear();
    }
}
