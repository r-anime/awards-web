<?php

namespace Tests\Feature;

use App\Filament\Admin\Pages\JurorAllocations;
use App\Models\Option;
use App\Models\User;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class JurorAllocationsAccessTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutVite();
        Filament::setCurrentPanel(Filament::getPanel('admin'));
    }

    public function test_hosts_need_the_names_option(): void
    {
        $this->actingAs(User::factory()->create(['role' => 2]));

        Livewire::test(JurorAllocations::class)->assertForbidden();

        Option::set('show_applicant_names_to_hosts', 'true');

        Livewire::test(JurorAllocations::class)->assertOk();
    }

    public function test_users_below_host_are_refused_even_with_the_names_option(): void
    {
        Option::set('show_applicant_names_to_hosts', 'true');

        foreach ([-1, 0, 1] as $role) {
            $this->actingAs(User::factory()->create(['role' => $role]));

            Livewire::test(JurorAllocations::class)->assertForbidden();
        }
    }
}
