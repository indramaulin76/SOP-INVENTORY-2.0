# 🍞 Sae Bakery Inventory System (v2.0)

[![Laravel](https://img.shields.io/badge/Laravel-12-FF2D20?style=for-the-badge&logo=laravel&logoColor=white)](https://laravel.com)
[![Vue.js](https://img.shields.io/badge/Vue.js-3-4FC08D?style=for-the-badge&logo=vue.js&logoColor=white)](https://vuejs.org)
[![Inertia.js](https://img.shields.io/badge/Inertia.js-2.0-9553E9?style=for-the-badge&logo=inertia&logoColor=white)](https://inertiajs.com)
[![Tailwind CSS](https://img.shields.io/badge/Tailwind-3.x-38B2AC?style=for-the-badge&logo=tailwind-css&logoColor=white)](https://tailwindcss.com)
[![MySQL](https://img.shields.io/badge/MySQL-8.0-4479A1?style=for-the-badge&logo=mysql&logoColor=white)](https://mysql.com)
[![Docker](https://img.shields.io/badge/Docker-Ready-2496ED?style=for-the-badge&logo=docker&logoColor=white)](https://docker.com)
[![PHP](https://img.shields.io/badge/PHP-8.4-777BB4?style=for-the-badge&logo=php&logoColor=white)](https://php.net)

**Sistem Manajemen Inventori Modern** untuk bisnis bakery/F&B dengan kalkulasi HPP otomatis dan laporan keuangan terintegrasi.

![Dashboard Preview](https://via.placeholder.com/800x400?text=Sae+Bakery+Dashboard)

---

## ✨ Fitur Utama

| Fitur | Deskripsi |
|-------|-----------|
| 📊 **Dashboard Real-time** | Monitoring stok, nilai aset, dan transaksi terkini |
| 📦 **Master Data** | Kelola Produk, Supplier, Customer, Departemen |
| 🔄 **Transaksi Lengkap** | Pembelian Bahan Baku, Produksi WIP, Barang Jadi, Penjualan |
| 💰 **Metode Valuasi FIFO** | First-In First-Out untuk kalkulasi HPP akurat |
| 📋 **Stock Opname** | Penyesuaian stok fisik dengan audit trail |
| 📑 **Laporan PDF & Excel** | Kartu Stok, Laba/Rugi, Nilai Aset |
| 🔐 **Role-Based Access** | Pimpinan, Admin, Karyawan dengan hak akses berbeda |

---

## 🛠️ Tech Stack

```
Backend   : Laravel 12 (PHP 8.4)
Frontend  : Vue.js 3 + Inertia.js
Styling   : Tailwind CSS 3.x
Database  : MySQL 8.0
Server    : Nginx (Alpine)
Container : Docker & Docker Compose
```

---

## 🚀 Instalasi (Docker)

### Prasyarat
- [Docker](https://docs.docker.com/get-docker/) (v20.10+)
- [Docker Compose](https://docs.docker.com/compose/install/) (v2.0+)
- Git

### Langkah-langkah

```bash
# 1. Clone repository
git clone https://github.com/indramaulin76/SOP-INVENTORY-2.0.git
cd SOP-INVENTORY-2.0

# 2. Salin file environment
cp .env.example .env
```

**⚙️ Edit `.env` sesuaikan konfigurasi database:**
```env
APP_URL=http://localhost:8080

DB_CONNECTION=mysql
DB_HOST=db
DB_PORT=3306
DB_DATABASE=sae_inventory
DB_USERNAME=sae_user
DB_PASSWORD=secret123
```

```bash
# 3. Build dan jalankan container
docker-compose up -d --build

# 4. Install dependencies PHP
docker-compose exec app composer install

# 5. Generate application key
docker-compose exec app php artisan key:generate

# 6. Jalankan migrasi dan seeder
docker-compose exec app php artisan migrate:fresh --seed

# 7. Install dependencies Node.js & Build assets
docker-compose exec app npm install
docker-compose exec app npm run build

# 8. Fix permissions folder storage
docker-compose exec app chown -R www-data:www-data /var/www/storage /var/www/public/build

# 9. Clear cache (opsional)
docker-compose exec app php artisan optimize:clear
```

### ✅ Akses Aplikasi

Buka browser: **http://localhost:8080**

---

## 🔑 Kredensial Default

| Role | Email | Password |
|------|-------|----------|
| **Pimpinan** | `pimpinan@saebakery.com` | `password` |
| **Admin** | `admin@saebakery.com` | `password` |
| **Karyawan** | `karyawan@saebakery.com` | `password` |

> ⚠️ **Penting:** Segera ganti password setelah deployment production!

---

## 🔧 Troubleshooting

### ❌ White Screen / 404 Assets (CSS/JS tidak muncul)

**Penyebab:** Build assets belum dijalankan atau permission salah.

**Solusi:**
```bash
# Rebuild assets
docker-compose exec app npm run build

# Fix permission
docker-compose exec app chown -R www-data:www-data /var/www/public/build

# Clear semua cache
docker-compose exec app php artisan optimize:clear
docker-compose exec app php artisan view:clear

# Restart container
docker-compose restart
```

---

### ❌ HTTPS Redirect Loop / Mixed Content Error

**Penyebab:** Aplikasi memaksa HTTPS padahal server belum dipasang SSL.

**Solusi:**

1. Pastikan `.env` menggunakan HTTP:
   ```env
   APP_URL=http://localhost:8080
   APP_ENV=local
   ```

2. Matikan force HTTPS di `app/Providers/AppServiceProvider.php`:
   ```php
   // Comment baris ini jika belum ada SSL
   // if($this->app->environment('production')) {
   //     URL::forceScheme('https');
   // }
   ```

3. Clear cache browser (HSTS):
   - Chrome: `chrome://net-internals/#hsts` → Delete domain `localhost`
   - Atau buka dengan Incognito/Private Window

4. Rebuild:
   ```bash
   docker-compose exec app php artisan config:clear
   docker-compose restart
   ```

---

### ❌ Database Connection Refused

**Penyebab:** Container database belum ready atau konfigurasi salah.

**Solusi:**
```bash
# Cek status container
docker-compose ps

# Pastikan DB_HOST di .env adalah nama service (bukan localhost)
DB_HOST=db

# Restart semua
docker-compose down
docker-compose up -d
```

---

## 📁 Struktur Proyek

```
SOP-INVENTORY-2.0/
├── app/
│   ├── Http/Controllers/    # Logic bisnis
│   ├── Models/              # Eloquent models
│   └── Services/            # InventoryService (HPP calculation)
├── database/
│   ├── migrations/          # Struktur database
│   └── seeders/             # Data awal
├── resources/
│   └── js/Pages/            # Vue components (Inertia)
├── docker/
│   └── nginx/conf.d/        # Nginx configuration
├── Dockerfile               # PHP-FPM + Node.js image
├── docker-compose.yml       # Service orchestration
└── .env.example             # Template environment
```

---

## 📜 Perintah Berguna

```bash
# Masuk ke container app
docker-compose exec app bash

# Lihat logs real-time
docker-compose logs -f

# Restart semua service
docker-compose restart

# Stop dan hapus container
docker-compose down

# Stop dan hapus termasuk volume database
docker-compose down -v

# Rebuild image dari awal
docker-compose build --no-cache
```

---

## 📄 Lisensi

Proyek ini dilisensikan di bawah [MIT License](LICENSE).

---

## 👨‍💻 Developer

**Indra Maulin**  
[![GitHub](https://img.shields.io/badge/GitHub-indramaulin76-181717?style=flat-square&logo=github)](https://github.com/indramaulin76)

---

<p align="center">
  <sub>Built with ❤️ for Indonesian SME Bakeries</sub>
</p>
