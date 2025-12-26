<?php

namespace App\Http\Controllers\Api\Geo;

use App\Http\Controllers\Controller;
use App\Models\Ward;
use Illuminate\Http\Request;

class WardController extends Controller
{
    public function index(Request $request)
    {
        $query = Ward::select('id','code','name','slug','type','district_id')->orderBy('name');

        if ($request->filled('district_id')) {
            $query->where('district_id', $request->get('district_id'));
        }

        return response()->json(['data' => $query->get()]);
    }
}
