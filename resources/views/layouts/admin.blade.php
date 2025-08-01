<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title', 'Admin') - {{ config('app.name', 'Laravel') }}</title>

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    
    <!-- Custom CSS -->
    <style>
        /* ======================================== */
        /* ESTILOS BASE DEL PANEL */
        /* ======================================== */
        
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
            background-color: #f8f9fa;
        }

        /* Sidebar */
        .sidebar {
            min-height: 100vh;
            background: linear-gradient(135deg, #c28928 0%, #c08025 100%);
            box-shadow: 2px 0 10px rgba(0, 0, 0, 0.1);
        }

        .sidebar .nav-link {
            color: rgba(255, 255, 255, 0.85);
            padding: 0.75rem 1rem;
            border-radius: 0.375rem;
            margin-bottom: 0.25rem;
            transition: all 0.3s ease;
            font-weight: 500;
        }

        .sidebar .nav-link:hover {
            background: rgba(255, 255, 255, 0.1);
            color: white;
            transform: translateX(5px);
        }

        .sidebar .nav-link.active {
            background: rgba(255, 255, 255, 0.2);
            color: white;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.15);
        }

        .sidebar .nav-link i {
            margin-right: 0.5rem;
            width: 1.2rem;
            text-align: center;
        }

        .sidebar hr {
            border-color: rgba(255, 255, 255, 0.2);
            margin: 1rem 0;
        }

        /* Content wrapper */
        .content-wrapper {
            min-height: 100vh;
            background-color: #f8f9fa;
        }


        /* ======================================== */
        /* ESTILOS PARA CARDS */
        /* ======================================== */
        
        .card {
            border: none;
            border-radius: 0.75rem;
            box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
            transition: all 0.3s ease;
        }

        .card:hover {
            box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.1);
            transform: translateY(-2px);
        }

        .card-header {
            background: linear-gradient(135deg, #c28928 0%, #c08025 100%);
            color: white;
            border-radius: 0.75rem 0.75rem 0 0 !important;
            font-weight: 600;
            border: none;
        }

        .card-header h5,
        .card-header h6 {
            margin-bottom: 0;
            color: white;
        }

        /* ======================================== */
        /* ESTILOS PARA BOTONES */
        /* ======================================== */
        
        .btn-primary {
            background: linear-gradient(135deg, #c28928 0%, #c08025 100%);
            border: none;
            border-radius: 0.375rem;
            font-weight: 500;
            transition: all 0.3s ease;
        }

        .btn-primary:hover {
            background: linear-gradient(135deg, #d9a745 0%, #c89330 100%);
            transform: translateY(-1px);
            box-shadow: 0 4px 8px rgba(194, 137, 40, 0.3);
        }

        .btn-secondary {
            background-color: #6c757d;
            border: none;
            transition: all 0.3s ease;
        }

        .btn-secondary:hover {
            background-color: #5a6268;
            transform: translateY(-1px);
        }

        .btn-info {
            background-color: #17a2b8;
            border: none;
        }

        .btn-warning {
            background-color: #ffc107;
            border: none;
            color: #212529;
        }

        .btn-danger {
            background-color: #dc3545;
            border: none;
        }

        .btn-success {
            background-color: #28a745;
            border: none;
        }

        /* Small buttons */
        .btn-sm {
            padding: 0.25rem 0.5rem;
            font-size: 0.875rem;
        }

        /* ======================================== */
        /* ESTILOS PARA TABLAS */
        /* ======================================== */
        
        .table {
            background-color: white;
        }

        .table thead th {
            background-color: #f8f9fa;
            border-top: none;
            border-bottom: 2px solid #dee2e6;
            font-weight: 600;
            color: #495057;
            text-transform: uppercase;
            font-size: 0.875rem;
            letter-spacing: 0.5px;
        }

        .table-hover tbody tr:hover {
            background-color: rgba(194, 137, 40, 0.05);
        }

        /* ======================================== */
        /* ESTILOS PARA FORMULARIOS */
        /* ======================================== */
        
        .form-label {
            font-weight: 600;
            color: #495057;
            margin-bottom: 0.5rem;
        }

        .form-control,
        .form-select {
            border: 1px solid #ced4da;
            border-radius: 0.375rem;
            transition: all 0.3s ease;
        }

        .form-control:focus,
        .form-select:focus {
            border-color: #c28928;
            box-shadow: 0 0 0 0.2rem rgba(194, 137, 40, 0.25);
        }

        .form-check-input:checked {
            background-color: #c28928;
            border-color: #c28928;
        }

        .form-check-input:focus {
            box-shadow: 0 0 0 0.2rem rgba(194, 137, 40, 0.25);
        }

        /* ======================================== */
        /* ESTILOS PARA BREADCRUMBS */
        /* ======================================== */
        
        .breadcrumb {
            background-color: transparent;
            padding: 0.75rem 0;
            margin-bottom: 1.5rem;
        }

        .breadcrumb-item + .breadcrumb-item::before {
            color: #6c757d;
        }

        /* ======================================== */
        /* ESTILOS PARA BADGES */
        /* ======================================== */
        
        .badge {
            padding: 0.375rem 0.75rem;
            font-weight: 500;
            border-radius: 0.375rem;
        }

        /* ======================================== */
        /* ESTILOS PARA ALERTAS */
        /* ======================================== */
        
        .alert {
            border: none;
            border-radius: 0.5rem;
        }

        .alert-success {
            background-color: #d4edda;
            color: #155724;
            border-left: 4px solid #28a745;
        }

        .alert-danger {
            background-color: #f8d7da;
            color: #721c24;
            border-left: 4px solid #dc3545;
        }

        .alert-info {
            background-color: #d1ecf1;
            color: #0c5460;
            border-left: 4px solid #17a2b8;
        }

        .alert-warning {
            background-color: #fff3cd;
            color: #856404;
            border-left: 4px solid #ffc107;
        }

        /* ======================================== */
        /* DASHBOARD STATS CARDS */
        /* ======================================== */
        
        .border-left-primary {
            border-left: 4px solid #0d6efd !important;
        }

        .border-left-success {
            border-left: 4px solid #198754 !important;
        }

        .border-left-info {
            border-left: 4px solid #0dcaf0 !important;
        }

        .border-left-warning {
            border-left: 4px solid #ffc107 !important;
        }

        .text-xs {
            font-size: 0.7rem;
        }

        .font-weight-bold {
            font-weight: 700 !important;
        }

        .text-uppercase {
            text-transform: uppercase !important;
        }

        .shadow {
            box-shadow: 0 .15rem 1.75rem 0 rgba(58,59,69,.15) !important;
        }

        .text-gray-800 {
            color: #5a5c69 !important;
        }

        /* ======================================== */
        /* UTILIDADES ADICIONALES */
        /* ======================================== */
        
        .text-danger {
            color: #dc3545 !important;
        }

        .text-muted {
            color: #6c757d !important;
        }

        .img-thumbnail {
            border: 2px solid #dee2e6;
            border-radius: 0.375rem;
            transition: border-color 0.3s ease;
        }

        .img-thumbnail:hover {
            border-color: #c28928;
        }

        /* Form switch */
        .form-switch .form-check-input {
            width: 3em;
        }

        .form-switch .form-check-input:checked {
            background-color: #c28928;
            border-color: #c28928;
        }

        /* ======================================== */
        /* RESPONSIVE */
        /* ======================================== */
        
        @media (max-width: 768px) {
            .sidebar {
                min-height: auto;
                margin-bottom: 1rem;
            }
            
            .content-wrapper {
                padding: 0 0.5rem;
            }
            
            .table {
                font-size: 0.875rem;
            }
            
            .btn-group {
                display: flex;
                flex-direction: column;
            }
            
            .btn-group .btn {
                border-radius: 0.375rem !important;
                margin-bottom: 0.25rem;
            }
        }

        /* ======================================== */
        /* ANIMACIONES */
        /* ======================================== */
        
        * {
            transition: color 0.15s ease-in-out,
                        background-color 0.15s ease-in-out,
                        border-color 0.15s ease-in-out,
                        box-shadow 0.15s ease-in-out,
                        transform 0.15s ease-in-out;
        }

        /* Loading spinner */
        .spinner-border {
            color: #c28928;
        }
    </style>
    
    @stack('styles')
</head>
<body>
    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar -->
            <nav class="col-md-2 d-md-block sidebar py-4">
                <div class="position-sticky">
                    <div class="text-center mb-4 text-white">
                        <h5 class="mb-1">{{ config('app.name', 'Laravel') }}</h5>
                        <small class="opacity-75">Panel de Administración</small>
                    </div>
                    
                    <ul class="nav flex-column">
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}" 
                               href="{{ route('admin.dashboard') }}">
                                <i class="bi bi-speedometer2"></i> Dashboard
                            </a>
                        </li>
                        
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('admin.categories.*') ? 'active' : '' }}" 
                               href="{{ route('admin.categories.index') }}">
                                <i class="bi bi-folder"></i> Categorías
                            </a>
                        </li>
                        
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('admin.subcategories.*') ? 'active' : '' }}" 
                               href="{{ route('admin.subcategories.index') }}">
                                <i class="bi bi-folder2-open"></i> Subcategorías
                            </a>
                        </li>
                        
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('admin.products.*') ? 'active' : '' }}" 
                               href="{{ route('admin.products.index') }}">
                                <i class="bi bi-box-seam"></i> Productos
                            </a>
                        </li>
                        
                        @if(Route::has('admin.orders.index'))
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('admin.orders.*') ? 'active' : '' }}" 
                               href="{{ route('admin.orders.index') }}">
                                <i class="bi bi-cart"></i> Pedidos
                            </a>
                        </li>
                        @endif
                        
                        <hr>
                        
                        <li class="nav-item">
                            <a class="nav-link" href="{{ route('profile.edit') }}">
                                <i class="bi bi-person"></i> Mi Perfil
                            </a>
                        </li>
                        
                        <li class="nav-item">
                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <button type="submit" class="nav-link btn btn-link text-start w-100 border-0 bg-transparent">
                                    <i class="bi bi-box-arrow-right"></i> Cerrar Sesión
                                </button>
                            </form>
                        </li>
                    </ul>
                </div>
            </nav>

            <!-- Main content -->
            <main class="col-md-10 ms-sm-auto px-md-4 content-wrapper">
                <!-- Top navbar -->
                <nav class="navbar navbar-light mb-4">
                    <div class="container-fluid">
                        <button class="navbar-toggler d-md-none" type="button" data-bs-toggle="collapse" data-bs-target="#sidebarMenu">
                            <span class="navbar-toggler-icon"></span>
                        </button>
                        
                        <div class="d-flex align-items-center ms-auto">
                            <span class="me-3">{{ Auth::user()->name }}</span>
                            <img src="https://ui-avatars.com/api/?name={{ urlencode(Auth::user()->name) }}&background=c28928&color=fff" 
                                 alt="Avatar" 
                                 class="rounded-circle" 
                                 width="32" 
                                 height="32">
                        </div>
                    </div>
                </nav>

                <!-- Page content -->
                <div class="py-3">
                    @yield('content')
                </div>
            </main>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    @stack('scripts')
</body>
</html>