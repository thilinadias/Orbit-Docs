<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration 
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Organizations
        if (!Schema::hasTable('organizations')) {
            Schema::create('organizations', function (Blueprint $table) {
                $table->id();
                $table->string('name');
                $table->string('slug')->unique();
                $table->string('logo')->nullable();
                $table->timestamps();
            });
        }

        // 2. Roles & Permissions (Simple RBAC)
        if (!Schema::hasTable('roles')) {
            Schema::create('roles', function (Blueprint $table) {
                $table->id();
                $table->string('name')->unique(); // Super Admin, Admin, Technician, Read-Only
                $table->string('label')->nullable();
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('permissions')) {
            Schema::create('permissions', function (Blueprint $table) {
                $table->id();
                $table->string('name')->unique();
                $table->string('label')->nullable();
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('permission_role')) {
            Schema::create('permission_role', function (Blueprint $table) {
                $table->id();
                $table->foreignId('role_id')->constrained()->cascadeOnDelete();
                $table->foreignId('permission_id')->constrained()->cascadeOnDelete();
            });
        }

        // 3. Organization User Pivot with Role
        Schema::dropIfExists('organization_user');
        Schema::create('organization_user', function (Blueprint $table) {
            $table->id();
            $table->foreignId('organization_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('role_id')->nullable()->constrained()->nullOnDelete(); // Role within this org
            $table->timestamps();

            $table->unique(['organization_id', 'user_id']);
        });

        // 4. Asset Management
        if (!Schema::hasTable('asset_types')) {
            Schema::create('asset_types', function (Blueprint $table) {
                $table->id();
                $table->string('name'); // Server, Laptop, Licence, etc.
                $table->string('icon')->nullable();
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('assets')) {
            Schema::create('assets', function (Blueprint $table) {
                $table->id();
                $table->foreignId('organization_id')->constrained()->cascadeOnDelete();
                $table->foreignId('asset_type_id')->constrained()->cascadeOnDelete();
                $table->string('name');
                $table->string('serial_number')->nullable();
                $table->date('purchase_date')->nullable();
                $table->date('warranty_expire_date')->nullable();
                $table->string('status')->default('active'); // active, archived, broken
                $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('asset_custom_fields')) {
            Schema::create('asset_custom_fields', function (Blueprint $table) {
                $table->id();
                $table->foreignId('asset_type_id')->constrained()->cascadeOnDelete();
                $table->string('name'); // e.g. "IP Address", "CPU", "RAM"
                $table->string('field_type')->default('text'); // text, date, number, select
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('asset_values')) {
            Schema::create('asset_values', function (Blueprint $table) {
                $table->id();
                $table->foreignId('asset_id')->constrained()->cascadeOnDelete();
                $table->foreignId('asset_custom_field_id')->constrained()->cascadeOnDelete();
                $table->text('value')->nullable();
                $table->timestamps();
            });
        }

        // 5. Credentials Vault
        if (!Schema::hasTable('credentials')) {
            Schema::create('credentials', function (Blueprint $table) {
                $table->id();
                $table->foreignId('organization_id')->constrained()->cascadeOnDelete();
                $table->foreignId('asset_id')->nullable()->constrained()->nullOnDelete(); // Optional link to asset
                $table->string('title');
                $table->string('username')->nullable();
                $table->text('encrypted_password'); // Will use Laravel Crypt
                $table->string('url')->nullable();
                $table->text('notes')->nullable();
                $table->timestamps();
            });
        }

        // 6. Documentation System
        if (!Schema::hasTable('folders')) {
            Schema::create('folders', function (Blueprint $table) { // Optional structure
                $table->id();
                $table->foreignId('organization_id')->constrained()->cascadeOnDelete();
                $table->foreignId('parent_id')->nullable()->constrained('folders')->cascadeOnDelete();
                $table->string('name');
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('documents')) {
            Schema::create('documents', function (Blueprint $table) {
                $table->id();
                $table->foreignId('organization_id')->constrained()->cascadeOnDelete();
                $table->foreignId('folder_id')->nullable()->constrained()->nullOnDelete();
                $table->string('title');
                $table->longText('content')->nullable(); // Markdown
                $table->boolean('is_public')->default(false);
                $table->foreignId('last_modified_by')->nullable()->constrained('users')->nullOnDelete();
                $table->timestamps();

            // Fulltext index manually added if needed, or via DB statement
            });
        }

        // Fulltext index for MySQL 8 — only add if not already present
        if (DB::getDriverName() !== 'sqlite') {
            $dbName = DB::connection()->getDatabaseName();
            $existing = DB::select(
                "SELECT INDEX_NAME FROM information_schema.STATISTICS
                  WHERE TABLE_SCHEMA = ? AND TABLE_NAME = 'documents'
                    AND INDEX_NAME = 'fulltext_index'",
            [$dbName]
            );
            if (empty($existing)) {
                DB::statement('ALTER TABLE documents ADD FULLTEXT fulltext_index (title, content)');
            }
        }

        if (!Schema::hasTable('document_versions')) {
            Schema::create('document_versions', function (Blueprint $table) {
                $table->id();
                $table->foreignId('document_id')->constrained()->cascadeOnDelete();
                $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
                $table->longText('content');
                $table->timestamps(); // Created at = version date
            });
        }

        // 7. Tags
        if (!Schema::hasTable('tags')) {
            Schema::create('tags', function (Blueprint $table) {
                $table->id();
                $table->string('name');
                $table->string('slug')->unique();
                $table->string('color')->default('gray');
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('taggables')) {
            Schema::create('taggables', function (Blueprint $table) {
                $table->id();
                $table->foreignId('tag_id')->constrained()->cascadeOnDelete();
                $table->morphs('taggable'); // asset, document, credential
            });
        }

        // 8. Relationships (Many-to-Many Linking)
        if (!Schema::hasTable('relationships')) {
            Schema::create('relationships', function (Blueprint $table) {
                $table->id();
                $table->foreignId('organization_id')->constrained()->cascadeOnDelete();
                $table->morphs('source'); // e.g. asset:1
                $table->morphs('target'); // e.g. document:5
                $table->string('type')->nullable(); // "installed_on", "depends_on"
                $table->timestamps();
            });
        }

        // 9. Activity Logs
        if (!Schema::hasTable('activity_logs')) {
            Schema::create('activity_logs', function (Blueprint $table) {
                $table->id();
                $table->foreignId('organization_id')->nullable()->constrained()->cascadeOnDelete();
                $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
                $table->string('action'); // create, update, delete, view_credential
                $table->nullableMorphs('subject'); // The item changed
                $table->text('description')->nullable();
                $table->json('properties')->nullable(); // Old/New values
                $table->string('ip_address')->nullable();
                $table->timestamps();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Reverse order
        Schema::dropIfExists('activity_logs');
        Schema::dropIfExists('relationships');
        Schema::dropIfExists('taggables');
        Schema::dropIfExists('tags');
        Schema::dropIfExists('document_versions');
        Schema::dropIfExists('documents');
        Schema::dropIfExists('folders');
        Schema::dropIfExists('credentials');
        Schema::dropIfExists('asset_values');
        Schema::dropIfExists('asset_custom_fields');
        Schema::dropIfExists('assets');
        Schema::dropIfExists('asset_types');
        Schema::dropIfExists('organization_user');
        Schema::dropIfExists('permission_role');
        Schema::dropIfExists('permissions');
        Schema::dropIfExists('roles');
        Schema::dropIfExists('organizations');
    }
};
