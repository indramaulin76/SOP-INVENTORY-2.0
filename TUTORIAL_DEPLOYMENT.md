# 📚 Tutorial Lengkap Deploy SOP Inventory ke VPS dengan Docker & HTTPS

**Panduan step-by-step deployment aplikasi SOP Inventory System ke VPS menggunakan Docker dengan MySQL standalone dan HTTPS.**

---

## 📋 Table of Contents

1. [Prerequisites](#prerequisites)
2. [Persiapan VPS](#persiapan-vps)
3. [Setup Domain & SSL](#setup-domain--ssl)
4. [Deploy Aplikasi](#deploy-aplikasi)
5. [Setup Database](#setup-database)
6. [Konfigurasi Nginx Reverse Proxy](#konfigurasi-nginx-reverse-proxy)
7. [Verifikasi & Testing](#verifikasi--testing)
8. [Update & Maintenance](#update--maintenance)
9. [Troubleshooting](#troubleshooting)
10. [Database Management](#database-management)

---

## 1. Prerequisites

### ✅ Yang Harus Ada di VPS:

- **OS**: Ubuntu 20.04/22.04 atau Debian 11/12
- **RAM**: Minimal 2GB (recommended 4GB)
- **Storage**: Minimal 20GB free space
- **Docker**: Version 20.10+
- **Docker Compose**: Version 2.0+
- **Nginx**: Untuk reverse proxy
- **Domain**: Sudah pointing ke IP VPS
- **SSL Certificate**: Cloudflare atau Let's Encrypt

### Cek Versi Docker:

```bash
docker --version
# Docker version 24.0.0 atau lebih tinggi

docker compose version
# Docker Compose version v2.20.0 atau lebih tinggi
```

### Install Docker (Jika Belum Ada):

```bash
# Update package
sudo apt update

# Install dependencies
sudo apt install -y apt-transport-https ca-certificates curl software-properties-common

# Add Docker GPG key
curl -fsSL https://download.docker.com/linux/ubuntu/gpg | sudo gpg --dearmor -o /usr/share/keyrings/docker-archive-keyring.gpg

# Add Docker repository
echo "deb [arch=$(dpkg --print-architecture) signed-by=/usr/share/keyrings/docker-archive-keyring.gpg] https://download.docker.com/linux/ubuntu $(lsb_release -cs) stable" | sudo tee /etc/apt/sources.list.d/docker.list > /dev/null

# Install Docker
sudo apt update
sudo apt install -y docker-ce docker-ce-cli containerd.io docker-compose-plugin

# Start Docker
sudo systemctl start docker
sudo systemctl enable docker

# Add user to docker group (agar tidak perlu sudo)
sudo usermod -aG docker $USER
newgrp docker
```

---

## 2. Persiapan VPS

### 2.1 Login ke VPS

```bash
ssh root@your-vps-ip
# atau
ssh your-username@your-vps-ip
```

### 2.2 Update System

```bash
sudo apt update && sudo apt upgrade -y
```

### 2.3 Install Nginx

```bash
sudo apt install -y nginx
sudo systemctl start nginx
sudo systemctl enable nginx
```

### 2.4 Install Git

```bash
sudo apt install -y git
```

### 2.5 Buat Directory untuk Project

```bash
# Pindah ke directory /opt (or /var/www)
cd /opt

# Atau buat di home directory
# cd ~
```

---

## 3. Setup Domain & SSL

### 3.1 Setting DNS

Di control panel domain (Cloudflare/Domain provider):

```
Type: A
Name: app
Value: YOUR_VPS_IP
TTL: Auto atau 300

Hasil: app.indra-casa.my.id → YOUR_VPS_IP
```

### 3.2 Setup SSL dengan Cloudflare

**Option A: Cloudflare SSL (Recommended)**

1. Login ke Cloudflare Dashboard
2. Pilih domain kamu: `indra-casa.my.id`
3. Go to **SSL/TLS** → **Origin Server**
4. Klik **Create Certificate**
5. Generate certificate (default 15 tahun)
6. Copy **Certificate** dan **Private Key**

Save di VPS:

```bash
# Buat directory untuk SSL
sudo mkdir -p /etc/ssl/cloudflare

# Create certificate file
sudo nano /etc/ssl/cloudflare/cert.pem
# Paste certificate, save (Ctrl+O, Enter, Ctrl+X)

# Create private key file
sudo nano /etc/ssl/cloudflare/key.pem
# Paste private key, save (Ctrl+O, Enter, Ctrl+X)

# Set permissions
sudo chmod 600 /etc/ssl/cloudflare/*.pem
```

**Option B: Let's Encrypt (Free SSL)**

```bash
# Install certbot
sudo apt install -y certbot python3-certbot-nginx

# Generate certificate
sudo certbot --nginx -d app.indra-casa.my.id

# Follow prompts
# Email: your-email@example.com
# Agree to terms: Y
# Redirect HTTP to HTTPS: Y

# Auto-renewal
sudo certbot renew --dry-run
```

---

## 4. Deploy Aplikasi

### 4.1 Clone Repository

```bash
# Pindah ke /opt
cd /opt

# Clone project
git clone https://github.com/indramaulin76/SOP-INVENTORY-2.0.git

# Masuk ke directory project
cd SOP-INVENTORY-2.0
```

### 4.2 Setup Environment File

```bash
# Copy template production environment
cp .env.production.example .env

# Edit file .env
nano .env
```

**Isi file `.env`:**

```env
# Application Settings
APP_NAME="SOP Inventory System"
APP_ENV=production
APP_KEY=  # Akan digenerate otomatis, kosongkan dulu
APP_DEBUG=false
APP_TIMEZONE=Asia/Jakarta

# HTTPS URLs - WAJIB HTTPS!
APP_URL=https://app.indra-casa.my.id
ASSET_URL=https://app.indra-casa.my.id

APP_LOCALE=id
APP_FALLBACK_LOCALE=en

# Logging
LOG_CHANNEL=daily
LOG_LEVEL=error

# Database Configuration
DB_CONNECTION=mysql
DB_HOST=db
DB_PORT=3306
DB_DATABASE=sae_inventory
DB_USERNAME=indra
DB_PASSWORD=GantiDenganPasswordKuatKamu123!
DB_ROOT_PASSWORD=GantiRootPasswordJuga456!

# Session & Cache
SESSION_DRIVER=database
SESSION_LIFETIME=120
SESSION_ENCRYPT=true
SESSION_DOMAIN=.indra-casa.my.id
CACHE_STORE=database

# Security
BCRYPT_ROUNDS=12
```

> **⚠️ PENTING:**
> - Ganti `DB_PASSWORD` dengan password yang kuat!
> - Ganti `DB_ROOT_PASSWORD` dengan password root yang kuat!
> - Pastikan `APP_URL` dan `ASSET_URL` menggunakan `https://`

**Save file:** `Ctrl+O`, `Enter`, `Ctrl+X`

### 4.3 Build Docker Images

```bash
# Build images (butuh waktu 5-10 menit pertama kali)
docker compose build --no-cache
```

**Proses build akan:**
- Install PHP 8.4 + ekstensi Laravel
- Install Node.js 20.x
- Install Composer dependencies
- Install NPM dependencies
- Build frontend assets dengan Vite

**Output sukses:**
```
[+] Building 350.2s (18/18) FINISHED
 => [internal] load build definition from Dockerfile
 => => transferring dockerfile: ...
 ...
 => exporting to image
 => => exporting layers
 => => writing image sha256:...
```

### 4.4 Start Docker Containers

```bash
# Start semua containers (DB + App + Web)
docker compose up -d
```

**Cek status containers:**

```bash
docker compose ps
```

**Output yang benar:**

```
NAME              IMAGE                STATUS        PORTS
sae-bakery-db     mysql:8.0            Up 30s        0.0.0.0:3307->3306/tcp
sae-bakery-app    sop-inventory-app     Up 28s        9000/tcp
sae-bakery-web    nginx:alpine         Up 27s        0.0.0.0:8000->80/tcp
```

---

## 5. Setup Database

### 5.1 Generate Application Key

```bash
docker compose exec app php artisan key:generate --force
```

**Output:**
```
Application key set successfully.
```

### 5.2 Run Database Migrations

```bash
# Jalankan migrations untuk membuat tables
docker compose exec app php artisan migrate --force
```

**Output:**
```
Migration table created successfully.
Migrating: 2014_10_12_000000_create_users_table
Migrated:  2014_10_12_000000_create_users_table (50.33ms)
...
```

### 5.3 Seed Data Awal (Optional)

```bash
# Seed data dummy untuk testing
docker compose exec app php artisan db:seed --force
```

### 5.4 Create Storage Symlink

```bash
docker compose exec app php artisan storage:link
```

### 5.5 Set File Permissions

```bash
docker compose exec app chown -R www-data:www-data /var/www/storage
docker compose exec app chown -R www-data:www-data /var/www/bootstrap/cache
docker compose exec app chmod -R 775 /var/www/storage
docker compose exec app chmod -R 775 /var/www/bootstrap/cache
```

### 5.6 Cache Configuration untuk Production

```bash
# Clear all caches first
docker compose exec app php artisan config:clear
docker compose exec app php artisan cache:clear
docker compose exec app php artisan route:clear
docker compose exec app php artisan view:clear

# Cache untuk production (PENTING untuk performance!)
docker compose exec app php artisan config:cache
docker compose exec app php artisan route:cache
docker compose exec app php artisan view:cache
```

---

## 6. Konfigurasi Nginx Reverse Proxy

### 6.1 Buat File Konfigurasi Nginx

```bash
sudo nano /etc/nginx/sites-available/sop-inventory
```

**Paste konfigurasi berikut:**

```nginx
# Upstream backend (Docker container)
upstream sop_backend {
    server 127.0.0.1:8000;
    keepalive 32;
}

# HTTP: Redirect to HTTPS
server {
    listen 80;
    listen [::]:80;
    server_name app.indra-casa.my.id;
    
    # Redirect all HTTP to HTTPS
    return 301 https://$server_name$request_uri;
}

# HTTPS: Main application
server {
    listen 443 ssl http2;
    listen [::]:443 ssl http2;
    server_name app.indra-casa.my.id;

    # SSL Configuration - PILIH SALAH SATU
    
    # Option A: Cloudflare Origin Certificate
    ssl_certificate /etc/ssl/cloudflare/cert.pem;
    ssl_certificate_key /etc/ssl/cloudflare/key.pem;
    
    # Option B: Let's Encrypt (Comment option A jika pakai ini)
    # ssl_certificate /etc/letsencrypt/live/app.indra-casa.my.id/fullchain.pem;
    # ssl_certificate_key /etc/letsencrypt/live/app.indra-casa.my.id/privkey.pem;
    
    # SSL Settings
    ssl_protocols TLSv1.2 TLSv1.3;
    ssl_ciphers 'ECDHE-ECDSA-AES128-GCM-SHA256:ECDHE-RSA-AES128-GCM-SHA256:ECDHE-ECDSA-AES256-GCM-SHA384:ECDHE-RSA-AES256-GCM-SHA384';
    ssl_prefer_server_ciphers on;
    ssl_session_cache shared:SSL:10m;
    ssl_session_timeout 10m;

    # Security Headers
    add_header X-Frame-Options "SAMEORIGIN" always;
    add_header X-Content-Type-Options "nosniff" always;
    add_header X-XSS-Protection "1; mode=block" always;
    add_header Strict-Transport-Security "max-age=31536000; includeSubDomains; preload" always;
    add_header Referrer-Policy "no-referrer-when-downgrade" always;

    # Logging
    access_log /var/log/nginx/sop-inventory-access.log;
    error_log /var/log/nginx/sop-inventory-error.log warn;

    # Max upload size
    client_max_body_size 20M;
    client_body_buffer_size 128k;

    # Proxy settings
    location / {
        proxy_pass http://sop_backend;
        
        # Headers untuk Laravel detect HTTPS
        proxy_set_header Host $host;
        proxy_set_header X-Real-IP $remote_addr;
        proxy_set_header X-Forwarded-For $proxy_add_x_forwarded_for;
        proxy_set_header X-Forwarded-Proto $scheme;
        proxy_set_header X-Forwarded-Host $host;
        proxy_set_header X-Forwarded-Port $server_port;
        
        # WebSocket support
        proxy_http_version 1.1;
        proxy_set_header Upgrade $http_upgrade;
        proxy_set_header Connection "upgrade";
        
        # Timeouts
        proxy_connect_timeout 60s;
        proxy_send_timeout 60s;
        proxy_read_timeout 60s;
        proxy_buffering off;
    }
}
```

**Save:** `Ctrl+O`, `Enter`, `Ctrl+X`

### 6.2 Aktifkan Konfigurasi

```bash
# Buat symbolic link ke sites-enabled
sudo ln -s /etc/nginx/sites-available/sop-inventory /etc/nginx/sites-enabled/

# Test konfigurasi nginx
sudo nginx -t
```

**Output sukses:**
```
nginx: the configuration file /etc/nginx/nginx.conf syntax is ok
nginx: configuration file /etc/nginx/nginx.conf test is successful
```

### 6.3 Reload Nginx

```bash
sudo systemctl reload nginx

# atau restart
sudo systemctl restart nginx

# Cek status
sudo systemctl status nginx
```

---

## 7. Verifikasi & Testing

### 7.1 Cek Container Logs

```bash
# Lihat logs app container
docker compose logs -f app

# Lihat logs nginx container
docker compose logs -f web

# Lihat logs database
docker compose logs -f db

# Keluar dari logs: Ctrl+C
```

### 7.2 Test Akses Aplikasi

Buka browser dan akses:

```
https://app.indra-casa.my.id/login
```

### 7.3 Verifikasi Browser Console

Buka **Developer Tools** (F12) → **Console Tab**

**✅ Yang HARUS muncul:**
- ✅ **NO "Mixed Content" errors**
- ✅ All assets loaded via `https://`
- ✅ Page loads successfully
- ✅ CSS styling tampil dengan benar
- ✅ JavaScript berfungsi normal

**❌ Jika ada error "Mixed Content":**

Cek file `.env`:
```bash
docker compose exec app cat .env | grep APP_URL
docker compose exec app cat .env | grep ASSET_URL
```

Kedua harus `https://`, bukan `http://`

### 7.4 Test Login

Default credentials (jika sudah seeding):
- **Username**: `admin`
- **Password**: cek di database atau seeder file

### 7.5 Verifikasi Database Connection

```bash
# Test koneksi database
docker compose exec app php artisan db:show
```

**Output:**
```
MySQL 8.0.x ····················· db:3306
Database ···················· sae_inventory
...
```

---

## 8. Update & Maintenance

### 8.1 Update Aplikasi (One Command)

Gunakan script deploy otomatis:

```bash
cd /opt/SOP-INVENTORY-2.0
bash deploy.sh
```

Script akan otomatis:
1. Pull code terbaru dari GitHub
2. Stop containers
3. Rebuild images
4. Start containers
5. Run migrations
6. Clear & cache configs
7. Set permissions

### 8.2 Manual Update

Jika script gagal, update manual:

```bash
# 1. Pull latest code
git pull origin main

# 2. Rebuild containers
docker compose down
docker compose build --no-cache
docker compose up -d

# 3. Run migrations (jika ada)
docker compose exec app php artisan migrate --force

# 4. Clear & cache
docker compose exec app php artisan config:clear
docker compose exec app php artisan cache:clear
docker compose exec app php artisan config:cache
docker compose exec app php artisan route:cache
docker compose exec app php artisan view:cache

# 5. Set permissions
docker compose exec app chown -R www-data:www-data /var/www/storage /var/www/bootstrap/cache
```

### 8.3 Restart Services

```bash
# Restart semua containers
docker compose restart

# Restart specific container
docker compose restart app
docker compose restart web
docker compose restart db
```

### 8.4 View Real-time Logs

```bash
# All containers
docker compose logs -f

# Specific container
docker compose logs -f app
```

---

## 9. Troubleshooting

### Problem 1: Mixed Content Error Masih Muncul

**Gejala:**
```
Mixed Content: The page at 'https://...' was loaded over HTTPS,
but requested an insecure resource 'http://...'
```

**Solusi:**

```bash
# 1. Cek .env
docker compose exec app cat .env | grep -E "APP_URL|ASSET_URL|APP_ENV"

# Harus:
# APP_URL=https://app.indra-casa.my.id
# ASSET_URL=https://app.indra-casa.my.id
# APP_ENV=production

# 2. Jika salah, edit .env
nano .env
# Update APP_URL dan ASSET_URL ke https://

# 3. Clear cache
docker compose exec app php artisan config:clear
docker compose exec app php artisan cache:clear

# 4. Restart containers
docker compose restart
```

### Problem 2: 502 Bad Gateway

**Gejala:** Nginx menampilkan "502 Bad Gateway"

**Solusi:**

```bash
# 1. Cek status containers
docker compose ps

# Pastikan semua container status "Up"

# 2. Cek logs app container
docker compose logs app

# 3. Cek nginx logs
sudo tail -f /var/log/nginx/sop-inventory-error.log

# 4. Cek nginx di host
sudo nginx -t
sudo systemctl status nginx

# 5. Restart semua
docker compose restart
sudo systemctl restart nginx
```

### Problem 3: Database Connection Failed

**Gejala:**
```
SQLSTATE[HY000] [2002] Connection refused
```

**Solusi:**

```bash
# 1. Cek container db running
docker compose ps db

# 2. Test ping dari app ke db
docker compose exec app ping db

# 3. Cek DB logs
docker compose logs db

# 4. Verify .env DB settings
docker compose exec app cat .env | grep DB_

# Harus:
# DB_HOST=db (bukan localhost atau master-db)

# 5. Restart database
docker compose restart db

# Wait 10 detik lalu restart app
sleep 10
docker compose restart app
```

### Problem 4: Permission Denied Storage

**Gejala:**
```
The stream or file "/var/www/storage/logs/laravel.log" could not be opened
```

**Solusi:**

```bash
docker compose exec app chown -R www-data:www-data /var/www/storage
docker compose exec app chown -R www-data:www-data /var/www/bootstrap/cache
docker compose exec app chmod -R 775 /var/www/storage
docker compose exec app chmod -R 775 /var/www/bootstrap/cache

docker compose restart app
```

### Problem 5: Build Frontend Gagal

**Gejala:**
```
ERROR: failed to solve: process "/bin/sh -c npm run build"
```

**Solusi:**

```bash
# Masuk ke container
docker compose exec app bash

# Install ulang dependencies
rm -rf node_modules package-lock.json
npm install

# Build manual
npm run build

# Keluar
exit

# Restart
docker compose restart app
```

### Problem 6: Cloudflare SSL Error

**Gejala:** SSL certificate error atau mismatch

**Solusi:**

1. **Cloudflare SSL Mode** harus diset ke **Full** atau **Full (strict)**
   - Login Cloudflare → SSL/TLS → Overview
   - Pilih: **Full** atau **Full (strict)**

2. **Regenerate Origin Certificate** jika expired:
   - SSL/TLS → Origin Server → Create Certificate

3. **Update certificate di VPS:**
```bash
sudo nano /etc/ssl/cloudflare/cert.pem  # Paste new cert
sudo nano /etc/ssl/cloudflare/key.pem   # Paste new key
sudo nginx -t
sudo systemctl reload nginx
```

---

## 10. Database Management

### 10.1 Access MySQL dari Host

```bash
# Via port 3307 (external port)
mysql -h 127.0.0.1 -P 3307 -u indra -p
# Enter password: indra123 (atau password kamu)

# Pilih database
USE sae_inventory;

# Show tables
SHOW TABLES;

# Exit
EXIT;
```

### 10.2 Access MySQL dari Container

```bash
docker compose exec db mysql -u indra -p

# atau langsung dengan password (not recommended for production)
docker compose exec db mysql -u indra -pindra123 sae_inventory
```

### 10.3 Backup Database

```bash
# Backup ke file
docker compose exec db mysqldump -u indra -pindra123 sae_inventory > backup_$(date +%Y%m%d_%H%M%S).sql

# Atau dengan gzip compression
docker compose exec db mysqldump -u indra -pindra123 sae_inventory | gzip > backup_$(date +%Y%m%d_%H%M%S).sql.gz
```

### 10.4 Restore Database

```bash
# Restore dari backup
docker compose exec -T db mysql -u indra -pindra123 sae_inventory < backup_20260101_010000.sql

# Atau dari gzip
gunzip < backup_20260101_010000.sql.gz | docker compose exec -T db mysql -u indra -pindra123 sae_inventory
```

### 10.5 Reset Database

```bash
# WARNING: Ini akan menghapus semua data!

# Drop & create ulang database
docker compose exec db mysql -u root -proot_password -e "DROP DATABASE IF EXISTS sae_inventory; CREATE DATABASE sae_inventory;"

# Run migrations ulang
docker compose exec app php artisan migrate:fresh --force

# Seed data (optional)
docker compose exec app php artisan db:seed --force
```

---

## 11. Monitoring & Performance

### 11.1 Monitor Resource Usage

```bash
# CPU & Memory usage per container
docker stats

# Disk usage
docker system df

# Detailed volume usage
docker system df -v
```

### 11.2 Cleanup Docker

```bash
# Remove unused images
docker image prune -a

# Remove unused volumes (HATI-HATI! Bisa hapus data)
docker volume prune

# Remove all unused data
docker system prune -a
```

### 11.3 Auto-restart Containers

Semua containers sudah di-set `restart: unless-stopped`, jadi akan otomatis restart jika:
- Container crash
- VPS reboot
- Docker daemon restart

---

## 12. Security Checklist

### ✅ Production Security

- [x] `APP_ENV=production` di `.env`
- [x] `APP_DEBUG=false` di `.env`
- [x] HTTPS enabled dengan valid SSL certificate
- [x] Strong database passwords (bukan default!)
- [x] `.env` file tidak ter-commit ke Git
- [x] File permissions correct (775 untuk storage)
- [x] Nginx security headers enabled
- [x] Session encryption enabled
- [x] Firewall configured (UFW)
- [x] SSH key-based authentication
- [x] Regular backups scheduled

### Firewall Setup (UFW)

```bash
# Install UFW
sudo apt install -y ufw

# Allow SSH (PENTING! Jangan sampai kamu terkunci)
sudo ufw allow 22/tcp

# Allow HTTP & HTTPS
sudo ufw allow 80/tcp
sudo ufw allow 443/tcp

# Enable firewall
sudo ufw enable

# Check status
sudo ufw status
```

---

## 13. Command Reference Cepat

```bash
# ============ Docker Compose ============
docker compose up -d              # Start containers
docker compose down               # Stop containers
docker compose restart            # Restart containers
docker compose ps                 # List containers
docker compose logs -f            # View logs (real-time)
docker compose build --no-cache   # Rebuild images

# ============ Laravel Artisan ============
docker compose exec app php artisan migrate --force
docker compose exec app php artisan db:seed --force
docker compose exec app php artisan config:cache
docker compose exec app php artisan route:cache
docker compose exec app php artisan view:cache
docker compose exec app php artisan config:clear
docker compose exec app php artisan cache:clear

# ============ Database ============
docker compose exec db mysql -u indra -p
docker compose exec db mysqldump -u indra -pPASSWORD sae_inventory > backup.sql

# ============ Container Access ============
docker compose exec app bash      # Enter app container
docker compose exec db bash       # Enter database container

# ============ Deployment ============
bash deploy.sh                    # Auto deploy
git pull origin main              # Manual pull code

# ============ Nginx ============
sudo nginx -t                     # Test config
sudo systemctl reload nginx       # Reload nginx
sudo systemctl restart nginx      # Restart nginx
sudo systemctl status nginx       # Check status

# ============ Logs ============
docker compose logs -f app        # App logs
docker compose logs -f web        # Web logs
docker compose logs -f db         # Database logs
sudo tail -f /var/log/nginx/sop-inventory-error.log
```

---

## 🎉 Selesai!

Aplikasi **SOP Inventory System** kamu sekarang sudah **fully deployed** di production dengan:

✅ **Docker containerized** (MySQL + PHP + Nginx)  
✅ **HTTPS secure** dengan SSL certificate  
✅ **Production optimized** (cached configs)  
✅ **Auto-deploy ready** (`deploy.sh`)  
✅ **Standalone database** (tidak perlu external services)

**Production URL:** https://app.indra-casa.my.id

**Default Login:** (sesuai seeder kamu)

---

## 📞 Support

Jika ada masalah:

1. **Check logs first:**
   ```bash
   docker compose logs -f app
   ```

2. **Check troubleshooting section** di tutorial ini

3. **Verify `.env` configuration:**
   ```bash
   docker compose exec app cat .env
   ```

4. **Container health:**
   ```bash
   docker compose ps
   docker stats
   ```

---

**Happy deploying! 🚀**
