<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sistem Monitoring Pembayaran Parkir</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-gradient-to-br from-blue-50 to-indigo-100 min-h-screen flex items-center justify-center">
    <div class="text-center">
        <!-- Logo -->
        <div class="mb-8">
            <div class="text-6xl font-bold text-blue-600 mb-4">🅓</div>
            <h1 class="text-4xl font-bold text-gray-900 mb-2">DISHUB</h1>
            <p class="text-xl text-gray-600">Sistem Monitoring Pembayaran Parkir</p>
        </div>

        <!-- Description -->
        <div class="max-w-md mx-auto mb-8">
            <p class="text-gray-700 mb-6">
                Platform monitoring pembayaran parkir non-tunai yang terintegrasi dengan Midtrans untuk memfasilitasi pembayaran QRIS, e-wallet, dan Virtual Account.
            </p>
        </div>

        <!-- Action Buttons -->
        <div class="flex flex-col sm:flex-row gap-4 justify-center">
            <a href="{{ route('login') }}" class="px-8 py-3 bg-blue-600 text-white rounded-lg font-medium hover:bg-blue-700 transition-colors duration-200">
                Login Admin
            </a>
            <a href="{{ route('attendant.login') }}" class="px-8 py-3 bg-gray-600 text-white rounded-lg font-medium hover:bg-gray-700 transition-colors duration-200">
                Login Juru Parkir
            </a>
        </div>

        <!-- Footer -->
        <div class="mt-12 text-sm text-gray-600">
            <p>&copy; {{ now()->year }} Dinas Perhubungan</p>
        </div>
    </div>
</body>
</html>
