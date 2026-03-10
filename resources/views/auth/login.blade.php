@extends('layouts.auth')

@section('title', 'Login Admin - Sistem Monitoring Pembayaran Parkir')

@section('content')
<div x-data="loginForm()" @submit.prevent="handleLogin">
    <h2 class="text-2xl font-bold text-gray-900 mb-6">Login Admin</h2>

    <!-- Error Message -->
    <div x-show="errorMessage" class="mb-4 p-4 bg-red-50 border border-red-200 rounded-lg">
        <p class="text-red-700 text-sm" x-text="errorMessage"></p>
    </div>

    <!-- Email Field -->
    <div class="mb-4">
        <label for="email" class="block text-sm font-medium text-gray-700 mb-2">Email</label>
        <input
            type="email"
            id="email"
            x-model="form.email"
            placeholder="admin@dishub.go.id"
            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent outline-none transition"
            required
        />
    </div>

    <!-- Password Field -->
    <div class="mb-6">
        <label for="password" class="block text-sm font-medium text-gray-700 mb-2">Password</label>
        <input
            type="password"
            id="password"
            x-model="form.password"
            placeholder="••••••••"
            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent outline-none transition"
            required
        />
    </div>

    <!-- Remember Me Checkbox -->
    <div class="mb-6 flex items-center">
        <input
            type="checkbox"
            id="remember"
            x-model="form.remember"
            class="w-4 h-4 text-blue-600 border-gray-300 rounded focus:ring-2 focus:ring-blue-500"
        />
        <label for="remember" class="ml-2 text-sm text-gray-700">Ingat saya</label>
    </div>

    <!-- CSRF Token -->
    <input type="hidden" name="_token" value="{{ csrf_token() }}">

    <!-- Submit Button -->
    <button
        type="submit"
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
</div>

<script>
function loginForm() {
    return {
        form: {
            email: '',
            password: '',
            remember: false,
        },
        isLoading: false,
        errorMessage: '',

        async handleLogin() {
            this.isLoading = true;
            this.errorMessage = '';

            try {
                const response = await fetch('/api/auth/login', {
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

                // Store remember me preference
                if (this.form.remember) {
                    localStorage.setItem('rememberEmail', this.form.email);
                } else {
                    localStorage.removeItem('rememberEmail');
                }

                // Redirect to dashboard
                window.location.href = '/dashboard';
            } catch (error) {
                this.errorMessage = 'Terjadi kesalahan. Silakan coba lagi.';
                console.error('Login error:', error);
            } finally {
                this.isLoading = false;
            }
        },

        init() {
            // Load remembered email if exists
            const rememberedEmail = localStorage.getItem('rememberEmail');
            if (rememberedEmail) {
                this.form.email = rememberedEmail;
                this.form.remember = true;
            }
        }
    }
}
</script>
@endsection
