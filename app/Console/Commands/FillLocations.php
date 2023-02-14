<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use PhpOffice\PhpSpreadsheet\IOFactory;
use App\Models\Location;

class FillLocations extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'fill:locations';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $inputFileName = '/home/w/Завантаження/Рубрикатор.xlsx';
        $reader = IOFactory::createReader('Xlsx');
        $reader->setLoadAllSheets();
        $spreadsheet = $reader->load($inputFileName);
        $worksheet = $spreadsheet->getSheet(1);
        $data = $worksheet->toArray();
        $regions = [];
        foreach ($data as $i => $item) {
            if ($i <= 1) continue;
            if ($item[1] != '') {
                // print_r($item);
                $regions[] = [
                    'region' => $item[1],
                    'cities' => $item[2],
                ];
            }
        }
        // print_r($regions);
        foreach ($regions as $regionArray) {
            $region = $regionArray['region'];
            $cities = explode("\n", $regionArray['cities']);
            echo $region . PHP_EOL;
            print_r($cities);
            echo '=========' . PHP_EOL;
            foreach ($cities as $city) {
                Location::updateOrCreate([
                    'city' => trim(explode('(', $city)[0]),
                    'region' => $region . ' область',
                ], [
                    'city' => trim(explode('(', $city)[0]),
                    'region' => $region . ' область',
                    'country' => 'Казахстан',
                ]);
            }
        }
    }
}
