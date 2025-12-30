# 🍞 Sae Bakery Inventory System (v2.0)

[![Laravel](https://img.shields.io/badge/Laravel-12-FF2D20?style=for-the-badge&logo=laravel&logoColor=white)](https://laravel.com)
[![Vue.js](https://img.shields.io/badge/Vue.js-3-4FC08D?style=for-the-badge&logo=vue.js&logoColor=white)](https://vuejs.org)
[![Inertia.js](https://img.shields.io/badge/Inertia.js-2.0-9553E9?style=for-the-badge&logo=inertia&logoColor=white)](https://inertiajs.com)
[![Tailwind CSS](https://img.shields.io/badge/Tailwind-3.x-38B2AC?style=for-the-badge&logo=tailwind-css&logoColor=white)](https://tailwindcss.com)
[![MySQL](https://img.shields.io/badge/MySQL-8.0-4479A1?style=for-the-badge&logo=mysql&logoColor=white)](https://mysql.com)
[![Docker](https://img.shields.io/badge/Docker-Ready-2496ED?style=for-the-badge&logo=docker&logoColor=white)](https://docker.com)
[![PHP](https://img.shields.io/badge/PHP-8.4-777BB4?style=for-the-badge&logo=php&logoColor=white)](https://php.net)

**Enterprise-Grade Inventory Management System** untuk bisnis bakery/F&B dengan kalkulasi HPP otomatis, laporan keuangan terintegrasi, dan auto carry-forward untuk periode bulanan.

---

## ✨ Fitur Utama

### 📊 Dashboard & Analytics
- **Real-time Monitoring** - Tracking stok, nilai aset, dan profit harian
- **Sales Trend Visualization** - Grafik penjualan 30 hari terakhir
- **Low Stock Alerts** - Notifikasi otomatis untuk stok menipis

### 📦 Master Data Management
- **Product Management** - Kategori: Bahan Baku (BB), Barang Dalam Proses (BDP), Barang Jadi (BJ)
- **Auto-Generated SKU** - Kode barang otomatis berdasarkan kategori
- **Supplier & Customer Database** - Manajemen relasi bisnis
- **Department Tracking** - Multi-departemen untuk organisasi yang lebih besar

### 🔄 Transaksi Lengkap
| Modul | Deskripsi |
|-------|-----------|
| **Pembelian Bahan Baku** | Input pembelian dengan tracking supplier dan harga beli |
| **WIP Entry** | Pencatatan barang dalam proses produksi |
| **Produksi Barang Jadi** | Konversi bahan baku + WIP menjadi finished goods |
| **Penjualan** | Pencatatan penjualan dengan kalkulasi profit otomatis |
| **Pemakaian** | Tracking penggunaan bahan untuk produksi |

### 💰 Metode Valuasi Inventory
- **FIFO (First-In First-Out)** - Stok masuk pertama keluar pertama
- **LIFO (Last-In First-Out)** - Stok masuk terakhir keluar pertama
- **Average Cost** - Harga rata-rata tertimbang
- **Switching Method** - Ganti metode valuasi real-time (hanya Pimpinan)

### 📋 Stock Opname & Adjustment
- **Physical Count Recording** - Input hasil perhitungan fisik
- **Variance Analysis** - Otomatis hitung selisih sistem vs fisik
- **Audit Trail** - History lengkap semua penyesuaian
- **Adjustment Impact** - Real-time update HPP setelah adjustment

### 📑 Reporting Suite

#### Core Reports
- **Laporan Data Barang** - Daftar produk dengan stok dan harga
- **Riwayat Transaksi Stok** - Timeline lengkap semua mutasi
- **Kartu Stok** - Movement history per produk
- **Status Barang** - Current stock levels dengan alert

#### Financial Reports
- **Laporan Penjualan & Laba** - Revenue, COGS, Gross Profit per transaksi
- **Analisis Profit Margin** - Persentase keuntungan per produk
- **Laporan Nilai Aset** - Total value inventory based on HPP

#### 🆕 New Feature: Monthly Stock Movement Report
- **Auto Carry-Forward** - Saldo akhir bulan lalu otomatis jadi saldo awal bulan ini
- **Dual Valuation Display** - Toggle antara HPP dan Harga Jual untuk barang keluar
- **Period Comparison** - Filter berdasarkan bulan dan tahun
- **Excel Export** - Download laporan dengan formatting profesional

### 🔐 Role-Based Access Control (RBAC)

| Role | Permissions |
|------|-------------|
| **Pimpinan** | Full access + Change inventory method + View all financial reports |
| **Admin** | Manage transactions + Stock opname + View reports (kecuali profit) |
| **Karyawan** | Input transaksi only + View basic reports |

### 🎨 User Interface
- **Dark Mode Support** - Tema gelap untuk kenyamanan mata
- **Responsive Design** - Mobile-friendly untuk tablet dan smartphone
- **Material Design Icons** - Google Material Symbols
- **Smooth Animations** - Transisi halus dan loading states

---

## 🛠️ Tech Stack

```
Backend Framework : Laravel 12 (PHP 8.4)
Frontend Framework: Vue.js 3 + Composition API
SPA Router        : Inertia.js 2.0
Styling           : Tailwind CSS 3.x
Database          : MySQL 8.0
Web Server        : Nginx (Alpine Linux)
Containerization  : Docker & Docker Compose
Export Library    : Maatwebsite/Excel (Laravel Excel)
PDF Generation    : DomPDF
```

---

## 🚀 Quick Start (Docker)

### Prerequisites
- [Docker](https://docs.docker.com/get-docker/) (v20.10+)
- [Docker Compose](https://docs.docker.com/compose/install/) (v2.0+)
- Git

### Installation Steps

```bash
# 1. Clone repository
git clone https://github.com/indramaulin76/SOP-INVENTORY-2.0.git
cd SOP-INVENTORY-2.0

# 2. Copy environment file
cp .env.example .env
```

**⚙️ Edit `.env` - Configure Database:**
```env
APP_NAME="Sae Bakery"
APP_URL=http://localhost:8000

DB_CONNECTION=mysql
DB_HOST=master-mysql   # Sesuaikan dengan nama container database
DB_PORT=3306
DB_DATABASE=sae_inventory
DB_USERNAME=sae_user
DB_PASSWORD=secret123
```

```bash
# 3. Create external network (if using shared database)
docker network create jaringan-pusat

# 4. Build and start containers
docker compose up -d --build

# 5. Wait for containers to be ready (check logs)
docker compose logs -f app

# 6. Install PHP dependencies
docker compose exec app composer install

# 7. Generate application key
docker compose exec app php artisan key:generate

# 8. Run migrations and seeders
docker compose exec app php artisan migrate:fresh --seed

# 9. Install Node.js dependencies
docker compose exec app npm install

# 10. Build frontend assets
docker compose exec app npm run build

# 11. Fix permissions
docker compose exec app chown -R www-data:www-data /var/www/storage /var/www/public/build

# 12. Clear all cache
docker compose exec app php artisan optimize:clear
```

### ✅ Access Application

Open browser: **http://localhost:8000**

---

## 🔑 Default Credentials

| Role | Email | Password |
|------|-------|----------|
| **Pimpinan** | `pimpinan@saebakery.com` | `password` |
| **Admin** | `admin@saebakery.com` | `password` |
| **Karyawan** | `karyawan@saebakery.com` | `password` |

> ⚠️ **Critical:** Change default passwords immediately in production!

---

## 📁 Project Structure

```
SOP-INVENTORY-2.0/
├── app/
│   ├── Http/Controllers/       # Business logic controllers
│   │   ├── ReportController.php           # Report generation
│   │   ├── ReportExportController.php     # Excel/PDF exports
│   │   ├── ProductController.php          # Product CRUD
│   │   ├── PurchaseRawMaterialController.php
│   │   ├── SalesFinishedGoodsController.php
│   │   └── StockOpnameController.php
│   ├── Models/                 # Eloquent ORM models
│   │   ├── Product.php
│   │   ├── InventoryBatch.php            # Batch tracking with FIFO/LIFO
│   │   ├── SalesFinishedGoodsItem.php
│   │   └── ...
│   ├── Services/               # Business logic services
│   │   └── InventoryService.php          # HPP calculation, stock management
│   └── Exports/                # Excel export classes
│       ├── MutasiStokExport.php          # Monthly stock movement
│       ├── StockHistoryExport.php
│       └── ProfitLossExport.php
├── database/
│   ├── migrations/             # Database schema
│   └── seeders/                # Initial data
│       ├── MasterDataSeeder.php
│       └── UserSeeder.php
├── resources/
│   ├── js/
│   │   ├── Pages/              # Vue components (Inertia pages)
│   │   │   ├── Dashboard.vue
│   │   │   ├── LaporanMutasiStok.vue      # NEW: Monthly report
│   │   │   ├── RiwayatTransaksiStok.vue
│   │   │   └── ...
│   │   └── Layouts/
│   │       └── SaeLayout.vue              # Main layout with sidebar
│   └── css/
│       └── app.css                        # Tailwind styles
├── docker/
│   └── nginx/
│       └── conf.d/
│           └── app.conf                   # Nginx configuration
├── Dockerfile                             # Multi-stage PHP + Node.js
├── docker-compose.yml                     # Service orchestration
├── .env.example                           # Environment template
├── tailwind.config.js                     # Tailwind configuration
├── vite.config.js                         # Vite bundler config
└── README.md
```

---

## 🔧 Troubleshooting

### ❌ Problem: Menu "Mutasi Stok Bulanan" tidak muncul

**Solution:**
```bash
# Rebuild frontend assets inside Docker container
docker compose exec app npm run build

# Clear browser cache (Hard refresh)
# Chrome/Edge: Ctrl+Shift+R
# Firefox: Ctrl+F5
# Mac: Cmd+Shift+R

# Verify build was successful
docker compose exec app ls -la /var/www/public/build/assets/LaporanMutasiStok*
```

---

### ❌ Problem: 502 Bad Gateway / Connection Refused

**Cause:** Nginx cannot connect to PHP-FPM (hardcoded IP changed)

**Solution:**
```bash
# Check nginx config uses service name instead of IP
cat docker/nginx/conf.d/app.conf | grep fastcgi_pass
# Should show: fastcgi_pass app:9000;

# If showing IP (172.x.x.x), edit file and change to:
# fastcgi_pass app:9000;

# Restart nginx
docker compose restart web
```

---

### ❌ Problem: White Screen / Assets Not Loading

**Cause:** Build assets not generated or permission issues

**Solution:**
```bash
# Rebuild assets
docker compose exec app npm run build

# Fix permissions
docker compose exec app chown -R www-data:www-data /var/www/public/build

# Clear all cache
docker compose exec app php artisan optimize:clear
docker compose exec app php artisan view:clear

# Restart containers
docker compose restart
```

---

### ❌ Problem: Database Connection Refused

**Cause:** Database container not ready or wrong configuration

**Solution:**
```bash
# Check container status
docker compose ps

# Verify .env DB_HOST matches service name
# If using external DB: DB_HOST=master-mysql
# If using local DB: DB_HOST=db

# Check database container logs
docker compose logs db

# Restart all services
docker compose down
docker compose up -d

# Wait 30 seconds for DB to initialize
sleep 30
docker compose exec app php artisan migrate:fresh --seed
```

---

## 📜 Useful Commands

### Docker Management
```bash
# Enter app container
docker compose exec app bash

# View real-time logs
docker compose logs -f

# View specific service logs
docker compose logs -f app
docker compose logs -f web

# Restart all services
docker compose restart

# Stop and remove containers
docker compose down

# Stop and remove including volumes (⚠️ deletes data)
docker compose down -v

# Rebuild from scratch
docker compose build --no-cache
docker compose up -d --build
```

### Laravel Artisan
```bash
# Clear all cache
docker compose exec app php artisan optimize:clear

# Run migrations
docker compose exec app php artisan migrate

# Fresh install with seed
docker compose exec app php artisan migrate:fresh --seed

# Check routes
docker compose exec app php artisan route:list

# Generate IDE helper (for development)
docker compose exec app php artisan ide-helper:generate
```

### Frontend Development
```bash
# Install dependencies
docker compose exec app npm install

# Build for production
docker compose exec app npm run build

# Development with hot reload (outside Docker)
npm run dev
```

---

## 📊 Database Schema Overview

### Core Tables
- `products` - Master produk (BB, BDP, BJ, Kemasan)
- `inventory_batches` - Batch tracking untuk FIFO/LIFO
- `categories` - Kategori produk
- `units` - Satuan (kg, pcs, box, dll)

### Transaction Tables
- `purchase_raw_materials` + `purchase_raw_material_items`
- `wip_entries` + `wip_entry_items`
- `finished_goods_productions` + related pivot tables
- `sales_finished_goods` + `sales_finished_goods_items`
- `usage_raw_materials` + `usage_raw_material_items`
- `stock_opnames` + `stock_opname_items`

### Master Data
- `customers` - Data pelanggan
- `suppliers` - Data pemasok
- `departments` - Departemen untuk tracking
- `users` - User dengan role-based access
- `settings` - Konfigurasi aplikasi (inventory method)

---

## 🎯 Roadmap & Future Features

- [ ] **Barcode/QR Scanning** - Mobile app integration
- [ ] **Multi-warehouse Support** - Manage multiple locations
- [ ] **Purchase Order (PO) System** - Procurement workflow
- [ ] **Production Planning** - MRP (Material Requirements Planning)
- [ ] **API Integration** - RESTful API untuk third-party apps
- [ ] **Advanced Analytics** - Predictive analytics dan forecasting
- [ ] **WhatsApp Notifications** - Stock alerts via WA Business API
- [ ] **E-commerce Integration** - Sync dengan Tokopedia/Shopee

---

## 🤝 Contributing

Contributions are welcome! Please follow these steps:

1. Fork the repository
2. Create feature branch (`git checkout -b feature/AmazingFeature`)
3. Commit changes (`git commit -m 'Add some AmazingFeature'`)
4. Push to branch (`git push origin feature/AmazingFeature`)
5. Open a Pull Request

---

## 📄 License

This project is licensed under the [MIT License](LICENSE).

---

## 👨‍💻 Developer

**Indra Maulin**  
[![GitHub](https://img.shields.io/badge/GitHub-indramaulin76-181717?style=flat-square&logo=github)](https://github.com/indramaulin76)
[![Email](https://img.shields.io/badge/Email-Contact-EA4335?style=flat-square&logo=gmail)](mailto:indramaulin76@gmail.com)

---

## 🙏 Acknowledgments

- Laravel Framework - Elegant PHP framework
- Vue.js - Progressive JavaScript framework
- Inertia.js - Modern monolith SPA approach
- Tailwind CSS - Utility-first CSS framework
- Maatwebsite/Excel - Excel export library

---

<p align="center">
  <sub>Built with ❤️ for Indonesian SME Bakeries</sub><br>
  <sub>© 2025 Sae Bakery Inventory System</sub>
</p>
