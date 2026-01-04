# 🚨 SI TERNAK - TROUBLESHOOTING GUIDE

## ❌ PROBLEM: "Not Found" Error

### ✅ SOLUTION: Fix XAMPP Setup

## Step 1: Check XAMPP Installation
1. Open XAMPP Control Panel
2. Make sure Apache & MySQL are GREEN (running)
3. If red, click "Start" for both

## Step 2: Check Folder Location
1. Open Windows Explorer
2. Go to: `C:\xampp\htdocs\`
3. Make sure `Si Ternak_Ariani` folder exists here

## Step 3: Rename Folder (IMPORTANT!)
```
RENAME THIS FOLDER:
From: C:\xampp\htdocs\Si Ternak_Ariani (with space)
To:   C:\xampp\htdocs\sipandhewa (no space)
```

## Step 4: Restart XAMPP
1. Click "Stop" for Apache & MySQL
2. Click "Start" for Apache & MySQL
3. Wait until both are green

## Step 5: Test Access
Open browser and go to:
```
http://localhost/si_ternak_ariani/
```

## ✅ WORKING URLs After Fix:
```
http://localhost/si_ternak_ariani/
http://localhost/si_ternak_ariani/index.php
http://localhost/si_ternak_ariani/login.php
http://localhost/si_ternak_ariani/Dashboard.php
http://localhost/si_ternak_ariani/test_server.php
```

## 🔍 If Still Not Working:

### Check Apache Port
- Make sure Apache is running on port 80
- Check XAMPP control panel for port conflicts

### Check Permissions
1. Right-click the folder
2. Properties → Security → Edit
3. Add user "IUSR" with Full Control

### Test Basic PHP
Create file: `C:\xampp\htdocs\test.php`
```php
<?php echo "PHP works!"; ?>
```
Access: `http://localhost/test.php`

## 🎯 QUICK FIX SUMMARY:

1. **Rename folder** (remove space)
2. **Restart XAMPP**
3. **Access:** `http://localhost/si_ternak_ariani/`
4. **Login:** admin / admin123

---
**If you follow these steps, the application WILL work!**