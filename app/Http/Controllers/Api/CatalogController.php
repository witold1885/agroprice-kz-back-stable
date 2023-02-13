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

            $category = Category::where('url', $url)->with('children')->first();

            if (!$category) {
                return response()->json(['success' => false, 'error' => 'Category not found']);
            }

            $category->path = $this->getCategoryPath($category->id);
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

}
