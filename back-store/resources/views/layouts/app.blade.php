<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin - @yield('title', 'Admin Panel')</title>

    <!-- Bootstrap & Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">

    <style>
        body {
            font-family: "Poppins", sans-serif;
            background-color: #f8fafc;
            margin: 0;
        }

        /* Sidebar */
        .sidebar {
            background-color: #1e293b;
            color: #fff;
            position: fixed;
            top: 0;
            left: 0;
            bottom: 0;
            width: 220px;
            padding-top: 25px;
            display: flex;
            flex-direction: column;
            box-shadow: 2px 0 8px rgba(0, 0, 0, 0.1);
            transition: all 0.3s;
            z-index: 1000;
        }

        .sidebar.hidden {
            left: -250px;
        }

        .sidebar h4 {
            font-size: 18px;
            font-weight: 600;
            text-align: center;
            margin-bottom: 20px;
            color: #f1f5f9;
        }

        .sidebar a {
            color: #cbd5e1;
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 10px 20px;
            text-decoration: none;
            border-radius: 8px;
            margin: 2px 10px;
            transition: 0.2s;
            white-space: nowrap;
        }

        .sidebar a:hover,
        .sidebar a.active {
            background-color: #334155;
            color: #fff;
        }

        /* Main Content */
        .main-content {
            margin-left: 220px;
            padding: 20px;
            transition: all 0.3s;
            min-height: 100vh;
        }

        .main-content.full-width {
            margin-left: 0;
        }

        /* Navbar */
        .navbar {
            background-color: #0f172a;
            border-radius: 8px;
        }

        .navbar .navbar-brand {
            color: #f8fafc !important;
            font-weight: 500;
        }

        .alert {
            border-radius: 10px;
        }

        /* Responsive */
        @media (max-width: 768px) {
            .sidebar {
                left: -250px;
                position: fixed;
                height: 100%;
            }

            .main-content {
                margin-left: 0;
            }
        }
    </style>
</head>

<body>
    <!-- Sidebar -->
    <div class="sidebar" id="sidebar">
        <h4>🛍️ Admin Panel</h4>
        <a href="{{ route('admin.categories.index') }}"
            class="{{ request()->routeIs('admin.categories.*') ? 'active' : '' }}">
            <i class="bi bi-folder"></i> Danh mục
        </a>
        <a href="{{ route('admin.products.index') }}"
            class="{{ request()->routeIs('admin.products.*') ? 'active' : '' }}">
            <i class="bi bi-box"></i> Sản phẩm
        </a>
        <a href="{{ route('admin.users.index') }}" class="{{ request()->routeIs('admin.users.*') ? 'active' : '' }}">
            <i class="bi bi-people"></i> Người dùng
        </a>

        <!-- Thêm quản lý tài khoản -->
        <a href="{{ route('admin.accounts.index') }}"
            class="{{ request()->routeIs('admin.accounts.*') ? 'active' : '' }}">
            <i class="bi bi-person-badge"></i> Tài khoản
        </a>

        <!-- Thêm quản lý đơn hàng -->
        <a href="{{ route('admin.orders.index') }}" class="{{ request()->routeIs('admin.orders.*') ? 'active' : '' }}">
            <i class="bi bi-cart-check"></i> Đơn hàng
        </a>
    </div>

    <!-- Main Content -->
    <div class="main-content" id="main-content">
        <nav class="navbar navbar-dark mb-3 px-3 d-flex justify-content-between">
            <span class="navbar-brand mb-0 h1">@yield('title')</span>
            <button class="btn btn-outline-light d-md-none" id="sidebar-toggle">
                <i class="bi bi-list"></i>
            </button>
        </nav>

        @if (session('success'))
            <div class="alert alert-success">
                {{ session('success') }}
            </div>
        @endif

        @if (session('error'))
            <div class="alert alert-danger">
                {{ session('error') }}
            </div>
        @endif

        {{-- Responsive wrapper cho bảng/form --}}
        <div class="table-responsive">
            @yield('content')
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        const toggleBtn = document.getElementById('sidebar-toggle');
        const sidebar = document.getElementById('sidebar');
        const mainContent = document.getElementById('main-content');

        toggleBtn?.addEventListener('click', () => {
            sidebar.classList.toggle('hidden');
            mainContent.classList.toggle('full-width');
        });
    </script>
    @yield('scripts')
</body>

</html>
