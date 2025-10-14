<x-filament-panels::page>
    <div class="fi-page-content p-6">
        <div class="fi-section">
            <div class="fi-section-content">                
                @if(auth()->user()->role === -1)
                <div class="fi-card fi-card-color-danger mt-4">
                    <div class="fi-card-body p-6">
                        <div class="flex items-center justify-center mb-4">
                            <div class="fi-icon fi-icon-color-danger-500 fi-icon-size-lg mr-3">
                                <svg class="fi-icon-svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" style="width: 2rem; height: 2rem;">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z" />
                                </svg>
                            </div>
                            <h3 class="fi-section-header-heading text-danger-600 dark:text-danger-400 mt-4">
                                Account Age Restriction
                            </h3>
                        </div>
                        <p class="fi-section-header-description text-center text-gray-700 dark:text-gray-300 mt-2">
                            Your Reddit account is too young to participate in the awards. Your account must be at least 
                            <strong>{{ \App\Models\Option::get('account_age_requirement', 30) }} days old</strong> to access the full system.
                        </p>
                        <p class="fi-section-header-description text-center text-gray-600 dark:text-gray-400 mt-2">
                            You may contact a host or site administrator to request an exception.
                        </p>
                    </div>
                </div>
                @endif
                
                @if(auth()->user()->role >= 2)
                <div class="fi-card fi-card-color-primary mt-4">
                    <div class="fi-card-body p-6">
                        <h3 class="fi-section-header-heading has-text-centered mb-4">
                            Host Access Available
                        </h3>
                        <p class="fi-section-header-description has-text-centered">
                            You have host privileges. You can access the full admin panel to manage awards, entries, and results.
                        </p>
                    </div>
                </div>
                @elseif(auth()->user()->role !== -1)
                <div class="fi-card fi-card-color-warning mt-4">
                    <div class="fi-card-body p-6">
                        <h3 class="fi-section-header-heading has-text-centered mb-4">
                            Limited Access
                        </h3>
                        <p class="fi-section-header-description has-text-centered">
                            Congratulations! You found the dashboard page. Contact an administrator if you need additional permissions.
                        </p>
                    </div>
                </div>
                @endif
            </div>
        </div>
    </div>
</x-filament-panels::page>
