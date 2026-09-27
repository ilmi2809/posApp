# Design Document
## Sistem Order & Manajemen Stock Toko Material A

**Versi:** 1.0
**Berdasarkan:** PRD Sistem POS Toko Material A v1.0
**Tanggal:** 26 September 2026
**Status:** Draft

---

## 1. Prinsip Desain

Sistem ini adalah **tools kerja internal**, bukan produk konsumen. Kasir dan admin stock akan membukanya berulang kali setiap hari, sering dalam kondisi terburu-buru (ada antrean pelanggan). Maka prinsip utamanya:

1. **Fungsi di atas estetika.** Setiap elemen visual harus punya alasan fungsional — bukan dekorasi.
2. **Konsisten dan bisa ditebak.** Layout, posisi tombol, dan pola interaksi sama di semua halaman. User tidak perlu belajar ulang di tiap modul.
3. **Minim distraksi visual.** Tidak ada gradien, tidak ada ikon berlebihan, tidak ada animasi hias. Warna dipakai untuk menyampaikan makna (status, peringatan), bukan estetika.
4. **Density tinggi tapi tetap terbaca.** Kasir & admin stock butuh melihat banyak data (daftar item, riwayat transaksi) tanpa scroll berlebihan.
5. **Kesalahan harus mahal untuk terjadi, murah untuk diperbaiki.** Validasi jelas terlihat, bukan tersembunyi dalam tooltip kecil.

**Yang sengaja dihindari (anti-AI-slop):**
- ❌ Gradien warna di background, tombol, atau card
- ❌ Ikon di setiap label, tombol, atau menu (ikon hanya dipakai jika benar-benar membantu pemindaian cepat: status, aksi tabel)
- ❌ Lebih dari 1 font family
- ❌ Drop shadow tebal, glassmorphism, efek "modern" yang tidak menambah fungsi
- ❌ Emoji di UI produksi
- ❌ Card dengan border-radius besar dan padding berlebihan yang membuang ruang layar
- ❌ Warna aksen yang banyak (ungu-pink-biru sekaligus) — cukup 1 warna aksen + warna status

---

## 2. Target Pengguna & Konteks Pemakaian

| Role | Perangkat | Konteks Pemakaian |
|---|---|---|
| Kasir | Desktop/laptop kasir, kadang tablet | Berdiri/duduk di depan pelanggan, transaksi cepat berulang, tekanan waktu tinggi |
| Admin Stock | Desktop di gudang | Input data lebih santai, tapi volume data besar (banyak item) |
| Superadmin | Desktop, kadang laptop pribadi | Cek data, atur user/privilege, sesekali lihat report |

Implikasi desain: **target device utama adalah desktop/laptop dengan mouse+keyboard**, bukan mobile-first. Layout boleh menggunakan tabel padat dan form dengan banyak field terlihat sekaligus, bukan pola card mobile.

---

## 3. Informasi Arsitektur

```
Login
 │
 ├─ Kasir
 │   ├─ Buat Order (halaman utama/default setelah login)
 │   ├─ Riwayat Transaksi Saya
 │   └─ Struk (hasil setelah order selesai)
 │
 ├─ Admin Stock
 │   ├─ Master Item
 │   ├─ Stock Masuk (input)
 │   ├─ Riwayat Pergerakan Stock
 │   └─ Report Stock (filter hari ini)
 │
 └─ Superadmin
     ├─ Dashboard (ringkasan, jika dikerjakan)
     ├─ Master Item
     ├─ Master User & Role
     ├─ Master Privilege
     ├─ Master Harga
     ├─ Master UoM
     ├─ Master Metode Pembayaran
     ├─ Semua Transaksi (read/monitor)
     ├─ Report Penjualan
     └─ Report Stock
```

**Aturan navigasi:**
- Menu sidebar hanya menampilkan item yang sesuai privilege role — bukan ditampilkan lalu di-disable. Menu yang tidak boleh diakses tidak perlu terlihat sama sekali.
- Setelah login, user langsung diarahkan ke halaman kerja utamanya (Kasir → form order baru; Admin Stock → daftar item; Superadmin → dashboard/master user).
- Tidak ada mega-menu atau dropdown bertingkat dalam. Maksimal 2 level (kategori menu → sub-halaman).

---

## 4. Design System

### 4.1 Tipografi

Satu font family untuk seluruh sistem, dengan font monospace **hanya** untuk angka pada tabel finansial (nomor invoice, harga, qty) agar mudah dibandingkan secara visual (align digit).

