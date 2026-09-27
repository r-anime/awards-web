<?php

namespace Tests\Feature;

use Database\Seeders\DevelopmentAcknowledgementsSeeder;
use Database\Seeders\DevelopmentResultsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DevelopmentContentSeederTest extends TestCase
{
    use RefreshDatabase;

    public function test_bundled_archives_populate_public_content_routes(): void
    {
        $this->withoutVite();

        $this->seed([
            DevelopmentResultsSeeder::class,
            DevelopmentAcknowledgementsSeeder::class,
        ]);

        $this->assertDatabaseCount('categories', 19);
        $this->assertDatabaseCount('results', 190);
        $this->assertDatabaseHas('acknowledgements', ['year' => 2024]);

        $this->get('/results')
            ->assertRedirect('/results/2024');
        $this->get('/results/2024')
            ->assertOk();
        $this->get('/acknowledgements')
            ->assertRedirect('/acknowledgements/2025');
        $this->get('/acknowledgements/2024')
            ->assertOk();
    }
}
