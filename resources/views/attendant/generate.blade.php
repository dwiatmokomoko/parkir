@extends('layouts.attendant')

@section('title', 'Generate QR Code - Sistem Monitoring Pembayaran Parkir')

@section('content')
<div x-data="qrGenerator()" @load="init()" class="min-h-screen bg-gradient-to-br from-blue-50 to-indigo-100 py-8">
    <div class="max-w-2xl mx-auto px-4">
        <!-- Header -->
        <div class="text-center mb-8">
            <div class="text-5xl font-bold text-blue-600 mb-2">🅓</div>
            <h1 class="text-3xl font-bold text-gray-900">DISHUB</h1>
            <p class="text-gray-600 mt-2">Sistem Pembayaran Parkir Non-Tunai</p>
        </div>

        <!-- Main Card -->
        <div class="bg-white rounded-lg shadow-lg p-8 mb-6">
            <!-- Vehicle Type Selection -->
            <div class="mb-8">
                <h2 class="text-xl font-semibold text-gray-900 mb-4">Pilih Jenis Kendaraan</h2>
                <div class="grid grid-cols-2 gap-4">
                    <button
                        @click="selectVehicleType('motorcycle')"
                        :class="form.vehicle_type === 'motorcycle' ? 'ring-2 ring-blue-500 bg-blue-50' : 'border-2 border-gray-300 hover:border-blue-300'"
                        class="p-6 rounded-lg transition text-center"
                    >
                        <div class="text-4xl mb-2">🏍️</div>
                        <p class="font-semibold text-gray-900">Motor</p>
                        <p class="text-sm text-gray-600 mt-2">Rp <span x-text="formatCurrency(rates.motorcycle)"></span></p>
                    </button>

                    <button
                        @click="selectVehicleType('car')"
                        :class="form.vehicle_type === 'car' ? 'ring-2 ring-blue-500 bg-blue-50' : 'border-2 border-gray-300 hover:border-blue-300'"
                        class="p-6 rounded-lg transition text-center"
                    >
                        <div class="text-4xl mb-2">🚗</div>
                        <p class="font-semibold text-gray-900">Mobil</p>
                        <p class="text-sm text-gray-600 mt-2">Rp <span x-text="formatCurrency(rates.car)"></span></p>
                    </button>
                </div>
            </div>

            <!-- Generate Button -->
            <div class="mb-8">
                <button
                    @click="generateQR()"
                    :disabled="!form.vehicle_type || isGenerating"
                    class="w-full bg-blue-600 hover:bg-blue-700 disabled:bg-gray-400 text-white font-semibold py-3 px-4 rounded-lg transition duration-200 flex items-center justify-center space-x-2"
                >
                    <span x-show="!isGenerating">Generate QR Code</span>
                    <span x-show="isGenerating" class="flex items-center space-x-2">
                        <svg class="animate-spin h-5 w-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        <span>Memproses...</span>
                    </span>
                </button>
            </div>

            <!-- QR Code Display -->
            <template x-if="qrCode">
                <div class="border-t pt-8">
                    <div class="bg-gray-50 rounded-lg p-8 text-center mb-6">
                        <!-- QR Code Image -->
                        <img :src="qrCode" alt="QR Code" class="w-64 h-64 mx-auto mb-4">

                        <!-- Attendant Info -->
                        <div class="bg-white rounded-lg p-4 mb-4 text-left">
                            <p class="text-sm text-gray-600">Nomor Registrasi</p>
                            <p class="text-lg font-semibold text-gray-900" x-text="attendantInfo.registration_number"></p>
                            
                            <p class="text-sm text-gray-600 mt-3">Nama Juru Parkir</p>
                            <p class="text-lg font-semibold text-gray-900" x-text="attendantInfo.name"></p>

                            <p class="text-sm text-gray-600 mt-3">Lokasi</p>
                            <p class="text-lg font-semibold text-gray-900" x-text="attendantInfo.street_section"></p>
                        </div>

                        <!-- Expiration Timer -->
                        <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4 mb-4">
                            <p class="text-sm text-yellow-800">QR Code berlaku selama:</p>
                            <p class="text-3xl font-bold text-yellow-600" x-text="`${expirationMinutes}:${String(expirationSeconds).padStart(2, '0')}`"></p>
                        </div>

                        <!-- Regenerate Button -->
                        <template x-if="isExpired">
                            <button
                                @click="generateQR()"
                                class="w-full bg-orange-600 hover:bg-orange-700 text-white font-semibold py-2 px-4 rounded-lg transition duration-200"
                            >
                                Generate QR Code Baru
                            </button>
                        </template>
                    </div>

                    <!-- Complaint Hotline -->
                    <div class="bg-red-50 border border-red-200 rounded-lg p-6 mb-6">
                        <p class="text-sm text-red-800 mb-2">Nomor Aduan Pelanggan</p>
                        <p class="text-3xl font-bold text-red-600">1500 135</p>
                        <p class="text-sm text-red-700 mt-2">Hubungi nomor ini jika ada keluhan atau pertanyaan</p>
                    </div>

                    <!-- Dishub Logo -->
                    <div class="text-center">
                        <p class="text-sm text-gray-600">Resmi Dinas Perhubungan</p>
                        <p class="text-2xl font-bold text-blue-600 mt-2">🅓 DISHUB</p>
                    </div>
                </div>
            </template>
        </div>

        <!-- Logout Button -->
        <div class="text-center">
            <form method="POST" action="{{ route('attendant.logout') }}" class="inline">
                @csrf
                <button type="submit" class="text-gray-600 hover:text-gray-900 font-medium">
                    Logout
                </button>
            </form>
        </div>
    </div>

    <!-- Notification Panel -->
    <div x-show="showNotification" class="fixed bottom-4 right-4 bg-green-50 border border-green-200 rounded-lg p-4 shadow-lg max-w-sm" @click="showNotification = false">
        <div class="flex items-start space-x-3">
            <div class="flex-shrink-0">
                <svg class="h-5 w-5 text-green-400" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                </svg>
            </div>
            <div>
                <h3 class="text-sm font-medium text-green-800">Pembayaran Berhasil</h3>
                <p class="text-sm text-green-700 mt-1" x-text="notificationMessage"></p>
            </div>
        </div>
    </div>

    <!-- Notification History -->
    <template x-if="notifications.length > 0">
        <div class="max-w-2xl mx-auto px-4 mt-8">
            <div class="bg-white rounded-lg shadow-lg p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Riwayat Notifikasi Hari Ini</h3>
                <div class="space-y-3">
                    <template x-for="notification in notifications" :key="notification.id">
                        <div class="flex items-start space-x-3 p-3 bg-gray-50 rounded-lg">
                            <div class="flex-shrink-0">
                                <svg class="h-5 w-5 text-green-500" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                                </svg>
                            </div>
                            <div class="flex-1">
                                <p class="text-sm font-medium text-gray-900" x-text="notification.title"></p>
                                <p class="text-sm text-gray-600" x-text="notification.message"></p>
                                <p class="text-xs text-gray-500 mt-1" x-text="formatTime(notification.created_at)"></p>
                            </div>
                        </div>
                    </template>
                </div>
            </div>
        </div>
    </template>
