<?php
require_once 'config/db.php';

if (!isLoggedIn()) {
    redirect('login.php');
}

$db = Database::getInstance()->getConnection();
$message = '';
$error = '';

// Fetch user data
$stmt = $db->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$_SESSION['user_id']]);
$user = $stmt->fetch();

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    if ($action === 'update_profile') {
        try {
            $stmt = $db->prepare("UPDATE users SET full_name = ?, email = ?, phone = ?, address = ? WHERE id = ?");
            $stmt->execute([
                sanitize($_POST['full_name']),
                sanitize($_POST['email']),
                sanitize($_POST['phone']),
                sanitize($_POST['address']),
                $_SESSION['user_id']
            ]);
            $_SESSION['full_name'] = sanitize($_POST['full_name']);
            $message = "Profile updated successfully!";
            
            // Refresh user data
            $stmt = $db->prepare("SELECT * FROM users WHERE id = ?");
            $stmt->execute([$_SESSION['user_id']]);
            $user = $stmt->fetch();
        } catch (Exception $e) {
            $error = "Error updating profile: " . $e->getMessage();
        }
    } elseif ($action === 'change_password') {
        $current_password = $_POST['current_password'];
        $new_password = $_POST['new_password'];
        $confirm_password = $_POST['confirm_password'];
        
        if (!password_verify($current_password, $user['password'])) {
            $error = "Current password is incorrect";
        } elseif ($new_password !== $confirm_password) {
            $error = "New passwords do not match";
        } elseif (strlen($new_password) < 6) {
            $error = "Password must be at least 6 characters";
        } else {
            try {
                $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
                $stmt = $db->prepare("UPDATE users SET password = ? WHERE id = ?");
                $stmt->execute([$hashed_password, $_SESSION['user_id']]);
                $message = "Password changed successfully!";
            } catch (Exception $e) {
                $error = "Error changing password: " . $e->getMessage();
            }
        }
    }
}

