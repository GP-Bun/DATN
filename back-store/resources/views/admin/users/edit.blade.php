@extends('layouts.app')
@section('title','Sửa người dùng')
@section('content')
<div class="p-4 bg-white rounded shadow-sm">
    @if($errors->any())
        <div class="alert alert-danger">
            <ul class="mb-0">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <h4 class="mb-4">Cập nhật người dùng</h4>
    <form action="{{ route('admin.users.update', $user->id) }}" method="POST">
        @csrf
        @method('PUT')

        <div class="mb-3">
            <label for="name" class="form-label">Tên</label>
            <input type="text" name="name" id="name" class="form-control"
                   value="{{ old('name', $user->name) }}" required>
        </div>

        <div class="mb-3">
            <label for="email" class="form-label">Email</label>
            <input type="email" name="email" id="email" class="form-control"
                   value="{{ old('email', $user->email) }}" required>
        </div>

        <div class="mb-3">
            <label for="password" class="form-label">Mật khẩu (để trống nếu không đổi)</label>
            <input type="password" name="password" id="password" class="form-control">
        </div>

        <div class="mb-3">
            <label for="password_confirmation" class="form-label">Xác nhận mật khẩu</label>
            <input type="password" name="password_confirmation" id="password_confirmation" class="form-control">
        </div>

        <div class="mb-3">
            <label for="role" class="form-label">Vai trò</label>
            <select name="role" id="role" class="form-select" required>
                <option value="admin" {{ $user->role === 'admin' ? 'selected' : '' }}>Admin</option>
                <option value="user" {{ $user->role === 'user' ? 'selected' : '' }}>User</option>
                <option value="staff" {{ $user->role === 'staff' ? 'selected' : '' }}>Staff</option>
            </select>
        </div>

        @php
            $availablePermissions = [
                'view_orders' => 'Xem đơn hàng',
                'edit_products' => 'Chỉnh sửa sản phẩm',
                'manage_reviews' => 'Quản lý đánh giá',
                'chat_support' => 'Hỗ trợ khách hàng',
            ];
            $userPermissions = is_array($user->permissions ?? null) ? $user->permissions : [];
        @endphp

        <div class="mb-3">
            <label class="form-label">Phân quyền (chỉ áp dụng cho Staff)</label>
            <div class="row">
                @foreach($availablePermissions as $key => $label)
                    <div class="col-md-6">
                        <div class="form-check">
                            <input type="checkbox" name="permissions[]" value="{{ $key }}"
                                   class="form-check-input" id="perm_{{ $key }}"
                                   {{ in_array($key, $userPermissions) ? 'checked' : '' }}>
                            <label class="form-check-label" for="perm_{{ $key }}">{{ $label }}</label>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>

        <div class="mb-3 form-check">
            <input type="checkbox" name="active" id="active" class="form-check-input"
                   {{ $user->active ? 'checked' : '' }}>
            <label for="active" class="form-check-label">Kích hoạt tài khoản</label>
        </div>

        <button type="submit" class="btn btn-primary">Cập nhật</button>
    </form>
</div>
@endsection
