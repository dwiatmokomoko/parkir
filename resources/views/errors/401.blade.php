@extends('layouts.app')

@section('content')
<div class="min-h-screen flex items-center justify-center bg-gray-50 py-12 px-4 sm:px-6 lg:px-8">
    <div class="max-w-md w-full space-y-8 text-center">
        <div>
            <h1 class="text-6xl font-bold text-red-600">401</h1>
            <h2 class="mt-4 text-3xl font-extrabold text-gray-900">
                Tidak Diizinkan
            </h2>
            <p class="mt-2 text-sm text-gray-600">
                Anda tidak memiliki akses untuk mengakses halaman ini. Silakan login terlebih dahulu.
            </p>
        </div>
        <div class="mt-8">
            <a href="{{ route('login') }}" class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-blue-600 hover:bg-blue-700">
                Kembali ke Login
            </a>
        </div>
    </div>
</div>
@endsection
