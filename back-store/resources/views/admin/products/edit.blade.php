@extends('layouts.app')

@section('page-title', 'Sửa sản phẩm')

@section('content')
@php
    $sizes = ['36','37','38','39','40','41','42','43','44'];
    $colors = ['Đen','Trắng','Đỏ','Xanh'];
@endphp

<form action="{{ route('admin.products.update', $product->id) }}" method="POST" enctype="multipart/form-data">
    @csrf
    @method('PUT')

    <div class="mb-3">
        <label>Tên sản phẩm</label>
        <input type="text" name="name" class="form-control" value="{{ $product->name }}" required>
    </div>

    <div class="mb-3">
        <label>Danh mục</label>
        <select name="category_id" class="form-select" required>
            @foreach($categories as $cat)
                <option value="{{ $cat->id }}" {{ $product->category_id == $cat->id ? 'selected' : '' }}>
                    {{ $cat->name }}
                </option>
            @endforeach
        </select>
    </div>

    <div class="mb-3">
        <label>Giá</label>
        <input type="number" name="price" class="form-control" value="{{ $product->price }}" min="0" required>
    </div>

    <div class="mb-3">
        <label>Trạng thái</label>
        <select name="status" class="form-select" required>
            <option value="ACTIVE" {{ $product->status=='ACTIVE' ? 'selected' : '' }}>Hoạt động</option>
            <option value="INACTIVE" {{ $product->status=='INACTIVE' ? 'selected' : '' }}>Không hoạt động</option>
            <option value="OUT_OF_STOCK" {{ $product->status=='OUT_OF_STOCK' ? 'selected' : '' }}>Hết hàng</option>
        </select>
    </div>

    <div class="mb-3">
        <label>Ảnh sản phẩm</label>
        <input type="file" name="image" class="form-control">
        @if($product->image)
            <img src="{{ asset('storage/'.$product->image) }}" width="80" class="mt-2">
        @endif
    </div>

    <hr>
    <h5>Biến thể sản phẩm</h5>
    <div id="variants-wrapper">
        @foreach($product->variants as $i => $variant)
            <div class="variant-row mb-2 border p-2 rounded">
                <select name="variants[{{ $i }}][color]" class="form-select mb-1" required>
                    @foreach($colors as $c)
                        <option value="{{ $c }}" {{ $variant->color == $c ? 'selected' : '' }}>{{ $c }}</option>
                    @endforeach
                </select>
                <select name="variants[{{ $i }}][size]" class="form-select mb-1" required>
                    @foreach($sizes as $s)
                        <option value="{{ $s }}" {{ $variant->size == $s ? 'selected' : '' }}>{{ $s }}</option>
                    @endforeach
                </select>
                <input type="number" name="variants[{{ $i }}][stock]" value="{{ $variant->stock }}" placeholder="Stock" class="form-control mb-1" min="0" required>
                <input type="number" name="variants[{{ $i }}][price]" value="{{ $variant->price }}" placeholder="Giá" class="form-control mb-1" step="0.01" required>
                <button type="button" class="btn btn-danger btn-sm remove-variant">Xóa</button>
            </div>
        @endforeach
    </div>
    <button type="button" id="add-variant" class="btn btn-secondary btn-sm mb-3">+ Thêm biến thể</button>

    <button type="submit" class="btn btn-primary">Cập nhật sản phẩm</button>
</form>

<script>
let variantIndex = {{ $product->variants->count() }};
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
