@extends('layouts.app')

@section('page-title', 'Thêm sản phẩm')

@section('content')
@php
    $sizes = ['36','37','38','39','40','41','42','43','44'];
    $colors = ['Đen','Trắng','Đỏ','Xanh'];
@endphp

<form action="{{ route('admin.products.store') }}" method="POST" enctype="multipart/form-data">
    @csrf

    <div class="mb-3">
        <label>Tên sản phẩm</label>
        <input type="text" name="name" class="form-control" required>
    </div>

    <div class="mb-3">
        <label>Danh mục</label>
        <select name="category_id" class="form-select" required>
            @foreach($categories as $cat)
                <option value="{{ $cat->id }}">{{ $cat->name }}</option>
            @endforeach
        </select>
    </div>

    <div class="mb-3">
        <label>Giá</label>
        <input type="number" name="price" class="form-control" min="0" required>
    </div>

    <div class="mb-3">
        <label>Trạng thái</label>
        <select name="status" class="form-select" required>
            <option value="ACTIVE">Hoạt động</option>
            <option value="INACTIVE">Không hoạt động</option>
            <option value="OUT_OF_STOCK">Hết hàng</option>
        </select>
    </div>

    <div class="mb-3">
        <label>Ảnh sản phẩm</label>
        <input type="file" name="image" class="form-control">
    </div>

    <hr>
    <h5>Biến thể sản phẩm</h5>
    <div id="variants-wrapper"></div>
    <button type="button" id="add-variant" class="btn btn-secondary btn-sm mb-3">+ Thêm biến thể</button>

    <button type="submit" class="btn btn-primary">Tạo sản phẩm</button>
</form>

<script>
let variantIndex = 0;
const wrapper = document.getElementById('variants-wrapper');

const sizes = @json($sizes);
const colors = @json($colors);

document.getElementById('add-variant').addEventListener('click', function() {
    const div = document.createElement('div');
    div.classList.add('variant-row', 'mb-2', 'border', 'p-2', 'rounded');

    let colorOptions = colors.map(c => `<option value="${c}">${c}</option>`).join('');
    let sizeOptions = sizes.map(s => `<option value="${s}">${s}</option>`).join('');

    div.innerHTML = `
        <select name="variants[${variantIndex}][color]" class="form-select mb-1" required>${colorOptions}</select>
        <select name="variants[${variantIndex}][size]" class="form-select mb-1" required>${sizeOptions}</select>
        <input type="number" name="variants[${variantIndex}][stock]" placeholder="Stock" class="form-control mb-1" min="0" required>
        <input type="number" name="variants[${variantIndex}][price]" placeholder="Giá" class="form-control mb-1" step="0.01" required>
        <button type="button" class="btn btn-danger btn-sm remove-variant">Xóa</button>
    `;
    wrapper.appendChild(div);
    variantIndex++;
});

wrapper.addEventListener('click', function(e){
    if(e.target.classList.contains('remove-variant')){
        e.target.closest('.variant-row').remove();
    }
});
</script>
@endsection