| Elemen | Font | Ukuran | Weight |
|---|---|---|---|
| Font utama | Inter (atau system-ui sebagai fallback) | — | 400 / 500 / 600 |
| Angka di tabel (harga, qty, total) | ui-monospace / "SF Mono" / Consolas (fallback system) | — | 400 |
| H1 (judul halaman) | Inter | 20px | 600 |
| H2 (judul section/card) | Inter | 16px | 600 |
| Body / label form | Inter | 14px | 400 |
| Table header | Inter | 13px | 600 (uppercase, letter-spacing kecil) |
| Table cell | Inter | 14px | 400 |
| Caption / helper text | Inter | 12px | 400 |

Tidak menggunakan font dekoratif atau serif di bagian manapun. Satu skala tipografi dipakai konsisten di semua halaman — tidak ada halaman dengan judul font berbeda.

### 4.2 Warna

Palet terbatas: satu warna netral (untuk 90% UI), satu warna aksen (untuk aksi utama), dan warna status standar (jangan dimodifikasi jadi gradien).

| Token | Hex | Penggunaan |
|---|---|---|
| `--bg` | `#F7F8FA` | Background halaman |
| `--surface` | `#FFFFFF` | Card, tabel, form |
| `--border` | `#E2E4E9` | Garis pembatas, border input/tabel |
| `--text-primary` | `#1A1D23` | Teks utama |
| `--text-secondary` | `#6B7280` | Label, helper text, placeholder |
| `--accent` | `#2563EB` | Tombol utama, link, elemen aktif (satu-satunya warna aksen) |
| `--accent-hover` | `#1D4ED8` | Hover state tombol aksen |
| `--success` | `#16A34A` | Transaksi berhasil, stok cukup |
| `--warning` | `#D97706` | Stok menipis, peringatan |
| `--danger` | `#DC2626` | Stok ditolak/minus, error, hapus |
| `--disabled` | `#D1D5DB` | Elemen nonaktif |

Aturan: **tidak ada gradien** — semua warna solid flat. Warna status (success/warning/danger) hanya dipakai pada badge, teks validasi, dan indikator baris tabel — bukan sebagai warna dekoratif tombol biasa.

### 4.3 Spacing & Grid

- Basis spacing 4px (4 / 8 / 12 / 16 / 24 / 32).
- Container utama max-width 1280px, dengan sidebar tetap 220px di kiri.
- Padding card/section: 16–24px, tidak lebih (hindari card kosong yang "mengambang" dengan whitespace berlebihan).
- Border-radius konsisten kecil: 6px untuk card/input, 4px untuk badge — bukan rounded penuh (pill) di semua tempat.

### 4.4 Elevasi

Tanpa drop-shadow berlapis. Cukup:
- Card: `border: 1px solid var(--border)` — tanpa shadow, atau shadow sangat tipis (`0 1px 2px rgba(0,0,0,0.04)`) hanya untuk modal/dropdown agar terlihat "mengambang" di atas konten.
- Modal/dialog: shadow sedikit lebih terlihat (`0 4px 12px rgba(0,0,0,0.1)`) karena perlu terlihat terpisah dari background.

### 4.5 Ikon

Ikon dipakai **secukupnya**, hanya di tempat yang benar-benar mempercepat pemindaian visual:
- Ikon status di tabel (✓ berhasil, ⚠ stok menipis) — pakai set ikon tunggal (mis. Lucide/Feather), garis tipis (outline), bukan filled/3D.
- Ikon di sidebar navigasi — 1 ikon per menu, ukuran konsisten 18–20px.
- **Tidak** ada ikon di setiap tombol, setiap label form, atau setiap heading. Tombol teks cukup dengan teks ("Simpan", "Tambah Item") tanpa ikon dekoratif tambahan kecuali aksi destruktif (hapus) yang boleh diberi ikon tempat sampah untuk mengurangi risiko salah klik.

---

## 5. Pola Layout per Halaman

### 5.1 Login
- Form sederhana di tengah layar: logo/nama toko, field username, field password, tombol "Masuk".
- Tidak ada background image besar, ilustrasi, atau gradien — cukup background warna netral `--bg`.
- Pesan error validasi tampil jelas di atas form (bukan hanya warna merah di border input).

### 5.2 Kasir — Buat Order (halaman paling sering dipakai)
Layout 2 kolom agar kasir bisa bekerja cepat tanpa berpindah halaman:

```
┌─────────────────────────────┬───────────────────┐
│  Pencarian & Daftar Item     │  Ringkasan Order   │
│  (search box + tabel item,   │  (item terpilih,   │
│   klik/tambah ke order)      │   qty, subtotal,   │
│                               │   pajak 11%, total)│
│                               │                    │
│                               │  Metode Pembayaran │
│                               │  [Cash][Debit]...  │
│                               │                    │
│                               │  [ Proses Order ]  │
└─────────────────────────────┴───────────────────┘
```
- Kolom kanan (ringkasan order) **sticky**, selalu terlihat saat kolom kiri di-scroll.
- Baris item di ringkasan order langsung menunjukkan qty vs stok tersedia; jika qty melebihi stok, baris ditandai warna `--danger` dan tombol "Proses Order" nonaktif dengan pesan jelas — bukan hanya toast yang hilang cepat.
- Total & pajak ditampilkan dengan font monospace agar angka mudah dibandingkan.
- Setelah order berhasil: tampilkan struk dalam layout print-friendly terpisah (bukan modal kecil yang sulit dibaca), dengan nomor invoice jelas di bagian atas.

