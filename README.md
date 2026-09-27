# Sistem POS & Manajemen Stok Toko Material A

Aplikasi Web Point of Sale (POS) & Inventory Management System yang dibangun berdasarkan spesifikasi **PRD-1.md**, **PRD-2.md**, **design.md**, dan dokumen rancangan UI/UX.

---

## 🌟 Fitur Utama & Kesesuaian Requirement

### 1. Autentikasi & Role-Based Access Control (RBAC)
- **Role Terintegrasi**: `Superadmin`, `Admin Stock`, `Kasir`.
- **Dynamic Sidebar Navigation**: Menu sidebar hanya menampilkan link yang sesuai dengan hak akses (*privilege*) role pengelolanya.
- **Pengaturan Privilege (Superadmin)**: Superadmin dapat mengelola hak akses per role secara granular (checkbox matrix per modul).
- **Akun Demo Ready**:
  - **Superadmin**: username `superadmin`, password `password`
  - **Admin Stock**: username `adminstock`, password `password`
  - **Kasir**: username `kasir`, password `password`

### 2. POS & Modul Transaksi Order (Kasir)
- **Interface 2 Kolom**: Catalog item di kiri dengan pencarian *real-time*, sticky cart summary di kanan.
- **Hitung Otomatis**: Subtotal, Pajak PPn 11% (`subtotal × 11%`), dan Total Transaksi dihitung di server-side.
- **Validasi Stok Real-Time & Anti-Minus**: Sistem secara otomatis menolak transaksi jika jumlah pesanan melebihi stok tersedia.
- **Concurrency & Race Condition Protection**: Menggunakan Database Transaction & Row-Level Locking (`lockForUpdate()`) di MySQL/SQLite.
- **Nomor Invoice Unik**: Format otomatis `INV/[BULAN_ROMAWI]/[TAHUN]/[URUTAN 3 DIGIT]` (contoh: `INV/IX/2026/001`).
- **Cetak Struk/Invoice**: Layout struk thermal POS yang *print-friendly*.

### 3. Manajemen Stok Barang (Admin Stock)
- **Master Stok**: Menampilkan status stok dengan badge warna visual (*Stok Normal*, *Stok Menipis ≤ 10*, *Stok Habis*).
- **Stock In (Input Stok Masuk)**: Admin Stock dapat menambahkan stok masuk beserta catatan/nomor surat jalan.
- **Audit Trail Mutasi Stok**: Mencatat setiap histori pergerakan stok (`IN` / `OUT`), stok awal, stok akhir, operator user, dan referensi transaksi order.

### 4. Master Data & Reporting (Superadmin)
- **Master Item**: CRUD SKU, Nama Barang, Kategori, Satuan (UoM), Harga Jual, Stok Awal.
- **Master Harga Jual**: Pengelolaan harga jual per item.
- **Master UoM**: Pengelolaan satuan (Pcs, Kg, Sak, Meter, Batang, Box, Roll, Set).
- **Master Metode Pembayaran**: Cash, Debit, Kredit, Lainnya.
- **Master User & Akun**: Pembuatan & pengaturan user serta penetapan role.
- **Laporan Penjualan**: Filter rentang tanggal (`dd/mm/yyyy`), rincian item, harga sebelum & sesudah pajak.
- **Laporan Stok Barang**: Laporan posisi stok barang real-time per tanggal.

---

## 🛠️ Technology Stack

- **Backend**: Laravel 11 / PHP 8.5
- **Database**: SQLite / MySQL (PDO Driver)
- **Auth & RBAC**: Laravel Auth + Spatie Laravel-Permission
- **Frontend**: Blade + Vanilla CSS Design System (Flat Solid, High Contrast, Desktop-First)
- **Testing Framework**: PHPUnit / Laravel Feature Tests

---

## 🚀 Cara Menjalankan Aplikasi

### 1. Kloning / Akses Repository
Pastikan dependensi PHP & Composer terinstal:
```bash
composer install
```

### 2. Jalankan Migrasi & Seeder Data Awal
```bash
php artisan migrate:fresh --seed
```

### 3. Jalankan Dev Server
```bash
php artisan serve
```
Buka browser di: `http://127.0.0.1:8000`

---

## 🧪 Menjalankan Automated Tests

Aplikasi dilengkapi dengan pengujian otomatis (*Feature Tests*) untuk memverifikasi login, perhitungan transaksi, validasi stok, keunikan invoice, dan stok masuk.

Jalankan perintah berikut:
```bash
php artisan test
```
