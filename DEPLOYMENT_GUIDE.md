# Cursor1 Website - Deployment Guide

## Overview
This website is designed to work on both localhost (XAMPP) and live cPanel hosting with automatic environment detection.

## File Structure for cPanel
```
html_public/projects/cursor1/
├── home/
├── contact/
├── login/
├── register/
├── cfresponse/
├── config/
├── assets/css/
├── .htaccess
├── index.php
└── setup_database_cpanel.sql
```

## Database Configuration

### Automatic Environment Detection
The website automatically detects whether it's running on:
- **Localhost**: Uses XAMPP database settings
- **Live Hosting**: Uses cPanel database settings

### Database Settings

#### Localhost (XAMPP)
- Host: `localhost`
- Database: `cursor1`
- Username: `root`
- Password: (empty)

#### Live Hosting (cPanel)
- Host: `localhost`
- Database: `a1e750tdxgba_cursor1`
- Username: `a1e750tdxgba_15Crossways`
- Password: `Crossways15!`

## Deployment Steps

### 1. Database Setup on cPanel

1. **Create Database in cPanel:**
   - Go to cPanel → MySQL Databases
   - Create database: `a1e750tdxgba_cursor1`
   - Create user: `a1e750tdxgba_15Crossways`
   - Set password: `Crossways15!`
   - Assign user to database with ALL PRIVILEGES

2. **Import Database Structure:**
   - Go to cPanel → phpMyAdmin
   - Select the `a1e750tdxgba_cursor1` database
   - Import the `setup_database_cpanel.sql` file
   - Or run the SQL commands manually

### 2. File Upload

1. **Upload Files:**
   - Upload all files to `html_public/projects/cursor1/`
   - Ensure proper file permissions (644 for files, 755 for directories)

2. **Verify .htaccess:**
   - Ensure `.htaccess` file is uploaded
   - Check that mod_rewrite is enabled on your hosting

### 3. Testing

1. **Access the Website:**
   - Visit: `https://yourdomain.com/projects/cursor1/`
   - Should redirect to: `https://yourdomain.com/projects/cursor1/home`

2. **Test All Features:**
   - Home page loads correctly
   - Contact form submits successfully
   - Login system works (admin/admin123)
   - Admin dashboard shows form responses

## Environment Detection Logic

The system automatically detects the environment based on:
- Server name containing 'localhost' or '127.0.0.1'
- HTTP host containing 'localhost' or '.local'

## Troubleshooting

### Common Issues

1. **Database Connection Error:**
   - Check database credentials in `config/database.php`
   - Verify database exists in cPanel
   - Ensure user has proper permissions

2. **Clean URLs Not Working:**
   - Check if mod_rewrite is enabled
   - Verify `.htaccess` file is uploaded
   - Check file permissions

3. **Environment Detection Issues:**
   - Add debug output to see detected environment
   - Check server variables in `config/database.php`

### Debug Mode

To debug environment detection, temporarily add this to any page:
```php
<?php
require_once 'config/database.php';
echo "Environment: " . $environment;
echo "<br>Host: " . $host;
echo "<br>Database: " . $dbname;
?>
```

## Security Features

- SQL injection protection with prepared statements
- XSS protection with htmlspecialchars()
- Password hashing with PHP's password_hash()
- File access restrictions for .sql and .md files
- Security headers (if mod_headers is available)

## Performance Optimizations

- Gzip compression enabled
- Clean URLs for SEO
- Responsive design for all devices
- Optimized database queries with indexes

## Support

If you encounter issues:
1. Check the error messages in the browser
2. Verify database connection settings
3. Check file permissions on the server
4. Ensure all required PHP extensions are enabled
