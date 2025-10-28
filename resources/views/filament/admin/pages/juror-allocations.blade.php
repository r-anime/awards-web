<x-filament-panels::page>
    <div class="fi-page-content">
        <div class="fi-section">
            <div class="fi-section-content">
                <div class="fi-card">
                    <div class="fi-card-header px-6 py-5">
                        <h3 class="fi-section-header-heading">Juror Allocations</h3>
                        <p class="fi-section-header-description">
                            View all applicants with their scores and preferences for juror allocation.
                        </p>
                        
                        <div class="mt-4 grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Base Cutoff</label>
                            <input type="number" 
                                   step="0.1" 
                                   min="0" 
                                   max="10" 
                                   wire:model.live="baseCutoff"
                                   class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white"
                                   placeholder="1.5"
                                   value="{{ $this->baseCutoff }}">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Main Category Cutoff</label>
                                <input type="number" 
                                       step="0.1" 
                                       min="0" 
                                       max="10" 
                                       wire:model.live="mainCategoryCutoff"
                                       class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white"
                                       placeholder="2.5"
                                       value="{{ $this->mainCategoryCutoff }}">
                            </div>
                        </div>
                    </div>
                    <div class="fi-card-body px-6 py-6">
                        @php
                            try {
                                $applicants = $this->getApplicantsWithScores();
                            } catch (\Exception $e) {
                                $applicants = [];
                                $error = $e->getMessage();
                            }
                        @endphp

                        @if(isset($error))
                            <div class="text-center py-12">
                                <div class="mx-auto w-12 h-12 bg-red-100 dark:bg-red-900 rounded-full flex items-center justify-center mb-4">
                                    <svg class="w-6 h-6 text-red-600 dark:text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z"></path>
                                    </svg>
                                </div>
                                <h3 class="text-lg font-medium text-red-900 dark:text-red-100 mb-2">Error Loading Data</h3>
                                <p class="text-red-600 dark:text-red-400 mb-4">{{ $error }}</p>
                                <p class="text-sm text-gray-500 dark:text-gray-400">This might be due to a large dataset. Try refreshing the page or clearing the cache.</p>
                            </div>
                        @elseif(empty($applicants))
                            <div class="text-center py-12">
                                <div class="mx-auto w-12 h-12 bg-gray-100 dark:bg-gray-800 rounded-full flex items-center justify-center mb-4">
                                    <svg class="w-6 h-6 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a2.5 2.5 0 11-5 0 2.5 2.5 0 015 0z"></path>
                                    </svg>
                                </div>
                                <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-2">No Applicants Found</h3>
                                <p class="text-gray-500 dark:text-gray-400">No applicants with scores found for the current year.</p>
                            </div>
                        @else
                            @php
                                // Get the first applicant to determine how many essay questions there are
                                $firstApplicant = $applicants[0] ?? null;
                                $essayQuestionColumns = [];
                                if ($firstApplicant && isset($firstApplicant['individual_scores'])) {
                                    foreach ($firstApplicant['individual_scores'] as $questionId => $scoreData) {
                                        $essayQuestionColumns[] = [
                                            'id' => $questionId,
                                            'text' => $scoreData['question_text']
                                        ];
                                    }
                                }
                            @endphp
                            
                            
                             <h3> Total Eligible Applicants: {{ count($applicants) }}</h3>
                             
                             @php
                                 // Get main categories from database
                                 $application = $this->getApplication();
                                 $mainCategories = [];
                                 
                                 if ($application) {
                                     // Get all categories with type "main" OR OP/ED categories for this application's year, comedy getting fucked up lmao
                                     $categories = \App\Models\Category::where('year', $application->year)
                                         ->where(function($query) {
                                             $query->where('type', 'main')
                                                   ->orWhere('name', 'LIKE', '%OP%')
                                                   ->orWhere('name', 'LIKE', '%ED%');
                                         })
                                         ->where('name', 'NOT LIKE', '%comedy%')
                                         ->where('name', 'NOT LIKE', '%Comedy%')
                                         ->get();
                                     
                                     foreach ($categories as $category) {
                                         $mainCategories[] = [
                                             'id' => $category->id,
                                             'name' => $category->name,
                                             'max_jurors' => 15
                                         ];
                                     }
                                 }
                                 
                                 // Allocation algorithm
                                 $allocations = [];
                                 $remainingApplicants = $applicants;
                                 $vaxxinatedApplicants = [];
                                 $jurorAllocationCount = []; // Track how many categories each juror is allocated to
                                 
                                 foreach ($mainCategories as $category) {
                                     $categoryAllocations = [];
                                     $categoryId = $category['id'];
                                     $categoryName = $category['name'];
                                     $maxJurors = $category['max_jurors'];
                                     
                                     // Check if this is OP or ED category
                                     $isOPorED = (stripos($categoryName, 'OP') !== false || stripos($categoryName, 'ED') !== false);
                                     
                                     if ($isOPorED) {
                                         
                                         // For OP/ED: up to 11 jurors who scored above main threshold only on question 2
                                         $opEdCandidates = 0;
                                         $allocatedCount = 0;
                                         foreach ($remainingApplicants as $applicant) {
                                             if ($allocatedCount >= 11) {
                                                 break; // Stop at 11 jurors for OP/ED categories
                                             }
                                             
                                             $jurorId = $applicant['id'];
                                             $currentAllocations = $jurorAllocationCount[$jurorId] ?? 0;
                                             
                                             // Check if they have preference for this category in non-main preferences
                                             $hasPreference = false;
                                             foreach ($applicant['non_main_preferences'] as $pref) {
                                                 if ($pref['id'] == $categoryId) {
                                                     $hasPreference = true;
                                                     break;
                                                 }
                                             }
                                             
                                             if (!$hasPreference) {
                                                 continue; // Skip if no preference for this category
                                             }
                                             
                                             $opEdCandidates++;
                                             
                                             // Check if they scored above main threshold only on question 2
                                             \Log::info('Debug: Checking OP/ED applicant', ['applicant_id' => $applicant['id'], 'individual_scores' => $applicant['individual_scores']]);
                                             
                                             // Find the average_score for the 2nd question in the JSON structure
                                             $question2Score = 0;
                                             $questionCount = 0;
                                             foreach ($applicant['individual_scores'] as $questionId => $scoreData) {
                                                 $questionCount++;
                                                 if ($questionCount == 2) { // 2nd question
                                                     $question2Score = $scoreData['average_score'] ?? 0;
                                                     break;
                                                 }
                                             }
                                             
                                             if ($question2Score >= $this->mainCategoryCutoff) {
                                                 $categoryAllocations[] = $applicant;
                                                 $allocatedCount++;
                                                 
                                                 \Log::info('Debug: Allocated OP/ED applicant', ['applicant_id' => $applicant['id'], 'question2_score' => $question2Score, 'allocated_count' => $allocatedCount]);
                                             }
                                         }
                                         
                                         \Log::info('Debug: OP/ED category summary', [
                                             'category_name' => $categoryName,
                                             'candidates_with_preference' => $opEdCandidates,
                                             'allocated_count' => count($categoryAllocations)
                                         ]);
                                     } else {
                                         // Regular category logic
                                         // Sort applicants by preference order for this category
                                         $preferredApplicants = [];
                                         foreach ($remainingApplicants as $applicant) {
                                             // No limit on number of categories per juror
                                             $jurorId = $applicant['id'];
                                             $currentAllocations = $jurorAllocationCount[$jurorId] ?? 0;
                                             
                                             foreach ($applicant['main_preferences'] as $pref) {
                                                 if ($pref['id'] == $categoryId) {
                                                     $preferredApplicants[] = [
                                                         'applicant' => $applicant,
                                                         'preference_order' => $pref['order'] ?? 999,
                                                         'score' => $applicant['total_average_score']
                                                     ];
                                                     break;
                                                 }
                                             }
                                         }
                                         
                                         // Sort by preference order, then by score
                                         usort($preferredApplicants, function($a, $b) {
                                             if ($a['preference_order'] == $b['preference_order']) {
                                                 return $b['score'] <=> $a['score']; // Higher score first
                                             }
                                             return $a['preference_order'] <=> $b['preference_order'];
                                         });
                                         
                                         // Allocate up to max_jurors
                                         $allocatedCount = 0;
                                         $lastPreferenceOrder = null;
                                         
                                         foreach ($preferredApplicants as $prefApplicant) {
                                             
                                             if ($allocatedCount >= $maxJurors) {
                                                 // Check if this applicant has the same preference order as the last allocated
                                                 if ($lastPreferenceOrder !== null && $prefApplicant['preference_order'] == $lastPreferenceOrder) {
                                                     $vaxxinatedApplicants[] = $prefApplicant['applicant'];
                                                 }
                                                 break;
                                             }
                                             
                                             // Check if applicant meets main category cutoff
                                             if ($prefApplicant['score'] >= $this->mainCategoryCutoff) {
                                                 $categoryAllocations[] = $prefApplicant['applicant'];
                                                 
                                                 // Track allocation count for this juror
                                                 $jurorId = $prefApplicant['applicant']['id'];
                                                 $jurorAllocationCount[$jurorId] = ($jurorAllocationCount[$jurorId] ?? 0) + 1;
                                                 
                                                 $allocatedCount++;
                                                 $lastPreferenceOrder = $prefApplicant['preference_order'];
                                                 
                                                 \Log::info('Debug: Allocated applicant', ['applicant_id' => $prefApplicant['applicant']['id'], 'allocated_count' => $allocatedCount]);
                                             }
                                         }
                                     }
                                     
                                     
                                     $allocations[$categoryId] = [
                                         'name' => $category['name'],
                                         'allocations' => $categoryAllocations,
                                         'count' => count($categoryAllocations),
                                         'max_jurors' => $maxJurors
                                     ];
                                 }
                             @endphp
                             
                             <div class="mt-6">
                                 <h3 class="text-lg font-semibold mb-4">Category Allocations</h3>
                                 @foreach($allocations as $categoryId => $allocation)
                                     <div class="mb-6 border border-gray-200 dark:border-gray-700 rounded-lg p-4">
                                        <h4 class="text-lg font-black mb-3 text-gray-900 dark:text-white" style="font-weight: 900 !important; font-size: 1.125rem !important;">
                                            {{ $allocation['name'] }} 
                                            <span class="text-sm text-gray-500 dark:text-gray-400 font-normal" style="font-weight: 400 !important;">
                                                ({{ $allocation['count'] }}/{{ $allocation['max_jurors'] }} jurors)
                                            </span>
                                        </h4>
                                         
                                         @if(count($allocation['allocations']) > 0)
                                             <div class="grid grid-cols-1 md:grid-cols-2 gap-4 max-w-full category-grid" style="display: grid !important; grid-template-columns: repeat(1, minmax(0, 1fr)) !important;">
                                             <style>
                                                 @media (min-width: 768px) {
                                                     .category-grid {
                                                         grid-template-columns: repeat(2, minmax(0, 1fr)) !important;
                                                     }
                                                 }
                                             </style>
                                                 @foreach($allocation['allocations'] as $allocatedApplicant)
                                                     <div class="bg-gray-50 dark:bg-gray-800 p-3 rounded border">
                                                         <div class="font-medium text-sm">{{ $allocatedApplicant['name'] }}( {{ $allocatedApplicant['total_average_score'] }})</div>
                                                     </div>
                                                 @endforeach
                                             </div>
                                         @else
                                             <p class="text-gray-500 dark:text-gray-400 text-sm">No jurors allocated to this category.</p>
                                         @endif
                                     </div>
                                 @endforeach
                                 
                                 @if(count($remainingApplicants) > 0)
                                     <div class="mt-6 border border-gray-200 dark:border-gray-700 rounded-lg p-4">
                                         <h4 class="text-md font-medium mb-3">
                                             Unallocated Applicants ({{ count($remainingApplicants) }})
                                         </h4>
                                         <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-3">
                                             @foreach($remainingApplicants as $applicant)
                                                <div class="bg-gray-50 dark:bg-gray-800 p-3 rounded border">
                                                    <div class="font-medium text-sm">{{ $applicant['name'] }}( {{ $applicant['total_average_score'] }})</div>
                                                </div>
                                             @endforeach
                                         </div>
                                     </div>
                                 @endif
                             </div>
                            <div class="overflow-x-auto">
                                <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                                    <thead class="bg-gray-50 dark:bg-gray-800">
                                        <tr>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Applicant</th>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Total Avg Score</th>
                                            @foreach($essayQuestionColumns as $question)
                                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                                    {{ Str::limit($question['text'], 20) }}
                                                </th>
                                            @endforeach
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Main Preferences</th>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Non-Main Preferences</th>
                                        </tr>
                                    </thead>
                                    <tbody class="bg-white dark:bg-gray-900 divide-y divide-gray-200 dark:divide-gray-700">
                                        @foreach($applicants as $applicant)
                                            <tr class="hover:bg-gray-50 dark:hover:bg-gray-800">
                                                <td class="px-6 py-4 whitespace-nowrap">
                                                    <div class="flex items-center">
                                                        <div class="ml-4">
                                                            <div class="text-sm font-medium text-gray-900 dark:text-white">
                                                                {{ $applicant['name'] }}
                                                            </div>
                                                        </div>
                                                    </div>
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap">
                                                    <div class="text-sm font-medium text-gray-900 dark:text-white">
                                                        {{ $applicant['total_average_score'] > 0 ? $applicant['total_average_score'] : 'N/A' }}
                                                    </div>
                                                </td>
                                                @foreach($essayQuestionColumns as $question)
                                                    <td class="px-6 py-4 whitespace-nowrap">
                                                        <div class="text-sm">
                                                            <div class="font-medium text-gray-900 dark:text-white">
                                                                {{ $applicant['individual_scores'][$question['id']]['average_score'] ?? 'N/A' }}
                                                            </div>
                                                        </div>
                                                    </td>
                                                @endforeach
                                                <td class="px-6 py-4">
                                                    <div class="space-y-1">
                                                        @if(empty($applicant['main_preferences']))
                                                            <span class="text-sm text-gray-500 dark:text-gray-400">None</span>
                                                        @else
                                                            @foreach($applicant['main_preferences'] as $index => $preference)
                                                                <div class="text-sm">
                                                                    <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200">
                                                                        {{ $index + 1 }}. {{ $preference['name'] }}
                                                                    </span>
                                                                </div>
                                                            @endforeach
                                                        @endif
                                                    </div>
                                                </td>
                                                <td class="px-6 py-4">
                                                    <div class="space-y-1">
                                                        @if(empty($applicant['non_main_preferences']))
                                                            <span class="text-sm text-gray-500 dark:text-gray-400">None</span>
                                                        @else
                                                            @foreach($applicant['non_main_preferences'] as $preference)
                                                                <div class="text-sm">
                                                                    <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-200">
                                                                        {{ $preference['name'] }}
                                                                    </span>
                                                                </div>
                                                            @endforeach
                                                        @endif
                                                    </div>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-filament-panels::page>
