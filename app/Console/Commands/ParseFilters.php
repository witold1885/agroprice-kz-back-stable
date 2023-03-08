<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use PhpOffice\PhpSpreadsheet\IOFactory;
use App\Models\FilterGroup;
use App\Models\Filter;
use DB;

class ParseFilters extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'parse:filters';

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
        $inputFileName = '/home/w/Завантаження/Фильтры.xlsx';
        $reader = IOFactory::createReader('Xlsx');
        $reader->setLoadAllSheets();
        $spreadsheet = $reader->load($inputFileName);
        $worksheet = $spreadsheet->getSheet(0);
        $data = $worksheet->toArray();
        // print_r($data);
        $categoryId = 13;
        foreach ($data as $i => $item) {
            if ($item[0] != '' && $item[1] != '') {
                print_r($item);
                $filterGroup = FilterGroup::updateOrCreate([
                    'name' => trim($item[0]),
                ], [
                    'name' => trim($item[0]),
                    'type' => 'checkbox',
                ]);
                DB::table('category_filter_groups')->updateOrInsert([
                    'category_id' => $categoryId,
                    'filter_group_id' => $filterGroup->id,
                ], [
                    'category_id' => $categoryId,
                    'filter_group_id' => $filterGroup->id,
                ], ['timestamps' => false]);
                foreach (explode("\n", $item[1]) as $filter) {
                    echo $filter . PHP_EOL;
                    $value = trim(str_replace('кг', '', $filter));
                    if ($value != '') {
                        Filter::updateOrCreate([
                            'filter_group_id' => $filterGroup->id,
                            'value' => $value,
                        ], [
                            'filter_group_id' => $filterGroup->id,
                            'value' => $value,
                        ]);
                    }
                }
            }
        }
    }
}
