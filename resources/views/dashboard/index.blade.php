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
        background:
            repeating-linear-gradient(0deg, rgba(148, 163, 184, 0.10) 0, rgba(148, 163, 184, 0.10) 1px, transparent 1px, transparent 28px),
            #ffffff;
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

    .dash-bars-body {
        height: 320px;
        min-height: 320px;
    }

    .dash-month-bars {
        display: grid;
        gap: 7px;
        grid-template-columns: 1fr;
        height: 100%;
        align-content: center;
    }

    .dash-month-bar {
        align-items: center;
        display: grid;
        gap: 10px;
        grid-template-columns: 84px minmax(0, 1fr) 100px;
        min-width: 0;
    }

    .dash-month-value,
    .dash-month-label {
        color: #475569;
        font-size: 11px;
        font-weight: 700;
        line-height: 1.2;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
    }

    .dash-month-label {
        text-align: left;
    }

    .dash-month-value {
        text-align: right;
    }

    .dash-month-track {
        background: #e2e8f0;
        border-radius: 999px;
        display: flex;
        height: 12px;
        overflow: hidden;
    }

    .dash-month-fill {
        background: linear-gradient(90deg, #059669, #22c55e);
        border-radius: inherit;
        height: 100%;
        min-width: 0;
        transition: width 0.2s ease;
    }

    .dash-month-bar:nth-child(3n+1) .dash-month-fill { background: linear-gradient(90deg, #0891b2, #22d3ee); }
    .dash-month-bar:nth-child(3n+2) .dash-month-fill { background: linear-gradient(90deg, #059669, #34d399); }
    .dash-month-bar:nth-child(3n+3) .dash-month-fill { background: linear-gradient(90deg, #2563eb, #60a5fa); }

    .dash-bar-list {
        display: grid;
        gap: 14px;
        height: 100%;
        align-content: center;
    }

    .dash-bar-row {
        display: grid;
        gap: 8px;
    }

    .dash-bar-meta {
        align-items: center;
        color: #475569;
        display: flex;
        font-size: 12px;
        font-weight: 700;
        gap: 12px;
        justify-content: space-between;
    }

    .dash-bar-label {
        min-width: 0;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
    }

    .dash-bar-value {
        color: #0f172a;
        flex: 0 0 auto;
    }

    .dash-bar-track {
        background: #e2e8f0;
        border-radius: 999px;
        height: 16px;
        overflow: hidden;
    }

    .dash-bar-fill {
        border-radius: inherit;
        height: 100%;
        min-width: 4px;
        transition: width 0.2s ease;
    }

    .dash-bar-fill-location { background: linear-gradient(90deg, #7c3aed, #a855f7); }
    .dash-bar-fill-vehicle { background: linear-gradient(90deg, #d97706, #f59e0b); }

    .dash-bar-row:nth-child(even) .dash-bar-fill-location {
        background: linear-gradient(90deg, #2563eb, #38bdf8);
    }

    .dash-bar-row:nth-child(even) .dash-bar-fill-vehicle {
        background: linear-gradient(90deg, #059669, #34d399);
    }

    .dash-donut-body {
        align-items: center;
        display: grid;
        gap: 24px;
        grid-template-columns: minmax(170px, 220px) minmax(0, 1fr);
    }

    .dash-donut {
        aspect-ratio: 1;
        background: conic-gradient(var(--donut-gradient, #e2e8f0));
        border-radius: 999px;
        box-shadow: inset 0 0 0 1px rgba(15, 23, 42, 0.06), 0 14px 30px rgba(15, 23, 42, 0.08);
        display: grid;
        margin: 0 auto;
        max-width: 210px;
        place-items: center;
        position: relative;
        width: 100%;
    }

    .dash-donut::after {
        background: #ffffff;
        border-radius: inherit;
        box-shadow: inset 0 0 0 1px #e5e7eb;
        content: "";
        height: 62%;
        position: absolute;
        width: 62%;
    }

    .dash-donut-center {
        color: #0f172a;
        display: grid;
        gap: 2px;
        place-items: center;
        position: relative;
        z-index: 1;
    }

    .dash-donut-total {
        font-size: 26px;
        font-weight: 800;
        line-height: 1;
    }

    .dash-donut-caption {
        color: #64748b;
        font-size: 11px;
        font-weight: 700;
        text-transform: uppercase;
    }

    .dash-donut-legend {
        display: grid;
        gap: 12px;
    }

    .dash-donut-item {
        align-items: center;
        background: rgba(248, 250, 252, 0.88);
        border: 1px solid #e5e7eb;
        border-radius: 8px;
        display: grid;
        gap: 8px;
        grid-template-columns: 12px minmax(0, 1fr) auto;
        padding: 10px 12px;
    }

    .dash-donut-swatch {
        border-radius: 999px;
        height: 12px;
        width: 12px;
    }

    .dash-donut-name {
        color: #334155;
        font-size: 13px;
        font-weight: 800;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
    }

    .dash-donut-metric {
        color: #0f172a;
        font-size: 12px;
        font-weight: 800;
        text-align: right;
        white-space: nowrap;
    }

    .dash-donut-submetric {
        color: #64748b;
        display: block;
        font-size: 11px;
        font-weight: 700;
        margin-top: 2px;
    }

    .dash-empty-chart {
        align-items: center;
        color: #94a3b8;
        display: flex;
        font-size: 13px;
        height: 100%;
        justify-content: center;
        text-align: center;
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

        .dash-month-bar {
            gap: 8px;
            grid-template-columns: 62px minmax(0, 1fr) 78px;
        }

        .dash-donut-body {
            grid-template-columns: 1fr;
        }

        .dash-donut {
            max-width: 180px;
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
                    <p class="dash-panel-caption">Pendapatan, otomatis tampil sebagai jumlah transaksi jika belum ada pembayaran berhasil</p>
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
                    <p class="dash-panel-caption">Pendapatan, otomatis tampil sebagai jumlah transaksi jika belum ada pembayaran berhasil</p>
                </div>
            </div>
            <div class="dash-chart-body dash-bars-body">
                <div class="dash-month-bars" x-show="monthlyBars.length > 0">
                    <template x-for="item in monthlyBars" :key="item.label">
                        <div class="dash-month-bar">
                            <div class="dash-month-label" x-text="item.label"></div>
                            <div class="dash-month-track">
                                <div class="dash-month-fill" :style="`width: ${item.percent}%`"></div>
                            </div>
                            <div class="dash-month-value" x-text="item.display"></div>
                        </div>
                    </template>
                </div>
                <div class="dash-empty-chart" x-show="monthlyBars.length === 0">
                    Belum ada data bulanan.
                </div>
            </div>
        </div>

        <div class="dash-panel">
            <div class="dash-panel-header">
                <div>
                    <h2 class="dash-panel-title">Distribusi Lokasi</h2>
                    <p class="dash-panel-caption">Semua transaksi per lokasi</p>
                </div>
            </div>
            <div class="dash-chart-body dash-bars-body">
                <div class="dash-bar-list" x-show="locationBars.length > 0">
                    <template x-for="item in locationBars" :key="item.label">
                        <div class="dash-bar-row">
                            <div class="dash-bar-meta">
                                <span class="dash-bar-label" x-text="item.label"></span>
                                <span class="dash-bar-value" x-text="item.display"></span>
                            </div>
                            <div class="dash-bar-track">
                                <div class="dash-bar-fill dash-bar-fill-location" :style="`width: ${item.percent}%`"></div>
                            </div>
                        </div>
                    </template>
                </div>
                <div class="dash-empty-chart" x-show="locationBars.length === 0">
                    Belum ada data lokasi.
                </div>
            </div>
        </div>

        <div class="dash-panel">
            <div class="dash-panel-header">
                <div>
                    <h2 class="dash-panel-title">Jenis Kendaraan</h2>
                    <p class="dash-panel-caption">Semua transaksi per jenis kendaraan</p>
                </div>
            </div>
            <div class="dash-chart-body dash-donut-body">
                <template x-if="vehicleDonut.length > 0">
                    <div class="dash-donut" :style="`--donut-gradient: ${vehicleDonutGradient}`">
                        <div class="dash-donut-center">
                            <div class="dash-donut-total" x-text="vehicleTotal"></div>
                            <div class="dash-donut-caption">Transaksi</div>
                        </div>
                    </div>
                </template>

                <div class="dash-donut-legend" x-show="vehicleDonut.length > 0">
                    <template x-for="item in vehicleDonut" :key="item.label">
                        <div class="dash-donut-item">
                            <span class="dash-donut-swatch" :style="`background: ${item.color}`"></span>
                            <span class="dash-donut-name" x-text="item.label"></span>
                            <span class="dash-donut-metric">
                                <span x-text="item.value"></span>
                                <span class="dash-donut-submetric" x-text="item.percentLabel"></span>
                            </span>
                        </div>
                    </template>
                </div>

                <div class="dash-empty-chart" x-show="vehicleDonut.length === 0">
                    Belum ada data jenis kendaraan.
                </div>
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
window.dashboardInitialData = @json($initialDashboardData ?? null);

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
        monthlyBars: [],
        locationBars: [],
        vehicleBars: [],
        vehicleDonut: [],
        vehicleDonutGradient: '#e2e8f0',
        vehicleTotal: 0,
        refreshInterval: null,
        loading: false,
        lastUpdated: '',
        errorMessage: '',

        async init() {
            this.initCharts();

            if (window.dashboardInitialData?.success) {
                this.applyDashboardData(window.dashboardInitialData);
            } else {
                await this.refreshData();
            }

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
                this.applyDashboardData(data);
            } catch (error) {
                console.error('Error loading dashboard data:', error);
                if (showErrors) {
                    this.errorMessage = error.message || 'Dashboard belum bisa memuat data. Periksa sesi login atau koneksi API.';
                }
            }
        },

        applyDashboardData(data) {
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
            this.transactions = this.asArray(data.transactions || data.recent_transactions);
            this.lastUpdated = 'Diperbarui ' + new Date().toLocaleTimeString('id-ID', {
                hour: '2-digit',
                minute: '2-digit',
            });
            this.errorMessage = '';
            this.clampTransactionPage();
            this.updateCharts(data);
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

            this.charts.location = this.makeCartesianChart('locationChart', 'bar', {
                label: 'Jumlah transaksi',
                backgroundColor: '#7c3aed',
                borderRadius: 4,
            }, false, true);

            this.charts.vehicle = this.makeCartesianChart('vehicleChart', 'bar', {
                label: 'Jumlah transaksi',
                backgroundColor: '#d97706',
                borderRadius: 4,
            }, false);
        },

        makeCartesianChart(elementId, type, datasetOptions, isMoney, horizontal = false) {
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
                    indexAxis: horizontal ? 'y' : 'x',
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
                            beginAtZero: horizontal,
                            grid: { display: horizontal },
                            ticks: horizontal ? {
                                callback: (value) => isMoney
                                    ? 'Rp ' + Number(value).toLocaleString('id-ID')
                                    : value,
                            } : {
                                maxRotation: 0,
                                autoSkip: true,
                            },
                        },
                        y: {
                            beginAtZero: !horizontal,
                            grid: { color: '#f3f4f6' },
                            ticks: horizontal ? {
                                autoSkip: false,
                            } : {
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
                const charts = data.charts || {};
                const transactions = this.asArray(data.transactions || data.recent_transactions);
                const dailyRows = this.prepareRevenueRows(
                    this.asArray(charts.dailyRevenue || data.dailyRevenue || data.daily_revenue),
                    transactions,
                    'day'
                );
                const monthlyRows = this.prepareRevenueRows(
                    this.asArray(charts.monthlyRevenue || data.monthlyRevenue || data.monthly_revenue),
                    transactions,
                    'month'
                );
                const locationRows = this.prepareStatRows(
                    this.asArray(charts.locations || data.locationStats || data.location_stats),
                    transactions,
                    'street_section',
                    'Tidak diketahui'
                );
                const vehicleRows = this.prepareStatRows(
                    this.asArray(charts.vehicles || data.vehicleStats || data.vehicle_stats),
                    transactions,
                    'vehicle_type',
                    'Tidak diketahui'
                );

                const monthlyIsMoney = monthlyRows.some((row) => Number(row.revenue || 0) > 0 || row.type === 'money');
                const monthlyValueKey = monthlyIsMoney ? 'revenue' : 'count';
                this.monthlyBars = this.toBarItems(
                    monthlyRows,
                    (row) => row.label || row.month || '-',
                    (row) => Number(row[monthlyValueKey] ?? row.value ?? 0),
                    monthlyIsMoney ? 'money' : 'count',
                    false
                );
                this.locationBars = this.toBarItems(
                    locationRows,
                    (row) => row.street_section || row.label || 'Tidak diketahui',
                    (row) => Number(row.count ?? row.value ?? 0),
                    'count',
                    true
                );
                this.vehicleBars = this.toBarItems(
                    vehicleRows,
                    (row) => this.getVehicleLabel(row.vehicle_type || row.label),
                    (row) => Number(row.count ?? row.value ?? 0),
                    'count',
                    true
                );
                this.vehicleDonut = this.toDonutItems(vehicleRows, (row) => this.getVehicleLabel(row.vehicle_type || row.label));
                this.vehicleTotal = this.vehicleDonut.reduce((total, item) => total + item.value, 0);
                this.vehicleDonutGradient = this.buildDonutGradient(this.vehicleDonut);

                this.updateRevenueChart(this.charts.daily, dailyRows, 'label');
                this.updateRevenueChart(this.charts.monthly, monthlyRows, 'label');
                this.updateChart(this.charts.location, locationRows, 'street_section', 'count');
                this.updateChart(this.charts.vehicle, vehicleRows, 'vehicle_type', 'count');
            } catch (error) {
                console.error('Error updating dashboard charts:', error);
            }
        },

        asArray(value) {
            if (Array.isArray(value)) return value;
            if (value && typeof value === 'object') return Object.values(value);
            return [];
        },

        hasVisibleValues(rows, keys = ['revenue', 'count', 'chart_value']) {
            return this.asArray(rows).some((row) => keys.some((key) => Number(row?.[key] || 0) > 0));
        },

        prepareRevenueRows(rows, transactions, period) {
            if (this.hasVisibleValues(rows)) {
                return rows;
            }

            return this.buildRevenueRowsFromTransactions(transactions, period);
        },

        prepareStatRows(rows, transactions, key, fallbackLabel) {
            if (this.hasVisibleValues(rows, ['count'])) {
                return rows.map((row) => ({
                    ...row,
                    [key]: row[key] || row.label || fallbackLabel,
                    count: Number(row.count ?? row.value ?? 0),
                }));
            }

            return this.groupTransactionsBy(transactions, key, fallbackLabel);
        },

        buildRevenueRowsFromTransactions(transactions, period) {
            const now = new Date();
            const rows = [];
            const total = period === 'month' ? 12 : 30;

            for (let index = total - 1; index >= 0; index--) {
                const date = new Date(now);
                if (period === 'month') {
                    date.setMonth(now.getMonth() - index, 1);
                    date.setHours(0, 0, 0, 0);
                } else {
                    date.setDate(now.getDate() - index);
                    date.setHours(0, 0, 0, 0);
                }

                const key = period === 'month' ? this.formatMonthKey(date) : this.formatDateKey(date);
                rows.push({
                    date: period === 'day' ? key : undefined,
                    month: period === 'month' ? key : undefined,
                    label: period === 'month'
                        ? date.toLocaleDateString('id-ID', { month: 'short', year: 'numeric' })
                        : date.toLocaleDateString('id-ID', { day: '2-digit', month: '2-digit' }),
                    revenue: 0,
                    count: 0,
                });
            }

            const rowByKey = new Map(rows.map((row) => [period === 'month' ? row.month : row.date, row]));

            this.asArray(transactions).forEach((transaction) => {
                const transactionDate = new Date(transaction.created_at);
                if (Number.isNaN(transactionDate.getTime())) return;

                const key = period === 'month'
                    ? this.formatMonthKey(transactionDate)
                    : this.formatDateKey(transactionDate);
                const row = rowByKey.get(key);
                if (!row) return;

                row.count += 1;
                if (transaction.payment_status === 'success') {
                    row.revenue += Number(transaction.amount || 0);
                }
            });

            return rows;
        },

        groupTransactionsBy(transactions, key, fallbackLabel) {
            const counts = new Map();

            this.asArray(transactions).forEach((transaction) => {
                const label = transaction[key] || fallbackLabel;
                counts.set(label, (counts.get(label) || 0) + 1);
            });

            return Array.from(counts.entries())
                .map(([label, count]) => ({ [key]: label, count }))
                .sort((a, b) => b.count - a.count)
                .slice(0, 10);
        },

        toBarItems(rows, labelResolver, valueResolver, type, hideZeroRows) {
            const items = this.asArray(rows)
                .map((row) => {
                    const value = Number(valueResolver(row) || 0);
                    return {
                        label: String(labelResolver(row) || '-'),
                        value,
                        display: type === 'money'
                            ? 'Rp ' + this.formatCurrency(value)
                            : value + ' transaksi',
                    };
                })
                .filter((item) => !hideZeroRows || item.value > 0);

            const maxValue = Math.max(1, ...items.map((item) => item.value));

            return items.map((item) => ({
                ...item,
                percent: item.value > 0 ? Math.max(4, Math.round((item.value / maxValue) * 100)) : 0,
            }));
        },

        toDonutItems(rows, labelResolver) {
            const colors = ['#0ea5e9', '#f59e0b', '#10b981', '#8b5cf6', '#ef4444', '#14b8a6'];
            const items = this.asArray(rows)
                .map((row, index) => ({
                    label: String(labelResolver(row) || 'Tidak diketahui'),
                    value: Number(row.count ?? row.value ?? 0),
                    color: colors[index % colors.length],
                }))
                .filter((item) => item.value > 0);

            const total = items.reduce((sum, item) => sum + item.value, 0);

            return items.map((item) => ({
                ...item,
                percent: total > 0 ? (item.value / total) * 100 : 0,
                percentLabel: total > 0
                    ? new Intl.NumberFormat('id-ID', { maximumFractionDigits: 1 }).format((item.value / total) * 100) + '%'
                    : '0%',
            }));
        },

        buildDonutGradient(items) {
            if (!items.length) return '#e2e8f0 0% 100%';

            let cursor = 0;
            const segments = items.map((item, index) => {
                const start = cursor;
                const end = index === items.length - 1 ? 100 : cursor + item.percent;
                cursor = end;
                return `${item.color} ${start.toFixed(2)}% ${end.toFixed(2)}%`;
            });

            return segments.join(', ');
        },

        formatDateKey(date) {
            const year = date.getFullYear();
            const month = String(date.getMonth() + 1).padStart(2, '0');
            const day = String(date.getDate()).padStart(2, '0');
            return `${year}-${month}-${day}`;
        },

        formatMonthKey(date) {
            const year = date.getFullYear();
            const month = String(date.getMonth() + 1).padStart(2, '0');
            return `${year}-${month}`;
        },

        updateRevenueChart(chart, rows, labelKey) {
            if (!chart) return;

            const hasAnyRevenue = rows.some((row) => Number(row.revenue || 0) > 0 || row.type === 'money');
            const valueKey = hasAnyRevenue ? 'revenue' : 'count';
            const isMoney = hasAnyRevenue;

            chart.data.labels = rows.map((row) => row[labelKey] || row.date || row.month);
            chart.data.datasets[0].label = isMoney ? 'Pendapatan' : 'Jumlah transaksi';
            chart.data.datasets[0].data = rows.map((row) => Number(row[valueKey] ?? row.value ?? 0));
            chart.options.plugins.tooltip.callbacks.label = (context) => isMoney
                ? 'Rp ' + Number(context.raw || 0).toLocaleString('id-ID')
                : String(context.raw || 0) + ' transaksi';
            chart.options.scales.y.ticks.callback = (value) => isMoney
                ? 'Rp ' + Number(value).toLocaleString('id-ID')
                : value;
            chart.update('none');
        },

        updateChart(chart, rows, labelKey, valueKey) {
            if (!chart) return;

            chart.data.labels = rows.map((row) => row[labelKey] || row.label || 'Tidak diketahui');
            chart.data.datasets[0].data = rows.map((row) => Number(row[valueKey] ?? row.value ?? 0));
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

        getVehicleLabel(vehicleType) {
            const labels = {
                motorcycle: 'Motor',
                car: 'Mobil',
                truck: 'Truk',
                bus: 'Bus',
            };
            return labels[vehicleType] || vehicleType || 'Tidak diketahui';
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
