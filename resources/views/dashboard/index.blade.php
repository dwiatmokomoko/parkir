@extends('layouts.app')

@section('title', 'Dashboard - Sistem Monitoring Pembayaran Parkir')

@section('content')
<div x-data="dashboard()" x-init="init()" class="min-h-screen bg-gray-50">
    <!-- Page Header -->
    <div class="bg-white shadow-sm border-b border-gray-200">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <h1 class="text-3xl font-bold text-gray-900">Dashboard</h1>
                    <p class="text-gray-600 mt-1">Monitoring real-time transaksi pembayaran parkir</p>
                </div>
                <div class="mt-4 sm:mt-0">
                    <button @click="refreshData()" :disabled="loading" class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 disabled:opacity-50">
                        <svg x-show="loading" class="animate-spin -ml-1 mr-3 h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        <svg x-show="!loading" class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                        </svg>
                        Refresh Data
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Main Content -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <!-- Summary Cards -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
            <!-- Daily Revenue Card -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 transition-all duration-200 hover:shadow-md">
                <div class="flex items-center justify-between">
                    <div class="flex-1">
                        <p class="text-gray-600 text-sm font-medium mb-1">Pendapatan Hari Ini</p>
                        <div x-show="loading" class="animate-pulse">
                            <div class="h-8 bg-gray-200 rounded w-24 mb-2"></div>
                        </div>
                        <p x-show="!loading" class="text-3xl font-bold text-gray-900">
                            Rp <span x-text="formatCurrency(summary.dailyRevenue)"></span>
                        </p>
                    </div>
                    <div class="bg-blue-100 rounded-full p-3 ml-4">
                        <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </div>
                </div>
            </div>

            <!-- Monthly Revenue Card -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 transition-all duration-200 hover:shadow-md">
                <div class="flex items-center justify-between">
                    <div class="flex-1">
                        <p class="text-gray-600 text-sm font-medium mb-1">Pendapatan Bulan Ini</p>
                        <div x-show="loading" class="animate-pulse">
                            <div class="h-8 bg-gray-200 rounded w-24 mb-2"></div>
                        </div>
                        <p x-show="!loading" class="text-3xl font-bold text-gray-900">
                            Rp <span x-text="formatCurrency(summary.monthlyRevenue)"></span>
                        </p>
                    </div>
                    <div class="bg-green-100 rounded-full p-3 ml-4">
                        <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8L5.257 19.393A2 2 0 005 18.07V5a2 2 0 012-2h5.5"></path>
                        </svg>
                    </div>
                </div>
            </div>

            <!-- Total Transactions Card -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 transition-all duration-200 hover:shadow-md">
                <div class="flex items-center justify-between">
                    <div class="flex-1">
                        <p class="text-gray-600 text-sm font-medium mb-1">Total Transaksi</p>
                        <div x-show="loading" class="animate-pulse">
                            <div class="h-8 bg-gray-200 rounded w-16 mb-2"></div>
                        </div>
                        <p x-show="!loading" class="text-3xl font-bold text-gray-900" x-text="summary.totalTransactions"></p>
                    </div>
                    <div class="bg-purple-100 rounded-full p-3 ml-4">
                        <svg class="w-6 h-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </div>
                </div>
            </div>
        </div>

        <!-- Charts Section -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 mb-8">
            <!-- Daily Revenue Chart -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                <div class="flex items-center justify-between mb-6">
                    <h3 class="text-lg font-semibold text-gray-900">Pendapatan Harian</h3>
                    <span class="text-sm text-gray-500">30 hari terakhir</span>
                </div>
                <div x-show="loading" class="animate-pulse">
                    <div class="h-72 bg-gray-200 rounded-lg"></div>
                </div>
                <div x-show="!loading" class="relative h-72">
                    <canvas id="dailyRevenueChart" class="absolute inset-0 h-full w-full"></canvas>
                </div>
            </div>

            <!-- Monthly Revenue Chart -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                <div class="flex items-center justify-between mb-6">
                    <h3 class="text-lg font-semibold text-gray-900">Pendapatan Bulanan</h3>
                    <span class="text-sm text-gray-500">12 bulan terakhir</span>
                </div>
                <div x-show="loading" class="animate-pulse">
                    <div class="h-72 bg-gray-200 rounded-lg"></div>
                </div>
                <div x-show="!loading" class="relative h-72">
                    <canvas id="monthlyRevenueChart" class="absolute inset-0 h-full w-full"></canvas>
                </div>
            </div>

            <!-- Location Distribution Chart -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                <div class="flex items-center justify-between mb-6">
                    <h3 class="text-lg font-semibold text-gray-900">Distribusi Lokasi</h3>
                    <span class="text-sm text-gray-500">Transaksi per lokasi</span>
                </div>
                <div x-show="loading" class="animate-pulse">
                    <div class="h-72 bg-gray-200 rounded-lg"></div>
                </div>
                <div x-show="!loading" class="relative h-72">
                    <canvas id="locationChart" class="absolute inset-0 h-full w-full"></canvas>
                </div>
            </div>

            <!-- Vehicle Type Distribution Chart -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                <div class="flex items-center justify-between mb-6">
                    <h3 class="text-lg font-semibold text-gray-900">Jenis Kendaraan</h3>
                    <span class="text-sm text-gray-500">Transaksi per jenis</span>
                </div>
                <div x-show="loading" class="animate-pulse">
                    <div class="h-72 bg-gray-200 rounded-lg"></div>
                </div>
                <div x-show="!loading" class="relative h-72">
                    <canvas id="vehicleChart" class="absolute inset-0 h-full w-full"></canvas>
                </div>
            </div>
        </div>

        <!-- Payment Status Distribution -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 mb-8">
            <div class="flex items-center justify-between mb-6">
                <h3 class="text-lg font-semibold text-gray-900">Status Pembayaran</h3>
                <span class="text-sm text-gray-500">Ringkasan status transaksi</span>
            </div>
            <div x-show="loading" class="animate-pulse">
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div class="h-20 bg-gray-200 rounded-lg"></div>
                    <div class="h-20 bg-gray-200 rounded-lg"></div>
                    <div class="h-20 bg-gray-200 rounded-lg"></div>
                </div>
            </div>
            <div x-show="!loading" class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <div class="flex items-center justify-between p-6 bg-green-50 rounded-xl border border-green-200 transition-all duration-200 hover:shadow-sm">
                    <div class="flex items-center space-x-4">
                        <div class="bg-green-100 rounded-full p-3">
                            <svg class="w-8 h-8 text-green-600" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                            </svg>
                        </div>
                        <div>
                            <p class="text-gray-600 text-sm font-medium">Berhasil</p>
                            <p class="text-2xl font-bold text-green-600" x-text="paymentStatus.success"></p>
                        </div>
                    </div>
                </div>

                <div class="flex items-center justify-between p-6 bg-yellow-50 rounded-xl border border-yellow-200 transition-all duration-200 hover:shadow-sm">
                    <div class="flex items-center space-x-4">
                        <div class="bg-yellow-100 rounded-full p-3">
                            <svg class="w-8 h-8 text-yellow-600" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-11a1 1 0 10-2 0v3.5a1 1 0 002 0V7z" clip-rule="evenodd"></path>
                            </svg>
                        </div>
                        <div>
                            <p class="text-gray-600 text-sm font-medium">Pending</p>
                            <p class="text-2xl font-bold text-yellow-600" x-text="paymentStatus.pending"></p>
                        </div>
                    </div>
                </div>

                <div class="flex items-center justify-between p-6 bg-red-50 rounded-xl border border-red-200 transition-all duration-200 hover:shadow-sm">
                    <div class="flex items-center space-x-4">
                        <div class="bg-red-100 rounded-full p-3">
                            <svg class="w-8 h-8 text-red-600" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"></path>
                            </svg>
                        </div>
                        <div>
                            <p class="text-gray-600 text-sm font-medium">Gagal</p>
                            <p class="text-2xl font-bold text-red-600" x-text="paymentStatus.failed"></p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Recent Transactions -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-200">
                <div class="flex items-center justify-between">
                    <div>
                        <h3 class="text-lg font-semibold text-gray-900">Transaksi Terbaru</h3>
                        <p class="text-sm text-gray-500 mt-1">Daftar transaksi terakhir</p>
                    </div>
                    <button @click="refreshTransactions()" :disabled="loading" class="inline-flex items-center px-3 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 disabled:opacity-50">
                        <svg x-show="loading" class="animate-spin -ml-1 mr-2 h-4 w-4 text-gray-700" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        <svg x-show="!loading" class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                        </svg>
                        Refresh
                    </button>
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead class="bg-gray-50 border-b border-gray-200">
                        <tr>
                            <th class="px-6 py-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">ID Transaksi</th>
                            <th class="px-6 py-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Juru Parkir</th>
                            <th class="px-6 py-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Lokasi</th>
                            <th class="px-6 py-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Jumlah</th>
                            <th class="px-6 py-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                            <th class="px-6 py-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Waktu</th>
                        </tr>
                    </thead>
                    <tbody x-show="loading" class="divide-y divide-gray-200">
                        <tr class="animate-pulse">
                            <td class="px-6 py-4"><div class="h-4 bg-gray-200 rounded w-20"></div></td>
                            <td class="px-6 py-4"><div class="h-4 bg-gray-200 rounded w-24"></div></td>
                            <td class="px-6 py-4"><div class="h-4 bg-gray-200 rounded w-16"></div></td>
                            <td class="px-6 py-4"><div class="h-4 bg-gray-200 rounded w-18"></div></td>
                            <td class="px-6 py-4"><div class="h-6 bg-gray-200 rounded w-16"></div></td>
                            <td class="px-6 py-4"><div class="h-4 bg-gray-200 rounded w-20"></div></td>
                        </tr>
                        <tr class="animate-pulse">
                            <td class="px-6 py-4"><div class="h-4 bg-gray-200 rounded w-20"></div></td>
                            <td class="px-6 py-4"><div class="h-4 bg-gray-200 rounded w-24"></div></td>
                            <td class="px-6 py-4"><div class="h-4 bg-gray-200 rounded w-16"></div></td>
                            <td class="px-6 py-4"><div class="h-4 bg-gray-200 rounded w-18"></div></td>
                            <td class="px-6 py-4"><div class="h-6 bg-gray-200 rounded w-16"></div></td>
                            <td class="px-6 py-4"><div class="h-4 bg-gray-200 rounded w-20"></div></td>
                        </tr>
                        <tr class="animate-pulse">
                            <td class="px-6 py-4"><div class="h-4 bg-gray-200 rounded w-20"></div></td>
                            <td class="px-6 py-4"><div class="h-4 bg-gray-200 rounded w-24"></div></td>
                            <td class="px-6 py-4"><div class="h-4 bg-gray-200 rounded w-16"></div></td>
                            <td class="px-6 py-4"><div class="h-4 bg-gray-200 rounded w-18"></div></td>
                            <td class="px-6 py-4"><div class="h-6 bg-gray-200 rounded w-16"></div></td>
                            <td class="px-6 py-4"><div class="h-4 bg-gray-200 rounded w-20"></div></td>
                        </tr>
                    </tbody>
                    <tbody x-show="!loading" class="divide-y divide-gray-200">
                        <template x-for="transaction in paginatedTransactions" :key="transaction.id">
                            <tr class="hover:bg-gray-50 transition-colors duration-150">
                                <td class="px-6 py-4 text-sm font-medium text-gray-900" x-text="transaction.transaction_id"></td>
                                <td class="px-6 py-4 text-sm text-gray-900" x-text="transaction.parking_attendant?.name || '-'"></td>
                                <td class="px-6 py-4 text-sm text-gray-900" x-text="transaction.street_section"></td>
                                <td class="px-6 py-4 text-sm font-semibold text-gray-900">
                                    Rp <span x-text="formatCurrency(transaction.amount)"></span>
                                </td>
                                <td class="px-6 py-4 text-sm">
                                    <span :class="getStatusBadgeClass(transaction.payment_status)" x-text="getStatusLabel(transaction.payment_status)"></span>
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-600" x-text="formatDate(transaction.created_at)"></td>
                            </tr>
                        </template>
                        <template x-if="transactions.length === 0">
                            <tr>
                                <td colspan="6" class="px-6 py-12 text-center">
                                    <div class="flex flex-col items-center">
                                        <svg class="w-12 h-12 text-gray-400 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                        </svg>
                                        <p class="text-sm text-gray-500">Belum ada transaksi</p>
                                    </div>
                                </td>
                            </tr>
                        </template>
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <div x-show="transactions.length > 0 && !loading" class="px-6 py-4 bg-gray-50 border-t border-gray-200">
                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                    <p class="text-sm text-gray-700">
                        Menampilkan
                        <span class="font-medium" x-text="transactionStart"></span>
                        -
                        <span class="font-medium" x-text="transactionEnd"></span>
                        dari
                        <span class="font-medium" x-text="transactions.length"></span>
                        transaksi
                    </p>
                    <div class="flex items-center gap-2">
                        <button
                            @click="previousTransactionPage()"
                            :disabled="transactionsPage === 1"
                            class="px-3 py-2 text-sm font-medium rounded-md border border-gray-300 text-gray-700 disabled:text-gray-400 disabled:bg-gray-100 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors duration-150"
                        >
                            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                            </svg>
                            Sebelumnya
                        </button>
                        <span class="px-3 py-2 text-sm text-gray-700 bg-white border border-gray-300 rounded-md">
                            <span x-text="transactionsPage"></span> / <span x-text="totalTransactionPages"></span>
                        </span>
                        <button
                            @click="nextTransactionPage()"
                            :disabled="transactionsPage === totalTransactionPages"
                            class="px-3 py-2 text-sm font-medium rounded-md border border-gray-300 text-gray-700 disabled:text-gray-400 disabled:bg-gray-100 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors duration-150"
                        >
                            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                            </svg>
                            Berikutnya
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
function dashboard() {
    return {
        summary: {
            dailyRevenue: 0,
            monthlyRevenue: 0,
            totalTransactions: 0,
        },
        paymentStatus: {
            success: 0,
            pending: 0,
            failed: 0,
        },
        transactions: [],
        transactionsPage: 1,
        transactionsPerPage: 10,
        charts: {},
        refreshInterval: null,
        loading: false,

        async init() {
            this.loading = true;
            this.initCharts();
            await this.loadDashboardData();
            this.loading = false;

            // Auto-refresh every 30 seconds
            this.refreshInterval = setInterval(() => {
                this.loadDashboardData();
            }, 30000);
        },

        async loadDashboardData() {
            try {
                const response = await fetch('/api/dashboard', {
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    }
                });

                if (!response.ok) throw new Error('Failed to load dashboard data');

                const data = await response.json();
                this.summary = data.summary;
                this.paymentStatus = data.paymentStatus;
                this.transactions = data.transactions || [];
                this.clampTransactionPage();

                // Update charts
                this.updateCharts(data);
            } catch (error) {
                console.error('Error loading dashboard data:', error);
            }
        },

        async refreshData() {
            this.loading = true;
            await this.loadDashboardData();
            this.loading = false;
        },

        async refreshTransactions() {
            await this.loadDashboardData();
        },

        get totalTransactionPages() {
            return Math.max(1, Math.ceil(this.transactions.length / this.transactionsPerPage));
        },

        get paginatedTransactions() {
            const start = (this.transactionsPage - 1) * this.transactionsPerPage;
            return this.transactions.slice(start, start + this.transactionsPerPage);
        },

        get transactionStart() {
            if (this.transactions.length === 0) return 0;
            return (this.transactionsPage - 1) * this.transactionsPerPage + 1;
        },

        get transactionEnd() {
            return Math.min(this.transactionsPage * this.transactionsPerPage, this.transactions.length);
        },

        nextTransactionPage() {
            if (this.transactionsPage < this.totalTransactionPages) {
                this.transactionsPage++;
            }
        },

        previousTransactionPage() {
            if (this.transactionsPage > 1) {
                this.transactionsPage--;
            }
        },

        clampTransactionPage() {
            if (this.transactionsPage > this.totalTransactionPages) {
                this.transactionsPage = this.totalTransactionPages;
            }
        },

        initCharts() {
            // Daily Revenue Chart
            const dailyCtx = document.getElementById('dailyRevenueChart');
            if (dailyCtx) {
                this.charts.daily = new Chart(dailyCtx, {
                    type: 'line',
                    data: {
                        labels: [],
                        datasets: [{
                            label: 'Pendapatan (Rp)',
                            data: [],
                            borderColor: '#3b82f6',
                            backgroundColor: 'rgba(59, 130, 246, 0.1)',
                            tension: 0.4,
                            fill: true,
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: {
                                display: true,
                                position: 'top',
                            }
                        },
                        scales: {
                            y: {
                                beginAtZero: true,
                                ticks: {
                                    callback: function(value) {
                                        return 'Rp ' + value.toLocaleString('id-ID');
                                    }
                                }
                            }
                        }
                    }
                });
            }

            // Monthly Revenue Chart
            const monthlyCtx = document.getElementById('monthlyRevenueChart');
            if (monthlyCtx) {
                this.charts.monthly = new Chart(monthlyCtx, {
                    type: 'bar',
                    data: {
                        labels: [],
                        datasets: [{
                            label: 'Pendapatan (Rp)',
                            data: [],
                            backgroundColor: '#10b981',
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: {
                                display: true,
                                position: 'top',
                            }
                        },
                        scales: {
                            y: {
                                beginAtZero: true,
                                ticks: {
                                    callback: function(value) {
                                        return 'Rp ' + value.toLocaleString('id-ID');
                                    }
                                }
                            }
                        }
                    }
                });
            }

            // Location Distribution Chart
            const locationCtx = document.getElementById('locationChart');
            if (locationCtx) {
                this.charts.location = new Chart(locationCtx, {
                    type: 'pie',
                    data: {
                        labels: [],
                        datasets: [{
                            data: [],
                            backgroundColor: [
                                '#3b82f6', '#10b981', '#f59e0b', '#ef4444',
                                '#8b5cf6', '#ec4899', '#14b8a6', '#f97316'
                            ],
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: {
                                position: 'bottom',
                            }
                        }
                    }
                });
            }

            // Vehicle Type Distribution Chart
            const vehicleCtx = document.getElementById('vehicleChart');
            if (vehicleCtx) {
                this.charts.vehicle = new Chart(vehicleCtx, {
                    type: 'bar',
                    data: {
                        labels: [],
                        datasets: [{
                            label: 'Jumlah Transaksi',
                            data: [],
                            backgroundColor: '#f59e0b',
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: {
                                display: true,
                                position: 'top',
                            }
                        },
                        scales: {
                            y: {
                                beginAtZero: true,
                            }
                        }
                    }
                });
            }
        },

        updateCharts(data) {
            // Update Daily Revenue Chart
            if (this.charts.daily && data.dailyRevenue) {
                this.charts.daily.data.labels = data.dailyRevenue.map(d => d.date);
                this.charts.daily.data.datasets[0].data = data.dailyRevenue.map(d => d.revenue);
                this.charts.daily.update();
            }

            // Update Monthly Revenue Chart
            if (this.charts.monthly && data.monthlyRevenue) {
                this.charts.monthly.data.labels = data.monthlyRevenue.map(d => d.month);
                this.charts.monthly.data.datasets[0].data = data.monthlyRevenue.map(d => d.revenue);
                this.charts.monthly.update();
            }

            // Update Location Distribution Chart
            if (this.charts.location && data.locationStats) {
                this.charts.location.data.labels = data.locationStats.map(d => d.street_section);
                this.charts.location.data.datasets[0].data = data.locationStats.map(d => d.count);
                this.charts.location.update();
            }

            // Update Vehicle Type Distribution Chart
            if (this.charts.vehicle && data.vehicleStats) {
                this.charts.vehicle.data.labels = data.vehicleStats.map(d => d.vehicle_type);
                this.charts.vehicle.data.datasets[0].data = data.vehicleStats.map(d => d.count);
                this.charts.vehicle.update();
            }
        },

        formatCurrency(value) {
            return new Intl.NumberFormat('id-ID').format(value || 0);
        },

        formatDate(dateString) {
            const date = new Date(dateString);
            return date.toLocaleString('id-ID', {
                year: 'numeric',
                month: '2-digit',
                day: '2-digit',
                hour: '2-digit',
                minute: '2-digit'
            });
        },

        getStatusLabel(status) {
            const labels = {
                'success': 'Berhasil',
                'pending': 'Pending',
                'failed': 'Gagal',
                'expired': 'Kadaluarsa'
            };
            return labels[status] || status;
        },

        getStatusBadgeClass(status) {
            const classes = {
                'success': 'inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800',
                'pending': 'inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800',
                'failed': 'inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-red-100 text-red-800',
                'expired': 'inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-gray-100 text-gray-800'
            };
            return classes[status] || classes['pending'];
        },

        destroy() {
            if (this.refreshInterval) {
                clearInterval(this.refreshInterval);
            }
        }
    }
}
</script>
@endsection
