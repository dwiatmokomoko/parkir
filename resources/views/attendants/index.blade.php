@extends('layouts.app')

@section('title', 'Juru Parkir - Sistem Monitoring Pembayaran Parkir')

@section('content')
<div x-data="attendantsPage()" @load="init()" class="space-y-6">
    <!-- Page Header -->
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-3xl font-bold text-gray-900">Juru Parkir</h1>
            <p class="text-gray-600 mt-2">Kelola profil dan data juru parkir</p>
        </div>
        <button @click="showAddModal = true" class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg text-sm font-medium">
            + Tambah Juru Parkir
        </button>
    </div>

    <!-- Search and Filter -->
    <div class="bg-white rounded-lg shadow p-6">
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <input type="text" x-model="filters.search" @input="applyFilters()" placeholder="Cari nama atau nomor registrasi..." class="px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 outline-none">
            
            <select x-model="filters.location" @change="applyFilters()" class="px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 outline-none">
                <option value="">Semua Lokasi</option>
                <template x-for="location in locations" :key="location">
                    <option :value="location" x-text="location"></option>
                </template>
            </select>

            <select x-model="filters.status" @change="applyFilters()" class="px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 outline-none">
                <option value="">Semua Status</option>
                <option value="active">Aktif</option>
                <option value="inactive">Tidak Aktif</option>
            </select>
        </div>
    </div>

    <!-- Attendants Table -->
    <div class="bg-white rounded-lg shadow overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-gray-50 border-b border-gray-200">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-700 uppercase tracking-wider">Nomor Registrasi</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-700 uppercase tracking-wider">Nama</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-700 uppercase tracking-wider">Lokasi</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-700 uppercase tracking-wider">Total Transaksi</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-700 uppercase tracking-wider">Total Pendapatan</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-700 uppercase tracking-wider">Status</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-700 uppercase tracking-wider">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    <template x-for="attendant in attendants" :key="attendant.id">
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4 text-sm font-medium text-blue-600" x-text="attendant.registration_number"></td>
                            <td class="px-6 py-4 text-sm text-gray-900" x-text="attendant.name"></td>
                            <td class="px-6 py-4 text-sm text-gray-900" x-text="attendant.street_section"></td>
                            <td class="px-6 py-4 text-sm text-gray-900" x-text="attendant.transaction_count || 0"></td>
                            <td class="px-6 py-4 text-sm font-medium text-gray-900">
                                Rp <span x-text="formatCurrency(attendant.total_revenue || 0)"></span>
                            </td>
                            <td class="px-6 py-4 text-sm">
                                <span :class="attendant.is_active ? 'inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800' : 'inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-red-100 text-red-800'" x-text="attendant.is_active ? 'Aktif' : 'Tidak Aktif'"></span>
                            </td>
                            <td class="px-6 py-4 text-sm space-x-2">
                                <button @click="editAttendant(attendant)" class="text-blue-600 hover:text-blue-700 font-medium">Edit</button>
                                <button @click="toggleStatus(attendant)" :class="attendant.is_active ? 'text-red-600 hover:text-red-700' : 'text-green-600 hover:text-green-700'" x-text="attendant.is_active ? 'Nonaktifkan' : 'Aktifkan'"></button>
                            </td>
                        </tr>
                    </template>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Add/Edit Modal -->
    <div x-show="showAddModal || showEditModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50" @click.self="showAddModal = false; showEditModal = false;">
        <div class="bg-white rounded-lg shadow-lg max-w-2xl w-full mx-4 max-h-96 overflow-y-auto">
            <div class="p-6">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-semibold text-gray-900" x-text="showEditModal ? 'Edit Juru Parkir' : 'Tambah Juru Parkir'"></h3>
                    <button @click="showAddModal = false; showEditModal = false;" class="text-gray-500 hover:text-gray-700">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                </div>

                <form @submit.prevent="saveAttendant()" class="space-y-4">
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Nomor Registrasi</label>
                            <input type="text" x-model="form.registration_number" :disabled="showEditModal" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 outline-none disabled:bg-gray-100" required>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Nama</label>
                            <input type="text" x-model="form.name" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 outline-none" required>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Ruas Jalan</label>
                            <input type="text" x-model="form.street_section" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 outline-none" required>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Sisi Lokasi</label>
                            <input type="text" x-model="form.location_side" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 outline-none">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Nomor Rekening</label>
                            <input type="text" x-model="form.bank_account_number" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 outline-none" required>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Nama Bank</label>
                            <input type="text" x-model="form.bank_name" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 outline-none">
                        </div>
                        <template x-if="!showEditModal">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">PIN</label>
                                <input type="password" x-model="form.pin" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 outline-none" required>
                            </div>
                        </template>
                    </div>

                    <div class="flex items-center justify-end space-x-3 pt-4 border-t">
                        <button type="button" @click="showAddModal = false; showEditModal = false;" class="px-4 py-2 text-gray-700 border border-gray-300 rounded-lg hover:bg-gray-50">
                            Batal
                        </button>
                        <button type="submit" class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg">
                            Simpan
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
function attendantsPage() {
    return {
        attendants: [],
        locations: [],
        currentPage: 1,
        pageSize: 10,
        filters: {
            search: '',
            location: '',
            status: '',
        },
        showAddModal: false,
        showEditModal: false,
        form: {
            registration_number: '',
            name: '',
            street_section: '',
            location_side: '',
            bank_account_number: '',
            bank_name: '',
            pin: '',
        },

        async init() {
            await this.loadAttendants();
        },

        async loadAttendants() {
            try {
                const params = new URLSearchParams({
                    ...this.filters,
                });

                const response = await fetch(`/api/attendants?${params}`, {
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    }
                });

                if (!response.ok) throw new Error('Failed to load attendants');

                const data = await response.json();
                this.attendants = data.data || [];
                this.locations = [...new Set(this.attendants.map(a => a.street_section))];
            } catch (error) {
                console.error('Error loading attendants:', error);
            }
        },

        async applyFilters() {
            this.currentPage = 1;
            await this.loadAttendants();
        },

        editAttendant(attendant) {
            this.form = { ...attendant };
            this.showEditModal = true;
        },

        async saveAttendant() {
            try {
                const method = this.showEditModal ? 'PUT' : 'POST';
                const url = this.showEditModal ? `/api/attendants/${this.form.id}` : '/api/attendants';

                const response = await fetch(url, {
                    method: method,
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    },
                    body: JSON.stringify(this.form),
                });

                if (!response.ok) {
                    const error = await response.json();
                    alert(error.message || 'Gagal menyimpan data');
                    return;
                }

                this.showAddModal = false;
                this.showEditModal = false;
                this.resetForm();
                await this.loadAttendants();
            } catch (error) {
                console.error('Error saving attendant:', error);
                alert('Terjadi kesalahan saat menyimpan data');
            }
        },

        async toggleStatus(attendant) {
            if (!confirm(`Apakah Anda yakin ingin ${attendant.is_active ? 'menonaktifkan' : 'mengaktifkan'} juru parkir ini?`)) {
                return;
            }

            try {
                const url = attendant.is_active ? `/api/attendants/${attendant.id}/deactivate` : `/api/attendants/${attendant.id}/activate`;
                const response = await fetch(url, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    }
                });

                if (!response.ok) throw new Error('Failed to update status');

                await this.loadAttendants();
            } catch (error) {
                console.error('Error updating status:', error);
                alert('Gagal mengubah status');
            }
        },

        resetForm() {
            this.form = {
                registration_number: '',
                name: '',
                street_section: '',
                location_side: '',
                bank_account_number: '',
                bank_name: '',
                pin: '',
            };
        },

        formatCurrency(value) {
            return new Intl.NumberFormat('id-ID').format(value || 0);
        }
    }
}
</script>
@endsection
