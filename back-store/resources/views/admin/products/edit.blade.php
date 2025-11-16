@extends('layouts.app')

@section('page-title', 'Sửa sản phẩm')

@section('content')
<div class="bg-white p-4 rounded shadow-sm">
    <h4 class="mb-3">Sửa sản phẩm: {{ $product->name }}</h4>

    <form action="{{ route('admin.products.update', $product->id) }}" method="POST" enctype="multipart/form-data">
        @csrf
        @method('PUT')

        @php
            $sizes = ['36','37','38','39','40','41','42','43','44'];
            $colors = ['Đen','Trắng','Đỏ','Xanh'];
        @endphp

        <div class="mb-3">
            <label class="form-label">Tên sản phẩm</label>
            <input type="text" name="name" class="form-control" value="{{ old('name', $product->name) }}" required>
        </div>

        <div class="mb-3">
            <label class="form-label">Danh mục</label>
            <select name="category_id" class="form-select" required>
                @foreach($categories as $cate)
                    <option value="{{ $cate->id }}" {{ old('category_id', $product->category_id) == $cate->id ? 'selected' : '' }}>
                        {{ $cate->name }}
                    </option>
                @endforeach
            </select>
        </div>

        <div class="mb-3">
            <label class="form-label">Giá cơ bản</label>
            <input type="number" name="price" class="form-control" value="{{ old('price', $product->price) }}" min="0" required>
        </div>

        <div class="mb-3">
            <label class="form-label">Trạng thái</label>
            <select name="status" class="form-select" required>
                <option value="ACTIVE" {{ old('status', $product->status)=='ACTIVE' ? 'selected' : '' }}>Hoạt động</option>
                <option value="INACTIVE" {{ old('status', $product->status)=='INACTIVE' ? 'selected' : '' }}>Ngừng kinh doanh</option>
                <option value="OUT_OF_STOCK" {{ old('status', $product->status)=='OUT_OF_STOCK' ? 'selected' : '' }}>Hết hàng</option>
            </select>
        </div>

        <div class="mb-3">
            <label class="form-label">Ảnh sản phẩm</label><br>
            @if($product->image)
                <img src="{{ asset('storage/'.$product->image) }}" alt="Ảnh sản phẩm" class="mb-2" style="max-width:150px;">
            @endif
            <input type="file" name="image" class="form-control">
        </div>

        <hr class="my-4">
        <h5>Biến thể sản phẩm</h5>
        <div id="variants-wrapper">
            @foreach($product->variants as $i => $variant)
                <div class="variant-row row g-2 mb-2 align-items-end border p-2 rounded">
                    <div class="col-md-2">
                        <select name="variants[{{ $i }}][color]" class="form-select" required>
                            @foreach($colors as $c)
                                <option value="{{ $c }}" {{ old("variants.$i.color", $variant->color) == $c ? 'selected' : '' }}>{{ $c }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2">
                        <select name="variants[{{ $i }}][size]" class="form-select" required>
                            @foreach($sizes as $s)
                                <option value="{{ $s }}" {{ old("variants.$i.size", $variant->size) == $s ? 'selected' : '' }}>{{ $s }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2">
                        <input type="number" name="variants[{{ $i }}][price]" class="form-control" value="{{ old("variants.$i.price", $variant->price) }}" placeholder="Giá" required>
                    </div>
                    <div class="col-md-2">
                        <input type="number" name="variants[{{ $i }}][stock]" class="form-control" value="{{ old("variants.$i.stock", $variant->stock) }}" placeholder="Tồn kho" required>
                    </div>
                    <div class="col-md-2">
                        <button type="button" class="btn btn-danger btn-remove-variant w-100">Xóa</button>
                    </div>
                </div>
            @endforeach
        </div>

        <button type="button" id="add-variant" class="btn btn-primary mb-3">+ Thêm biến thể</button>

        <div>
            <button type="submit" class="btn btn-success">Cập nhật sản phẩm</button>
            <a href="{{ route('admin.products.index') }}" class="btn btn-secondary">Quay lại</a>
        </div>
    </form>
</div>

@section('scripts')
<script>
let variantIndex = {{ $product->variants->count() }};
const wrapper = document.getElementById('variants-wrapper');
const sizes = @json($sizes);
const colors = @json($colors);

document.getElementById('add-variant').addEventListener('click', function() {
    const div = document.createElement('div');
    div.classList.add('variant-row', 'row', 'g-2', 'mb-2', 'align-items-end', 'border', 'p-2', 'rounded');

    let colorOptions = colors.map(c => `<option value="${c}">${c}</option>`).join('');
    let sizeOptions = sizes.map(s => `<option value="${s}">${s}</option>`).join('');

    div.innerHTML = `
        <div class="col-md-2">
            <select name="variants[${variantIndex}][color]" class="form-select" required>${colorOptions}</select>
        </div>
        <div class="col-md-2">
            <select name="variants[${variantIndex}][size]" class="form-select" required>${sizeOptions}</select>
        </div>
        <div class="col-md-2">
            <input type="number" name="variants[${variantIndex}][price]" placeholder="Giá" class="form-control" required>
        </div>
        <div class="col-md-2">
            <input type="number" name="variants[${variantIndex}][stock]" placeholder="Tồn kho" class="form-control" required>
        </div>
        <div class="col-md-2">
            <button type="button" class="btn btn-danger btn-remove-variant w-100">Xóa</button>
        </div>
    `;
    wrapper.appendChild(div);
    variantIndex++;
});

wrapper.addEventListener('click', function(e){
    if(e.target.classList.contains('btn-remove-variant')){
        e.target.closest('.variant-row').remove();
    }
});
</script>
@endsection
