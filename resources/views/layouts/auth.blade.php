<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Login - Sistem Monitoring Pembayaran Parkir')</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-gradient-to-br from-blue-50 to-indigo-100 min-h-screen flex items-center justify-center">
    <div class="w-full max-w-md">
        <!-- Logo -->
        <div class="text-center mb-8">
            <div class="text-5xl font-bold text-blue-600 mb-2">🅓</div>
            <h1 class="text-3xl font-bold text-gray-900">DISHUB</h1>
            <p class="text-gray-600 mt-2">Sistem Monitoring Pembayaran Parkir</p>
        </div>

        <!-- Card -->
        <div class="bg-white rounded-lg shadow-lg p-8">
            @yield('content')
        </div>

        <!-- Footer -->
        <div class="text-center mt-8 text-sm text-gray-600">
            <p>&copy; {{ now()->year }} Dinas Perhubungan</p>
        </div>
    </div>
</body>
</html>
