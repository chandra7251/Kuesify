<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['key', 'name', 'description', 'rarity', 'criteria_type', 'criteria_value'])]
class Badge extends Model
{
}
