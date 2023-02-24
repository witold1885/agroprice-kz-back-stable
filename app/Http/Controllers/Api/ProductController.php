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
use App\Models\Helper;
use DB;
use Log;

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
                DB::table('product_categories')->updateOrInsert([
                    'category_id' => $category['id'],
                    'product_id' => $product->id,
                ], [
                    'category_id' => $category['id'],
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
            foreach ($request->images as $image) {
                $file = $image['file'];
                $extension = $file->getClientOriginalExtension();                
                $filename = date('y') . '-' . date('m') . '-' . date('d') . '-' . $productUrl . '-' . $image['num'] . '.' . $extension;
                $path = $file->storeAs('products', $filename);
                ProductImage::updateOrCreate([
                    'product_id' => $product->id,
                    'path' => $path,
                ], [
                    'product_id' => $product->id,
                    'path' => $path,
                    'order' => $image['num'],
                ]);
            }

            return response()->json(['success' => true]);
        } catch (\ErrorException $e) {
            return response()->json(['success' => false, 'error' => $e->getMessage()]);
        }
    }
}
