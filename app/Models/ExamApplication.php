<?php

// filepath: c:\GSPS\Graduate_School_System\app\Models\ExamApplication.php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ExamApplication extends Model
{
    protected $table = 'exam_application';

    protected $primaryKey = 'application_id';

    public $timestamps = false;

    protected function casts(): array
    {
        return [
            'submitted_at' => 'datetime',
            'aa_reviewed_at' => 'datetime',
            'registrar_reviewed_at' => 'datetime',
            'scheduled_at' => 'datetime',
            'completed_at' => 'datetime',
            'created_at' => 'datetime',
        ];
    }

    protected $fillable = [
        'application_no',
        'student_id',
        'school_year',
        'workflow_status',
        'submitted_at',
        'aa_reviewed_at',
        'aa_reviewed_by',
        'aa_remarks',
        'registrar_reviewed_at',
        'registrar_reviewed_by',
        'registrar_remarks',
        'payment_status',
        'scheduled_at',
        'completed_at',
        'version',
        'permit_status',
        'permit_DATE',
        'permit_reason',
        'final_approval_status',
        'final_approval_DATE',
        'approved_by',
        'reviewed_by', 'reviewed_at', 'review_remarks',
        'contact_number',
        'telephone_number',
        'office_address',
        'program',
        'created_at',
    ];

    public function subjects(): HasMany
    {
        return $this->hasMany(ExamApplicationSubject::class, 'application_id', 'application_id');
    }

    public function events(): HasMany
    {
        return $this->hasMany(ExamApplicationEvent::class, 'exam_application_id', 'application_id')->orderBy('created_at');
    }

    public function requirementChecks(): HasMany
    {
        return $this->hasMany(ExamRequirementCheck::class, 'exam_application_id', 'application_id');
    }

    public function schedules(): HasMany
    {
        return $this->hasMany(ExamSchedule::class, 'exam_application_id', 'application_id');
    }
}
