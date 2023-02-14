<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Banner;

class InfoController extends Controller
{
    public function getBanner($code)
    {
        try {
            $banner = Banner::select('id', 'code', 'autoplay', 'duration')->where('code', $code)->first();
            $banner->images = $banner->getActiveImages();

            return response()->json(['success' => true, 'banner' => $banner]);
        } catch (\ErrorException $e) {
            return response()->json(['success' => false, 'error' => $e->getMessage()]);
        }
    }
}
