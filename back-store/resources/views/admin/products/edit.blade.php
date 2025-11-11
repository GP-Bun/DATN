@extends('layouts.app')

@section('content')
<div class="bg-white p-4 rounded shadow-sm">
    <h4 class="mb-3">Sửa sản phẩm: {{ $product->name }}</h4>

    <form action="{{ route('products.update', $product->id) }}" method="POST" enctype="multipart/form-data">
        @csrf
        @method('PUT')

        {{-- Thông tin sản phẩm --}}
        <div class="mb-3">
            <label class="form-label">Danh mục</label>
            <select name="category_id" class="form-select">
                <option value="">-- Chọn danh mục --</option>
                @foreach($categories as $cate)
                    <option value="{{ $cate->id }}" {{ $product->category_id == $cate->id ? 'selected' : '' }}>
                        {{ $cate->name }}
                    </option>
                @endforeach
            </select>
            @error('category_id') <small class="text-danger">{{ $message }}</small> @enderror
        </div>

        <div class="mb-3">
            <label class="form-label">Tên sản phẩm</label>
            <input type="text" name="name" class="form-control" value="{{ old('name', $product->name) }}">
            @error('name') <small class="text-danger">{{ $message }}</small> @enderror
        </div>

        <div class="mb-3">
            <label class="form-label">Giá cơ bản</label>
            <input type="number" name="price" class="form-control" value="{{ old('price', $product->price) }}">
            @error('price') <small class="text-danger">{{ $message }}</small> @enderror
        </div>

        <div class="mb-3">
            <label class="form-label">Mô tả</label>
            <textarea name="description" class="form-control" rows="3">{{ old('description', $product->description) }}</textarea>
        </div>

        <div class="mb-3">
            <label class="form-label">Ảnh sản phẩm</label><br>
            @if($product->image)
                <img src="{{ asset('storage/'.$product->image) }}" alt="Ảnh sản phẩm" class="mb-2" style="max-width:150px;">
            @endif
            <input type="file" name="image" class="form-control">
            @error('image') <small class="text-danger">{{ $message }}</small> @enderror
        </div>

        <div class="mb-3">
            <label class="form-label">Trạng thái</label>
            <select name="status" class="form-select">
                <option value="ACTIVE" {{ $product->status=='ACTIVE' ? 'selected' : '' }}>Hoạt động</option>
                <option value="INACTIVE" {{ $product->status=='INACTIVE' ? 'selected' : '' }}>Ngừng kinh doanh</option>
                <option value="OUT_OF_STOCK" {{ $product->status=='OUT_OF_STOCK' ? 'selected' : '' }}>Hết hàng</option>
            </select>
        </div>

        <hr class="my-4">
        <h5>Biến thể sản phẩm</h5>

        <div id="variants-wrapper">
            {{-- Hiển thị các biến thể hiện có --}}
            @foreach($product->variants as $i => $v)
            <div class="variant-row mb-2 row g-2 align-items-end">
                <div class="col-md-3">
                    <input type="text" name="variants[{{ $v->id }}][color]" class="form-control" value="{{ old("variants.$v->id.color", $v->color) }}" placeholder="Màu">
                </div>
                <div class="col-md-2">
                    <input type="text" name="variants[{{ $v->id }}][size]" class="form-control" value="{{ old("variants.$v->id.size", $v->size) }}" placeholder="Size">
                </div>
                <div class="col-md-3">
                    <input type="number" name="variants[{{ $v->id }}][price]" class="form-control" value="{{ old("variants.$v->id.price", $v->price) }}" placeholder="Giá">
                </div>
                <div class="col-md-2">
                    <input type="number" name="variants[{{ $v->id }}][stock]" class="form-control" value="{{ old("variants.$v->id.stock", $v->stock) }}" placeholder="Tồn kho">
                </div>
                <div class="col-md-2">
                    <button type="button" class="btn btn-danger btn-remove-variant">Xóa</button>
                </div>
            </div>
            @endforeach
        </div>

        <button type="button" id="add-variant" class="btn btn-primary mb-3">+ Thêm biến thể</button>

        <div>
            <button type="submit" class="btn btn-success">Cập nhật sản phẩm</button>
            <a href="{{ route('products.index') }}" class="btn btn-secondary">Quay lại</a>
        </div>
    </form>
</div>

{{-- JS thêm/xóa biến thể --}}
@section('scripts')
<script>
    let variantIndex = 0;

    document.getElementById('add-variant').addEventListener('click', function() {
        const wrapper = document.getElementById('variants-wrapper');
        const row = document.createElement('div');
        row.classList.add('variant-row','mb-2','row','g-2','align-items-end');
        row.innerHTML = `
            <div class="col-md-3">
                <input type="text" name="new_variants[${variantIndex}][color]" class="form-control" placeholder="Màu">
            </div>
            <div class="col-md-2">
                <input type="text" name="new_variants[${variantIndex}][size]" class="form-control" placeholder="Size">
            </div>
            <div class="col-md-3">
                <input type="number" name="new_variants[${variantIndex}][price]" class="form-control" placeholder="Giá">
            </div>
            <div class="col-md-2">
                <input type="number" name="new_variants[${variantIndex}][stock]" class="form-control" placeholder="Tồn kho">
            </div>
            <div class="col-md-2">
                <button type="button" class="btn btn-danger btn-remove-variant">Xóa</button>
            </div>
        `;
        wrapper.appendChild(row);
        variantIndex++;
    });

    document.addEventListener('click', function(e) {
        if(e.target.classList.contains('btn-remove-variant')) {
            e.target.closest('.variant-row').remove();
        }
    });@extends('layouts.app')

