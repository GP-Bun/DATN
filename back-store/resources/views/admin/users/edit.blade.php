@extends('layouts.app')
@section('title','Sửa người dùng')
@section('content')
<div class="p-4 bg-white rounded shadow-sm">
    @if($errors->any())
    <div class="alert alert-danger">
        <ul>
            @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif
    <h4>Cập nhật người dùng</h4>
    <form action="{{ route('admin.users.update',$user->id) }}" method="POST">
        @csrf @method('PUT')

        <div class="mb-3">
            <label for="name" class="form-label">Tên</label>
            <input type="text" name="name" id="name" class="form-control" value="{{ $user->name }}" required>
        </div>

        <div class="mb-3">
            <label for="email" class="form-label">Email</label>
            <input type="email" name="email" id="email" class="form-control" value="{{ $user->email }}" required>
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
                <option value="admin" @if($user->role=='admin') selected @endif>Admin</option>
                <option value="user" @if($user->role=='user') selected @endif>User</option>
                <option value="customer" @if($user->role=='customer') selected @endif>Customer</option>
            </select>
        </div>

        <div class="mb-3 form-check">
            <input type="checkbox" name="active" id="active" class="form-check-input" @if($user->active) checked @endif>
            <label for="active" class="form-check-label">Kích hoạt tài khoản</label>
        </div>

        <button type="submit" class="btn btn-primary">Cập nhật</button>
    </form>
</div>
@endsection
