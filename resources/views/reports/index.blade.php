@extends('layouts.app')

@section('title', 'Laporan - Sistem Monitoring Pembayaran Parkir')

@section('content')
<style>
    [x-cloak] { display: none !important; }

    .report-page {
        display: grid;
        gap: 24px;
    }

    .report-header {
        align-items: flex-end;
        display: flex;
        gap: 16px;
        justify-content: space-between;
    }

    .report-title {
        color: #111827;
        font-size: 30px;
        font-weight: 800;
        line-height: 1.2;
        margin: 0;
    }

    .report-subtitle {
        color: #64748b;
        margin-top: 8px;
    }

    .report-card {
        background: #ffffff;
        border: 1px solid #e5e7eb;
        border-radius: 8px;
        box-shadow: 0 1px 2px rgba(15, 23, 42, 0.05);
    }

    .report-card-header {
        border-bottom: 1px solid #e5e7eb;
        padding: 18px 22px;
    }

    .report-card-title {
        color: #0f172a;
        font-size: 18px;
        font-weight: 800;
        margin: 0;
    }

    .report-card-note {
        color: #64748b;
        font-size: 13px;
        margin-top: 4px;
    }

    .report-card-body {
        padding: 22px;
    }

    .report-grid {
        display: grid;
        gap: 18px;
        grid-template-columns: repeat(2, minmax(0, 1fr));
    }

    .report-field label,
    .report-section-label {
        color: #334155;
        display: block;
        font-size: 13px;
        font-weight: 800;
        margin-bottom: 8px;
    }

    .report-input {
        border: 1px solid #cbd5e1;
        border-radius: 7px;
        color: #0f172a;
        min-height: 42px;
        padding: 8px 11px;
        width: 100%;
    }

    .report-input:focus {
        border-color: #2563eb;
        box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.12);
        outline: none;
    }

    .report-quick-row,
    .report-format-row {
        display: flex;
        flex-wrap: wrap;
        gap: 10px;
    }

    .report-chip,
    .report-format-card {
        border: 1px solid #cbd5e1;
        border-radius: 999px;
        color: #334155;
        cursor: pointer;
        font-size: 13px;
        font-weight: 800;
        padding: 8px 12px;
    }

    .report-chip:hover,
    .report-format-card:hover {
        background: #f8fafc;
    }

    .report-format-card {
        align-items: center;
        border-radius: 8px;
        display: flex;
        gap: 10px;
        min-width: 140px;
    }

    .report-format-card.is-active {
        background: #eff6ff;
        border-color: #2563eb;
        color: #1d4ed8;
    }

    .report-check-grid {
        border: 1px solid #e2e8f0;
        border-radius: 8px;
        display: grid;
        gap: 8px;
        max-height: 168px;
        overflow: auto;
        padding: 10px;
    }

    .report-check {
        align-items: center;
        display: flex;
        gap: 9px;
        min-height: 30px;
    }

    .report-check span {
        color: #1f2937;
        font-size: 13px;
    }

    .report-muted {
        color: #94a3b8;
        font-size: 13px;
    }

    .report-alert {
        border-radius: 8px;
        font-size: 14px;
        padding: 12px 14px;
    }

    .report-alert-success {
        background: #ecfdf5;
        border: 1px solid #bbf7d0;
        color: #047857;
    }

    .report-alert-error {
        background: #fef2f2;
        border: 1px solid #fecaca;
        color: #991b1b;
    }

    .report-actions {
        align-items: center;
        border-top: 1px solid #e5e7eb;
        display: flex;
        gap: 12px;
        justify-content: flex-end;
        margin-top: 22px;
        padding-top: 18px;
    }

    .report-button {
        align-items: center;
        border: 1px solid #cbd5e1;
        border-radius: 7px;
        display: inline-flex;
        font-size: 14px;
        font-weight: 800;
        justify-content: center;
        min-height: 40px;
        padding: 9px 14px;
    }

    .report-button-primary {
        background: #2563eb;
        border-color: #2563eb;
        color: #ffffff;
    }

    .report-button-primary:disabled {
        background: #94a3b8;
        border-color: #94a3b8;
        cursor: not-allowed;
    }

    .report-table {
        border-collapse: collapse;
        min-width: 920px;
        width: 100%;
    }

    .report-table th {
        background: #f8fafc;
        border-bottom: 1px solid #e5e7eb;
        color: #64748b;
        font-size: 11px;
        font-weight: 900;
        letter-spacing: 0.04em;
        padding: 12px 16px;
        text-align: left;
        text-transform: uppercase;
    }

    .report-table td {
        border-bottom: 1px solid #f1f5f9;
        color: #0f172a;
        font-size: 13px;
        padding: 14px 16px;
        vertical-align: top;
    }

    .report-badge {
        border-radius: 999px;
        display: inline-flex;
        font-size: 12px;
        font-weight: 800;
        padding: 5px 9px;
    }

    .report-badge-pdf { background: #fef2f2; color: #b91c1c; }
    .report-badge-excel { background: #ecfdf5; color: #047857; }
    .report-badge-pending { background: #fffbeb; color: #b45309; }
    .report-badge-processing { background: #eff6ff; color: #1d4ed8; }
    .report-badge-completed { background: #ecfdf5; color: #047857; }
    .report-badge-failed { background: #fef2f2; color: #b91c1c; }

    .report-empty {
        color: #94a3b8;
        padding: 28px;
        text-align: center;
    }

    @media (max-width: 860px) {
        .report-header,
        .report-actions {
            align-items: stretch;
            flex-direction: column;
        }

        .report-grid {
            grid-template-columns: 1fr;
        }
    }
</style>

<div x-data="reportsPage()" x-init="init()" class="report-page">
    <div class="report-header">
        <div>
            <h1 class="report-title">Laporan</h1>
            <p class="report-subtitle">Buat dan unduh laporan transaksi parkir berdasarkan periode, lokasi, dan juru parkir.</p>
        </div>
        <button type="button" class="report-button" @click="loadReports()">Refresh Riwayat</button>
    </div>

    <template x-if="successMessage">
        <div class="report-alert report-alert-success" x-text="successMessage"></div>
    </template>
    <template x-if="errorMessage">
        <div class="report-alert report-alert-error" x-text="errorMessage"></div>
    </template>

    <section class="report-card">
        <div class="report-card-header">
            <h2 class="report-card-title">Generate Laporan Baru</h2>
            <p class="report-card-note">Maksimal rentang 90 hari. Kosongkan lokasi atau juru parkir untuk mengambil semua data.</p>
        </div>

        <div class="report-card-body">
            <form @submit.prevent="generateReport()">
                <div class="report-grid">
                    <div class="report-field">
                        <label>Dari Tanggal</label>
                        <input type="date" x-model="reportForm.start_date" class="report-input" required>
                    </div>

                    <div class="report-field">
                        <label>Sampai Tanggal</label>
                        <input type="date" x-model="reportForm.end_date" class="report-input" required>
                    </div>
                </div>

                <div class="mt-4">
                    <div class="report-section-label">Pilih Cepat</div>
                    <div class="report-quick-row">
                        <button type="button" class="report-chip" @click="setRange(0)">Hari ini</button>
                        <button type="button" class="report-chip" @click="setRange(6)">7 hari</button>
                        <button type="button" class="report-chip" @click="setRange(29)">30 hari</button>
                        <button type="button" class="report-chip" @click="setThisMonth()">Bulan ini</button>
                    </div>
                </div>

                <div class="report-grid mt-5">
                    <div>
                        <div class="report-section-label">Lokasi</div>
                        <div class="report-check-grid">
                            <template x-if="locations.length === 0">
                                <div class="report-muted">Belum ada lokasi.</div>
                            </template>
                            <template x-for="location in locations" :key="location">
                                <label class="report-check">
                                    <input type="checkbox" :value="location" x-model="reportForm.street_sections">
                                    <span x-text="location"></span>
                                </label>
                            </template>
                        </div>
                    </div>

                    <div>
                        <div class="report-section-label">Juru Parkir</div>
                        <div class="report-check-grid">
                            <template x-if="attendants.length === 0">
                                <div class="report-muted">Belum ada juru parkir.</div>
                            </template>
                            <template x-for="attendant in attendants" :key="attendant.id">
                                <label class="report-check">
                                    <input type="checkbox" :value="attendant.id" x-model.number="reportForm.parking_attendant_ids">
                                    <span><strong x-text="attendant.registration_number"></strong> - <span x-text="attendant.name"></span></span>
                                </label>
                            </template>
                        </div>
                    </div>
                </div>

                <div class="mt-5">
                    <div class="report-section-label">Format Laporan</div>
                    <div class="report-format-row">
                        <label :class="reportForm.type === 'pdf' ? 'report-format-card is-active' : 'report-format-card'">
                            <input type="radio" x-model="reportForm.type" value="pdf">
                            <span>PDF</span>
                        </label>
                        <label :class="reportForm.type === 'excel' ? 'report-format-card is-active' : 'report-format-card'">
                            <input type="radio" x-model="reportForm.type" value="excel">
                            <span>Excel</span>
                        </label>
                    </div>
                </div>

                <div class="report-actions">
                    <button type="button" class="report-button" @click="resetFilters()">Reset Filter</button>
                    <button type="submit" :disabled="isGenerating" class="report-button report-button-primary">
                        <span x-text="isGenerating ? 'Membuat laporan...' : 'Generate Laporan'"></span>
                    </button>
                </div>
            </form>
        </div>
    </section>

    <section class="report-card">
        <div class="report-card-header">
            <h2 class="report-card-title">Laporan Terbaru</h2>
            <p class="report-card-note">Daftar 25 laporan terakhir yang dibuat oleh admin ini.</p>
        </div>

        <div class="overflow-x-auto">
            <table class="report-table">
                <thead>
                    <tr>
                        <th>Tanggal Dibuat</th>
                        <th>Periode</th>
                        <th>Filter</th>
                        <th>Format</th>
                        <th>Status</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <template x-for="report in reports" :key="report.id">
                        <tr>
                            <td x-text="formatDateTime(report.created_at)"></td>
                            <td x-text="formatPeriod(report.filters)"></td>
                            <td x-text="formatFilters(report.filters)"></td>
                            <td>
                                <span :class="report.type === 'pdf' ? 'report-badge report-badge-pdf' : 'report-badge report-badge-excel'" x-text="report.type.toUpperCase()"></span>
                            </td>
                            <td>
                                <span :class="getStatusBadgeClass(report.status)" x-text="getStatusLabel(report.status)"></span>
                                <template x-if="report.error_message">
                                    <div class="text-xs text-red-600 mt-1" x-text="report.error_message"></div>
                                </template>
                            </td>
                            <td>
                                <template x-if="report.status === 'completed'">
                                    <a :href="`/api/reports/${report.id}/download`" class="text-blue-600 hover:text-blue-700 font-bold">Download</a>
                                </template>
                                <template x-if="report.status !== 'completed'">
                                    <span class="text-gray-400">-</span>
                                </template>
                            </td>
                        </tr>
                    </template>
                    <template x-if="reports.length === 0">
                        <tr>
                            <td colspan="6" class="report-empty">Belum ada laporan. Generate laporan pertama dari form di atas.</td>
                        </tr>
                    </template>
                </tbody>
            </table>
        </div>
    </section>
</div>

<script>
function reportsPage() {
    return {
        reportForm: {
            start_date: '',
            end_date: '',
            street_sections: [],
            parking_attendant_ids: [],
            type: 'pdf',
        },
        reports: [],
        locations: [],
        attendants: [],
        isGenerating: false,
        successMessage: '',
        errorMessage: '',

        async init() {
            this.setRange(29);
            await Promise.all([this.loadAttendants(), this.loadReports()]);
        },

        async loadAttendants() {
            try {
                const response = await fetch('/api/attendants', {
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

                const data = await response.json();
                this.attendants = data.data || [];
                this.locations = data.locations || [...new Set(this.attendants.map((item) => item.street_section).filter(Boolean))];
            } catch (error) {
                this.errorMessage = 'Gagal memuat data juru parkir.';
                console.error('Error loading attendants:', error);
            }
        },

        async loadReports() {
            try {
                const response = await fetch('/api/reports', {
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

                if (!response.ok) throw new Error('Failed to load reports');

                const data = await response.json();
                this.reports = data.data || [];
            } catch (error) {
                this.errorMessage = 'Gagal memuat riwayat laporan.';
                console.error('Error loading reports:', error);
            }
        },

        async generateReport() {
            this.isGenerating = true;
            this.successMessage = '';
            this.errorMessage = '';

            try {
                const response = await fetch('/api/reports/generate', {
                    method: 'POST',
                    credentials: 'same-origin',
                    headers: {
                        'Accept': 'application/json',
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    },
                    body: JSON.stringify(this.reportForm),
                });

                const data = await response.json();

                if (!response.ok || !data.success) {
                    this.errorMessage = data.message || 'Gagal membuat laporan.';
                    return;
                }

                this.successMessage = 'Laporan berhasil dibuat dan siap diunduh.';
                await this.loadReports();
            } catch (error) {
                this.errorMessage = 'Terjadi kesalahan saat membuat laporan.';
                console.error('Error generating report:', error);
            } finally {
                this.isGenerating = false;
            }
        },

        setRange(daysAgo) {
            const end = new Date();
            const start = new Date();
            start.setDate(end.getDate() - daysAgo);
            this.reportForm.start_date = this.formatDateForInput(start);
            this.reportForm.end_date = this.formatDateForInput(end);
        },

        setThisMonth() {
            const now = new Date();
            this.reportForm.start_date = this.formatDateForInput(new Date(now.getFullYear(), now.getMonth(), 1));
            this.reportForm.end_date = this.formatDateForInput(now);
        },

        resetFilters() {
            this.setRange(29);
            this.reportForm.street_sections = [];
            this.reportForm.parking_attendant_ids = [];
            this.reportForm.type = 'pdf';
            this.successMessage = '';
            this.errorMessage = '';
        },

        formatDateForInput(date) {
            const year = date.getFullYear();
            const month = String(date.getMonth() + 1).padStart(2, '0');
            const day = String(date.getDate()).padStart(2, '0');
            return `${year}-${month}-${day}`;
        },

        formatDateTime(dateString) {
            if (!dateString) return '-';

            return new Date(dateString).toLocaleString('id-ID', {
                day: '2-digit',
                month: '2-digit',
                year: 'numeric',
                hour: '2-digit',
                minute: '2-digit',
            });
        },

        formatPeriod(filters) {
            if (!filters?.start_date || !filters?.end_date) return '-';
            return `${filters.start_date} s/d ${filters.end_date}`;
        },

        formatFilters(filters) {
            const parts = [];
            if (filters?.street_sections?.length) parts.push(`${filters.street_sections.length} lokasi`);
            if (filters?.parking_attendant_ids?.length) parts.push(`${filters.parking_attendant_ids.length} juru parkir`);
            return parts.length ? parts.join(', ') : 'Semua data';
        },

        getStatusLabel(status) {
            const labels = {
                pending: 'Pending',
                processing: 'Memproses',
                completed: 'Selesai',
                failed: 'Gagal',
            };
            return labels[status] || status || '-';
        },

        getStatusBadgeClass(status) {
            const classes = {
                pending: 'report-badge report-badge-pending',
                processing: 'report-badge report-badge-processing',
                completed: 'report-badge report-badge-completed',
                failed: 'report-badge report-badge-failed',
            };
            return classes[status] || classes.pending;
        },
    }
}
</script>
@endsection
