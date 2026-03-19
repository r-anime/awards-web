<?php

namespace App\Console\Commands;

use App\Models\Acknowledgement;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class ImportAcknowledgements extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:import-acknowledgements';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Import Acknowledgements from "acknowledgements.json" in the archive directory';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        //
        $file = File::get(app_path('Console/Commands/archive/acknowledgements.json'));
        $archive = json_decode($file, true);
        foreach ($archive as $year => $ackArray) {
            $this->info('Importing acknowledgements for '.$year);
            
            foreach ($ackArray as $ack) {
                $title = $ack['title'];
                $contentArr = [
                    'english' => $ack['english']
                ];
                if(array_key_exists('japanese', $ack))
                    $contentArr['japanese'] = $ack['japanese'];
                if(array_key_exists('extra', $ack))
                    $contentArr['extra'] = $ack['extra'];
                $order = $ack['order'] ?? 5;
                Acknowledgement::updateOrCreate([
                    'year' => $year,
                    'title' => $title
                ],
                [
                    'subtitle' => '',
                    'content' => json_encode($contentArr),
                    'order' => $order
                ]);
            }
        }
    }
}
