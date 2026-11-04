<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class HonorariumActivity extends Model
{
    protected $fillable = [
        'defense_request_id', 'user_id', 'finance_document_submission_id',
<<<<<<< Updated upstream
        'activity_type', 'title', 'description', 'recipient_role', 'occurred_at',
=======
        'activity_type', 'title', 'description', 'occurred_at', 'recipient_role',
>>>>>>> Stashed changes
    ];

    protected function casts(): array
    {
        return ['occurred_at' => 'datetime'];
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function defenseRequest()
    {
        return $this->belongsTo(DefenseRequest::class);
    }

    public function documentSubmission()
    {
        return $this->belongsTo(FinanceDocumentSubmission::class, 'finance_document_submission_id');
    }
}
