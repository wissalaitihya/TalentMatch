<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('analyses') && ! Schema::hasColumn('analyses', 'statut_analyse')) {
            Schema::table('analyses', function (Blueprint $table) {
                $table->string('statut_analyse')->default('pending');
            });
        }

        if (Schema::hasTable('analyses') && ! Schema::hasColumn('analyses', 'message_erreur')) {
            Schema::table('analyses', function (Blueprint $table) {
                $table->text('message_erreur')->nullable();
            });
        }
    }

    public function down(): void {}
};
