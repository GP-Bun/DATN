<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin - @yield('title', 'Admin Panel')</title>
    <link rel="stylesheet" href="{{ asset('css/dashboard.css') }}">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        body {
            font-family: "Poppins", sans-serif;
            background-color: #f8fafc;
            margin: 0;
        }

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

        .main-content {
            margin-left: 220px;
            padding: 20px;
            transition: all 0.3s;
            min-height: 100vh;
        }

        .main-content.full-width {
            margin-left: 0;
        }

        .navbar {
            background-color: #0f172a;
            border-radius: 8px;
        }

        .navbar .navbar-brand {
            color: #f8fafc !important;
            font-weight: 500;
        }

        #sidebar-toggle {
            padding: 8px 12px;
            border: 1px solid rgba(255, 255, 255, 0.3);
            background-color: transparent;
            color: #f8fafc;
            border-radius: 6px;
            cursor: pointer;
            transition: all 0.2s;
            display: flex;
            align-items: center;
            justify-content: center;
            min-width: 40px;
            height: 40px;
        }

        #sidebar-toggle:hover {
            background-color: rgba(255, 255, 255, 0.1);
            border-color: rgba(255, 255, 255, 0.5);
            color: #fff;
        }

        #sidebar-toggle i {
            font-size: 20px;
        }

        .alert {
            border-radius: 10px;
        }

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
        <a href="{{ route('admin.dashboard') }}" class="{{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">
            <i class="bi bi-speedometer2"></i> Dashboard
        </a>

        @auth
            @if (Auth::user()->role === 'admin')
                <a href="{{ route('admin.categories.index') }}"
                    class="{{ request()->routeIs('admin.categories.*') ? 'active' : '' }}">
                    <i class="bi bi-folder"></i> Danh mục
                </a>
                <a href="{{ route('admin.users.index') }}"
                    class="{{ request()->routeIs('admin.users.*') ? 'active' : '' }}">
                    <i class="bi bi-people"></i> Người dùng
                </a>
                <a href="{{ route('admin.coupons.index') }}"
                    class="{{ request()->routeIs('admin.coupons.*') ? 'active' : '' }}">
                    <i class="bi bi-ticket-perforated"></i> Voucher
                </a>
            @endif
        @endauth

        <a href="{{ route('admin.products.index') }}"
            class="{{ request()->routeIs('admin.products.*') ? 'active' : '' }}">
            <i class="bi bi-box"></i> Sản phẩm
        </a>

        <a href="{{ route('admin.orders.index') }}"
            class="{{ request()->routeIs('admin.orders.*') ? 'active' : '' }}">
            <i class="bi bi-cart-check"></i> Đơn hàng
        </a>

        <a href="{{ route('admin.reviews.index') }}"
            class="{{ request()->routeIs('admin.reviews.*') ? 'active' : '' }}">
            <i class="bi bi-chat-dots"></i> Đánh giá
        </a>

        <a href="{{ route('admin.chat.index') }}" class="{{ request()->routeIs('admin.chat.*') ? 'active' : '' }}">
            <i class="bi bi-headset"></i> Hỗ trợ chat
        </a>
    </div>

    <!-- Main Content -->
    <div class="main-content" id="main-content">
        <nav class="navbar navbar-dark mb-3 px-3 d-flex justify-content-between align-items-center">
            <div class="d-flex align-items-center gap-3">
                <button class="btn btn-outline-light d-md-none" id="sidebar-toggle">
                    <i class="bi bi-list"></i>
                </button>
                <span class="navbar-brand mb-0 h1">@yield('title')</span>
            </div>

            <div class="d-flex align-items-center gap-3">
                @auth
                    <div class="text-white d-flex align-items-center gap-2">
                        <i class="bi bi-person-circle" style="font-size: 24px;"></i>
                        <div class="d-none d-sm-block">
                            <div style="font-size: 14px; font-weight: 500;">{{ Auth::user()->name }}</div>
                            <div style="font-size: 11px; opacity: 0.7;">
                                @if (Auth::user()->role === 'admin')
                                    <span class="badge bg-primary">Admin</span>
                                @else
                                    <span class="badge bg-success">Nhân viên</span>
                                @endif
                            </div>
                        </div>
                    </div>
                    <form action="{{ route('admin.logout') }}" method="POST" class="d-inline">
                        @csrf
                        <button type="submit" class="btn btn-outline-danger btn-sm">
                            <i class="bi bi-box-arrow-right"></i>
                            <span class="d-none d-sm-inline">Đăng xuất</span>
                        </button>
                    </form>
                @endauth
            </div>
        </nav>

        @if (session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="bi bi-check-circle me-2"></i>{{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        @if (session('error'))
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="bi bi-exclamation-circle me-2"></i>{{ session('error') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

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
