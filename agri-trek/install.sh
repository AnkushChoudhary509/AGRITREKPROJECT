#!/bin/bash
# ============================================================
# Agri-Trek – Automated Setup Script
# Run: bash install.sh
# ============================================================

GREEN='\033[0;32m'
YELLOW='\033[1;33m'
RED='\033[0;31m'
NC='\033[0m' # No Color

echo -e "${GREEN}"
echo "╔══════════════════════════════════════════╗"
echo "║   🚁  Agri-Trek Installation Script       ║"
echo "║   Precision Agriculture System            ║"
echo "╚══════════════════════════════════════════╝"
echo -e "${NC}"

# --- Check PHP ---
if ! command -v php &> /dev/null; then
    echo -e "${RED}❌ PHP not found. Please install PHP 8.2+${NC}"
    exit 1
fi
PHP_VERSION=$(php -r "echo PHP_MAJOR_VERSION.'.'.PHP_MINOR_VERSION;")
echo -e "${GREEN}✅ PHP ${PHP_VERSION} found${NC}"

# --- Check Composer ---
if ! command -v composer &> /dev/null; then
    echo -e "${RED}❌ Composer not found. Please install Composer 2.x${NC}"
    exit 1
fi
echo -e "${GREEN}✅ Composer found${NC}"

# --- Install dependencies ---
echo -e "\n${YELLOW}📦 Installing PHP dependencies...${NC}"
composer install --no-interaction --prefer-dist --optimize-autoloader

# --- Copy .env ---
if [ ! -f .env ]; then
    echo -e "\n${YELLOW}⚙️  Creating .env file...${NC}"
    cp .env.example .env
fi

# --- Generate key ---
echo -e "\n${YELLOW}🔑 Generating application key...${NC}"
php artisan key:generate

# --- Prompt for DB details ---
echo -e "\n${YELLOW}🗄️  Database Configuration${NC}"
read -p "Database host [127.0.0.1]: " DB_HOST
DB_HOST=${DB_HOST:-127.0.0.1}

read -p "Database name [agritrek]: " DB_NAME
DB_NAME=${DB_NAME:-agritrek}

read -p "Database username [root]: " DB_USER
DB_USER=${DB_USER:-root}

read -s -p "Database password [empty]: " DB_PASS
echo ""

# Update .env
sed -i "s/DB_HOST=127.0.0.1/DB_HOST=${DB_HOST}/" .env
sed -i "s/DB_DATABASE=agritrek/DB_DATABASE=${DB_NAME}/" .env
sed -i "s/DB_USERNAME=root/DB_USERNAME=${DB_USER}/" .env
sed -i "s/DB_PASSWORD=/DB_PASSWORD=${DB_PASS}/" .env

# --- Run migrations ---
echo -e "\n${YELLOW}🏗️  Running database migrations...${NC}"
php artisan migrate --force

# --- Seed the database ---
echo -e "\n${YELLOW}🌱 Seeding demo data...${NC}"
php artisan db:seed --force

# --- Create storage symlink ---
echo -e "\n${YELLOW}🔗 Creating storage symlink...${NC}"
php artisan storage:link

# --- Clear caches ---
echo -e "\n${YELLOW}🧹 Clearing caches...${NC}"
php artisan config:clear
php artisan cache:clear
php artisan view:clear

echo -e "\n${GREEN}"
echo "╔══════════════════════════════════════════╗"
echo "║  ✅  Installation Complete!               ║"
echo "╠══════════════════════════════════════════╣"
echo "║  Run: php artisan serve                  ║"
echo "║  Open: http://localhost:8000             ║"
echo "║                                          ║"
echo "║  Admin:  admin@agritrek.com / password   ║"
echo "║  Farmer: farmer@agritrek.com / password  ║"
echo "╚══════════════════════════════════════════╝"
echo -e "${NC}"
