@extends('layouts.app')

@section('title', 'Alur Sistem - Sistem Monitoring Pembayaran Parkir')

@section('content')
<style>
    .flow-page {
        display: grid;
        gap: 20px;
    }

    .flow-hero,
    .flow-section,
    .flow-step,
    .flow-role,
    .flow-status-card,
    .flow-check-card {
        background: #ffffff;
        border: 1px solid #e5e7eb;
        border-radius: 8px;
        box-shadow: 0 1px 2px rgba(15, 23, 42, 0.04);
    }

    .flow-hero,
    .flow-section {
        padding: 22px;
    }

    .flow-kicker {
        color: #2563eb;
        font-size: 12px;
        font-weight: 800;
        letter-spacing: 0.08em;
        text-transform: uppercase;
    }

    .flow-title {
        margin: 8px 0 0;
        color: #111827;
        font-size: 28px;
        font-weight: 800;
        line-height: 1.2;
    }

    .flow-copy,
    .flow-section-copy {
        color: #4b5563;
        font-size: 14px;
        line-height: 1.65;
    }

    .flow-copy {
        max-width: 920px;
        margin: 10px 0 0;
    }

    .flow-section-head {
        display: flex;
        align-items: flex-start;
        justify-content: space-between;
        gap: 16px;
        margin-bottom: 16px;
    }

    .flow-section-title {
        margin: 0;
        color: #111827;
        font-size: 20px;
        font-weight: 800;
        line-height: 1.3;
    }

    .flow-section-copy {
        margin: 6px 0 0;
        max-width: 840px;
    }

    .flow-summary {
        display: grid;
        grid-template-columns: repeat(4, minmax(0, 1fr));
        gap: 12px;
        margin-top: 18px;
    }

    .flow-summary-item {
        border: 1px solid #dbeafe;
        border-radius: 8px;
        background: #eff6ff;
        padding: 14px;
    }

    .flow-summary-item strong {
        display: block;
        color: #1e3a8a;
        font-size: 14px;
    }

    .flow-summary-item span {
        display: block;
        margin-top: 6px;
        color: #334155;
        font-size: 13px;
        line-height: 1.45;
    }

    .flow-role-grid {
        display: grid;
        grid-template-columns: repeat(3, minmax(0, 1fr));
        gap: 14px;
    }

    .flow-role {
        padding: 16px;
    }

    .flow-role h3,
    .flow-step h3,
    .flow-status-card h3,
    .flow-check-card h3 {
        margin: 0;
        color: #111827;
        font-size: 16px;
        font-weight: 800;
        line-height: 1.35;
    }

    .flow-role p,
    .flow-step p,
    .flow-check-card p {
        margin: 8px 0 0;
        color: #4b5563;
        font-size: 14px;
        line-height: 1.55;
    }

    .flow-timeline {
        display: grid;
        gap: 12px;
    }

    .flow-step {
        display: grid;
        grid-template-columns: 48px minmax(0, 1fr);
        gap: 14px;
        padding: 16px;
    }

    .flow-number {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 38px;
        height: 38px;
        border-radius: 999px;
        background: #2563eb;
        color: #ffffff;
        font-size: 14px;
        font-weight: 800;
    }

    .flow-step-detail {
        display: grid;
        gap: 10px;
    }

    .flow-step-list,
    .flow-check-list {
        display: grid;
        gap: 7px;
        margin: 0;
        padding-left: 18px;
    }

    .flow-step-list li,
    .flow-check-list li {
        color: #4b5563;
        font-size: 14px;
        line-height: 1.5;
    }

    .flow-tags {
        display: flex;
        flex-wrap: wrap;
        gap: 8px;
    }

    .flow-tag {
        display: inline-flex;
        align-items: center;
        border-radius: 999px;
        background: #f3f4f6;
        color: #374151;
        font-size: 12px;
        font-weight: 700;
        padding: 6px 9px;
    }

    .flow-status-grid,
    .flow-check-grid {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 14px;
    }

    .flow-status-card,
    .flow-check-card {
        padding: 16px;
    }

    .flow-status-card p {
        margin: 8px 0 0;
        color: #4b5563;
        font-size: 14px;
        line-height: 1.55;
    }

    .flow-status-pill {
        display: inline-flex;
        align-items: center;
        border-radius: 999px;
        font-size: 12px;
        font-weight: 800;
        padding: 6px 10px;
        margin-bottom: 10px;
    }

    .flow-status-pending {
        background: #fef3c7;
        color: #92400e;
    }

    .flow-status-success {
        background: #dcfce7;
        color: #166534;
    }

    .flow-status-failed {
        background: #fee2e2;
        color: #991b1b;
    }

    .flow-status-expired {
        background: #f1f5f9;
        color: #475569;
    }

    .flow-highlight {
        border: 1px solid #bfdbfe;
        border-radius: 8px;
        background: #eff6ff;
        padding: 14px 16px;
        color: #1e3a8a;
        font-size: 14px;
        line-height: 1.6;
    }

    .flow-highlight strong {
        color: #1d4ed8;
    }

    @media (max-width: 1024px) {
        .flow-summary,
        .flow-role-grid {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }
    }

    @media (max-width: 720px) {
        .flow-title {
            font-size: 24px;
        }

        .flow-section-head {
            display: block;
        }

        .flow-summary,
        .flow-role-grid,
        .flow-status-grid,
        .flow-check-grid {
            grid-template-columns: 1fr;
        }

        .flow-step {
            grid-template-columns: 1fr;
        }
    }
