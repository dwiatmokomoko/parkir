@extends('layouts.app')

@section('title', 'Laporan - Sistem Monitoring Pembayaran Parkir')

@section('content')
<div x-data="reportsPage()" @load="init()" class="space-y-6">
    <!-- Page Header -->
    <div>
        <h1 class="text-3xl font-bold text-gray-900">Laporan</h1>
        <p class="text-gray-600 mt-2">Generate dan download laporan transaksi parkir</p>
    </div>

    <!-- Report Generation Form -->
    <div class="bg-white rounded-lg shadow p-6">
        <h3 class="text-lg font-semibold text-gray-900 mb-4">Generate Laporan Baru</h3>
        <form @submit.prevent="generateReport()" class="space-y-4">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <!-- Date Range -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Dari Tanggal</label>
                    <input type="date" x-model="reportForm.dateFrom" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 outline-none" required>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Sampai Tanggal</label>
                    <input type="date" x-model="reportForm.dateTo" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 outline-none" required>
                </div>

                <!-- Location Filter -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Lokasi (Multi-select)</label>
                    <select x-model="reportForm.locations" multiple class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 outline-none">
                        <template x-for="location in locations" :key="location">
                            <option :value="location" x-text="location"></option>
                        </template>
                    </select>
                    <p class="text-xs text-gray-500 mt-1">Kosongkan untuk semua lokasi</p>
                </div>

                <!-- Attendant Filter -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Juru Parkir (Multi-select)</label>
                    <select x-model="reportForm.attendants" multiple class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 outline-none">
                        <template x-for="attendant in attendants" :key="attendant.id">
                            <option :value="attendant.id" x-text="attendant.name"></option>
                        </template>
                    </select>
                    <p class="text-xs text-gray-500 mt-1">Kosongkan untuk semua juru parkir</p>
                </div>
            </div>

            <!-- Format Selection -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Format Laporan</label>
                <div class="flex items-center space-x-6">
                    <label class="flex items-center">
                        <input type="radio" x-model="reportForm.format" value="pdf" class="w-4 h-4 text-blue-600">
                        <span class="ml-2 text-sm text-gray-700">PDF</span>
                    </label>
                    <label class="flex items-center">
                        <input type="radio" x-model="reportForm.format" value="excel" class="w-4 h-4 text-blue-600">
                        <span class="ml-2 text-sm text-gray-700">Excel</span>
                    </label>
                </div>
            </div>

            <!-- Submit Button -->
            <div class="flex items-center justify-end pt-4 border-t">
                <button type="submit" :disabled="isGenerating" class="px-6 py-2 bg-blue-600 hover:bg-blue-700 disabled:bg-gray-400 text-white rounded-lg font-medium flex items-center space-x-2">
                    <span x-show="!isGenerating">Generate Laporan</span>
                    <span x-show="isGenerating" class="flex items-center space-x-2">
                        <svg class="animate-spin h-5 w-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        <span>Memproses...</span>
                    </span>
                </button>
            </div>
        </form>
    </div>

    <!-- Reports List -->
    <div class="bg-white rounded-lg shadow overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-200">
            <h3 class="text-lg font-semibold text-gray-900">Laporan Terbaru</h3>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-gray-50 border-b border-gray-200">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-700 uppercase tracking-wider">Tanggal Dibuat</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-700 uppercase tracking-wider">Format</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-700 uppercase tracking-wider">Status</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-700 uppercase tracking-wider">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    <template x-for="report in reports" :key="report.id">
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4 text-sm text-gray-900" x-text="formatDate(report.created_at)"></td>
                            <td class="px-6 py-4 text-sm text-gray-900" x-text="report.type.toUpperCase()"></td>
                            <td class="px-6 py-4 text-sm">
                                <span :class="getStatusBadgeClass(report.status)" x-text="getStatusLabel(report.status)"></span>
                            </td>
                            <td class="px-6 py-4 text-sm space-x-2">
                                <template x-if="report.status === 'completed'">
                                    <a :href="`/api/reports/${report.id}/download`" class="text-blue-600 hover:text-blue-700 font-medium">
                                        Download
                                    </a>
                                </template>
                                <template x-if="report.status === 'failed'">
                                    <span class="text-red-600 text-xs" x-text="report.error_message"></span>
                                </template>
                            </td>
                        </tr>
                    </template>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
function reportsPage() {
    return {
        reportForm: {
            dateFrom: '',
            dateTo: '',
            locations: [],
            attendants: [],
            format: 'pdf',
        },
        reports: [],
        locations: [],
        attendants: [],
        isGenerating: false,
        refreshInterval: null,

        async init() {
            // Set default date range (last 30 days)
            const today = new Date();
            const thirtyDaysAgo = new Date(today.getTime() - 30 * 24 * 60 * 60 * 1000);

            this.reportForm.dateTo = this.formatDateForInput(today);
            this.reportForm.dateFrom = this.formatDateForInput(thirtyDaysAgo);

            await this.loadLocations();
            await this.loadAttendants();
            await this.loadReports();

            // Auto-refresh reports every 5 seconds
            this.refreshInterval = setInterval(() => {
                this.loadReports();
            }, 5000);
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

        async loadReports() {
            try {
                const response = await fetch('/api/reports', {
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    }
                });

                if (!response.ok) throw new Error('Failed to load reports');

                const data = await response.json();
                this.reports = data.data || [];
            } catch (error) {
                console.error('Error loading reports:', error);
            }
        },

        async generateReport() {
            this.isGenerating = true;

            try {
                const response = await fetch('/api/reports/generate', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    },
                    body: JSON.stringify(this.reportForm),
                });

                if (!response.ok) {
                    const error = await response.json();
                    alert(error.message || 'Gagal membuat laporan');
                    return;
                }

                alert('Laporan sedang diproses. Silakan tunggu...');
                await this.loadReports();
            } catch (error) {
                console.error('Error generating report:', error);
                alert('Terjadi kesalahan saat membuat laporan');
            } finally {
                this.isGenerating = false;
            }
        },

        formatDateForInput(date) {
            const year = date.getFullYear();
            const month = String(date.getMonth() + 1).padStart(2, '0');
            const day = String(date.getDate()).padStart(2, '0');
            return `${year}-${month}-${day}`;
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
                'pending': 'Pending',
                'processing': 'Memproses',
                'completed': 'Selesai',
                'failed': 'Gagal'
            };
            return labels[status] || status;
        },

        getStatusBadgeClass(status) {
            const classes = {
                'pending': 'inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800',
                'processing': 'inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-blue-100 text-blue-800',
                'completed': 'inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800',
                'failed': 'inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-red-100 text-red-800'
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
