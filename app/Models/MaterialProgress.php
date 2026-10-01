<?php

declare(strict_types=1);

namespace App\Models;

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['organization_id', 'material_id', 'user_id', 'read_at'])]
class MaterialProgress extends Model
{
    use BelongsToTenant;

    protected $table = 'material_progresses';

    protected function casts(): array
    {
        return ['read_at' => 'datetime'];
    }
}