</style>

<div class="flow-page">
    <section class="flow-hero">
        <span class="flow-kicker">Panduan untuk Admin</span>
        <h1 class="flow-title">Cara Kerja Sistem Pembayaran Parkir</h1>
        <p class="flow-copy">
            Bayangkan sistem ini seperti buku catatan parkir digital. Juru parkir membuat QR untuk kendaraan, pengguna membayar lewat QRIS, Midtrans memberi kabar pembayaran, lalu sistem mencatat hasilnya ke riwayat, dashboard, dan laporan.
        </p>

        <div class="flow-summary" aria-label="Ringkasan sistem">
            <div class="flow-summary-item">
                <strong>1. Data disiapkan</strong>
                <span>Admin mengatur juru parkir, lokasi, dan tarif.</span>
            </div>
            <div class="flow-summary-item">
                <strong>2. QR dibuat</strong>
                <span>Juru parkir mengisi kendaraan dan plat nomor.</span>
            </div>
            <div class="flow-summary-item">
                <strong>3. Pengguna membayar</strong>
                <span>Pembayaran dilakukan melalui QRIS.</span>
            </div>
            <div class="flow-summary-item">
                <strong>4. Data masuk laporan</strong>
                <span>Status sukses masuk dashboard dan laporan.</span>
            </div>
        </div>
    </section>

    <section class="flow-section">
        <div class="flow-section-head">
            <div>
                <h2 class="flow-section-title">Siapa Melakukan Apa?</h2>
                <p class="flow-section-copy">Bagian ini membantu admin memahami peran utama tanpa perlu membaca istilah teknis.</p>
            </div>
        </div>

        <div class="flow-role-grid">
            <article class="flow-role">
                <h3>Admin</h3>
                <p>Mengelola akun juru parkir, mengatur tarif, memantau transaksi, dan mengambil laporan pendapatan.</p>
            </article>
            <article class="flow-role">
                <h3>Juru Parkir</h3>
                <p>Membuat QR pembayaran saat kendaraan selesai parkir, mengecek status pembayaran, dan melihat riwayat hariannya.</p>
            </article>
            <article class="flow-role">
                <h3>Pengguna Parkir</h3>
                <p>Memindai QR yang diberikan juru parkir dan membayar melalui aplikasi pembayaran yang mendukung QRIS.</p>
            </article>
        </div>
    </section>

    <section class="flow-section">
        <div class="flow-section-head">
            <div>
                <h2 class="flow-section-title">Alur dari Awal Sampai Masuk Laporan</h2>
                <p class="flow-section-copy">Ikuti langkah berikut untuk memahami perjalanan satu transaksi parkir.</p>
            </div>
        </div>

        <div class="flow-timeline">
            <article class="flow-step">
                <span class="flow-number">1</span>
                <div class="flow-step-detail">
                    <h3>Admin menyiapkan petugas dan tarif</h3>
                    <p>Sebelum transaksi berjalan, admin memastikan juru parkir sudah aktif dan tarif sesuai lokasi sudah tersedia.</p>
                    <ul class="flow-step-list">
                        <li>Akun juru parkir memiliki nomor registrasi dan PIN.</li>
                        <li>Lokasi kerja juru parkir sudah benar.</li>
                        <li>Tarif motor dan mobil sudah diatur.</li>
                    </ul>
                    <div class="flow-tags">
                        <span class="flow-tag">Menu Juru Parkir</span>
                        <span class="flow-tag">Menu Tarif</span>
                    </div>
                </div>
            </article>

            <article class="flow-step">
                <span class="flow-number">2</span>
                <div class="flow-step-detail">
                    <h3>Juru parkir membuat QR pembayaran</h3>
                    <p>Setelah kendaraan selesai parkir, juru parkir memilih jenis kendaraan dan mengisi plat nomor. Sistem membuat transaksi dengan status pending.</p>
                    <ul class="flow-step-list">
                        <li>Pending berarti transaksi sudah dibuat, tetapi belum dibayar.</li>
                        <li>Plat nomor tersimpan agar transaksi mudah dicocokkan dengan kendaraan.</li>
                        <li>QR memiliki batas waktu, sehingga perlu dibuat ulang jika sudah expired.</li>
                    </ul>
                    <div class="flow-tags">
                        <span class="flow-tag">Generate QR</span>
                        <span class="flow-tag">Plat Nomor</span>
                    </div>
                </div>
            </article>

            <article class="flow-step">
                <span class="flow-number">3</span>
                <div class="flow-step-detail">
                    <h3>Pengguna membayar lewat QRIS</h3>
                    <p>Pengguna memindai QR memakai aplikasi pembayaran. Midtrans menjadi pihak yang memproses pembayaran tersebut.</p>
                    <ul class="flow-step-list">
                        <li>Jika pembayaran belum selesai, status tetap pending.</li>
                        <li>Jika pengguna menyelesaikan pembayaran, Midtrans akan mencatat transaksi sukses.</li>
                        <li>Nominal yang diterima harus sama dengan tarif yang dibuat sistem.</li>
                    </ul>
                    <div class="flow-tags">
                        <span class="flow-tag">QRIS</span>
                        <span class="flow-tag">Midtrans</span>
                    </div>
                </div>
            </article>

            <article class="flow-step">
                <span class="flow-number">4</span>
                <div class="flow-step-detail">
                    <h3>Midtrans mengirim kabar ke sistem</h3>
                    <p>Setelah ada perubahan pembayaran, Midtrans mengirim notifikasi ke alamat callback sistem.</p>
                    <ul class="flow-step-list">
                        <li>Sistem mengecek tanda tangan keamanan dari Midtrans.</li>
                        <li>Sistem mencocokkan order ID dan nominal pembayaran.</li>
                        <li>Jika semuanya benar, status transaksi diperbarui.</li>
                    </ul>
                    <div class="flow-tags">
                        <span class="flow-tag">Callback</span>
                        <span class="flow-tag">Validasi Aman</span>
                    </div>
                </div>
            </article>

            <article class="flow-step">
                <span class="flow-number">5</span>
                <div class="flow-step-detail">
                    <h3>Status tampil di juru parkir</h3>
                    <p>Jika pembayaran sukses, QR di halaman juru parkir hilang dan muncul pemberitahuan pembayaran berhasil.</p>
                    <ul class="flow-step-list">
                        <li>Riwayat juru parkir berubah dari pending menjadi berhasil.</li>
                        <li>Jika notifikasi Midtrans terlambat, sistem tetap mengecek ulang status ke Midtrans saat halaman status atau riwayat dibuka.</li>
                        <li>Notifikasi lama otomatis ditandai terbaca supaya tidak menumpuk di bawah halaman generate QR.</li>
                    </ul>
                    <div class="flow-tags">
                        <span class="flow-tag">Notifikasi</span>
                        <span class="flow-tag">Riwayat</span>
                    </div>
                </div>
            </article>

            <article class="flow-step">
                <span class="flow-number">6</span>
                <div class="flow-step-detail">
                    <h3>Admin memantau transaksi dan laporan</h3>
                    <p>Transaksi yang berhasil masuk ke dashboard, daftar transaksi, statistik pendapatan, dan laporan.</p>
                    <ul class="flow-step-list">
                        <li>Dashboard menampilkan ringkasan pendapatan dan jumlah transaksi.</li>
                        <li>Menu Transaksi menampilkan detail seperti lokasi, juru parkir, jenis kendaraan, plat nomor, nominal, dan status.</li>
                        <li>Menu Laporan dipakai untuk rekap berdasarkan periode.</li>
                    </ul>
                    <div class="flow-tags">
                        <span class="flow-tag">Dashboard</span>
                        <span class="flow-tag">Transaksi</span>
                        <span class="flow-tag">Laporan</span>
                    </div>
                </div>
            </article>
        </div>
    </section>

    <section class="flow-section">
        <div class="flow-section-head">
            <div>
                <h2 class="flow-section-title">Arti Status Transaksi</h2>
                <p class="flow-section-copy">Status membantu admin mengetahui posisi pembayaran tanpa membuka dashboard Midtrans.</p>
            </div>
        </div>

        <div class="flow-status-grid">
            <article class="flow-status-card">
                <span class="flow-status-pill flow-status-pending">Pending</span>
                <h3>Menunggu Pembayaran</h3>
                <p>QR sudah dibuat, tetapi pengguna belum menyelesaikan pembayaran atau sistem belum menerima kabar terbaru.</p>
            </article>
            <article class="flow-status-card">
                <span class="flow-status-pill flow-status-success">Berhasil</span>
                <h3>Pembayaran Diterima</h3>
                <p>Transaksi sudah dibayar. Nominalnya masuk ke perhitungan pendapatan dan laporan.</p>
            </article>
            <article class="flow-status-card">
                <span class="flow-status-pill flow-status-failed">Gagal</span>
                <h3>Pembayaran Tidak Berhasil</h3>
                <p>Pembayaran ditolak, dibatalkan, atau gagal diproses. Juru parkir dapat membuat QR baru jika diperlukan.</p>
            </article>
            <article class="flow-status-card">
                <span class="flow-status-pill flow-status-expired">Expired</span>
                <h3>QR Sudah Kedaluwarsa</h3>
                <p>Batas waktu QR sudah habis. Jika pengguna belum membayar, juru parkir perlu membuat QR baru.</p>
            </article>
        </div>
    </section>

    <section class="flow-section">
        <div class="flow-section-head">
            <div>
                <h2 class="flow-section-title">Kalau Terjadi Kendala</h2>
                <p class="flow-section-copy">Gunakan daftar berikut sebagai pengecekan awal sebelum menghubungi teknis.</p>
            </div>
        </div>

        <div class="flow-check-grid">
            <article class="flow-check-card">
                <h3>Midtrans sudah sukses, tetapi riwayat masih pending</h3>
                <p>Biasanya terjadi karena notifikasi dari Midtrans belum sampai ke server.</p>
                <ul class="flow-check-list">
                    <li>Tunggu beberapa detik lalu buka ulang riwayat juru parkir.</li>
                    <li>Pastikan domain callback Midtrans memakai domain aktif.</li>
                    <li>Pastikan HTTPS domain valid dan bisa diakses dari luar.</li>
                    <li>Pastikan nilai `MIDTRANS_NOTIFICATION_URL` mengarah ke `/api/payments/callback`.</li>
                </ul>
            </article>

            <article class="flow-check-card">
                <h3>Juru parkir tidak bisa membuat QR</h3>
                <p>Periksa data dasar yang dibutuhkan sebelum transaksi dibuat.</p>
                <ul class="flow-check-list">
                    <li>Akun juru parkir harus aktif.</li>
                    <li>Lokasi juru parkir harus terisi.</li>
                    <li>Tarif untuk jenis kendaraan harus tersedia.</li>
                    <li>Plat nomor wajib diisi sebelum QR dibuat.</li>
                </ul>
            </article>

            <article class="flow-check-card">
                <h3>Notifikasi terlihat menumpuk</h3>
                <p>Sistem menampilkan notifikasi pembayaran yang belum terbaca.</p>
                <ul class="flow-check-list">
                    <li>Notifikasi baru akan hilang otomatis setelah beberapa detik.</li>
                    <li>Notifikasi yang sudah ditampilkan akan ditandai terbaca.</li>
                    <li>Jika browser lama terbuka, muat ulang halaman generate QR.</li>
                </ul>
            </article>

            <article class="flow-check-card">
                <h3>Domain baru belum dipakai Midtrans</h3>
                <p>Transaksi baru harus dibuat setelah konfigurasi domain diganti.</p>
                <ul class="flow-check-list">
                    <li>Periksa `APP_URL` dan `MIDTRANS_NOTIFICATION_URL` di server.</li>
                    <li>Bersihkan cache konfigurasi Laravel setelah mengubah `.env`.</li>
                    <li>Pastikan dashboard Midtrans juga memakai URL callback yang sama.</li>
                </ul>
            </article>
        </div>
    </section>

    <section class="flow-section">
        <div class="flow-highlight">
            <strong>Kesimpulan singkat:</strong> admin menyiapkan data, juru parkir membuat QR, pengguna membayar, Midtrans mengirim kabar, lalu sistem memperbarui riwayat dan laporan. Jika kabar dari Midtrans terlambat, sistem tetap punya pengecekan cadangan saat status atau riwayat dibuka.
        </div>
    </section>
</div>
@endsection
