@extends('layouts.app')
@section('title','Người dùng')
@section('content')
<div class="p-4 bg-white rounded shadow-sm">
<h4>Danh sách người dùng</h4>
<a href="{{ route('admin.users.create') }}" class="btn btn-success mb-3">+ Thêm mới</a>
@if(session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif
<table class="table table-bordered">
<thead class="table-light"><tr>
<th>#</th><th>Tên</th><th>Email</th><th>Vai trò</th><th>Trạng thái</th><th>Hành động</th>
</tr></thead>
<tbody>
@forelse($users as $u)
<tr>
<td>{{ $u->id }}</td>
<td>{{ $u->name }}</td>
<td>{{ $u->email }}</td>
<td>{{ $u->role }}</td>
<td>{{ $u->status }}</td>
<td>
<a href="{{ route('admin.users.edit',$u->id) }}" class="btn btn-sm btn-warning">Sửa</a>
<form action="{{ route('admin.users.destroy',$u->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Xóa tài khoản?');">
@csrf @method('DELETE')
<button class="btn btn-sm btn-danger">Xóa</button>
</form>
</td>
</tr>
@empty
<tr><td colspan="6" class="text-center text-muted">Chưa có người dùng</td></tr>
@endforelse
</tbody>
</table>
{{ $users->links() }}
</div>
@endsection
