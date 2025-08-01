
<x-app-layout>
    <x-slot name="header">
        <div class="d-flex justify-content-between align-items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight mb-0">
                {{ __('Dashboard') }}
            </h2>
            <a href="{{ route('admin.dashboard') }}" class="btn btn-primary">
                <i class="bi bi-gear me-2"></i>
                Panel de Administración
            </a>
        </div>
    </x-slot>

    <div class="py-4">
        <div class="row">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">
                            <i class="bi bi-house me-2"></i>
                            Bienvenido a {{ config('app.name') }}
                        </h5>
                    </div>
                    <div class="card-body">
                        <p class="card-text">
                            ¡Hola <strong>{{ Auth::user()->name }}</strong>! Bienvenido a tu sistema de personalización de productos.
                        </p>
                        <p class="card-text">
                            Desde aquí puedes gestionar todo tu catálogo de productos personalizables, 
                            categorías, pedidos y mucho más.
                        </p>
                        
                        <div class="row mt-4">
                            <div class="col-md-6 mb-3">
                                <div class="card bg-light">
                                    <div class="card-body text-center">
                                        <i class="bi bi-box display-4 text-primary mb-3"></i>
                                        <h6>Gestionar Productos</h6>
                                        <p class="card-text small">Administra tu catálogo de productos personalizables</p>
                                        <a href="{{ route('admin.products.index') }}" class="btn btn-outline-primary btn-sm">
                                            Ver Productos
                                        </a>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6 mb-3">
                                <div class="card bg-light">
                                    <div class="card-body text-center">
                                        <i class="bi bi-cart display-4 text-success mb-3"></i>
                                        <h6>Ver Pedidos</h6>
                                        <p class="card-text small">Revisa y gestiona los pedidos de tus clientes</p>
                                        <a href="{{ route('admin.orders.index') }}" class="btn btn-outline-success btn-sm">
                                            Ver Pedidos
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-md-4">
                <div class="card">
                    <div class="card-header">
                        <h6 class="mb-0">
                            <i class="bi bi-lightning me-2"></i>
                            Accesos Rápidos
                        </h6>
                    </div>
                    <div class="card-body">
                        <div class="d-grid gap-2">
                            <a href="{{ route('admin.dashboard') }}" class="btn btn-primary">
                                <i class="bi bi-speedometer2 me-2"></i>
                                Panel Admin
                            </a>
                            <a href="{{ route('admin.products.create') }}" class="btn btn-outline-primary">
                                <i class="bi bi-plus-circle me-2"></i>
                                Nuevo Producto
                            </a>
                            <a href="{{ route('admin.categories.index') }}" class="btn btn-outline-secondary">
                                <i class="bi bi-folder me-2"></i>
                                Categorías
                            </a>
                            <a href="{{ route('admin.orders.index') }}" class="btn btn-outline-success">
                                <i class="bi bi-cart me-2"></i>
                                Pedidos
                            </a>
                        </div>
                    </div>
                </div>
                
                <div class="card mt-3">
                    <div class="card-header">
                        <h6 class="mb-0">
                            <i class="bi bi-info-circle me-2"></i>
                            Sistema
                        </h6>
                    </div>
                    <div class="card-body">
                        <p class="card-text small">
                            <strong>Versión:</strong> 1.0<br>
                            <strong>Laravel:</strong> {{ app()->version() }}<br>
                            <strong>Usuario:</strong> {{ Auth::user()->email }}
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>