@section('content')
<div class="bg-white p-4 rounded shadow-sm">
    <h4 class="mb-3">Sửa sản phẩm: {{ $product->name }}</h4>

    <form action="{{ route('products.update', $product->id) }}" method="POST" enctype="multipart/form-data">
        @csrf
        @method('PUT')

        {{-- Thông tin sản phẩm --}}
        <div class="mb-3">
            <label class="form-label">Danh mục</label>
            <select name="category_id" class="form-select">
                <option value="">-- Chọn danh mục --</option>
                @foreach($categories as $cate)
                    <option value="{{ $cate->id }}" {{ $product->category_id == $cate->id ? 'selected' : '' }}>
                        {{ $cate->name }}
                    </option>
                @endforeach
            </select>
            @error('category_id') <small class="text-danger">{{ $message }}</small> @enderror
        </div>

        <div class="mb-3">
            <label class="form-label">Tên sản phẩm</label>
            <input type="text" name="name" class="form-control" value="{{ old('name', $product->name) }}">
            @error('name') <small class="text-danger">{{ $message }}</small> @enderror
        </div>

        <div class="mb-3">
            <label class="form-label">Giá cơ bản</label>
            <input type="number" name="price" class="form-control" value="{{ old('price', $product->price) }}">
            @error('price') <small class="text-danger">{{ $message }}</small> @enderror
        </div>

        <div class="mb-3">
            <label class="form-label">Mô tả</label>
            <textarea name="description" class="form-control" rows="3">{{ old('description', $product->description) }}</textarea>
        </div>

        <div class="mb-3">
            <label class="form-label">Ảnh sản phẩm</label><br>
            @if($product->image)
                <img src="{{ asset('storage/'.$product->image) }}" alt="Ảnh sản phẩm" class="mb-2" style="max-width:150px;">
            @endif
            <input type="file" name="image" class="form-control">
            @error('image') <small class="text-danger">{{ $message }}</small> @enderror
        </div>

        <div class="mb-3">
            <label class="form-label">Trạng thái</label>
            <select name="status" class="form-select">
                <option value="ACTIVE" {{ $product->status=='ACTIVE' ? 'selected' : '' }}>Hoạt động</option>
                <option value="INACTIVE" {{ $product->status=='INACTIVE' ? 'selected' : '' }}>Ngừng kinh doanh</option>
                <option value="OUT_OF_STOCK" {{ $product->status=='OUT_OF_STOCK' ? 'selected' : '' }}>Hết hàng</option>
            </select>
        </div>

        <hr class="my-4">
        <h5>Biến thể sản phẩm</h5>

        <div id="variants-wrapper">
            @foreach($product->variants as $v)
            <div class="variant-row row g-2 mb-2 align-items-end">
                <div class="col-md-2 col-sm-3">
                    <input type="text" name="variants[{{ $v->id }}][color]" class="form-control" value="{{ old("variants.$v->id.color", $v->color) }}" placeholder="Màu">
                </div>
                <div class="col-md-2 col-sm-3">
                    <input type="text" name="variants[{{ $v->id }}][size]" class="form-control" value="{{ old("variants.$v->id.size", $v->size) }}" placeholder="Size">
                </div>
                <div class="col-md-2 col-sm-3">
                    <input type="number" name="variants[{{ $v->id }}][price]" class="form-control" value="{{ old("variants.$v->id.price", $v->price) }}" placeholder="Giá">
                </div>
                <div class="col-md-2 col-sm-3">
                    <input type="number" name="variants[{{ $v->id }}][stock]" class="form-control" value="{{ old("variants.$v->id.stock", $v->stock) }}" placeholder="Tồn kho">
                </div>
                <div class="col-md-2 col-sm-12">
                    <button type="button" class="btn btn-danger btn-remove-variant w-100">Xóa</button>
                </div>
            </div>
            @endforeach
        </div>

        <button type="button" id="add-variant" class="btn btn-primary mb-3">+ Thêm biến thể</button>

        <div>
            <button type="submit" class="btn btn-success">Cập nhật sản phẩm</button>
            <a href="{{ route('products.index') }}" class="btn btn-secondary">Quay lại</a>
        </div>
    </form>
</div>

@section('scripts')
<script>
let variantIndex = 0;

document.getElementById('add-variant').addEventListener('click', function() {
    const wrapper = document.getElementById('variants-wrapper');
    const row = document.createElement('div');
    row.classList.add('variant-row','row','g-2','mb-2','align-items-end');
    row.innerHTML = `
        <div class="col-md-2 col-sm-3">
            <input type="text" name="new_variants[${variantIndex}][color]" class="form-control" placeholder="Màu">
        </div>
        <div class="col-md-2 col-sm-3">
            <input type="text" name="new_variants[${variantIndex}][size]" class="form-control" placeholder="Size">
        </div>
        <div class="col-md-2 col-sm-3">
            <input type="number" name="new_variants[${variantIndex}][price]" class="form-control" placeholder="Giá">
        </div>
        <div class="col-md-2 col-sm-3">
            <input type="number" name="new_variants[${variantIndex}][stock]" class="form-control" placeholder="Tồn kho">
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

</script>
@endsection
