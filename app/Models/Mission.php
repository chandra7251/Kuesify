<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['key', 'kind', 'title', 'description', 'goal', 'reward_xp', 'is_active'])]
class Mission extends Model
{
    protected function casts(): array
    {
        return ['is_active' => 'boolean'];
    }
}