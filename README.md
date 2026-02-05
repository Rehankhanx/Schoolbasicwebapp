# School Management System - Complete Installation Guide

## 📋 System Requirements

- **XAMPP** (Apache + MySQL + PHP 7.4+)
- **Web Browser** (Chrome, Firefox, Edge)
- **Minimum RAM**: 2GB
- **Disk Space**: 500MB

---

## 🚀 Quick Installation (5 Minutes)

### Step 1: Install XAMPP

1. Download XAMPP from: https://www.apachefriends.org/download.html
2. Run installer and install to `C:\xampp` (or USB drive like `E:\xampp`)
3. During installation, select: Apache, MySQL, PHP, phpMyAdmin

### Step 2: Start XAMPP Services

1. Open **XAMPP Control Panel** (run as Administrator)
2. Click **Start** for **Apache** (should turn green)
3. Click **Start** for **MySQL** (should turn green)

### Step 3: Copy Project Files

1. Navigate to XAMPP's web folder:
   - Windows: `C:\xampp\htdocs\`
   - USB: `E:\xampp\htdocs\`
   
2. Create a folder named `schoolapp`

3. Copy ALL project files into `schoolapp` folder

Your folder structure should look like:
```
C:\xampp\htdocs\schoolapp\
├── index.php
├── login.php
├── dashboard.php
├── logout.php
├── profile.php
├── database.sql
├── README.md
├── config/
│   └── db.php
├── css/
│   └── style.css
├── js/
│   └── app.js
├── admin/
│   ├── students.php
│   ├── teachers.php
│   ├── classes.php
│   ├── subjects.php
│   ├── fees.php
│   ├── payments.php
│   ├── attendance.php
│   ├── exams.php
│   ├── results.php
│   ├── homework.php
│   ├── settings.php
│   ├── reports.php
│   ├── admission_form.php
│   ├── fee_slip.php
│   └── receipt.php
├── teacher/
│   ├── students.php
│   ├── attendance.php
│   ├── homework.php
│   └── exams.php
├── student/
│   ├── attendance.php
│   ├── homework.php
│   ├── results.php
│   ├── fees.php
│   └── receipts.php
├── includes/
│   ├── sidebar.php
│   └── header.php
├── uploads/
│   ├── students/
│   ├── teachers/
│   ├── documents/
│   └── general/
└── logs/
```

### Step 4: Create Database

**Method 1: Using phpMyAdmin (Recommended)**

1. Open browser and go to: `http://localhost/phpmyadmin`
2. Click **"New"** in left sidebar
3. Enter database name: `school_db`
4. Select Collation: `utf8mb4_general_ci`
5. Click **"Create"**
6. Click on `school_db` in left sidebar
7. Click **"Import"** tab at top
8. Click **"Choose File"** and select `database.sql` from your schoolapp folder
9. Scroll down and click **"Go"**
10. Wait for success message: "Import has been successfully finished"

**Method 2: Using SQL Tab**

1. Open `http://localhost/phpmyadmin`
2. Click **"New"** → Enter `school_db` → Click **"Create"**
3. Select `school_db` from left sidebar
4. Click **"SQL"** tab
5. Open `database.sql` in Notepad, copy all content
6. Paste into SQL query box
7. Click **"Go"**

### Step 5: Access the Application

1. Open your web browser
2. Go to: `http://localhost/schoolapp`
3. You will see the school landing page
4. Click "Login" to access the system

---

## 🔑 Login Credentials

| Role     | Username  | Password     | Access Level |
|----------|-----------|--------------|--------------|
| Admin    | admin     | admin123     | Full Access  |
| Teacher  | teacher   | teacher123   | Class Management |
| Student  | student   | student123   | View Only    |

**Note**: Only Admin account exists by default. Create teacher and student accounts through Admin panel.

---

## ⚙️ Configuration

### School Settings

After logging in as Admin:
1. Go to **Settings** from sidebar
2. Update school information:
   - School Name
   - Address, Phone, Email
   - Principal Name
   - Logo and Stamp
   - Academic Year
   - Fee Settings
   - Attendance Timing

### Database Configuration

Edit `config/db.php` if needed:
```php
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');          // Default XAMPP has no password
define('DB_NAME', 'school_db');
```

---

## 📱 Features

### Admin Portal
- ✅ **Dashboard** - Overview of all activities
- ✅ **Student Management** - Add, edit, delete students with auto roll numbers
- ✅ **Teacher Management** - Staff records with employee IDs
- ✅ **Class Management** - Create classes, assign teachers
- ✅ **Subject Management** - Define subjects per class
- ✅ **Fee Management** - Create fee structures, track payments
- ✅ **Payment Collection** - Receive fees, generate receipts
- ✅ **Attendance** - Mark and view attendance
- ✅ **Exam Management** - Create exams, schedules
- ✅ **Result Management** - Enter marks, calculate grades
- ✅ **Reports** - Generate various reports
- ✅ **Settings** - Configure all school settings
- ✅ **Print Documents** - Admission forms, fee slips, receipts

