<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Location;

class LocationController extends Controller
{
    public function getLocations()
    {
        try {
            $allLocations = Location::all();
            $regions = Location::select('region')->distinct()->get();
            $locations = [
                'regions' => [],
                'except' => [],
            ];
            foreach ($regions as $r => $item) {
                $cities = Location::select('id', 'city')->where('region', $item->region)->get();
                if ($item['region'] !== null) {
                    $locations['regions'][$r] = [
                        'region' => $item['region'],
                        'cities_ids' => [],
                        'cities' => [],
                    ];
                    foreach ($cities as $city) {
                        $locations['regions'][$r]['cities_ids'][] = $city->id;
                        $locations['regions'][$r]['cities'][] = $city;
                    }
                }
                else {
                    $locations['except'] = [
                        'cities' => [],
                    ];
                    foreach ($cities as $city) {
                        $locations['except']['cities'][] = $city;
                    }
                }
            }
            return response()->json(['success' => true, 'locations' => $locations]);

        } catch (\ErrorException $e) {
            return response()->json(['success' => false, 'error' => $e->getMessage()]);
        }
    }

    public function searchLocations($search)
    {
        try {
            $locations = Location::where('city', 'like', $search . '%')->limit(10)->get();
            
            return response()->json(['success' => true, 'locations' => $locations]);

        } catch (\ErrorException $e) {
            return response()->json(['success' => false, 'error' => $e->getMessage()]);
        }
    }
}
