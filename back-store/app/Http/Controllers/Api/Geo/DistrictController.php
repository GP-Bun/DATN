<?php

namespace App\Http\Controllers\Api\Geo;

use App\Http\Controllers\Controller;
use App\Models\District;
use Illuminate\Http\Request;

class DistrictController extends Controller
{
    public function index(Request $request)
    {
        $query = District::select('id','code','name','slug','type','province_id')->orderBy('name');

        if ($request->filled('province_id')) {
            $query->where('province_id', $request->get('province_id'));
        }

        return response()->json(['data' => $query->get()]);
    }
}
