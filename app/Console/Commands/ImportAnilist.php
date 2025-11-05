<?php

namespace App\Console\Commands;

use App\Jobs\DownloadImage;
use App\Models\Entry;
use App\Models\ItemName;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Sleep;
use Storage;

class ImportAnilist extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:import-anilist {year} {--queue} {--chars} {--vas}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Import Anime, Chars and VAs from Anilist by year';

    public const ANIME_QUERY = 'query ($page: Int, $perPage: Int, $edlow: FuzzyDateInt, $edhigh: FuzzyDateInt) {
        Page(page: $page, perPage: $perPage) {
            pageInfo {
                currentPage,
                hasNextPage,
                perPage,
                total
            }
            media(
                type: ANIME
                endDate_greater: $edlow
                endDate_lesser: $edhigh
            ) {
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

    public const FULL_QUERY = 'query ($page: Int, $perPage: Int, $edlow: FuzzyDateInt, $edhigh: FuzzyDateInt) {
        Page(page: $page, perPage: $perPage) {
            pageInfo {
                currentPage,
                hasNextPage,
                perPage,
                total
            }
            media(
                type: ANIME
                endDate_greater: $edlow
                endDate_lesser: $edhigh
            ) {
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
                characters {
                  edges {
                    node {
                      id
                      image {
                        large
                      }
                      name {
                        full
                      }
                      siteUrl
                    }
                    voiceActors(language: JAPANESE) {
                      id
                      name {
                        full
                      }
                      image {
                        large
                      }
                      siteUrl
                    }
                  }
                }
            }
        }
    }';

    public function queueimgdownload(string $filename, string $url, bool $asbackground = false)
    {

        // TODO: Take argument for checking for downloading
        if ($asbackground) {
            // Download Image as Background Job
            DownloadImage::dispatch($filename, $url);
        } else {
            // Download Image Procedurally
            $imageContent = file_get_contents($url);
            Storage::disk('public')->put($filename, $imageContent);
        }
    }

    /**
     * Execute the console command.
     */
    public function handle()
    {
        // Read year from arg

        $year = $this->argument('year');

        // Options for importing Characters and VAs in one go
        $import_chars = $this->option('chars');
        $import_vas = $this->option('vas');

        if($import_vas && !$import_chars) {
            $this->info('Error: Cannnot import VAs without Characters');
            return;
        }

        $nextyear = $year + 1;
        $this->info('Importing shows'
            .($import_chars?', characters':'')
            .($import_vas?', VAs':'')
            .' for year '.$year.' upto year '.$nextyear);

        //  Arg for downloading as background job
        $imgdownload_as_bgjob = $this->option('queue');
        if($imgdownload_as_bgjob) {
            $this->info('Queuing image downloads as background jobs');
        }
        // Query Params

        // Current date filter; beginning to end of $year
        $edlow = $year * 10000;
        $edhigh = $nextyear * 10000;

        $entries_per_page = 50;
        $seconds_between_requests = 5;

        // Loop Variables
        $lastPage = false;
        $currentpage = 1;
        while (! $lastPage) {

            $response = Http::post('https://graphql.anilist.co', [
                'query' => ImportAnilist::FULL_QUERY,
                'variables' => [
                    'page' => $currentpage,
                    'perpage' => $entries_per_page,
                    'edlow' => $edlow,
                    'edhigh' => $edhigh,
                ],
            ]);

            $pageinfo = ($response->json())['data']['Page']['pageInfo'];
            $showlist = ($response->json())['data']['Page']['media'];

            // For dev, save responses to json
            Storage::disk('local')->put('anilist-response-'.$currentpage.'.json', var_export(($response->json())['data']['Page'], true));

            $this->info('Queried page '.$currentpage.' : Status '.($response->ok() ? 'ok' : 'error'));

            foreach ($showlist as $show) {
                // Format filter isn't applied to show query, since Type = anime was enough
                /*
                if($show['format']=='MANGA' || $show['format']=='NOVEL' || $show['format']=='ONE_SHOT')
                    $this->info('Format error detected');
                */

                $this->info('Importing: '.$show['title']['romaji']);
                $characters = $show['characters']['edges'];

                $show_img_filename = '/entry/anilist-'.$show['id'].'.jpg';
                $show_img_url = $show['coverImage']['large'];
                $this->queueimgdownload($show_img_filename, $show_img_url, $imgdownload_as_bgjob);

                // New show Entry
                $newshow = Entry::updateOrCreate(
                    [
                        'type' => 'anime',
                        'anilist_id' => $show['id'],
                    ],
                    [
                        'name' => $show['title']['romaji'],
                        'year' => $year,
                        'image' => $show_img_filename,
                    ]
                );

                // New anime entry romaji name
                ItemName::updateOrCreate(
                    [
                        'entry_id' => $newshow->id,
                        'language_code' => 'jp',
                    ],
                    [
                        'name' => $show['title']['romaji'],
                    ]
                );

                // New anime entry english name
                if (isset($show['title']['english'])) {
                    ItemName::updateOrCreate(
                        [
                            'entry_id' => $newshow->id,
                            'language_code' => 'en',
                        ],
                        [
                            'name' => $show['title']['english'],
                        ]
                    );
                }

                // New anime entry synonyms
                foreach ($show['synonyms'] as $skey => $svalue) {
                    ItemName::updateOrCreate(
                        [
                            'entry_id' => $newshow->id,
                            'language_code' => 'alternate',
                            'name' => $svalue,
                        ],
                        []
                    );
                }

                if(!$import_chars)
                    break;

                foreach ($characters as $charedge) {

                    $character = $charedge['node'];
                    $char_img_filename = 'entry/'.'anilist-char-'.$character['id'].'.jpg';
                    $charimg_url = $character['image']['large'];

                    $this->info('Importing character: '.$character['name']['full']);

                    $this->queueimgdownload($char_img_filename, $charimg_url, $imgdownload_as_bgjob);

                    $newchar = Entry::updateOrCreate(
                        [
                            'type' => 'char',
                            'anilist_id' => $character['id'],
                            'parent_id' => $newshow['id'],
                        ],
                        [
                            'name' => $character['name']['full'],
                            'year' => $year,
                            'image' => $char_img_filename,
                        ]
                    );

                    if(!$import_vas)
                        break;

                    if (isset($charedge['voiceActors'][0])) {
                        $va = $charedge['voiceActors'][0];

                        $this->info('Importing VA: '.$va['name']['full']);


                        $newva = Entry::updateOrCreate(
                            [
                                'type' => 'va',
                                'anilist_id' => $va['id'],
                                'parent_id' => $newchar->id,
                            ],
                            [
                                'name' => $va['name']['full'],
                                'year' => $year,
                                'image' => $char_img_filename,
                            ]
                        );
                    }
                }

            }

            $currentpage++;
            $lastPage = ! ($pageinfo['hasNextPage']);
            Sleep::for($seconds_between_requests)->second();
        }
    }
}
