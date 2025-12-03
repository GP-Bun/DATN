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

            <div class="mb-3">
                <label class="form-label">Tên sản phẩm</label>
                <input type="text" name="name" class="form-control" value="{{ old('name') }}" required>
            </div>

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
            </div>

            <div class="mb-3">
                <label class="form-label">Mô tả</label>
                <textarea name="description" class="form-control" rows="3">{{ old('description') }}</textarea>
            </div>

            <div class="mb-3">
                <label class="form-label">Trạng thái</label>
                <select name="status" class="form-select" required>
                    <option value="1" {{ old('status') == 1 ? 'selected' : '' }}>Còn hàng</option>
                    <option value="2" {{ old('status') == 2 ? 'selected' : '' }}>Hết hàng</option>
                    <option value="0" {{ old('status') == 0 ? 'selected' : '' }}>Ẩn sản phẩm</option>
                </select>
            </div>

            <div class="mb-3">
                <label class="form-label">Ảnh đại diện</label>
                <input type="file" name="thumbnail" class="form-control" required>
                <img id="preview-thumbnail" src="" class="mt-2" style="width:120px; display:none;">
            </div>

            <div class="mb-3">
                <label class="form-label">Ảnh bổ sung</label>
                <input type="file" name="images[]" multiple class="form-control">
                <div id="preview-images" class="d-flex mt-2" style="gap:10px;"></div>
            </div>

            <hr>
            <h5>Biến thể sản phẩm</h5>

            @php $oldVariants = old('variants', [[]]); @endphp

            <div id="variants-wrapper">
                @foreach ($oldVariants as $i => $variant)
                    <div class="variant-row row g-2 mb-3 p-3 border rounded">
                        <div class="col-md-2">
                            <label>Màu</label>
                            <select name="variants[{{ $i }}][color_id]" class="form-select" required>
                                <option value="">-- Chọn màu --</option>
                                @foreach ($colors as $color)
                                    <option value="{{ $color->id }}"
                                        {{ isset($variant['color_id']) && $variant['color_id'] == $color->id ? 'selected' : '' }}>
                                        {{ $color->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-4">
                            <label>Size</label>
                            <div class="d-flex flex-wrap">
                                @foreach ($sizes as $size)
                                    <div class="form-check me-2">
                                        <input type="checkbox" class="form-check-input"
                                            name="variants[{{ $i }}][sizes][]" value="{{ $size->id }}"
                                            {{ in_array($size->id, $variant['sizes'] ?? []) ? 'checked' : '' }}>
                                        <label class="form-check-label">{{ $size->value }}</label>
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

            <div class="mb-3 d-flex gap-2">
                <button type="button" class="btn btn-primary" id="add-variant">+ Thêm biến thể</button>
                <button type="button" class="btn btn-outline-success" data-bs-toggle="modal"
                    data-bs-target="#modalAddColor">+ Thêm màu</button>
                <button type="button" class="btn btn-outline-info" data-bs-toggle="modal" data-bs-target="#modalAddSize">+
                    Thêm size</button>
            </div>

            <div class="mt-3">
                <button type="submit" class="btn btn-success">Lưu sản phẩm</button>
                <a href="{{ route('admin.products.index') }}" class="btn btn-secondary">Quay lại</a>
            </div>
        </form>
    </div>

    {{-- Modal thêm màu --}}
    <div class="modal fade" id="modalAddColor" tabindex="-1">
        <div class="modal-dialog">
            <form method="POST" action="{{ route('admin.colors.store') }}">
                @csrf
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Thêm màu mới</h5>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label>Tên màu</label>
                            <input type="text" name="name" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label>Mã màu</label>
                            <input type="color" name="code" class="form-control form-control-color" value="#000000"
                                required>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="submit" class="btn btn-success">Lưu</button>
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Đóng</button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    {{-- Modal thêm size --}}
    <div class="modal fade" id="modalAddSize" tabindex="-1">
        <div class="modal-dialog">
            <form method="POST" action="{{ route('admin.sizes.store') }}">
                @csrf
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Thêm size mới</h5>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label>Giá trị size</label>
                            <input type="number" name="value" class="form-control" required>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="submit" class="btn btn-success">Lưu</button>
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Đóng</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
@endsection
@section('scripts')
    <script>
        let variantIndex = {{ count($oldVariants) }};
        const colors = @json($colors);
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
            let htmlColors = `<option value="">-- Chọn màu --</option>`;
            colors.forEach(color => {
                htmlColors += `<option value="${color.id}">${color.name}</option>`;
            });

            let htmlSizes = "";
            sizes.forEach(size => {
                htmlSizes += `
                <div class="form-check me-2">
                    <input class="form-check-input" type="checkbox"
                           name="variants[${variantIndex}][sizes][]"
                           value="${size.id}">
                    <label class="form-check-label">${size.value}</label>
                </div>
            `;
            });

            const div = document.createElement('div');
            div.className = "variant-row row g-2 mb-3 p-3 border rounded";

            div.innerHTML = `
            <div class="col-md-2">
                <label>Màu</label>
                <select name="variants[${variantIndex}][color_id]" class="form-select" required>
                    ${htmlColors}
                </select>
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
