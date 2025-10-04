<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Sleep;

use App\Models\Entry;
use App\Models\ItemName;
use App\Models\Category;
use App\Models\CategoryInfo;
use App\Models\Result;

use File;
use Http;
use Storage;
use Exception;


class ImportArchive extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:import-archive {year}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Import Old Data';

    public const ANILIST_ANIME_QUERY = 'query ($id: [Int], $page: Int, $perPage: Int) {
        Page (page: $page, perPage: $perPage) {
          pageInfo {
            total
            currentPage
            lastPage
          }
          results: media(type: ANIME, id_in: $id) {
            id
            format
            startDate {
              year
            }
            title {
              romaji
              english
            }
            synonyms
            coverImage {
              large
              extraLarge
            }
            siteUrl
            idMal
          }
        }
      }';


    public const ANILIST_CHAR_QUERY = 'query ($id: [Int], $page: Int, $perPage: Int) {
        Page(page: $page, perPage: $perPage) {
          pageInfo {
            total
            currentPage
            lastPage
          }
          results: characters(id_in: $id) {
            id
            name {
              full
              alternative
            }
            image {
              large
            }
            media(sort: [START_DATE], type: ANIME, page: 1, perPage: 1) {
              nodes {
                id
                title {
                  romaji
                  english
                }
              }
              edges {
                id
                node {
                  id
                }
                characterRole
                voiceActors(language: JAPANESE) {
                  id
                  name {
                    full
                    alternative
                  }
                }
              }
            }
            siteUrl
          }
        }
      }';
    
    /**
     * Execute the console command.
     */
     public function handle()
    {
        $year = $this->argument('year');
        $content = File::get(app_path("Console/Commands/archive/git coresults{$year}.json"));
        $json = json_decode(json: $content, associative: true);
        $anime = [];
        $char = [];
        $va = [];

        foreach ($json['anime'] as $key => $value) {
            if ($year == 2016 && $key < 9010){
                continue;
            }
            $anime[] = $key;
            //$this->info($key . ' - ' . $value);
        }

        for ($i = 0; $i <= ceil(count($anime)/50); $i++){
            $response = Http::post('https://graphql.anilist.co', [
                'query' => ImportArchive::ANILIST_ANIME_QUERY,
                'variables' => [
                    'id' => $anime,
                    'page' => $i,
                    'perPage' => 50,
                ],
            ]);

            $response_shows = ($response->json())['data']['Page']['results'];
            foreach ($response_shows as $show){
                $this->info("Importing: " . $show['title']['romaji']);
                $imageContent = file_get_contents($show['coverImage']['large']);
                Storage::disk('public')->put('/entry/anilist-' . $show['id'] . '.jpg', $imageContent);

                $newentry = Entry::updateOrCreate(
                        [
                            'type' => 'anime',
                            'anilist_id' => $show['id'],
                        ],
                        [
                            'name' => $show['title']['romaji'],
                            'year' => $year,
                            'image' => 'entry/' . 'anilist-' . $show['id'] . '.jpg'
                        ]
                );

                ItemName::updateOrCreate(
                    [
                        'entry_id' => $newentry->id,
                        'language_code' => 'jp',
                    ],
                    [
                        'name' => $show['title']['romaji']
                    ]
                );

                if (isset($show['title']['english'])){
                    ItemName::updateOrCreate(
                        [
                            'entry_id' => $newentry->id,
                            'language_code' => 'en',
                        ],
                        [
                            'name' => $show['title']['english']
                        ]
                    );
                }

                foreach ($show['synonyms'] as $skey => $svalue){
                    ItemName::updateOrCreate(
                        [
                            'entry_id' => $newentry->id,
                            'language_code' => 'alternate',
                            'name' => $svalue
                        ],
                        []
                    );
                }
            }
            Sleep::for(5)->second();
        }

        foreach ($json['characters'] as $key => $value) {
            $response = Http::post('https://graphql.anilist.co', [
                'query' => ImportArchive::ANILIST_CHAR_QUERY,
                'variables' => [
                    'id' => [$key],
                    'page' => 0,
                    'perPage' => 50,
                ],
            ]);
            // $this->info(print_r(($response->json())['data']['Page']['results'], true));

            $charres = ($response->json())['data']['Page']['results'][0];

            $this->info("Importing: " . $charres['name']['full']);
            $imageContent = file_get_contents($charres['image']['large']);
            Storage::disk('public')->put('/entry/anilist-char-' . $charres['id'] . '.jpg', $imageContent);

            $parentanime = Entry::firstWhere('anilist_id', $charres['media']['nodes'][0]['id']);
            $parent_id = null;
            if ($parentanime != null){
                $parent_id = $parentanime->id;
            }

            $newentry = Entry::updateOrCreate(
                    [
                        'type' => 'char',
                        'anilist_id' => $charres['id'],
                    ],
                    [
                        'name' => $charres['name']['full'],
                        'year' => $year,
                        'image' => 'entry/' . 'anilist-char-' . $charres['id'] . '.jpg',
                        'parent_id' => $parent_id
                    ]
            );

            if (isset( $charres['media']['edges'][0]) && isset($charres['media']['edges'][0]['voiceActors'][0])){
                $vares = $charres['media']['edges'][0]['voiceActors'][0];
                $newva = Entry::updateOrCreate(
                    [
                        'type' => 'va',
                        'anilist_id' => $vares['id'],
                    ],
                    [
                        'name' => $vares['name']['full'],
                        'year' => $year,
                        'image' => 'entry/' . 'anilist-char-' . $charres['id'] . '.jpg',
                        'parent_id' => $newentry->id
                    ]
                );
            }

            Sleep::for(5)->second();
        }

        foreach ($json['themes'] as $key => $value) {
          
          // print_r($value);
          $split = explode('-',$value);
          $anime = trim($split[0]);
          $animesplit = explode(' ', $anime);
          $themeversion = $animesplit[count($animesplit)-1];
          $song = trim($split[1]);
          $response = Http::get('https://api.animethemes.moe/search', [
              'q' => $song,
              'fields[search]' => 'animethemes',
              'include[animetheme]' => 'animethemeentries.videos,anime.images,song.artists,group',
              'page[limit]' => 1
          ]);

          $res1 = $response->json();
          if (isset($res1['search']['animethemes'][0])){
            $anime = $res1['search']['animethemes'][0]['anime']['name'];
          } else {
            array_splice( $animesplit, -1 );
            $anime = implode(' ', $animesplit);
          }
          
          if ($anime != null && $anime != ''){

            $res2 = Http::get('https://api.animethemes.moe/search', [
              'q' => $anime,
              'fields[search]' => 'anime',
              'include[anime]' => 'resources',
              'page[limit]' => 1
            ]);

            //print_r($res2->json());

            $anilist = array_filter($res2['search']['anime'][0]['resources'], function($var){
              return $var['site'] == 'AniList';
            });

            $anilist = array_pop($anilist);

            $anilistid = $anilist['external_id'];
            // print_r($anilist);

            $this->info($anime . " - " . $anilistid);

            $parent = Entry::firstWhere(['anilist_id' => $anilistid, 'type' => 'anime']);
            $parent_id = 0;
            if ($parent != null){
                $parent_id = $parent->id;
            }

            $newva = Entry::updateOrCreate(
                [
                    'type' => 'theme',
                    'anilist_id' => $anilistid,
                    'name' => $song,
                    'theme_version' => $themeversion,
                    'year' => $year,
                ],
                [
                    'image' => 'entry/' . 'anilist-theme-' . $anilistid . '.jpg',
                    'parent_id' => $parent_id
                ]
            );

          }

          Sleep::for(5)->second();
        }       
        
        // Import categories and results
        $this->importCategoriesAndResults($year, $json);
    }

    /**
     * Import categories and results from the JSON data
     */
    private function importCategoriesAndResults($year, $json)
    {
        $this->info("Importing categories and results for year {$year}...");
        
        foreach ($json['sections'] as $section) {
            $this->info("Processing section: {$section['name']}");
            
            foreach ($section['awards'] as $awardIndex => $award) {
                $this->info("Processing award: {$award['name']}");
                
                // Create category
                $category = Category::updateOrCreate(
                    [
                        'year' => $year,
                        'name' => $award['name'],
                        'type' => $section['slug']
                    ],
                    [
                        'order' => $awardIndex + 1
                    ]
                );
                
                // Create category info
                CategoryInfo::updateOrCreate(
                    [
                        'category_id' => $category->id
                    ],
                    [
                        'description' => $award['blurb'] ?? '',
                        'sotc_blurb' => $section['blurb'] ?? ''
                    ]
                );
                
                // Import results for this category
                $this->importResultsForCategory($year, $category, $award);
            }
        }
    }

    /**
     * Import results for a specific category
     */
    private function importResultsForCategory($year, $category, $award)
    {
        if (!isset($award['nominees']) || empty($award['nominees'])) {
            $this->warn("No nominees found for category: {$category->name}");
            return;
        }
        
        // Sort nominees by jury rank (lower number = better rank)
        $nominees = $award['nominees'];
        usort($nominees, function($a, $b) {
            return $a['jury'] <=> $b['jury'];
        });
        
        foreach ($nominees as $index => $nominee) {
            // Find the entry by anilist_id
            $entry = Entry::where('anilist_id', $nominee['id'])->first();
            
            if (!$entry) {
                $this->warn("Entry not found for anilist_id: {$nominee['id']}");
                continue;
            }
            
            // Determine entry name and image (only use alt if not empty)
            $entryName = !empty($nominee['altname']) ? $nominee['altname'] : $entry->name;
            $entryImage = !empty($nominee['altimg']) ? $nominee['altimg'] : $entry->image;
            
            // If altimg is a URL, download and store it
            if (!empty($nominee['altimg']) && filter_var($nominee['altimg'], FILTER_VALIDATE_URL)) {
                try {
                    $imageContent = file_get_contents($nominee['altimg']);
                    $filename = 'entry/result-' . $nominee['id'] . '-' . $category->id . '.jpg';
                    Storage::disk('public')->put($filename, $imageContent);
                    $entryImage = $filename;
                } catch (Exception $e) {
                    $this->warn("Failed to download image for entry {$nominee['id']}: " . $e->getMessage());
                    $entryImage = $entry->image; // Fallback to original image
                }
            }
            
            // Parse staff credits
            $staffCredits = null;
            if (!empty($nominee['staff'])) {
                $staffCreditsArray = $this->parseStaffCredits($nominee['staff']);
                $staffCredits = $staffCreditsArray ? json_encode($staffCreditsArray) : null;
            }
            
            // Create result
            Result::updateOrCreate(
                [
                    'year' => $year,
                    'category_id' => $category->id,
                    'entry_id' => $entry->id
                ],
                [
                    'name' => $entryName,
                    'image' => $entryImage,
                    'jury_rank' => $nominee['jury'],
                    'public_rank' => $nominee['public'],
                    'description' => $nominee['writeup'] ?? '',
                    'staff_credits' => $staffCredits
                ]
            );
            
            $this->info("Imported result: {$entryName} (Jury: {$nominee['jury']}, Public: {$nominee['public']})");
        }
    }

    /**
     * Parse staff credits from string format to key-value array format
     */
    private function parseStaffCredits($staffString)
    {
        if (empty($staffString)) {
            return null;
        }
        
        // Split by newlines and create structured data
        $lines = explode("\n", trim($staffString));
        $credits = [];
        
        foreach ($lines as $line) {
            $line = trim($line);
            if (empty($line)) continue;
            
            // Check if line contains "Director:" or similar
            if (strpos($line, 'Director:') !== false) {
                $credits[] = [
                    'role' => 'Director',
                    'name' => trim(str_replace('Director:', '', $line))
                ];
            } elseif (strpos($line, 'Studio:') !== false || strpos($line, 'Production:') !== false) {
                $role = strpos($line, 'Studio:') !== false ? 'Studio' : 'Production';
                $credits[] = [
                    'role' => $role,
                    'name' => trim(str_replace(['Studio:', 'Production:'], '', $line))
                ];
            } elseif (strpos($line, 'Writer:') !== false) {
                $credits[] = [
                    'role' => 'Writer',
                    'name' => trim(str_replace('Writer:', '', $line))
                ];
            } elseif (strpos($line, 'Producer:') !== false) {
                $credits[] = [
                    'role' => 'Producer',
                    'name' => trim(str_replace('Producer:', '', $line))
                ];
            } else {
                // Assume it's a studio/production company if no role is specified
                $credits[] = [
                    'role' => 'Studio',
                    'name' => $line
                ];
            }
        }
        
        return !empty($credits) ? $credits : null;
    }
}
