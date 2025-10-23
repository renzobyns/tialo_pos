# Deployment Checklist

## Pre-Deployment

### Code Review
- [ ] All PHP files follow coding standards
- [ ] No debug statements left in code
- [ ] Error handling implemented
- [ ] Input validation in place
- [ ] SQL injection prevention (prepared statements)
- [ ] XSS prevention (htmlspecialchars)

### Security
- [ ] Default admin password changed
- [ ] Database credentials secured
- [ ] File permissions set correctly
- [ ] .htaccess configured
- [ ] Error logging enabled
- [ ] Error display disabled in production

### Testing
- [ ] All modules tested locally
- [ ] Cross-browser testing completed
- [ ] Mobile responsiveness verified
- [ ] Database transactions tested
- [ ] Payment processing tested
- [ ] Reports generation tested

### Database
- [ ] Database backup created
- [ ] SQL script tested on fresh database
- [ ] Sample data loaded
- [ ] Indexes created for performance
- [ ] Foreign keys verified

## Deployment Steps

### 1. Server Setup
- [ ] PHP 7.4+ installed
- [ ] MySQL 5.7+ installed
- [ ] Apache with mod_rewrite enabled
- [ ] SSL certificate installed
- [ ] Firewall configured

### 2. File Upload
- [ ] All files uploaded via FTP/SFTP
- [ ] File permissions set (644 for files, 755 for directories)
- [ ] .htaccess file in place
- [ ] index.php accessible

### 3. Database Setup
- [ ] Database created
- [ ] SQL script imported
- [ ] User created with proper permissions
- [ ] Backup scheduled

### 4. Configuration
- [ ] db_connect.php updated with production credentials
- [ ] Error logging configured
- [ ] Session settings configured
- [ ] Timezone set correctly

### 5. Testing
- [ ] Login functionality works
- [ ] POS module operational
- [ ] Inventory management functional
- [ ] Reports generating correctly
- [ ] Payments processing
- [ ] Receipts printing

### 6. Monitoring
- [ ] Error logs monitored
- [ ] Database performance checked
- [ ] Backup verification
- [ ] User access logs reviewed

## Post-Deployment

### Maintenance
- [ ] Regular backups scheduled
- [ ] Security updates applied
- [ ] Performance monitoring active
- [ ] User support available

### Documentation
- [ ] System documentation updated
- [ ] User manual provided
- [ ] Admin guide created
- [ ] Troubleshooting guide available

### Optimization
- [ ] Database queries optimized
- [ ] Caching implemented if needed
- [ ] Asset compression enabled
- [ ] CDN configured if applicable

## Rollback Plan

If issues occur:
1. Restore from latest backup
2. Revert code changes
3. Notify users of downtime
4. Investigate root cause
5. Test thoroughly before re-deployment
\`\`\`

```apache file=".htaccess"
# Enable mod_rewrite
RewriteEngine On

# Prevent direct access to sensitive files
<FilesMatch "\.(env|sql|json|config)$">
    Order allow,deny
    Deny from all
</FilesMatch>

# Prevent directory listing
Options -Indexes

# Set security headers
<IfModule mod_headers.c>
    Header set X-Content-Type-Options "nosniff"
    Header set X-Frame-Options "SAMEORIGIN"
    Header set X-XSS-Protection "1; mode=block"
    Header set Referrer-Policy "strict-origin-when-cross-origin"
</IfModule>

# Compress output
<IfModule mod_deflate.c>
    AddOutputFilterByType DEFLATE text/html text/plain text/xml text/css text/javascript application/javascript
</IfModule>

# Cache static files
<IfModule mod_expires.c>
    ExpiresActive On
    ExpiresByType image/jpeg "access plus 1 year"
    ExpiresByType image/gif "access plus 1 year"
    ExpiresByType image/png "access plus 1 year"
    ExpiresByType text/css "access plus 1 month"
    ExpiresByType application/javascript "access plus 1 month"
</IfModule>
