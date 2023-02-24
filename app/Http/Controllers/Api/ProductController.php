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
use DB;

class ProductController extends Controller
{
    public function saveProduct(Request $request)
    {
        try {
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
                'price_negotiable' => $request->price_negotiable,
                'location_id' => $request->location_id,
                'status' => $request->status, // draft || moderating
            ]);

            foreach ($request->categories as $category) {                
                DB::table('product_categories')->updateOrInsert([
                    'category_id' => $category->id,
                    'product_id' => $product->id,
                ], [
                    'category_id' => $category->id,
                    'product_id' => $product->id,
                ], ['timestamps' => false]);
            }

            ProductContact::updateOrCreate([
                'product_id' => $product->id,
            ], [
                'product_id' => $product->id,
                'person' => $request->contact['person'],
                'email' => $request->contact['email'],
                'phone' => $request->contact['phone'],
            ]);

            return response()->json(['success' => true]);
        } catch (\ErrorException $e) {
            return response()->json(['success' => false, 'error' => $e->getMessage()]);
        }
    }
}
