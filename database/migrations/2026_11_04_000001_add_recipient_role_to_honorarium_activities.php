<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('honorarium_activities', function (Blueprint $table) {
<<<<<<< Updated upstream
            $table->string('recipient_role', 60)->nullable()->after('description');
=======
            $table->string('recipient_role')->nullable()->index();
>>>>>>> Stashed changes
        });
    }

    public function down(): void
    {
        Schema::table('honorarium_activities', function (Blueprint $table) {
<<<<<<< Updated upstream
=======
            $table->dropIndex(['recipient_role']);
>>>>>>> Stashed changes
            $table->dropColumn('recipient_role');
        });
    }
};
