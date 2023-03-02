<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Category;

class CatalogController extends Controller
{
    public function getCategory($url)
    {
        try {
            if (!$url) {
                return response()->json(['success' => false, 'error' => 'Category URL not specified']);
            }

            $category = Category::where('url', $url)->first();

            if (!$category) {
                return response()->json(['success' => false, 'error' => 'Category not found']);
            }

            $category->path = $this->getCategoryPath($category->id);

            $children = Category::where('parent_id', $category->id)->get()->toArray();

            usort($children, function($a, $b) {
                if ($a['order'] == $b['order']) return 0;
                return $a['order'] > $b['order'] ? 1 : -1;
            });

            $category->children = $children;

            return response()->json(['success' => true, 'category' => $category]);
        } catch (\ErrorException $e) {
            return response()->json(['success' => false, 'error' => $e->getMessage()]);
        }
    }

    private function getCategoryPath($id, $path = [])
    {
        $category = \App\Models\Category::find($id);
        $path[] = [
            'category_id' => $category->id,
            'name' => $category->name,
            'url' => $category->url,
        ];
        if ($category->parent_id) {
            return $this->getCategoryPath($category->parent_id, $path);
        }
        return array_reverse($path);
    }

    public function getMainCategories()
    {
        try {
            $categories = Category::where('parent_id', 0)->get();

            return response()->json(['success' => true, 'categories' => $categories]);
        } catch (\ErrorException $e) {
            return response()->json(['success' => false, 'error' => $e->getMessage()]);
        }
    }

    public function getMenuCategories()
    {
        try {
            $menu_categories = [];
            $categories = Category::where('parent_id', 0)->get();
            foreach ($categories as $category) {
                $menu_category = $category;
                $menu_subcategories = [];
                $subcategories = Category::where('parent_id', $category->id)->get();
                foreach ($subcategories as $subcategory) {
                    $menu_subcategory = $subcategory;
                    $subsubcategories = Category::where('parent_id', $subcategory->id)->get();
                    $menu_subcategory->subsubcategories = $subsubcategories;
                    $menu_subcategories[] = $menu_subcategory;
                }
                $menu_category->subcategories = $menu_subcategories;
                $menu_categories[] = $menu_category;
            }

            return response()->json(['success' => true, 'categories' => $menu_categories]);
        } catch (\ErrorException $e) {
            return response()->json(['success' => false, 'error' => $e->getMessage()]);
        }
    }

    public function getChildCategories($parent_id)
    {
        try {
            $categories = Category::where('parent_id', $parent_id)->get();

            return response()->json(['success' => true, 'categories' => $categories]);
        } catch (\ErrorException $e) {
            return response()->json(['success' => false, 'error' => $e->getMessage()]);
        }
    }

    public function getCategoryProducts($category_id)
    {
        try {
            $category_products = ProductCategory::where('category_id', $category_id)->get();

            $products_ids = [];
            foreach ($category_products as $category_product) {
                $products_ids[] = $category_product->product_id;
            }

            $products = Product::whereIn('id', $products_ids)->get();
            
            return response()->json(['success' => true, 'products' => $products]);
        } catch (\ErrorException $e) {
            return response()->json(['success' => false, 'error' => $e->getMessage()]);
        }
    }

}
