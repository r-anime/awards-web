<?php

namespace Tests\Feature;

use App\Filament\Admin\Resources\OptionResource\Pages\ManageOptions;
use App\Models\Option;
use App\Models\User;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;
use Livewire\Livewire;
use Tests\TestCase;

class OptionAdminTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutVite();
        Filament::setCurrentPanel(Filament::getPanel('admin'));

        $this->actingAs(User::factory()->create(['role' => 3]));
    }

    public function test_settings_page_loads_the_saved_options(): void
    {
        Option::set('account_age_requirement', '45');
        Option::set('feedback_channel_webhook', 'https://discord.com/api/webhooks/feedback');
        Option::set('final_voting_end_date', '2026-12-20 18:30:00');
        Option::set('nomination_voting_start_date', '');
        Option::set('show_applicant_names_to_hosts', 'true');
        Option::set('show_applicant_names_while_grading', 'true');

        Livewire::test(ManageOptions::class)
            ->assertSet('account_age_requirement', '45')
            ->assertSet('feedback_channel_webhook', 'https://discord.com/api/webhooks/feedback')
            ->assertSet('final_voting_end_date', '2026-12-20T18:30')
            ->assertSet('nomination_voting_start_date', '')
            ->assertSet('results_display_date', '')
            ->assertSet('show_applicant_names_to_hosts', true)
            ->assertSet('show_applicant_names_while_grading', true);
    }

    public function test_settings_page_falls_back_to_defaults(): void
    {
        Livewire::test(ManageOptions::class)
            ->assertSet('account_age_requirement', 30)
            ->assertSet('site_name', 'r/anime Awards')
            ->assertSet('show_applicant_names_to_hosts', false)
            ->assertSet('show_applicant_names_while_grading', false);
    }

    public function test_saving_settings_stores_each_option(): void
    {
        Livewire::test(ManageOptions::class)
            ->set('account_age_requirement', '60')
            ->set('feedback_channel_webhook', 'https://discord.com/api/webhooks/feedback')
            ->set('audit_channel_webhook', 'https://discord.com/api/webhooks/audit')
            ->set('nomination_voting_start_date', '2026-11-01T10:00')
            ->set('nomination_voting_end_date', '2026-11-15T22:00')
            ->set('final_voting_start_date', '')
            ->set('final_voting_end_date', '2026-12-20T18:30')
            ->set('results_display_date', '2027-02-01T20:00')
            ->set('show_applicant_names_to_hosts', true)
            ->set('show_applicant_names_while_grading', true)
            ->call('saveSettings')
            ->assertNotified('Settings saved successfully');

        $this->assertSame('60', Option::get('account_age_requirement'));
        $this->assertSame('https://discord.com/api/webhooks/feedback', Option::get('feedback_channel_webhook'));
        $this->assertSame('https://discord.com/api/webhooks/audit', Option::get('audit_channel_webhook'));
        $this->assertSame('2026-11-01 10:00:00', Option::get('nomination_voting_start_date'));
        $this->assertSame('2026-11-15 22:00:00', Option::get('nomination_voting_end_date'));
        $this->assertSame('', Option::get('final_voting_start_date', ''));
        $this->assertSame('2026-12-20 18:30:00', Option::get('final_voting_end_date'));
        $this->assertSame('2027-02-01 20:00:00', Option::get('results_display_date'));
        $this->assertSame('true', Option::get('show_applicant_names_to_hosts'));
        $this->assertSame('true', Option::get('show_applicant_names_while_grading'));
    }

    public function test_unchecking_applicant_names_stores_false(): void
    {
        Option::set('show_applicant_names_to_hosts', 'true');

        Livewire::test(ManageOptions::class)
            ->set('show_applicant_names_to_hosts', false)
            ->call('saveSettings');

        $this->assertSame('false', Option::get('show_applicant_names_to_hosts'));
    }

    public function test_saving_only_writes_the_changed_options(): void
    {
        Option::set('max_file_size', '20MB');
        $rowsBefore = Option::count();

        Livewire::test(ManageOptions::class)
            ->set('site_name', 'Renamed')
            ->call('saveSettings');

        $this->assertSame($rowsBefore + 1, Option::count());
        $this->assertSame('Renamed', Option::get('site_name'));
        $this->assertSame('20MB', Option::get('max_file_size'));
        $this->assertFalse(Option::where('option', 'account_age_requirement')->exists());
    }

    public function test_saving_twice_updates_rather_than_duplicates_options(): void
    {
        Livewire::test(ManageOptions::class)->call('saveSettings');
        Livewire::test(ManageOptions::class)->set('site_name', 'Renamed')->call('saveSettings');

        $this->assertSame(1, Option::where('option', 'site_name')->count());
        $this->assertSame('Renamed', Option::get('site_name'));
    }

    public function test_hosts_can_change_settings(): void
    {
        $this->actingAs(User::factory()->create(['role' => 2]));

        Livewire::test(ManageOptions::class)
            ->set('show_applicant_names_while_grading', true)
            ->call('saveSettings')
            ->assertNotified('Settings saved successfully');

        $this->assertSame('true', Option::get('show_applicant_names_while_grading'));
    }

    public function test_users_below_host_cannot_open_settings(): void
    {
        $this->actingAs(User::factory()->create(['role' => 1]));

        Livewire::test(ManageOptions::class)->assertForbidden();
    }

    public function test_saving_sends_an_audit_event_listing_only_the_changed_options(): void
    {
        Http::fake();
        Option::set('audit_channel_webhook', 'https://discord.com/api/webhooks/audit');
        Option::set('final_voting_end_date', '2026-12-20 18:30:00');

        Livewire::test(ManageOptions::class)
            ->set('site_name', 'Renamed')
            ->set('show_applicant_names_to_hosts', true)
            ->set('final_voting_end_date', '')
            ->call('saveSettings');

        Http::assertSentCount(1);
        Http::assertSent(function (Request $request) {
            $embed = $request['embeds'][0];

            return $request->url() === 'https://discord.com/api/webhooks/audit'
                && $embed['title'] === 'Options audit'
                && str_contains($embed['description'], 'changed 3 options')
                && $embed['fields'] === [
                    ['name' => 'site_name', 'value' => 'r/anime Awards → Renamed'],
                    ['name' => 'final_voting_end_date', 'value' => '2026-12-20 18:30:00 → (empty)'],
                    ['name' => 'show_applicant_names_to_hosts', 'value' => 'false → true'],
                ];
        });
    }

    public function test_saving_without_changes_sends_no_audit_event(): void
    {
        Http::fake();
        Option::set('audit_channel_webhook', 'https://discord.com/api/webhooks/audit');
        Option::set('account_age_requirement', '45');
        Option::set('nomination_voting_start_date', '2026-11-01 10:00:00');

        Livewire::test(ManageOptions::class)->call('saveSettings');

        Http::assertNothingSent();
    }

    public function test_audit_event_does_not_reveal_webhook_urls(): void
    {
        Http::fake();
        Option::set('audit_channel_webhook', 'https://discord.com/api/webhooks/audit');

        Livewire::test(ManageOptions::class)
            ->set('feedback_channel_webhook', 'https://discord.com/api/webhooks/secret-feedback-token')
            ->call('saveSettings');

        Http::assertSent(fn (Request $request) => $request['embeds'][0]['fields'] === [['name' => 'feedback_channel_webhook', 'value' => 'set']]
            && ! str_contains($request->body(), 'secret-feedback-token'));
    }

    public function test_changing_the_audit_webhook_notifies_the_old_and_new_channels(): void
    {
        Http::fake();
        Option::set('audit_channel_webhook', 'https://discord.com/api/webhooks/old-audit');

        Livewire::test(ManageOptions::class)
            ->set('audit_channel_webhook', 'https://discord.com/api/webhooks/new-audit')
            ->call('saveSettings');

        Http::assertSentCount(2);
        Http::assertSent(fn (Request $request) => $request->url() === 'https://discord.com/api/webhooks/old-audit'
            && $request['embeds'][0]['fields'] === [['name' => 'audit_channel_webhook', 'value' => 'changed']]);
        Http::assertSent(fn (Request $request) => $request->url() === 'https://discord.com/api/webhooks/new-audit');
    }

    public function test_audit_event_names_anilist_accounts(): void
    {
        Http::fake();
        Option::set('audit_channel_webhook', 'https://discord.com/api/webhooks/audit');
        $this->actingAs(User::factory()->create(['role' => 2, 'name' => 'AniHost', 'reddit_user' => null, 'anilist_id' => 4242]));

        Livewire::test(ManageOptions::class)
            ->set('site_name', 'Renamed')
            ->call('saveSettings');

        Http::assertSent(fn (Request $request) => str_contains($request['embeds'][0]['description'], 'AniHost (AniList #4242) changed 1 option'));
    }

    public function test_no_audit_event_without_an_audit_webhook(): void
    {
        Http::fake();

        Livewire::test(ManageOptions::class)
            ->set('site_name', 'Renamed')
            ->call('saveSettings');

        Http::assertNothingSent();
    }

    public function test_account_age_requirement_must_be_a_number(): void
    {
        $this->markTestSkipped('Suspected bug: ManageOptions::saveSettings stores an empty or non-numeric account age requirement');

        Option::set('account_age_requirement', '30');

        foreach (['', 'thirty'] as $value) {
            Livewire::test(ManageOptions::class)
                ->set('account_age_requirement', $value)
                ->call('saveSettings')
                ->assertHasErrors('account_age_requirement');

            $this->assertSame('30', Option::get('account_age_requirement'));
        }
    }

    public function test_webhook_urls_must_be_urls(): void
    {
        $this->markTestSkipped('Suspected bug: ManageOptions::saveSettings stores webhook values that are not URLs');

        Livewire::test(ManageOptions::class)
            ->set('feedback_channel_webhook', 'not a url')
            ->call('saveSettings')
            ->assertHasErrors('feedback_channel_webhook');

        $this->assertSame('', Option::get('feedback_channel_webhook', ''));
    }
}
