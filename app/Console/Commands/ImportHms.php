<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Category;
use App\Models\HonorableMention;
use File;
use Storage;


class ImportHms extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:import-hms {year}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $current_year = intval($this->argument('year')); //Set the year for importing
        $year_range = range(2016, $current_year-1); //Set the list of years to import for archive

        // $this->checkYearError(2020); //checking the error on 2020 not returning hms

        foreach ($year_range as $year) {
            $json_file = File::get(app_path("Console/Commands/archive/results{$year}.json"));
            $json_data = json_decode($json_file, true);
            // $this->info('current year: '.$year);
            foreach ($json_data['sections'] as $data) {
                // $this->info(json_encode($data));
                $awards = $data['awards'];
                foreach ($awards as $key=>$value) {
                    $category_name = $value['name'];
                    // $this->info($category_name.': '.$year);
                    $db_category = Category::where('name', $category_name)
                                ->where('year', $year)
                                ->firstOrFail();
                    // $this->info(json_encode($value['hms']));
                    $hms = $value['hms'];
                    foreach ($hms as $hm) {
                        // $this->info(json_encode($hm));
                        $name = $hm['name'];
                        $category_id = $db_category->id;
                        $writeup = $hm['writeup'];

                        $new_hm = HonorableMention::updateOrCreate (
                            [
                                'name' => $name,
                                'year' => $year,
                                'category_id' => $category_id,
                            ],
                            [
                                'writeup' => $writeup,
                            ]
                        );
                    }

                }

            };

        };

    }

    //Private function used to test whether or not results appear for a certain year

    // private function checkYearError($year)
    // {
    //     $json_file = File::get(app_path("Console/Commands/archive/results{$year}.json"));
    //     $json_data = json_decode($json_file, true);
    //     foreach ($json_data['sections'] as $data)
    //         {
    //             $awards = $data['awards'];
    //             foreach ($awards as $key=>$value)
    //             {
    //                 $category_name = $value['name'];
    //                 $db_category = Category::where('name', $category_name)
    //                             ->where('year', $year)
    //                             ->firstOrFail();
    //                 $hms = $value['hms'];
    //                 $this->info($category_name.' || '.$db_category->id.' || '.$year);
    //                 $this->info(json_encode($hms));
    //                 foreach ($hms as $hm)
    //                     {
    //                         $name = $hm['name'];
    //                         $category_id = $db_category->id;
    //                         $writeup = $hm['writeup'];
    //                         $this->info('category name: '.$category_name.'|| category id: '.$category_id.'|| year: '.$year.'|| writeup name: '.$name);
    //                         $this->info(json_encode($writeup));
    //                     }

    //             };
    //         }



    // }


}
