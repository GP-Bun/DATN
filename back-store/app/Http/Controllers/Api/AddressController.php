<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Address;
use App\Http\Resources\AddressResource;


class AddressController extends Controller
{
    // Danh sách địa chỉ của user
    public function index(Request $request)
    {
        $addresses = $request->user()->addresses()
            ->with(['province', 'district', 'ward'])
            ->orderByDesc('is_default')
            ->get();

        return AddressResource::collection($addresses);
    }

    // Thêm địa chỉ mới
    public function store(Request $request)
    {
        $validated = $request->validate([
            'receiver_name'   => 'required|string|max:255',
            'receiver_phone'  => 'required|string|max:20',
            'line1'           => 'required|string|max:255',
            'province_id'     => 'required|exists:provinces,id',
            'district_id'     => 'required|exists:districts,id',
            'ward_id'         => 'required|exists:wards,id',
            'province_code'   => 'nullable|string|max:10',
            'district_code'   => 'nullable|string|max:10',
            'ward_code'       => 'nullable|string|max:10',
            'zip'             => 'nullable|string|max:20',
            'is_default'      => 'boolean'
        ]);

        if ($validated['is_default'] ?? false) {
            $request->user()->addresses()->update(['is_default' => false]);
        }

        $address = $request->user()->addresses()->create($validated);

        return new AddressResource($address->load(['province', 'district', 'ward']));
    }

    // Cập nhật địa chỉ
    public function update(Request $request, Address $address)
    {
        if ($address->user_id !== $request->user()->id) {
            return response()->json(['message' => 'Không có quyền'], 403);
        }

        $validated = $request->validate([
            'receiver_name'   => 'nullable|string|max:255',
            'receiver_phone'  => 'nullable|string|max:20',
            'line1'           => 'nullable|string|max:255',
            'province_id'     => 'nullable|exists:provinces,id',
            'district_id'     => 'nullable|exists:districts,id',
            'ward_id'         => 'nullable|exists:wards,id',
            'province_code'   => 'nullable|string|max:10',
            'district_code'   => 'nullable|string|max:10',
            'ward_code'       => 'nullable|string|max:10',
            'zip'             => 'nullable|string|max:20',
            'is_default'      => 'boolean'
        ]);

        if ($validated['is_default'] ?? false) {
            $request->user()->addresses()->update(['is_default' => false]);
        }

        $address->update($validated);

        return new AddressResource($address->load(['province', 'district', 'ward']));
    }

    // Xóa địa chỉ
    public function destroy(Request $request, Address $address)
    {
        if ($address->user_id !== $request->user()->id) {
            return response()->json(['message' => 'Không có quyền'], 403);
        }

        $address->delete();

        return response()->json(['message' => 'Đã xóa địa chỉ']);
    }

    // Đặt địa chỉ mặc định
    public function setDefault(Request $request, Address $address)
    {
        if ($address->user_id !== $request->user()->id) {
            return response()->json(['message' => 'Không có quyền'], 403);
        }

        $request->user()->addresses()->update(['is_default' => false]);
        $address->update(['is_default' => true]);

        return new AddressResource($address->load(['province','district','ward']));
    }
}
