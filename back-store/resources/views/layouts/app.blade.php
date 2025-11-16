<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Admin Panel')</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

    <style>
        /* Sidebar */
        .sidebar {
            height: 100vh;
            position: fixed;
            top: 0;
            left: 0;
            width: 250px;
            background-color: #f8f9fa;
            padding: 20px;
            border-right: 1px solid #dee2e6;
        }

        .sidebar h4 {
            font-weight: bold;
            margin-bottom: 20px;
        }

        .sidebar a {
            display: block;
            padding: 8px 12px;
            margin-bottom: 5px;
            color: #333;
            text-decoration: none;
            border-radius: 5px;
        }

        .sidebar a:hover, .sidebar a.active {
            background-color: #0d6efd;
            color: white;
        }

        /* Main content */
        .main-content {
            margin-left: 270px;
            padding: 30px;
        }

        /* Page header */
        .page-header {
            margin-bottom: 20px;
        }

        .page-title {
            font-size: 2rem;
            font-weight: 600;
        }

        .btn-primary-custom {
            background-color: #0d6efd;
            color: white;
            border-radius: 5px;
            padding: 5px 12px;
        }

        .btn-primary-custom:hover {
            background-color: #0b5ed7;
            color: white;
        }

        /* Table */
        table th, table td {
            vertical-align: middle !important;
        }

        /* Card */
        .admin-card {
            border: 1px solid #dee2e6;
            border-radius: 8px;
            padding: 15px;
            background: #fff;
            margin-bottom: 20px;
        }
    </style>
</head>
<body>

    <!-- Sidebar -->
    <div class="sidebar">
        <h4>Admin Panel</h4>
        <a href="{{ route('admin.dashboard') }}" class="{{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">Dashboard</a>
        <a href="{{ route('admin.categories.index') }}" class="{{ request()->routeIs('admin.categories.*') ? 'active' : '' }}">Danh mục</a>
        <a href="{{ route('admin.products.index') }}" class="{{ request()->routeIs('admin.products.*') ? 'active' : '' }}">Sản phẩm</a>
    </div>

    <!-- Main content -->
    <div class="main-content">

        <!-- Page Header -->
        <div class="page-header">
            <h1 class="page-title">@yield('page-title', 'Trang quản trị')</h1>
            @yield('page-subtitle')
        </div>

        <!-- Alert messages -->
        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show">
                {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        @if(session('error'))
            <div class="alert alert-danger alert-dismissible fade show">
                {{ session('error') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        <!-- Content -->
        @yield('content')

    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
