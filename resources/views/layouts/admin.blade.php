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
    
    <!-- Toastr CSS -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css">
    
    <!-- SweetAlert2 CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
    
    <!-- Admin CSS -->
    @vite('resources/css/admin.css')
    
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
                        
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('admin.attribute-groups.*') ? 'active' : '' }}" 
                               href="{{ route('admin.attribute-groups.index') }}">
                                <i class="bi bi-collection-fill"></i> Grupos de Atributos
                            </a>
                        </li>
                        
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('admin.product-attributes.*') ? 'active' : '' }}" 
                               href="{{ route('admin.product-attributes.index') }}">
                                <i class="bi bi-palette-fill"></i> Atributos
                            </a>
                        </li>
                        
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('admin.attribute-dependencies.*') ? 'active' : '' }}" 
                               href="{{ route('admin.attribute-dependencies.index') }}">
                                <i class="bi bi-diagram-3"></i> Dependencias & Precios
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
                        
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('admin.customers.*') ? 'active' : '' }}"
                               href="{{ route('admin.customers.index') }}">
                                <i class="bi bi-people"></i> Clientes
                            </a>
                        </li>

                        <hr>

                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('admin.data-import.*') ? 'active' : '' }}"
                               href="{{ route('admin.data-import.index') }}">
                                <i class="bi bi-database"></i> Importar/Exportar
                            </a>
                        </li>

                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('admin.settings.*') ? 'active' : '' }}"
                               href="{{ route('admin.settings.3d') }}">
                                <i class="bi bi-badge-3d"></i> Config. 3D
                            </a>
                        </li>

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
    
    <!-- jQuery (required for Toastr) -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
    
    <!-- Toastr JS -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>
    
    <!-- SweetAlert2 JS -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    
    <!-- Model Viewer for 3D models -->
    <script type="module" src="https://unpkg.com/@google/model-viewer@3.4.0/dist/model-viewer.min.js"></script>
    <script nomodule src="https://unpkg.com/@google/model-viewer@3.4.0/dist/model-viewer-legacy.js"></script>
    
    <!-- Toastr Configuration -->
    <script>
        // Configure toastr options
        if (typeof toastr !== 'undefined') {
            toastr.options = {
                "closeButton": true,
                "debug": false,
                "newestOnTop": true,
                "progressBar": true,
                "positionClass": "toast-top-right",
                "preventDuplicates": true,
                "onclick": null,
                "showDuration": "300",
                "hideDuration": "1000",
                "timeOut": "4000",
                "extendedTimeOut": "1000",
                "showEasing": "swing",
                "hideEasing": "linear",
                "showMethod": "fadeIn",
                "hideMethod": "fadeOut"
            };

            // Show session flash messages as toastr notifications
            @if(session('success'))
                toastr.success('{{ session('success') }}');
            @endif
            
            @if(session('error'))
                toastr.error('{{ session('error') }}');
            @endif
            
            @if(session('warning'))
                toastr.warning('{{ session('warning') }}');
            @endif
            
            @if(session('info'))
                toastr.info('{{ session('info') }}');
            @endif
            
            @if(session('status'))
                @if(session('status') == 'profile-updated')
                    toastr.success('Perfil actualizado exitosamente');
                @elseif(session('status') == 'password-updated')
                    toastr.success('Contraseña actualizada exitosamente');
                @elseif(session('status') == 'verification-link-sent')
                    toastr.info('Enlace de verificación enviado');
                @else
                    toastr.info('{{ session('status') }}');
                @endif
            @endif

            // Show validation errors as toastr notifications
            @if($errors->any())
                @foreach($errors->all() as $error)
                    toastr.error('{{ $error }}');
                @endforeach
            @endif
        }

    </script>
    
    <!-- Admin Common JS -->
    @vite('resources/js/admin/common.js')
    
    @stack('scripts')
</body>
</html>