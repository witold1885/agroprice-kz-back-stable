<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Models\User;
use App\Models\UserProfile;
use App\Models\Product;

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

    public function getProfileProducts($user_id, $page = 1)
    {
        try {
            $limit = 20;
            $offset = ($page - 1) * $limit;

            $products = Product::where('user_id', $request->user_id)->with('location')->with('productImages')->skip($offset)->take($limit)->get();

            $total = Product::where('user_id', $request->user_id)->count();

            return response()->json(['success' => true, 'products' => $products, 'total' => $total]);
        } catch (\ErrorException $e) {
            return response()->json(['success' => false, 'error' => $e->getMessage()]);
        }
    }
}
