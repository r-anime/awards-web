<?php

namespace App\Filament\Admin\Resources\OptionResource\Pages;

use App\Filament\Admin\Resources\OptionResource;
use App\Models\Option;
use Filament\Actions;
use Filament\Resources\Pages\Page;
use Filament\Forms;
use Filament\Schemas\Schema;
use Filament\Notifications\Notification;

class ManageOptions extends Page
{
    protected static string $resource = OptionResource::class;

    protected string $view = 'filament.admin.resources.option-resource.pages.manage-options';

    public $account_age_requirement;
    public $site_name;
    public $maintenance_mode;
    public $max_file_size;
    public $feedback_channel_webhook;
    public $audit_channel_webhook;
    public $nomination_voting_start_date;
    public $nomination_voting_end_date;

    public static function canAccess(array $parameters = []): bool
    {
        return auth()->check() && auth()->user()->role >= 4;
    }

    public function mount(): void
    {
        $this->account_age_requirement = Option::get('account_age_requirement', 30);
        $this->site_name = Option::get('site_name', 'r/anime Awards');
        $this->maintenance_mode = Option::get('maintenance_mode', 'false');
        $this->max_file_size = Option::get('max_file_size', '10MB');
        $this->feedback_channel_webhook = Option::get('feedback_channel_webhook', '');
        $this->audit_channel_webhook = Option::get('audit_channel_webhook', '');
        
        // Parse dates for datetime-local input format (YYYY-MM-DDTHH:mm)
        $startDate = Option::get('nomination_voting_start_date', '');
        $endDate = Option::get('nomination_voting_end_date', '');
        $this->nomination_voting_start_date = $startDate ? 
            \Carbon\Carbon::parse($startDate)->format('Y-m-d\TH:i') : '';
        $this->nomination_voting_end_date = $endDate ? 
            \Carbon\Carbon::parse($endDate)->format('Y-m-d\TH:i') : '';
    }

    public function saveSettings(): void
    {
        // Save each option to the database
        Option::set('account_age_requirement', $this->account_age_requirement);
        Option::set('site_name', $this->site_name);
        Option::set('maintenance_mode', $this->maintenance_mode);
        Option::set('max_file_size', $this->max_file_size);
        Option::set('feedback_channel_webhook', $this->feedback_channel_webhook);
        Option::set('audit_channel_webhook', $this->audit_channel_webhook);
        
        // Format dates properly for storage
        $startDate = $this->nomination_voting_start_date ? 
            (\Carbon\Carbon::parse($this->nomination_voting_start_date)->format('Y-m-d H:i:s')) : '';
        $endDate = $this->nomination_voting_end_date ? 
            (\Carbon\Carbon::parse($this->nomination_voting_end_date)->format('Y-m-d H:i:s')) : '';
        
        Option::set('nomination_voting_start_date', $startDate);
        Option::set('nomination_voting_end_date', $endDate);

        Notification::make()
            ->title('Settings saved successfully')
            ->success()
            ->send();
    }


    public function getTitle(): string
    {
        return 'Application Settings';
    }
}
