@extends('layouts.app')

@section('title', 'Dashboard - Sistem Monitoring Pembayaran Parkir')

@section('content')
<div x-data="dashboard()" x-init="init()" class="space-y-6">
    <div class="flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
        <div>
            <h1 class="text-2xl font-semibold text-gray-900">Dashboard</h1>
            <p class="mt-1 text-sm text-gray-600">Monitoring transaksi pembayaran parkir secara ringkas dan real-time.</p>
        </div>

        <div class="flex flex-col gap-2 sm:flex-row sm:items-center">
            <p class="text-xs text-gray-500" x-show="lastUpdated">
                Diperbarui <span x-text="lastUpdated"></span>
            </p>
            <button
                type="button"
                @click="refreshData()"
                :disabled="loading"
                class="inline-flex h-10 items-center justify-center gap-2 rounded-md bg-blue-600 px-4 text-sm font-medium text-white hover:bg-blue-700 disabled:cursor-not-allowed disabled:opacity-60"
            >
                <svg x-show="loading" class="h-4 w-4 animate-spin" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                </svg>
                <svg x-show="!loading" class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v6h6M20 20v-6h-6M20 9A8 8 0 006.3 4.7M4 15a8 8 0 0013.7 4.3"></path>
                </svg>
                Refresh
            </button>
        </div>
    </div>

    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-4">
        <div class="rounded-lg border border-gray-200 bg-white p-5 shadow-sm">
            <p class="text-sm font-medium text-gray-500">Pendapatan Hari Ini</p>
            <p class="mt-3 text-2xl font-semibold text-gray-900">Rp <span x-text="formatCurrency(summary.dailyRevenue)"></span></p>
            <p class="mt-1 text-xs text-gray-500"><span x-text="summary.todayTransactions"></span> transaksi hari ini</p>
        </div>

        <div class="rounded-lg border border-gray-200 bg-white p-5 shadow-sm">
            <p class="text-sm font-medium text-gray-500">Pendapatan Bulan Ini</p>
            <p class="mt-3 text-2xl font-semibold text-gray-900">Rp <span x-text="formatCurrency(summary.monthlyRevenue)"></span></p>
            <p class="mt-1 text-xs text-gray-500"><span x-text="summary.totalTransactions"></span> transaksi bulan ini</p>
        </div>

        <div class="rounded-lg border border-gray-200 bg-white p-5 shadow-sm">
            <p class="text-sm font-medium text-gray-500">Tingkat Berhasil</p>
            <p class="mt-3 text-2xl font-semibold text-gray-900"><span x-text="formatPercent(summary.successRate)"></span></p>
            <p class="mt-1 text-xs text-gray-500">Dari seluruh transaksi tercatat</p>
        </div>

        <div class="rounded-lg border border-gray-200 bg-white p-5 shadow-sm">
            <p class="text-sm font-medium text-gray-500">Status Aktif</p>
            <div class="mt-3 flex flex-wrap items-center gap-2 text-sm">
                <span class="inline-flex items-center rounded-full bg-green-50 px-2.5 py-1 font-medium text-green-700">Berhasil <span class="ml-1" x-text="paymentStatus.success"></span></span>
                <span class="inline-flex items-center rounded-full bg-yellow-50 px-2.5 py-1 font-medium text-yellow-700">Pending <span class="ml-1" x-text="paymentStatus.pending"></span></span>
            </div>
            <p class="mt-2 text-xs text-gray-500">Gagal: <span x-text="paymentStatus.failed"></span>, kedaluwarsa: <span x-text="paymentStatus.expired"></span></p>
        </div>
    </div>

    <div class="grid grid-cols-1 gap-4 xl:grid-cols-2">
        <section class="rounded-lg border border-gray-200 bg-white p-5 shadow-sm">
            <div class="mb-4 flex items-center justify-between gap-3">
                <div>
                    <h2 class="text-base font-semibold text-gray-900">Pendapatan Harian</h2>
                    <p class="text-xs text-gray-500">30 hari terakhir</p>
                </div>
            </div>
            <div class="relative h-72">
                <canvas id="dailyRevenueChart" class="h-full w-full"></canvas>
            </div>
        </section>

        <section class="rounded-lg border border-gray-200 bg-white p-5 shadow-sm">
            <div class="mb-4 flex items-center justify-between gap-3">
                <div>
                    <h2 class="text-base font-semibold text-gray-900">Pendapatan Bulanan</h2>
                    <p class="text-xs text-gray-500">12 bulan terakhir</p>
                </div>
            </div>
            <div class="relative h-72">
                <canvas id="monthlyRevenueChart" class="h-full w-full"></canvas>
            </div>
        </section>

        <section class="rounded-lg border border-gray-200 bg-white p-5 shadow-sm">
            <div class="mb-4 flex items-center justify-between gap-3">
                <div>
                    <h2 class="text-base font-semibold text-gray-900">Distribusi Lokasi</h2>
                    <p class="text-xs text-gray-500">Transaksi berhasil per lokasi</p>
                </div>
            </div>
            <div class="relative h-72">
                <canvas id="locationChart" class="h-full w-full"></canvas>
            </div>
        </section>

        <section class="rounded-lg border border-gray-200 bg-white p-5 shadow-sm">
            <div class="mb-4 flex items-center justify-between gap-3">
                <div>
                    <h2 class="text-base font-semibold text-gray-900">Jenis Kendaraan</h2>
                    <p class="text-xs text-gray-500">Transaksi berhasil per jenis kendaraan</p>
                </div>
            </div>
            <div class="relative h-72">
                <canvas id="vehicleChart" class="h-full w-full"></canvas>
            </div>
        </section>
    </div>

    <section class="overflow-hidden rounded-lg border border-gray-200 bg-white shadow-sm">
        <div class="border-b border-gray-200 px-5 py-4">
            <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
                <div>
                    <h2 class="text-base font-semibold text-gray-900">Transaksi Terbaru</h2>
                    <p class="text-xs text-gray-500">Maksimal 50 transaksi terakhir, ditampilkan dengan pagination.</p>
                </div>

                <div class="grid grid-cols-1 gap-2 sm:grid-cols-[minmax(220px,1fr)_150px_110px]">
                    <input
                        type="search"
                        x-model.debounce.250ms="transactionSearch"
                        @input="transactionsPage = 1"
                        placeholder="Cari ID, lokasi, juru parkir"
                        class="h-10 rounded-md border border-gray-300 px-3 text-sm focus:border-blue-500 focus:outline-none focus:ring-1 focus:ring-blue-500"
                    >
                    <select
                        x-model="transactionStatusFilter"
                        @change="transactionsPage = 1"
                        class="h-10 rounded-md border border-gray-300 px-3 text-sm focus:border-blue-500 focus:outline-none focus:ring-1 focus:ring-blue-500"
                    >
                        <option value="">Semua status</option>
                        <option value="success">Berhasil</option>
                        <option value="pending">Pending</option>
                        <option value="failed">Gagal</option>
                        <option value="expired">Kedaluwarsa</option>
                    </select>
                    <select
                        x-model.number="transactionsPerPage"
                        @change="transactionsPage = 1"
                        class="h-10 rounded-md border border-gray-300 px-3 text-sm focus:border-blue-500 focus:outline-none focus:ring-1 focus:ring-blue-500"
                    >
                        <option :value="10">10 baris</option>
                        <option :value="25">25 baris</option>
                        <option :value="50">50 baris</option>
                    </select>
                </div>
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="min-w-full table-fixed divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="w-56 px-5 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500">ID Transaksi</th>
                        <th class="w-40 px-5 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500">Juru Parkir</th>
                        <th class="w-40 px-5 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500">Lokasi</th>
                        <th class="w-32 px-5 py-3 text-right text-xs font-semibold uppercase tracking-wide text-gray-500">Jumlah</th>
                        <th class="w-32 px-5 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500">Status</th>
                        <th class="w-40 px-5 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500">Waktu</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 bg-white">
                    <template x-for="transaction in paginatedTransactions" :key="transaction.id">
                        <tr class="hover:bg-gray-50">
                            <td class="px-5 py-3 text-sm font-medium text-gray-900">
                                <span class="block truncate" x-text="transaction.transaction_id"></span>
                            </td>
                            <td class="px-5 py-3 text-sm text-gray-700">
                                <span class="block truncate" x-text="transaction.parking_attendant?.name || '-'"></span>
                            </td>
                            <td class="px-5 py-3 text-sm text-gray-700">
                                <span class="block truncate" x-text="transaction.street_section || '-'"></span>
                            </td>
                            <td class="px-5 py-3 text-right text-sm font-semibold text-gray-900">Rp <span x-text="formatCurrency(transaction.amount)"></span></td>
                            <td class="px-5 py-3 text-sm">
                                <span :class="getStatusBadgeClass(transaction.payment_status)" x-text="getStatusLabel(transaction.payment_status)"></span>
                            </td>
                            <td class="px-5 py-3 text-sm text-gray-600" x-text="formatDate(transaction.created_at)"></td>
                        </tr>
                    </template>
                    <template x-if="filteredTransactions.length === 0">
                        <tr>
                            <td colspan="6" class="px-5 py-10 text-center text-sm text-gray-500">Tidak ada transaksi yang cocok</td>
                        </tr>
                    </template>
                </tbody>
            </table>
        </div>

        <div class="flex flex-col gap-3 border-t border-gray-200 bg-gray-50 px-5 py-4 sm:flex-row sm:items-center sm:justify-between">
            <p class="text-sm text-gray-600">
                Menampilkan <span class="font-medium" x-text="transactionStart"></span>-<span class="font-medium" x-text="transactionEnd"></span>
                dari <span class="font-medium" x-text="filteredTransactions.length"></span> transaksi
            </p>
            <div class="inline-flex items-center gap-2">
                <button
                    type="button"
                    @click="previousTransactionPage()"
                    :disabled="transactionsPage === 1"
                    class="h-9 rounded-md border border-gray-300 bg-white px-3 text-sm font-medium text-gray-700 hover:bg-gray-50 disabled:cursor-not-allowed disabled:text-gray-400"
                >
                    Sebelumnya
                </button>
                <span class="w-16 text-center text-sm text-gray-700">
                    <span x-text="transactionsPage"></span> / <span x-text="totalTransactionPages"></span>
                </span>
                <button
                    type="button"
                    @click="nextTransactionPage()"
                    :disabled="transactionsPage === totalTransactionPages"
                    class="h-9 rounded-md border border-gray-300 bg-white px-3 text-sm font-medium text-gray-700 hover:bg-gray-50 disabled:cursor-not-allowed disabled:text-gray-400"
                >
                    Berikutnya
                </button>
            </div>
        </div>
    </section>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
