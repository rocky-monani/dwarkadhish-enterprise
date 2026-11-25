# 🚀 FRESH PC INSTALLATION & SETUP GUIDE — DWARKADHISH ENTERPRISE

This guide provides step-by-step instructions for installing and running **Dwarkadhish Enterprise** on a completely **NEW Windows PC**.


---

## ⚡ FASTEST METHOD: 1-Click Automated Setup via GitHub (`install_fresh_pc_from_github.bat`)

If you want everything handled automatically:
1. Copy or download **`install_fresh_pc_from_github.bat`** onto your new PC.
2. Double-click it!
3. The script will automatically:
   - Check and install **Git, Node.js (LTS), Composer, and Chrome** via `winget`
   - Prompt/verify **XAMPP** (`C:\xampp`)
   - Fetch and clone the latest project files from GitHub (`https://github.com/rocky-monani/dwarkadhish-enterprise.git`)
   - Start MySQL and create the `dwarkadhish_enterprise` database
   - Install all Composer & Node dependencies + Playwright Chromium browser
   - Migrate database tables & seed initial accounts
   - Build frontend assets and create a **Desktop shortcut**

---

## 📥 STEP 1: Download Prerequisites (One-Time Setup)

Download and install these **3 required software programs** on the new PC:

### 1️⃣ XAMPP for Windows (Provides PHP & MySQL)
- **Download Link:** [https://www.apachefriends.org/download.html](https://www.apachefriends.org/download.html)
- **Version:** Download **XAMPP for Windows (PHP 8.2 or PHP 8.3)**.
- **Installation:**
  1. Run the installer (`xampp-windows-x64-8.2.x-installer.exe`).
  2. Click **Next** through all default prompts.
  3. Destination folder: `C:\xampp` (or `D:\xampp`).
  4. Click **Finish**.

### 2️⃣ Node.js (Provides Automation Engine)
- **Download Link:** [https://nodejs.org/en/download](https://nodejs.org/en/download)
- **Version:** Download **Node.js LTS (v20+)** Windows Installer (`.msi`).
- **Installation:**
  1. Open the downloaded `.msi` file.
  2. Click **Next** → Accept License → **Next** → **Install** → **Finish**.

### 3️⃣ Composer for Windows (PHP Package Manager)
- **Download Link:** [https://getcomposer.org/Composer-Setup.exe](https://getcomposer.org/Composer-Setup.exe)
- **Installation:**
  1. Open `Composer-Setup.exe`.
  2. Choose **Install for all users**.
  3. When prompted for PHP location, select `C:\xampp\php\php.exe` (or `D:\xampp\php\php.exe`).
  4. Click **Next** → **Install** → **Finish**.

---

## 📁 STEP 2: Copy Application Folder to New PC

1. Copy your `dwarkadhish enterprise` project folder onto the new PC.
2. Recommended location:
   - `C:\xampp\htdocs\dwarkadhish enterprise`
   - *(or `D:\xampp\htdocs\dwarkadhish enterprise`)*

---

## ⚙️ STEP 3: First-Time Setup (`setup.bat`)

1. Open the `dwarkadhish enterprise` folder.
2. Double-click **`setup.bat`**.

**What `setup.bat` automatically configures:**
- Creates `.env` configuration file.
- Creates `database.sqlite` database file.
- Installs Composer & Node dependencies.
- Generates Laravel security keys.
- Downloads Chromium browser binaries for Battery Connect automation.
- Creates database tables & inserts default Admin user.
- Builds production UI assets & storage links.

*(Note: `setup.bat` is only required ONCE when setting up a new PC).*

---

## ▶️ STEP 4: Daily Software Launch (`start.bat`)

Whenever you want to run the application:

1. Double-click **`start.bat`**.
2. **`start.bat` automatically handles everything:**
   - Starts XAMPP Apache & MySQL silently in the background (no need to open XAMPP Control Panel).
   - Starts Node Automation Server (`http://127.0.0.1:3000`).
   - Starts Laravel Application Server (`http://127.0.0.1:8000`).
   - Automatically opens **Google Chrome** to `http://127.0.0.1:8000`.

---

## 🌐 STEP 5: LAN / Local Network Access (Other PCs & Mobiles)

You can operate the software from **PC 2**, tablets, or mobile phones on the same WiFi network without installing anything on those devices:

1. On **Main PC**:
   - Double-click **`start.bat`**.
   - Note the **OTHER PCs ON LAN** address printed in the window (e.g. `http://192.168.1.50:8000`).
   - *(If Windows Firewall blocks access from other PCs, right-click `allow_firewall.bat` and select "Run as Administrator").*

2. On **Other Devices**:
   - Open Chrome browser.
   - Enter the LAN IP URL (e.g. `http://192.168.1.50:8000`).
   - Log in using `admin@dwarkadhish.com` / `password`.

---

## ⏹️ STEP 6: Daily Software Shutdown (`stop.bat`)

When you finish working for the day:

1. Double-click **`stop.bat`**.
2. It will cleanly terminate all background servers, Node daemons, and XAMPP background processes.

---

## 🔐 Default Login Accounts & Roles

| Role | Name | Email / Login ID | Default Password | Permissions |
| :--- | :--- | :--- | :--- | :--- |
| **Main Admin** | Dwarkadhish Admin | `admin@gmail.com` | `admin@123` | Full Access (All Features, Settings, Excel Import/Export, Delete) |
| **Secondary Admin** | Service Center Admin | `admin@amaron.local` | `password` | Admin Access |
| **Operator 1** | Inward Entry Operator | `inward@gmail.com` | `inward@123` | New Complaint Inward Entry & Customer Registration |
| **Operator 2** | Pending & Case Operator | `case@gmail.com` | `case@123` | Pending Cases, CRM Case Number Entry, Settlement & Printing |
| **Viewer** | Read-Only Viewer | `viewer@gmail.com` | `viewer@123` | **Read-Only Access**: View All Complaints, Search/Filter, Full Record Details, Print Documents & Export Data |

---

## 🛠️ Handy Management Scripts

- **`update.bat`**: Double-click anytime to automatically pull the latest updated files from GitHub, run database migrations, and clear caches.
- **`start.bat`**: Double-click to start XAMPP, Node automation, Laravel server, and launch Chrome browser.
- **`stop.bat`**: Double-click to cleanly stop all background servers and services.
- **`allow_firewall.bat`**: Run as Administrator if access from other PCs/mobiles on LAN is blocked by Windows Firewall.

