# 🚀 RAILWAY DEPLOYMENT GUIDE

## STEP-BY-STEP DEPLOYMENT

### 1. Create GitHub Repository
1. Go to https://github.com/new
2. Repository name: `shopify-flooring-app`
3. Description: "Shopify app for flooring products"
4. Make it PRIVATE
5. Click "Create repository"

### 2. Upload Code to GitHub

**Option A: Using GitHub Web Interface (Easiest)**
1. On your new repository page, click "uploading an existing file"
2. Drag and drop ALL files from the railway_deploy folder
3. Scroll down and click "Commit changes"

**Option B: Using Git (If you have Git installed)**
```bash
git init
git add .
git commit -m "Initial commit"
git branch -M main
git remote add origin https://github.com/YOUR_USERNAME/shopify-flooring-app.git
git push -u origin main
```

### 3. Connect Railway to GitHub
1. Go back to Railway (https://railway.app/)
2. Click "New Project"
3. Select "Deploy from GitHub repo"
4. If asked, authorize Railway to access GitHub
5. Select your `shopify-flooring-app` repository
6. Railway will auto-detect PHP and deploy!

### 4. Get Your Railway URL
1. Once deployed, click on your project
2. Go to "Settings" tab
3. Click "Generate Domain"
4. Copy the domain (e.g., `yourapp.up.railway.app`)

### 5. Add MySQL Database (Required!)
1. In Railway, click "New" → "Database" → "MySQL"
2. Wait for it to provision
3. Railway will automatically connect it to your app

### 6. Update Shopify Partner Dashboard
1. Go to https://partners.shopify.com/
2. Navigate to your app
3. Update App URL: `https://yourapp.up.railway.app/index.php`
4. Update Redirect URL: `https://yourapp.up.railway.app/login.php`
5. Save

### 7. Test Your App!
1. Go to Shopify Admin
2. Click Apps → Flooring Magic App
3. Should load the dashboard!

## Troubleshooting

**If deployment fails:**
- Check Railway logs (click "Deployments" → View logs)
- Make sure all files were uploaded to GitHub

**If app loads but shows errors:**
- Check that MySQL database is connected
- Verify connection.php has correct credentials

**If you see "refused to connect":**
- Verify the App URL in Shopify is correct
- Make sure it includes `/index.php` at the end

## Files Included
✅ All PHP files (index, login, dashboard, etc.)
✅ Railway configuration (nixpacks.toml, railway.json)
✅ Database connection
✅ Shopify API integration
✅ .gitignore for sensitive files

## Support
If you encounter issues, check:
1. Railway deployment logs
2. Browser console (F12)
3. Shopify Partner Dashboard app settings
