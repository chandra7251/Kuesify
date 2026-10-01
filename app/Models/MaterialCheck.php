<?php

declare(strict_types=1);

namespace App\Models;

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['organization_id', 'material_id', 'user_id', 'answer', 'confidence'])]
class MaterialCheck extends Model
{
    use BelongsToTenant;
}
