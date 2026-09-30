#!/bin/bash
# ==============================================================================
# Ferrox VPS / Bare Metal Deployment Script
# Use this to scaffold a fresh Ubuntu 22.04+ Server for Ferrox production.
# ==============================================================================
set -e

echo "[1/6] Updating System & Installing Dependencies..."
apt-get update && apt-get upgrade -y
apt-get install -y nginx curl unzip software-properties-common ufw

echo "[2/6] Installing PHP 8.2 & Swoole..."
add-apt-repository ppa:ondrej/php -y
apt-get update
apt-get install -y php8.2-cli php8.2-swoole php8.2-xml php8.2-curl php8.2-mbstring

echo "[3/6] Installing Composer..."
curl -sS https://getcomposer.org/installer | php
mv composer.phar /usr/local/bin/composer

echo "[4/6] Creating Dedicated Non-Root User (ferroxuser)..."
if ! id "ferroxuser" &>/dev/null; then
    useradd -m -s /bin/bash ferroxuser
fi
mkdir -p /var/www/ferrox
chown -R ferroxuser:ferroxuser /var/www/ferrox

echo "[5/6] Hardening Firewall (UFW)..."
ufw allow 'Nginx Full'
ufw allow OpenSSH
ufw --force enable

echo "[6/6] Applying Nginx and Systemd Configuration..."
# Assuming you have cloned this repo into /root/ferrox-php
cp ferrox-php-iac/vps/nginx/ferrox.conf /etc/nginx/sites-available/ferrox.conf
ln -sf /etc/nginx/sites-available/ferrox.conf /etc/nginx/sites-enabled/
rm -f /etc/nginx/sites-enabled/default

cp ferrox-php-iac/vps/systemd/ferrox.service /etc/systemd/system/
systemctl daemon-reload
systemctl enable ferrox.service

echo "=============================================================================="
echo "Deployment Scaffold Complete!"
echo "Next Steps:"
echo "1. Switch to ferroxuser: su - ferroxuser"
echo "2. Clone your app into /var/www/ferrox and run: composer install --no-dev"
echo "3. Run: sudo systemctl start ferrox.service"
echo "4. Run: sudo systemctl restart nginx"
echo "=============================================================================="
