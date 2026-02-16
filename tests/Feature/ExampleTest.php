<?php

namespace Tests\Feature;

// use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExampleTest extends TestCase
{
    /**
     * A basic test example.
     */
    public function test_the_application_redirects_to_installer_if_not_installed(): void
    {
        // Ensure no installed file exists
        if (file_exists(storage_path('app/installed'))) {
            unlink(storage_path('app/installed'));
        }

        $response = $this->get('/');

        $response->assertRedirect(route('install.welcome'));
    }

    public function test_the_application_returns_successful_response_if_installed(): void
    {
        // Create installed file
        file_put_contents(storage_path('app/installed'), 'TEST INSTALLED');

        $response = $this->get('/');

        $response->assertStatus(200);

        // Cleanup
        if (file_exists(storage_path('app/installed'))) {
            unlink(storage_path('app/installed'));
        }
    }
}
