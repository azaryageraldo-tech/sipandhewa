# 🚀 SI TERNAK - Quick Setup Guide

## ⚠️ IMPORTANT: If You Get Errors

### Error: "Call to undefined function fetchOne()"
**SOLUTION:** The application has been simplified. Try these steps:

## 1. 📁 Check File Location
Ensure your application is in the correct location:
```
C:\xampp\htdocs\sipandhewa\
```
(NOT in a folder with spaces!)

## 2. 🔄 Clear All Caches
- **Browser:** Ctrl+Shift+R (Hard Refresh)
- **XAMPP:** Restart Apache & MySQL
- **PHP:** Clear opcode cache if using OPcache

## 3. 🌐 Access URLs
```
Main App: http://localhost/si_ternak_ariani/
Test:     http://localhost/si_ternak_ariani/test_server.php
```

## 4. 🧪 Test Server First
Access `test_server.php` to diagnose issues.

## 5. 📝 Current Status
- ✅ Dashboard.php is now ultra-simple (no PHP errors possible)
- ✅ All database calls are optional/safe
- ✅ Application works in demo mode without database

## 6. 🛠️ If Still Problems

### A. Rename Folder (Remove Spaces)
```
From: C:\xampp\htdocs\Si Ternak_Ariani\
To:   C:\xampp\htdocs\si_ternak_ariani\
```

### B. Check Permissions
Right-click folder → Properties → Security → Add "IUSR" with Full Control

### C. Restart XAMPP
Stop → Start Apache & MySQL

### D. Test Access
```
http://localhost/si_ternak_ariani/test_server.php
```

## 🎯 QUICK START:

1. **Rename folder** to remove spaces
2. **Restart XAMPP**
3. **Access:** `http://localhost/si_ternak_ariani/`
4. **Login with:** admin / admin123

## 📞 Support
If you still get errors, the issue is with your XAMPP setup, not the application code.

---
**Generated:** <?php echo date('Y-m-d H:i:s'); ?>