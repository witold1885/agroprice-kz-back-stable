<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Category;

class FillCategoriesPath extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'fill:catspath';

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
        $categories = Category::all();
        echo count($categories) . PHP_EOL;
        foreach ($categories as $category) {
            if (!$category->path) {
                echo $category->id . ' - ' . $category->name . PHP_EOL;
                $category->update(['path' => implode(' > ', array_reverse($this->getPath($category->id)))]);
            }

        }
    }

    private function getPath($id, $path = [])
    {
        $category = \App\Models\Category::find($id);
        $path[] = $category->name;
        if ($category->parent_id) {
            return $this->getPath($category->parent_id, $path);
        }
        return $path;
    }

}
