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
  echo -e "${YELLOW}Use: curl -sSL https://raw.githubusercontent.com/keithbrinks/HomeDeploy/main/install.sh | sudo bash${NC}"
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
apt-get install -y php8.2 php8.2-cli php8.2-common php8.2-sqlite3 php8.2-mysql \
    php8.2-curl php8.2-mbstring php8.2-xml php8.2-zip php8.2-bcmath php8.2-intl \
    php8.2-fpm php8.2-redis

# Install Composer
if ! command -v composer &> /dev/null; then
    curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer
fi

# Install Node.js (LTS)
echo -e "${BLUE}Installing Node.js...${NC}"
if ! command -v node &> /dev/null; then
    curl -fsSL https://deb.nodesource.com/setup_20.x | bash -
    apt-get install -y nodejs
fi

# 2. Secure MySQL
echo -e "${BLUE}Securing MySQL...${NC}"
MYSQL_ROOT_PASSWORD=$(openssl rand -base64 20 | tr -d "=+/" | cut -c1-25)

# Set root password and secure installation
mysql -e "ALTER USER 'root'@'localhost' IDENTIFIED WITH mysql_native_password BY '${MYSQL_ROOT_PASSWORD}';" 2>/dev/null || true
mysql -u root -p"${MYSQL_ROOT_PASSWORD}" -e "DELETE FROM mysql.user WHERE User='';" 2>/dev/null || true
mysql -u root -p"${MYSQL_ROOT_PASSWORD}" -e "DELETE FROM mysql.user WHERE User='root' AND Host NOT IN ('localhost', '127.0.0.1', '::1');" 2>/dev/null || true
mysql -u root -p"${MYSQL_ROOT_PASSWORD}" -e "DROP DATABASE IF EXISTS test;" 2>/dev/null || true
mysql -u root -p"${MYSQL_ROOT_PASSWORD}" -e "DELETE FROM mysql.db WHERE Db='test' OR Db='test\\_%';" 2>/dev/null || true
mysql -u root -p"${MYSQL_ROOT_PASSWORD}" -e "FLUSH PRIVILEGES;" 2>/dev/null || true

# Save MySQL credentials
MYSQL_CRED_FILE="/root/mysql-root-credentials.txt"
cat > "$MYSQL_CRED_FILE" <<EOF
MySQL Root Credentials
======================
Username: root
Password: $MYSQL_ROOT_PASSWORD
Host: localhost

IMPORTANT: HomeDeploy uses these credentials to create databases for deployed apps.
Keep this file secure and delete after saving credentials elsewhere.
EOF
chmod 600 "$MYSQL_CRED_FILE"
echo -e "${GREEN}MySQL secured. Credentials saved to $MYSQL_CRED_FILE${NC}"

# 3. Setup Application
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
    
    # Configure SQLite for HomeDeploy's database
    touch database/database.sqlite
    sed -i 's/DB_CONNECTION=mysql/DB_CONNECTION=sqlite/' .env
    sed -i 's/# DB_DATABASE=laravel/DB_DATABASE=\/opt\/homedeploy\/database\/database.sqlite/' .env
    # Remove other DB lines
    sed -i '/DB_HOST/d' .env
    sed -i '/DB_PORT/d' .env
    sed -i '/DB_USERNAME/d' .env
    sed -i '/DB_PASSWORD/d' .env
    
    # Add MySQL root password for app database creation
    echo "" >> .env
    echo "# MySQL credentials for creating databases for deployed apps" >> .env
    echo "MYSQL_ROOT_PASSWORD=$MYSQL_ROOT_PASSWORD" >> .env
fi

# Set proper permissions
echo -e "${BLUE}Setting permissions...${NC}"
chown -R www-data:www-data $INSTALL_DIR
chmod -R 755 $INSTALL_DIR
chmod -R 775 $INSTALL_DIR/storage
chmod -R 775 $INSTALL_DIR/bootstrap/cache
chmod 775 $INSTALL_DIR/database
chmod 664 $INSTALL_DIR/database/database.sqlite

# Configure sudo permissions for www-data
echo -e "${BLUE}Configuring sudo permissions...${NC}"
cat > /etc/sudoers.d/homedeploy <<EOF
# HomeDeploy: Allow www-data to manage deployments and server configuration
www-data ALL=(ALL) NOPASSWD: /bin/mkdir, /bin/chown, /bin/cp, /bin/ln, /bin/cat, /usr/bin/git, /usr/sbin/nginx, /usr/bin/systemctl reload nginx, /usr/bin/systemctl restart nginx, /usr/bin/systemctl start cloudflared-tunnel, /usr/bin/systemctl stop cloudflared-tunnel, /usr/bin/systemctl enable cloudflared-tunnel, /usr/bin/systemctl disable cloudflared-tunnel, /usr/bin/systemctl daemon-reload, /usr/bin/mysql
EOF
chmod 440 /etc/sudoers.d/homedeploy

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

# Configure Nginx
echo -e "${BLUE}Configuring Nginx...${NC}"

# Prompt for domain/hostname
read -p "Enter domain for HomeDeploy (press Enter for server's hostname): " HOMEDEPLOY_DOMAIN
if [ -z "$HOMEDEPLOY_DOMAIN" ]; then
    HOMEDEPLOY_DOMAIN=$(hostname -f 2>/dev/null || hostname)
fi

cat > /etc/nginx/sites-available/homedeploy <<EOF
server {
    listen 80;
    listen [::]:80;
    server_name ${HOMEDEPLOY_DOMAIN};

    location / {
        proxy_pass http://127.0.0.1:8080;
        proxy_set_header Host \$host;
        proxy_set_header X-Real-IP \$remote_addr;
        proxy_set_header X-Forwarded-For \$proxy_add_x_forwarded_for;
        proxy_set_header X-Forwarded-Proto \$scheme;
        proxy_redirect off;
    }
}
EOF

# Remove default site and enable HomeDeploy
rm -f /etc/nginx/sites-enabled/default
ln -sf /etc/nginx/sites-available/homedeploy /etc/nginx/sites-enabled/
nginx -t && systemctl restart nginx

echo ""
echo -e "${GREEN}========================================${NC}"
echo -e "${GREEN}    Installation Complete!${NC}"
echo -e "${GREEN}========================================${NC}"
echo ""
echo -e "${BLUE}Access HomeDeploy:${NC}"
echo -e "  URL: http://YOUR_SERVER_IP"
echo ""
echo -e "${BLUE}Admin Credentials:${NC}"
echo -e "  Email:    admin@localhost"
echo -e "  Password: ${GREEN}$PASSWORD${NC}"
echo ""
echo -e "${BLUE}MySQL Root Credentials (for TablePlus/database tools):${NC}"
echo -e "  Host:     127.0.0.1 or localhost"
echo -e "  Port:     3306"
echo -e "  Username: root"
echo -e "  Password: ${GREEN}$MYSQL_ROOT_PASSWORD${NC}"
echo -e "  ${YELLOW}Note: Also saved to $MYSQL_CRED_FILE${NC}"
echo ""
echo -e "${YELLOW}⚠️  IMPORTANT: Save these credentials securely!${NC}"
echo ""
