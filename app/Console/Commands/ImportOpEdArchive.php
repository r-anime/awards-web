<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Category;
use App\Models\Entry;
use App\Models\Result;
use File;
use Storage;

class ImportOpEdArchive extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:import-op-ed-archive {year}';

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
        $year = intval($this->argument('year')); //Set the year for importing
        // $year_range = range(2016, $current_year-1); //Set the list of years to import for archive

        // foreach ($year_range as $year) {
        $json_file = File::get(app_path("Console/Commands/archive/results{$year}.json"));
        $json_data = json_decode($json_file, true);
        // $this->info('current year: '.$year);
        $json_themes_map = $json_data['themes'];
        // $this->info(json_encode($json_themes_map)[x]);   #If entire json array is cast as a string, this prints the character at that index. aka. this prints one character at a time

        // $this->info(json_encode($json_themes_map["301"]));   #Testing how to call individual themes based on the index/key

        foreach ($json_data['sections'] as $data) {
            // $this->info(json_encode($data));
            $awards = $data['awards'];
            foreach ($awards as $key=>$value) {
                $category_name = $value['name'];
                // $this->info($category_name.': '.$year);
                $db_category = Category::where('name', $category_name)
                            ->where('year', $year)
                            ->firstOrFail();
                $category_id = $db_category->id;


                $entry_type = $value['entryType'];
                $juror_list = $value['jurors'];
                $cat_overview = $value['blurb'];
                $nominees = $value['nominees'];
                // $this->info(json_encode($nominees));
                if ($category_name == 'Best OP' || $category_name == 'OP' || $category_name == 'Best ED' || $category_name == 'ED') {
                    // $this->info($category_name.': '.$year);
                    // $this->info($entry_type);
                    // $this->info('Jurors: '.json_encode($juror_list));
                    // $this->info('State of Category: '.json_encode($cat_overview));
                    // $this->info(json_encode($nominees));
                    foreach ($nominees as $nominee) {
                        // $this->info(json_encode($nominee));

                        $result_id = $nominee['id'];
                        $alt_name = $nominee['altname'];
                        $alt_img = $nominee['altimg'];
                        $public_vote = $nominee['public'];
                        $jury_rank = $nominee['jury'];
                        $writeup = $nominee['writeup'];
                        $stafflist = $nominee['staff'];


                        // $this->info('entry id: '.$entry_id);
                        // $this->info('public votes: '.$public_vote);
                        // $this->info('jury placement: '.$jury_rank);
                        // $this->info(json_encode($writeup));
                        // $this->info(json_encode($stafflist));






                        // $entry_id = $json_themes_map[key];
                        $json_entry = $json_themes_map[$result_id];
                        $json_name = explode(' - ', $json_entry);
                        $result_anime = $json_name[0];
                        $result_name = $json_name[1];
                        // $this->info($result_theme);

                        $db_entry = Entry::where('name', $result_name)
                            ->first();

                        // For entries that exist
                        if ($db_entry !== NULL) {
                            // $this->info($db_entry['id']);

                            // $this->info($entry_name);
                            // $entry_image = $db_entry['image'];
                            $entry_id = $db_entry['id'];


                            $imageContent = file_get_contents($alt_img);
                            $theme_image_filename = '/entry/theme-'.$entry_id.'.jpg';
                            Storage::disk('public')->put($theme_image_filename, $imageContent);

                            // $this->info($alt_img);



                            $new_result = Result::updateOrCreate (
                                [
                                    'name' => $result_name,
                                    'year' => $year,
                                    'category_id' => $category_id,
                                ],
                                [
                                    'image' => $theme_image_filename,
                                    'entry_id' => $entry_id,
                                    'jury_rank' => $jury_rank,
                                    'public_rank' => $public_vote,
                                    'description' => $writeup,
                                    'staff_credits' => $stafflist,
                                ]
                            );
                        }

                        if ($db_entry == NULL) {
                            $this->info($result_anime.': '.$result_name);  //Check for entries that break the import and need to be manually added
                        }

                    }
                }
            }
        }
        // }
    }
}
