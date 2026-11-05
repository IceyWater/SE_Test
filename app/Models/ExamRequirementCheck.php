<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ExamRequirementCheck extends Model
{
    protected $fillable = [
        'exam_application_id',
        'requirement_key',
        'status',
        'remarks',
        'checked_by',
        'checked_at',
    ];

    protected function casts(): array
    {
        return ['checked_at' => 'datetime'];
    }

    public function application(): BelongsTo
    {
        return $this->belongsTo(ExamApplication::class, 'exam_application_id', 'application_id');
    }

    public function checker(): BelongsTo
    {
        return $this->belongsTo(User::class, 'checked_by');
    }
}
