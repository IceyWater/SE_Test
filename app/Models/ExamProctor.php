<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ExamProctor extends Model
{
    protected $fillable = [
        'exam_schedule_id',
        'user_id',
        'role',
        'status',
        'responded_at',
        'decline_reason',
    ];

    protected function casts(): array
    {
        return ['responded_at' => 'datetime'];
    }

    public function schedule(): BelongsTo
    {
        return $this->belongsTo(ExamSchedule::class, 'exam_schedule_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
