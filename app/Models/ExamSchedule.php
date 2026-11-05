<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ExamSchedule extends Model
{
    protected $fillable = [
        'exam_application_id',
        'exam_application_subject_id',
        'room_id',
        'starts_at',
        'ends_at',
        'status',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'starts_at' => 'datetime',
            'ends_at' => 'datetime',
        ];
    }

    public function application(): BelongsTo
    {
        return $this->belongsTo(ExamApplication::class, 'exam_application_id', 'application_id');
    }

    public function subject(): BelongsTo
    {
        return $this->belongsTo(ExamApplicationSubject::class, 'exam_application_subject_id');
    }

    public function room(): BelongsTo
    {
        return $this->belongsTo(ExamRoom::class);
    }

    public function proctors(): HasMany
    {
        return $this->hasMany(ExamProctor::class);
    }
}
