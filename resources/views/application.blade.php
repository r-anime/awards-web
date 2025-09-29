<x-layout title="Applications - r/anime Awards">
    <div class="section">
        <div class="container application-container">
            <h1 class="title is-2 has-text-centered has-text-white mb-6">
                Juror Application
            </h1>
            <p class="subtitle is-6 has-text-centered has-text-white">
                <strong>Important:</strong> Applications no longer autosave. Please make sure to submit your application using the submit button when you're finished.
            </p>
            
            <div class="content">
                            @if(session('success'))
                                <div class="notification is-success">
                                    {{ session('success') }}
                                </div>
                            @endif
                            
                            @if(session('error'))
                                <div class="notification is-danger">
                                    {{ session('error') }}
                                </div>
                            @endif
                            
                            @php
                                $isOpen = false;
                                if($application) {
                                    $now = \Carbon\Carbon::now();
                                    $isOpen = $now->between($application->start_time, $application->end_time);
                                    $isUpcoming = $now->lt($application->start_time);
                                    $isClosed = $now->gt($application->end_time);
                                }
                            @endphp
                            
                            @if($application)
                                @if($isOpen)
                                    <div class="notification is-info">
                                        <strong>Applications are OPEN!</strong> Applications are currently being accepted for the {{ $application->year }} awards season.
                                        <br>
                                        <strong>Application Period:</strong> {{ \Carbon\Carbon::parse($application->start_time)->format('F j, Y') }} - {{ \Carbon\Carbon::parse($application->end_time)->format('F j, Y') }}
                                    </div>
                                    
                                    <div class="countdown-container">
                                        <strong>Time Remaining:</strong>
                                        <div id="countdown" class="countdown-timer" data-end-time="{{ \Carbon\Carbon::parse($application->end_time)->toISOString() }}">
                                            <span class="countdown-item">
                                                <span class="countdown-number" id="days">0</span>
                                                <span class="countdown-label">Days</span>
                                            </span>
                                            <span class="countdown-item">
                                                <span class="countdown-number" id="hours">0</span>
                                                <span class="countdown-label">Hours</span>
                                            </span>
                                            <span class="countdown-item">
                                                <span class="countdown-number" id="minutes">0</span>
                                                <span class="countdown-label">Minutes</span>
                                            </span>
                                            <span class="countdown-item">
                                                <span class="countdown-number" id="seconds">0</span>
                                                <span class="countdown-label">Seconds</span>
                                            </span>
                                        </div>
                                    </div>
                                @elseif($isUpcoming)
                                    <div class="notification is-info">
                                        <strong>Applications Coming Soon:</strong> Applications for the {{ $application->year }} awards season will open on {{ \Carbon\Carbon::parse($application->start_time)->format('F j, Y') }}.
                                        <br><br>
                                        <strong>Application Period:</strong> {{ \Carbon\Carbon::parse($application->start_time)->format('F j, Y') }} - {{ \Carbon\Carbon::parse($application->end_time)->format('F j, Y') }}
                                        @if(auth()->check() && auth()->user()->role >= 2)
                                            <br><br>
                                            <strong>Host Access:</strong> You have elevated access, you can still access and submit applications before the official opening date.
                                        @endif
                                    </div>
                                @else
                                    <div class="notification is-warning">
                                        <strong>Applications Closed:</strong> Applications for the {{ $application->year }} awards season have closed as of {{ \Carbon\Carbon::parse($application->end_time)->format('F j, Y') }}.
                                        <br><br>
                                        <strong>Application Period Was:</strong> {{ \Carbon\Carbon::parse($application->start_time)->format('F j, Y') }} - {{ \Carbon\Carbon::parse($application->end_time)->format('F j, Y') }}
                                        @if(auth()->check() && auth()->user()->role >= 2)
                                            <br><br>
                                            <strong>Host Access:</strong> You have elevated access, you can still access and submit applications outside the normal period.
                                        @endif
                                    </div>
                                @endif
                            @else
                                <div class="notification is-light">
                                    <strong>No Applications Available:</strong> There are currently no applications set up for the {{ $currentYear }} awards season.
                                </div>
                            @endif
                            
                            @auth
                                @if($application && ($isOpen || (auth()->user()->role >= 2)))
                                    <form method="POST" action="{{ route('application.store') }}" id="applicationForm" onsubmit="console.log('Form submitting...'); return true;">
                                        @csrf
                                        
                                        @if($application->form && count($application->form) > 0)
                                            @foreach($application->form as $index => $question)
                                                <div class="field question-container mb-4">
                                                    <label class="label has-text-white is-size-5 mb-4">
                                                        {{ $question['question'] }}
                                                        <span class="tag is-dark ml-2">{{ ucfirst(str_replace('_', ' ', $question['type'])) }}</span>
                                                    </label>
                                                    
                                                    @if($question['type'] === 'multiple_choice')
                                                        <div class="control">
                                                            @if(isset($question['options']) && count($question['options']) > 0)
                                                                @foreach($question['options'] as $optionIndex => $option)
                                                                    <label class="radio has-text-white">
                                                                        <input type="radio" name="question_{{ $question['id'] }}" value="{{ $option['id'] }}"
                                                                               {{ isset($existingAnswers[$question['id']]) && $existingAnswers[$question['id']] == $option['id'] ? 'checked' : '' }}>
                                                                        <span class="checkmark"></span>
                                                                        {{ $option['option'] }}
                                                                    </label>
                                                                    <br>
                                                                @endforeach
                                                            @endif
                                                        </div>
                                                    @elseif($question['type'] === 'essay')
                                                        <div class="control">
                                                            <textarea 
                                                                id="editor_{{ $question['id'] }}" 
                                                                name="question_{{ $question['id'] }}" 
                                                                class="textarea essay-editor"
                                                                placeholder="Type your answer here..."
                                                            >{{ isset($existingAnswers[$question['id']]) ? $existingAnswers[$question['id']] : '' }}</textarea>
                                                        </div>
                                                    @elseif($question['type'] === 'preference')
                                                        <div class="preference-form">
                                                            <div class="columns">
                                                                <div class="column">
                                                                    <h4 class="title is-5 has-text-white mb-3">Non-Main Categories</h4>
                                                                    <p class="has-text-white mb-4">Select the categories you're interested in:</p>
                                                                    <div class="checkbox-list">
                                                                        @php
                                                                            $selectedNonMain = [];
                                                                            if (isset($existingAnswers[$question['id']])) {
                                                                                $preferenceData = json_decode($existingAnswers[$question['id']], true);
                                                                                $selectedNonMain = $preferenceData['non_main'] ?? [];
                                                                            }
                                                                        @endphp
                                                                        @foreach($nonMainCategories as $category)
                                                                            <label class="checkbox has-text-white mb-2">
                                                                                <input type="checkbox" name="preference_non_main[]" value="{{ $category->id }}"
                                                                                       {{ in_array($category->id, $selectedNonMain) ? 'checked' : '' }}>
                                                                                <span class="checkmark"></span>
                                                                                {{ $category->name }} ({{ ucfirst($category->type) }})
                                                                            </label>
                                                                        @endforeach
                                                                    </div>
                                                                </div>
                                                                <div class="column">
                                                                    <h4 class="title is-5 has-text-white mb-3">Main Categories</h4>
                                                                    <p class="has-text-white mb-4">Drag and drop to order your preferences:</p>
                                                                    @php
                                                                        $mainOrder = '';
                                                                        $noMainOrder = '';
                                                                        $mainOrderData = [];
                                                                        $noMainOrderData = [];
                                                                        
                                                                        if (isset($existingAnswers[$question['id']])) {
                                                                            $preferenceData = json_decode($existingAnswers[$question['id']], true);
                                                                            $mainOrder = $preferenceData['main_order'] ?? '';
                                                                            $noMainOrder = $preferenceData['no_main_order'] ?? '';
                                                                            
                                                                            // Parse the order data
                                                                            if ($mainOrder) {
                                                                                $mainOrderData = json_decode($mainOrder, true) ?? [];
                                                                            }
                                                                            if ($noMainOrder) {
                                                                                $noMainOrderData = json_decode($noMainOrder, true) ?? [];
                                                                            }
                                                                        }
                                                                        
                                                                        // Create maps for quick lookup
                                                                        $mainOrderMap = [];
                                                                        $noMainOrderMap = [];
                                                                        
                                                                        foreach ($mainOrderData as $item) {
                                                                            $mainOrderMap[$item['category_id']] = $item['order'];
                                                                        }
                                                                        
                                                                        foreach ($noMainOrderData as $item) {
                                                                            $noMainOrderMap[$item['category_id']] = $item['order'];
                                                                        }
                                                                        
                                                                        // Separate categories into main and no-main based on saved data
                                                                        $mainCategoriesList = [];
                                                                        $noMainCategoriesList = [];
                                                                        
                                                                        foreach ($mainCategories as $category) {
                                                                            if (isset($noMainOrderMap[$category->id])) {
                                                                                $noMainCategoriesList[] = $category;
                                                                            } else {
                                                                                $mainCategoriesList[] = $category;
                                                                            }
                                                                        }
                                                                        
                                                                        // Sort main categories by their saved order
                                                                        usort($mainCategoriesList, function($a, $b) use ($mainOrderMap) {
                                                                            $aOrder = $mainOrderMap[$a->id] ?? 999;
                                                                            $bOrder = $mainOrderMap[$b->id] ?? 999;
                                                                            return $aOrder - $bOrder;
                                                                        });
                                                                        
                                                                        // Sort no-main categories by their saved order
                                                                        usort($noMainCategoriesList, function($a, $b) use ($noMainOrderMap) {
                                                                            $aOrder = $noMainOrderMap[$a->id] ?? 999;
                                                                            $bOrder = $noMainOrderMap[$b->id] ?? 999;
                                                                            return $aOrder - $bOrder;
                                                                        });
                                                                    @endphp
                                                                    <div id="main_categories_sortable" class="sortable-list">
                                                                        @foreach($mainCategoriesList as $category)
                                                                            <div class="sortable-item" data-category-id="{{ $category->id }}">
                                                                                <span class="drag-handle">⋮⋮</span>
                                                                                {{ $category->name }}
                                                                            </div>
                                                                        @endforeach
                                                                    </div>
                                                                    <input type="hidden" name="preference_main_order" id="main_categories_order" value="{{ $mainOrder }}">
                                                                    <p class="has-text-white mb-4">If you don't want any main category, drag items here:</p>
                                                                    <div id="no_main_categories_sortable" class="sortable-list no-main-category">
                                                                        @if(count($noMainCategoriesList) > 0)
                                                                            @foreach($noMainCategoriesList as $category)
                                                                                <div class="sortable-item" data-category-id="{{ $category->id }}">
                                                                                    <span class="drag-handle">⋮⋮</span>
                                                                                    {{ $category->name }}
                                                                                </div>
                                                                            @endforeach
                                                                        @else
                                                                            <div class="empty-placeholder">
                                                                                <p class="has-text-white has-text-centered">Drag unwanted main categories here</p>
                                                                            </div>
                                                                        @endif
                                                                    </div>
                                                                    <input type="hidden" name="preference_no_main_order" id="no_main_categories_order" value="{{ $noMainOrder }}">
                                                                </div>
                                                            </div>
                                                        </div>
                                                    @endif
                                                    
                                                    @if(isset($question['instructions']) && !empty($question['instructions']))
                                                        <div class="notification is-info is-light mt-4">
                                                            <div class="content">
                                                                <p class="has-text-weight-semibold mb-2">
                                                                    <span class="icon-text">
                                                                        <span class="icon">
                                                                            <i class="fas fa-info-circle"></i>
                                                                        </span>
                                                                        <span>Additional Instructions:</span>
                                                                    </span>
                                                                </p>
                                                                <p class="has-text-dark">{{ $question['instructions'] }}</p>
                                                            </div>
                                                        </div>
                                                    @endif
                                                </div>
                                            @endforeach
                                        @endif
                                        
                                        <div class="field">
                                            <div class="control has-text-centered">
                                                <button type="submit" class="button is-primary is-large" onclick="console.log('Submit button clicked');">
                                                    Submit Application
                                                </button>
                                            </div>
                                        </div>
                                    </form>
                                @else
                                    <div class="notification is-info">
                                        <p class="has-text-white">
                                            @if(!$application)
                                                No application form is available for the {{ $currentYear }} awards season.
                                            @elseif($isUpcoming)
                                                Applications will open on {{ \Carbon\Carbon::parse($application->start_time)->format('F j, Y') }}.
                                            @else
                                                Applications have closed for the {{ $application->year }} awards season.
                                            @endif
                                        </p>
                                    </div>
                                @endif
                            @else
                                <div class="has-text-centered mt-6">
                                    <p class="has-text-white mb-4">
                                        You must be logged in to submit an application.
                                    </p>
                                    <a class="button is-primary" href="/login?redirect={{ urlencode(request()->fullUrl()) }}">
                                        <strong>Log In</strong>
                                    </a>
                                </div>
                            @endauth
            </div>
        </div>
    </div>
    
    
    <!-- Include EasyMDE (SimpleMDE) Markdown Editor -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/easymde@2.18.0/dist/easymde.min.css">
    <script src="https://cdn.jsdelivr.net/npm/easymde@2.18.0/dist/easymde.min.js"></script>
    
    <!-- Include Sortable.js for drag and drop -->
    <script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.0/Sortable.min.js"></script>
    
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            
            const editors = {};
            
            // Initialize EasyMDE editors
            if (typeof EasyMDE !== 'undefined') {
                document.querySelectorAll('[id^="editor_"]').forEach(function(element) {
                    const questionId = element.id.replace('editor_', '');
                    
                    try {
                        // Initialize EasyMDE editor
                        const editor = new EasyMDE({
                            element: element,
                            toolbar: [
                                'bold', 'italic', 'strikethrough', '|',
                                'heading-1', 'heading-2', 'heading-3', '|',
                                'quote', 'unordered-list', 'ordered-list', '|',
                                'link', 'image', 'table', '|',
                                'preview', 'side-by-side', 'fullscreen', '|',
                                'guide'
                            ],
                            placeholder: 'Type your answer here...',
                            spellChecker: false,
                            status: false,
                            autosave: {
                                enabled: false
                            },
                            insertTexts: {
                                link: ['[', '](url)']
                            }
                        });
                        
                        editors[questionId] = editor;
                    } catch (error) {
                        console.error('Error initializing EasyMDE editor for question', questionId, error);
                    }
                });
            }
            
            // Initialize sortable for main categories
            function initializeSortable() {
                const mainSortableElement = document.getElementById('main_categories_sortable');
                const noMainSortableElement = document.getElementById('no_main_categories_sortable');
                
                if (mainSortableElement && typeof Sortable !== 'undefined') {
                    // Initialize main categories sortable
                    const mainSortable = Sortable.create(mainSortableElement, {
                        group: 'main-categories',
                        animation: 150,
                        ghostClass: 'sortable-ghost',
                        chosenClass: 'sortable-chosen',
                        dragClass: 'sortable-drag',
                        draggable: '.sortable-item',
                        onEnd: function(evt) {
                            updateCategoriesOrder();
                        }
                    });
                    
                    // Initialize no main categories sortable
                    if (noMainSortableElement) {
                        const noMainSortable = Sortable.create(noMainSortableElement, {
                            group: 'main-categories',
                            animation: 150,
                            ghostClass: 'sortable-ghost',
                            chosenClass: 'sortable-chosen',
                            dragClass: 'sortable-drag',
                            draggable: '.sortable-item',
                            onEnd: function(evt) {
                                updateCategoriesOrder();
                            }
                        });
                    }
                    
                    function updateCategoriesOrder() {
                        // Update main categories order
                        const mainOrder = [];
                        document.querySelectorAll('#main_categories_sortable .sortable-item').forEach(function(item, index) {
                            mainOrder.push({
                                category_id: item.dataset.categoryId,
                                order: index + 1
                            });
                        });
                        document.getElementById('main_categories_order').value = JSON.stringify(mainOrder);
                        
                        // Update no main categories order
                        const noMainOrder = [];
                        document.querySelectorAll('#no_main_categories_sortable .sortable-item').forEach(function(item, index) {
                            noMainOrder.push({
                                category_id: item.dataset.categoryId,
                                order: index + 1
                            });
                        });
                        document.getElementById('no_main_categories_order').value = JSON.stringify(noMainOrder);
                        
                        // Hide/show empty placeholder
                        const placeholder = document.querySelector('#no_main_categories_sortable .empty-placeholder');
                        const hasItems = document.querySelectorAll('#no_main_categories_sortable .sortable-item').length > 0;
                        if (placeholder) {
                            placeholder.style.display = hasItems ? 'none' : 'block';
                        }
                    }
                    
                    // Drag and drop state will be restored after initialization
                }
            }
            
            // Initialize sortable after DOM is ready
            setTimeout(initializeSortable, 100);
            
        });
    </script>
    
    <style>
        .application-container {
            background: rgba(255, 255, 255, 0.02);
            backdrop-filter: blur(5px);
            border: 1px solid rgba(255, 215, 0, 0.2);
            border-radius: 15px;
            padding: 4rem;
            max-width: 1000px;
            overflow: hidden;
            word-wrap: break-word;
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.1);
        }
        
        .question-container {
            margin: 2rem 0;
        }
        
        
        .essay-editor {
            min-height: 200px;
            max-height: 400px;
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            font-size: 14px;
            line-height: 1.5;
            background: white;
            border: 1px solid #ddd;
            border-radius: 5px;
        }
        
        .EasyMDEContainer {
            border-radius: 5px;
            overflow: hidden;
        }
        
        .EasyMDEContainer .CodeMirror {
            min-height: 200px;
            max-height: 400px;
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            font-size: 14px;
            line-height: 1.5;
        }
        
        .EasyMDEContainer .editor-toolbar {
            background: #f8f9fa;
            border-bottom: 1px solid #ddd;
        }
        
        .EasyMDEContainer .editor-toolbar button {
            color: #333;
        }
        
        .EasyMDEContainer .editor-toolbar button:hover {
            background: #e9ecef;
            border-color: #adb5bd;
        }
        
        .EasyMDEContainer .editor-toolbar button.active {
            background: #e3f2fd;
            border-color: #2196f3;
            color: #1976d2;
        }
        
        .radio {
            position: relative;
            display: inline-flex;
            align-items: center;
            padding: 0px 0 18px 30px;
            cursor: pointer;
            font-size: 16px;
            line-height: 1.2;
            min-height: 24px;
        }
        
        .radio input[type="radio"] {
            position: absolute;
            opacity: 0;
            cursor: pointer;
        }
        
        .radio .checkmark {
            position: absolute;
            top: 0;
            left: 0;
            height: 20px;
            width: 20px;
            background-color: transparent;
            border: 2px solid #dbdbdb;
            border-radius: 50%;
            transition: all 0.3s ease;
        }
        
        .radio:hover input ~ .checkmark {
            border-color: #3273dc;
        }
        
        .radio input:checked ~ .checkmark {
            background-color: #3273dc;
            border-color: #3273dc;
        }
        
        .radio .checkmark:after {
            content: "";
            position: absolute;
            display: none;
        }
        
        .radio input:checked ~ .checkmark:after {
            display: block;
        }
        
        .radio .checkmark:after {
            top: 4px;
            left: 4px;
            width: 8px;
            height: 8px;
            border-radius: 50%;
            background: white;
        }
        
        .checkbox {
            position: relative;
            display: inline-flex;
            align-items: center;
            padding: 6px 30px 8px 30px;
            margin-bottom: 10px;
            cursor: pointer;
            font-size: 16px;
            line-height: 1.5;
            min-height: 24px;
        }
        
        .checkbox input[type="checkbox"] {
            position: absolute;
            opacity: 0;
            cursor: pointer;
        }
        
        .checkbox .checkmark {
            position: absolute;
            top: 8px;
            left: 0;
            height: 20px;
            width: 20px;
            background-color: transparent;
            border: 2px solid #dbdbdb;
            border-radius: 3px;
            transition: all 0.3s ease;
        }
        
        .checkbox:hover input ~ .checkmark {
            border-color: #3273dc;
        }
        
        .checkbox input:checked ~ .checkmark {
            background-color: #3273dc;
            border-color: #3273dc;
        }
        
        .checkbox .checkmark:after {
            content: "";
            position: absolute;
            display: none;
        }
        
        .checkbox input:checked ~ .checkmark:after {
            display: block;
        }
        
        .checkbox .checkmark:after {
            left: 6px;
            top: 2px;
            width: 5px;
            height: 10px;
            border: solid white;
            border-width: 0 3px 3px 0;
            transform: rotate(45deg);
        }
        
        
        .preference-form {
            background: rgba(255, 255, 255, 0.03);
            border-radius: 10px;
            padding: 1.5rem;
            margin: 1rem 0;
        }
        
        .checkbox-list {
            max-height: 300px;
            overflow-y: auto;
        }
        
        .sortable-list {
            min-height: 200px;
            border: 2px dashed rgba(255, 255, 255, 0.3);
            border-radius: 5px;
            padding: 10px;
        }
        
        .sortable-list.no-main-category {
            border-color: rgba(255, 100, 100, 0.5);
            background: rgba(255, 100, 100, 0.05);
        }
        
        .empty-placeholder {
            padding: 20px;
            text-align: center;
            color: rgba(255, 255, 255, 0.6);
            font-style: italic;
        }
        
        .sortable-item {
            background: rgba(255, 255, 255, 0.1);
            border: 1px solid rgba(255, 255, 255, 0.7);
            border-radius: 5px;
            padding: 10px;
            margin: 5px 0;
            cursor: move;
            display: flex;
            align-items: center;
            color: white;
        }
        
        .drag-handle {
            margin-right: 10px;
            cursor: grab;
            color: rgba(255, 255, 255, 0.7);
            user-select: none;
            font-size: 16px;
            line-height: 1;
        }
        
        .drag-handle:active {
            cursor: grabbing;
        }
        
        .sortable-ghost {
            opacity: 0.4;
        }
        
        .sortable-chosen {
            background: rgba(255, 255, 255, 0.2);
        }
        
        .sortable-drag {
            background: rgba(255, 255, 255, 0.3);
        }
        
        /* Countdown Timer Styles */
        .countdown-container {
            margin-top: 15px;
        }
        
        .countdown-timer {
            display: flex;
            justify-content: center;
            gap: 20px;
            margin-top: 10px;
            flex-wrap: wrap;
        }
        
        .countdown-item {
            display: flex;
            flex-direction: column;
            align-items: center;
            background: rgba(255, 255, 255, 0.1);
            border-radius: 8px;
            padding: 15px 10px;
            min-width: 70px;
            border: 1px solid rgba(255, 255, 255, 0.2);
        }
        
        .countdown-number {
            font-size: 24px;
            font-weight: bold;
            color: #fff;
            line-height: 1;
        }
        
        .countdown-label {
            font-size: 12px;
            color: rgba(255, 255, 255, 0.8);
            margin-top: 5px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        
        @media (max-width: 768px) {
            .countdown-timer {
                gap: 10px;
            }
            
            .countdown-item {
                min-width: 60px;
                padding: 12px 8px;
            }
            
            .countdown-number {
                font-size: 20px;
            }
        }
        
    </style>
    
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const countdownElement = document.getElementById('countdown');
            if (!countdownElement) return;
            
            const endTime = new Date(countdownElement.dataset.endTime).getTime();
            
            function updateCountdown() {
                const now = new Date().getTime();
                const timeLeft = endTime - now;
                
                if (timeLeft <= 0) {
                    // Countdown finished
                    document.getElementById('days').textContent = '0';
                    document.getElementById('hours').textContent = '0';
                    document.getElementById('minutes').textContent = '0';
                    document.getElementById('seconds').textContent = '0';
                    
                    // Optionally reload the page to show updated status
                    setTimeout(() => {
                        window.location.reload();
                    }, 1000);
                    return;
                }
                
                // Calculate time units
                const days = Math.floor(timeLeft / (1000 * 60 * 60 * 24));
                const hours = Math.floor((timeLeft % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
                const minutes = Math.floor((timeLeft % (1000 * 60 * 60)) / (1000 * 60));
                const seconds = Math.floor((timeLeft % (1000 * 60)) / 1000);
                
                // Update the display
                document.getElementById('days').textContent = days.toString().padStart(2, '0');
                document.getElementById('hours').textContent = hours.toString().padStart(2, '0');
                document.getElementById('minutes').textContent = minutes.toString().padStart(2, '0');
                document.getElementById('seconds').textContent = seconds.toString().padStart(2, '0');
            }
            
            // Update immediately and then every second
            updateCountdown();
            setInterval(updateCountdown, 1000);
        });
    </script>
</x-layout>