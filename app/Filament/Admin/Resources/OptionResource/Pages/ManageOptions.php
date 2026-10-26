<?php

namespace App\Filament\Admin\Resources\OptionResource\Pages;

use App\Filament\Admin\Resources\OptionResource;
use App\Jobs\SendAuditWebhook;
use App\Models\Option;
use Carbon\Carbon;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\Page;
use Illuminate\Support\Str;

class ManageOptions extends Page
{
    protected static string $resource = OptionResource::class;

    protected string $view = 'filament.admin.resources.option-resource.pages.manage-options';

    // What this page shows for options that have never been saved. Also what a save is compared
    // against, so leaving an unsaved option at its fallback doesn't count as a change
    private const DEFAULTS = [
        'account_age_requirement' => 30,
        'site_name' => 'r/anime Awards',
        'maintenance_mode' => 'false',
        'max_file_size' => '10MB',
        'feedback_channel_webhook' => '',
        'audit_channel_webhook' => '',
        'nomination_voting_start_date' => '',
        'nomination_voting_end_date' => '',
        'final_voting_start_date' => '',
        'final_voting_end_date' => '',
        'results_display_date' => '',
        'show_applicant_names_to_hosts' => 'false',
        'show_applicant_names_while_grading' => 'false',
    ];

    public $account_age_requirement;

    public $site_name;

    public $maintenance_mode;

    public $max_file_size;

    public $feedback_channel_webhook;

    public $audit_channel_webhook;

    public $nomination_voting_start_date;

    public $nomination_voting_end_date;

    public $final_voting_start_date;

    public $final_voting_end_date;

    public $results_display_date;

    public $show_applicant_names_to_hosts;

    public $show_applicant_names_while_grading;

    public static function canAccess(array $parameters = []): bool
    {
        return auth()->check() && auth()->user()->role >= 2;
    }

    public function mount(): void
    {
        $saved = Option::getMany(self::DEFAULTS);

        $this->account_age_requirement = $saved['account_age_requirement'];
        $this->site_name = $saved['site_name'];
        $this->maintenance_mode = $saved['maintenance_mode'];
        $this->max_file_size = $saved['max_file_size'];
        $this->feedback_channel_webhook = $saved['feedback_channel_webhook'];
        $this->audit_channel_webhook = $saved['audit_channel_webhook'];

        $this->nomination_voting_start_date = $this->toInputDate($saved['nomination_voting_start_date']);
        $this->nomination_voting_end_date = $this->toInputDate($saved['nomination_voting_end_date']);
        $this->final_voting_start_date = $this->toInputDate($saved['final_voting_start_date']);
        $this->final_voting_end_date = $this->toInputDate($saved['final_voting_end_date']);
        $this->results_display_date = $this->toInputDate($saved['results_display_date']);

        $this->show_applicant_names_to_hosts = $saved['show_applicant_names_to_hosts'] === 'true';
        $this->show_applicant_names_while_grading = $saved['show_applicant_names_while_grading'] === 'true';
    }

    public function saveSettings(): void
    {
        // Every option in the format it's stored in, so it can be compared with the saved value
        $newValues = [
            'account_age_requirement' => $this->account_age_requirement,
            'site_name' => $this->site_name,
            'maintenance_mode' => $this->maintenance_mode,
            'max_file_size' => $this->max_file_size,
            'feedback_channel_webhook' => $this->feedback_channel_webhook,
            'audit_channel_webhook' => $this->audit_channel_webhook,
            'nomination_voting_start_date' => $this->toStoredDate($this->nomination_voting_start_date),
            'nomination_voting_end_date' => $this->toStoredDate($this->nomination_voting_end_date),
            'final_voting_start_date' => $this->toStoredDate($this->final_voting_start_date),
            'final_voting_end_date' => $this->toStoredDate($this->final_voting_end_date),
            'results_display_date' => $this->toStoredDate($this->results_display_date),
            'show_applicant_names_to_hosts' => $this->show_applicant_names_to_hosts ? 'true' : 'false',
            'show_applicant_names_while_grading' => $this->show_applicant_names_while_grading ? 'true' : 'false',
        ];

        $saved = Option::getMany(self::DEFAULTS);

        $changes = [];
        foreach ($newValues as $option => $value) {
            if ((string) $saved[$option] !== (string) $value) {
                $changes[$option] = [$saved[$option], $value];
            }
        }

        // Only the changed options are written
        Option::setMany(array_map(fn (array $change) => $change[1], $changes));

        // The old audit webhook hears about a change to itself as well as the new one
        $this->sendAuditEvent($changes, [$saved['audit_channel_webhook'], $newValues['audit_channel_webhook']]);

        Notification::make()
            ->title('Settings saved successfully')
            ->success()
            ->send();
    }

    public function getTitle(): string
    {
        return 'Application Settings';
    }

    // Stored Y-m-d H:i:s => the datetime-local input's Y-m-d\TH:i
    private function toInputDate(?string $stored): string
    {
        return $stored ? Carbon::parse($stored)->format('Y-m-d\TH:i') : '';
    }

    // The datetime-local input's value => stored Y-m-d H:i:s, or '' when cleared
    private function toStoredDate(?string $input): string
    {
        return $input ? Carbon::parse($input)->format('Y-m-d H:i:s') : '';
    }

    /**
     * Posts one embed listing every changed option (old → new) to the audit webhook. If the webhook
     * itself changed, the old and the new channel both get it.
     *
     * @param  array<string, array{0: mixed, 1: mixed}>  $changes  option => [old value, new value]
     * @param  array<int, string|null>  $webhookUrls
     */
    private function sendAuditEvent(array $changes, array $webhookUrls): void
    {
        $webhookUrls = array_unique(array_filter($webhookUrls));

        if ($changes === [] || $webhookUrls === []) {
            return;
        }

        $actor = auth()->user();
        $account = $actor->reddit_user ?? ($actor->anilist_id ? 'AniList #'.$actor->anilist_id : 'n/a');
        $actorStr = sprintf('#%d %s (%s)', $actor->id, $actor->name ?? $actor->reddit_user ?? 'Unknown', $account);

        $embed = [
            'title' => 'Options audit',
            'description' => $actorStr.' changed '.count($changes).' '.Str::plural('option', count($changes)),
            'color' => 0xFFA500,
            'fields' => collect($changes)
                ->map(fn (array $change, string $option) => [
                    'name' => $option,
                    'value' => $this->describeChange($option, $change[0], $change[1]),
                ])
                ->values()
                ->all(),
            'timestamp' => now()->toIso8601String(),
        ];

        foreach ($webhookUrls as $webhookUrl) {
            try {
                SendAuditWebhook::dispatchSync($webhookUrl, ['embeds' => [$embed]]);
            } catch (\Throwable $e) {
                // Intentionally ignore webhook errors so saving settings isn't affected
            }
        }
    }

    // One audit field's value. Webhook URLs only say set/changed/cleared: anyone who sees the URL
    // can post to that channel. Other values are cut short to stay inside Discord's embed limits
    private function describeChange(string $option, mixed $old, mixed $new): string
    {
        if (str_ends_with($option, '_webhook')) {
            return match (true) {
                blank($old) => 'set',
                blank($new) => 'cleared',
                default => 'changed',
            };
        }

        $show = fn ($value) => blank($value) ? '(empty)' : Str::limit((string) $value, 200);

        return $show($old).' → '.$show($new);
    }
}
