@extends('layouts.app')

@section('title', 'Transaksi - Sistem Monitoring Pembayaran Parkir')

@section('content')
<div x-data="transactionsPage()" @load="init()" class="space-y-6">
    <!-- Page Header -->
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-3xl font-bold text-gray-900">Transaksi</h1>
            <p class="text-gray-600 mt-2">Kelola dan pantau semua transaksi pembayaran parkir</p>
        </div>
        <div class="flex items-center space-x-3">
            <button @click="exportPDF()" class="px-4 py-2 bg-red-600 hover:bg-red-700 text-white rounded-lg text-sm font-medium">
                Export PDF
            </button>
            <button @click="exportExcel()" class="px-4 py-2 bg-green-600 hover:bg-green-700 text-white rounded-lg text-sm font-medium">
                Export Excel
            </button>
        </div>
    </div>

    <!-- Filters -->
    <div class="bg-white rounded-lg shadow p-6">
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-4">
            <!-- Date Range -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Dari Tanggal</label>
                <input type="date" x-model="filters.dateFrom" @change="applyFilters()" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 outline-none">
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Sampai Tanggal</label>
                <input type="date" x-model="filters.dateTo" @change="applyFilters()" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 outline-none">
            </div>

            <!-- Location Filter -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Lokasi</label>
                <select x-model="filters.location" @change="applyFilters()" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 outline-none">
                    <option value="">Semua Lokasi</option>
                    <template x-for="location in locations" :key="location">
                        <option :value="location" x-text="location"></option>
                    </template>
                </select>
            </div>

            <!-- Attendant Filter -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Juru Parkir</label>
                <select x-model="filters.attendant" @change="applyFilters()" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 outline-none">
                    <option value="">Semua Juru Parkir</option>
                    <template x-for="attendant in attendants" :key="attendant.id">
                        <option :value="attendant.id" x-text="attendant.name"></option>
                    </template>
                </select>
            </div>

            <!-- Status Filter -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Status</label>
                <select x-model="filters.status" @change="applyFilters()" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 outline-none">
                    <option value="">Semua Status</option>
                    <option value="success">Berhasil</option>
                    <option value="pending">Pending</option>
                    <option value="failed">Gagal</option>
                </select>
            </div>
        </div>

        <!-- Search -->
        <div class="mt-4">
            <input type="text" x-model="filters.search" @input="applyFilters()" placeholder="Cari ID transaksi..." class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 outline-none">
        </div>
    </div>

    <!-- Transactions Table -->
    <div class="bg-white rounded-lg shadow overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-gray-50 border-b border-gray-200">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-700 uppercase tracking-wider">ID Transaksi</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-700 uppercase tracking-wider">Juru Parkir</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-700 uppercase tracking-wider">Lokasi</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-700 uppercase tracking-wider">Jenis Kendaraan</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-700 uppercase tracking-wider">Jumlah</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-700 uppercase tracking-wider">Status</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-700 uppercase tracking-wider">Waktu</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-700 uppercase tracking-wider">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    <template x-for="transaction in transactions" :key="transaction.id">
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4 text-sm font-medium text-blue-600" x-text="transaction.transaction_id"></td>
                            <td class="px-6 py-4 text-sm text-gray-900" x-text="transaction.parking_attendant?.name || '-'"></td>
                            <td class="px-6 py-4 text-sm text-gray-900" x-text="transaction.street_section"></td>
                            <td class="px-6 py-4 text-sm text-gray-900" x-text="getVehicleLabel(transaction.vehicle_type)"></td>
                            <td class="px-6 py-4 text-sm font-medium text-gray-900">
                                Rp <span x-text="formatCurrency(transaction.amount)"></span>
                            </td>
                            <td class="px-6 py-4 text-sm">
                                <span :class="getStatusBadgeClass(transaction.payment_status)" x-text="getStatusLabel(transaction.payment_status)"></span>
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-600" x-text="formatDate(transaction.created_at)"></td>
                            <td class="px-6 py-4 text-sm">
                                <button @click="viewDetails(transaction)" class="text-blue-600 hover:text-blue-700 font-medium">
                                    Detail
                                </button>
                            </td>
                        </tr>
                    </template>
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        <div class="bg-white px-6 py-4 border-t border-gray-200 flex items-center justify-between">
            <div class="text-sm text-gray-600">
                Menampilkan <span x-text="(currentPage - 1) * pageSize + 1"></span> hingga <span x-text="Math.min(currentPage * pageSize, totalTransactions)"></span> dari <span x-text="totalTransactions"></span> transaksi
            </div>
            <div class="flex items-center space-x-2">
                <button @click="previousPage()" :disabled="currentPage === 1" class="px-3 py-1 border border-gray-300 rounded-lg text-sm disabled:opacity-50">
                    Sebelumnya
                </button>
                <span class="text-sm text-gray-600">
                    Halaman <span x-text="currentPage"></span> dari <span x-text="totalPages"></span>
                </span>
                <button @click="nextPage()" :disabled="currentPage === totalPages" class="px-3 py-1 border border-gray-300 rounded-lg text-sm disabled:opacity-50">
                    Berikutnya
                </button>
            </div>
        </div>
    </div>

    <!-- Details Modal -->
    <div x-show="showDetailsModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50" @click.self="showDetailsModal = false">
        <div class="bg-white rounded-lg shadow-lg max-w-2xl w-full mx-4 max-h-96 overflow-y-auto">
            <div class="p-6">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-semibold text-gray-900">Detail Transaksi</h3>
                    <button @click="showDetailsModal = false" class="text-gray-500 hover:text-gray-700">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                </div>

                <div class="space-y-4">
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <p class="text-sm text-gray-600">ID Transaksi</p>
                            <p class="text-lg font-semibold text-gray-900" x-text="selectedTransaction?.transaction_id"></p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-600">Status</p>
                            <p :class="getStatusBadgeClass(selectedTransaction?.payment_status)" x-text="getStatusLabel(selectedTransaction?.payment_status)"></p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-600">Juru Parkir</p>
                            <p class="text-lg font-semibold text-gray-900" x-text="selectedTransaction?.parking_attendant?.name"></p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-600">Lokasi</p>
                            <p class="text-lg font-semibold text-gray-900" x-text="selectedTransaction?.street_section"></p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-600">Jenis Kendaraan</p>
                            <p class="text-lg font-semibold text-gray-900" x-text="getVehicleLabel(selectedTransaction?.vehicle_type)"></p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-600">Jumlah</p>
                            <p class="text-lg font-semibold text-gray-900">
                                Rp <span x-text="formatCurrency(selectedTransaction?.amount)"></span>
                            </p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-600">Metode Pembayaran</p>
                            <p class="text-lg font-semibold text-gray-900" x-text="selectedTransaction?.payment_method || '-'"></p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-600">Waktu Dibuat</p>
                            <p class="text-lg font-semibold text-gray-900" x-text="formatDate(selectedTransaction?.created_at)"></p>
                        </div>
                        <template x-if="selectedTransaction?.paid_at">
                            <div>
                                <p class="text-sm text-gray-600">Waktu Pembayaran</p>
                                <p class="text-lg font-semibold text-gray-900" x-text="formatDate(selectedTransaction?.paid_at)"></p>
                            </div>
                        </template>
                        <template x-if="selectedTransaction?.failure_reason">
                            <div class="col-span-2">
                                <p class="text-sm text-gray-600">Alasan Kegagalan</p>
                                <p class="text-lg font-semibold text-red-600" x-text="selectedTransaction?.failure_reason"></p>
                            </div>
                        </template>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function transactionsPage() {
    return {
        transactions: [],
        locations: [],
        attendants: [],
        currentPage: 1,
        pageSize: 10,
        totalTransactions: 0,
        totalPages: 1,
        filters: {
            dateFrom: '',
            dateTo: '',
            location: '',
            attendant: '',
            status: '',
            search: '',
        },
        showDetailsModal: false,
        selectedTransaction: null,

        async init() {
            await this.loadLocations();
            await this.loadAttendants();
            await this.loadTransactions();
        },

        async loadLocations() {
            try {
                const response = await fetch('/api/transactions?limit=1000', {
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    }
                });
                const data = await response.json();
                this.locations = [...new Set(data.data.map(t => t.street_section))];
            } catch (error) {
                console.error('Error loading locations:', error);
            }
        },

        async loadAttendants() {
            try {
                const response = await fetch('/api/attendants', {
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    }
                });
                const data = await response.json();
                this.attendants = data.data || [];
            } catch (error) {
                console.error('Error loading attendants:', error);
            }
        },

        async loadTransactions() {
            try {
                const params = new URLSearchParams({
                    page: this.currentPage,
                    limit: this.pageSize,
                    ...this.filters,
                });

                const response = await fetch(`/api/transactions?${params}`, {
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    }
                });

                if (!response.ok) throw new Error('Failed to load transactions');

                const data = await response.json();
                this.transactions = data.data || [];
                this.totalTransactions = data.total || 0;
                this.totalPages = Math.ceil(this.totalTransactions / this.pageSize);
            } catch (error) {
                console.error('Error loading transactions:', error);
            }
        },

        async applyFilters() {
            this.currentPage = 1;
            await this.loadTransactions();
        },

        async nextPage() {
            if (this.currentPage < this.totalPages) {
                this.currentPage++;
                await this.loadTransactions();
            }
        },

        async previousPage() {
            if (this.currentPage > 1) {
                this.currentPage--;
                await this.loadTransactions();
            }
        },

        viewDetails(transaction) {
            this.selectedTransaction = transaction;
            this.showDetailsModal = true;
        },

        async exportPDF() {
            const params = new URLSearchParams(this.filters);
            window.location.href = `/api/reports/generate?format=pdf&${params}`;
        },

        async exportExcel() {
            const params = new URLSearchParams(this.filters);
            window.location.href = `/api/reports/generate?format=excel&${params}`;
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

        getVehicleLabel(type) {
            const labels = {
                'motorcycle': 'Motor',
                'car': 'Mobil'
            };
            return labels[type] || type;
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
        }
    }
}
</script>
@endsection
