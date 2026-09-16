<?php

namespace App\Models;

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['organization_id', 'user_id', 'xp', 'level', 'streak', 'last_activity_date'])]
class UserProgress extends Model
{
    use BelongsToTenant;

    protected $table = 'user_progresses';

    protected function casts(): array
    {
        return ['last_activity_date' => 'date'];
    }
}
