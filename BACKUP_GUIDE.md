# Local Database Backup & Deployment Guide

This guide explains how to back up your **Personal Expense Tracker** database, restore it, and transfer your project to another computer or access it across your local network (LAN).

---

## 1. Where the Database is Located

- **Database Name**: `expense_tracker`
- **Database Engine**: MySQL 9.1 (running inside WAMP)
- **Host**: `127.0.0.1` (or `localhost`)
- **Port**: `3306`
- **Default Username**: `root`
- **Default Password**: *(blank / empty)*
- **Data Files Storage**: `C:\wamp64\bin\mysql\mysql9.1.0\data\expense_tracker`

---

## 2. How to Export (Backup) Using phpMyAdmin

1. Open your browser and go to:
   ```
   http://localhost/phpmyadmin
   ```
   *(Or `http://localhost:8080/phpmyadmin` depending on your Apache port)*
2. Log in (Username: `root`, Password: leave empty).
3. On the left sidebar, click on **`expense_tracker`**.
4. In the top navigation tabs, click **Export**.
5. Choose **Export method**: `Quick - display only the minimal options`.
6. Format: `SQL`.
7. Click **Export** (or **Go**).
8. A file named `expense_tracker.sql` will be saved to your computer. Keep this file in a safe place (e.g. Google Drive, USB flash drive).

### Fast CLI Backup (Alternative):
Run this in PowerShell/Command Prompt:
```powershell
& "C:\wamp64\bin\mysql\mysql9.1.0\bin\mysqldump.exe" -u root expense_tracker > "C:\wamp64\www\expense-tracker\expense_tracker_backup.sql"
```

---

## 3. How to Restore the Database

1. Open **phpMyAdmin** (`http://localhost/phpmyadmin`).
2. If `expense_tracker` does not exist, click **New** on the left sidebar, type `expense_tracker`, select `utf8mb4_unicode_ci`, and click **Create**.
3. Click on the `expense_tracker` database in the left sidebar.
4. Click the **Import** tab at the top.
5. Under "File to import", click **Choose File** and select your `expense_tracker.sql` file.
6. Scroll down and click **Import** (or **Go**). All your users, expenses, incomes, and budgets are restored!

### Fast CLI Restore (Alternative):
```powershell
& "C:\wamp64\bin\mysql\mysql9.1.0\bin\mysql.exe" -u root expense_tracker < "C:\wamp64\www\expense-tracker\expense_tracker_backup.sql"
```

---

## 4. How to Move the Project to Another Computer

1. **Copy the Files**:
   - Copy the entire folder `C:\wamp64\www\expense-tracker` to the new computer inside its `C:\wamp64\www\` directory.
2. **Export and Restore Database**:
   - Follow Step 2 on the old PC to export `expense_tracker.sql`.
   - Follow Step 3 on the new PC to import `expense_tracker.sql`.
3. **Environment File (`.env`)**:
   - Check that `C:\wamp64\www\expense-tracker\.env` points to the correct MySQL port and credentials on the new PC.
4. **Create the Storage Link**:
   ```powershell
   php artisan storage:link
   ```
5. **Launch the App**:
   - Double click `run_app.bat`!

---

## 5. How to Access From a Mobile Phone on the Same Wi-Fi (LAN)

1. Find your computer's local IP address by opening PowerShell and typing:
   ```powershell
   ipconfig
   ```
   Look for the `IPv4 Address` (for example: `192.168.1.15`).
2. Make sure the server is started with:
   ```powershell
   php artisan serve --host 0.0.0.0 --port 8000
   ```
   *(The included `run_app.bat` does this automatically!)*
3. On your mobile phone connected to the same Wi-Fi, open your mobile browser (Chrome / Safari) and visit:
   ```
   http://192.168.1.15:8000
   ```
   *(Replace with your computer's actual IP address)*
4. In Chrome for Android or Safari for iOS, tap **"Add to Home Screen"** / **"Install App"** to install it as a native standalone PWA!
