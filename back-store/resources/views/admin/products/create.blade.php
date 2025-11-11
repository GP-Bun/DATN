@extends('layouts.app')

@section('content')
<div class="bg-white p-4 rounded shadow-sm">
    <h4 class="mb-3">Thêm sản phẩm mới</h4>

    <form action="{{ route('products.store') }}" method="POST" enctype="multipart/form-data">
        @csrf

        {{-- Thông tin sản phẩm --}}
        <div class="mb-3">
            <label class="form-label">Danh mục</label>
            <select name="category_id" class="form-select">
                <option value="">-- Chọn danh mục --</option>
                @foreach($categories as $cate)
                    <option value="{{ $cate->id }}" {{ old('category_id') == $cate->id ? 'selected' : '' }}>
                        {{ $cate->name }}
                    </option>
                @endforeach
            </select>
            @error('category_id') <small class="text-danger">{{ $message }}</small> @enderror
        </div>

        <div class="mb-3">
            <label class="form-label">Tên sản phẩm</label>
            <input type="text" name="name" class="form-control" value="{{ old('name') }}">
            @error('name') <small class="text-danger">{{ $message }}</small> @enderror
        </div>

        <div class="mb-3">
            <label class="form-label">Giá cơ bản</label>
            <input type="number" name="price" class="form-control" value="{{ old('price') }}">
            @error('price') <small class="text-danger">{{ $message }}</small> @enderror
        </div>

        <div class="mb-3">
            <label class="form-label">Mô tả</label>
            <textarea name="description" class="form-control" rows="3">{{ old('description') }}</textarea>
        </div>

        <div class="mb-3">
            <label class="form-label">Ảnh sản phẩm</label>
            <input type="file" name="image" class="form-control">
            @error('image') <small class="text-danger">{{ $message }}</small> @enderror
        </div>

        <div class="mb-3">
            <label class="form-label">Trạng thái</label>
            <select name="status" class="form-select">
                <option value="ACTIVE" {{ old('status')=='ACTIVE' ? 'selected' : '' }}>Hoạt động</option>
                <option value="INACTIVE" {{ old('status')=='INACTIVE' ? 'selected' : '' }}>Ngừng kinh doanh</option>
                <option value="OUT_OF_STOCK" {{ old('status')=='OUT_OF_STOCK' ? 'selected' : '' }}>Hết hàng</option>
            </select>
        </div>

        <hr class="my-4">
        <h5>Biến thể sản phẩm</h5>

        <div id="variants-wrapper">
            @if(old('variants'))
                @foreach(old('variants') as $i => $v)
                <div class="variant-row row g-2 mb-2 align-items-end">
                    <div class="col-md-2 col-sm-3">
                        <input type="text" name="variants[{{ $i }}][color]" class="form-control" placeholder="Màu" value="{{ $v['color'] ?? '' }}">
                    </div>
                    <div class="col-md-2 col-sm-3">
                        <input type="text" name="variants[{{ $i }}][size]" class="form-control" placeholder="Size" value="{{ $v['size'] ?? '' }}">
                    </div>
                    <div class="col-md-2 col-sm-3">
                        <input type="number" name="variants[{{ $i }}][price]" class="form-control" placeholder="Giá" value="{{ $v['price'] ?? '' }}">
                    </div>
                    <div class="col-md-2 col-sm-3">
                        <input type="number" name="variants[{{ $i }}][stock]" class="form-control" placeholder="Tồn kho" value="{{ $v['stock'] ?? '' }}">
                    </div>
                    <div class="col-md-2 col-sm-12">
                        <button type="button" class="btn btn-danger btn-remove-variant w-100">Xóa</button>
                    </div>
                </div>
                @endforeach
            @else
            <div class="variant-row row g-2 mb-2 align-items-end">
                <div class="col-md-2 col-sm-3">
                    <input type="text" name="variants[0][color]" class="form-control" placeholder="Màu">
                </div>
                <div class="col-md-2 col-sm-3">
                    <input type="text" name="variants[0][size]" class="form-control" placeholder="Size">
                </div>
                <div class="col-md-2 col-sm-3">
                    <input type="number" name="variants[0][price]" class="form-control" placeholder="Giá">
                </div>
                <div class="col-md-2 col-sm-3">
                    <input type="number" name="variants[0][stock]" class="form-control" placeholder="Tồn kho">
                </div>
                <div class="col-md-2 col-sm-12">
                    <button type="button" class="btn btn-danger btn-remove-variant w-100">Xóa</button>
                </div>
            </div>
            @endif
        </div>

        <button type="button" id="add-variant" class="btn btn-primary mb-3">+ Thêm biến thể</button>

        <div>
            <button type="submit" class="btn btn-success">Lưu sản phẩm</button>
            <a href="{{ route('products.index') }}" class="btn btn-secondary">Quay lại</a>
        </div>
    </form>
</div>

@section('scripts')
<script>
let variantIndex = {{ old('variants') ? count(old('variants')) : 1 }};

document.getElementById('add-variant').addEventListener('click', function() {
    const wrapper = document.getElementById('variants-wrapper');
    const row = document.createElement('div');
    row.classList.add('variant-row','row','g-2','mb-2','align-items-end');
    row.innerHTML = `
        <div class="col-md-2 col-sm-3">
            <input type="text" name="variants[${variantIndex}][color]" class="form-control" placeholder="Màu">
        </div>
        <div class="col-md-2 col-sm-3">
            <input type="text" name="variants[${variantIndex}][size]" class="form-control" placeholder="Size">
        </div>
        <div class="col-md-2 col-sm-3">
            <input type="number" name="variants[${variantIndex}][price]" class="form-control" placeholder="Giá">
        </div>
        <div class="col-md-2 col-sm-3">
            <input type="number" name="variants[${variantIndex}][stock]" class="form-control" placeholder="Tồn kho">
        </div>
        <div class="col-md-2 col-sm-12">
            <button type="button" class="btn btn-danger btn-remove-variant w-100">Xóa</button>
        </div>
    `;
    wrapper.appendChild(row);
    variantIndex++;
});

document.addEventListener('click', function(e){
    if(e.target.classList.contains('btn-remove-variant')){
        e.target.closest('.variant-row').remove();
    }
});
</script>
@endsection
