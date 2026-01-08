<?php

namespace App\Console\Commands;

use App\Models\Entry;
use Http;
use Illuminate\Console\Command;
use Illuminate\Support\Sleep;
use Storage;

class ImportAnimethemes extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:import-animethemes {year}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Import themes by year ';

    // Executes query for the current awards year
    public const CURRENT_YEAR_QUERY = 'query ThemesByYear ($page: Int, $year: Int) {
  animePagination(year: $year, page: $page) {
    paginationInfo {
      currentPage
      count
      total
      lastPage
      hasMorePages
    }
    data {
      resources (site:ANILIST) {
        nodes {
        		link
        	}
        }
      name
      mediaFormat
      season
      year
      animethemes {
        slug
        song {
          id
          title
        }
        animethemeentries{
          id
          version
          videos{
            nodes{
              link
            }
          }
        }
      }
    }
    paginationInfo {
      hasMorePages
    }
  }
  }';

    // Executes query for the prior fall to the current awards year, need to manually check which entries are eligible for current year
    public const PREVIOUS_FALL_QUERY = 'query ThemesByPreviousYear ($page: Int, $year: Int) {
  animePagination(year: $year, season: FALL, page: $page) {
    paginationInfo {
      currentPage
      count
      total
      lastPage
      hasMorePages
    }
    data {
      resources (site:ANILIST) {
        nodes {
        		link
        	}
        }
      name
      mediaFormat
      season
      year
      animethemes {
        slug
        song {
          id
          title
        }
        animethemeentries{
          id
          version
          videos{
            nodes{
              link
            }
          }
        }
      }
    }
    paginationInfo {
      hasMorePages
    }
  }
  }';

    // Only useful for the 2025 year, check the eligible entries
    public const ONE_PIECE_QUERY = 'query ONEPIECE ($page: Int, $year: Int) {
  animePagination(name: "One Piece", year: $year, page: $page) {
    paginationInfo {
      currentPage
      count
      total
      lastPage
      hasMorePages
    }
    data {
      resources (site:ANILIST) {
        nodes {
        		link
        	}
        }
      name
      mediaFormat
      season
      year
      animethemes {
        slug
        song {
          id
          title
        }
        animethemeentries{
          id
          version
          videos{
            nodes{
              link
            }
          }
        }
      }
    }
    paginationInfo {
      hasMorePages
    }
  }
  }';

    // Check if there are eligible Detective Conan entries
    public const DETECTIVE_CONAN_QUERY = 'query DETECTIVECONAN ($page: Int, $year: Int) {
  animePagination(name: "Detective Conan", year: $year, page: $page){
    paginationInfo {
      currentPage
      count
      total
      lastPage
      hasMorePages
    }
    data {
      resources (site:ANILIST) {
        nodes {
        		link
        	}
        }
      name
      mediaFormat
      season
      year
      animethemes {
        slug
        song {
          id
          title
        }
        animethemeentries{
          id
          version
          videos{
            nodes{
              link
            }
          }
        }
      }
    }
    paginationInfo {
      hasMorePages
    }
  }
  }';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        // Set the condition for the year(s) being imported
        $year = intval($this->argument('year'));
        $prioryear = $year - 1;

        $this->info("Import themes according to year {$year}");
        // $this->getAnimeThemes($year);

        // $this->themesLoop($year, $year, ImportAnimethemes::CURRENT_YEAR_QUERY);

        //$this->themesLoop($year, $prioryear, ImportAnimethemes::PREVIOUS_FALL_QUERY);

        // Only really needed for 2025 year
        $this->themesLoop($year, 1999, ImportAnimethemes::ONE_PIECE_QUERY); //1999 is One Piece's year

        $this->themesLoop($year, 1996, ImportAnimethemes::DETECTIVE_CONAN_QUERY); //1996 is Detective Conan's year
    }

    private function themesLoop($year, $query_year, $query)
    {
        $lastPage = false;
        $currentpage = 1;

        $seconds_between_requests = 5;

        while (! $lastPage) {
            $currentYearEntries = Http::post('https://graphql.animethemes.moe/', [
                'query' => $query,
                'variables' => [
                    // 'hasMorePages' =>
                    'year' => $query_year,
                    'page' => $currentpage,
                ],
            ]);

            $animePagination = ($currentYearEntries->json())['data']['animePagination']['data'];

            foreach ($animePagination as $anime) {
                $anime_name = $anime['name'];
                $anilist_id = substr($anime['resources']['nodes'][0]['link'], 25);
                $parent = Entry::where('anilist_id', $anilist_id)->first();
                $animethemes = $anime['animethemes'];
                $this->info('Currently importing themes for '.$anime_name);

                foreach ($animethemes as $theme) {
                    $theme_slug = $theme['slug'];
                    // If theme title doesn't exist then assign it as the anime name plus its slug value
                    $theme_title = $theme['song']['title'] ?? $anime_name.' '.$theme_slug;
                    $themeentries = $theme['animethemeentries'];

                    foreach ($themeentries as $entry) {
                        $entry_version = $entry['version'];
                        // If there are no video links available, then setting the link to NULL
                        $entry_link = $entry['videos']['nodes'] ? $entry['videos']['nodes'][0]['link'] : null;
                        // Pair entry version if it exists with theme slug
                        $theme_version = $theme_slug.($entry_version ? 'v'.$entry_version : '');
                        $new_entry = Entry::updateOrCreate(
                            [
                                'name' => $theme_title,
                                'theme_version' => $theme_version,
                                'anilist_id' => $anilist_id,
                            ],
                            [
                                'year' => $year,
                                'type' => 'theme',
                                'parent_id' => ($parent ? $parent->id : null),
                                'link' => $entry_link,
                            ],
                        );
                    }
                }
            }

            $currentpage++;

            $paginationInfo = ($currentYearEntries->json())['data']['animePagination']['paginationInfo'];
            $lastPage = ! ($paginationInfo['hasMorePages']);
            // Sleep::for($seconds_between_requests)->second();

            // local dev unit testing
            $this->info('Query '.($currentpage - 1).' Executed');
            // Storage::disk('local')->put('themes-response-2025-'.$currentpage.'.json', var_export($currentYearEntries->json(), true));
            // Storage::disk('local')->put('themes-response-2025-'.$currentpage.'.json', var_export($animePagination, true));
        }
    }

    // Testing different functions
    // private function priorThemesLoop($year)
    // {
    //     $lastPage = false;
    //     $currentpage = 1;

    //     $seconds_between_requests = 5;

    //     while (! $lastPage) {
    //         // Shows that aired in prior year with themes extending into current year
    //         $extendedEntries = Http::post('https://graphql.animethemes.moe/', [
    //             'query' => ImportAnimethemes::PREVIOUS_FALL_QUERY,
    //             'variables' => [
    //                 // 'hasMorePages' =>
    //                 'year' => $prioryear,
    //                 'page' => $currentpage,
    //             ],
    //         ]);
    //         $currentpage++;

    //         $paginationInfo = ($extendedEntries->json())['data']['animePagination']['paginationInfo'];
    //         $lastPage = ! ($paginationInfo['hasMorePages']);
    //         Sleep::for($seconds_between_requests)->second();

    //         // local dev unit testing
    //         $this->info('Query '.($currentpage - 1).' Executed');
    //         // Storage::disk('local')->put('themes-response-2024-'.$currentpage.'.json', var_export($extendedEntries->json(), true));
    //     }
    // }
}
