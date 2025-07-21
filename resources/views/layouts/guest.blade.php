<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Laravel') }}</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

        <!-- Bootstrap CSS -->
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
        <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css" rel="stylesheet">

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])

        <style>
            body {
                background: linear-gradient(135deg, #c28928 0%, #c08025 100%);
                min-height: 100vh;
                display: flex;
                align-items: center;
                justify-content: center;
                font-family: 'Figtree', sans-serif;
            }
            .login-container {
                background: white;
                border-radius: 1rem;
                box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
                padding: 2rem;
                width: 100%;
                max-width: 400px;
            }
            .logo-container {
                text-align: center;
                margin-bottom: 2rem;
            }
            .logo {
                background: linear-gradient(135deg, #c28928 0%, #c08025 100%);
                color: white;
                width: 80px;
                height: 80px;
                border-radius: 50%;
                display: inline-flex;
                align-items: center;
                justify-content: center;
                font-size: 2rem;
                margin-bottom: 1rem;
            }
            .btn-primary {
                background: linear-gradient(135deg, #c28928 0%, #c08025 100%);
                border: none;
                padding: 0.75rem 1.5rem;
                border-radius: 0.5rem;
                font-weight: 600;
                width: 100%;
            }
            .btn-primary:hover {
                background: linear-gradient(135deg, #d9a745 0%, #c89330 100%);
                transform: translateY(-1px);
                transition: all 0.2s;
            }
            .form-control {
                border-radius: 0.5rem;
                padding: 0.75rem;
                border: 2px solid #e9ecef;
            }
            .form-control:focus {
                border-color: #667eea;
                box-shadow: 0 0 0 0.2rem rgba(102, 126, 234, 0.25);
            }
            .app-title {
                color: #667eea;
                font-weight: 700;
                font-size: 1.25rem;
            }
        </style>
    </head>
    <body>
        <div class="login-container">
            <div class="logo-container">
                <div class="logo">
                    <i class="bi bi-shop"></i>
                </div>
                <h4 class="app-title">{{ config('app.name') }}</h4>
                <p class="text-muted">Panel de Administración</p>
            </div>
            
            {{ $slot }}
        </div>

        <!-- Bootstrap JS -->
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    </body>
</html>
