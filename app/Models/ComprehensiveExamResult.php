<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ComprehensiveExamResult extends Model
{
    protected $guarded = [];

    protected $casts = ['exam_date' => 'date', 'history' => 'array', 'version' => 'integer'];
}
