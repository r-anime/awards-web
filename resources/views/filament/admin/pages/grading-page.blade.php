<x-filament-panels::page>
    <div class="fi-page-content">
        @if($this->ungradedApplication)
            <div class="fi-section mb-4">
                <div class="fi-section-content">
                    <div class="fi-card">
                        <div class="fi-card-body p-6">
                            <h2 class="fi-section-header-heading mb-4">
                                Grading Application for: <strong>{{ $this->ungradedApplication->uuid }}</strong>
                                @if(auth()->user()->role >= 3)
                                    <br><span class="text-lg font-normal text-gray-600 dark:text-gray-400">
                                        Applicant: {{ $this->ungradedApplication->name ?? $this->ungradedApplication->reddit_user ?? 'User #' . $this->ungradedApplication->id }}
                                    </span>
                                @endif
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
                $nonEssayQuestions = $this->getNonEssayQuestions();
                $existingAnswers = $this->getExistingAnswers();
                $preferenceQid = $this->getPreferenceQuestionId();
                $categoriesById = $this->getCategoriesById();
                $otherHostScores = $this->getOtherHostScores();
            @endphp

            {{-- Non-essay answers (no grading UI) --}}
            @if(!empty($nonEssayQuestions))
                <div class="fi-section mb-6">
                    <div class="fi-section-content">
                        <div class="fi-card">
                            <div class="fi-card-body p-6">
                                <h3 class="fi-section-header-heading mb-4">Applicant's Non‑Essay Answers</h3>

                                @foreach($nonEssayQuestions as $q)
                                    <div class="mb-6">
                                        <div class="font-medium mb-2">{{ $q['question'] ?? 'Question' }}</div>
                                        @php
                                            $raw = $existingAnswers[$q['id']] ?? null;
                                        @endphp
                                        @if(($q['type'] ?? null) === 'preference')
                                            @php
                                                $pref = $raw ? json_decode($raw, true) : ['non_main' => [], 'main_order' => '', 'no_main_order' => ''];
                                                $nonMainIds = is_array($pref['non_main'] ?? []) ? $pref['non_main'] : [];
                                                $mainOrderData = [];
                                                if (!empty($pref['main_order'])) {
                                                    $decoded = is_array($pref['main_order']) ? $pref['main_order'] : json_decode($pref['main_order'], true);
                                                    if (is_array($decoded)) {
                                                        $mainOrderData = $decoded;
                                                    }
                                                }
                                                usort($mainOrderData, function($a, $b) {
                                                    return (int) ($a['order'] ?? 999) <=> (int) ($b['order'] ?? 999);
                                                });
                                            @endphp
                                            <div class="text-gray-700 dark:text-gray-300">
                                                <div class="mb-2">
                                                    <span class="font-semibold">Checked non‑main categories:</span>
                                                    @if(!empty($nonMainIds))
                                                        <ul class="list-disc ml-6 mt-1">
                                                            @foreach($nonMainIds as $cid)
                                                                @php $c = $categoriesById[(int) $cid] ?? null; @endphp
                                                                @if($c)
                                                                    <li>{{ $c->name }} ({{ $c->type }})</li>
                                                                @endif
                                                            @endforeach
                                                        </ul>
                                                    @else
                                                        <span>None</span>
                                                    @endif
                                                </div>
                                                <div>
                                                    <span class="font-semibold">Selected main categories (in order):</span>
                                                    @if(!empty($mainOrderData))
                                                        <ol class="list-decimal ml-6 mt-1">
                                                            @foreach($mainOrderData as $item)
                                                                @php $c = $categoriesById[(int) ($item['category_id'] ?? 0)] ?? null; @endphp
                                                                @if($c)
                                                                    <li>{{ $c->name }}</li>
                                                                @endif
                                                            @endforeach
                                                        </ol>
                                                    @else
                                                        <span>None</span>
                                                    @endif
                                                </div>
                                            </div>
                                        @elseif(($q['type'] ?? null) === 'multiple_choice')
                                            @php
                                                $display = 'No answer provided';
                                                if (!empty($raw) && !empty($q['options'] ?? [])) {
                                                    foreach (($q['options'] ?? []) as $opt) {
                                                        if ((string) ($opt['id'] ?? '') === (string) $raw) {
                                                            $display = $opt['option'] ?? (string) $raw;
                                                            break;
                                                        }
                                                    }
                                                }
                                            @endphp
                                            <p class="text-gray-700 dark:text-gray-300 bg-gray-50 dark:bg-gray-800 p-4 rounded-lg border">{{ $display }}</p>
                                        @else
                                            @php
                                                $display = $raw;
                                                if (is_string($raw) && str_starts_with($raw, '[')) {
                                                    $decoded = json_decode($raw, true);
                                                    if (json_last_error() === JSON_ERROR_NONE) {
                                                        $display = implode(', ', array_map('strval', (array) $decoded));
                                                    }
                                                }
                                            @endphp
                                            <p class="text-gray-700 dark:text-gray-300 bg-gray-50 dark:bg-gray-800 p-4 rounded-lg border">{{ $display ?? 'No answer provided' }}</p>
                                        @endif
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>
            @endif

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

                                {{-- Other hosts' grades/comments for this essay --}}
                                @php
                                    $others = $otherHostScores[(string) $question['id']] ?? [];
                                @endphp
                                @if(!empty($others))
                                    <div class="mt-6">
                                        <div class="font-medium mb-2">Other Hosts' Feedback</div>
                                        <div class="space-y-2">
                                            @foreach($others as $o)
                                                <div class="bg-gray-50 dark:bg-gray-800 p-3 rounded border">
                                                    <div class="text-sm text-gray-600 dark:text-gray-400 mb-1">{{ $o['scorer'] }}</div>
                                                    <div class="text-gray-800 dark:text-gray-200"><span class="font-semibold">Grade:</span> {{ $o['score'] }}</div>
                                                    @if(!empty($o['comment']))
                                                        <div class="text-gray-700 dark:text-gray-300"><span class="font-semibold">Comment:</span> {{ $o['comment'] }}</div>
                                                    @endif
                                                </div>
                                            @endforeach
                                        </div>
                                    </div>
                                @endif
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
