@extends('layouts.app')

@section('content')
<div class="min-h-screen flex items-center justify-center bg-gray-50 py-12 px-4 sm:px-6 lg:px-8">
    <div class="max-w-md w-full space-y-8 text-center">
        <div>
            <h1 class="text-6xl font-bold text-red-600">404</h1>
            <h2 class="mt-4 text-3xl font-extrabold text-gray-900">
                Halaman Tidak Ditemukan
            </h2>
            <p class="mt-2 text-sm text-gray-600">
                Halaman yang Anda cari tidak dapat ditemukan.
            </p>
        </div>
        <div class="mt-8">
            <a href="{{ route('dashboard') }}" class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-blue-600 hover:bg-blue-700">
                Kembali ke Dashboard
            </a>
        </div>
    </div>
</div>
@endsection
