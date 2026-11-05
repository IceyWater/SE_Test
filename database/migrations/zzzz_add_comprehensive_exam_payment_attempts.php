<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('payment_submissions', function (Blueprint $table) {
            if (! Schema::hasColumn('payment_submissions', 'exam_application_id')) {
                $table->unsignedBigInteger('exam_application_id')->nullable()->after('student_id');
            }
            if (! Schema::hasColumn('payment_submissions', 'attempt')) {
                $table->unsignedSmallInteger('attempt')->default(1)->after('exam_application_id')
                    ->comment('Attempt number for this application payment.');
            }
            if (! Schema::hasColumn('payment_submissions', 'receipt_sha256')) {
                $table->char('receipt_sha256', 64)->nullable()->after('receipt_image');
            }
        });

        $paymentForeignKeys = Schema::getForeignKeys('payment_submissions');
        if (! in_array('exam_application_id', array_merge([], ...array_column($paymentForeignKeys, 'columns')), true)) {
            Schema::table('payment_submissions', function (Blueprint $table) {
                $table->foreign('exam_application_id')
                    ->references('application_id')->on('exam_application')
                    ->nullOnDelete();
            });
        }

        DB::table('payment_submissions')->orderBy('payment_id')->chunk(100, function ($payments) {
            foreach ($payments as $payment) {
                $user = DB::table('users')->where('id', $payment->student_id)->first();
                if (! $user) {
                    continue;
                }

                $studentIds = array_values(array_filter([$user->school_id, $user->id], fn ($id) => $id !== null && $id !== ''));
                $applications = DB::table('exam_application')
                    ->whereIn('student_id', $studentIds)
                    ->get(['application_id']);

                if ($applications->count() === 1) {
                    DB::table('payment_submissions')->where('payment_id', $payment->payment_id)
                        ->update(['exam_application_id' => $applications->first()->application_id]);
                }
            }
        });

        if (! Schema::hasIndex('payment_submissions', 'payment_submissions_student_exam_attempt_index')) {
            Schema::table('payment_submissions', function (Blueprint $table) {
                $table->index(['student_id', 'payment_type', 'payment_id'], 'payment_submissions_student_exam_attempt_index');
            });
        }
        if (Schema::hasIndex('payment_submissions', 'payment_submissions_student_id_payment_type_unique')) {
            Schema::table('payment_submissions', function (Blueprint $table) {
                $table->dropUnique('payment_submissions_student_id_payment_type_unique');
            });
        }
        if (! Schema::hasIndex('payment_submissions', 'payment_submissions_application_attempt_unique')) {
            Schema::table('payment_submissions', function (Blueprint $table) {
                $table->unique(['exam_application_id', 'attempt'], 'payment_submissions_application_attempt_unique');
            });
        }
    }

    public function down(): void
    {
        $hasMultipleStudentAttempts = DB::table('payment_submissions')
            ->select('student_id', 'payment_type')
            ->groupBy('student_id', 'payment_type')
            ->havingRaw('COUNT(*) > 1')
            ->exists();
        if ($hasMultipleStudentAttempts) {
            throw new RuntimeException('Cannot roll back exam payment attempts while multiple attempts exist for a student; archive those attempts first.');
        }

        Schema::table('payment_submissions', function (Blueprint $table) {
            $table->dropUnique('payment_submissions_application_attempt_unique');
            $table->dropIndex('payment_submissions_student_exam_attempt_index');
            $table->dropForeign(['exam_application_id']);
            $table->dropColumn(['exam_application_id', 'attempt', 'receipt_sha256']);
            $table->unique(['student_id', 'payment_type']);
        });
    }
};
