@extends('layouts.attendant')

@section('title', 'Riwayat Transaksi - Sistem Monitoring Pembayaran Parkir')

@section('content')
<style>
    [x-cloak] {
        display: none !important;
    }

    .attendant-history {
        --history-blue: #2563eb;
        --history-blue-dark: #1d4ed8;
        --history-border: #e5e7eb;
        --history-muted: #64748b;
        --history-text: #0f172a;
        --history-soft: #f8fafc;
        display: grid;
        gap: 20px;
    }

    .history-hero,
    .history-panel,
    .history-table-panel {
        background: #ffffff;
        border: 1px solid var(--history-border);
        border-radius: 12px;
        box-shadow: 0 10px 24px rgba(15, 23, 42, 0.06);
    }

    .history-hero {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 18px;
        padding: 22px;
    }

    .history-kicker {
        display: inline-flex;
        margin-bottom: 8px;
        color: var(--history-blue);
        font-size: 12px;
        font-weight: 700;
        letter-spacing: 0.08em;
        text-transform: uppercase;
    }

    .history-hero h1,
    .history-panel h2,
    .history-table-title h2 {
        margin: 0;
        color: var(--history-text);
        font-weight: 800;
        letter-spacing: 0;
    }

    .history-hero h1 {
        font-size: 28px;
        line-height: 1.2;
    }

    .history-hero p,
    .history-panel p,
    .history-table-title p {
        margin: 6px 0 0;
        color: var(--history-muted);
        font-size: 14px;
        line-height: 1.5;
    }

    .history-primary-action,
    .history-secondary-action,
    .history-refresh-action {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        min-height: 42px;
        border-radius: 10px;
        border: 1px solid transparent;
        padding: 10px 16px;
        font-size: 14px;
        font-weight: 700;
        text-decoration: none;
        white-space: nowrap;
        cursor: pointer;
        transition: background-color 160ms ease, border-color 160ms ease, color 160ms ease, transform 160ms ease;
    }

    .history-primary-action {
        background: var(--history-blue);
        color: #ffffff;
    }

    .history-primary-action:hover,
    .history-refresh-action:hover {
        background: var(--history-blue-dark);
        transform: translateY(-1px);
    }

    .history-secondary-action {
        background: #ffffff;
        border-color: #cbd5e1;
        color: #334155;
    }

    .history-secondary-action:hover {
        background: #f1f5f9;
        border-color: #94a3b8;
    }

    .history-refresh-action {
        background: var(--history-blue);
        color: #ffffff;
    }

    .history-summary-grid {
        display: grid;
        grid-template-columns: repeat(5, minmax(0, 1fr));
        gap: 14px;
    }

    .history-summary-card {
        min-height: 118px;
        padding: 18px;
        border: 1px solid var(--history-border);
        border-radius: 12px;
        background: #ffffff;
        box-shadow: 0 8px 18px rgba(15, 23, 42, 0.05);
    }

    .history-summary-card p {
        margin: 0;
        color: var(--history-muted);
        font-size: 12px;
        font-weight: 700;
        letter-spacing: 0.03em;
        text-transform: uppercase;
    }

    .history-summary-card strong {
        display: block;
        margin-top: 14px;
        color: var(--history-text);
        font-size: 26px;
        line-height: 1;
    }

    .history-summary-card small {
        display: block;
        margin-top: 10px;
        color: #94a3b8;
        font-size: 12px;
    }

    .history-summary-card.is-success {
        background: linear-gradient(135deg, #f0fdf4 0%, #ffffff 72%);
        border-color: #bbf7d0;
    }

    .history-summary-card.is-success strong,
    .history-summary-card.is-success p {
        color: #047857;
    }

    .history-summary-card.is-pending {
        background: linear-gradient(135deg, #fffbeb 0%, #ffffff 72%);
        border-color: #fde68a;
    }

    .history-summary-card.is-pending strong,
    .history-summary-card.is-pending p {
        color: #b45309;
    }

    .history-summary-card.is-danger {
        background: linear-gradient(135deg, #fef2f2 0%, #ffffff 72%);
        border-color: #fecaca;
    }

    .history-summary-card.is-danger strong,
    .history-summary-card.is-danger p {
        color: #b91c1c;
    }

    .history-summary-card.is-revenue {
        background: linear-gradient(135deg, #eff6ff 0%, #ffffff 72%);
        border-color: #bfdbfe;
    }

    .history-summary-card.is-revenue strong,
    .history-summary-card.is-revenue p {
        color: var(--history-blue-dark);
    }

    .history-panel {
        padding: 20px;
    }

    .history-panel-heading {
        display: flex;
        align-items: flex-start;
        justify-content: space-between;
        gap: 12px;
        margin-bottom: 16px;
    }

    .history-panel h2,
    .history-table-title h2 {
        font-size: 18px;
    }

    .history-filter-grid {
        display: grid;
        grid-template-columns: repeat(4, minmax(0, 1fr)) auto;
        gap: 12px;
        align-items: end;
    }

    .history-field label {
        display: block;
        margin-bottom: 6px;
        color: #334155;
        font-size: 13px;
        font-weight: 700;
    }

    .history-field select,
    .history-field input {
        width: 100%;
        min-height: 42px;
        border: 1px solid #cbd5e1;
        border-radius: 10px;
        background: #ffffff;
        color: var(--history-text);
        font-size: 14px;
        padding: 9px 12px;
        outline: none;
    }

    .history-field select:focus,
    .history-field input:focus {
        border-color: var(--history-blue);
        box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.14);
    }

    .history-filter-actions {
        display: flex;
        gap: 8px;
    }

    .history-table-panel {
        overflow: hidden;
    }

    .history-table-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 14px;
        padding: 18px 20px;
        border-bottom: 1px solid var(--history-border);
    }

    .history-loading {
        display: inline-flex;
        align-items: center;
        border-radius: 999px;
        background: #eff6ff;
        color: var(--history-blue-dark);
        font-size: 12px;
        font-weight: 700;
        padding: 6px 10px;
    }

    .history-table-wrap {
        overflow-x: auto;
    }

    .history-table {
        width: 100%;
        border-collapse: collapse;
    }

    .history-table th {
        background: #f8fafc;
        border-bottom: 1px solid var(--history-border);
        color: #475569;
        font-size: 12px;
        font-weight: 800;
        letter-spacing: 0.05em;
        padding: 13px 16px;
        text-align: left;
        text-transform: uppercase;
        white-space: nowrap;
    }

    .history-table td {
        border-bottom: 1px solid #eef2f7;
        color: #1e293b;
        font-size: 14px;
        padding: 16px;
        vertical-align: middle;
    }

    .history-table tr:hover td {
        background: #f8fafc;
    }

    .history-id {
        color: #0f172a;
        font-weight: 800;
    }

    .history-money {
        color: #0f172a;
        font-weight: 800;
        white-space: nowrap;
    }

    .status-pill {
        display: inline-flex;
        align-items: center;
        border-radius: 999px;
        font-size: 12px;
        font-weight: 800;
        padding: 6px 10px;
        white-space: nowrap;
    }

    .status-pill--success {
        background: #dcfce7;
        color: #047857;
    }

    .status-pill--pending {
        background: #fef3c7;
        color: #b45309;
    }

    .status-pill--failed {
        background: #fee2e2;
        color: #b91c1c;
    }

    .status-pill--expired,
    .status-pill--default {
        background: #f1f5f9;
        color: #475569;
    }

    .history-mobile-list {
        display: none;
    }

    .history-empty {
        padding: 48px 20px;
        text-align: center;
    }

    .history-empty strong {
        display: block;
        color: var(--history-text);
        font-size: 16px;
    }

    .history-empty span {
        display: block;
        margin-top: 6px;
        color: var(--history-muted);
        font-size: 14px;
    }

    .history-pagination {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
        padding: 16px 20px;
        border-top: 1px solid var(--history-border);
    }

    .history-pagination p {
        margin: 0;
        color: var(--history-muted);
        font-size: 14px;
    }

    .history-pagination-controls {
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .history-page-button {
        min-height: 38px;
        border: 1px solid #cbd5e1;
        border-radius: 9px;
        background: #ffffff;
        color: #334155;
        font-size: 13px;
        font-weight: 700;
        padding: 8px 12px;
        cursor: pointer;
    }

    .history-page-button:hover:not(:disabled) {
        background: #f8fafc;
        border-color: #94a3b8;
    }

    .history-page-button:disabled {
        cursor: not-allowed;
        opacity: 0.48;
    }

    .history-page-count {
        color: #475569;
        font-size: 13px;
        font-weight: 700;
        min-width: 44px;
        text-align: center;
    }

    @media (max-width: 1100px) {
        .history-summary-grid {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }

        .history-summary-card.is-revenue {
            grid-column: span 2;
        }

        .history-filter-grid {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }

        .history-filter-actions {
            grid-column: span 2;
        }
    }

    @media (max-width: 720px) {
        .history-hero,
        .history-panel-heading,
        .history-table-header,
        .history-pagination {
            align-items: stretch;
            flex-direction: column;
        }

        .history-hero h1 {
            font-size: 24px;
        }

        .history-primary-action,
        .history-secondary-action,
        .history-refresh-action {
            width: 100%;
        }

        .history-summary-grid,
        .history-filter-grid {
            grid-template-columns: 1fr;
        }

        .history-summary-card.is-revenue,
        .history-filter-actions {
            grid-column: auto;
        }

        .history-filter-actions {
            flex-direction: column;
        }

        .history-table-wrap {
            display: none;
        }

        .history-mobile-list {
            display: block;
        }

        .history-mobile-item {
            display: grid;
            gap: 10px;
            padding: 16px;
            border-bottom: 1px solid #eef2f7;
        }

        .history-mobile-row {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
        }
    }
</style>

<div x-data="attendantHistory()" x-init="init()" class="attendant-history">
    <section class="history-hero">
        <div>
            <span class="history-kicker">Juru Parkir</span>
            <h1>Riwayat Transaksi</h1>
            <p>Pantau transaksi parkir, status pembayaran, dan pendapatan dari akun ini.</p>
        </div>
        <a href="{{ route('attendant.generate') }}" class="history-primary-action">Generate QR Baru</a>
    </section>

    <section class="history-summary-grid" aria-label="Ringkasan transaksi">
        <article class="history-summary-card">
            <p>Total</p>
            <strong x-text="summary.total || 0"></strong>
            <small>Seluruh transaksi</small>
        </article>
        <article class="history-summary-card is-success">
            <p>Berhasil</p>
            <strong x-text="summary.success || 0"></strong>
            <small>Sudah dibayar</small>
        </article>
        <article class="history-summary-card is-pending">
            <p>Pending</p>
            <strong x-text="summary.pending || 0"></strong>
            <small>Menunggu bayar</small>
        </article>
        <article class="history-summary-card is-danger">
            <p>Gagal</p>
            <strong x-text="(summary.failed || 0) + (summary.expired || 0)"></strong>
            <small>Gagal atau expired</small>
        </article>
        <article class="history-summary-card is-revenue">
            <p>Pendapatan</p>
            <strong x-text="formatCurrency(summary.revenue || 0)"></strong>
            <small>Dari transaksi berhasil</small>
        </article>
    </section>

    <section class="history-panel">
        <div class="history-panel-heading">
            <div>
                <h2>Filter Transaksi</h2>
                <p>Gunakan filter untuk mencari transaksi berdasarkan status, kendaraan, atau tanggal.</p>
            </div>
            <div x-show="lastLoadedAt" x-cloak class="history-loading">
                Diperbarui <span x-text="formatTimeOnly(lastLoadedAt)" style="margin-left: 4px;"></span>
            </div>
        </div>

        <div class="history-filter-grid">
            <div class="history-field">
                <label for="history-status">Status</label>
                <select id="history-status" x-model="filters.status" @change="applyFilters()">
                    <option value="all">Semua status</option>
                    <option value="success">Berhasil</option>
                    <option value="pending">Pending</option>
                    <option value="failed">Gagal</option>
                    <option value="expired">Expired</option>
                </select>
            </div>
            <div class="history-field">
                <label for="history-vehicle">Kendaraan</label>
                <select id="history-vehicle" x-model="filters.vehicle_type" @change="applyFilters()">
                    <option value="all">Semua kendaraan</option>
                    <option value="motorcycle">Motor</option>
                    <option value="car">Mobil</option>
                </select>
            </div>
            <div class="history-field">
                <label for="history-date-from">Dari</label>
                <input id="history-date-from" type="date" x-model="filters.date_from" @change="applyFilters()">
            </div>
            <div class="history-field">
                <label for="history-date-to">Sampai</label>
                <input id="history-date-to" type="date" x-model="filters.date_to" @change="applyFilters()">
            </div>
            <div class="history-filter-actions">
                <button type="button" @click="resetFilters()" class="history-secondary-action">Reset</button>
                <button type="button" @click="loadTransactions()" class="history-refresh-action">Refresh</button>
            </div>
        </div>
    </section>

    <section class="history-table-panel">
        <div class="history-table-header">
            <div class="history-table-title">
                <h2>Daftar Transaksi</h2>
                <p>Transaksi terbaru ditampilkan paling atas.</p>
            </div>
            <div x-show="isLoading" x-cloak class="history-loading">Memuat data...</div>
        </div>

        <div class="history-table-wrap">
            <table class="history-table">
                <thead>
                    <tr>
                        <th>ID Transaksi</th>
                        <th>Kendaraan</th>
                        <th>Jumlah</th>
                        <th>Status</th>
                        <th>Waktu</th>
                    </tr>
                </thead>
                <tbody>
                    <template x-for="transaction in transactions" :key="transaction.id">
                        <tr>
                            <td class="history-id" x-text="shortId(transaction.transaction_id)"></td>
                            <td x-text="transaction.vehicle_label"></td>
                            <td class="history-money" x-text="formatCurrency(transaction.amount)"></td>
                            <td>
                                <span :class="statusClass(transaction.payment_status)" x-text="statusLabel(transaction.payment_status)"></span>
                            </td>
                            <td x-text="formatDate(transaction.created_at)"></td>
                        </tr>
                    </template>
                </tbody>
            </table>
        </div>

        <div class="history-mobile-list">
            <template x-for="transaction in transactions" :key="transaction.id">
                <article class="history-mobile-item">
                    <div class="history-mobile-row">
                        <strong class="history-id" x-text="shortId(transaction.transaction_id)"></strong>
                        <span :class="statusClass(transaction.payment_status)" x-text="statusLabel(transaction.payment_status)"></span>
                    </div>
                    <div class="history-mobile-row">
                        <span x-text="transaction.vehicle_label"></span>
                        <span class="history-money" x-text="formatCurrency(transaction.amount)"></span>
                    </div>
                    <div style="color: var(--history-muted); font-size: 13px;" x-text="formatDate(transaction.created_at)"></div>
                </article>
            </template>
        </div>

        <div x-show="!isLoading && transactions.length === 0" x-cloak class="history-empty">
            <strong>Belum ada transaksi</strong>
            <span>Transaksi akan muncul setelah QR berhasil dibuat.</span>
        </div>

        <div class="history-pagination">
            <p>
                Menampilkan <span x-text="pagination.from || 0"></span>-<span x-text="pagination.to || 0"></span>
                dari <span x-text="pagination.total || 0"></span> transaksi
            </p>
            <div class="history-pagination-controls">
                <button type="button" @click="previousPage()" :disabled="pagination.current_page <= 1" class="history-page-button">
                    Sebelumnya
                </button>
                <span class="history-page-count">
                    <span x-text="pagination.current_page || 1"></span>/<span x-text="pagination.last_page || 1"></span>
                </span>
                <button type="button" @click="nextPage()" :disabled="pagination.current_page >= pagination.last_page" class="history-page-button">
                    Berikutnya
                </button>
            </div>
        </div>
    </section>
</div>

<script>
function attendantHistory() {
    return {
        transactions: [],
        summary: {},
        pagination: {
            current_page: 1,
            last_page: 1,
            total: 0,
            from: 0,
            to: 0,
        },
        filters: {
            status: 'all',
            vehicle_type: 'all',
            date_from: '',
            date_to: '',
        },
        isLoading: false,
        lastLoadedAt: '',

        async init() {
            await this.loadTransactions();
        },

        async loadTransactions() {
            this.isLoading = true;

            try {
                const params = new URLSearchParams({
                    page: this.pagination.current_page || 1,
                    per_page: 10,
                    status: this.filters.status,
                    vehicle_type: this.filters.vehicle_type,
                    date_from: this.filters.date_from,
                    date_to: this.filters.date_to,
                });

                const response = await fetch(`/api/attendant/transactions?${params}`, {
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    }
                });

                if (!response.ok) throw new Error('Gagal memuat transaksi');

                const data = await response.json();
                this.transactions = data.data || [];
                this.summary = data.summary || {};
                this.pagination = data.pagination || this.pagination;
                this.lastLoadedAt = new Date().toISOString();
            } catch (error) {
                console.error(error);
                this.transactions = [];
            } finally {
                this.isLoading = false;
            }
        },

        applyFilters() {
            this.pagination.current_page = 1;
            this.loadTransactions();
        },

        resetFilters() {
            this.filters = {
                status: 'all',
                vehicle_type: 'all',
                date_from: '',
                date_to: '',
            };
            this.applyFilters();
        },

        previousPage() {
            if (this.pagination.current_page <= 1) return;
            this.pagination.current_page--;
            this.loadTransactions();
        },

        nextPage() {
            if (this.pagination.current_page >= this.pagination.last_page) return;
            this.pagination.current_page++;
            this.loadTransactions();
        },

        formatCurrency(value) {
            return 'Rp ' + new Intl.NumberFormat('id-ID').format(value || 0);
        },

        formatDate(value) {
            if (!value) return '-';
            return new Date(value).toLocaleString('id-ID', {
                day: '2-digit',
                month: '2-digit',
                year: 'numeric',
                hour: '2-digit',
                minute: '2-digit',
            });
        },

        formatTimeOnly(value) {
            if (!value) return '-';
            return new Date(value).toLocaleTimeString('id-ID', {
                hour: '2-digit',
                minute: '2-digit',
            });
        },

        shortId(value) {
            if (!value) return '-';
            return value.length > 26 ? value.slice(0, 26) + '...' : value;
        },

        statusLabel(status) {
            const labels = {
                success: 'Berhasil',
                pending: 'Pending',
                failed: 'Gagal',
                expired: 'Expired',
            };
            return labels[status] || status || '-';
        },

        statusClass(status) {
            const classes = {
                success: 'status-pill status-pill--success',
                pending: 'status-pill status-pill--pending',
                failed: 'status-pill status-pill--failed',
                expired: 'status-pill status-pill--expired',
            };
            return classes[status] || 'status-pill status-pill--default';
        },
    };
}
</script>
@endsection
