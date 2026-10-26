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
                                $applicants = $this->getEligibleApplicants();
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
                             <h3> Total Eligible Applicants: {{ count($applicants) }}</h3>

                             @php
                                 $allocationResult = $this->currentAllocations();
                                 $allocations = $allocationResult['categories'];
                                 $remainingApplicants = $allocationResult['unallocated'];
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
                            <div class="mt-6">
                                {{ $this->table }}
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-filament-panels::page>
