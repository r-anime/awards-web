<?php

namespace App\Filament\Admin\Resources\EntryResource\Pages;

use App\Filament\Admin\Resources\EntryResource;
use App\Models\Entry;
use App\Models\Category;
use App\Models\CategoryEligible;
use App\Jobs\DownloadImage;
use Filament\Actions;
use Filament\Forms;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;

use Illuminate\Database\Eloquent\Builder;
use Livewire\Attributes\On;

class ListEntries extends ListRecords
{
    protected static string $resource = EntryResource::class;

    protected function getHeaderActions(): array
    {
        $selectedType = session('selected-type-filter');
        $selectedTypeName = $selectedType ? ucfirst($selectedType) : 'All Types';

        $typeOptions = [
            'anime' => 'Anime',
            'char' => 'Characters', 
            'va' => 'Voice Actors',
            'theme' => 'Themes',
        ];

        $dropdownActions = [];
        
        // Add "All Types" option
        $dropdownActions[] = Actions\Action::make('all_types')
            ->label('All Types')
            ->action(function () {
                session(['selected-type-filter' => null]);
                $this->dispatch('type-filter-updated');
            })
            ->icon('heroicon-o-x-mark');
        
        // Add individual type options
        foreach ($typeOptions as $type => $label) {
            $dropdownActions[] = Actions\Action::make('type_' . $type)
                ->label($label)
                ->action(function () use ($type) {
                    session(['selected-type-filter' => $type]);
                    $this->dispatch('type-filter-updated');
                })
                ->icon('heroicon-o-funnel');
        }

        return [
            Actions\ActionGroup::make($dropdownActions)
                ->label($selectedTypeName)
                ->icon('heroicon-o-funnel')
                ->color('gray')
                ->outlined()
                ->button(),
            Actions\CreateAction::make(),
            Actions\Action::make('importYoutubeShorts')
                ->label('Import YouTube Shorts')
                ->icon('heroicon-o-arrow-down-tray')
                ->color('success')
                ->form([
                    Forms\Components\Textarea::make('shorts_list')
                        ->label('Shorts List')
                        ->helperText('Enter comma-delimited list of title,youtube link (one per line)')
                        ->required()
                        ->rows(10)
                        ->columnSpanFull(),
                    Forms\Components\Checkbox::make('import_into_category')
                        ->label('Import into category')
                        ->default(false)
                        ->live(),
                    Forms\Components\Select::make('category_id')
                        ->label('Category')
                        ->options(function () {
                            $currentYear = session('selected-year-filter') ?? intval(app('current-year'));
                            return Category::where('year', $currentYear)
                                ->orderBy('name')
                                ->pluck('name', 'id');
                        })
                        ->searchable()
                        ->visible(fn ($get) => $get('import_into_category'))
                        ->required(fn ($get) => $get('import_into_category')),
                ])
                ->action(function (array $data) {
                    $currentYear = session('selected-year-filter') ?? intval(app('current-year'));
                    $importIntoCategory = $data['import_into_category'] ?? false;
                    $categoryId = $data['category_id'] ?? null;
                    $shortsList = $data['shorts_list'] ?? '';
                    
                    // Get selected category if checkbox is checked
                    $selectedCategory = null;
                    if ($importIntoCategory && $categoryId) {
                        $selectedCategory = Category::find($categoryId);
                        
                        if (!$selectedCategory) {
                            Notification::make()
                                ->title('Warning')
                                ->body('Selected category not found. Entries will be created but not added to category.')
                                ->warning()
                                ->send();
                        }
                    }
                    
                    // Parse the input
                    $lines = explode("\n", $shortsList);
                    $parsedEntries = [];
                    $errors = [];
                    $timestamp = time();
                    
                    // Pre-parse all entries (validation phase)
                    foreach ($lines as $lineNumber => $line) {
                        $line = trim($line);
                        if (empty($line)) {
                            continue;
                        }
                        
                        // Parse title,youtube link
                        $parts = explode(',', $line, 2);
                        if (count($parts) !== 2) {
                            $errors[] = "Line " . ($lineNumber + 1) . ": Invalid format. Expected 'title,youtube link'";
                            continue;
                        }
                        
                        $title = trim($parts[0]);
                        $youtubeLink = trim($parts[1]);
                        
                        if (empty($title) || empty($youtubeLink)) {
                            $errors[] = "Line " . ($lineNumber + 1) . ": Title and YouTube link are required";
                            continue;
                        }
                        
                        // Extract YouTube video ID
                        $videoId = $this->extractYouTubeVideoId($youtubeLink);
                        if (!$videoId) {
                            $errors[] = "Line " . ($lineNumber + 1) . ": Invalid YouTube URL: {$youtubeLink}";
                            continue;
                        }
                        
                        // Generate thumbnail URL
                        $thumbnailUrl = "https://img.youtube.com/vi/{$videoId}/maxresdefault.jpg";
                        
                        // Generate unique image filename
                        $imageFilename = 'entry/youtube-short-' . $videoId . '-' . $timestamp . '-' . count($parsedEntries) . '.jpg';
                        
                        $parsedEntries[] = [
                            'title' => $title,
                            'youtubeLink' => $youtubeLink,
                            'videoId' => $videoId,
                            'thumbnailUrl' => $thumbnailUrl,
                            'imageFilename' => $imageFilename,
                        ];
                    }
                    
                    // Batch create entries using transaction
                    $created = 0;
                    $categoryEligiblesToCreate = [];
                    
                    if (!empty($parsedEntries)) {
                        try {
                            \DB::transaction(function () use ($parsedEntries, $currentYear, $importIntoCategory, $selectedCategory, &$created, &$categoryEligiblesToCreate) {
                                $now = now();
                                
                                // Create entries and collect IDs
                                foreach ($parsedEntries as $entryData) {
                                    $entry = Entry::create([
                                        'anilist_id' => -883,
                                        'type' => 'anime',
                                        'name' => $entryData['title'],
                                        'year' => $currentYear,
                                        'link' => $entryData['youtubeLink'],
                                        'image' => $entryData['imageFilename'],
                                    ]);
                                    
                                    $created++;
                                    
                                    // Queue thumbnail download
                                    DownloadImage::dispatch($entryData['imageFilename'], $entryData['thumbnailUrl']);
                                    
                                    // Prepare CategoryEligible record if needed
                                    if ($importIntoCategory && $selectedCategory) {
                                        $categoryEligiblesToCreate[] = [
                                            'category_id' => $selectedCategory->id,
                                            'entry_id' => $entry->id,
                                            'active' => true,
                                            'created_at' => $now,
                                            'updated_at' => $now,
                                        ];
                                    }
                                }
                                
                                // Batch insert category eligibles if any
                                if (!empty($categoryEligiblesToCreate)) {
                                    \DB::table('category_eligibles')->insertOrIgnore($categoryEligiblesToCreate);
                                }
                            });
                            
                        } catch (\Exception $e) {
                            $errors[] = "Error during batch creation: " . $e->getMessage();
                        }
                    }
                    
                    // Show notification
                    $message = "Successfully created {$created} entry/entries";
                    if (!empty($errors)) {
                        $message .= ". " . count($errors) . " error(s) occurred.";
                        Notification::make()
                            ->title('Import Complete with Errors')
                            ->body($message . "\n\nErrors:\n" . implode("\n", array_slice($errors, 0, 10)))
                            ->warning()
                            ->send();
                    } else {
                        Notification::make()
                            ->title('Import Successful')
                            ->body($message)
                            ->success()
                            ->send();
                    }
                    
                    // Refresh the table
                    $this->resetTable();
                }),
        ];
    }

    #[On('filter-year-updated')]
    public function refreshOnYearFilter()
    {
        $this->resetTable();
    }

    #[On('type-filter-updated')]
    public function refreshOnTypeFilter()
    {
        $this->resetTable();
    }
    
    /**
     * Extract YouTube video ID from various YouTube URL formats
     */
    private function extractYouTubeVideoId(string $url): ?string
    {
        // Remove any whitespace
        $url = trim($url);
        
        // Pattern for standard YouTube URLs: https://www.youtube.com/watch?v=VIDEO_ID
        if (preg_match('/(?:youtube\.com\/watch\?v=|youtu\.be\/|youtube\.com\/embed\/)([a-zA-Z0-9_-]{11})/', $url, $matches)) {
            return $matches[1];
        }
        
        // Pattern for YouTube Shorts: https://www.youtube.com/shorts/VIDEO_ID
        if (preg_match('/youtube\.com\/shorts\/([a-zA-Z0-9_-]{11})/', $url, $matches)) {
            return $matches[1];
        }
        
        return null;
    }
}
