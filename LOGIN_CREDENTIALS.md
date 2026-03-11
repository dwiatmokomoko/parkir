# Login Credentials

## Default Admin Accounts

### Admin Utama
- **Email**: `admin@dishub.go.id`
- **Password**: `password123`
- **Role**: Admin
- **Status**: Active

### Admin Test
- **Email**: `admin.test@dishub.go.id`
- **Password**: `password123`
- **Role**: Admin
- **Status**: Active

## Parking Attendant Accounts

Attendant accounts dibuat melalui admin panel. Default password untuk attendant yang dibuat via seeder adalah `password123`.

## Akses Aplikasi

### Admin Panel
- URL: `https://your-domain.com/login`
- Fitur:
  - Dashboard dengan statistik
  - Manajemen Parking Attendant
  - Manajemen Tarif Parkir
  - Laporan Transaksi
  - Audit Logs
  - Notifikasi

### Attendant Panel
- URL: `https://your-domain.com/attendant/login`
- Fitur:
  - Generate QR Code untuk pembayaran
  - Lihat transaksi sendiri
  - Notifikasi

## Keamanan

⚠️ **PENTING**: Segera ubah password default setelah login pertama kali!

### Cara Ubah Password

1. Login ke aplikasi
2. Klik profile/settings
3. Pilih "Change Password"
4. Masukkan password baru yang kuat

### Password Requirements

- Minimal 8 karakter
- Kombinasi huruf besar dan kecil
- Mengandung angka
- Mengandung karakter spesial (disarankan)

## Session Timeout

- **Admin**: 30 menit
- **Attendant**: 15 menit

Session akan otomatis logout setelah periode inaktif.

## Troubleshooting Login

### Lupa Password

Hubungi administrator untuk reset password atau gunakan fitur "Forgot Password" jika sudah dikonfigurasi.

### Account Locked

Jika terlalu banyak percobaan login gagal, account akan terkunci sementara. Tunggu beberapa menit atau hubungi administrator.

### Session Expired

Jika muncul "Session Expired", silakan login kembali.

## Database Status

Cek status data di server:

```bash
cd /var/www/html/parkir
php artisan tinker --execute="echo 'Users: ' . User::count() . PHP_EOL;"
php artisan tinker --execute="echo 'Attendants: ' . ParkingAttendant::count() . PHP_EOL;"
php artisan tinker --execute="echo 'Transactions: ' . Transaction::count() . PHP_EOL;"
```

## Support

Untuk bantuan lebih lanjut, hubungi:
- Email: support@dishub.go.id
- Hotline: 021-1234567