</div>

<script>
function qrGenerator() {
    return {
        form: {
            vehicle_type: '',
        },
        rates: {
            motorcycle: 0,
            car: 0,
        },
        qrCode: null,
        attendantInfo: {},
        isGenerating: false,
        isExpired: false,
        expirationMinutes: 15,
        expirationSeconds: 0,
        expirationTimer: null,
        notifications: [],
        showNotification: false,
        notificationMessage: '',
        notificationPollInterval: null,

        async init() {
            await this.loadRates();
            await this.loadAttendantInfo();
            await this.loadNotifications();

            // Poll for notifications every 5 seconds
            this.notificationPollInterval = setInterval(() => {
                this.loadNotifications();
            }, 5000);
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

                const motorcycleRate = rates.find(r => r.vehicle_type === 'motorcycle' && !r.street_section);
                const carRate = rates.find(r => r.vehicle_type === 'car' && !r.street_section);

                this.rates.motorcycle = motorcycleRate?.rate || 0;
                this.rates.car = carRate?.rate || 0;
            } catch (error) {
                console.error('Error loading rates:', error);
            }
        },

        async loadAttendantInfo() {
            try {
                const response = await fetch('/api/attendant/profile', {
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    }
                });

                if (response.ok) {
                    const data = await response.json();
                    this.attendantInfo = data.data || {};
                }
            } catch (error) {
                console.error('Error loading attendant info:', error);
            }
        },

        async loadNotifications() {
            try {
                const response = await fetch('/api/attendant/notifications', {
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    }
                });

                if (response.ok) {
                    const data = await response.json();
                    const newNotifications = data.data || [];

                    // Check for new notifications
                    const previousCount = this.notifications.length;
                    this.notifications = newNotifications;

                    if (newNotifications.length > previousCount) {
                        const latestNotification = newNotifications[0];
                        this.showNotificationAlert(latestNotification);
                        this.playAudioAlert();
                    }
                }
            } catch (error) {
                console.error('Error loading notifications:', error);
            }
        },

        selectVehicleType(type) {
            this.form.vehicle_type = type;
        },

        async generateQR() {
            if (!this.form.vehicle_type) {
                alert('Silakan pilih jenis kendaraan terlebih dahulu');
                return;
            }

            this.isGenerating = true;
            this.isExpired = false;

            try {
                const response = await fetch('/api/payments/generate-qr', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    },
                    body: JSON.stringify(this.form),
                });

                if (!response.ok) {
                    const error = await response.json();
                    alert(error.message || 'Gagal membuat QR code');
                    return;
                }

                const data = await response.json();
                this.qrCode = data.qr_code;

                // Start expiration timer
                this.startExpirationTimer();
            } catch (error) {
                console.error('Error generating QR:', error);
                alert('Terjadi kesalahan saat membuat QR code');
            } finally {
                this.isGenerating = false;
            }
        },

        startExpirationTimer() {
            this.expirationMinutes = 15;
            this.expirationSeconds = 0;

            if (this.expirationTimer) {
                clearInterval(this.expirationTimer);
            }

            this.expirationTimer = setInterval(() => {
                if (this.expirationSeconds > 0) {
                    this.expirationSeconds--;
                } else if (this.expirationMinutes > 0) {
                    this.expirationMinutes--;
                    this.expirationSeconds = 59;
                } else {
                    this.isExpired = true;
                    clearInterval(this.expirationTimer);
                }
            }, 1000);
        },

        showNotificationAlert(notification) {
            this.notificationMessage = notification.message;
            this.showNotification = true;

            setTimeout(() => {
                this.showNotification = false;
            }, 10000);
        },

        playAudioAlert() {
            // Create a simple beep sound
            const audioContext = new (window.AudioContext || window.webkitAudioContext)();
            const oscillator = audioContext.createOscillator();
            const gainNode = audioContext.createGain();

            oscillator.connect(gainNode);
            gainNode.connect(audioContext.destination);

            oscillator.frequency.value = 800;
            oscillator.type = 'sine';

            gainNode.gain.setValueAtTime(0.3, audioContext.currentTime);
            gainNode.gain.exponentialRampToValueAtTime(0.01, audioContext.currentTime + 0.5);

            oscillator.start(audioContext.currentTime);
            oscillator.stop(audioContext.currentTime + 0.5);
        },

        formatCurrency(value) {
            return new Intl.NumberFormat('id-ID').format(value || 0);
        },

        formatTime(dateString) {
            const date = new Date(dateString);
            return date.toLocaleTimeString('id-ID', {
                hour: '2-digit',
                minute: '2-digit'
            });
        },

        destroy() {
            if (this.expirationTimer) {
                clearInterval(this.expirationTimer);
            }
            if (this.notificationPollInterval) {
                clearInterval(this.notificationPollInterval);
            }
        }
    }
}
</script>
@endsection
