<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Models\User;
use App\Models\UserProfile;
use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\Category;

class ProfileController extends Controller
{
    public function updateProfile(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'fullname' => 'required',
            'email' => 'required|email',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'error' => $validator->messages()->first()]);
        }

        $user = User::where('id', $request->user_id)->first();
        if ($request->email != $user->email) {
            $checkUser = User::where('email', $request->email)->first();
            if ($checkUser) {
                return response()->json(['success' => false, 'error' => 'Указанный email уже используется.']);
            }
            else {
                $user->update(['email' => $request->email]);
            }
        }        
        
        UserProfile::updateOrCreate([
            'user_id' => $request->user_id,
        ], [
            'user_id' => $request->user_id,
            'fullname' => $request->fullname,
            'phone' => $request->phone,
            'birthdate' => $request->birthdate,
            'gender' => $request->gender,
        ]);

        return response()->json(['success' => true]);
    }

    public function getProfileProducts($user_id, $page = 1, $status = '')
    {
        try {
            $limit = 20;
            $offset = ($page - 1) * $limit;

            if (!$status) {
                $products = Product::/*where('user_id', $user_id)->*/with('location')->with('productImages')->skip($offset)->take($limit)->get();
                $total = Product::where('user_id', $user_id)->count();
            }
            elseif ($status == 'published') {
                $products = Product::/*where('user_id', $user_id)->*/whereIn('status', ['published', 'accepted'])
                    ->with('location')->with('productImages')->skip($offset)->take($limit)->get();
                $total = Product::where('user_id', $user_id)->whereIn('status', ['published', 'accepted'])->count();
            }
            else {
                $products = Product::/*where('user_id', $user_id)->*/where('status', $status)
                    ->with('location')->with('productImages')->skip($offset)->take($limit)->get();
                $total = Product::where('user_id', $user_id)->where('status', $status)->count();
            }

            $pages = ceil($total / $limit);

            /*$query = Product::where('user_id', $user_id);
            if ($status) {
                if ($status == 'published') {
                    $query->whereIn('status', ['published', 'accepted']);
                }
                else {
                    $query->where('status', $status);
                }
            }

            if (isset($_GET['search'])) {
                $searchValue = $_GET['search'];
                $query->where('name', 'like')
            }*/

            return response()->json(['success' => true, 'products' => $products, 'pages' => $pages]);
        } catch (\ErrorException $e) {
            return response()->json(['success' => false, 'error' => $e->getMessage()]);
        }
    }

    public function changeProductStatus(Request $request)
    {
        try {
            $product = Product::find($request->product_id);

            if (!$product) {
                return response()->json(['success' => false, 'error' => 'Объявление не найдено']);
            }

            $product->update(['status' => $request->status]);

            return response()->json(['success' => true, 'product' => $product]);
        } catch (\ErrorException $e) {
            return response()->json(['success' => false, 'error' => $e->getMessage()]);
        }
    }

    public function getProduct($product_id)
    {
        try {
            $product = Product::where('id', $product_id)->with('user')->with('location')->with('productImages')->first();

            if (!$product) {
                return response()->json(['success' => false, 'error' => 'Объявление не найдено']);
            }

            $categories = [];
            $product_categories = ProductCategory::where('product_id', $product->id)->get();
            foreach ($product_categories as $product_category) {
                $category = Category::find($product_category->category_id);
                if ($category) $categories[] = $category;
            }
            $product->categories = $categories;

            return response()->json(['success' => true, 'product' => $product]);
        } catch (\ErrorException $e) {
            return response()->json(['success' => false, 'error' => $e->getMessage()]);
        }
    }
}
