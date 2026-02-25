<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Organization;
use App\Models\Document;
use App\Models\Role;
use App\Models\Permission;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\UploadedFile;
use Tests\TestCase;

class DocumentDeletionTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutMiddleware(\App\Http\Middleware\VerifyCsrfToken::class);

        // Manual setup for robustness
        $adminRole = Role::firstOrCreate(['slug' => 'admin'], ['name' => 'Admin']);
        $deletePerm = Permission::firstOrCreate(
        ['slug' => 'document.delete'],
        ['name' => 'Delete Document', 'module' => 'document']
        );
        $adminRole->permissions()->syncWithoutDetaching([$deletePerm->id]);
    }

    public function test_super_admin_can_delete_document(): void
    {
        $superAdmin = User::factory()->create(['is_super_admin' => true]);
        $org = Organization::create(['name' => 'Org 1', 'slug' => 'org-1', 'status' => 'active']);
        $doc = Document::create(['title' => 'Doc 1', 'organization_id' => $org->id]);

        $response = $this->actingAs($superAdmin)->delete("/org-1/documents/{$doc->id}");
        $response->assertStatus(302);
        $this->assertDatabaseMissing('documents', ['id' => $doc->id]);
    }

    public function test_org_admin_can_delete_document(): void
    {
        $adminRole = Role::where('slug', 'admin')->first();
        $user = User::factory()->create();
        $org = Organization::create(['name' => 'Org 2', 'slug' => 'org-2', 'status' => 'active']);
        $user->organizations()->attach($org->id, ['role_id' => $adminRole->id]);
        $user->refresh();

        $doc = Document::create(['title' => 'Doc 2', 'organization_id' => $org->id]);

        // This will print the trace if it fails
        $this->withoutExceptionHandling();

        $response = $this->actingAs($user)->delete("/org-2/documents/{$doc->id}");

        $response->assertStatus(302);
        $this->assertDatabaseMissing('documents', ['id' => $doc->id]);
    }

    public function test_regular_user_cannot_delete_document(): void
    {
        $user = User::factory()->create();
        $org = Organization::create(['name' => 'Org 3', 'slug' => 'org-3', 'status' => 'active']);

        $doc = Document::create(['title' => 'Doc 3', 'organization_id' => $org->id]);

        $response = $this->actingAs($user)->delete("/org-3/documents/{$doc->id}");
        $response->assertStatus(403);
    }
}
