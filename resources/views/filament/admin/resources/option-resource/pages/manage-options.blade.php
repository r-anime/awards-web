<x-filament-panels::page>
    <form wire:submit="saveSettings">
        <div class="fi-page-content p-6">
            <div class="fi-section space-y-6">
                <!-- Application Settings -->
                <div class="fi-section-content mb-2">
                    <div class="fi-card">
                        <div class="fi-card-header p-6 pb-0">
                            <h3 class="fi-section-header-heading text-lg font-semibold text-gray-950 dark:text-white">Application Settings</h3>
                            <p class="fi-section-header-description text-sm text-gray-500 dark:text-gray-400 mt-1">Configure general application settings</p>
                        </div>
                        <div class="fi-card-body p-6 pt-4">
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div class="fi-fo-field-wrp-label space-y-2">
                                    <label class="fi-fo-field-wrp-label-text inline-flex items-center gap-x-3">
                                        <span class="fi-fo-field-wrp-label-text-label text-sm font-medium text-gray-700 dark:text-gray-300">Account Age Requirement (days)</span>
                                    </label>
                                    <x-filament::input 
                                        type="number" 
                                        wire:model="account_age_requirement"
                                        placeholder="30"
                                        class="fi-input"
                                    />
                                    <p class="fi-section-header-description text-sm text-gray-500 dark:text-gray-400 mt-1">Minimum account age in days for user registration</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Discord Integration -->
                <div class="fi-section-content mb-2">
                    <div class="fi-card">
                        <div class="fi-card-header p-6 pb-0">
                            <h3 class="fi-section-header-heading text-lg font-semibold text-gray-950 dark:text-white">Discord Integration</h3>
                            <p class="fi-section-header-description text-sm text-gray-500 dark:text-gray-400 mt-1">Configure Discord webhook settings</p>
                        </div>
                        <div class="fi-card-body p-6 pt-4">
                            <div class="grid grid-cols-1 gap-6">
                                <div class="fi-fo-field-wrp-label space-y-2">
                                    <label class="fi-fo-field-wrp-label-text inline-flex items-center gap-x-3">
                                        <span class="fi-fo-field-wrp-label-text-label text-sm font-medium text-gray-700 dark:text-gray-300">Feedback Channel Webhook URL</span>
                                    </label>
                                    <x-filament::input 
                                        type="url" 
                                        wire:model="feedback_channel_webhook"
                                        placeholder="https://discord.com/api/webhooks/..."
                                        class="fi-input"
                                    />
                                    <p class="fi-section-header-description text-sm text-gray-500 dark:text-gray-400 mt-1">Discord webhook URL for feedback channel notifications</p>
                                </div>
                                
                                <div class="fi-fo-field-wrp-label space-y-2">
                                    <label class="fi-fo-field-wrp-label-text inline-flex items-center gap-x-3">
                                        <span class="fi-fo-field-wrp-label-text-label text-sm font-medium text-gray-700 dark:text-gray-300">Audit Channel Webhook URL</span>
                                    </label>
                                    <x-filament::input 
                                        type="url" 
                                        wire:model="audit_channel_webhook"
                                        placeholder="https://discord.com/api/webhooks/..."
                                        class="fi-input"
                                    />
                                    <p class="fi-section-header-description text-sm text-gray-500 dark:text-gray-400 mt-1">Discord webhook URL for audit channel notifications</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="mt-6 flex justify-end">
                <x-filament::button type="submit" color="success">
                    Save Settings
                </x-filament::button>
            </div>
        </div>
    </form>
</x-filament-panels::page>
