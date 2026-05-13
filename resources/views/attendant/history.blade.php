@extends('layouts.attendant')

@section('title', 'Riwayat Transaksi - Sistem Monitoring Pembayaran Parkir')

@section('content')
<div x-data="attendantHistory()" x-init="init()" class="space-y-6">
    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h1 class="text-2xl sm:text-3xl font-bold text-gray-900">Riwayat Transaksi</h1>
            <p class="text-gray-600 mt-1">Pantau transaksi parkir yang dibuat dari akun juru parkir ini.</p>
        </div>
        <a href="{{ route('attendant.generate') }}" class="inline-flex items-center justify-center rounded-lg bg-blue-600 px-4 py-2 text-sm font-semibold text-white hover:bg-blue-700">
            Generate QR
        </a>
    </div>

    <div class="grid grid-cols-2 lg:grid-cols-5 gap-3">
        <div class="bg-white rounded-lg border border-gray-200 p-4">
            <p class="text-xs font-medium text-gray-500">Total</p>
            <p class="mt-2 text-2xl font-bold text-gray-900" x-text="summary.total || 0"></p>
        </div>
        <div class="bg-white rounded-lg border border-green-100 p-4">
            <p class="text-xs font-medium text-green-700">Berhasil</p>
            <p class="mt-2 text-2xl font-bold text-green-700" x-text="summary.success || 0"></p>
        </div>
        <div class="bg-white rounded-lg border border-yellow-100 p-4">
            <p class="text-xs font-medium text-yellow-700">Pending</p>
            <p class="mt-2 text-2xl font-bold text-yellow-700" x-text="summary.pending || 0"></p>
        </div>
        <div class="bg-white rounded-lg border border-red-100 p-4">
            <p class="text-xs font-medium text-red-700">Gagal</p>
            <p class="mt-2 text-2xl font-bold text-red-700" x-text="(summary.failed || 0) + (summary.expired || 0)"></p>
        </div>
        <div class="bg-white rounded-lg border border-blue-100 p-4 col-span-2 lg:col-span-1">
            <p class="text-xs font-medium text-blue-700">Pendapatan</p>
            <p class="mt-2 text-2xl font-bold text-blue-700" x-text="formatCurrency(summary.revenue || 0)"></p>
        </div>
    </div>

    <div class="bg-white rounded-lg border border-gray-200 p-4">
        <div class="grid grid-cols-1 md:grid-cols-5 gap-3">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Status</label>
                <select x-model="filters.status" @change="applyFilters()" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-blue-500 focus:ring-blue-500">
                    <option value="all">Semua status</option>
                    <option value="success">Berhasil</option>
                    <option value="pending">Pending</option>
                    <option value="failed">Gagal</option>
                    <option value="expired">Expired</option>
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Kendaraan</label>
                <select x-model="filters.vehicle_type" @change="applyFilters()" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-blue-500 focus:ring-blue-500">
                    <option value="all">Semua kendaraan</option>
                    <option value="motorcycle">Motor</option>
                    <option value="car">Mobil</option>
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Dari</label>
                <input type="date" x-model="filters.date_from" @change="applyFilters()" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-blue-500 focus:ring-blue-500">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Sampai</label>
                <input type="date" x-model="filters.date_to" @change="applyFilters()" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-blue-500 focus:ring-blue-500">
            </div>
            <div class="flex items-end gap-2">
                <button type="button" @click="resetFilters()" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm font-semibold text-gray-700 hover:bg-gray-50">
                    Reset
                </button>
                <button type="button" @click="loadTransactions()" class="w-full rounded-lg bg-gray-900 px-3 py-2 text-sm font-semibold text-white hover:bg-gray-800">
                    Refresh
                </button>
            </div>
        </div>
    </div>

    <div class="bg-white rounded-lg border border-gray-200 overflow-hidden">
        <div class="px-4 py-4 border-b border-gray-200 flex items-center justify-between">
            <div>
                <h2 class="font-semibold text-gray-900">Daftar Transaksi</h2>
                <p class="text-sm text-gray-500">Data terbaru ditampilkan paling atas.</p>
            </div>
            <div x-show="isLoading" class="text-sm text-blue-600">Memuat...</div>
        </div>

        <div class="hidden md:block overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-600">ID</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-600">Kendaraan</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-600">Jumlah</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-600">Status</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-600">Waktu</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    <template x-for="transaction in transactions" :key="transaction.id">
                        <tr class="hover:bg-gray-50">
                            <td class="px-4 py-4 text-sm font-medium text-gray-900" x-text="shortId(transaction.transaction_id)"></td>
                            <td class="px-4 py-4 text-sm text-gray-700" x-text="transaction.vehicle_label"></td>
                            <td class="px-4 py-4 text-sm font-semibold text-gray-900" x-text="formatCurrency(transaction.amount)"></td>
                            <td class="px-4 py-4 text-sm">
                                <span :class="statusClass(transaction.payment_status)" x-text="statusLabel(transaction.payment_status)"></span>
                            </td>
                            <td class="px-4 py-4 text-sm text-gray-600" x-text="formatDate(transaction.created_at)"></td>
                        </tr>
                    </template>
                </tbody>
            </table>
        </div>

        <div class="md:hidden divide-y divide-gray-100">
            <template x-for="transaction in transactions" :key="transaction.id">
                <div class="p-4 space-y-2">
                    <div class="flex items-start justify-between gap-3">
                        <p class="text-sm font-semibold text-gray-900 break-all" x-text="shortId(transaction.transaction_id)"></p>
                        <span :class="statusClass(transaction.payment_status)" x-text="statusLabel(transaction.payment_status)"></span>
                    </div>
                    <div class="flex items-center justify-between text-sm">
                        <span class="text-gray-600" x-text="transaction.vehicle_label"></span>
                        <span class="font-semibold text-gray-900" x-text="formatCurrency(transaction.amount)"></span>
                    </div>
                    <p class="text-xs text-gray-500" x-text="formatDate(transaction.created_at)"></p>
                </div>
            </template>
        </div>

        <div x-show="!isLoading && transactions.length === 0" class="p-10 text-center">
            <p class="font-semibold text-gray-900">Belum ada transaksi</p>
            <p class="mt-1 text-sm text-gray-500">Transaksi akan muncul setelah QR dibuat.</p>
        </div>

        <div class="px-4 py-4 border-t border-gray-200 flex flex-col sm:flex-row gap-3 sm:items-center sm:justify-between">
            <p class="text-sm text-gray-600">
                Menampilkan <span x-text="pagination.from || 0"></span>-<span x-text="pagination.to || 0"></span> dari <span x-text="pagination.total || 0"></span>
            </p>
            <div class="flex items-center gap-2">
                <button type="button" @click="previousPage()" :disabled="pagination.current_page <= 1" class="rounded-lg border border-gray-300 px-3 py-2 text-sm font-medium text-gray-700 disabled:opacity-50">
                    Sebelumnya
                </button>
                <span class="text-sm text-gray-600">
                    <span x-text="pagination.current_page || 1"></span>/<span x-text="pagination.last_page || 1"></span>
                </span>
                <button type="button" @click="nextPage()" :disabled="pagination.current_page >= pagination.last_page" class="rounded-lg border border-gray-300 px-3 py-2 text-sm font-medium text-gray-700 disabled:opacity-50">
                    Berikutnya
                </button>
            </div>
        </div>
    </div>
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

        shortId(value) {
            if (!value) return '-';
            return value.length > 22 ? value.slice(0, 22) + '...' : value;
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
            const base = 'inline-flex items-center rounded-full px-3 py-1 text-xs font-semibold';
            const classes = {
                success: ' bg-green-100 text-green-800',
                pending: ' bg-yellow-100 text-yellow-800',
                failed: ' bg-red-100 text-red-800',
                expired: ' bg-gray-100 text-gray-700',
            };
            return base + (classes[status] || ' bg-gray-100 text-gray-700');
        },
    };
}
</script>
@endsection
