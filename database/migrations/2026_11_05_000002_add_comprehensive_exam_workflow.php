<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $duplicateExists = DB::table('exam_application')
            ->select('student_id', 'school_year')
            ->whereNotNull('school_year')
            ->groupBy('student_id', 'school_year')
            ->havingRaw('COUNT(*) > 1')
            ->exists();

        if ($duplicateExists) {
            throw new \RuntimeException(
                'Cannot add the comprehensive-exam uniqueness constraint: duplicate student/year applications exist. '
                .'Resolve duplicates in a backed-up database, then rerun the migration.'
            );
        }

        $applicationIndexes = array_column(Schema::getIndexes('exam_application'), 'name');
        $applicationForeignKeyColumns = array_merge(
            [],
            ...array_column(Schema::getForeignKeys('exam_application'), 'columns')
        );

        Schema::table('exam_application', function (Blueprint $table) use ($applicationIndexes, $applicationForeignKeyColumns) {
            if (! Schema::hasColumn('exam_application', 'application_no')) {
                $table->string('application_no', 32)->nullable()->after('application_id');
            }
            if (! Schema::hasColumn('exam_application', 'workflow_status')) {
                $table->string('workflow_status', 32)->default('submitted');
            }
            if (! Schema::hasColumn('exam_application', 'submitted_at')) {
                $table->timestamp('submitted_at')->nullable();
            }
            if (! Schema::hasColumn('exam_application', 'aa_reviewed_at')) {
                $table->timestamp('aa_reviewed_at')->nullable();
            }
            if (! Schema::hasColumn('exam_application', 'aa_reviewed_by')) {
                $table->foreignId('aa_reviewed_by')->nullable();
            }
            if (! in_array('aa_reviewed_by', $applicationForeignKeyColumns, true)) {
                $table->foreign('aa_reviewed_by')->references('id')->on('users')->nullOnDelete();
            }
            if (! Schema::hasColumn('exam_application', 'aa_remarks')) {
                $table->text('aa_remarks')->nullable();
            }
            if (! Schema::hasColumn('exam_application', 'registrar_reviewed_at')) {
                $table->timestamp('registrar_reviewed_at')->nullable();
            }
            if (! Schema::hasColumn('exam_application', 'registrar_reviewed_by')) {
                $table->foreignId('registrar_reviewed_by')->nullable();
            }
            if (! in_array('registrar_reviewed_by', $applicationForeignKeyColumns, true)) {
                $table->foreign('registrar_reviewed_by')->references('id')->on('users')->nullOnDelete();
            }
            if (! Schema::hasColumn('exam_application', 'registrar_remarks')) {
                $table->text('registrar_remarks')->nullable();
            }
            if (! Schema::hasColumn('exam_application', 'payment_status')) {
                $table->string('payment_status', 20)->default('not_paid');
            }
            if (! Schema::hasColumn('exam_application', 'scheduled_at')) {
                $table->timestamp('scheduled_at')->nullable();
            }
            if (! Schema::hasColumn('exam_application', 'completed_at')) {
                $table->timestamp('completed_at')->nullable();
            }
            if (! Schema::hasColumn('exam_application', 'version')) {
                $table->unsignedInteger('version')->default(1);
            }
            if (! in_array('exam_application_application_no_unique', $applicationIndexes, true)) {
                $table->unique('application_no', 'exam_application_application_no_unique');
            }
            if (! in_array('exam_application_workflow_status_index', $applicationIndexes, true)) {
                $table->index('workflow_status', 'exam_application_workflow_status_index');
            }
            if (! in_array('exam_application_payment_status_index', $applicationIndexes, true)) {
                $table->index('payment_status', 'exam_application_payment_status_index');
            }
            if (! in_array('exam_application_student_year_unique', $applicationIndexes, true)) {
                $table->unique(['student_id', 'school_year'], 'exam_application_student_year_unique');
            }
        });

        DB::table('exam_application')->whereNull('application_no')->orderBy('application_id')->chunkById(100, function ($applications) {
            foreach ($applications as $application) {
                $legacyStatus = strtolower((string) $application->final_approval_status);
                $workflowStatus = match ($legacyStatus) {
                    'approved' => 'registrar_approved',
                    'rejected' => 'registrar_rejected',
                    default => 'submitted',
                };

                DB::table('exam_application')->where('application_id', $application->application_id)->update([
                    'application_no' => sprintf('CE-%s-%06d', now()->format('Y'), $application->application_id),
                    'workflow_status' => $workflowStatus,
                    'submitted_at' => $application->created_at,
                    'registrar_reviewed_at' => in_array($workflowStatus, ['registrar_approved', 'registrar_rejected'], true)
                        ? ($application->final_approval_DATE ?? $application->created_at)
                        : null,
                    'registrar_remarks' => $workflowStatus === 'registrar_rejected'
                        ? ($application->permit_reason ?? $application->review_remarks ?? null)
                        : null,
                    'updated_at' => $application->updated_at ?? $application->created_at,
                ]);
            }
        }, 'application_id');

        if (! Schema::hasTable('exam_application_events')) {
            Schema::create('exam_application_events', function (Blueprint $table) {
                $table->id();
                $table->foreignId('exam_application_id')->constrained('exam_application', 'application_id')->cascadeOnDelete();
                $table->foreignId('actor_id')->nullable()->constrained('users')->nullOnDelete();
                $table->string('action', 40);
                $table->string('from_status', 32)->nullable();
                $table->string('to_status', 32)->nullable();
                $table->text('remarks')->nullable();
                $table->json('metadata')->nullable();
                $table->timestamp('created_at')->useCurrent();
                $table->index(['exam_application_id', 'created_at']);
            });
        }

        if (! Schema::hasTable('exam_requirement_checks')) {
            Schema::create('exam_requirement_checks', function (Blueprint $table) {
                $table->id();
                $table->foreignId('exam_application_id')->constrained('exam_application', 'application_id')->cascadeOnDelete();
                $table->string('requirement_key', 60);
                $table->string('status', 20)->default('pending');
                $table->text('remarks')->nullable();
                $table->foreignId('checked_by')->nullable()->constrained('users')->nullOnDelete();
                $table->timestamp('checked_at')->nullable();
                $table->timestamps();
            });
        }
        if (! Schema::hasIndex('exam_requirement_checks', 'exam_requirement_checks_app_req_unique')) {
            Schema::table('exam_requirement_checks', function (Blueprint $table) {
                $table->unique(['exam_application_id', 'requirement_key'], 'exam_requirement_checks_app_req_unique');
            });
        }

        if (! Schema::hasTable('exam_rooms')) {
            Schema::create('exam_rooms', function (Blueprint $table) {
                $table->id();
                $table->string('code', 30)->unique();
                $table->string('name', 120);
                $table->string('building', 120)->nullable();
                $table->unsignedInteger('capacity')->default(30);
                $table->boolean('is_active')->default(true);
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('exam_schedules')) {
            Schema::create('exam_schedules', function (Blueprint $table) {
                $table->id();
                $table->foreignId('exam_application_id')->constrained('exam_application', 'application_id')->cascadeOnDelete();
                $table->foreignId('exam_application_subject_id')->nullable()->unique()
                    ->constrained('exam_application_subject', 'id')->cascadeOnDelete();
                $table->foreignId('room_id')->nullable()->constrained('exam_rooms')->nullOnDelete();
                $table->dateTime('starts_at');
                $table->dateTime('ends_at');
                $table->string('status', 20)->default('draft');
                $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
                $table->timestamps();
                $table->index(['room_id', 'starts_at', 'ends_at']);
            });
        }

        if (! Schema::hasTable('exam_proctors')) {
            Schema::create('exam_proctors', function (Blueprint $table) {
                $table->id();
                $table->foreignId('exam_schedule_id')->constrained('exam_schedules')->cascadeOnDelete();
                $table->foreignId('user_id')->constrained()->cascadeOnDelete();
                $table->string('role', 20)->default('proctor');
                $table->string('status', 20)->default('invited');
                $table->timestamp('responded_at')->nullable();
                $table->text('decline_reason')->nullable();
                $table->timestamps();
                $table->unique(['exam_schedule_id', 'user_id']);
                $table->index(['user_id', 'status']);
            });
        }

        if (! Schema::hasTable('student_academic_snapshots')) {
            Schema::create('student_academic_snapshots', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')->constrained()->cascadeOnDelete();
                $table->string('school_year', 20)->nullable();
                $table->string('semester_id', 40)->nullable();
                $table->json('subjects')->nullable();
                $table->json('grades_summary')->nullable();
                $table->json('clearance_summary')->nullable();
                $table->decimal('outstanding_balance', 12, 2)->default(0);
                $table->string('source', 30)->default('legacy_portal');
                $table->char('checksum', 64)->nullable();
                $table->timestamp('fetched_at')->nullable();
                $table->boolean('stale')->default(false);
                $table->timestamps();
                $table->unique(['user_id', 'semester_id']);
            });
        }

        if (! Schema::hasTable('exam_settings')) {
            Schema::create('exam_settings', function (Blueprint $table) {
                $table->id();
                $table->string('school_year', 20)->unique();
                $table->dateTime('opens_at')->nullable();
                $table->dateTime('closes_at')->nullable();
                $table->decimal('fee', 10, 2)->default(0);
                $table->json('required_requirements')->nullable();
                $table->unsignedInteger('max_receipt_kb')->default(5120);
                $table->boolean('sms_enabled')->default(false);
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('exam_settings');
        Schema::dropIfExists('student_academic_snapshots');
        Schema::dropIfExists('exam_proctors');
        Schema::dropIfExists('exam_schedules');
        Schema::dropIfExists('exam_rooms');
        Schema::dropIfExists('exam_requirement_checks');
        Schema::dropIfExists('exam_application_events');

        Schema::table('exam_application', function (Blueprint $table) {
            $table->dropUnique('exam_application_student_year_unique');
            $table->dropForeign(['aa_reviewed_by']);
            $table->dropForeign(['registrar_reviewed_by']);
            $table->dropUnique(['application_no']);
            $table->dropIndex(['workflow_status']);
            $table->dropIndex(['payment_status']);
            $table->dropColumn([
                'application_no',
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
            ]);
        });
    }
};
