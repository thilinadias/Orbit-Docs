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
        Schema::table('documents', function (Blueprint $table) {
            if (!Schema::hasColumn('documents', 'documentable_type')) {
                $table->nullableMorphs('documentable');
            }

            if (!Schema::hasColumn('documents', 'is_upload')) {
                $table->boolean('is_upload')->after('visibility')->default(false);
            }

            if (!Schema::hasColumn('documents', 'file_path')) {
                $table->string('file_path')->after('is_upload')->nullable();
            }

            if (!Schema::hasColumn('documents', 'file_name')) {
                $table->string('file_name')->after('file_path')->nullable();
            }

            if (!Schema::hasColumn('documents', 'mime_type')) {
                $table->string('mime_type')->after('file_name')->nullable();
            }

            if (!Schema::hasColumn('documents', 'file_size')) {
                $table->unsignedBigInteger('file_size')->after('mime_type')->nullable();
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('documents', function (Blueprint $table) {
            $table->dropMorphs('documentable');
            $table->dropColumn(['is_upload', 'file_path', 'file_name', 'mime_type', 'file_size']);
        });
    }
};
