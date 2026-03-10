@extends('layouts.app')

@section('title', 'Audit Log - Sistem Monitoring Pembayaran Parkir')

@section('content')
<div x-data="auditLogsPage()" @load="init()" class="space-y-6">
    <!-- Page Header -->
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-3xl font-bold text-gray-900">Audit Log</h1>
            <p class="text-gray-600 mt-2">Pantau semua aktivitas sistem dan perubahan data</p>
        </div>
        <button @click="exportLogs()" class="px-4 py-2 bg-green-600 hover:bg-green-700 text-white rounded-lg text-sm font-medium">
            Export
        </button>
    </div>

    <!-- Filters -->
    <div class="bg-white rounded-lg shadow p-6">
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
            <!-- Date Range -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Dari Tanggal</label>
                <input type="date" x-model="filters.dateFrom" @change="applyFilters()" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 outline-none">
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Sampai Tanggal</label>
                <input type="date" x-model="filters.dateTo" @change="applyFilters()" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 outline-none">
            </div>

            <!-- User Filter -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Pengguna</label>
                <select x-model="filters.user" @change="applyFilters()" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 outline-none">
                    <option value="">Semua Pengguna</option>
                    <template x-for="user in users" :key="user.id">
                        <option :value="user.id" x-text="user.name"></option>
                    </template>
                </select>
            </div>

            <!-- Action Type Filter -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Jenis Aksi</label>
                <select x-model="filters.action" @change="applyFilters()" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 outline-none">
                    <option value="">Semua Aksi</option>
                    <option value="create">Buat</option>
                    <option value="update">Ubah</option>
                    <option value="delete">Hapus</option>
                    <option value="login">Login</option>
                    <option value="logout">Logout</option>
                </select>
            </div>
        </div>

        <!-- Search -->
        <div class="mt-4">
            <input type="text" x-model="filters.search" @input="applyFilters()" placeholder="Cari entitas atau deskripsi..." class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 outline-none">
        </div>
    </div>

    <!-- Audit Logs Table -->
    <div class="bg-white rounded-lg shadow overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-gray-50 border-b border-gray-200">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-700 uppercase tracking-wider">Waktu</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-700 uppercase tracking-wider">Pengguna</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-700 uppercase tracking-wider">Aksi</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-700 uppercase tracking-wider">Entitas</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-700 uppercase tracking-wider">IP Address</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-700 uppercase tracking-wider">Detail</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    <template x-for="log in logs" :key="log.id">
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4 text-sm text-gray-900" x-text="formatDate(log.created_at)"></td>
                            <td class="px-6 py-4 text-sm text-gray-900" x-text="log.user?.name || 'System'"></td>
                            <td class="px-6 py-4 text-sm">
                                <span :class="getActionBadgeClass(log.action)" x-text="getActionLabel(log.action)"></span>
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-900">
                                <span x-text="log.entity_type"></span>
                                <span class="text-gray-600" x-text="`#${log.entity_id}`"></span>
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-600" x-text="log.ip_address"></td>
                            <td class="px-6 py-4 text-sm">
                                <button @click="viewDetails(log)" class="text-blue-600 hover:text-blue-700 font-medium">
                                    Lihat
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
                Menampilkan <span x-text="(currentPage - 1) * pageSize + 1"></span> hingga <span x-text="Math.min(currentPage * pageSize, totalLogs)"></span> dari <span x-text="totalLogs"></span> log
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
                    <h3 class="text-lg font-semibold text-gray-900">Detail Audit Log</h3>
                    <button @click="showDetailsModal = false" class="text-gray-500 hover:text-gray-700">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                </div>

                <div class="space-y-4">
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <p class="text-sm text-gray-600">Waktu</p>
                            <p class="text-lg font-semibold text-gray-900" x-text="formatDate(selectedLog?.created_at)"></p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-600">Pengguna</p>
                            <p class="text-lg font-semibold text-gray-900" x-text="selectedLog?.user?.name || 'System'"></p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-600">Aksi</p>
                            <p :class="getActionBadgeClass(selectedLog?.action)" x-text="getActionLabel(selectedLog?.action)"></p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-600">Entitas</p>
                            <p class="text-lg font-semibold text-gray-900">
                                <span x-text="selectedLog?.entity_type"></span>
                                <span class="text-gray-600" x-text="`#${selectedLog?.entity_id}`"></span>
                            </p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-600">IP Address</p>
                            <p class="text-lg font-semibold text-gray-900" x-text="selectedLog?.ip_address"></p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-600">User Agent</p>
                            <p class="text-sm text-gray-900 break-words" x-text="selectedLog?.user_agent || '-'"></p>
                        </div>
                    </div>

                    <!-- Old Values -->
                    <template x-if="selectedLog?.old_values && Object.keys(selectedLog.old_values).length > 0">
                        <div class="border-t pt-4">
                            <p class="text-sm font-medium text-gray-700 mb-2">Nilai Lama</p>
                            <div class="bg-red-50 p-3 rounded-lg">
                                <pre class="text-xs text-gray-900 overflow-auto" x-text="JSON.stringify(selectedLog.old_values, null, 2)"></pre>
                            </div>
                        </div>
                    </template>

                    <!-- New Values -->
                    <template x-if="selectedLog?.new_values && Object.keys(selectedLog.new_values).length > 0">
                        <div class="border-t pt-4">
                            <p class="text-sm font-medium text-gray-700 mb-2">Nilai Baru</p>
                            <div class="bg-green-50 p-3 rounded-lg">
                                <pre class="text-xs text-gray-900 overflow-auto" x-text="JSON.stringify(selectedLog.new_values, null, 2)"></pre>
                            </div>
                        </div>
                    </template>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function auditLogsPage() {
    return {
        logs: [],
        users: [],
        currentPage: 1,
        pageSize: 10,
        totalLogs: 0,
        totalPages: 1,
        filters: {
            dateFrom: '',
            dateTo: '',
            user: '',
            action: '',
            search: '',
        },
        showDetailsModal: false,
        selectedLog: null,

        async init() {
            await this.loadUsers();
            await this.loadLogs();
        },

        async loadUsers() {
            try {
                const response = await fetch('/api/users', {
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    }
                });
                if (response.ok) {
                    const data = await response.json();
                    this.users = data.data || [];
                }
            } catch (error) {
                console.error('Error loading users:', error);
            }
        },

        async loadLogs() {
            try {
                const params = new URLSearchParams({
                    page: this.currentPage,
                    limit: this.pageSize,
                    ...this.filters,
                });

                const response = await fetch(`/api/audit-logs?${params}`, {
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    }
                });

                if (!response.ok) throw new Error('Failed to load logs');

                const data = await response.json();
                this.logs = data.data || [];
                this.totalLogs = data.total || 0;
                this.totalPages = Math.ceil(this.totalLogs / this.pageSize);
            } catch (error) {
                console.error('Error loading logs:', error);
            }
        },

        async applyFilters() {
            this.currentPage = 1;
            await this.loadLogs();
        },

        async nextPage() {
            if (this.currentPage < this.totalPages) {
                this.currentPage++;
                await this.loadLogs();
            }
        },

        async previousPage() {
            if (this.currentPage > 1) {
                this.currentPage--;
                await this.loadLogs();
            }
        },

        viewDetails(log) {
            this.selectedLog = log;
            this.showDetailsModal = true;
        },

        async exportLogs() {
            const params = new URLSearchParams(this.filters);
            window.location.href = `/api/audit-logs/export?${params}`;
        },

        formatDate(dateString) {
            const date = new Date(dateString);
            return date.toLocaleString('id-ID', {
                year: 'numeric',
                month: '2-digit',
                day: '2-digit',
                hour: '2-digit',
                minute: '2-digit',
                second: '2-digit'
            });
        },

        getActionLabel(action) {
            const labels = {
                'create': 'Buat',
                'update': 'Ubah',
                'delete': 'Hapus',
                'login': 'Login',
                'logout': 'Logout',
                'activate': 'Aktifkan',
                'deactivate': 'Nonaktifkan'
            };
            return labels[action] || action;
        },

        getActionBadgeClass(action) {
            const classes = {
                'create': 'inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800',
                'update': 'inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-blue-100 text-blue-800',
                'delete': 'inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-red-100 text-red-800',
                'login': 'inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-purple-100 text-purple-800',
                'logout': 'inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-gray-100 text-gray-800',
                'activate': 'inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800',
                'deactivate': 'inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800'
            };
            return classes[action] || classes['update'];
        }
    }
}
</script>
@endsection
