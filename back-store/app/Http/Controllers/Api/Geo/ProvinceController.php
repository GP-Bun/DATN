<?php

namespace App\Http\Controllers\Api\Geo;

use App\Http\Controllers\Controller;
use App\Models\Province;

class ProvinceController extends Controller
{
    public function index()
    {
        return response()->json([
            'data' => Province::select('id','code','name','slug','type')->orderBy('name')->get()
        ]);
    }
}
