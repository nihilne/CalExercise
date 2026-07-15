<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ExerciseLog extends Model
{
    protected $fillable = ['date', 'minutes', 'kilometers'];

    protected $casts = [
        'date' => 'date',
    ];
}
