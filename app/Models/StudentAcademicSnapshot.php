<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StudentAcademicSnapshot extends Model
{
    protected $fillable = [
        'user_id',
        'school_year',
        'semester_id',
        'subjects',
        'grades_summary',
        'clearance_summary',
        'outstanding_balance',
        'source',
        'checksum',
        'fetched_at',
        'stale',
    ];

    protected function casts(): array
    {
        return [
            'subjects' => 'array',
            'grades_summary' => 'array',
            'clearance_summary' => 'array',
            'outstanding_balance' => 'decimal:2',
            'fetched_at' => 'datetime',
            'stale' => 'boolean',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