// Get additional info based on role
$extra_info = null;
if ($_SESSION['role'] === 'student') {
    $stmt = $db->prepare("SELECT s.*, c.class_name, c.section FROM students s LEFT JOIN classes c ON s.class_id = c.id WHERE s.user_id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $extra_info = $stmt->fetch();
} elseif ($_SESSION['role'] === 'teacher') {
    $stmt = $db->prepare("SELECT * FROM teachers WHERE user_id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $extra_info = $stmt->fetch();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profile - School Management System</title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <div class="dashboard-wrapper">
        <!-- Sidebar -->
        <aside class="sidebar">
            <div class="sidebar-header">
                <div class="logo-icon"><i class="fas fa-graduation-cap"></i></div>
                <h2>EduManage</h2>
            </div>
            <nav class="sidebar-menu">
                <div class="menu-label">Main</div>
                <a href="dashboard.php" class="menu-item"><i class="fas fa-home"></i><span>Dashboard</span></a>
                
                <?php if ($_SESSION['role'] === 'admin'): ?>
                    <div class="menu-label">Management</div>
                    <a href="admin/students.php" class="menu-item"><i class="fas fa-user-graduate"></i><span>Students</span></a>
                    <a href="admin/teachers.php" class="menu-item"><i class="fas fa-chalkboard-teacher"></i><span>Teachers</span></a>
                    <a href="admin/classes.php" class="menu-item"><i class="fas fa-school"></i><span>Classes</span></a>
                    <div class="menu-label">Finance</div>
                    <a href="admin/fees.php" class="menu-item"><i class="fas fa-file-invoice-dollar"></i><span>Fee Management</span></a>
                <?php elseif ($_SESSION['role'] === 'teacher'): ?>
                    <div class="menu-label">My Class</div>
                    <a href="teacher/students.php" class="menu-item"><i class="fas fa-users"></i><span>My Students</span></a>
                    <a href="teacher/attendance.php" class="menu-item"><i class="fas fa-clipboard-check"></i><span>Attendance</span></a>
                <?php else: ?>
                    <div class="menu-label">Academics</div>
                    <a href="student/attendance.php" class="menu-item"><i class="fas fa-calendar-check"></i><span>My Attendance</span></a>
                    <a href="student/results.php" class="menu-item"><i class="fas fa-chart-line"></i><span>My Results</span></a>
                <?php endif; ?>
                
                <div class="menu-label">Account</div>
                <a href="profile.php" class="menu-item active"><i class="fas fa-user-circle"></i><span>Profile</span></a>
                <a href="logout.php" class="menu-item"><i class="fas fa-sign-out-alt"></i><span>Logout</span></a>
            </nav>
        </aside>

        <main class="main-content">
            <header class="header">
                <div class="header-left">
                    <button id="sidebarToggle" class="header-icon"><i class="fas fa-bars"></i></button>
                    <h1>My Profile</h1>
                </div>
                <div class="header-right">
                    <div class="user-menu">
                        <div class="user-avatar"><?php echo strtoupper(substr($_SESSION['full_name'], 0, 1)); ?></div>
                        <div class="user-info">
                            <h4><?php echo htmlspecialchars($_SESSION['full_name']); ?></h4>
                            <span><?php echo ucfirst($_SESSION['role']); ?></span>
                        </div>
                    </div>
                </div>
            </header>

            <div class="page-content">
                <?php if ($message): ?>
                    <div class="alert alert-success"><i class="fas fa-check-circle"></i> <?php echo $message; ?></div>
                <?php endif; ?>
                <?php if ($error): ?>
                    <div class="alert alert-danger"><i class="fas fa-exclamation-circle"></i> <?php echo $error; ?></div>
                <?php endif; ?>

                <div style="display: grid; grid-template-columns: 1fr 2fr; gap: 20px;">
                    <!-- Profile Card -->
                    <div class="card">
                        <div class="card-body text-center">
                            <div style="width: 120px; height: 120px; border-radius: 50%; background: var(--primary); color: white; display: flex; align-items: center; justify-content: center; font-size: 48px; margin: 0 auto 20px;">
                                <?php echo strtoupper(substr($user['full_name'], 0, 1)); ?>
                            </div>
                            <h2 style="margin-bottom: 5px;"><?php echo htmlspecialchars($user['full_name']); ?></h2>
                            <p class="text-muted"><?php echo ucfirst($user['role']); ?></p>
                            <hr style="margin: 20px 0;">
                            
                            <div style="text-align: left;">
                                <p style="margin-bottom: 10px;">
                                    <i class="fas fa-envelope" style="width: 20px; color: var(--gray-500);"></i>
                                    <?php echo htmlspecialchars($user['email']); ?>
                                </p>
                                <p style="margin-bottom: 10px;">
                                    <i class="fas fa-phone" style="width: 20px; color: var(--gray-500);"></i>
                                    <?php echo htmlspecialchars($user['phone'] ?: 'Not provided'); ?>
                                </p>
                                <p style="margin-bottom: 10px;">
                                    <i class="fas fa-user" style="width: 20px; color: var(--gray-500);"></i>
                                    @<?php echo htmlspecialchars($user['username']); ?>
                                </p>
                                <p>
                                    <i class="fas fa-calendar" style="width: 20px; color: var(--gray-500);"></i>
                                    Joined <?php echo formatDate($user['created_at']); ?>
                                </p>
                            </div>
                            
                            <?php if ($extra_info): ?>
                                <hr style="margin: 20px 0;">
                                <?php if ($_SESSION['role'] === 'student'): ?>
                                    <div style="text-align: left;">
                                        <p style="margin-bottom: 10px;">
                                            <strong>Roll Number:</strong> <?php echo htmlspecialchars($extra_info['roll_number']); ?>
                                        </p>
                                        <p style="margin-bottom: 10px;">
                                            <strong>Admission No:</strong> <?php echo htmlspecialchars($extra_info['admission_number']); ?>
                                        </p>
                                        <p>
                                            <strong>Class:</strong> <?php echo htmlspecialchars($extra_info['class_name'] . ' - ' . $extra_info['section']); ?>
                                        </p>
                                    </div>
                                <?php elseif ($_SESSION['role'] === 'teacher'): ?>
                                    <div style="text-align: left;">
                                        <p style="margin-bottom: 10px;">
                                            <strong>Employee ID:</strong> <?php echo htmlspecialchars($extra_info['employee_id']); ?>
                                        </p>
                                        <p style="margin-bottom: 10px;">
                                            <strong>Qualification:</strong> <?php echo htmlspecialchars($extra_info['qualification'] ?: 'N/A'); ?>
                                        </p>
                                        <p>
                                            <strong>Specialization:</strong> <?php echo htmlspecialchars($extra_info['specialization'] ?: 'N/A'); ?>
                                        </p>
                                    </div>
                                <?php endif; ?>
                            <?php endif; ?>
                        </div>
                    </div>

                    <!-- Edit Forms -->
                    <div>
                        <!-- Update Profile -->
                        <div class="card mb-4">
                            <div class="card-header">
                                <h3><i class="fas fa-edit"></i> Update Profile</h3>
                            </div>
                            <div class="card-body">
                                <form method="POST">
                                    <input type="hidden" name="action" value="update_profile">
                                    <div class="form-row">
                                        <div class="form-group">
                                            <label>Full Name *</label>
                                            <input type="text" name="full_name" class="form-control" required value="<?php echo htmlspecialchars($user['full_name']); ?>">
                                        </div>
                                        <div class="form-group">
                                            <label>Email *</label>
                                            <input type="email" name="email" class="form-control" required value="<?php echo htmlspecialchars($user['email']); ?>">
                                        </div>
                                    </div>
                                    <div class="form-row">
                                        <div class="form-group">
                                            <label>Phone</label>
                                            <input type="text" name="phone" class="form-control" value="<?php echo htmlspecialchars($user['phone']); ?>">
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label>Address</label>
                                        <textarea name="address" class="form-control"><?php echo htmlspecialchars($user['address']); ?></textarea>
                                    </div>
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fas fa-save"></i> Update Profile
                                    </button>
                                </form>
                            </div>
                        </div>

                        <!-- Change Password -->
                        <div class="card">
                            <div class="card-header">
                                <h3><i class="fas fa-lock"></i> Change Password</h3>
                            </div>
                            <div class="card-body">
                                <form method="POST">
                                    <input type="hidden" name="action" value="change_password">
                                    <div class="form-group">
                                        <label>Current Password *</label>
                                        <input type="password" name="current_password" class="form-control" required>
                                    </div>
                                    <div class="form-row">
                                        <div class="form-group">
                                            <label>New Password *</label>
                                            <input type="password" name="new_password" class="form-control" required minlength="6">
                                        </div>
                                        <div class="form-group">
                                            <label>Confirm New Password *</label>
                                            <input type="password" name="confirm_password" class="form-control" required minlength="6">
                                        </div>
                                    </div>
                                    <button type="submit" class="btn btn-warning">
                                        <i class="fas fa-key"></i> Change Password
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>
    <script src="js/app.js"></script>
</body>
</html>
