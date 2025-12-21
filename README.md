# 🍞 Sae Bakery Inventory System

> **Enterprise-Grade Inventory Management untuk Industri Bakery**

Sistem manajemen inventori lengkap yang dirancang khusus untuk bisnis bakery dan UKM makanan. Dilengkapi dengan kalkulasi HPP real-time, pelacakan produksi, dan laporan keuangan yang akurat.

---

## 📋 Daftar Isi

- [Fitur Unggulan](#-fitur-unggulan)
- [Teknologi](#-teknologi)
- [Panduan Instalasi Lokal](#-panduan-instalasi-lokal)
- [Panduan Deployment Server](#-panduan-deployment-server)
- [Akun Demo](#-akun-demo)
- [Struktur Folder](#-struktur-folder)
- [Kontribusi](#-kontribusi)

---

## ✨ Fitur Unggulan

### 🧮 Smart Inventory (Kalkulasi HPP Otomatis)
- **3 Metode Akuntansi:** FIFO, LIFO, dan AVERAGE
- **Real-time Switching:** Pimpinan bisa ganti metode langsung dari dashboard
- **Batch Tracking:** Setiap transaksi tercatat per batch dengan harga masing-masing
- **Auto HPP Calculation:** Sistem otomatis hitung Harga Pokok Penjualan

### 🏭 Manufacturing Logic (Produksi Barang Jadi)
- **Auto-Deduct Ingredients:** Input produksi → bahan baku otomatis terpotong
- **Cost Accumulation:** Total biaya produksi dihitung dari semua bahan yang terpakai
- **HPP Per Unit:** Sistem kalkulasi HPP/unit = Total Biaya ÷ Qty Produksi
- **Multi-Level BOM:** Support Bahan Baku → WIP → Barang Jadi

### 📊 Dynamic Reporting
- **Laporan Laba Rugi:** Penjualan - HPP = Laba Kotor (dengan margin %)
- **Riwayat Transaksi Stok:** Semua pergerakan masuk/keluar tercatat
- **Kartu Stok:** Detail per produk dengan running balance
- **Status Barang:** Monitoring stok rendah secara real-time
- **Filter Tanggal & Kategori:** Semua laporan bisa difilter

### 🔐 Role-Based Access Control (RBAC)
| Role | Akses |
|------|-------|
| **Pimpinan** | Full access + ubah metode akuntansi + lihat laporan laba |
| **Admin** | Input transaksi + lihat laporan (tanpa laba) |
| **Karyawan** | Input transaksi dasar |

### 📦 Stock Opname (Penyesuaian Stok)
- **Input Stok Fisik:** Bandingkan stok sistem vs stok aktual
- **Workflow Draft → Finalize:** Karyawan input draft, Admin/Pimpinan approve
- **Auto-Adjustment:** Sistem otomatis buat batch penyesuaian (plus/minus)
- **History Tracking:** Semua opname tercatat dengan detail selisih

---

## 🛠 Teknologi

| Komponen | Teknologi |
|----------|-----------|
| **Backend** | Laravel 12 (PHP 8.2+) |
| **Frontend** | Vue 3 (Composition API) + Inertia.js |
| **Styling** | Tailwind CSS 4 |
| **Database** | MySQL 8.0 |
| **Auth** | Laravel Breeze (Session) |
| **Build Tool** | Vite |

---

## 🚀 Panduan Instalasi Lokal

### Prerequisites
- PHP 8.2 atau lebih tinggi
- Composer 2.x
- Node.js 18+ dan NPM
- MySQL 8.0 / MariaDB 10.6+

### Langkah Instalasi

```bash
# 1. Clone repository
git clone https://github.com/your-repo/sae-bakery.git
cd sae-bakery

# 2. Install dependencies PHP
composer install

# 3. Install dependencies JavaScript
npm install

# 4. Copy file environment
cp .env.example .env

# 5. Generate application key
php artisan key:generate

# 6. Konfigurasi database di .env
# DB_DATABASE=sae_bakery
# DB_USERNAME=root
# DB_PASSWORD=

# 7. Jalankan migrasi dan seeder
php artisan migrate --seed

# 8. (Opsional) Jalankan SimulationSeeder untuk data testing 2 minggu
php artisan db:seed --class=SimulationSeeder

# 9. Build assets frontend
npm run build

# 10. Jalankan development server
php artisan serve
npm run dev  # Di terminal terpisah untuk hot-reload
```

### Akses Aplikasi
Buka browser: `http://127.0.0.1:8000`

---

## 🌐 Panduan Deployment Server

### Environment yang Direkomendasikan
- **VPS/Cloud:** Proxmox LXC, DigitalOcean, Vultr, atau AWS Lightsail
- **OS:** Ubuntu 22.04 LTS / Debian 12
- **Control Panel:** aaPanel (Gratis, mudah dipakai)
- **Web Server:** Nginx
- **PHP:** 8.2 dengan ekstensi: BCMath, Ctype, JSON, Mbstring, OpenSSL, PDO, Tokenizer, XML

### Langkah Deployment dengan aaPanel

#### 1. Setup Server
```bash
# Install aaPanel (Ubuntu/Debian)
wget -O install.sh http://www.aapanel.com/script/install-ubuntu_6.0_en.sh && bash install.sh aapanel

# Akses panel via browser, install:
# - Nginx
# - MySQL 8.0
# - PHP 8.2
# - phpMyAdmin
```

#### 2. Upload Project
```bash
# Upload ke folder
/www/wwwroot/sae-bakery/

# Atau clone langsung
cd /www/wwwroot/
git clone https://github.com/your-repo/sae-bakery.git
```

#### 3. Set Permissions
```bash
cd /www/wwwroot/sae-bakery
chown -R www-data:www-data .
chmod -R 755 storage bootstrap/cache
```

#### 4. Setup Nginx di aaPanel
1. Tambah Website baru
2. Domain: `sae-bakery.com` (atau subdomain)
3. Root Directory: `/www/wwwroot/sae-bakery/public`
4. PHP Version: 8.2
5. **URL Rewrite:** Pilih `Laravel 5` (penting!)

#### 5. Konfigurasi Environment
```bash
cd /www/wwwroot/sae-bakery
cp .env.example .env
php artisan key:generate

# Edit .env
nano .env
# Set APP_ENV=production
# Set APP_DEBUG=false
# Set database credentials
```

#### 6. Install Dependencies & Build
```bash
composer install --optimize-autoloader --no-dev
npm install
npm run build
php artisan migrate --seed
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

#### 7. (Opsional) Setup Queue dengan Supervisor
Jika menggunakan background jobs:
```bash
# Install supervisor
apt install supervisor

# Buat config
nano /etc/supervisor/conf.d/sae-bakery-worker.conf

# Isi:
[program:sae-bakery-worker]
process_name=%(program_name)s_%(process_num)02d
command=php /www/wwwroot/sae-bakery/artisan queue:work --sleep=3 --tries=3
autostart=true
autorestart=true
user=www-data
numprocs=1
redirect_stderr=true
stdout_logfile=/www/wwwroot/sae-bakery/storage/logs/worker.log

# Reload supervisor
supervisorctl reread
supervisorctl update
supervisorctl start sae-bakery-worker:*
```

---

## 👤 Akun Demo

Setelah menjalankan seeder, gunakan akun berikut untuk login:

| Role | Email | Password |
|------|-------|----------|
| **Pimpinan** | `pimpinan@saebakery.com` | `password` |
| **Admin** | `admin@saebakery.com` | `password` |
| **Karyawan** | `karyawan@saebakery.com` | `password` |

> ⚠️ **Penting:** Ganti password default sebelum production!

---

## 📁 Struktur Folder

```
sae-bakery/
├── app/
│   ├── Http/Controllers/     # Controller Laravel
│   ├── Models/               # Eloquent Models
│   └── Services/             # Business Logic (InventoryService)
├── database/
│   ├── migrations/           # Struktur tabel
│   └── seeders/              # Data awal & simulasi
├── resources/
│   ├── js/
│   │   ├── Components/       # Komponen Vue reusable
│   │   ├── Layouts/          # Layout utama (SaeLayout)
│   │   └── Pages/            # Halaman Vue (per fitur)
│   └── css/                  # Tailwind CSS
├── routes/
│   └── web.php               # Definisi routes
└── public/
    └── build/                # Assets hasil build
```

---

## 🔧 Troubleshooting

### Error: "Stok tidak mencukupi"
- Pastikan sudah input stok awal via menu "Input Saldo Awal"
- Cek apakah batch sudah habis terpakai

### Filter tidak berfungsi
```bash
php artisan cache:clear
php artisan view:clear
npm run build
```

### Metode akuntansi tidak berubah
- Pastikan login sebagai **Pimpinan**
- Cek setting di database (`settings` table)

---

## 📝 Changelog

### v1.0.0 (Desember 2024)
- Initial release
- Fitur: FIFO/LIFO/AVERAGE switching
- Fitur: Manufacturing dengan auto-deduct
- Fitur: Laporan lengkap dengan filter
- Fitur: RBAC (Pimpinan, Admin, Karyawan)

---

## 🤝 Kontribusi

Pull request selalu welcome. Untuk perubahan besar, silakan buka issue terlebih dahulu untuk diskusi.

---

## 📄 Lisensi

Proprietary - Sae Bakery © 2024

---

<p align="center">
  <strong>Dibuat dengan ❤️ untuk Sae Bakery</strong>
</p>
