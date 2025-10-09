<x-filament-panels::page>
    <div class="fi-page-content">
        @php
            $application = $this->getApplication();
            $filterYear = session('selected-year-filter') ?? intval(date('Y'));
        @endphp

        @if(!$application)
            <div class="fi-section mb-2">
                <div class="fi-section-content">
                    <div class="fi-card">
                        <div class="fi-card-body has-text-centered p-4">
                            <div class="fi-icon fi-icon-color-gray-400 fi-icon-size-md mx-auto">
                                <svg class="fi-icon-svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" style="width: 3rem; height: 3rem;">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h3.75M9 15h3.75M9 18h3.75m3 .75H18a2.25 2.25 0 002.25-2.25V6.108c0-1.135-.845-2.098-1.976-2.192a48.424 48.424 0 00-1.123-.08m-5.801 0c-.065.21-.1.433-.1.664 0 .414.336.75.75.75h4.5a.75.75 0 00.75-.75 2.25 2.25 0 00-.1-.664m-5.8 0A2.251 2.251 0 0113.5 2.25H15c1.012 0 1.867.668 2.15 1.586m-5.8 0c-.376.023-.75.05-1.124.08C9.095 4.01 8.25 4.973 8.25 6.108V8.25m0 0H4.875c-.621 0-1.125.504-1.125 1.125v11.25c0 .621.504 1.125 1.125 1.125h9.75c.621 0 1.125-.504 1.125-1.125V9.375c0-.621-.504-1.125-1.125-1.125H8.25zM6.75 12h.008v.008H6.75V12zm0 3h.008v.008H6.75V15zm0 3h.008v.008H6.75V18z" />
                                </svg>
                            </div>
                            <h3 class="fi-section-header-heading mb-2">
                                No Application Form for {{ $filterYear }}
                            </h3>
                            <p class="fi-section-header-description mb-6">
                                Create an application form first to view grading data for the {{ $filterYear }} awards.
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        @else

            <div class="fi-section">
                <div class="fi-section-content">
                    <div class="fi-card">
                        <div class="fi-card-header px-6 py-5">
                            <h3 class="fi-section-header-heading">Application Grading Overview</h3>
                            <p class="fi-section-header-description">
                                View all applicants and their grades assigned by different scorers for each essay question.
                                @if(auth()->user()->role < 3)
                                    <br><span class="text-sm text-gray-500">Note: Usernames are hidden for privacy. Only user IDs and UUIDs are shown.</span>
                                @endif
                            </p>
                        </div>
                        <div class="fi-card-body px-6 py-6">
                            {{ $this->table }}
                        </div>
                    </div>
                </div>
            </div>
        @endif
    </div>
</x-filament-panels::page>
