@extends('layouts.app')

@section('title', 'Dashboard - Sistem Monitoring Pembayaran Parkir')

@section('content')
<style>
    [x-cloak] { display: none !important; }

    .dash-page {
        display: grid;
        gap: 24px;
    }

    .dash-toolbar,
    .dash-card,
    .dash-panel {
        background: #ffffff;
        border: 1px solid #e5e7eb;
        border-radius: 8px;
        box-shadow: 0 1px 2px rgba(15, 23, 42, 0.04);
    }

    .dash-toolbar {
        display: flex;
        align-items: flex-end;
        justify-content: space-between;
        gap: 16px;
        padding: 20px;
    }

    .dash-title {
        margin: 0;
        color: #111827;
        font-size: 24px;
        font-weight: 700;
        line-height: 1.25;
    }

    .dash-subtitle {
        margin: 6px 0 0;
        color: #6b7280;
        font-size: 14px;
    }

    .dash-actions {
        display: flex;
        align-items: center;
        gap: 12px;
        flex-wrap: wrap;
        justify-content: flex-end;
    }

    .dash-updated {
        color: #6b7280;
        font-size: 12px;
        white-space: nowrap;
    }

    .dash-button {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        min-height: 40px;
        gap: 8px;
        border: 1px solid #d1d5db;
        border-radius: 6px;
        background: #ffffff;
        color: #374151;
        cursor: pointer;
        font-size: 14px;
        font-weight: 600;
        padding: 8px 14px;
    }

    .dash-button:hover {
        background: #f9fafb;
    }

    .dash-button:disabled {
        cursor: not-allowed;
        opacity: 0.55;
    }

    .dash-button-primary {
        border-color: #2563eb;
        background: #2563eb;
        color: #ffffff;
    }

    .dash-button-primary:hover {
        background: #1d4ed8;
    }

    .dash-spinner {
        width: 16px;
        height: 16px;
        border: 2px solid rgba(255, 255, 255, 0.45);
        border-top-color: #ffffff;
        border-radius: 999px;
        animation: dash-spin 0.8s linear infinite;
    }

    @keyframes dash-spin {
        to { transform: rotate(360deg); }
    }

    .dash-alert {
        display: flex;
        align-items: flex-start;
        justify-content: space-between;
        gap: 12px;
        padding: 14px 16px;
        border: 1px solid #fecaca;
        border-radius: 8px;
        background: #fef2f2;
        color: #991b1b;
        font-size: 14px;
    }

    .dash-metrics {
        display: grid;
        grid-template-columns: repeat(4, minmax(0, 1fr));
        gap: 16px;
    }

    .dash-card {
        min-height: 128px;
        padding: 18px;
    }

    .dash-card-label {
        color: #6b7280;
        font-size: 13px;
        font-weight: 600;
    }

    .dash-card-value {
        margin-top: 12px;
        color: #111827;
        font-size: 26px;
        font-weight: 700;
        line-height: 1.15;
        word-break: break-word;
    }

    .dash-card-note {
        margin-top: 8px;
        color: #9ca3af;
        font-size: 12px;
    }

    .dash-status-row {
        display: flex;
        flex-wrap: wrap;
        gap: 8px;
        margin-top: 12px;
    }

    .dash-pill {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        border-radius: 999px;
        font-size: 12px;
        font-weight: 700;
        padding: 6px 10px;
    }

    .dash-pill-success { background: #ecfdf5; color: #047857; }
    .dash-pill-pending { background: #fffbeb; color: #b45309; }
    .dash-pill-failed { background: #fef2f2; color: #b91c1c; }
    .dash-pill-expired { background: #f3f4f6; color: #4b5563; }

    .dash-chart-grid {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 16px;
    }

    .dash-panel-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
        border-bottom: 1px solid #e5e7eb;
        padding: 16px 18px;
    }

    .dash-panel-title {
        margin: 0;
        color: #111827;
        font-size: 15px;
        font-weight: 700;
    }

    .dash-panel-caption {
        margin: 4px 0 0;
        color: #6b7280;
        font-size: 12px;
    }

    .dash-chart-body {
        height: 320px;
        min-height: 320px;
        padding: 16px;
        position: relative;
    }

    .dash-chart-body canvas {
        display: block;
        width: 100% !important;
        height: 100% !important;
    }

    .dash-table-tools {
        display: grid;
        grid-template-columns: minmax(240px, 1fr) 150px 120px;
        gap: 10px;
        min-width: min(100%, 560px);
    }

    .dash-input,
    .dash-select {
        width: 100%;
        min-height: 40px;
        border: 1px solid #d1d5db;
        border-radius: 6px;
        background: #ffffff;
        color: #111827;
        font-size: 14px;
        padding: 8px 10px;
    }

    .dash-input:focus,
    .dash-select:focus {
        border-color: #2563eb;
        box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.12);
        outline: none;
    }

    .dash-table-wrap {
        overflow-x: auto;
    }

    .dash-table {
        width: 100%;
        min-width: 920px;
        border-collapse: collapse;
        table-layout: fixed;
    }

    .dash-table th {
        background: #f9fafb;
        border-bottom: 1px solid #e5e7eb;
        color: #6b7280;
        font-size: 11px;
        font-weight: 800;
        letter-spacing: 0.04em;
        padding: 12px 14px;
        text-align: left;
        text-transform: uppercase;
    }

    .dash-table td {
        border-bottom: 1px solid #f3f4f6;
        color: #374151;
        font-size: 13px;
        padding: 12px 14px;
        vertical-align: middle;
    }

    .dash-table tbody tr:hover {
        background: #f8fafc;
    }

    .dash-truncate {
        display: block;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
    }

    .dash-money {
        color: #111827;
        font-weight: 700;
        text-align: right;
    }

    .dash-empty {
        padding: 42px 16px !important;
        text-align: center;
        color: #6b7280 !important;
    }

    .dash-pagination {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
        background: #f9fafb;
        border-top: 1px solid #e5e7eb;
        padding: 14px 18px;
    }

    .dash-page-info {
        color: #6b7280;
        font-size: 13px;
    }

    .dash-page-controls {
        display: inline-flex;
        align-items: center;
        gap: 8px;
    }

    .dash-page-count {
        color: #374151;
        font-size: 13px;
        min-width: 58px;
        text-align: center;
    }

    @media (max-width: 1180px) {
        .dash-metrics {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }
    }

    @media (max-width: 920px) {
        .dash-toolbar,
        .dash-panel-header,
        .dash-pagination {
            align-items: stretch;
            flex-direction: column;
        }

        .dash-actions {
            justify-content: flex-start;
        }

        .dash-chart-grid,
        .dash-metrics {
            grid-template-columns: 1fr;
        }

        .dash-table-tools {
            grid-template-columns: 1fr;
            min-width: 0;
        }
    }

    @media (max-width: 640px) {
        .dash-title {
            font-size: 22px;
        }

        .dash-card-value {
            font-size: 22px;
        }

        .dash-chart-body {
            height: 280px;
            min-height: 280px;
        }

        .dash-page-controls {
            justify-content: space-between;
            width: 100%;
        }
    }
</style>

<div x-data="dashboard()" x-init="init()" class="dash-page">
    <section class="dash-toolbar">
        <div>
            <h1 class="dash-title">Dashboard</h1>
            <p class="dash-subtitle">Monitoring transaksi pembayaran parkir secara real-time.</p>
        </div>

        <div class="dash-actions">
            <span class="dash-updated" x-text="lastUpdated || 'Belum dimuat'"></span>
            <button type="button" class="dash-button dash-button-primary" @click="refreshData()" :disabled="loading">
                <span x-show="loading" class="dash-spinner" aria-hidden="true"></span>
                <span x-text="loading ? 'Memuat' : 'Refresh'"></span>
            </button>
        </div>
    </section>

    <template x-if="errorMessage">
        <div class="dash-alert">
            <span x-text="errorMessage"></span>
            <button type="button" class="dash-button" @click="refreshData()">Coba Lagi</button>
        </div>
    </template>

    <section class="dash-metrics" aria-label="Ringkasan dashboard">
        <div class="dash-card">
            <div class="dash-card-label">Pendapatan Hari Ini</div>
            <div class="dash-card-value">Rp <span x-text="formatCurrency(summary.dailyRevenue)"></span></div>
            <div class="dash-card-note"><span x-text="summary.todayTransactions"></span> transaksi hari ini</div>
        </div>

        <div class="dash-card">
            <div class="dash-card-label">Pendapatan Bulan Ini</div>
            <div class="dash-card-value">Rp <span x-text="formatCurrency(summary.monthlyRevenue)"></span></div>
            <div class="dash-card-note"><span x-text="summary.totalTransactions"></span> transaksi bulan ini</div>
        </div>

        <div class="dash-card">
            <div class="dash-card-label">Tingkat Berhasil</div>
            <div class="dash-card-value" x-text="formatPercent(summary.successRate)"></div>
            <div class="dash-card-note">Rasio transaksi berhasil dari seluruh data</div>
        </div>

        <div class="dash-card">
            <div class="dash-card-label">Status Pembayaran</div>
            <div class="dash-status-row">
                <span class="dash-pill dash-pill-success">Berhasil <span x-text="paymentStatus.success"></span></span>
                <span class="dash-pill dash-pill-pending">Pending <span x-text="paymentStatus.pending"></span></span>
                <span class="dash-pill dash-pill-failed">Gagal <span x-text="paymentStatus.failed"></span></span>
                <span class="dash-pill dash-pill-expired">Expired <span x-text="paymentStatus.expired"></span></span>
            </div>
        </div>
    </section>

    <section class="dash-chart-grid" aria-label="Grafik dashboard">
        <div class="dash-panel">
            <div class="dash-panel-header">
                <div>
                    <h2 class="dash-panel-title">Pendapatan Harian</h2>
                    <p class="dash-panel-caption">30 hari terakhir</p>
                </div>
            </div>
            <div class="dash-chart-body">
                <canvas id="dailyRevenueChart"></canvas>
            </div>
        </div>

        <div class="dash-panel">
            <div class="dash-panel-header">
                <div>
                    <h2 class="dash-panel-title">Pendapatan Bulanan</h2>
                    <p class="dash-panel-caption">12 bulan terakhir</p>
                </div>
            </div>
            <div class="dash-chart-body">
                <canvas id="monthlyRevenueChart"></canvas>
            </div>
        </div>

        <div class="dash-panel">
            <div class="dash-panel-header">
                <div>
                    <h2 class="dash-panel-title">Distribusi Lokasi</h2>
                    <p class="dash-panel-caption">Transaksi berhasil per lokasi</p>
                </div>
            </div>
            <div class="dash-chart-body">
                <canvas id="locationChart"></canvas>
            </div>
        </div>

        <div class="dash-panel">
            <div class="dash-panel-header">
                <div>
                    <h2 class="dash-panel-title">Jenis Kendaraan</h2>
                    <p class="dash-panel-caption">Transaksi berhasil per jenis kendaraan</p>
                </div>
            </div>
            <div class="dash-chart-body">
                <canvas id="vehicleChart"></canvas>
            </div>
        </div>
    </section>

    <section class="dash-panel">
        <div class="dash-panel-header">
            <div>
                <h2 class="dash-panel-title">Transaksi Terbaru</h2>
                <p class="dash-panel-caption">Maksimal 50 transaksi terakhir dengan pencarian dan pagination.</p>
            </div>

            <div class="dash-table-tools">
                <input
                    type="search"
                    class="dash-input"
                    x-model.debounce.250ms="transactionSearch"
                    @input="transactionsPage = 1"
                    placeholder="Cari ID, lokasi, juru parkir"
                >
                <select class="dash-select" x-model="transactionStatusFilter" @change="transactionsPage = 1">
                    <option value="">Semua status</option>
                    <option value="success">Berhasil</option>
                    <option value="pending">Pending</option>
                    <option value="failed">Gagal</option>
                    <option value="expired">Expired</option>
                </select>
                <select class="dash-select" x-model.number="transactionsPerPage" @change="transactionsPage = 1">
                    <option :value="10">10 baris</option>
                    <option :value="25">25 baris</option>
                    <option :value="50">50 baris</option>
                </select>
            </div>
        </div>

        <div class="dash-table-wrap">
            <table class="dash-table">
                <thead>
                    <tr>
                        <th style="width: 250px;">ID Transaksi</th>
                        <th style="width: 170px;">Juru Parkir</th>
                        <th style="width: 180px;">Lokasi</th>
                        <th style="width: 130px; text-align: right;">Jumlah</th>
                        <th style="width: 130px;">Status</th>
                        <th style="width: 180px;">Waktu</th>
                    </tr>
                </thead>
                <tbody>
                    <template x-for="transaction in paginatedTransactions" :key="transaction.id">
                        <tr>
                            <td><span class="dash-truncate" x-text="transaction.transaction_id"></span></td>
                            <td><span class="dash-truncate" x-text="transaction.parking_attendant?.name || '-'"></span></td>
                            <td><span class="dash-truncate" x-text="transaction.street_section || '-'"></span></td>
                            <td class="dash-money">Rp <span x-text="formatCurrency(transaction.amount)"></span></td>
                            <td>
                                <span :class="getStatusBadgeClass(transaction.payment_status)" x-text="getStatusLabel(transaction.payment_status)"></span>
                            </td>
                            <td x-text="formatDate(transaction.created_at)"></td>
                        </tr>
                    </template>
                    <template x-if="filteredTransactions.length === 0">
                        <tr>
                            <td class="dash-empty" colspan="6">Tidak ada transaksi yang cocok.</td>
                        </tr>
                    </template>
                </tbody>
            </table>
        </div>

        <div class="dash-pagination">
            <div class="dash-page-info">
                Menampilkan
                <strong x-text="transactionStart"></strong>-<strong x-text="transactionEnd"></strong>
                dari <strong x-text="filteredTransactions.length"></strong> transaksi
            </div>
            <div class="dash-page-controls">
                <button type="button" class="dash-button" @click="previousTransactionPage()" :disabled="transactionsPage === 1">
                    Sebelumnya
                </button>
                <span class="dash-page-count"><span x-text="transactionsPage"></span> / <span x-text="totalTransactionPages"></span></span>
                <button type="button" class="dash-button" @click="nextTransactionPage()" :disabled="transactionsPage === totalTransactionPages">
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
        errorMessage: '',

        async init() {
            this.initCharts();
            await this.refreshData();

            this.refreshInterval = setInterval(() => {
                this.loadDashboardData(false);
            }, 30000);
        },

        async refreshData() {
            this.loading = true;
            await this.loadDashboardData(true);
            this.loading = false;
        },

        async loadDashboardData(showErrors = true) {
            try {
                const response = await fetch('/api/dashboard', {
                    credentials: 'same-origin',
                    headers: {
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    }
                });

                if (response.status === 401) {
                    window.location.href = '/login';
                    return;
                }

                if (!response.ok) {
                    let message = 'API dashboard gagal dimuat';
                    try {
                        const errorData = await response.json();
                        message = errorData.message || message;
                    } catch (parseError) {
                        message = `${message} (${response.status})`;
                    }
                    throw new Error(message);
                }

                const data = await response.json();
                this.summary = {
                    dailyRevenue: Number(data.summary?.dailyRevenue ?? data.today_revenue ?? 0),
                    monthlyRevenue: Number(data.summary?.monthlyRevenue ?? data.month_revenue ?? 0),
                    totalTransactions: Number(data.summary?.totalTransactions ?? data.month_transactions ?? 0),
                    todayTransactions: Number(data.summary?.todayTransactions ?? data.today_transactions ?? 0),
                    successRate: Number(data.summary?.successRate ?? data.success_rate ?? 0),
                };
                this.paymentStatus = {
                    success: Number(data.paymentStatus?.success ?? data.status_distribution?.success ?? 0),
                    pending: Number(data.paymentStatus?.pending ?? data.status_distribution?.pending ?? 0),
                    failed: Number(data.paymentStatus?.failed ?? data.status_distribution?.failed ?? 0),
                    expired: Number(data.paymentStatus?.expired ?? data.status_distribution?.expired ?? 0),
                };
                this.transactions = data.transactions || [];
                this.lastUpdated = 'Diperbarui ' + new Date().toLocaleTimeString('id-ID', {
                    hour: '2-digit',
                    minute: '2-digit',
                });
                this.errorMessage = '';
                this.clampTransactionPage();
                this.updateCharts(data);
            } catch (error) {
                console.error('Error loading dashboard data:', error);
                if (showErrors) {
                    this.errorMessage = error.message || 'Dashboard belum bisa memuat data. Periksa sesi login atau koneksi API.';
                }
            }
        },

        get filteredTransactions() {
            const search = this.transactionSearch.trim().toLowerCase();

            return this.transactions.filter((transaction) => {
                const matchesStatus = !this.transactionStatusFilter || transaction.payment_status === this.transactionStatusFilter;
                const searchableText = [
                    transaction.transaction_id,
                    transaction.street_section,
                    transaction.parking_attendant?.name,
                    transaction.vehicle_type,
                ].filter(Boolean).join(' ').toLowerCase();

                return matchesStatus && (!search || searchableText.includes(search));
            });
        },

        get totalTransactionPages() {
            return Math.max(1, Math.ceil(this.filteredTransactions.length / this.transactionsPerPage));
        },

        get paginatedTransactions() {
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
            if (!window.Chart) return;

            this.charts.daily = this.makeCartesianChart('dailyRevenueChart', 'line', {
                label: 'Pendapatan',
                borderColor: '#2563eb',
                backgroundColor: 'rgba(37, 99, 235, 0.12)',
                fill: true,
                tension: 0.35,
                pointRadius: 2,
                pointHoverRadius: 4,
            }, true);

            this.charts.monthly = this.makeCartesianChart('monthlyRevenueChart', 'bar', {
                label: 'Pendapatan',
                backgroundColor: '#059669',
                borderRadius: 4,
            }, true);

            this.charts.location = this.makeDoughnutChart('locationChart');

            this.charts.vehicle = this.makeCartesianChart('vehicleChart', 'bar', {
                label: 'Jumlah transaksi',
                backgroundColor: '#d97706',
                borderRadius: 4,
            }, false);
        },

        makeCartesianChart(elementId, type, datasetOptions, isMoney) {
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
                    animation: false,
                    resizeDelay: 150,
                    plugins: {
                        legend: { display: false },
                        tooltip: {
                            callbacks: {
                                label: (context) => isMoney
                                    ? 'Rp ' + Number(context.raw || 0).toLocaleString('id-ID')
                                    : String(context.raw || 0),
                            },
                        },
                    },
                    scales: {
                        x: {
                            grid: { display: false },
                            ticks: { maxRotation: 0, autoSkip: true },
                        },
                        y: {
                            beginAtZero: true,
                            grid: { color: '#f3f4f6' },
                            ticks: {
                                callback: (value) => isMoney
                                    ? 'Rp ' + Number(value).toLocaleString('id-ID')
                                    : value,
                            },
                        },
                    },
                },
            });
        },

        makeDoughnutChart(elementId) {
            const element = document.getElementById(elementId);
            if (!element) return null;

            return new Chart(element, {
                type: 'doughnut',
                data: {
                    labels: [],
                    datasets: [{
                        data: [],
                        backgroundColor: ['#2563eb', '#059669', '#d97706', '#dc2626', '#7c3aed', '#0891b2', '#65a30d', '#db2777'],
                        borderWidth: 0,
                    }],
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    animation: false,
                    resizeDelay: 150,
                    plugins: {
                        legend: {
                            position: 'bottom',
                            labels: {
                                boxWidth: 10,
                                padding: 14,
                            },
                        },
                    },
                },
            });
        },

        updateCharts(data) {
            try {
                this.updateChart(this.charts.daily, data.dailyRevenue || [], 'date', 'revenue');
                this.updateChart(this.charts.monthly, data.monthlyRevenue || [], 'month', 'revenue');
                this.updateChart(this.charts.location, data.locationStats || [], 'street_section', 'count');
                this.updateChart(this.charts.vehicle, data.vehicleStats || [], 'vehicle_type', 'count');
            } catch (error) {
                console.error('Error updating dashboard charts:', error);
            }
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

            return new Date(dateString).toLocaleString('id-ID', {
                day: '2-digit',
                month: '2-digit',
                year: 'numeric',
                hour: '2-digit',
                minute: '2-digit',
            });
        },

        getStatusLabel(status) {
            const labels = {
                success: 'Berhasil',
                pending: 'Pending',
                failed: 'Gagal',
                expired: 'Expired',
            };
            return labels[status] || status || '-';
        },

        getStatusBadgeClass(status) {
            const classes = {
                success: 'dash-pill dash-pill-success',
                pending: 'dash-pill dash-pill-pending',
                failed: 'dash-pill dash-pill-failed',
                expired: 'dash-pill dash-pill-expired',
            };
            return classes[status] || classes.pending;
        },
    }
}
</script>
@endsection
