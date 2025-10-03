<x-filament-panels::page>
    <div class="fi-page-content">
        @if($this->ungradedApplication)
            <div class="fi-section mb-4">
                <div class="fi-section-content">
                    <div class="fi-card">
                        <div class="fi-card-body p-6">
                            <h2 class="fi-section-header-heading mb-4">
                                Grading Application for: <strong>{{ $this->ungradedApplication->uuid }}</strong>
                            </h2>
                            <p class="fi-section-header-description">
                                Please grade the essay questions below.
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            @php
                $essayQuestions = $this->getEssayQuestions();
                $existingAnswers = $this->getExistingAnswers();
            @endphp

            @foreach($essayQuestions as $question)
                <div class="fi-section mb-4">
                    <div class="fi-section-content">
                        <div class="fi-card">
                            <div class="fi-card-body p-6">
                                <h3 class="fi-section-header-heading mb-4">{{ $question['question'] }}</h3>
                                
                                <div class="mb-4">
                                    <label class="fi-fo-field-wrp-label">
                                        <span class="fi-fo-field-wrp-label-text">
                                            <span class="fi-fo-field-wrp-label-text-label">Applicant's Answer:</span>
                                        </span>
                                    </label>
                                    <div class="fi-input-wrp">
                                        <div class="fi-input-wrp-content">
                                            <div class="fi-input-wrp-content-inner">
                                                <div class="fi-input-wrp-content-inner-content">
                                                    <div class="fi-input-wrp-content-inner-content-text">
                                                        <p class="text-gray-700 dark:text-gray-300 bg-gray-50 dark:bg-gray-800 p-4 rounded-lg border">
                                                            {{ $existingAnswers[$question['id']] ?? 'No answer provided' }}
                                                        </p>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                    <div>
                                        <label class="fi-fo-field-wrp-label">
                                            <span class="fi-fo-field-wrp-label-text">
                                                <span class="fi-fo-field-wrp-label-text-label">Grade:</span>
                                            </span>
                                        </label>
                                        <div class="fi-input-wrp">
                                            <div class="fi-input-wrp-content">
                                                <div class="fi-input-wrp-content-inner">
                                                    <div class="fi-input-wrp-content-inner-content">
                                                        <div class="fi-input-wrp-content-inner-content-text">
                                                            <input 
                                                                type="number" 
                                                                wire:model="grades.{{ $question['id'] }}"
                                                                min="0" 
                                                                max="100" 
                                                                class="fi-input"
                                                                placeholder="Enter grade"
                                                            >
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <div class="mt-4">
                                        <label class="fi-fo-field-wrp-label">
                                            <span class="fi-fo-field-wrp-label-text">
                                                <span class="fi-fo-field-wrp-label-text-label">Comment:</span>
                                            </span>
                                        </label>
                                        <div class="fi-input-wrp">
                                            <div class="fi-input-wrp-content">
                                                <div class="fi-input-wrp-content-inner">
                                                    <div class="fi-input-wrp-content-inner-content">
                                                        <div class="fi-input-wrp-content-inner-content-text">
                                                            <input 
                                                                type="text"
                                                                wire:model="comments.{{ $question['id'] }}"
                                                                class="fi-input w-full"
                                                                placeholder="Optional comment..."
                                                            />
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach

            @if(count($essayQuestions) === 0)
                <div class="fi-section mb-6">
                    <div class="fi-section-content">
                        <div class="fi-card">
                            <div class="fi-card-body p-6 text-center">
                                <h3 class="fi-section-header-heading mb-4">No Essay Questions</h3>
                                <p class="fi-section-header-description">
                                    This application doesn't contain any essay questions that require grading.
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            @endif

        @else
            <div class="fi-section mb-6">
                <div class="fi-section-content">
                    <div class="fi-card">
                        <div class="fi-card-body p-6 text-center">
                            <div class="fi-icon fi-icon-color-gray-400 fi-icon-size-md mx-auto mb-6">
                                <svg class="fi-icon-svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" style="width: 3rem; height: 3rem;">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                            </div>
                            <h3 class="fi-section-header-heading mb-4">
                                No Ungraded Applications
                            </h3>
                            <p class="fi-section-header-description">
                                All applications have been graded by you. Great job!
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        @endif
    </div>
</x-filament-panels::page>
