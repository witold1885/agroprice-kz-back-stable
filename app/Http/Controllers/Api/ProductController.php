<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Models\User;
use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\ProductImage;
use App\Models\ProductContact;
use App\Models\Category;
use App\Models\Helper;
use DB;
use Log;
use App\Models\Admin;
use Laravel\Nova\Notifications\NovaNotification;
use Laravel\Nova\URL;

class ProductController extends Controller
{
    public function saveProduct(Request $request)
    {
        try {
            // Log::info($request);
            $validator = Validator::make($request->all(), [
                'name' => 'required',
                'description' => 'required',
            ]);

            if ($validator->fails()) {
                return response()->json(['success' => false, 'error' => $validator->messages()->first()]);
            }

            $product = Product::updateOrCreate([
                'id' => $request->id,
            ], [
                'user_id' => $request->user_id,
                'name' => $request->name,
                'description' => $request->description,
                'price' => $request->price,
                'price_negotiable' => $request->price_negotiable == 'true' ? 1 : 0,
                'location_id' => $request->location_id,
                'status' => $request->status, // draft || moderating
            ]);

            $productUrl = Helper::transliterate($product->name, 'ru') . '-' . $product->id;
            $product->update(['url' => $productUrl]);

            foreach ($request->categories as $category) {
                $categoryArray = json_decode($category, true);
                DB::table('product_categories')->updateOrInsert([
                    'category_id' => $categoryArray['id'],
                    'product_id' => $product->id,
                ], [
                    'category_id' => $categoryArray['id'],
                    'product_id' => $product->id,
                ], ['timestamps' => false]);
            }

            $contact = $request->contact;
            $user = User::find($request->user_id);
            if (!$contact['person']) {
                $contact['person'] = $user ? $user->profile->fullname : '';
            }
            if (!$contact['email']) {
                $contact['email'] = $user ? $user->email : '';
            }
            if (!$contact['phone']) {
                $contact['phone'] = $user ? $user->profile->phone : '';
            }

            ProductContact::updateOrCreate([
                'product_id' => $product->id,
            ], [
                'product_id' => $product->id,
                'person' => $contact['person'],
                'email' => $contact['email'],
                'phone' => $contact['phone'],
            ]);
            // Log::info($request->images);
            $order = 1;
            foreach ($request->images as $image) {
                $extension = $image->getClientOriginalExtension();                
                $filename = date('y') . '-' . date('m') . '-' . date('d') . '-' . $productUrl . '-' . $order . '.' . $extension;
                $path = $image->move(storage_path('app/public/products'), $filename);
                ProductImage::updateOrCreate([
                    'product_id' => $product->id,
                    'path' => 'products/' . $filename,
                ], [
                    'product_id' => $product->id,
                    'path' => 'products/' . $filename,
                    'order' => $order,
                ]);
                $order++;
            }

            if ($product->status == 'moderating') {
                $this->notifyAdmins($product->id, 'Добавлено новое объявление "' . $product->name . '"');
            }

            return response()->json(['success' => true]);
        } catch (\ErrorException $e) {
            return response()->json(['success' => false, 'error' => $e->getMessage(), 'line' => $e->getLine()]);
        }
    }

    public function notifyAdmins($product_id, $message)
    {
        foreach (Admin::all() as $admin) {
            $admin->notify(
                NovaNotification::make()
                    ->message($message)
                    ->action('Перейти', URL::remote(config('app.url') . '/nova/resources/products/' . $product_id))
                    // ->icon('download')
                    ->type('info')
            );
        }
    }

    public function getProduct($url)
    {
        try {
            if (!$url) {
                return response()->json(['success' => false, 'error' => 'Product URL not specified']);
            }

            $product = Product::where('url', $url)->with('user')->with('location')->with('productImages')->first();

            if (!$product) {
                return response()->json(['success' => false, 'error' => 'Product not found']);
            }

            $product_categories = ProductCategory::where('product_id', $product->id)->get();

            $categories = [];
            
            foreach ($product_categories as $product_category) {
                $category = Category::where('id', $product_category->category_id)->first();
                $categories[] = $category;
            }

            /*usort($children, function($a, $b) {
                if ($a['order'] == $b['order']) return 0;
                return $a['order'] > $b['order'] ? 1 : -1;
            });*/

            $product->categories = $categories;

            return response()->json(['success' => true, 'product' => $product]);
        } catch (\ErrorException $e) {
            return response()->json(['success' => false, 'error' => $e->getMessage()]);
        }
    }

}
