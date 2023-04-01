<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Category;
use App\Models\ProductCategory;
use App\Models\Product;

class CatalogController extends Controller
{
    public function checkCategory(Request $request)
    {
        try {
            $check = Category::where('url', $request->url)->count();

            return response()->json(['success' => true, 'check' => $check]);
        } catch (\ErrorException $e) {
            return response()->json(['success' => false, 'error' => $e->getMessage()]);
        }
    }

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

            $children = Category::where('parent_id', $category->id)->orderBy('order', 'asc')->get()->toArray();

            /*usort($children, function($a, $b) {
                if ($a['order'] == $b['order']) return 0;
                return $a['order'] > $b['order'] ? 1 : -1;
            });*/

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
            $categories = Category::where('parent_id', 0)->orderBy('order', 'asc')->get();
            foreach ($categories as $category) {
                $category->productsCount = ProductCategory::where('category_id', $category->id)->count();
            }

            return response()->json(['success' => true, 'categories' => $categories]);
        } catch (\ErrorException $e) {
            return response()->json(['success' => false, 'error' => $e->getMessage()]);
        }
    }

    public function getMenuCategories()
    {
        try {
            $menu_categories = [];
            $categories = Category::where('parent_id', 0)->orderBy('order', 'asc')->get();
            foreach ($categories as $category) {
                $menu_category = $category;
                $menu_subcategories = [];
                $subcategories = Category::where('parent_id', $category->id)->orderBy('order', 'asc')->get();
                foreach ($subcategories as $subcategory) {
                    $menu_subcategory = $subcategory;
                    $subsubcategories = Category::where('parent_id', $subcategory->id)->orderBy('order', 'asc')->get();
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
            $categories = Category::where('parent_id', $parent_id)->orderBy('order', 'asc')->get();

            return response()->json(['success' => true, 'categories' => $categories]);
        } catch (\ErrorException $e) {
            return response()->json(['success' => false, 'error' => $e->getMessage()]);
        }
    }

    public function getCategoryProducts($category_id, $page = 1)
    {
        try {
            $category_products = ProductCategory::where('category_id', $category_id)->get();

            $products_ids = [];
            foreach ($category_products as $category_product) {
                $products_ids[] = $category_product->product_id;
            }

            $limit = 20;
            $offset = ($page - 1) * $limit;

            $products = Product::whereIn('id', $products_ids)->whereIn('status', ['published', 'accepted'])->with('user')->with('location')->with('productImages')->skip($offset)->take($limit)->get();

            foreach ($products as $product) {
                $product->category_name = '';
                $product_categories = ProductCategory::where('product_id', $product->id)->get();
                $main_category_id = 0;
                foreach ($product_categories as $product_category) {
                    $category = Category::where('id', $product_category->category_id)->first();
                    if ($category->parent_id == 0) {
                        $product->category_name = $category->name;
                        $main_category_id = $category->id;
                    }
                }
                if ($main_category_id) {
                    foreach ($product_categories as $product_category) {
                        $category = Category::where('id', $product_category->category_id)->first();
                        if ($category->parent_id == $main_category_id) $product->category_name = $category->name;
                        break;
                    }
                }
            }

            return response()->json(['success' => true, 'products' => $products, 'total' => count($products_ids)]);
        } catch (\ErrorException $e) {
            return response()->json(['success' => false, 'error' => $e->getMessage()]);
        }
    }

    public function getCategoryProductsPost(Request $request)
    {
        try {
            $category_products = ProductCategory::where('category_id', $request->category_id)->get();

            $products_ids = [];
            foreach ($category_products as $category_product) {
                $products_ids[] = $category_product->product_id;
            }

            $limit = 20;
            $offset = ($request->page - 1) * $limit;

            if (!$request->locations) {
                $min_price_product = Product::whereIn('id', $products_ids)->whereIn('status', ['published', 'accepted'])->orderBy('price', 'asc')->first();
                $min_price = $min_price_product->price;
                $max_price_product = Product::whereIn('id', $products_ids)->whereIn('status', ['published', 'accepted'])->orderBy('price', 'desc')->first();
                $max_price = $max_price_product->price;
            }
            else {
                $min_price_product = Product::whereIn('id', $products_ids)->whereIn('status', ['published', 'accepted'])->whereIn('location_id', explode(',', $request->locations))->orderBy('price', 'asc')->first();
                $min_price = $min_price_product->price;
                $max_price_product = Product::whereIn('id', $products_ids)->whereIn('status', ['published', 'accepted'])->whereIn('location_id', explode(',', $request->locations))->orderBy('price', 'desc')->first();
                $max_price = $max_price_product->price;
            }

            $query = Product::whereIn('id', $products_ids)->whereIn('status', ['published', 'accepted']);
            if ($request->locations) {
                $query->whereIn('location_id', explode(',', $request->locations));
            }
            if ($request->filter_min_price) {
                $query->where('price', '>=', $request->filter_min_price);
            }
            if ($request->filter_max_price) {
                $query->where('price', '<=', $request->filter_max_price);
            }
            $total = $query->count();

            if (!$request->sort) {
                $query->orderBy('views', 'desc');
            }
            else {
                if ($request->sort == 'popular') $query->orderBy('views', 'desc');
                if ($request->sort == 'cheap') $query->orderBy('price', 'asc');
                if ($request->sort == 'expensive') $query->orderBy('price', 'desc');
                if ($request->sort == 'new') $query->orderBy('created_at', 'desc');
            }

            $products = $query->with('user')->with('location')->with('productImages')->skip($offset)->take($limit)->get();

            foreach ($products as $product) {
                $product->category_name = '';
                $product_categories = ProductCategory::where('product_id', $product->id)->get();
                $main_category_id = 0;
                foreach ($product_categories as $product_category) {
                    $category = Category::where('id', $product_category->category_id)->first();
                    if ($category->parent_id == 0) {
                        $product->category_name = $category->name;
                        $main_category_id = $category->id;
                    }
                }
                if ($main_category_id) {
                    foreach ($product_categories as $product_category) {
                        $category = Category::where('id', $product_category->category_id)->first();
                        if ($category->parent_id == $main_category_id) $product->category_name = $category->name;
                        break;
                    }
                }
            }

            return response()->json([
                'success' => true,
                'products' => $products,
                'total' => $total,
                'min_price' => $min_price,
                'max_price' => $max_price
            ]);
        } catch (\ErrorException $e) {
            return response()->json(['success' => false, 'error' => $e->getMessage()]);
        }
    }

    public function getRandomProducts()
    {
        try {
            $products = Product::with('user')->with('location')->with('productImages')->limit(8)->get();

            foreach ($products as $product) {
                $product->category_name = '';
                $product_categories = ProductCategory::where('product_id', $product->id)->get();
                $main_category_id = 0;
                foreach ($product_categories as $product_category) {
                    $category = Category::where('id', $product_category->category_id)->first();
                    if ($category->parent_id == 0) {
                        $product->category_name = $category->name;
                        $main_category_id = $category->id;
                    }
                }
                if ($main_category_id) {
                    foreach ($product_categories as $product_category) {
                        $category = Category::where('id', $product_category->category_id)->first();
                        if ($category->parent_id == $main_category_id) $product->category_name = $category->name;
                        break;
                    }
                }
            }

            return response()->json(['success' => true, 'products' => $products]);
        } catch (\ErrorException $e) {
            return response()->json(['success' => false, 'error' => $e->getMessage()]);
        }
    }

    public function getSellerProducts(Request $request)
    {
        try {
            $products = Product::where('user_id', $request->seller_id)->with('user')->with('location')->with('productImages')->limit($request->limit)->get();

            foreach ($products as $product) {
                $product->category_name = '';
                $product_categories = ProductCategory::where('product_id', $product->id)->get();
                $main_category_id = 0;
                foreach ($product_categories as $product_category) {
                    $category = Category::where('id', $product_category->category_id)->first();
                    if ($category->parent_id == 0) {
                        $product->category_name = $category->name;
                        $main_category_id = $category->id;
                    }
                }
                if ($main_category_id) {
                    foreach ($product_categories as $product_category) {
                        $category = Category::where('id', $product_category->category_id)->first();
                        if ($category->parent_id == $main_category_id) $product->category_name = $category->name;
                        break;
                    }
                }
            }

            return response()->json(['success' => true, 'products' => $products]);
        } catch (\ErrorException $e) {
            return response()->json(['success' => false, 'error' => $e->getMessage()]);
        }
    }

    public function getSearchResult(Request $request)
    {
        try {
            $products = Product::where('name', 'like', '%' . $request->q . '%')->with('user')->with('location')->with('productImages')->limit(5)->get();
            $categories = Category::where('name', 'like', '%' . $request->q . '%')->limit(5)->get();

            return response()->json(['success' => true, 'products' => $products, 'categories' => $categories]);
        } catch (\ErrorException $e) {
            return response()->json(['success' => false, 'error' => $e->getMessage()]);
        }
    }

}
