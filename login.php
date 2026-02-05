<?php
require_once 'config/db.php';

if (isLoggedIn()) {
    redirect('dashboard.php');
}

$error = '';
$school_name = getSchoolSetting('school_name', 'EduManage School');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = sanitize($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    $remember = isset($_POST['remember']);

    if (empty($username) || empty($password)) {
        $error = 'Please enter both username and password';
    } else {
        try {
            $db = Database::getInstance()->getConnection();
            $stmt = $db->prepare("SELECT * FROM users WHERE (username = ? OR email = ?) AND status = 'active'");
            $stmt->execute([$username, $username]);
            $user = $stmt->fetch();

            if ($user && password_verify($password, $user['password'])) {
                // Set session variables
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['username'] = $user['username'];
                $_SESSION['role'] = $user['role'];
                $_SESSION['full_name'] = $user['full_name'];
                $_SESSION['email'] = $user['email'];
                
                // Update last login
                $stmt = $db->prepare("UPDATE users SET last_login = NOW() WHERE id = ?");
                $stmt->execute([$user['id']]);

                // Log activity
                logActivity('Login', 'User logged in successfully');

                // Create welcome notification
                createNotification($user['id'], 'Welcome Back!', 'You have successfully logged in.', 'success');

                // Remember me cookie
                if ($remember) {
                    $token = bin2hex(random_bytes(32));
                    setcookie('remember_token', $token, time() + (30 * 24 * 60 * 60), '/');
                    // In production, store this token in database
                }

                redirect('dashboard.php');
            } else {
                $error = 'Invalid username or password';
                logActivity('Failed Login', 'Failed login attempt for: ' . $username);
            }
        } catch (PDOException $e) {
            $error = 'An error occurred. Please try again later.';
            error_log("Login error: " . $e->getMessage());
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - <?php echo htmlspecialchars($school_name); ?></title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .login-page {
            min-height: 100vh;
            display: flex;
        }
        .login-left {
            flex: 1;
            background: linear-gradient(135deg, #4f46e5 0%, #7c3aed 100%);
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            padding: 60px;
            color: white;
            position: relative;
            overflow: hidden;
        }
        .login-left::before {
            content: '';
            position: absolute;
            top: -50%;
            left: -50%;
            width: 200%;
            height: 200%;
            background: radial-gradient(circle, rgba(255,255,255,0.1) 0%, transparent 70%);
            animation: rotate 30s linear infinite;
        }
        @keyframes rotate {
            from { transform: rotate(0deg); }
            to { transform: rotate(360deg); }
        }
        .login-left-content {
            position: relative;
            z-index: 1;
            text-align: center;
            max-width: 500px;
        }
        .login-left-content i.main-icon {
            font-size: 80px;
            margin-bottom: 30px;
            opacity: 0.9;
        }
        .login-left-content h1 {
            font-size: 36px;
            margin-bottom: 15px;
        }
        .login-left-content p {
            font-size: 18px;
            opacity: 0.9;
            line-height: 1.6;
        }
        .login-features {
            margin-top: 40px;
            text-align: left;
        }
        .login-features li {
            display: flex;
            align-items: center;
            gap: 12px;
            margin-bottom: 15px;
            font-size: 16px;
            opacity: 0.9;
        }
        .login-features i {
            font-size: 20px;
        }
        .login-right {
            flex: 1;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 40px;
            background: #f9fafb;
        }
        .login-box {
            background: white;
            border-radius: 20px;
            box-shadow: 0 25px 50px rgba(0, 0, 0, 0.1);
            width: 100%;
            max-width: 450px;
            padding: 50px;
        }
        .login-header {
            text-align: center;
            margin-bottom: 35px;
        }
        .login-header h2 {
            font-size: 28px;
            color: #1f2937;
            margin-bottom: 8px;
        }
        .login-header p {
            color: #6b7280;
        }
        .demo-credentials {
            background: #f3f4f6;
            border-radius: 12px;
            padding: 20px;
            margin-top: 25px;
        }
        .demo-credentials h4 {
            font-size: 14px;
            color: #374151;
            margin-bottom: 12px;
            text-align: center;
        }
        .demo-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 10px;
        }
        .demo-item {
            background: white;
            padding: 12px;
            border-radius: 8px;
            text-align: center;
            border: 1px solid #e5e7eb;
            cursor: pointer;
            transition: all 0.2s;
        }
        .demo-item:hover {
            border-color: #4f46e5;
            transform: translateY(-2px);
        }
        .demo-item strong {
            display: block;
            font-size: 12px;
            margin-bottom: 4px;
        }
        .demo-item.admin strong { color: #ef4444; }
        .demo-item.teacher strong { color: #10b981; }
        .demo-item.student strong { color: #4f46e5; }
        .demo-item span {
            font-size: 11px;
            color: #6b7280;
        }
        .password-toggle {
            position: relative;
        }
        .password-toggle .toggle-btn {
            position: absolute;
            right: 15px;
            top: 50%;
            transform: translateY(-50%);
            background: none;
            border: none;
            color: #9ca3af;
            cursor: pointer;
        }
        @media (max-width: 1024px) {
            .login-left { display: none; }
            .login-right { padding: 20px; }
        }
        @media (max-width: 480px) {
            .login-box { padding: 30px 20px; }
            .demo-grid { grid-template-columns: 1fr; }
        }
    </style>
</head>
<body>
    <div class="login-page">
        <div class="login-left">
            <div class="login-left-content">
                <i class="fas fa-graduation-cap main-icon"></i>
                <h1><?php echo htmlspecialchars($school_name); ?></h1>
                <p>Your complete school management solution for efficient administration and better learning outcomes.</p>
                <ul class="login-features">
                    <li><i class="fas fa-check-circle"></i> Complete Student & Staff Management</li>
                    <li><i class="fas fa-check-circle"></i> Real-time Attendance Tracking</li>
                    <li><i class="fas fa-check-circle"></i> Comprehensive Fee Management</li>
                    <li><i class="fas fa-check-circle"></i> Exam & Result Management</li>
                    <li><i class="fas fa-check-circle"></i> Printable Reports & Documents</li>
                </ul>
            </div>
        </div>
        <div class="login-right">
            <div class="login-box">
                <div class="login-header">
                    <h2><i class="fas fa-sign-in-alt"></i> Welcome Back</h2>
                    <p>Sign in to continue to your dashboard</p>
                </div>

                <?php if ($error): ?>
                    <div class="alert alert-danger">
                        <i class="fas fa-exclamation-circle"></i>
                        <?php echo htmlspecialchars($error); ?>
                    </div>
                <?php endif; ?>

                <?php if (isset($_GET['logout'])): ?>
                    <div class="alert alert-success">
                        <i class="fas fa-check-circle"></i>
                        You have been logged out successfully.
                    </div>
                <?php endif; ?>

                <form method="POST" action="" id="loginForm">
                    <div class="form-group">
                        <label for="username"><i class="fas fa-user"></i> Username or Email</label>
                        <input type="text" id="username" name="username" class="form-control" 
                               placeholder="Enter your username or email" required 
                               value="<?php echo isset($_POST['username']) ? htmlspecialchars($_POST['username']) : ''; ?>"
                               autocomplete="username">
                    </div>

                    <div class="form-group">
                        <label for="password"><i class="fas fa-lock"></i> Password</label>
                        <div class="password-toggle">
                            <input type="password" id="password" name="password" class="form-control" 
                                   placeholder="Enter your password" required autocomplete="current-password">
                            <button type="button" class="toggle-btn" onclick="togglePassword()">
                                <i class="fas fa-eye" id="toggleIcon"></i>
                            </button>
                        </div>
                    </div>

                    <div class="form-group" style="display: flex; justify-content: space-between; align-items: center;">
                        <label style="display: flex; align-items: center; gap: 8px; cursor: pointer; margin: 0;">
                            <input type="checkbox" name="remember" style="width: auto;">
                            <span style="font-weight: normal; font-size: 14px;">Remember me</span>
                        </label>
                        <a href="forgot-password.php" style="color: var(--primary); font-size: 14px;">Forgot Password?</a>
                    </div>

                    <button type="submit" class="btn btn-primary btn-block btn-lg" style="margin-top: 10px;">
                        <i class="fas fa-sign-in-alt"></i> Sign In
                    </button>
                </form>

                <div class="demo-credentials">
                    <h4><i class="fas fa-info-circle"></i> Quick Login (Demo)</h4>
                    <div class="demo-grid">
                        <div class="demo-item admin" onclick="fillCredentials('admin', 'admin123')">
                            <strong><i class="fas fa-user-shield"></i> Admin</strong>
                            <span>admin / admin123</span>
                        </div>
                        <div class="demo-item teacher" onclick="fillCredentials('teacher', 'teacher123')">
                            <strong><i class="fas fa-chalkboard-teacher"></i> Teacher</strong>
                            <span>teacher / teacher123</span>
                        </div>
                        <div class="demo-item student" onclick="fillCredentials('student', 'student123')">
                            <strong><i class="fas fa-user-graduate"></i> Student</strong>
                            <span>student / student123</span>
                        </div>
                    </div>
                </div>

                <div style="text-align: center; margin-top: 25px;">
                    <a href="index.php" style="color: #6b7280; font-size: 14px;">
                        <i class="fas fa-arrow-left"></i> Back to Home
                    </a>
                </div>
            </div>
        </div>
    </div>

    <script>
        function fillCredentials(username, password) {
            document.getElementById('username').value = username;
            document.getElementById('password').value = password;
            document.getElementById('username').focus();
        }
        
        function togglePassword() {
            const passwordField = document.getElementById('password');
            const toggleIcon = document.getElementById('toggleIcon');
            
            if (passwordField.type === 'password') {
                passwordField.type = 'text';
                toggleIcon.classList.remove('fa-eye');
                toggleIcon.classList.add('fa-eye-slash');
            } else {
                passwordField.type = 'password';
                toggleIcon.classList.remove('fa-eye-slash');
                toggleIcon.classList.add('fa-eye');
            }
        }
    </script>
</body>
</html>
