# ============================================================================
# Quick Setup Guide - Standalone Database
# ============================================================================
# Untuk VPS yang TIDAK menggunakan master-db eksternal

## 🚀 Deployment Cepat (5 Menit)

### 1. Clone Repository

```bash
cd /opt
git clone https://github.com/indramaulin76/SOP-INVENTORY-2.0.git
cd SOP-INVENTORY-2.0
```

### 2. Setup Environment

```bash
# Copy environment file
cp .env.production.example .env

# Edit jika perlu (opsional)
nano .env
```

**Default credentials (sudah OK untuk production):**
```env
DB_DATABASE=sae_inventory
DB_USERNAME=indra
DB_PASSWORD=indra123
DB_ROOT_PASSWORD=root_password
```

> [!WARNING]
> **GANTI PASSWORD** untuk production yang lebih secure!

### 3. Build & Start

```bash
# Build images (5-10 menit)
docker compose build --no-cache

# Start semua services (DB + App + Web)
docker compose up -d
```

### 4. Setup Laravel

```bash
# Generate key
docker compose exec app php artisan key:generate --force

# Run migrations
docker compose exec app php artisan migrate --force

# Optional: seed data
docker compose exec app php artisan db:seed --force

# Setup storage & cache
docker compose exec app php artisan storage:link
docker compose exec app php artisan config:cache
docker compose exec app php artisan route:cache
docker compose exec app php artisan view:cache
```

### 5. Setup Nginx Reverse Proxy

**File:** `/etc/nginx/sites-available/sop-inventory`

```nginx
upstream sop_backend {
    server 127.0.0.1:8000;
}

server {
    listen 80;
    server_name app.indra-casa.my.id;
    return 301 https://$server_name$request_uri;
}

server {
    listen 443 ssl http2;
    server_name app.indra-casa.my.id;

    # SSL Certificate (Cloudflare/Let's Encrypt)
    ssl_certificate /path/to/cert.pem;
    ssl_certificate_key /path/to/key.pem;

    location / {
        proxy_pass http://sop_backend;
        proxy_set_header Host $host;
        proxy_set_header X-Real-IP $remote_addr;
        proxy_set_header X-Forwarded-For $proxy_add_x_forwarded_for;
        proxy_set_header X-Forwarded-Proto $scheme;
        proxy_set_header X-Forwarded-Host $host;
    }
}
```

```bash
# Enable & reload
sudo ln -s /etc/nginx/sites-available/sop-inventory /etc/nginx/sites-enabled/
sudo nginx -t
sudo systemctl reload nginx
```

### 6. Test

Open browser: **https://app.indra-casa.my.id/login**

✅ No Mixed Content errors  
✅ Assets load via HTTPS  
✅ Login works

---

## 📊 Containers Running

```bash
docker compose ps
```

**Output:**
```
NAME              STATUS        PORTS
sae-bakery-db     Up           0.0.0.0:3307->3306/tcp
sae-bakery-app    Up           9000/tcp
sae-bakery-web    Up           0.0.0.0:8000->80/tcp
```

---

## 🔄 Update Deployment

### One-Command Update:

```bash
bash deploy.sh
```

### Manual Update:

```bash
git pull origin main
docker compose down
docker compose build --no-cache
docker compose up -d
docker compose exec app php artisan migrate --force
docker compose exec app php artisan config:cache
```

---

## 🗄️ Database Access

### From Host (Port 3307):

```bash
mysql -h 127.0.0.1 -P 3307 -u indra -p
# Password: indra123
```

### From Container:

```bash
docker compose exec db mysql -u indra -p
```

### Backup Database:

```bash
docker compose exec db mysqldump -u indra -pindra123 sae_inventory > backup.sql
```

### Restore Database:

```bash
docker compose exec -T db mysql -u indra -pindra123 sae_inventory < backup.sql
```

---

## 🛠️ Common Commands

```bash
# View logs
docker compose logs -f app
docker compose logs -f db

# Restart services
docker compose restart

# Stop everything
docker compose down

# Stop and delete volumes (DANGER!)
docker compose down -v

# Enter app container
docker compose exec app bash

# Enter DB container
docker compose exec db bash

# Check DB status
docker compose exec db mysqladmin -u root -proot_password ping
```

---

## 🔒 Security Notes

> [!IMPORTANT]
> Untuk production, **WAJIB** ganti password default!

Edit `.env`:
```env
DB_PASSWORD=your_strong_password_here
DB_ROOT_PASSWORD=your_root_password_here
```

Lalu restart:
```bash
docker compose down -v  # Hapus volume lama
docker compose up -d     # Buat ulang dengan password baru
```

---

## ✅ Done!

Your app is now running at: **https://app.indra-casa.my.id**

With:
- ✅ MySQL 8.0 standalone database
- ✅ PHP 8.4 + Laravel
- ✅ Nginx web server
- ✅ HTTPS enabled
- ✅ Auto-deploy ready
