@extends('layouts.app')

@section('title', 'Tarif Parkir - Sistem Monitoring Pembayaran Parkir')

@section('content')
<div x-data="ratesPage()" @load="init()" class="space-y-6">
    <!-- Page Header -->
    <div>
        <h1 class="text-3xl font-bold text-gray-900">Tarif Parkir</h1>
        <p class="text-gray-600 mt-2">Kelola tarif parkir untuk berbagai jenis kendaraan</p>
    </div>

    <!-- Current Rates -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <!-- Motor Rate -->
        <div class="bg-white rounded-lg shadow p-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">Tarif Motor</h3>
            <div class="space-y-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Tarif Standar (Rp)</label>
                    <div class="flex items-center space-x-2">
                        <input type="number" x-model.number="form.motorcycle_rate" placeholder="0" class="flex-1 px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 outline-none" min="0" step="100">
                        <button @click="updateRate('motorcycle')" class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg">
                            Simpan
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Car Rate -->
        <div class="bg-white rounded-lg shadow p-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">Tarif Mobil</h3>
            <div class="space-y-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Tarif Standar (Rp)</label>
                    <div class="flex items-center space-x-2">
                        <input type="number" x-model.number="form.car_rate" placeholder="0" class="flex-1 px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 outline-none" min="0" step="100">
                        <button @click="updateRate('car')" class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg">
                            Simpan
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Location-Specific Rates -->
    <div class="bg-white rounded-lg shadow p-6">
        <div class="flex items-center justify-between mb-4">
            <h3 class="text-lg font-semibold text-gray-900">Tarif Spesifik Lokasi</h3>
            <button @click="showAddLocationRateModal = true" class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg text-sm font-medium">
                + Tambah Tarif Lokasi
            </button>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-gray-50 border-b border-gray-200">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-700 uppercase tracking-wider">Lokasi</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-700 uppercase tracking-wider">Jenis Kendaraan</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-700 uppercase tracking-wider">Tarif (Rp)</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-700 uppercase tracking-wider">Berlaku Sejak</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-700 uppercase tracking-wider">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    <template x-for="rate in locationRates" :key="rate.id">
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4 text-sm text-gray-900" x-text="rate.street_section"></td>
                            <td class="px-6 py-4 text-sm text-gray-900" x-text="getVehicleLabel(rate.vehicle_type)"></td>
                            <td class="px-6 py-4 text-sm font-medium text-gray-900" x-text="formatCurrency(rate.rate)"></td>
                            <td class="px-6 py-4 text-sm text-gray-600" x-text="formatDate(rate.effective_from)"></td>
                            <td class="px-6 py-4 text-sm">
                                <button @click="deleteLocationRate(rate.id)" class="text-red-600 hover:text-red-700 font-medium">
                                    Hapus
                                </button>
                            </td>
                        </tr>
                    </template>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Rate Change History -->
    <div class="bg-white rounded-lg shadow p-6">
        <h3 class="text-lg font-semibold text-gray-900 mb-4">Riwayat Perubahan Tarif</h3>
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-gray-50 border-b border-gray-200">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-700 uppercase tracking-wider">Jenis Kendaraan</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-700 uppercase tracking-wider">Lokasi</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-700 uppercase tracking-wider">Tarif Lama (Rp)</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-700 uppercase tracking-wider">Tarif Baru (Rp)</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-700 uppercase tracking-wider">Berlaku Sejak</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-700 uppercase tracking-wider">Diubah Oleh</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    <template x-for="history in rateHistory" :key="history.id">
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4 text-sm text-gray-900" x-text="getVehicleLabel(history.vehicle_type)"></td>
                            <td class="px-6 py-4 text-sm text-gray-900" x-text="history.street_section || 'Standar'"></td>
                            <td class="px-6 py-4 text-sm text-gray-600" x-text="history.old_rate ? formatCurrency(history.old_rate) : '-'"></td>
                            <td class="px-6 py-4 text-sm font-medium text-gray-900" x-text="formatCurrency(history.rate)"></td>
                            <td class="px-6 py-4 text-sm text-gray-600" x-text="formatDate(history.effective_from)"></td>
                            <td class="px-6 py-4 text-sm text-gray-600" x-text="history.creator?.name || '-'"></td>
                        </tr>
                    </template>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Add Location Rate Modal -->
    <div x-show="showAddLocationRateModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50" @click.self="showAddLocationRateModal = false;">
        <div class="bg-white rounded-lg shadow-lg max-w-md w-full mx-4">
            <div class="p-6">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-semibold text-gray-900">Tambah Tarif Lokasi</h3>
                    <button @click="showAddLocationRateModal = false;" class="text-gray-500 hover:text-gray-700">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                </div>

                <form @submit.prevent="saveLocationRate()" class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Lokasi</label>
                        <input type="text" x-model="locationRateForm.street_section" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 outline-none" required>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Jenis Kendaraan</label>
                        <select x-model="locationRateForm.vehicle_type" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 outline-none" required>
                            <option value="">Pilih Jenis Kendaraan</option>
                            <option value="motorcycle">Motor</option>
                            <option value="car">Mobil</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Tarif (Rp)</label>
                        <input type="number" x-model.number="locationRateForm.rate" placeholder="0" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 outline-none" min="0" step="100" required>
                    </div>

                    <div class="flex items-center justify-end space-x-3 pt-4 border-t">
                        <button type="button" @click="showAddLocationRateModal = false;" class="px-4 py-2 text-gray-700 border border-gray-300 rounded-lg hover:bg-gray-50">
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
function ratesPage() {
    return {
        form: {
            motorcycle_rate: 0,
            car_rate: 0,
        },
        locationRates: [],
        rateHistory: [],
        showAddLocationRateModal: false,
        locationRateForm: {
            street_section: '',
            vehicle_type: '',
            rate: 0,
        },

        async init() {
            await this.loadRates();
            await this.loadRateHistory();
        },

        async loadRates() {
            try {
                const response = await fetch('/api/rates', {
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    }
                });

                if (!response.ok) throw new Error('Failed to load rates');

                const data = await response.json();
                const rates = data.data || [];

                // Separate standard and location-specific rates
                const standardRates = rates.filter(r => !r.street_section);
                this.locationRates = rates.filter(r => r.street_section);

                // Set form values
                const motorcycleRate = standardRates.find(r => r.vehicle_type === 'motorcycle');
                const carRate = standardRates.find(r => r.vehicle_type === 'car');

                this.form.motorcycle_rate = motorcycleRate?.rate || 0;
                this.form.car_rate = carRate?.rate || 0;
            } catch (error) {
                console.error('Error loading rates:', error);
            }
        },

        async loadRateHistory() {
            try {
                // This would typically come from an audit log endpoint
                // For now, we'll load from the rates endpoint with history
                const response = await fetch('/api/rates?include=history', {
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    }
                });

                if (response.ok) {
                    const data = await response.json();
                    this.rateHistory = data.history || [];
                }
            } catch (error) {
                console.error('Error loading rate history:', error);
            }
        },

        async updateRate(vehicleType) {
            try {
                const rate = vehicleType === 'motorcycle' ? this.form.motorcycle_rate : this.form.car_rate;

                const response = await fetch('/api/rates', {
                    method: 'PUT',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    },
                    body: JSON.stringify({
                        vehicle_type: vehicleType,
                        rate: rate,
                    }),
                });

                if (!response.ok) {
                    const error = await response.json();
                    alert(error.message || 'Gagal mengubah tarif');
                    return;
                }

                alert('Tarif berhasil diperbarui');
                await this.loadRates();
                await this.loadRateHistory();
            } catch (error) {
                console.error('Error updating rate:', error);
                alert('Terjadi kesalahan saat mengubah tarif');
            }
        },

        async saveLocationRate() {
            try {
                const response = await fetch('/api/rates', {
                    method: 'PUT',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    },
                    body: JSON.stringify(this.locationRateForm),
                });

                if (!response.ok) {
                    const error = await response.json();
                    alert(error.message || 'Gagal menyimpan tarif');
                    return;
                }

                this.showAddLocationRateModal = false;
                this.locationRateForm = {
                    street_section: '',
                    vehicle_type: '',
                    rate: 0,
                };
                await this.loadRates();
                await this.loadRateHistory();
            } catch (error) {
                console.error('Error saving location rate:', error);
                alert('Terjadi kesalahan saat menyimpan tarif');
            }
        },

        async deleteLocationRate(rateId) {
            if (!confirm('Apakah Anda yakin ingin menghapus tarif ini?')) {
                return;
            }

            try {
                const response = await fetch(`/api/rates/${rateId}`, {
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    }
                });

                if (!response.ok) throw new Error('Failed to delete rate');

                await this.loadRates();
                await this.loadRateHistory();
            } catch (error) {
                console.error('Error deleting rate:', error);
                alert('Gagal menghapus tarif');
            }
        },

        getVehicleLabel(type) {
            const labels = {
                'motorcycle': 'Motor',
                'car': 'Mobil'
            };
            return labels[type] || type;
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
        }
    }
}
</script>
@endsection
