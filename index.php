<?php
require_once 'config/db.php';

if (isLoggedIn()) {
    redirect('dashboard.php');
}

$school_name = getSchoolSetting('school_name', 'EduManage School');
$school_tagline = getSchoolSetting('school_tagline', 'Excellence in Education');
$school_phone = getSchoolSetting('school_phone', '+91 1234567890');
$school_email = getSchoolSetting('school_email', 'info@school.com');
$admission_open = getSchoolSetting('admission_open', '1');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($school_name); ?> - School Management System</title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .hero {
            min-height: 100vh;
            background: linear-gradient(135deg, #4f46e5 0%, #7c3aed 100%);
            display: flex;
            flex-direction: column;
        }
        .nav {
            padding: 20px 50px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .nav-logo {
            display: flex;
            align-items: center;
            gap: 12px;
            color: white;
        }
        .nav-logo i {
            font-size: 32px;
        }
        .nav-logo h1 {
            font-size: 24px;
            font-weight: 700;
        }
        .nav-links {
            display: flex;
            gap: 30px;
            align-items: center;
        }
        .nav-links a {
            color: rgba(255,255,255,0.9);
            font-weight: 500;
            transition: color 0.2s;
        }
        .nav-links a:hover {
            color: white;
        }
        .nav-links .btn {
            background: white;
            color: #4f46e5;
            padding: 10px 24px;
            border-radius: 8px;
        }
        .nav-links .btn:hover {
            background: #f3f4f6;
        }
        .hero-content {
            flex: 1;
            display: flex;
            align-items: center;
            justify-content: center;
            text-align: center;
            padding: 40px;
        }
        .hero-text {
            max-width: 900px;
            color: white;
        }
        .hero-text h1 {
            font-size: 52px;
            font-weight: 800;
            margin-bottom: 20px;
            line-height: 1.2;
        }
        .hero-text p {
            font-size: 20px;
            opacity: 0.9;
            margin-bottom: 40px;
            max-width: 700px;
            margin-left: auto;
            margin-right: auto;
        }
        .hero-buttons {
            display: flex;
            gap: 20px;
            justify-content: center;
            flex-wrap: wrap;
        }
        .btn-white {
            background: white;
            color: #4f46e5;
        }
        .btn-white:hover {
            background: #f3f4f6;
        }
        .btn-outline-white {
            background: transparent;
            border: 2px solid white;
            color: white;
        }
        .btn-outline-white:hover {
            background: rgba(255,255,255,0.1);
        }
        .features {
            padding: 100px 50px;
            background: white;
        }
        .section-title {
            text-align: center;
            margin-bottom: 60px;
        }
        .section-title h2 {
            font-size: 42px;
            color: #1f2937;
            margin-bottom: 15px;
        }
        .section-title p {
            color: #6b7280;
            font-size: 18px;
            max-width: 600px;
            margin: 0 auto;
        }
        .features-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 30px;
            max-width: 1200px;
            margin: 0 auto;
        }
        .feature-card {
            padding: 35px;
            border-radius: 16px;
            background: #f9fafb;
            transition: all 0.3s;
            border: 1px solid #e5e7eb;
        }
        .feature-card:hover {
            transform: translateY(-8px);
            box-shadow: 0 25px 50px rgba(0,0,0,0.1);
            border-color: #4f46e5;
        }
        .feature-icon {
            width: 70px;
            height: 70px;
            border-radius: 16px;
            background: linear-gradient(135deg, #4f46e5, #7c3aed);
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 28px;
            margin-bottom: 25px;
        }
        .feature-card h3 {
            font-size: 22px;
            margin-bottom: 12px;
            color: #1f2937;
        }
        .feature-card p {
            color: #6b7280;
            line-height: 1.7;
        }
        .stats-section {
            padding: 80px 50px;
            background: linear-gradient(135deg, #1f2937, #374151);
            color: white;
        }
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 40px;
            max-width: 1000px;
            margin: 0 auto;
            text-align: center;
        }
        .stat-item h3 {
            font-size: 48px;
            font-weight: 700;
            margin-bottom: 10px;
            color: #818cf8;
        }
        .stat-item p {
            font-size: 16px;
            opacity: 0.8;
        }
        .portals {
            padding: 100px 50px;
            background: #f3f4f6;
        }
        .portals-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 30px;
            max-width: 1000px;
            margin: 0 auto;
        }
        .portal-card {
            background: white;
            border-radius: 20px;
            padding: 45px 35px;
            text-align: center;
            box-shadow: 0 4px 15px rgba(0,0,0,0.05);
            transition: all 0.3s;
            border: 2px solid transparent;
        }
        .portal-card:hover {
            transform: translateY(-10px);
            box-shadow: 0 25px 50px rgba(0,0,0,0.12);
        }
        .portal-card.admin:hover { border-color: #ef4444; }
        .portal-card.teacher:hover { border-color: #10b981; }
        .portal-card.student:hover { border-color: #4f46e5; }
        .portal-icon {
            width: 90px;
            height: 90px;
            border-radius: 50%;
            margin: 0 auto 25px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 40px;
        }
        .portal-icon.admin { background: rgba(239, 68, 68, 0.1); color: #ef4444; }
        .portal-icon.teacher { background: rgba(16, 185, 129, 0.1); color: #10b981; }
        .portal-icon.student { background: rgba(79, 70, 229, 0.1); color: #4f46e5; }
        .portal-card h3 {
            font-size: 24px;
            margin-bottom: 12px;
            color: #1f2937;
        }
        .portal-card p {
            color: #6b7280;
            margin-bottom: 25px;
            line-height: 1.6;
        }
        .contact-section {
            padding: 80px 50px;
            background: white;
        }
        .contact-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 30px;
            max-width: 900px;
            margin: 0 auto;
        }
        .contact-item {
            text-align: center;
            padding: 30px;
        }
        .contact-item i {
            font-size: 36px;
            color: #4f46e5;
            margin-bottom: 15px;
        }
        .contact-item h4 {
            font-size: 18px;
            margin-bottom: 8px;
            color: #1f2937;
        }
        .contact-item p {
            color: #6b7280;
        }
        .footer {
            background: #1f2937;
            color: white;
            padding: 50px;
        }
        .footer-content {
            max-width: 1200px;
            margin: 0 auto;
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 40px;
        }
        .footer-section h4 {
            font-size: 18px;
            margin-bottom: 20px;
            color: white;
        }
        .footer-section p, .footer-section a {
            color: rgba(255,255,255,0.7);
            line-height: 1.8;
            display: block;
        }
        .footer-section a:hover {
            color: white;
        }
        .footer-bottom {
            text-align: center;
            padding-top: 30px;
            margin-top: 40px;
            border-top: 1px solid rgba(255,255,255,0.1);
            color: rgba(255,255,255,0.5);
        }
        .admission-badge {
            position: fixed;
            right: 20px;
            bottom: 20px;
            background: #10b981;
            color: white;
            padding: 15px 25px;
            border-radius: 50px;
            font-weight: 600;
            box-shadow: 0 10px 30px rgba(16, 185, 129, 0.4);
            animation: pulse 2s infinite;
            z-index: 100;
        }
        @keyframes pulse {
            0%, 100% { transform: scale(1); }
            50% { transform: scale(1.05); }
        }
        @media (max-width: 768px) {
            .nav { padding: 15px 20px; flex-wrap: wrap; gap: 15px; }
            .nav-links { gap: 15px; }
            .hero-text h1 { font-size: 32px; }
            .hero-text p { font-size: 16px; }
            .hero-buttons { flex-direction: column; }
            .features, .portals, .contact-section { padding: 60px 20px; }
            .section-title h2 { font-size: 28px; }
            .stat-item h3 { font-size: 36px; }
        }
    </style>
</head>
<body>
    <?php if ($admission_open == '1'): ?>
        <div class="admission-badge">
            <i class="fas fa-bullhorn"></i> Admissions Open!
        </div>
    <?php endif; ?>

    <div class="hero">
        <nav class="nav">
            <div class="nav-logo">
                <i class="fas fa-graduation-cap"></i>
                <h1><?php echo htmlspecialchars($school_name); ?></h1>
            </div>
            <div class="nav-links">
                <a href="#features">Features</a>
                <a href="#portals">Portals</a>
                <a href="#contact">Contact</a>
                <a href="login.php" class="btn"><i class="fas fa-sign-in-alt"></i> Login</a>
            </div>
        </nav>
        <div class="hero-content">
            <div class="hero-text">
                <h1>Complete School Management Solution</h1>
                <p><?php echo htmlspecialchars($school_tagline); ?> - Streamline your school operations with our comprehensive management system. Handle admissions, attendance, fees, exams, and more - all in one place.</p>
                <div class="hero-buttons">
                    <a href="login.php" class="btn btn-lg btn-white">
                        <i class="fas fa-sign-in-alt"></i> Login to Portal
                    </a>
                    <a href="#features" class="btn btn-lg btn-outline-white">
                        <i class="fas fa-info-circle"></i> Explore Features
                    </a>
                </div>
            </div>
        </div>
    </div>

    <section class="stats-section">
        <div class="stats-grid">
            <div class="stat-item">
                <h3>1000+</h3>
                <p>Students Managed</p>
            </div>
            <div class="stat-item">
                <h3>50+</h3>
                <p>Expert Teachers</p>
            </div>
            <div class="stat-item">
                <h3>20+</h3>
                <p>Years Experience</p>
            </div>
            <div class="stat-item">
                <h3>100%</h3>
                <p>Digital Records</p>
            </div>
        </div>
    </section>

    <section class="features" id="features">
        <div class="section-title">
            <h2>Powerful Features</h2>
            <p>Everything you need to manage your school efficiently and effectively</p>
        </div>
        <div class="features-grid">
            <div class="feature-card">
                <div class="feature-icon"><i class="fas fa-user-graduate"></i></div>
                <h3>Student Management</h3>
                <p>Complete student records with admission forms, roll number auto-generation, comprehensive profiles, and document management.</p>
            </div>
            <div class="feature-card">
                <div class="feature-icon"><i class="fas fa-clipboard-check"></i></div>
                <h3>Attendance System</h3>
                <p>Mark and track daily attendance with detailed reports, analytics, and automated notifications for absences.</p>
            </div>
            <div class="feature-card">
                <div class="feature-icon"><i class="fas fa-file-invoice-dollar"></i></div>
                <h3>Fee Management</h3>
                <p>Track fee payments, generate receipts, manage discounts, and monitor paid/unpaid status with printable reports.</p>
            </div>
            <div class="feature-card">
                <div class="feature-icon"><i class="fas fa-chart-line"></i></div>
                <h3>Exam & Results</h3>
                <p>Manage exams, enter marks, calculate grades automatically, generate report cards and performance analytics.</p>
            </div>
            <div class="feature-card">
                <div class="feature-icon"><i class="fas fa-book"></i></div>
                <h3>Homework System</h3>
                <p>Assign homework, track submissions, provide feedback to students and keep parents informed.</p>
            </div>
            <div class="feature-card">
                <div class="feature-icon"><i class="fas fa-print"></i></div>
                <h3>Print & Export</h3>
                <p>Generate printable A4 documents including admission forms, fee slips, receipts, and result cards.</p>
            </div>
        </div>
    </section>

    <section class="portals" id="portals">
        <div class="section-title">
            <h2>Access Portals</h2>
            <p>Login to your respective portal to access all features</p>
        </div>
        <div class="portals-grid">
            <div class="portal-card admin">
                <div class="portal-icon admin"><i class="fas fa-user-shield"></i></div>
                <h3>Admin Portal</h3>
                <p>Complete control over school management, user accounts, finance, academics, and system settings.</p>
                <a href="login.php" class="btn btn-danger btn-block">
                    <i class="fas fa-sign-in-alt"></i> Admin Login
                </a>
            </div>
            <div class="portal-card teacher">
                <div class="portal-icon teacher"><i class="fas fa-chalkboard-teacher"></i></div>
                <h3>Teacher Portal</h3>
                <p>Manage classes, mark attendance, assign homework, enter exam results, and communicate with students.</p>
                <a href="login.php" class="btn btn-success btn-block">
                    <i class="fas fa-sign-in-alt"></i> Teacher Login
                </a>
            </div>
            <div class="portal-card student">
                <div class="portal-icon student"><i class="fas fa-user-graduate"></i></div>
                <h3>Student Portal</h3>
                <p>View attendance, homework, exam results, fee status, timetable and download important documents.</p>
                <a href="login.php" class="btn btn-primary btn-block">
                    <i class="fas fa-sign-in-alt"></i> Student Login
                </a>
            </div>
        </div>
    </section>

    <section class="contact-section" id="contact">
        <div class="section-title">
            <h2>Contact Us</h2>
            <p>Get in touch with us for any queries</p>
        </div>
        <div class="contact-grid">
            <div class="contact-item">
                <i class="fas fa-phone"></i>
                <h4>Phone</h4>
                <p><?php echo htmlspecialchars($school_phone); ?></p>
            </div>
            <div class="contact-item">
                <i class="fas fa-envelope"></i>
                <h4>Email</h4>
                <p><?php echo htmlspecialchars($school_email); ?></p>
            </div>
            <div class="contact-item">
                <i class="fas fa-map-marker-alt"></i>
                <h4>Address</h4>
                <p><?php echo htmlspecialchars(getSchoolAddress()); ?></p>
            </div>
        </div>
    </section>

    <footer class="footer">
        <div class="footer-content">
            <div class="footer-section">
                <h4><i class="fas fa-graduation-cap"></i> <?php echo htmlspecialchars($school_name); ?></h4>
                <p><?php echo htmlspecialchars($school_tagline); ?></p>
                <p style="margin-top: 15px;">
                    <i class="fas fa-certificate"></i> <?php echo htmlspecialchars(getSchoolSetting('school_affiliation', 'CBSE')); ?> Affiliated
                </p>
            </div>
            <div class="footer-section">
                <h4>Quick Links</h4>
                <a href="#features"><i class="fas fa-chevron-right"></i> Features</a>
                <a href="#portals"><i class="fas fa-chevron-right"></i> Portals</a>
                <a href="#contact"><i class="fas fa-chevron-right"></i> Contact</a>
                <a href="login.php"><i class="fas fa-chevron-right"></i> Login</a>
            </div>
            <div class="footer-section">
                <h4>Contact Info</h4>
                <p><i class="fas fa-phone"></i> <?php echo htmlspecialchars($school_phone); ?></p>
                <p><i class="fas fa-envelope"></i> <?php echo htmlspecialchars($school_email); ?></p>
                <p><i class="fas fa-globe"></i> <?php echo htmlspecialchars(getSchoolSetting('school_website', '')); ?></p>
            </div>
        </div>
        <div class="footer-bottom">
            <p>&copy; <?php echo date('Y'); ?> <?php echo htmlspecialchars($school_name); ?>. All rights reserved.</p>
            <p style="margin-top: 5px;">Powered by School Management System v<?php echo APP_VERSION; ?></p>
        </div>
    </footer>

    <script src="js/app.js"></script>
</body>
</html>
