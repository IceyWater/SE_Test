<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('honorarium_activities', function (Blueprint $table) {
            $table->string('recipient_role', 60)->nullable()->after('description');
        });
    }

    public function down(): void
    {
        Schema::table('honorarium_activities', function (Blueprint $table) {
            $table->dropColumn('recipient_role');
        });
    }
};
