# Flooring Magic Shopify App

A Shopify embedded app for managing flooring products with special pricing features.

## Deployment on Railway

This app is configured to deploy automatically on Railway.

### Features
- Product management dashboard
- Bulk price updates
- Square footage calculations
- Carrier API integration
- Webhook support

### Tech Stack
- PHP 8.2
- MySQL Database
- Shopify API

### Environment Variables Needed
None - credentials are in keys.php (update before deploying to production)

### Auto-Deploy
Push to main branch and Railway will automatically deploy.

## Local Development
```bash
php -S localhost:8000
```

## Shopify App Configuration
After deployment, update your Shopify Partner Dashboard:
- App URL: https://your-railway-domain.up.railway.app/index.php
- Redirect URL: https://your-railway-domain.up.railway.app/login.php
