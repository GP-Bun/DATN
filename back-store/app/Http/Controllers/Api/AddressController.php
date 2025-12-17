<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Address;

class AddressController extends Controller
{
    // Danh sách địa chỉ của user
    public function index(Request $request)
    {
        $addresses = Address::where('user_id', $request->user()->id)->get();
        return response()->json($addresses);
    }

    // Thêm địa chỉ mới
    public function store(Request $request)
    {
        $data = $request->validate([
            'receiver_name' => 'required|string|max:255',
            'receiver_phone' => 'required|string|max:20',
            'line1'         => 'required|string|max:255',
            'city'           => 'required|string|max:255',
            'province'       => 'required|string|max:255',
            'zip'            => 'nullable|string|max:10',
            'is_default'     => 'boolean',
        ]);

        $data['user_id'] = $request->user()->id;

        if (!empty($data['is_default']) && $data['is_default']) {
            Address::where('user_id', $request->user()->id)->update(['is_default' => false]);
        }

        $address = Address::create($data);
        return response()->json(['message' => 'Đã thêm địa chỉ mới', 'address' => $address]);
    }

    // Cập nhật địa chỉ
    public function update(Request $request, Address $address)
    {
        if ($address->user_id !== $request->user()->id) {
            return response()->json(['message' => 'Không có quyền'], 403);
        }

        $data = $request->validate([
            'receiver_name' => 'nullable|string|max:255',
            'receiver_phone' => 'nullable|string|max:20',
            'line1'         => 'nullable|string|max:255',
            'city'           => 'nullable|string|max:255',
            'province'       => 'nullable|string|max:255',
            'zip'            => 'nullable|string|max:10',
            'is_default'     => 'boolean',
        ]);

        if (!empty($data['is_default']) && $data['is_default']) {
            Address::where('user_id', $request->user()->id)->update(['is_default' => false]);
        }

        $address->update($data);
        return response()->json(['message' => 'Đã cập nhật địa chỉ', 'address' => $address]);
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
}