### 5.3 Admin Stock — Daftar Item & Stock Masuk
- Tabel data sebagai elemen utama (bukan card grid) — cocok untuk data tabular dengan banyak baris.
- Kolom tabel: SKU, Nama Item, Kategori, UoM, Stok Saat Ini, Harga, Aksi.
- Baris dengan stok rendah (di bawah ambang tertentu) diberi badge kuning `--warning`, stok habis diberi badge merah `--danger` — **bukan** seluruh baris diwarnai gradien, cukup badge kecil di kolom stok.
- Form "Tambah Stock Masuk" berupa panel/modal ringkas: pilih item, jumlah masuk, keterangan — tanpa field yang tidak perlu.
- Riwayat pergerakan stok: tabel kronologis (tanggal, item, jenis pergerakan masuk/keluar, qty, user, referensi transaksi).

### 5.4 Superadmin — Master Data & Report
- Semua master data (Item, User, Role, Privilege, Harga, UoM, Metode Pembayaran) memakai **pola tabel + form yang identik**: tabel list di atas dengan tombol "Tambah" di kanan atas, form tambah/edit dalam modal atau panel samping — konsisten di semua modul agar user tidak perlu belajar pola baru per menu.
- Report Penjualan & Report Stock: filter tanggal di bagian atas (format dd/mm/yyyy sesuai PRD), tabel hasil di bawah, tombol export jika diperlukan. Tidak perlu chart/grafik dekoratif kecuali benar-benar diminta — angka dalam tabel sudah cukup untuk kebutuhan operasional toko material.

---

## 6. Komponen UI Standar

| Komponen | Aturan |
|---|---|
| Tombol utama | Solid `--accent`, teks putih, tanpa gradien, satu ukuran per konteks (besar untuk aksi utama seperti "Proses Order", biasa untuk aksi sekunder) |
| Tombol sekunder | Outline/border `--border`, teks `--text-primary`, background transparan |
| Tombol destruktif | Solid/outline `--danger`, dipakai hanya untuk hapus/batalkan |
| Input & select | Border 1px `--border`, radius 6px, focus state ring tipis `--accent` — tanpa efek "glow" berlebihan |
| Badge status | Radius 4px, warna solid tipis (background pucat + teks warna status), bukan warna penuh mencolok |
| Tabel | Header sticky saat scroll panjang, border horizontal tipis antar baris (tanpa border vertikal penuh agar tidak terlihat seperti spreadsheet Excel lama), hover row highlight ringan |
| Modal | Maksimal 1 tingkat (tidak ada modal di atas modal), overlay gelap tipis, tombol aksi selalu di kanan bawah dengan urutan konsisten (Batal di kiri, Simpan/Konfirmasi di kanan) |
| Notifikasi/toast | Dipakai untuk konfirmasi ringan (berhasil disimpan). Untuk error yang butuh tindakan (stok tidak cukup), gunakan pesan inline di form — bukan toast yang bisa terlewat |

---

## 7. Aksesibilitas & Keandalan Visual

- Kontras teks terhadap background minimal memenuhi WCAG AA (rasio 4.5:1 untuk teks body).
- Status tidak hanya disampaikan lewat warna — selalu disertai teks/label (mis. bukan hanya titik merah, tapi "Stok Habis").
- Ukuran target klik (tombol, baris tabel dengan aksi) minimal 36–40px tinggi agar nyaman dipakai kasir yang bekerja cepat.
- Semua form field wajib punya label statis di atas input (bukan hanya placeholder yang hilang saat diisi).

---

## 8. Ringkasan Keputusan Desain

| Keputusan | Alasan |
|---|---|
| 1 font family, monospace hanya untuk angka | Konsistensi + kemudahan baca angka finansial |
| 1 warna aksen, tanpa gradien | Sistem internal butuh kejelasan, bukan branding mencolok |
| Layout berbasis tabel, bukan card grid | Data toko material bersifat tabular dan padat |
| Ikon minim, hanya di navigasi & status | Mengurangi noise visual, mempercepat pemindaian |
| Desktop-first, bukan mobile-first | Kasir & admin bekerja dari perangkat tetap di toko |
| Validasi inline, bukan toast untuk error kritikal | Error stok/transaksi tidak boleh terlewat oleh kasir |