function dashboard() {
    return {
        summary: {
            dailyRevenue: 0,
            monthlyRevenue: 0,
            totalTransactions: 0,
            todayTransactions: 0,
            successRate: 0,
        },
        paymentStatus: {
            success: 0,
            pending: 0,
            failed: 0,
            expired: 0,
        },
        transactions: [],
        transactionsPage: 1,
        transactionsPerPage: 10,
        transactionSearch: '',
        transactionStatusFilter: '',
        charts: {},
        refreshInterval: null,
        loading: false,
        lastUpdated: '',

        async init() {
            this.initCharts();
            await this.refreshData();

            this.refreshInterval = setInterval(() => {
                this.loadDashboardData();
            }, 30000);
        },

        async refreshData() {
            this.loading = true;
            await this.loadDashboardData();
            this.loading = false;
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
                this.summary = data.summary || this.summary;
                this.paymentStatus = data.paymentStatus || this.paymentStatus;
                this.transactions = data.transactions || [];
                this.lastUpdated = new Date().toLocaleTimeString('id-ID', {
                    hour: '2-digit',
                    minute: '2-digit',
                });
                this.clampTransactionPage();
                this.updateCharts(data);
            } catch (error) {
                console.error('Error loading dashboard data:', error);
            }
        },

        get filteredTransactions() {
            const search = this.transactionSearch.trim().toLowerCase();

            return this.transactions.filter((transaction) => {
                const statusMatches = !this.transactionStatusFilter || transaction.payment_status === this.transactionStatusFilter;
                const haystack = [
                    transaction.transaction_id,
                    transaction.street_section,
                    transaction.parking_attendant?.name,
                    transaction.vehicle_type,
                ].filter(Boolean).join(' ').toLowerCase();

                return statusMatches && (!search || haystack.includes(search));
            });
        },

        get totalTransactionPages() {
            return Math.max(1, Math.ceil(this.filteredTransactions.length / this.transactionsPerPage));
        },

        get paginatedTransactions() {
            this.clampTransactionPage();
            const start = (this.transactionsPage - 1) * this.transactionsPerPage;
            return this.filteredTransactions.slice(start, start + this.transactionsPerPage);
        },

        get transactionStart() {
            if (this.filteredTransactions.length === 0) return 0;
            return (this.transactionsPage - 1) * this.transactionsPerPage + 1;
        },

        get transactionEnd() {
            return Math.min(this.transactionsPage * this.transactionsPerPage, this.filteredTransactions.length);
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
            this.charts.daily = this.makeChart('dailyRevenueChart', 'line', {
                label: 'Pendapatan',
                borderColor: '#2563eb',
                backgroundColor: 'rgba(37, 99, 235, 0.12)',
                fill: true,
                tension: 0.35,
            });

            this.charts.monthly = this.makeChart('monthlyRevenueChart', 'bar', {
                label: 'Pendapatan',
                backgroundColor: '#059669',
                borderRadius: 4,
            });

            this.charts.location = this.makeChart('locationChart', 'doughnut', {
                backgroundColor: ['#2563eb', '#059669', '#d97706', '#dc2626', '#7c3aed', '#0891b2', '#65a30d', '#db2777'],
                borderWidth: 0,
            });

            this.charts.vehicle = this.makeChart('vehicleChart', 'bar', {
                label: 'Jumlah transaksi',
                backgroundColor: '#d97706',
                borderRadius: 4,
            });
        },

        makeChart(elementId, type, datasetOptions) {
            const element = document.getElementById(elementId);
            if (!element) return null;

            return new Chart(element, {
                type,
                data: {
                    labels: [],
                    datasets: [{
                        data: [],
                        ...datasetOptions,
                    }],
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    resizeDelay: 150,
                    animation: false,
                    plugins: {
                        legend: {
                            display: type === 'doughnut',
                            position: 'bottom',
                            labels: {
                                boxWidth: 10,
                                padding: 14,
                            },
                        },
                        tooltip: {
                            callbacks: {
                                label: (context) => {
                                    if (elementId.includes('Revenue')) {
                                        return 'Rp ' + Number(context.raw || 0).toLocaleString('id-ID');
                                    }
                                    return context.label + ': ' + context.raw;
                                }
                            }
                        }
                    },
                    scales: type === 'doughnut' ? {} : {
                        x: {
                            grid: { display: false },
                            ticks: { maxRotation: 0, autoSkip: true },
                        },
                        y: {
                            beginAtZero: true,
                            grid: { color: '#f3f4f6' },
                            ticks: {
                                callback: (value) => {
                                    if (elementId.includes('Revenue')) {
                                        return 'Rp ' + Number(value).toLocaleString('id-ID');
                                    }
                                    return value;
                                }
                            }
                        }
                    },
                },
            });
        },

        updateCharts(data) {
            this.updateChart(this.charts.daily, data.dailyRevenue || [], 'date', 'revenue');
            this.updateChart(this.charts.monthly, data.monthlyRevenue || [], 'month', 'revenue');
            this.updateChart(this.charts.location, data.locationStats || [], 'street_section', 'count');
            this.updateChart(this.charts.vehicle, data.vehicleStats || [], 'vehicle_type', 'count');
        },

        updateChart(chart, rows, labelKey, valueKey) {
            if (!chart) return;
            chart.data.labels = rows.map((row) => row[labelKey]);
            chart.data.datasets[0].data = rows.map((row) => Number(row[valueKey] || 0));
            chart.update('none');
        },

        formatCurrency(value) {
            return new Intl.NumberFormat('id-ID').format(value || 0);
        },

        formatPercent(value) {
            return new Intl.NumberFormat('id-ID', {
                maximumFractionDigits: 1,
            }).format(value || 0) + '%';
        },

        formatDate(dateString) {
            if (!dateString) return '-';
            const date = new Date(dateString);
            return date.toLocaleString('id-ID', {
                day: '2-digit',
                month: '2-digit',
                year: 'numeric',
                hour: '2-digit',
                minute: '2-digit'
            });
        },

        getStatusLabel(status) {
            const labels = {
                success: 'Berhasil',
                pending: 'Pending',
                failed: 'Gagal',
                expired: 'Kedaluwarsa',
            };
            return labels[status] || status || '-';
        },

        getStatusBadgeClass(status) {
            const classes = {
                success: 'inline-flex rounded-full bg-green-50 px-2.5 py-1 text-xs font-medium text-green-700',
                pending: 'inline-flex rounded-full bg-yellow-50 px-2.5 py-1 text-xs font-medium text-yellow-700',
                failed: 'inline-flex rounded-full bg-red-50 px-2.5 py-1 text-xs font-medium text-red-700',
                expired: 'inline-flex rounded-full bg-gray-100 px-2.5 py-1 text-xs font-medium text-gray-700',
            };
            return classes[status] || classes.pending;
        },

        destroy() {
            if (this.refreshInterval) clearInterval(this.refreshInterval);
            Object.values(this.charts).forEach((chart) => chart?.destroy());
        }
    }
}
</script>
@endsection
