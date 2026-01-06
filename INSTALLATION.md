# Installation Guide - SVC-UJDS

## Quick Start (Windows/XAMPP)

### 1. Prerequisites Check

```powershell
# Check PHP version (must be >= 8.0)
php -v

# Check if Composer is installed
composer --version

# Check if MySQL is running
# Open http://localhost/phpmyadmin in browser
```

### 2. Install Dependencies

```powershell
cd c:\xampp\htdocs\svc-ujds
composer install
```

This will install:
- `phpoffice/phpspreadsheet` for Excel import/export
- `tecnickcom/tcpdf` for PDF generation

### 3. Database Setup

#### Option A: Using phpMyAdmin (Recommended for beginners)

1. Open http://localhost/phpmyadmin
2. Click "New" to create a database
3. Database name: `svc_ujds`
4. Collation: `utf8mb4_unicode_ci`
5. Click "Create"
6. Select the `svc_ujds` database
7. Click "Import" tab
8. Choose file: `c:\xampp\htdocs\svc-ujds\database\schema.sql`
9. Click "Go"
10. Repeat for `database\seed.sql` (test data)

#### Option B: Using Command Line

```powershell
# Navigate to MySQL bin directory
cd c:\xampp\mysql\bin

# Create database and import schema
.\mysql.exe -u root -p

# In MySQL prompt:
CREATE DATABASE svc_ujds CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
exit;

# Import schema
.\mysql.exe -u root -p svc_ujds < c:\xampp\htdocs\svc-ujds\database\schema.sql

# Import test data
.\mysql.exe -u root -p svc_ujds < c:\xampp\htdocs\svc-ujds\database\seed.sql
```

### 4. Configuration

Edit `config/config.php` if your MySQL settings are different:

```php
define('DB_HOST', 'localhost');
define('DB_NAME', 'svc_ujds');
define('DB_USER', 'root');
define('DB_PASS', ''); // Your MySQL password
```

### 5. Apache Configuration

Ensure `mod_rewrite` is enabled in XAMPP:

1. Open `c:\xampp\apache\conf\httpd.conf`
2. Find line: `#LoadModule rewrite_module modules/mod_rewrite.so`
3. Remove the `#` to uncomment
4. Save and restart Apache

### 6. File Permissions

```powershell
# Ensure uploads directory is writable
icacls "c:\xampp\htdocs\svc-ujds\public\uploads" /grant Everyone:F
```

### 7. Access the Application

**URL:** http://localhost/svc-ujds/public/

**Default Login:**
- Username: `admin`
- Password: `password123`

**Other Test Accounts:**
- Username: `comptable` / Password: `password123`
- Username: `membre` / Password: `password123`

## Troubleshooting

### Error: "Database connection failed"
- Check MySQL is running in XAMPP Control Panel
- Verify database credentials in `config/config.php`
- Ensure database `svc_ujds` exists

### Error: "404 Not Found"
- Check `mod_rewrite` is enabled in Apache
- Verify `.htaccess` file exists in `public/` directory
- Ensure you're accessing via `public/` in the URL

### Error: "Class not found"
- Run `composer install` to install dependencies
- Check autoloader paths in `public/index.php`

### Blank page / No CSS
- Check TailwindCSS CDN is loading (requires internet)
- Verify `BASE_URL` in `config/config.php` matches your setup
- Clear browser cache

## Next Steps

1. **Change Default Passwords**
   ```sql
   UPDATE utilisateurs SET password = '$2y$10$...' WHERE username = 'admin';
   ```

2. **Add Real Members**
   - Login as admin
   - Navigate to "Membres" → "Nouveau Membre"

3. **Import Excel Data** (when implemented)
   - Prepare Excel file with required columns
   - Use import interface

4. **Configure for Production**
   - Change `APP_ENV` to `production` in `config/config.php`
   - Use strong passwords
   - Enable HTTPS
   - Configure backups

## Production Deployment

### Security Checklist

- [ ] Change all default passwords
- [ ] Set `APP_ENV` to `production`
- [ ] Disable error display
- [ ] Enable HTTPS
- [ ] Configure firewall
- [ ] Set up database backups
- [ ] Restrict file permissions
- [ ] Review security headers in `.htaccess`

### Performance Optimization

- [ ] Enable OPcache in PHP
- [ ] Configure MySQL query cache
- [ ] Minify CSS/JS (if not using CDN)
- [ ] Enable gzip compression
- [ ] Set up caching headers

## Support

For issues or questions:
1. Check error logs: `c:\xampp\apache\logs\error.log`
2. Check PHP errors: `c:\xampp\php\logs\php_error_log`
3. Review this documentation
4. Check `README.md` for detailed information

---

**Installation complete! Enjoy using SVC-UJDS** 🎉
