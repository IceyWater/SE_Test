<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ExamSetting extends Model
{
    protected $fillable = [
        'school_year',
        'opens_at',
        'closes_at',
        'fee',
        'required_requirements',
        'max_receipt_kb',
        'sms_enabled',
    ];

    protected function casts(): array
    {
        return [
            'opens_at' => 'datetime',
            'closes_at' => 'datetime',
            'fee' => 'decimal:2',
            'required_requirements' => 'array',
            'max_receipt_kb' => 'integer',
            'sms_enabled' => 'boolean',
        ];
    }
}
