<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Sleep;

use App\Models\Entry;
use App\Models\ItemName;

use File;
use Http;
use Storage;


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
        $content = File::get(app_path("Console\Commands\archive\\results{$year}.json"));
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
                Storage::disk('public')->put('/entry/anlist-' . $show['id'] . '.jpg', $imageContent);

                $newentry = Entry::updateOrCreate(
                        [
                            'type' => 'anime',
                            'anilist_id' => $show['id'],
                        ],
                        [
                            'name' => $show['title']['romaji'],
                            'year' => $year,
                            'image' => 'entry/' . 'anlist-' . $show['id'] . '.jpg'
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
            Storage::disk('public')->put('/entry/anlist-char-' . $charres['id'] . '.jpg', $imageContent);

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
                        'image' => 'entry/' . 'anlist-char-' . $charres['id'] . '.jpg',
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
                        'image' => 'entry/' . 'anlist-char-' . $charres['id'] . '.jpg',
                        'parent_id' => $newentry->id
                    ]
                );
            }

            Sleep::for(5)->second();
        }

        
        
    }
}