### Teacher Portal
- ✅ Mark daily attendance
- ✅ Assign homework
- ✅ Enter exam marks
- ✅ View student list
- ✅ Track homework submissions

### Student Portal
- ✅ View attendance record
- ✅ Check homework assignments
- ✅ View exam results
- ✅ Check fee status
- ✅ Download receipts

---

## 🖨️ Printable Documents

All documents are A4 size and print-ready:

1. **Admission Form** - Student details for records
2. **Fee Slip/Challan** - Monthly fee details
3. **Payment Receipt** - After fee payment
4. **Result Card** - Exam results with grades
5. **Attendance Report** - Monthly attendance summary

---

## 🔧 Troubleshooting

### Apache Won't Start
- **Port 80 in use**: Skype, IIS, or other software using port 80
- **Solution**: 
  1. Stop conflicting software, OR
  2. Change Apache port in XAMPP: Config → Apache (httpd.conf)
  3. Find `Listen 80` and change to `Listen 8080`
  4. Access via: `http://localhost:8080/schoolapp`

### MySQL Won't Start
- **Port 3306 in use**: Another MySQL instance running
- **Solution**: Stop other MySQL service from Windows Services

### Database Connection Error
```
Connection failed: Access denied for user 'root'@'localhost'
```
- Open `config/db.php`
- Set correct password if you've changed MySQL root password

### Blank Page or PHP Errors
1. Open `C:\xampp\php\php.ini`
2. Find `display_errors = Off`
3. Change to `display_errors = On`
4. Restart Apache

### 404 Not Found
- Ensure files are in `htdocs/schoolapp/` folder
- Check folder name spelling (case-sensitive on some systems)

### Import Error in phpMyAdmin
- Max file size exceeded: Split SQL file or increase limit
- Edit `C:\xampp\php\php.ini`:
  ```
  upload_max_filesize = 64M
  post_max_size = 64M
  ```
- Restart Apache

---

## 📁 USB Portable Installation

To run from USB drive:

1. Install XAMPP directly on USB (e.g., `E:\xampp`)
2. Copy schoolapp to `E:\xampp\htdocs\schoolapp`
3. Run `E:\xampp\xampp-control.exe` (portable mode)
4. Start Apache and MySQL
5. Access `http://localhost/schoolapp`

**Benefits**:
- Carry entire school system on USB
- No installation needed on new computers
- Data travels with you

---

## 🔒 Security Recommendations

For Production Use:

1. **Change Default Passwords**
   - Change admin password immediately
   - Use strong passwords (8+ chars, mixed case, numbers)

2. **Database Security**
   - Set MySQL root password
   - Update `config/db.php` with new password

3. **File Permissions**
   - uploads/ folder: 755
   - logs/ folder: 755
   - config/ folder: 644

4. **HTTPS**
   - Use SSL certificate for production
   - Enable HTTPS in Apache

---

## 📞 Quick Reference

| Action | URL |
|--------|-----|
| Home Page | http://localhost/schoolapp |
| Login | http://localhost/schoolapp/login.php |
| Dashboard | http://localhost/schoolapp/dashboard.php |
| phpMyAdmin | http://localhost/phpmyadmin |
| XAMPP Dashboard | http://localhost |

---

## 🔄 Backup Database

### Manual Backup
1. Open phpMyAdmin
2. Select `school_db`
3. Click **Export**
4. Choose **Quick** or **Custom**
5. Click **Go** to download .sql file

### Restore Backup
1. Open phpMyAdmin
2. Create new database or select existing
3. Click **Import**
4. Choose your backup .sql file
5. Click **Go**

---

## 📝 Creating First Student

1. Login as **admin**
2. Go to **Students** → Click **Add Student**
3. Fill in details:
   - Full Name, Username, Email, Password
   - Select Class
   - Date of Birth, Gender
   - Parent/Guardian details
4. Click **Add Student**
5. Roll number is auto-generated

---

## 🎓 Academic Year Setup

1. Go to **Settings** → **Academic** tab
2. Set:
   - Current Academic Year (e.g., 2024-2025)
   - Session Start Date
   - Session End Date
3. Save settings

---

## 💰 Fee Setup

1. Go to **Fee Management**
2. Define fee structure:
   - Select Class
   - Set Amount
   - Choose Frequency (Monthly/Yearly)
3. Generate fees for students
4. Collect payments and print receipts

---

## 📊 Reports Available

- Student List Report
- Attendance Report (Daily/Monthly)
- Fee Collection Report
- Fee Defaulters List
- Exam Results Summary
- Class-wise Performance

---

## 🆘 Support

For issues:
1. Check this README first
2. Verify XAMPP is running (Apache + MySQL green)
3. Check `logs/error.log` for PHP errors
4. Verify database exists in phpMyAdmin

---

## 📌 Version Information

- **Version**: 2.0.0
- **PHP Required**: 7.4+
- **MySQL Required**: 5.7+
- **Last Updated**: 2024

---

**© School Management System. All rights reserved.**
