@extends('layouts.auth')

@section('title', 'Login Juru Parkir - Sistem Monitoring Pembayaran Parkir')

@section('content')
<div x-data="attendantLoginForm()" @submit.prevent="handleLogin">
    <h2 class="text-2xl font-bold text-gray-900 mb-6">Login Juru Parkir</h2>

    <!-- Error Message -->
    <div x-show="errorMessage" class="mb-4 p-4 bg-red-50 border border-red-200 rounded-lg">
        <p class="text-red-700 text-sm" x-text="errorMessage"></p>
    </div>

    <!-- Registration Number Field -->
    <div class="mb-4">
        <label for="registration_number" class="block text-sm font-medium text-gray-700 mb-2">Nomor Registrasi</label>
        <input
            type="text"
            id="registration_number"
            x-model="form.registration_number"
            placeholder="Contoh: JP-001"
            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent outline-none transition"
            required
        />
    </div>

    <!-- PIN Field -->
    <div class="mb-6">
        <label for="pin" class="block text-sm font-medium text-gray-700 mb-2">PIN</label>
        <input
            type="password"
            id="pin"
            x-model="form.pin"
            placeholder="••••"
            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent outline-none transition"
            required
        />
    </div>

    <!-- CSRF Token -->
    <input type="hidden" name="_token" value="{{ csrf_token() }}">

    <!-- Submit Button -->
    <button
        type="submit"
        @click="handleLogin"
        :disabled="isLoading"
        class="w-full bg-blue-600 hover:bg-blue-700 disabled:bg-gray-400 text-white font-semibold py-2 px-4 rounded-lg transition duration-200"
    >
        <span x-show="!isLoading">Login</span>
        <span x-show="isLoading" class="flex items-center justify-center">
            <svg class="animate-spin -ml-1 mr-3 h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
            </svg>
            Memproses...
        </span>
    </button>

    <!-- Info Message -->
    <div class="mt-6 p-4 bg-blue-50 border border-blue-200 rounded-lg">
        <p class="text-blue-700 text-sm">
            <strong>Catatan:</strong> Gunakan nomor registrasi dan PIN yang telah diberikan oleh admin untuk login.
        </p>
    </div>
</div>

<script>
function attendantLoginForm() {
    return {
        form: {
            registration_number: '',
            pin: '',
        },
        isLoading: false,
        errorMessage: '',

        async handleLogin() {
            this.isLoading = true;
            this.errorMessage = '';

            try {
                const response = await fetch('/api/attendant/auth/login', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('input[name="_token"]').value,
                    },
                    body: JSON.stringify(this.form),
                });

                const data = await response.json();

                if (!response.ok) {
                    this.errorMessage = data.message || 'Login gagal. Silakan coba lagi.';
                    return;
                }

                // Redirect to QR generator
                window.location.href = '/attendant/generate';
            } catch (error) {
                this.errorMessage = 'Terjadi kesalahan. Silakan coba lagi.';
                console.error('Login error:', error);
            } finally {
                this.isLoading = false;
            }
        }
    }
}
</script>
@endsection
