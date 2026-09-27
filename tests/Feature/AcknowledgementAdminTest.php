<?php

namespace Tests\Feature;

use App\Filament\Admin\Resources\Acknowledgements\AcknowledgementResource;
use App\Filament\Admin\Resources\Acknowledgements\Pages\CreateAcknowledgement;
use App\Filament\Admin\Resources\Acknowledgements\Pages\EditAcknowledgement;
use App\Filament\Admin\Resources\Acknowledgements\Pages\ListAcknowledgements;
use App\Models\Acknowledgement;
use App\Models\User;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class AcknowledgementAdminTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutVite();
        Filament::setCurrentPanel(Filament::getPanel('admin'));
    }

    public function test_hosts_can_access_acknowledgements_but_ordinary_users_cannot(): void
    {
        $host = User::factory()->create(['role' => 2]);
        $user = User::factory()->create(['role' => 0]);

        $this->actingAs($host);
        $this->assertTrue(AcknowledgementResource::canAccess());

        $this->actingAs($user);
        $this->assertFalse(AcknowledgementResource::canAccess());
        $this->get('/dashboard/acknowledgements')->assertRedirect('/');
    }

    public function test_list_is_empty_when_the_selected_year_has_no_acknowledgements(): void
    {
        $host = User::factory()->create(['role' => 2]);
        $acknowledgement = $this->createAcknowledgement(2025);

        session(['selected-year-filter' => app('current-year')]);

        Livewire::actingAs($host)
            ->test(ListAcknowledgements::class)
            ->assertCanNotSeeTableRecords([$acknowledgement]);
    }

    public function test_list_refreshes_when_the_selected_year_changes(): void
    {
        $host = User::factory()->create(['role' => 2]);
        $acknowledgement = $this->createAcknowledgement(2025);

        session(['selected-year-filter' => app('current-year')]);

        $component = Livewire::actingAs($host)
            ->test(ListAcknowledgements::class)
            ->assertCanNotSeeTableRecords([$acknowledgement]);

        session(['selected-year-filter' => 2025]);

        $component
            ->dispatch('filter-year-updated')
            ->assertCanSeeTableRecords([$acknowledgement]);
    }

    public function test_host_can_create_an_acknowledgement_with_json_content(): void
    {
        $host = User::factory()->create(['role' => 2]);
        session(['selected-year-filter' => 2025]);

        Livewire::actingAs($host)
            ->test(CreateAcknowledgement::class)
            ->fillForm([
                'title' => 'Production Team',
                'subtitle' => 'Best Animation',
                'year' => 2025,
                'order' => 1,
                'content' => [
                    'english' => 'Thank you.',
                    'japanese' => 'ありがとうございます。',
                    'extra' => 'Translated by the awards team.',
                ],
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $acknowledgement = Acknowledgement::query()
            ->where('title', 'Production Team')
            ->firstOrFail();

        $this->assertSame(2025, $acknowledgement->year);
        $this->assertSame([
            'english' => 'Thank you.',
            'japanese' => 'ありがとうございます。',
            'extra' => 'Translated by the awards team.',
        ], $acknowledgement->content);
    }

    public function test_host_can_edit_acknowledgement_json_content(): void
    {
        $host = User::factory()->create(['role' => 2]);
        $acknowledgement = $this->createAcknowledgement(2025);

        Livewire::actingAs($host)
            ->test(EditAcknowledgement::class, ['record' => $acknowledgement->getRouteKey()])
            ->fillForm([
                'title' => 'Updated Production Team',
                'subtitle' => 'Updated subtitle',
                'year' => 2025,
                'order' => 2,
                'content' => [
                    'english' => 'Updated English text.',
                    'japanese' => '更新されたテキスト。',
                    'extra' => 'Updated extra text.',
                ],
            ])
            ->call('save')
            ->assertHasNoFormErrors();

        $acknowledgement->refresh();

        $this->assertSame('Updated Production Team', $acknowledgement->title);
        $this->assertSame(2, $acknowledgement->order);
        $this->assertSame([
            'english' => 'Updated English text.',
            'japanese' => '更新されたテキスト。',
            'extra' => 'Updated extra text.',
        ], $acknowledgement->content);
    }

    private function createAcknowledgement(int $year): Acknowledgement
    {
        return Acknowledgement::query()->create([
            'title' => 'Existing acknowledgement',
            'subtitle' => '',
            'year' => $year,
            'order' => 0,
            'content' => [
                'english' => 'Existing English text.',
            ],
        ]);
    }
}
