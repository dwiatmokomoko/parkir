@extends('layouts.app')

@section('title', 'Dashboard - Sistem Monitoring Pembayaran Parkir')

@section('content')
<div x-data="dashboard()" @load="init()" class="space-y-8">
    <!-- Page Header -->
    <div>
        <h1 class="text-3xl font-bold text-gray-900">Dashboard</h1>
        <p class="text-gray-600 mt-2">Monitoring real-time transaksi pembayaran parkir</p>
    </div>

    <!-- Summary Cards -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <!-- Daily Revenue Card -->
        <div class="bg-white rounded-lg shadow p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-gray-600 text-sm font-medium">Pendapatan Hari Ini</p>
                    <p class="text-3xl font-bold text-gray-900 mt-2">
                        Rp <span x-text="formatCurrency(summary.dailyRevenue)"></span>
                    </p>
                </div>
                <div class="bg-blue-100 rounded-full p-3">
                    <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                </div>
            </div>
        </div>

        <!-- Monthly Revenue Card -->
        <div class="bg-white rounded-lg shadow p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-gray-600 text-sm font-medium">Pendapatan Bulan Ini</p>
                    <p class="text-3xl font-bold text-gray-900 mt-2">
                        Rp <span x-text="formatCurrency(summary.monthlyRevenue)"></span>
                    </p>
                </div>
                <div class="bg-green-100 rounded-full p-3">
                    <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8L5.257 19.393A2 2 0 005 18.07V5a2 2 0 012-2h5.5"></path>
                    </svg>
                </div>
            </div>
        </div>

        <!-- Total Transactions Card -->
        <div class="bg-white rounded-lg shadow p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-gray-600 text-sm font-medium">Total Transaksi</p>
                    <p class="text-3xl font-bold text-gray-900 mt-2" x-text="summary.totalTransactions"></p>
                </div>
                <div class="bg-purple-100 rounded-full p-3">
                    <svg class="w-6 h-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                </div>
            </div>
        </div>
    </div>

    <!-- Charts Section -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Daily Revenue Chart -->
        <div class="bg-white rounded-lg shadow p-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">Pendapatan Harian (30 Hari Terakhir)</h3>
            <canvas id="dailyRevenueChart" height="300"></canvas>
        </div>

        <!-- Monthly Revenue Chart -->
        <div class="bg-white rounded-lg shadow p-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">Pendapatan Bulanan (12 Bulan Terakhir)</h3>
            <canvas id="monthlyRevenueChart" height="300"></canvas>
        </div>

        <!-- Location Distribution Chart -->
        <div class="bg-white rounded-lg shadow p-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">Distribusi Transaksi per Lokasi</h3>
            <canvas id="locationChart" height="300"></canvas>
        </div>

        <!-- Vehicle Type Distribution Chart -->
        <div class="bg-white rounded-lg shadow p-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">Distribusi Transaksi per Jenis Kendaraan</h3>
            <canvas id="vehicleChart" height="300"></canvas>
        </div>
    </div>

    <!-- Payment Status Distribution -->
    <div class="bg-white rounded-lg shadow p-6">
        <h3 class="text-lg font-semibold text-gray-900 mb-4">Distribusi Status Pembayaran</h3>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div class="flex items-center justify-between p-4 bg-green-50 rounded-lg">
                <div>
                    <p class="text-gray-600 text-sm">Berhasil</p>
                    <p class="text-2xl font-bold text-green-600" x-text="paymentStatus.success"></p>
                </div>
                <div class="text-green-600 opacity-20">
                    <svg class="w-12 h-12" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                    </svg>
                </div>
            </div>

            <div class="flex items-center justify-between p-4 bg-yellow-50 rounded-lg">
                <div>
                    <p class="text-gray-600 text-sm">Pending</p>
                    <p class="text-2xl font-bold text-yellow-600" x-text="paymentStatus.pending"></p>
                </div>
                <div class="text-yellow-600 opacity-20">
                    <svg class="w-12 h-12" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-11a1 1 0 10-2 0v3.5a1 1 0 002 0V7z" clip-rule="evenodd"></path>
                    </svg>
                </div>
            </div>

            <div class="flex items-center justify-between p-4 bg-red-50 rounded-lg">
                <div>
                    <p class="text-gray-600 text-sm">Gagal</p>
                    <p class="text-2xl font-bold text-red-600" x-text="paymentStatus.failed"></p>
                </div>
                <div class="text-red-600 opacity-20">
                    <svg class="w-12 h-12" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"></path>
                    </svg>
                </div>
            </div>
        </div>
    </div>

    <!-- Recent Transactions -->
    <div class="bg-white rounded-lg shadow p-6">
        <div class="flex items-center justify-between mb-4">
            <h3 class="text-lg font-semibold text-gray-900">Transaksi Terbaru</h3>
            <button @click="refreshTransactions()" class="text-blue-600 hover:text-blue-700 text-sm font-medium">
                Refresh
            </button>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-gray-50 border-b border-gray-200">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-700 uppercase tracking-wider">ID Transaksi</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-700 uppercase tracking-wider">Juru Parkir</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-700 uppercase tracking-wider">Lokasi</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-700 uppercase tracking-wider">Jumlah</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-700 uppercase tracking-wider">Status</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-700 uppercase tracking-wider">Waktu</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    <template x-for="transaction in transactions" :key="transaction.id">
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4 text-sm text-gray-900" x-text="transaction.transaction_id"></td>
                            <td class="px-6 py-4 text-sm text-gray-900" x-text="transaction.parking_attendant?.name || '-'"></td>
                            <td class="px-6 py-4 text-sm text-gray-900" x-text="transaction.street_section"></td>
                            <td class="px-6 py-4 text-sm font-medium text-gray-900">
                                Rp <span x-text="formatCurrency(transaction.amount)"></span>
                            </td>
                            <td class="px-6 py-4 text-sm">
                                <span :class="getStatusBadgeClass(transaction.payment_status)" x-text="getStatusLabel(transaction.payment_status)"></span>
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-600" x-text="formatDate(transaction.created_at)"></td>
                        </tr>
                    </template>
                </tbody>
            </table>
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
        charts: {},
        refreshInterval: null,

        async init() {
            await this.loadDashboardData();
            this.initCharts();
            
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

                // Update charts
                this.updateCharts(data);
            } catch (error) {
                console.error('Error loading dashboard data:', error);
            }
        },

        async refreshTransactions() {
            await this.loadDashboardData();
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