{{-- resources/views/welcome.blade.php - PÁGINA DE INICIO MEJORADA --}}
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>{{ config('app.name', 'Laravel') }}</title>
        
        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />
        
        <!-- Bootstrap CSS -->
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
        <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css" rel="stylesheet">
        
        <style>
            body {
                font-family: 'Figtree', sans-serif;
                background: linear-gradient(135deg, #c28928 0%, #c08025 100%);
                min-height: 100vh;
            }
            .hero-section {
                padding: 100px 0;
                color: white;
                text-align: center;
            }
            .feature-card {
                background: white;
                border-radius: 1rem;
                padding: 2rem;
                text-align: center;
                box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1);
                height: 100%;
            }
            .feature-icon {
                font-size: 3rem;
                color: #667eea;
                margin-bottom: 1rem;
            }
            .btn-custom {
                background: linear-gradient(135deg, #c28928 0%, #c08025 100%);
                border: none;
                color: white;
                padding: 12px 30px;
                border-radius: 50px;
                font-weight: 600;
                text-decoration: none;
                display: inline-block;
                transition: all 0.3s;
            }
            .btn-custom:hover {
                color: white;
                transform: translateY(-2px);
                box-shadow: 0 10px 20px rgba(102, 126, 234, 0.3);
            }
        </style>
    </head>
    <body>
        <!-- Navigation -->
        <nav class="navbar navbar-expand-lg navbar-dark">
            <div class="container">
                <a class="navbar-brand fw-bold" href="#">
                    <i class="bi bi-shop me-2"></i>
                    {{ config('app.name') }}
                </a>
                
                <div class="navbar-nav ms-auto">
                    @auth
                        <a href="{{ route('dashboard') }}" class="nav-link">Dashboard</a>
                        <a href="{{ route('admin.dashboard') }}" class="nav-link">Admin</a>
                    @else
                        <a href="{{ route('login') }}" class="nav-link">Iniciar Sesión</a>
                        @if (Route::has('register'))
                            <a href="{{ route('register') }}" class="nav-link">Registrarse</a>
                        @endif
                    @endauth
                </div>
            </div>
        </nav>

        <!-- Hero Section -->
        <section class="hero-section">
            <div class="container">
                <div class="row justify-content-center">
                    <div class="col-lg-8">
                        <h1 class="display-4 fw-bold mb-4">
                            Sistema de Personalización de Productos
                        </h1>
                        <p class="lead mb-5">
                            Gestiona tu catálogo de productos personalizables, procesa pedidos y 
                            ofrece experiencias únicas a tus clientes.
                        </p>
                        
                        @auth
                            <a href="{{ route('admin.dashboard') }}" class="btn-custom me-3">
                                <i class="bi bi-speedometer2 me-2"></i>
                                Ir al Panel
                            </a>
                        @else
                            <a href="{{ route('login') }}" class="btn-custom me-3">
                                <i class="bi bi-box-arrow-in-right me-2"></i>
                                Iniciar Sesión
                            </a>
                        @endauth
                        
                        <a href="#features" class="btn btn-outline-light">
                            <i class="bi bi-arrow-down me-2"></i>
                            Conocer Más
                        </a>
                    </div>
                </div>
            </div>
        </section>

        <!-- Features Section -->
        <section id="features" class="py-5 bg-light">
            <div class="container">
                <div class="row text-center mb-5">
                    <div class="col-lg-12">
                        <h2 class="fw-bold">Características Principales</h2>
                        <p class="text-muted">Todo lo que necesitas para gestionar productos personalizables</p>
                    </div>
                </div>
                
                <div class="row g-4">
                    <div class="col-md-4">
                        <div class="feature-card">
                            <i class="bi bi-box feature-icon"></i>
                            <h4>Gestión de Productos</h4>
                            <p class="text-muted">
                                Administra tu catálogo completo con imágenes, modelos 3D, 
                                precios por cantidad y especificaciones técnicas.
                            </p>
                        </div>
                    </div>
                    
                    <div class="col-md-4">
                        <div class="feature-card">
                            <i class="bi bi-sliders feature-icon"></i>
                            <h4>Campos Personalizados</h4>
                            <p class="text-muted">
                                Crea campos dinámicos para que tus clientes personalicen 
                                sus productos según sus necesidades específicas.
                            </p>
                        </div>
                    </div>
                    
                    <div class="col-md-4">
                        <div class="feature-card">
                            <i class="bi bi-cart feature-icon"></i>
                            <h4>Gestión de Pedidos</h4>
                            <p class="text-muted">
                                Procesa pedidos con workflow completo, desde la recepción 
                                hasta la entrega, con seguimiento detallado.
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Footer -->
        <footer class="bg-dark text-white py-4">
            <div class="container text-center">
                <p class="mb-0">
                    © {{ date('Y') }} {{ config('app.name') }}. 
                    Sistema de personalización de productos.
                </p>
            </div>
        </footer>

        <!-- Bootstrap JS -->
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    </body>
</html>