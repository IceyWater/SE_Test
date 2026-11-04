<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('comprehensive_exam_results', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained('users');
            $table->string('program');
            $table->string('result');
            $table->date('exam_date');
            $table->text('record_reference');
            $table->foreignId('recorded_by')->constrained('users');
            $table->unsignedInteger('version')->default(1);
            $table->json('history')->nullable();
            $table->timestamps();
            $table->unique(['student_id', 'program']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('comprehensive_exam_results');
    }
};
