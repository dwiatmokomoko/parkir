# Panduan Pengguna - Admin Dishub

## Daftar Isi

1. [Pendahuluan](#pendahuluan)
2. [Login](#login)
3. [Dashboard](#dashboard)
4. [Manajemen Transaksi](#manajemen-transaksi)
5. [Manajemen Juru Parkir](#manajemen-juru-parkir)
6. [Konfigurasi Tarif](#konfigurasi-tarif)
7. [Laporan](#laporan)
8. [Audit Log](#audit-log)
9. [Troubleshooting](#troubleshooting)

## Pendahuluan

Sistem Monitoring Pembayaran Parkir adalah platform yang dirancang untuk membantu Admin Dishub dalam:
- Memantau transaksi pembayaran parkir secara real-time
- Mengelola data juru parkir
- Mengatur tarif parkir
- Menghasilkan laporan transaksi
- Melacak aktivitas sistem melalui audit log

## Login

### Langkah-langkah Login

1. Buka browser dan akses: `https://parkir.dishub.go.id`
2. Masukkan email admin Anda
3. Masukkan password
4. Klik tombol "Login"

### Keamanan Password

- Gunakan password yang kuat (minimal 8 karakter)
- Jangan bagikan password dengan orang lain
- Ubah password secara berkala (minimal setiap 3 bulan)
- Logout setelah selesai menggunakan sistem

### Session Timeout

- Session admin akan otomatis logout setelah 30 menit tidak aktif
- Anda akan menerima peringatan 5 menit sebelum logout
- Klik "Perpanjang Session" untuk tetap login

## Dashboard

### Ringkasan Dashboard

Dashboard menampilkan informasi penting tentang transaksi parkir:

#### Kartu Ringkasan
- **Pendapatan Hari Ini**: Total pendapatan dari transaksi sukses hari ini
- **Pendapatan Bulan Ini**: Total pendapatan dari transaksi sukses bulan ini
- **Total Transaksi**: Jumlah total transaksi hari ini
- **Tingkat Keberhasilan**: Persentase transaksi yang berhasil

#### Grafik dan Statistik

1. **Grafik Pendapatan Harian** (30 hari terakhir)
   - Menampilkan tren pendapatan harian
   - Hover untuk melihat detail tanggal tertentu

2. **Grafik Pendapatan Bulanan** (12 bulan terakhir)
   - Menampilkan perbandingan pendapatan antar bulan
   - Membantu analisis tren jangka panjang

3. **Distribusi Transaksi per Lokasi** (Pie Chart)
   - Menampilkan persentase transaksi di setiap lokasi parkir
   - Klik untuk melihat detail lokasi

4. **Distribusi Jenis Kendaraan** (Bar Chart)
   - Perbandingan transaksi motor vs mobil
   - Membantu analisis pola parkir

5. **Top 10 Juru Parkir**
   - Daftar juru parkir dengan transaksi terbanyak
   - Menampilkan nama dan jumlah transaksi

#### Daftar Transaksi Real-time

- Menampilkan transaksi terbaru
- Auto-refresh setiap 30 detik
- Klik transaksi untuk melihat detail lengkap

## Manajemen Transaksi

### Melihat Daftar Transaksi

1. Klik menu "Transaksi" di sidebar
2. Daftar transaksi akan ditampilkan dengan informasi:
   - ID Transaksi
   - Tanggal dan Waktu
   - Juru Parkir
   - Jenis Kendaraan
   - Jumlah
   - Status Pembayaran

### Filter Transaksi

Gunakan filter untuk mencari transaksi spesifik:

1. **Rentang Tanggal**
   - Pilih tanggal mulai dan tanggal akhir
   - Klik "Terapkan Filter"

2. **Lokasi Parkir**
   - Pilih lokasi dari dropdown
   - Hanya transaksi di lokasi tersebut yang ditampilkan

3. **Juru Parkir**
   - Pilih juru parkir dari dropdown
   - Lihat semua transaksi dari juru parkir tertentu

4. **Status Pembayaran**
   - Filter berdasarkan: Pending, Sukses, Gagal, Expired
   - Kombinasikan dengan filter lain

### Detail Transaksi

Klik pada transaksi untuk melihat detail lengkap:
- ID Transaksi
- Data Juru Parkir
- Jenis Kendaraan dan Tarif
- Metode Pembayaran
- Waktu Pembayaran
- Response dari Payment Gateway

### Export Transaksi

1. Atur filter sesuai kebutuhan
2. Klik tombol "Export"
3. Pilih format: PDF atau Excel
4. File akan diunduh otomatis

## Manajemen Juru Parkir

### Melihat Daftar Juru Parkir

1. Klik menu "Juru Parkir" di sidebar
2. Daftar semua juru parkir akan ditampilkan

### Menambah Juru Parkir Baru

1. Klik tombol "Tambah Juru Parkir"
2. Isi form dengan data:
   - **Nomor Registrasi**: Nomor unik juru parkir (contoh: JP001)
   - **Nama**: Nama lengkap juru parkir
   - **Ruas Jalan**: Lokasi area parkir
   - **Sisi Lokasi**: Utara/Selatan/Timur/Barat
   - **Nomor Rekening Bank**: Untuk transfer pembayaran
   - **Nama Bank**: Bank tempat rekening
   - **PIN**: Kode akses juru parkir (minimal 4 digit)
3. Klik "Simpan"

### Mengubah Data Juru Parkir

1. Klik tombol "Edit" pada juru parkir yang ingin diubah
2. Ubah data yang diperlukan
3. Klik "Simpan"

### Mengaktifkan/Menonaktifkan Juru Parkir

1. Klik tombol "Aksi" pada juru parkir
2. Pilih "Aktifkan" atau "Nonaktifkan"
3. Konfirmasi tindakan

**Catatan**: Juru parkir yang dinonaktifkan tidak dapat membuat QR code baru

### Melihat Statistik Juru Parkir

Klik pada nama juru parkir untuk melihat:
- Total transaksi
- Total pendapatan
- Tingkat keberhasilan pembayaran
- Grafik transaksi bulanan

## Konfigurasi Tarif

### Melihat Tarif Saat Ini

1. Klik menu "Tarif Parkir" di sidebar
2. Tarif saat ini akan ditampilkan:
   - Motor: Rp 2.000
   - Mobil: Rp 5.000

### Mengubah Tarif

1. Klik tombol "Ubah Tarif"
2. Masukkan tarif baru untuk:
   - Motor
   - Mobil
3. Klik "Simpan"

**Catatan**: Tarif baru akan berlaku untuk transaksi berikutnya

### Tarif Spesifik Lokasi

Untuk mengatur tarif berbeda di lokasi tertentu:

1. Klik "Tambah Tarif Lokasi"
2. Pilih ruas jalan
3. Masukkan tarif untuk motor dan mobil
4. Klik "Simpan"

**Prioritas Tarif**:
- Tarif spesifik lokasi (jika ada)
- Tarif default (jika tidak ada tarif spesifik)

### Riwayat Perubahan Tarif

Lihat semua perubahan tarif yang pernah dilakukan:
- Tanggal perubahan
- Admin yang melakukan perubahan
- Tarif lama dan tarif baru

## Laporan

### Membuat Laporan Baru

1. Klik menu "Laporan" di sidebar
2. Klik tombol "Buat Laporan Baru"
3. Isi form:
   - **Rentang Tanggal**: Pilih tanggal mulai dan akhir
   - **Lokasi Parkir**: Pilih lokasi (opsional)
   - **Juru Parkir**: Pilih juru parkir (opsional)
   - **Format**: Pilih PDF atau Excel
4. Klik "Buat Laporan"

### Status Laporan

Laporan memiliki beberapa status:
- **Pending**: Laporan sedang menunggu untuk diproses
- **Processing**: Laporan sedang diproses
- **Completed**: Laporan siap diunduh
- **Failed**: Laporan gagal dibuat (coba lagi)

### Mengunduh Laporan

1. Cari laporan yang sudah selesai (status: Completed)
2. Klik tombol "Unduh"
3. File akan diunduh ke komputer Anda

### Isi Laporan

Laporan berisi:
- Tabel transaksi dengan detail lengkap
- Ringkasan total pendapatan
- Ringkasan jumlah transaksi
- Grafik distribusi pembayaran
- Logo dan informasi Dishub

## Audit Log

### Melihat Audit Log

1. Klik menu "Audit Log" di sidebar
2. Daftar semua aktivitas sistem akan ditampilkan

### Informasi Audit Log

Setiap entry audit log menampilkan:
- **Tanggal/Waktu**: Kapan aktivitas terjadi
- **Admin**: Siapa yang melakukan aktivitas
- **Aksi**: Jenis aktivitas (create, update, delete, login, logout)
- **Entitas**: Apa yang diubah (transaction, attendant, rate, user)
- **Nilai Lama**: Data sebelum perubahan
- **Nilai Baru**: Data setelah perubahan
- **IP Address**: Alamat IP admin

### Filter Audit Log

1. **Rentang Tanggal**: Filter berdasarkan tanggal
2. **Admin**: Filter berdasarkan admin yang melakukan aksi
3. **Jenis Aksi**: Filter berdasarkan jenis aktivitas
4. **Entitas**: Filter berdasarkan jenis data yang diubah

### Export Audit Log

1. Atur filter sesuai kebutuhan
2. Klik tombol "Export"
3. Pilih format: PDF atau Excel
4. File akan diunduh

## Troubleshooting

### Masalah Login

**Masalah**: Tidak bisa login
- **Solusi**: 
  - Pastikan email dan password benar
  - Cek koneksi internet
  - Coba refresh halaman
  - Hubungi administrator jika masalah berlanjut

### Masalah Dashboard

**Masalah**: Dashboard tidak menampilkan data
- **Solusi**:
  - Refresh halaman (F5)
  - Cek koneksi internet
  - Coba logout dan login kembali
  - Hubungi support jika masalah berlanjut

### Masalah Export Laporan

**Masalah**: Tidak bisa export laporan
- **Solusi**:
  - Pastikan ada data transaksi dalam rentang tanggal
  - Cek ukuran file (jika terlalu besar, gunakan rentang tanggal lebih kecil)
  - Coba format berbeda (PDF atau Excel)
  - Hubungi support jika masalah berlanjut

### Masalah Performa

**Masalah**: Sistem lambat
- **Solusi**:
  - Gunakan browser terbaru (Chrome, Firefox, Safari, Edge)
  - Bersihkan cache browser
  - Tutup tab browser yang tidak perlu
  - Cek koneksi internet
  - Hubungi support jika masalah berlanjut

## Kontak Support

Jika mengalami masalah atau memiliki pertanyaan:

- **Email**: support@dishub.go.id
- **Telepon**: 021-1234567
- **Jam Kerja**: Senin-Jumat, 08:00-17:00 WIB

---

**Versi**: 1.0
**Terakhir Diperbarui**: 2024-01-15
