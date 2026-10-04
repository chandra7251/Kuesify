<?php

declare(strict_types=1);

namespace App\Models;

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['organization_id', 'material_id', 'user_id', 'body'])]
class MaterialNote extends Model
{
    use BelongsToTenant;

    protected $table = 'material_notes';
}
