<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['question_id', 'answer', 'is_correct', 'points_awarded'])]
class LiveAnswer extends Model
{
}
