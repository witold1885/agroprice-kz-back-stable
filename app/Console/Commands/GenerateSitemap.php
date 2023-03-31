<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Category;
use App\Models\Product;

class GenerateSitemap extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'sitemap:generate';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';
    private $xml_data;

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
        define('GOOGLE_BASE_NS', 'http://base.google.com/ns/1.0');
        $xml = '<?xml version="1.0" encoding="utf-8"?>
            <urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9" xmlns:image="http://www.google.com/schemas/sitemap-image/1.1">
            </urlset>';
        $this->xml_data = new \SimpleXMLElement($xml);
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $this->addMainPage();
        $this->addCategories();
        // $this->addProducts();
        file_put_contents(config('app.spa_dist') . 'sitemap.xml', $this->xml_data->asXML());
    }

    private function addMainPage()
    {
        $url = $this->xml_data->addChild('url');
        $url->addChild('loc', 'https://agroprice.kz/');
        $url->addChild('changefreq', 'daily');
        $url->addChild('lastmod', date('Y-m-d') . 'T'. date('H:i:s') . '+00:00');
        $url->addChild('priority', '1.0');
    }

    private function addCategories()
    {
        foreach (Category::orderBy('parent_id', 'asc')->get() as $category) {
            $url = $this->xml_data->addChild('url');
            $url->addChild('loc', 'https://agroprice.kz/catalog/' . $category->url);
            $url->addChild('changefreq', 'daily');
            $url->addChild('lastmod', date('Y-m-d') . 'T'. date('H:i:s') . '+00:00');
            $url->addChild('priority', '1.0');
        }
    }

    private function addProducts()
    {
        foreach (Product::all() as $product) {
            $url = $this->xml_data->addChild('url');
            $url->addChild('loc', 'https://agroprice.kz/product/' . $product->url);
            $url->addChild('changefreq', 'daily');
            $url->addChild('lastmod', date('Y-m-d') . 'T'. date('H:i:s') . '+00:00');
            $url->addChild('priority', '1.0');
        }
    }
}
