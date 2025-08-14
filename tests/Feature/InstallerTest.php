<?php

namespace CodeHive\LaravelWebInstaller\Tests\Feature;

use CodeHive\LaravelWebInstaller\Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class InstallerTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_can_access_installer_route()
    {
        $response = $this->get(route('installer'));

        $response->assertStatus(200);
    }

    /** @test */
    public function it_redirects_when_already_installed()
    {
        // Create installed marker
        file_put_contents(storage_path('installed'), 'installed');

        $response = $this->get(route('installer'));

        $response->assertRedirect();

        // Clean up
        unlink(storage_path('installed'));
    }

    /** @test */
    public function it_can_access_success_page_when_installed()
    {
        // Create installed marker
        file_put_contents(storage_path('installed'), 'installed');

        $response = $this->get(route('installer.success'));

        $response->assertStatus(200);

        // Clean up
        unlink(storage_path('installed'));
    }
}