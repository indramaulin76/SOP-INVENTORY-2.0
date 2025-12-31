#!/bin/bash

# ============================================================================
# SOP Inventory - Production Deployment Script
# ============================================================================
# Script untuk auto-deploy/update aplikasi di VPS
# Jalankan: bash deploy.sh

set -e  # Exit on error

echo "🚀 Starting deployment..."

# 1. Pull latest code
echo "📥 Pulling latest code from GitHub..."
git pull origin main

# 2. Stop containers
echo "🛑 Stopping containers..."
docker compose down

# 3. Rebuild containers
echo "🔨 Building Docker images..."
docker compose build --no-cache

# 4. Start containers
echo "▶️  Starting containers..."
docker compose up -d

# 5. Wait for containers to be ready
echo "⏳ Waiting for containers to be ready..."
sleep 10

# 6. Run Laravel commands inside container
echo "🔧 Running Laravel setup commands..."

# Generate app key if not exists
docker compose exec -T app php artisan key:generate --force

# Run database migrations
docker compose exec -T app php artisan migrate --force

# Clear all caches
docker compose exec -T app php artisan config:clear
docker compose exec -T app php artisan cache:clear
docker compose exec -T app php artisan route:clear
docker compose exec -T app php artisan view:clear

# Cache for production
docker compose exec -T app php artisan config:cache
docker compose exec -T app php artisan route:cache
docker compose exec -T app php artisan view:cache

# Create storage link
docker compose exec -T app php artisan storage:link || true

# Set proper permissions
docker compose exec -T app chown -R www-data:www-data /var/www/storage /var/www/bootstrap/cache
docker compose exec -T app chmod -R 775 /var/www/storage /var/www/bootstrap/cache

echo ""
echo "✅ Deployment completed successfully!"
echo ""
echo "🔍 Container status:"
docker compose ps
echo ""
echo "📊 View logs: docker compose logs -f"
echo "🌐 Application URL: https://app.indra-casa.my.id"
