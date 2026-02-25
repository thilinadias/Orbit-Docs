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
        Schema::create('organizations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('parent_id')->nullable()->constrained('organizations')->onDelete('cascade');
            $table->string('name');
            $table->string('legal_name')->nullable();
            $table->string('slug')->unique();
            $table->string('logo')->nullable();
            $table->string('reg_number')->nullable();
            $table->string('tax_id')->nullable();
            $table->string('primary_email')->nullable();
            $table->string('website')->nullable();
            $table->string('phone')->nullable();
            $table->string('status')->default('active');
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        // 2. Roles, Permissions & RBAC
        Schema::create('roles', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('description')->nullable();
            $table->timestamps();
        });

        Schema::create('permissions', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('module');
            $table->timestamps();
        });

        Schema::create('role_permission', function (Blueprint $table) {
            $table->id();
            $table->foreignId('role_id')->constrained()->onDelete('cascade');
            $table->foreignId('permission_id')->constrained()->onDelete('cascade');
            $table->timestamps();
            $table->unique(['role_id', 'permission_id']);
        });

        Schema::create('organization_user', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('organization_id')->constrained()->onDelete('cascade');
            $table->foreignId('role_id')->nullable()->constrained()->onDelete('set null');
            $table->boolean('is_primary')->default(false);
            $table->json('permissions')->nullable();
            $table->timestamps();
            $table->unique(['user_id', 'organization_id']);
        });

        // 3. Teams
        Schema::create('teams', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->foreignId('organization_id')->constrained()->onDelete('cascade');
            $table->timestamps();
        });

        Schema::create('team_user', function (Blueprint $table) {
            $table->id();
            $table->foreignId('team_id')->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->timestamps();
            $table->unique(['team_id', 'user_id']);
        });

        // 4. Sites (Locations)
        Schema::create('sites', function (Blueprint $table) {
            $table->id();
            $table->foreignId('organization_id')->constrained()->onDelete('cascade');
            $table->string('name');
            $table->string('address')->nullable();
            $table->string('city')->nullable();
            $table->string('state')->nullable();
            $table->string('postcode')->nullable();
            $table->string('site_manager')->nullable();
            $table->string('internet_provider')->nullable();
            $table->string('circuit_id')->nullable();
            $table->string('alarm_code')->nullable();
            $table->text('after_hours_access')->nullable();
            $table->string('timezone')->nullable();
            $table->string('logo')->nullable();
            $table->string('status')->default('active');
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        // 5. Assets
        Schema::create('asset_types', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('icon')->nullable();
            $table->timestamps();
        });

        Schema::create('assets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('organization_id')->constrained()->onDelete('cascade');
            $table->foreignId('asset_type_id')->constrained()->onDelete('cascade');
            $table->foreignId('site_id')->nullable()->constrained()->onDelete('set null');
            $table->string('name');
            $table->string('asset_tag')->nullable();
            $table->string('serial_number')->nullable();
            $table->string('manufacturer')->nullable();
            $table->string('model')->nullable();
            $table->date('purchase_date')->nullable();
            $table->date('warranty_expiration_date')->nullable();
            $table->date('end_of_life')->nullable();
            $table->string('status')->default('active');
            $table->string('assigned_to')->nullable();
            $table->string('ip_address')->nullable();
            $table->string('mac_address')->nullable();
            $table->string('os_version')->nullable();
            $table->boolean('monitoring_enabled')->default(false);
            $table->boolean('rmm_agent_installed')->default(false);
            $table->text('notes')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamps();
        });

        Schema::create('asset_custom_fields', function (Blueprint $table) {
            $table->id();
            $table->foreignId('asset_type_id')->constrained()->onDelete('cascade');
            $table->string('name');
            $table->string('field_type')->default('text');
            $table->timestamps();
        });

        Schema::create('asset_values', function (Blueprint $table) {
            $table->id();
            $table->foreignId('asset_id')->constrained()->onDelete('cascade');
            $table->foreignId('asset_custom_field_id')->constrained()->onDelete('cascade');
            $table->text('value')->nullable();
            $table->timestamps();
        });

        // 6. Credentials
        Schema::create('credentials', function (Blueprint $table) {
            $table->id();
            $table->foreignId('organization_id')->constrained()->onDelete('cascade');
            $table->foreignId('asset_id')->nullable()->constrained()->onDelete('set null');
            $table->foreignId('user_id')->nullable()->constrained()->onDelete('set null');
            $table->string('title');
            $table->string('category')->nullable();
            $table->string('username')->nullable();
            $table->text('encrypted_password');
            $table->text('encrypted_2fa_secret')->nullable();
            $table->string('url')->nullable();
            $table->date('expiry_date')->nullable();
            $table->date('last_rotated_at')->nullable();
            $table->boolean('auto_rotate')->default(false);
            $table->string('visibility')->default('org');
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        Schema::create('credential_access_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('credential_id')->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('action'); // view, reveal, copy
            $table->string('ip_address')->nullable();
            $table->timestamps();
        });

        // 7. Documentation
        Schema::create('folders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('organization_id')->constrained()->onDelete('cascade');
            $table->foreignId('parent_id')->nullable()->constrained('folders')->onDelete('cascade');
            $table->string('name');
            $table->timestamps();
        });

        Schema::create('documents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('organization_id')->constrained()->onDelete('cascade');
            $table->foreignId('folder_id')->nullable()->constrained()->onDelete('set null');
            $table->foreignId('user_id')->nullable()->constrained()->onDelete('set null');
            $table->string('title');
            $table->longText('content')->nullable();
            $table->boolean('is_public')->default(false);
            $table->string('approval_status')->default('draft');
            $table->string('visibility')->default('org');
            $table->boolean('is_upload')->default(false);
            $table->string('file_path')->nullable();
            $table->string('file_name')->nullable();
            $table->string('mime_type')->nullable();
            $table->unsignedBigInteger('file_size')->nullable();
            $table->nullableMorphs('documentable');
            $table->foreignId('last_modified_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamps();

            if (DB::getDriverName() === 'mysql') {
                $table->fullText(['title', 'content']);
            }
        });

        Schema::create('document_versions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('document_id')->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->nullable()->constrained()->onDelete('set null');
            $table->longText('content');
            $table->timestamps();
        });

        // 8. Tags & Taggables
        Schema::create('tags', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('color')->default('gray');
            $table->timestamps();
        });

        Schema::create('taggables', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tag_id')->constrained()->onDelete('cascade');
            $table->morphs('taggable');
        });

        // 9. Contacts
        Schema::create('contacts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('organization_id')->constrained()->onDelete('cascade');
            $table->string('first_name');
            $table->string('last_name')->nullable();
            $table->string('title')->nullable();
            $table->string('department')->nullable();
            $table->string('email')->nullable();
            $table->string('phone_office')->nullable();
            $table->string('phone_mobile')->nullable();
            $table->boolean('is_vip')->default(false);
            $table->boolean('is_primary')->default(false);
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        // 10. Relationships, Favorites, Settings
        Schema::create('relationships', function (Blueprint $table) {
            $table->id();
            $table->foreignId('organization_id')->constrained()->onDelete('cascade');
            $table->morphs('source');
            $table->morphs('target');
            $table->string('type')->nullable();
            $table->timestamps();
        });

        Schema::create('favorites', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->morphs('favoritable');
            $table->timestamps();
            $table->unique(['user_id', 'favoritable_id', 'favoritable_type']);
        });

        Schema::create('settings', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique();
            $table->text('value')->nullable();
            $table->timestamps();
        });

        // 11. Activity Logs
        Schema::create('activity_logs', function (Blueprint $table) {
            $table->id();
            $table->string('log_name')->nullable();
            $table->foreignId('organization_id')->nullable()->constrained()->onDelete('set null');
            $table->foreignId('user_id')->nullable()->constrained()->onDelete('set null');
            $table->nullableMorphs('subject');
            $table->string('action');
            $table->text('description')->nullable();
            $table->json('properties')->nullable();
            $table->json('old_values')->nullable();
            $table->json('new_values')->nullable();
            $table->string('ip_address')->nullable();
            $table->string('user_agent')->nullable();
            $table->timestamps();
        });

        // 12. Object Specific Permissions (Overrides)
        Schema::create('object_permissions', function (Blueprint $table) {
            $table->id();
            $table->string('entity_type');
            $table->unsignedBigInteger('entity_id');
            $table->foreignId('user_id')->nullable()->constrained()->onDelete('cascade');
            $table->string('permission_type');
            $table->boolean('is_allowed')->default(true);
            $table->timestamps();
            $table->index(['entity_type', 'entity_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('object_permissions');
        Schema::dropIfExists('activity_logs');
        Schema::dropIfExists('settings');
        Schema::dropIfExists('favorites');
        Schema::dropIfExists('relationships');
        Schema::dropIfExists('contacts');
        Schema::dropIfExists('taggables');
        Schema::dropIfExists('tags');
        Schema::dropIfExists('document_versions');
        Schema::dropIfExists('documents');
        Schema::dropIfExists('folders');
        Schema::dropIfExists('credential_access_logs');
        Schema::dropIfExists('credentials');
        Schema::dropIfExists('asset_values');
        Schema::dropIfExists('asset_custom_fields');
        Schema::dropIfExists('assets');
        Schema::dropIfExists('asset_types');
        Schema::dropIfExists('sites');
        Schema::dropIfExists('team_user');
        Schema::dropIfExists('teams');
        Schema::dropIfExists('organization_user');
        Schema::dropIfExists('role_permission');
        Schema::dropIfExists('permissions');
        Schema::dropIfExists('roles');
        Schema::dropIfExists('organizations');
    }
};
