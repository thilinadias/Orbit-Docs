<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('activity_logs', function (Blueprint $table) {
            $table->renameColumn('entity_type', 'subject_type');
        });

        Schema::table('activity_logs', function (Blueprint $table) {
            $table->renameColumn('entity_id', 'subject_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('activity_logs', function (Blueprint $table) {
            $table->renameColumn('subject_type', 'entity_type');
        });

        Schema::table('activity_logs', function (Blueprint $table) {
            $table->renameColumn('subject_id', 'entity_id');
        });
    }
};
