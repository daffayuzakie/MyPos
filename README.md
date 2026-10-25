# MyPos — Aplikasi POS & Manajemen Toko

MyPos adalah aplikasi **Point of Sale (POS)** dan manajemen toko berbasis web yang dibangun dengan Laravel. Aplikasi ini dirancang sebagai **multi-tenant**, sehingga satu instalasi dapat digunakan oleh banyak usaha dengan data yang terpisah berdasarkan `business_id`.

MyPos membantu pemilik usaha dan kasir untuk:

- Memproses transaksi penjualan dengan cepat.
- Mengelola produk, kategori, satuan, dan stok.
- Menerima pembayaran tunai maupun online melalui Midtrans.
- Mengelola data pelanggan dan program loyalitas.
- Memantau riwayat transaksi dan melakukan retur.
- Melihat laporan penjualan dan laba kotor.
- Mengatur karyawan, role, hak akses, dan cabang.

---

## Daftar Isi

- [Fitur Utama](#fitur-utama)
- [Teknologi](#teknologi)
- [Kebutuhan Sistem](#kebutuhan-sistem)
- [Instalasi](#instalasi)
- [Konfigurasi Midtrans](#konfigurasi-midtrans)
- [Menjalankan Aplikasi](#menjalankan-aplikasi)
- [Akun Demo](#akun-demo)
- [Alur Bisnis](#alur-bisnis)
- [Struktur Project](#struktur-project)
- [Role & Hak Akses](#role--hak-akses)
- [Testing](#testing)
- [Status Pengembangan](#status-pengembangan)
- [Keamanan](#keamanan)
- [Lisensi](#lisensi)

---

## Fitur Utama

### 1. Kasir / POS

- Pencarian produk berdasarkan nama, SKU, atau barcode.
- Filter produk berdasarkan kategori.
- Keranjang belanja dengan pengaturan jumlah.
- Diskon nominal atau persentase.
- Perhitungan pajak otomatis berdasarkan pengaturan usaha.
- Pemilihan pelanggan.
- Pembayaran tunai dengan perhitungan kembalian.
- Pembayaran online melalui Midtrans Snap.
- Transaksi ditahan (**hold**) dan dilanjutkan kembali.
- Struk transaksi yang dapat dicetak.
- Pengiriman struk melalui tautan WhatsApp.

### 2. Produk & Stok

- CRUD produk, kategori, dan satuan.
- Upload gambar produk.
- Harga beli dan harga jual.
- Status aktif/nonaktif produk.
- Stok awal saat membuat produk.
- Stok masuk, stok keluar, dan penyesuaian stok.
- Riwayat pergerakan stok beserta stok sebelum dan sesudah.
- Peringatan stok rendah.

### 3. Pelanggan & Loyalitas

- CRUD data pelanggan.
- Pencarian pelanggan berdasarkan nama, telepon, atau email.
- Poin loyalitas otomatis dari transaksi.
- Level pelanggan: regular, silver, gold, dan platinum.
- Pencatatan total belanja pelanggan.

### 4. Transaksi & Retur

- Riwayat transaksi dengan filter tanggal, status, dan nomor nota.
- Detail transaksi dan pembayaran.
- Cetak ulang struk.
- Retur sebagian atau penuh.
- Pengembalian stok otomatis saat retur.
- Pencatatan pembayaran refund.

### 5. Laporan

- Ringkasan omzet dan jumlah transaksi.
- Rata-rata nilai transaksi.
- Laba kotor berdasarkan harga beli produk.
- Grafik penjualan harian.
- Rincian per metode pembayaran.
- Rincian per kategori.
- Rincian per kasir.
- Produk terlaris.
- Export CSV.
- Cetak laporan dalam format HTML/PDF via browser.

### 6. Karyawan & Hak Akses

- Role bawaan: **admin**, **manajer**, dan **kasir**.
- Permission granular menggunakan Spatie Laravel Permission.
- Aktivasi dan nonaktivasi karyawan.
- Login karyawan menggunakan email/password.
- Login kasir menggunakan **kode usaha + PIN**.
- Penugasan karyawan ke cabang.

### 7. Pengaturan Usaha

- Registrasi usaha baru.
- Onboarding usaha:
  1. Profil usaha.
  2. Alamat dan cabang.
  3. Pajak dan metode pembayaran.
  4. Produk pertama.
- Pengaturan nama usaha, slug, logo, kontak, dan alamat.
- Pengaturan pajak.
- Pengaturan prefix nomor nota.
- Pemilihan cabang aktif.

### 8. Pembayaran Online

- Integrasi Midtrans Snap.
- Pembuatan transaksi pending.
- Reservasi stok selama menunggu pembayaran.
- Webhook Midtrans dengan verifikasi signature.
- Rekonsiliasi status pembayaran jika webhook terlambat.
- Pencatatan event webhook untuk audit.

---

## Teknologi

| Komponen | Teknologi |
|---|---|
| Backend | Laravel 13 |
| Bahasa | PHP 8.3+ |
| Database | MySQL / MariaDB |
| Frontend | Blade, Alpine.js, Tailwind CSS |
| Build Tool | Vite |
| Role & Permission | Spatie Laravel Permission |
| Payment Gateway | Midtrans Snap |
| Testing | PHPUnit |

---

## Kebutuhan Sistem

Pastikan environment sudah memiliki:

- PHP 8.3 atau lebih baru.
- Composer 2.x.
- MySQL 8.x atau MariaDB.
- Node.js 20+ dan npm.
- Web server lokal seperti Laragon, XAMPP, atau `php artisan serve`.

---

## Instalasi

### 1. Clone repository

```bash
git clone <URL_REPOSITORY> laravel-pos
cd laravel-pos
```

### 2. Install dependency PHP

```bash
composer install
```

### 3. Buat file environment

Linux/macOS:

```bash
cp .env.example .env
```

Windows PowerShell:

```powershell
Copy-Item .env.example .env
```

### 4. Buat database

Contoh menggunakan MySQL:

```sql
CREATE DATABASE pos CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```

### 5. Konfigurasi `.env`

Sesuaikan bagian berikut:

```dotenv
APP_NAME=MyPos
APP_ENV=local
APP_DEBUG=true
APP_URL=http://localhost:8000

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=pos
DB_USERNAME=root
DB_PASSWORD=

FILESYSTEM_DISK=public
```

### 6. Generate application key

```bash
php artisan key:generate
```

### 7. Jalankan migrasi dan seeder

```bash
php artisan migrate --seed
```

### 8. Buat symbolic link storage

Diperlukan agar logo usaha dan gambar produk dapat diakses publik.

```bash
php artisan storage:link
```

### 9. Install dependency frontend dan build asset

```bash
npm install
npm run build
```

Untuk mode development:

```bash
npm run dev
```

---

## Konfigurasi Midtrans

Tambahkan konfigurasi berikut ke `.env`:

```dotenv
MIDTRANS_SERVER_KEY=
MIDTRANS_CLIENT_KEY=
MIDTRANS_IS_PRODUCTION=false
MIDTRANS_IS_SANITIZED=true
MIDTRANS_IS_3DS=true

MIDTRANS_NOTIFICATION_URL=http://localhost:8000/midtrans/webhook
```

Catatan:

- Gunakan kredensial **sandbox** terlebih dahulu.
- `MIDTRANS_SERVER_KEY` dan `MIDTRANS_CLIENT_KEY` dapat diperoleh dari dashboard Midtrans.
- Untuk development lokal, webhook Midtrans memerlukan URL publik. Gunakan tunnel seperti ngrok atau Cloudflare Tunnel.
- Endpoint webhook: `POST /midtrans/webhook`.
- Signature webhook diverifikasi secara otomatis oleh aplikasi.

---

## Menjalankan Aplikasi

Jalankan server Laravel:

```bash
php artisan serve
```

Jalankan Vite pada terminal terpisah:

```bash
npm run dev
```

Atau gunakan script development yang tersedia:

```bash
composer dev
```

Akses aplikasi di:

```text
http://localhost:8000
```

### Menjalankan Scheduler

Scheduler digunakan untuk:

- Meng-expire reservasi stok yang sudah kedaluwarsa.
- Merekonstruksi pembayaran Midtrans yang masih pending.

Untuk development:

```bash
php artisan schedule:work
```

Untuk production, tambahkan cron:

```cron
* * * * * cd /path/to/laravel-pos && php artisan schedule:run >> /dev/null 2>&1
```

---

## Akun Demo

Setelah menjalankan `php artisan migrate --seed`, akun berikut tersedia:

| Role | Email | Password | PIN |
|---|---|---|---|
| Admin | `admin@pos.com` | `password123` | `1234` |
| Kasir | `kasir@pos.com` | `password123` | `1234` |

Kode usaha untuk login PIN:

```text
default-cafe
```

> **Penting:** Segera ubah password dan PIN default sebelum aplikasi digunakan di lingkungan nyata.

### Data Produk Demo

Seeder utama belum otomatis mengisi produk demo. Untuk menambahkan produk dan pelanggan contoh:

```bash
php artisan db:seed --class=DemoPosSeeder
```

---

## Alur Bisnis

```text
Registrasi Usaha
      │
      ▼
Onboarding
(profil → cabang → pajak → produk)
      │
      ▼
Login Admin / Kasir
      │
      ▼
POS: pilih produk → keranjang → diskon → pelanggan
      │
      ├── Tunai
      │     └── Transaksi selesai → stok berkurang
      │
      └── Midtrans
            └── Transaksi pending → stok direservasi
                  └── Webhook sukses → stok berkurang → selesai
      │
      ▼
Struk → Riwayat Transaksi → Laporan
```

### Alur pembayaran tunai

1. Kasir memilih produk dan mengatur jumlah.
2. Sistem menghitung subtotal, diskon, pajak, dan total.
3. Kasir memasukkan nominal uang diterima.
4. Transaksi dibuat dengan status `completed`.
5. Stok produk dikurangi dan `stock_movements` dicatat.
6. Poin pelanggan ditambahkan jika pelanggan dipilih.

### Alur pembayaran Midtrans

1. Sistem membuat transaksi dengan status `pending`.
2. Stok produk **direservasi**, belum dikurangi.
3. Midtrans Snap token dibuat.
4. Kasir/pelanggan menyelesaikan pembayaran.
5. Webhook atau polling status memperbarui pembayaran.
6. Jika berhasil:
   - Pembayaran menjadi `paid`.
   - Transaksi menjadi `completed`.
   - Stok dikurangi.
   - Reservasi stok diubah menjadi `converted`.
7. Jika gagal/expired:
   - Transaksi menjadi `cancelled`.
   - Reservasi stok dilepas.

---

## Struktur Project

```text
app/
├── Console/Commands/        # Command scheduler (expire reservation, reconcile payment)
├── Http/
│   ├── Controllers/         # Controller aplikasi
│   ├── Requests/            # Form request & validasi
│   └── Middleware/
├── Models/                  # Model Eloquent
├── Policies/                # Otorisasi per model
├── Services/                # Business logic
│   ├── SaleService.php
│   ├── PaymentService.php
│   ├── StockService.php
│   ├── StockReservationService.php
│   ├── RefundService.php
│   ├── DiscountService.php
│   └── MidtransService.php
└── Traits/
    └── BelongsToBusiness.php

database/
├── migrations/              # Skema database
├── seeders/                 # Data awal & demo
└── factories/

resources/
├── views/
│   ├── pos/                 # Antarmuka kasir
│   ├── dashboard.blade.php
│   ├── products/
│   ├── customers/
│   ├── reports/
│   ├── sales/
│   ├── stock/
│   ├── users/
│   └── business/
├── js/
└── css/

routes/
├── web.php                  # Route utama
├── auth.php                 # Route autentikasi
└── console.php              # Scheduler

tests/
├── Feature/                 # Feature test
└── Unit/                    # Unit test
```

---

## Role & Hak Akses

| Fitur | Admin | Manajer | Kasir |
|---|---|---|---|
| Dashboard | Ya | Ya | Ya |
| POS / Checkout | Ya | Ya | Ya |
| Lihat produk | Ya | Ya | Ya |
| Kelola produk | Ya | Ya | Tidak |
| Kelola stok | Ya | Ya | Lihat saja |
| Lihat laporan | Ya | Ya | Ya |
| Kelola pelanggan | Ya | Ya | Ya |
| Kelola karyawan | Ya | Ya | Tidak |
| Pengaturan usaha | Ya | Tidak | Tidak |
| Retur transaksi | Ya | Ya | Tidak |

Permission dapat disesuaikan melalui `database/seeders/RoleAndUserSeeder.php`.

---

## Testing

Jalankan seluruh test:

```bash
php artisan test
```

Test menggunakan database SQLite in-memory sesuai konfigurasi `phpunit.xml`, sehingga tidak mengganggu database development.

Cakupan test meliputi antara lain:

- Isolasi data antar usaha.
- Checkout penjualan.
- Pengurangan dan penyesuaian stok.
- Reservasi stok.
- Onboarding usaha.
- Login PIN.
- Pemilihan cabang.
- Upload gambar produk.
- Rekonsiliasi pembayaran Midtrans.
- Dashboard.

---

## Status Pengembangan

Fitur inti POS sudah berjalan, namun beberapa modul masih dalam tahap pengembangan:

- **Multi-cabang:** struktur cabang, penugasan user, dan halaman pemilihan cabang sudah ada, tetapi produk, stok, dan transaksi belum dipisahkan secara operasional per cabang.
- **Pembelian & supplier:** tabel `suppliers`, `purchases`, dan `purchase_items` sudah tersedia, tetapi belum memiliki alur UI/controller yang lengkap.
- **Pengeluaran:** model dan tabel `expenses` tersedia, tetapi belum terhubung ke menu dan laporan.
- **Mode offline:** indikator online/offline sudah ada, tetapi antrean transaksi offline belum diimplementasikan.
- **Pengaturan lanjutan:** tab perangkat, notifikasi, dan keamanan/backup masih berupa tampilan statis.
- **Branding:** nilai `APP_NAME` pada `.env` perlu diubah menjadi `MyPos` agar konsisten dengan tampilan aplikasi.

---

## Keamanan

- Jangan commit file `.env` ke repository.
- Jangan membagikan `MIDTRANS_SERVER_KEY`.
- Ubah akun dan PIN default sebelum production.
- Gunakan HTTPS pada lingkungan production.
- Pastikan endpoint webhook Midtrans dapat diakses publik dan signature diverifikasi.
- Gunakan password database yang kuat.
- Batasi akses langsung ke database dan storage.

---

## Lisensi

Project ini menggunakan lisensi **MIT**. Silakan sesuaikan bagian ini dengan kebijakan lisensi usaha atau organisasi Anda.
