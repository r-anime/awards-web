<x-filament-panels::page>
    <div class="fi-page-content">
        
        @php
            $application = $this->getApplication();
            $filterYear = session('selected-year-filter') ?? intval(app('current-year'));
        @endphp

        @if(!$application)
            <div class="fi-section mb-6">
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
                                Create an application form to start collecting submissions for the {{ $filterYear }} awards.
                            </p>
                            <x-filament::button
                                wire:click="$dispatch('create')"
                                color="primary"
                                size="lg"
                            >
                                Create Application Form
                            </x-filament::button>
                        </div>
                    </div>
                </div>
            </div>
        @else
            <div class="fi-section">
                <div class="fi-section-content">
                    <div class="fi-card">
                        <div class="fi-card-body p-4">
                            <div class="info-overview">
                                <div class="info-item">
                                    <x-heroicon-o-calendar class="w-3 h-3 text-gray-500" />
                                    <span><strong>Start:</strong> {{ \Carbon\Carbon::parse($application->start_time)->format('M j, Y g:i A') }}</span>
                                </div>
                                <div class="info-item">
                                    <x-heroicon-o-calendar class="w-3 h-3 text-gray-500" />
                                    <span><strong>End:</strong> {{ \Carbon\Carbon::parse($application->end_time)->format('M j, Y g:i A') }}</span>
                                </div>
                                <div class="info-item">
                                    <x-heroicon-o-question-mark-circle class="w-3 h-3 text-gray-500" />
                                    <span><strong>Questions:</strong> {{ count($application->form ?? []) }}</span>
                                </div>
                                <div class="info-item">
                                    <x-heroicon-o-clock class="w-3 h-3 text-gray-500" />
                                    <span><strong>Status:</strong> 
                                        @if(\Carbon\Carbon::now()->between($application->start_time, $application->end_time))
                                            <span class="has-text-success">Active</span>
                                        @elseif(\Carbon\Carbon::now()->lt($application->start_time))
                                            <span class="has-text-info">Upcoming</span>
                                        @else
                                            <span class="has-text-grey">Closed</span>
                                        @endif
                                    </span>
                                </div>
                                <div class="info-item">
                                    <x-heroicon-o-users class="w-3 h-3 text-gray-500" />
                                    <span><strong>Responses:</strong> 
                                        @php
                                            $questionUuids = [];
                                            if ($application->form) {
                                                foreach ($application->form as $question) {
                                                    if (isset($question['id'])) {
                                                        $questionUuids[] = $question['id'];
                                                    }
                                                }
                                            }
                                            $uniqueApplicants = \App\Models\AppAnswer::whereIn('question_id', $questionUuids)
                                                ->distinct('applicant_id')
                                                ->count('applicant_id');
                                        @endphp
                                        {{ $uniqueApplicants }} unique applicants
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            @if($application->form && count($application->form) > 0)
                <div class="fi-section py-4">
                    <div class="fi-section-content">
                        <div class="fi-card">
                            <div class="fi-card-header px-6 py-5">
                                <h3 class="fi-section-header-heading">Application Questions</h3>
                            </div>
                            <div class="fi-card-body px-6 py-1">
                                <div class="mb-4">
                                    @foreach($application->form as $index => $question)
                                        <div class="p-4 mb-4">
                                            <div class="is-flex is-align-items-start is-justify-content-space-between mb-2">
                                                <h4 class="has-text-weight-medium">
                                                    <b>Question {{ $index + 1 }}</b>
                                                    <span class="fi-badge fi-color-{{ $question['type'] === 'multiple_choice' ? 'info' : ($question['type'] === 'essay' ? 'warning' : 'success') }}">
                                                        {{ ucfirst(str_replace('_', ' ', $question['type'])) }}
                                                    </span>
                                                </h4>
                                                
                                            </div>
                                            <p class="has-text-grey mb-3">
                                                {{ $question['question'] }}
                                            </p>
                                            
                                            @if($question['type'] === 'multiple_choice' && isset($question['options']))
                                                <div class="">
                                                    <ul class="is-size-6 has-text-grey" style="list-style-type: disc; list-style-position: inside;">
                                                        @foreach($question['options'] as $option)
                                                            <li class="mb-1">
                                                                {{ $option['option'] }}
                                                            </li>
                                                        @endforeach
                                                    </ul>
                                                </div>
                                            @endif
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            @endif
        @endif
    </div>
</x-filament-panels::page>
