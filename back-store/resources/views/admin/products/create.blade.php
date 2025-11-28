@extends('layouts.app')

@section('page-title', 'Thêm sản phẩm')

@section('content')
    <div class="bg-white p-4 rounded shadow-sm">
        <h4 class="mb-3">Thêm sản phẩm mới</h4>

        @if (session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif

        @if ($errors->any())
            <div class="alert alert-danger">
                <ul>
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form action="{{ route('admin.products.store') }}" method="POST" enctype="multipart/form-data">
            @csrf

            {{-- Tên sản phẩm --}}
            <div class="mb-3">
                <label class="form-label">Tên sản phẩm</label>
                <input type="text" name="name" class="form-control" value="{{ old('name') }}" required>
                @error('name')
                    <small class="text-danger">{{ $message }}</small>
                @enderror
            </div>

            {{-- Danh mục --}}
            <div class="mb-3">
                <label class="form-label">Danh mục</label>
                <select name="category_id" class="form-select" required>
                    <option value="">-- Chọn danh mục --</option>
                    @foreach ($categories as $cate)
                        <option value="{{ $cate->id }}" {{ old('category_id') == $cate->id ? 'selected' : '' }}>
                            {{ $cate->name }}
                        </option>
                    @endforeach
                </select>
                @error('category_id')
                    <small class="text-danger">{{ $message }}</small>
                @enderror
            </div>

            {{-- Mô tả --}}
            <div class="mb-3">
                <label class="form-label">Mô tả</label>
                <textarea name="description" class="form-control" rows="3">{{ old('description') }}</textarea>
            </div>

            {{-- Trạng thái --}}
            <div class="mb-3">
                <label class="form-label">Trạng thái</label>
                <select name="status" class="form-select" required>
                    <option value="1" {{ old('status') == 1 ? 'selected' : '' }}>Còn hàng</option>
                    <option value="2" {{ old('status') == 2 ? 'selected' : '' }}>Hết hàng</option>
                    <option value="0" {{ old('status') == 0 ? 'selected' : '' }}>Ẩn sản phẩm</option>
                </select>
                @error('status')
                    <small class="text-danger">{{ $message }}</small>
                @enderror
            </div>

            {{-- Ảnh đại diện --}}
            <div class="mb-3">
                <label class="form-label">Ảnh đại diện</label>
                <input type="file" name="thumbnail" class="form-control" required>
                <img id="preview-thumbnail" src="" class="mt-2" style="width:120px; display:none;">
                @error('thumbnail')
                    <small class="text-danger">{{ $message }}</small>
                @enderror
            </div>

            {{-- Ảnh bổ sung --}}
            <div class="mb-3">
                <label class="form-label">Ảnh bổ sung</label>
                <input type="file" name="images[]" multiple class="form-control">
                <div id="preview-images" class="d-flex mt-2" style="gap:10px;"></div>
                @error('images.*')
                    <small class="text-danger">{{ $message }}</small>
                @enderror
            </div>

            <hr>

            <h5>Biến thể sản phẩm</h5>
            <p class="text-muted">Mỗi màu có thể chọn nhiều size → Hệ thống sẽ tạo ra từng biến thể con</p>

            @php
                $sizes = ['36', '37', '38', '39', '40', '41', '42', '43', '44'];
                $oldVariants = old('variants', [[]]); // nếu không có old thì tạo 1 biến thể mẫu
            @endphp

            <div id="variants-wrapper">
                @foreach ($oldVariants as $i => $variant)
                    <div class="variant-row row g-2 mb-3 p-3 border rounded">
                        <div class="col-md-2">
                            <label>Màu</label>
                            <input type="text" name="variants[{{ $i }}][color]" class="form-control"
                                value="{{ $variant['color'] ?? '' }}" required>
                        </div>

                        <div class="col-md-4">
                            <label>Size</label>
                            <div class="d-flex flex-wrap">
                                @foreach ($sizes as $size)
                                    <div class="form-check me-2">
                                        <input type="checkbox" class="form-check-input"
                                            name="variants[{{ $i }}][sizes][]" value="{{ $size }}"
                                            {{ in_array($size, $variant['sizes'] ?? []) ? 'checked' : '' }}>
                                        <label class="form-check-label">{{ $size }}</label>
                                    </div>
                                @endforeach
                            </div>
                        </div>

                        <div class="col-md-2">
                            <label>Giá gốc</label>
                            <input type="number" name="variants[{{ $i }}][original_price]" class="form-control"
                                value="{{ $variant['original_price'] ?? '' }}" required>
                        </div>

                        <div class="col-md-2">
                            <label>Giá giảm</label>
                            <input type="number" name="variants[{{ $i }}][sale_price]" class="form-control"
                                value="{{ $variant['sale_price'] ?? '' }}">
                        </div>

                        <div class="col-md-1">
                            <label>Tồn kho</label>
                            <input type="number" name="variants[{{ $i }}][stock]" class="form-control"
                                value="{{ $variant['stock'] ?? '' }}" required>
                        </div>

                        <div class="col-md-1 d-flex align-items-end">
                            <button type="button" class="btn btn-danger btn-remove-variant w-100">Xóa</button>
                        </div>
                    </div>
                @endforeach
            </div>

            <button type="button" class="btn btn-primary mb-3" id="add-variant">+ Thêm màu</button>

            <div class="mt-3">
                <button type="submit" class="btn btn-success">Lưu sản phẩm</button>
                <a href="{{ route('admin.products.index') }}" class="btn btn-secondary">Quay lại</a>
            </div>
        </form>
    </div>
@endsection

@section('scripts')
    <script>
        let variantIndex = {{ count($oldVariants) }};
        const sizes = @json($sizes);

        // Preview Thumbnail
        document.querySelector('input[name="thumbnail"]').addEventListener('change', function(e) {
            const file = e.target.files[0];
            if (file) {
                const img = document.getElementById('preview-thumbnail');
                img.src = URL.createObjectURL(file);
                img.style.display = 'block';
            }
        });

        // Preview Multi Images
        document.querySelector('input[name="images[]"]').addEventListener('change', function(e) {
            const box = document.getElementById('preview-images');
            box.innerHTML = "";
            Array.from(e.target.files).forEach(f => {
                const img = document.createElement('img');
                img.src = URL.createObjectURL(f);
                img.style.width = "80px";
                img.style.height = "80px";
                img.style.objectFit = "cover";
                img.classList.add("rounded");
                box.appendChild(img);
            });
        });

        // Add Variant Row
        document.getElementById('add-variant').addEventListener('click', () => {
            let htmlSizes = "";
            sizes.forEach(size => {
                htmlSizes += `
                <div class="form-check me-2">
                    <input class="form-check-input" type="checkbox"
                           name="variants[${variantIndex}][sizes][]"
                           id="v${variantIndex}_size${size}"
                           value="${size}">
                    <label class="form-check-label" for="v${variantIndex}_size${size}">${size}</label>
                </div>
            `;
            });

            const div = document.createElement('div');
            div.className = "variant-row row g-2 mb-3 p-3 border rounded";

            div.innerHTML = `
            <div class="col-md-2">
                <label>Màu</label>
                <input type="text" name="variants[${variantIndex}][color]" class="form-control" required>
            </div>
            <div class="col-md-4">
                <label>Size</label>
                <div class="d-flex flex-wrap">${htmlSizes}</div>
            </div>
                    <div class="col-md-2">
            <label>Giá gốc</label>
            <input type="number" name="variants[${variantIndex}][original_price]" class="form-control" required>
        </div>
        <div class="col-md-2">
            <label>Giá giảm</label>
            <input type="number" name="variants[${variantIndex}][sale_price]" class="form-control">
        </div>
        <div class="col-md-1">
            <label>Tồn kho</label>
            <input type="number" name="variants[${variantIndex}][stock]" class="form-control" required>
        </div>
        <div class="col-md-1 d-flex align-items-end">
            <button type="button" class="btn btn-danger btn-remove-variant w-100">Xóa</button>
        </div>
    `;

            document.getElementById('variants-wrapper').appendChild(div);
            variantIndex++;
        });

        // Remove Variant Row
        document.getElementById('variants-wrapper').addEventListener('click', function(e) {
            if (e.target.classList.contains('btn-remove-variant')) {
                e.target.closest('.variant-row').remove();
            }
        });
    </script>
@endsection
