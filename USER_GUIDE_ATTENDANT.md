# Panduan Pengguna - Juru Parkir

## Daftar Isi

1. [Pendahuluan](#pendahuluan)
2. [Login](#login)
3. [Membuat QR Code](#membuat-qr-code)
4. [Menerima Notifikasi](#menerima-notifikasi)
5. [Riwayat Transaksi](#riwayat-transaksi)
6. [Troubleshooting](#troubleshooting)

## Pendahuluan

Sistem Monitoring Pembayaran Parkir memudahkan Anda sebagai juru parkir untuk:
- Membuat QR code pembayaran parkir
- Menerima notifikasi pembayaran sukses
- Melacak riwayat transaksi
- Memverifikasi pembayaran pengguna parkir

## Login

### Langkah-langkah Login

1. Buka browser dan akses: `https://parkir.dishub.go.id/attendant`
2. Masukkan nomor registrasi Anda (contoh: JP001)
3. Masukkan PIN Anda
4. Klik tombol "Login"

### Keamanan PIN

- Jangan bagikan PIN dengan orang lain
- Ubah PIN secara berkala
- Jika PIN lupa, hubungi admin Dishub
- Logout setelah selesai bekerja

### Session Timeout

- Session Anda akan otomatis logout setelah 15 menit tidak aktif
- Anda akan menerima peringatan 2 menit sebelum logout
- Klik "Perpanjang Session" untuk tetap login

## Membuat QR Code

### Langkah-langkah Membuat QR Code

1. Setelah login, Anda akan melihat halaman "Buat QR Code"
2. Pilih jenis kendaraan:
   - **Motor**: Rp 2.000
   - **Mobil**: Rp 5.000
3. Klik tombol "Buat QR Code"
4. QR code akan ditampilkan di layar

### Informasi QR Code

QR code menampilkan:
- **Nomor Registrasi Anda**: Untuk verifikasi juru parkir resmi
- **Jenis Kendaraan**: Motor atau Mobil
- **Tarif Parkir**: Jumlah yang harus dibayar
- **Lokasi Parkir**: Ruas jalan tempat Anda bekerja
- **Nomor Aduan**: Untuk keluhan pengguna parkir

### Masa Berlaku QR Code

- QR code berlaku selama **15 menit**
- Setelah 15 menit, QR code akan expired
- Jika expired, buat QR code baru
- Penghitung waktu akan ditampilkan di layar

### Cara Pengguna Parkir Membayar

1. Pengguna parkir memindai QR code dengan smartphone
2. Pengguna akan diarahkan ke halaman pembayaran
3. Pengguna memilih metode pembayaran (QRIS, GoPay, OVO, DANA, dll)
4. Pengguna menyelesaikan pembayaran
5. Anda akan menerima notifikasi pembayaran sukses

## Menerima Notifikasi

### Notifikasi Pembayaran Sukses

Ketika pembayaran berhasil:
1. Anda akan mendengar suara alert
2. Notifikasi akan muncul di layar dengan informasi:
   - Jumlah pembayaran
   - Jenis kendaraan
   - Waktu pembayaran
3. Notifikasi akan hilang otomatis setelah 10 detik

### Riwayat Notifikasi

Untuk melihat riwayat notifikasi:
1. Klik ikon "Notifikasi" di bagian atas layar
2. Daftar notifikasi hari ini akan ditampilkan
3. Klik notifikasi untuk melihat detail

### Jenis-jenis Notifikasi

1. **Pembayaran Sukses**
   - Menunjukkan pembayaran berhasil diproses
   - Pengguna parkir dapat meninggalkan area parkir

2. **Pembayaran Gagal**
   - Menunjukkan pembayaran gagal
   - Pengguna parkir perlu mencoba lagi

3. **QR Code Expired**
   - Menunjukkan QR code sudah tidak berlaku
   - Buat QR code baru untuk transaksi berikutnya

## Riwayat Transaksi

### Melihat Riwayat Transaksi

1. Klik menu "Riwayat" di sidebar
2. Daftar transaksi Anda akan ditampilkan dengan informasi:
   - Tanggal dan Waktu
   - Jenis Kendaraan
   - Jumlah Pembayaran
   - Status Pembayaran
   - Metode Pembayaran

### Filter Riwayat Transaksi

Gunakan filter untuk mencari transaksi spesifik:

1. **Rentang Tanggal**
   - Pilih tanggal mulai dan tanggal akhir
   - Klik "Terapkan Filter"

2. **Status Pembayaran**
   - Filter berdasarkan: Sukses, Gagal, Pending
   - Lihat hanya transaksi dengan status tertentu

3. **Jenis Kendaraan**
   - Filter berdasarkan: Motor, Mobil
   - Lihat transaksi untuk jenis kendaraan tertentu

### Detail Transaksi

Klik pada transaksi untuk melihat detail lengkap:
- ID Transaksi
- Tanggal dan Waktu
- Jenis Kendaraan
- Jumlah Pembayaran
- Metode Pembayaran
- Status Pembayaran
- Waktu Pembayaran

### Statistik Transaksi

Lihat ringkasan statistik Anda:
- **Total Transaksi Hari Ini**: Jumlah transaksi yang berhasil
- **Total Pendapatan Hari Ini**: Total uang yang terkumpul
- **Tingkat Keberhasilan**: Persentase transaksi sukses
- **Rata-rata Transaksi**: Rata-rata jumlah pembayaran

## Troubleshooting

### Masalah Login

**Masalah**: Tidak bisa login
- **Solusi**: 
  - Pastikan nomor registrasi dan PIN benar
  - Cek koneksi internet
  - Coba refresh halaman
  - Hubungi admin Dishub jika masalah berlanjut

**Masalah**: Akun dinonaktifkan
- **Solusi**:
  - Hubungi admin Dishub untuk mengaktifkan kembali
  - Pastikan Anda masih menjadi juru parkir resmi

### Masalah Membuat QR Code

**Masalah**: Tidak bisa membuat QR code
- **Solusi**:
  - Pastikan Anda sudah login
  - Cek koneksi internet
  - Refresh halaman dan coba lagi
  - Hubungi support jika masalah berlanjut

**Masalah**: QR code tidak terbaca
- **Solusi**:
  - Pastikan layar cukup terang
  - Bersihkan layar smartphone
  - Coba buat QR code baru
  - Gunakan aplikasi pembayaran yang kompatibel

### Masalah Notifikasi

**Masalah**: Tidak menerima notifikasi pembayaran
- **Solusi**:
  - Pastikan volume suara smartphone aktif
  - Cek koneksi internet
  - Refresh halaman
  - Logout dan login kembali
  - Hubungi support jika masalah berlanjut

**Masalah**: Notifikasi tidak muncul
- **Solusi**:
  - Pastikan browser tidak dalam mode silent
  - Cek pengaturan notifikasi browser
  - Coba browser berbeda
  - Hubungi support jika masalah berlanjut

### Masalah Performa

**Masalah**: Sistem lambat
- **Solusi**:
  - Gunakan browser terbaru (Chrome, Firefox, Safari, Edge)
  - Bersihkan cache browser
  - Tutup aplikasi lain yang tidak perlu
  - Cek koneksi internet
  - Hubungi support jika masalah berlanjut

### Masalah Pembayaran Pengguna

**Masalah**: Pengguna parkir tidak bisa membayar
- **Solusi**:
  - Pastikan QR code masih berlaku (belum expired)
  - Buat QR code baru jika sudah expired
  - Pastikan pengguna menggunakan metode pembayaran yang tersedia
  - Hubungi support jika masalah berlanjut

**Masalah**: Pembayaran gagal
- **Solusi**:
  - Minta pengguna untuk mencoba metode pembayaran lain
  - Pastikan saldo pengguna cukup
  - Coba buat QR code baru
  - Hubungi support jika masalah berlanjut

## Tips dan Trik

### Efisiensi Kerja

1. **Buat QR Code Sebelumnya**
   - Buat QR code sebelum pengguna parkir tiba
   - Siapkan beberapa QR code untuk transaksi berikutnya

2. **Verifikasi Pembayaran**
   - Tunggu notifikasi pembayaran sukses sebelum membiarkan pengguna pergi
   - Jangan andalkan pembayaran yang belum dikonfirmasi

3. **Catat Informasi Penting**
   - Catat nomor plat kendaraan untuk referensi
   - Catat waktu parkir untuk perhitungan tarif

### Keamanan

1. **Jaga Kerahasiaan PIN**
   - Jangan tulis PIN di tempat yang mudah dilihat
   - Ubah PIN secara berkala

2. **Logout Setelah Selesai**
   - Selalu logout setelah selesai bekerja
   - Jangan biarkan smartphone tanpa pengawasan saat login

3. **Laporkan Masalah**
   - Laporkan jika ada transaksi mencurigakan
   - Hubungi admin jika ada masalah keamanan

## Kontak Support

Jika mengalami masalah atau memiliki pertanyaan:

- **Email**: support@dishub.go.id
- **Telepon**: 021-1234567
- **Nomor Aduan**: 021-1234567 (untuk keluhan pengguna parkir)
- **Jam Kerja**: Senin-Jumat, 08:00-17:00 WIB

---

**Versi**: 1.0
**Terakhir Diperbarui**: 2024-01-15
