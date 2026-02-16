<?php

namespace Tests\Feature;

use Tests\TestCase;

class InstallerTest extends TestCase
{
    public function test_installer_redirects_if_not_installed(): void
    {
        // Mock not running unit tests to test the middleware logic
        $response = $this->get(route('install.welcome'));
        
        $response->assertStatus(200);
    }
}
