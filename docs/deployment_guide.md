# ULMS — Deployment Guide

## Option 1: Local Development (XAMPP — Windows)

### Requirements
- XAMPP 8.1+ (PHP 8.1, MySQL 8.0, Apache 2.4)
- Browser: Chrome, Firefox, or Opera

### Steps

```
1. Download and install XAMPP from https://www.apachefriends.org/
2. Start Apache and MySQL in XAMPP Control Panel
3. Copy library-system/ to: C:\xampp\htdocs\library-system\
4. Open phpMyAdmin: http://localhost/phpmyadmin
5. Create database: ulms_db (Charset: utf8mb4, Collation: utf8mb4_unicode_ci)
6. Import: library-system/database/schema.sql
7. Edit app/config/config.php — set DB_PASS if needed
8. Visit: http://localhost/library-system/setup.php
9. ⚠️ Delete setup.php after seeding
10. Login: http://localhost/library-system/public/login.php
```

---

## Option 2: Linux LAMP Server

### Install LAMP Stack (Ubuntu/Debian)

```bash
sudo apt update && sudo apt upgrade -y
sudo apt install apache2 php8.1 php8.1-mysql php8.1-pdo libapache2-mod-php mysql-server -y
sudo systemctl enable apache2 mysql
sudo systemctl start apache2 mysql
```

### Deploy Project

```bash
# Copy project
sudo cp -r library-system /var/www/html/
sudo chown -R www-data:www-data /var/www/html/library-system
sudo chmod -R 755 /var/www/html/library-system
sudo chmod -R 777 /var/www/html/library-system/app/logs  # writable logs dir

# Create database
sudo mysql -u root -p <<EOF
CREATE DATABASE ulms_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER 'ulms_user'@'localhost' IDENTIFIED BY 'SecurePass123!';
GRANT ALL ON ulms_db.* TO 'ulms_user'@'localhost';
FLUSH PRIVILEGES;
EOF

# Import schema
sudo mysql -u ulms_user -p ulms_db < /var/www/html/library-system/database/schema.sql

# Update config.php
sudo nano /var/www/html/library-system/app/config/config.php
# Set: DB_USER='ulms_user', DB_PASS='SecurePass123!'
```

### Apache Virtual Host

```apache
# /etc/apache2/sites-available/ulms.conf
<VirtualHost *:80>
    ServerName library.youruni.edu
    DocumentRoot /var/www/html/library-system/public
    
    <Directory /var/www/html/library-system>
        AllowOverride All
        Require all granted
    </Directory>
    
    ErrorLog ${APACHE_LOG_DIR}/ulms_error.log
    CustomLog ${APACHE_LOG_DIR}/ulms_access.log combined
</VirtualHost>
```

```bash
sudo a2ensite ulms.conf
sudo a2enmod rewrite
sudo systemctl reload apache2
```

---

## Option 3: VPS Deployment (Ubuntu + Nginx)

### Install Nginx + PHP-FPM

```bash
sudo apt install nginx php8.1-fpm php8.1-mysql -y

# Nginx config
sudo nano /etc/nginx/sites-available/ulms
```

```nginx
server {
    listen 80;
    server_name your-vps-ip;
    root /var/www/library-system/public;
    index index.php;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.1-fpm.sock;
        fastcgi_index index.php;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
    }

    location ~* /app/ {
        deny all;
        return 404;
    }
}
```

```bash
sudo ln -s /etc/nginx/sites-available/ulms /etc/nginx/sites-enabled/
sudo nginx -t
sudo systemctl reload nginx
```

---

## Option 4: Shared Web Hosting (cPanel)

```
1. Upload all files via File Manager or FTP to public_html/library-system/
2. Create MySQL database via cPanel → MySQL Databases
3. Create MySQL user and assign to database (ALL PRIVILEGES)
4. Import schema.sql via phpMyAdmin
5. Edit app/config/config.php with cPanel DB credentials
6. Visit: https://yourdomain.com/library-system/setup.php
7. Delete setup.php after seeding
```

---

## Security Hardening (Production)

```php
// app/config/config.php — Production settings
define('DEBUG_MODE', false);
```

```apache
# .htaccess in project root — block direct access to app/
<FilesMatch "\.php$">
    Order Deny,Allow
    Deny from all
</FilesMatch>
```

```apache
# .htaccess in public/ — only public folder accessible
Options -Indexes
```

### SSL (HTTPS) with Certbot

```bash
sudo apt install certbot python3-certbot-apache -y
sudo certbot --apache -d library.youruni.edu
```

---

## Post-Deployment Checklist

- [ ] Database imported successfully
- [ ] setup.php run and then **deleted**
- [ ] Admin login verified
- [ ] DEBUG_MODE set to false
- [ ] .htaccess blocks app/ directory
- [ ] SSL certificate installed
- [ ] Logs directory has write permissions
- [ ] Chrome, Firefox, Opera tested
