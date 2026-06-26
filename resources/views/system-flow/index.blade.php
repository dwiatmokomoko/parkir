@extends('layouts.app')

@section('title', 'Alur Sistem - Sistem Monitoring Pembayaran Parkir')

@section('content')
<style>
    .flow-page {
        display: grid;
        gap: 20px;
    }

    .flow-panel,
    .flow-step,
    .flow-note {
        background: #ffffff;
        border: 1px solid #e5e7eb;
        border-radius: 8px;
        box-shadow: 0 1px 2px rgba(15, 23, 42, 0.04);
    }

    .flow-panel {
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

    .flow-copy {
        max-width: 860px;
        margin: 10px 0 0;
        color: #4b5563;
        font-size: 14px;
        line-height: 1.65;
    }

    .flow-grid {
        display: grid;
        grid-template-columns: repeat(3, minmax(0, 1fr));
        gap: 14px;
    }

    .flow-step {
        display: grid;
        gap: 12px;
        min-height: 210px;
        padding: 18px;
    }

    .flow-step-head {
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .flow-number {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 34px;
        height: 34px;
        border-radius: 999px;
        background: #eff6ff;
        color: #1d4ed8;
        font-size: 14px;
        font-weight: 800;
        flex: 0 0 auto;
    }

    .flow-step h2,
    .flow-note h2 {
        margin: 0;
        color: #111827;
        font-size: 17px;
        font-weight: 800;
        line-height: 1.35;
    }

    .flow-step p,
    .flow-note p,
    .flow-list li {
        color: #4b5563;
        font-size: 14px;
        line-height: 1.55;
    }

    .flow-step p {
        margin: 0;
    }

    .flow-meta {
        display: flex;
        flex-wrap: wrap;
        gap: 8px;
        margin-top: auto;
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

    .flow-note-grid {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 14px;
    }

    .flow-note {
        padding: 18px;
    }

    .flow-list {
        display: grid;
        gap: 8px;
        margin: 12px 0 0;
        padding-left: 18px;
    }

    .flow-status {
        display: grid;
        grid-template-columns: repeat(4, minmax(0, 1fr));
        gap: 12px;
        margin-top: 16px;
    }

    .flow-status-item {
        border: 1px solid #e5e7eb;
        border-radius: 8px;
        padding: 12px;
        background: #f9fafb;
    }

    .flow-status-item strong {
        display: block;
        color: #111827;
        font-size: 14px;
    }

    .flow-status-item span {
        display: block;
        margin-top: 5px;
        color: #6b7280;
        font-size: 12px;
        line-height: 1.45;
    }

    @media (max-width: 1024px) {
        .flow-grid {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }

        .flow-status {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }
    }

    @media (max-width: 720px) {
        .flow-title {
            font-size: 24px;
        }

        .flow-grid,
        .flow-note-grid,
        .flow-status {
            grid-template-columns: 1fr;
        }
    }
</style>

<div class="flow-page">
    <section class="flow-panel">
        <span class="flow-kicker">Panduan Operasional</span>
        <h1 class="flow-title">Alur Sistem Pembayaran Parkir</h1>
        <p class="flow-copy">
            Halaman ini merangkum cara kerja sistem dari sisi admin, juru parkir, pengguna parkir, Midtrans, sampai data transaksi muncul di dashboard dan laporan.
        </p>
    </section>

    <section class="flow-grid" aria-label="Alur kerja sistem">
        <article class="flow-step">
            <div class="flow-step-head">
                <span class="flow-number">1</span>
                <h2>Admin Menyiapkan Data</h2>
            </div>
            <p>Admin membuat akun juru parkir, menentukan lokasi kerja, dan memastikan tarif motor atau mobil sudah aktif untuk lokasi tersebut.</p>
            <div class="flow-meta">
                <span class="flow-tag">Juru Parkir</span>
                <span class="flow-tag">Tarif</span>
            </div>
        </article>

        <article class="flow-step">
            <div class="flow-step-head">
                <span class="flow-number">2</span>
                <h2>Juru Parkir Membuat QR</h2>
            </div>
            <p>Juru parkir login, memilih jenis kendaraan, mengisi plat nomor, lalu sistem membuat transaksi pending dan QRIS dinamis untuk pembayaran akhir parkir.</p>
            <div class="flow-meta">
                <span class="flow-tag">Plat Nomor</span>
                <span class="flow-tag">QRIS</span>
            </div>
        </article>

        <article class="flow-step">
            <div class="flow-step-head">
                <span class="flow-number">3</span>
                <h2>Pengguna Membayar</h2>
            </div>
            <p>Pengguna parkir memindai QR dan menyelesaikan pembayaran melalui aplikasi yang mendukung QRIS. Midtrans memproses status pembayaran.</p>
            <div class="flow-meta">
                <span class="flow-tag">QR Scan</span>
                <span class="flow-tag">Midtrans</span>
            </div>
        </article>

        <article class="flow-step">
            <div class="flow-step-head">
                <span class="flow-number">4</span>
                <h2>Midtrans Mengirim Callback</h2>
            </div>
            <p>Midtrans mengirim notifikasi ke endpoint callback. Sistem memverifikasi signature, nominal, dan order ID sebelum mengubah status transaksi.</p>
            <div class="flow-meta">
                <span class="flow-tag">Webhook</span>
                <span class="flow-tag">Validasi</span>
            </div>
        </article>

        <article class="flow-step">
            <div class="flow-step-head">
                <span class="flow-number">5</span>
                <h2>Status Disinkronkan</h2>
            </div>
            <p>Jika callback terlambat, halaman juru parkir tetap melakukan pengecekan status ke Midtrans sebagai cadangan agar transaksi sukses tidak tertahan pending.</p>
            <div class="flow-meta">
                <span class="flow-tag">Fallback Sync</span>
                <span class="flow-tag">Riwayat</span>
            </div>
        </article>

        <article class="flow-step">
            <div class="flow-step-head">
                <span class="flow-number">6</span>
                <h2>Data Muncul di Admin</h2>
            </div>
            <p>Transaksi yang berhasil masuk ke dashboard, riwayat juru parkir, laporan, dan statistik pendapatan sesuai lokasi serta periode yang dipilih.</p>
            <div class="flow-meta">
                <span class="flow-tag">Dashboard</span>
                <span class="flow-tag">Laporan</span>
            </div>
        </article>
    </section>

    <section class="flow-note-grid">
        <article class="flow-note">
            <h2>Status Transaksi</h2>
            <div class="flow-status">
                <div class="flow-status-item">
                    <strong>Pending</strong>
                    <span>QR dibuat dan menunggu pembayaran pengguna.</span>
                </div>
                <div class="flow-status-item">
                    <strong>Berhasil</strong>
                    <span>Pembayaran diterima dan pendapatan dihitung.</span>
                </div>
                <div class="flow-status-item">
                    <strong>Gagal</strong>
                    <span>Midtrans menolak atau pembayaran dibatalkan.</span>
                </div>
                <div class="flow-status-item">
                    <strong>Expired</strong>
                    <span>QR melewati batas waktu dan perlu dibuat ulang.</span>
                </div>
            </div>
        </article>

        <article class="flow-note">
            <h2>Hal Penting untuk Operasional</h2>
            <ul class="flow-list">
                <li>Endpoint callback Midtrans harus memakai domain aktif dengan HTTPS valid.</li>
                <li>Nilai `MIDTRANS_NOTIFICATION_URL` harus mengarah ke `/api/payments/callback` pada domain produksi.</li>
                <li>Jika domain diganti, buat transaksi baru setelah cache konfigurasi Laravel dibersihkan.</li>
                <li>Riwayat juru parkir menampilkan status terbaru dari database setelah callback atau fallback sync berjalan.</li>
            </ul>
        </article>
    </section>
</div>
@endsection
