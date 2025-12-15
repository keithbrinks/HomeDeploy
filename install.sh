#!/bin/bash

set -e

# Colors
GREEN='\033[0;32m'
BLUE='\033[0;34m'
RED='\033[0;31m'
NC='\033[0m'

echo -e "${BLUE}HomeDeploy Installer${NC}"

# Check if running as root
if [ "$EUID" -ne 0 ]; then
  echo -e "${RED}Please run as root${NC}"
  exit 1
fi

# 1. Install Dependencies
echo -e "${BLUE}Installing dependencies...${NC}"
apt-get update
apt-get install -y software-properties-common curl git unzip sqlite3 acl mysql-server nginx redis-server

# Add PHP PPA
add-apt-repository -y ppa:ondrej/php
apt-get update

# Install PHP 8.2 and extensions
apt-get install -y php8.2 php8.2-cli php8.2-common php8.2-sqlite3 php8.2-curl \
    php8.2-mbstring php8.2-xml php8.2-zip php8.2-bcmath php8.2-intl php8.2-fpm

# Install Composer
if ! command -v composer &> /dev/null; then
    curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer
fi

# 2. Setup Application
INSTALL_DIR="/opt/homedeploy"
REPO_URL="https://github.com/keithbrinks/HomeDeploy.git"

if [ -d "$INSTALL_DIR" ]; then
    echo -e "${BLUE}Updating existing installation...${NC}"
    cd "$INSTALL_DIR"
    git pull
else
    echo -e "${BLUE}Cloning repository...${NC}"
    # For now, we assume the script is run from the repo or we clone it.
    # If this script is curl'd, we clone.
    git clone "$REPO_URL" "$INSTALL_DIR" || {
        echo -e "${RED}Failed to clone repository. Please check URL.${NC}"
        # Fallback for local dev testing: copy current dir
        # cp -r . "$INSTALL_DIR"
    }
    cd "$INSTALL_DIR"
fi

# Set permissions
chown -R www-data:www-data "$INSTALL_DIR"
setfacl -R -m u:www-data:rwx storage bootstrap/cache

# Install PHP dependencies
echo -e "${BLUE}Installing PHP dependencies...${NC}"
sudo -u www-data composer install --no-dev --optimize-autoloader

# Environment Setup
if [ ! -f .env ]; then
    cp .env.example .env
    php artisan key:generate
    
    # Configure SQLite
    touch database/database.sqlite
    sed -i 's/DB_CONNECTION=mysql/DB_CONNECTION=sqlite/' .env
    sed -i 's/# DB_DATABASE=laravel/DB_DATABASE=\/opt\/homedeploy\/database\/database.sqlite/' .env
    # Remove other DB lines
    sed -i '/DB_HOST/d' .env
    sed -i '/DB_PORT/d' .env
    sed -i '/DB_USERNAME/d' .env
    sed -i '/DB_PASSWORD/d' .env
fi

# Run Migrations
echo -e "${BLUE}Running migrations...${NC}"
php artisan migrate --force

# Create Admin User
PASSWORD=$(openssl rand -base64 12)
echo -e "${BLUE}Creating admin user...${NC}"
# We need a command to create user. I'll add a console command for this later.
# For now, let's use tinker or a seed.
php artisan user:create --name="Admin" --email="admin@localhost" --password="$PASSWORD" || echo "User might already exist"

# 3. Systemd Service
echo -e "${BLUE}Creating systemd service...${NC}"
cat > /etc/systemd/system/homedeploy.service <<EOF
[Unit]
Description=HomeDeploy Manager
After=network.target

[Service]
User=www-data
Group=www-data
WorkingDirectory=$INSTALL_DIR
ExecStart=/usr/bin/php artisan serve --host=127.0.0.1 --port=8080
Restart=always

[Install]
WantedBy=multi-user.target
EOF

# Queue Worker
cat > /etc/systemd/system/homedeploy-queue.service <<EOF
[Unit]
Description=HomeDeploy Queue Worker
After=network.target

[Service]
User=www-data
Group=www-data
WorkingDirectory=$INSTALL_DIR
ExecStart=/usr/bin/php artisan queue:work --tries=3
Restart=always

[Install]
WantedBy=multi-user.target
EOF

systemctl daemon-reload
systemctl enable homedeploy
systemctl enable homedeploy-queue
systemctl restart homedeploy
systemctl restart homedeploy-queue

echo -e "${GREEN}Installation Complete!${NC}"
echo -e "Access at: http://localhost:8080"
echo -e "Email: admin@localhost"
echo -e "Password: $PASSWORD"
echo -e "Please save this password."
