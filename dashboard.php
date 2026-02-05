<?php
require_once 'config/db.php';

if (!isLoggedIn()) {
    redirect('login.php');
}

$db = Database::getInstance()->getConnection();
$role = $_SESSION['role'];
$school_name = getSchoolSetting('school_name', 'EduManage School');
$page_title = 'Dashboard';

// Get statistics based on role
$stats = [];

if ($role === 'admin') {
    // Total Students
    $stmt = $db->query("SELECT COUNT(*) as count FROM students");
    $stats['students'] = $stmt->fetch()['count'];
    
    // Total Teachers
    $stmt = $db->query("SELECT COUNT(*) as count FROM teachers");
    $stats['teachers'] = $stmt->fetch()['count'];
    
    // Total Classes
    $stmt = $db->query("SELECT COUNT(*) as count FROM classes WHERE status = 'active'");
    $stats['classes'] = $stmt->fetch()['count'];
    
    // Pending Fees
    $stmt = $db->query("SELECT COUNT(*) as count, COALESCE(SUM(net_amount), 0) as total FROM fees WHERE status IN ('unpaid', 'partial', 'overdue')");
    $pending = $stmt->fetch();
    $stats['pending_fees_count'] = $pending['count'];
    $stats['pending_fees_amount'] = $pending['total'];
    
    // Today's Collection
    $stmt = $db->query("SELECT COALESCE(SUM(amount_paid), 0) as total FROM payments WHERE DATE(payment_date) = CURDATE()");
    $stats['today_collection'] = $stmt->fetch()['total'];
    
    // This Month Collection
    $stmt = $db->query("SELECT COALESCE(SUM(amount_paid), 0) as total FROM payments WHERE MONTH(payment_date) = MONTH(CURDATE()) AND YEAR(payment_date) = YEAR(CURDATE())");
    $stats['month_collection'] = $stmt->fetch()['total'];
    
    // Today's Attendance
    $stmt = $db->query("SELECT 
        COUNT(CASE WHEN status = 'present' THEN 1 END) as present,
        COUNT(CASE WHEN status = 'absent' THEN 1 END) as absent,
        COUNT(*) as total
        FROM attendance WHERE date = CURDATE()");
    $stats['today_attendance'] = $stmt->fetch();
    
    // Recent Admissions
    $stmt = $db->query("SELECT s.*, u.full_name, c.class_name, c.section FROM students s 
                        JOIN users u ON s.user_id = u.id 
                        LEFT JOIN classes c ON s.class_id = c.id 
                        ORDER BY s.created_at DESC LIMIT 5");
    $recent_admissions = $stmt->fetchAll();
    
    // Recent Payments
    $stmt = $db->query("SELECT p.*, u.full_name, s.roll_number FROM payments p 
                        JOIN students s ON p.student_id = s.id 
                        JOIN users u ON s.user_id = u.id 
                        ORDER BY p.created_at DESC LIMIT 5");
    $recent_payments = $stmt->fetchAll();
    
    // Upcoming Events
    $stmt = $db->query("SELECT * FROM events WHERE start_date >= CURDATE() ORDER BY start_date ASC LIMIT 5");
    $upcoming_events = $stmt->fetchAll();
    
    // Fee Due This Week
    $stmt = $db->query("SELECT COUNT(*) as count FROM fees WHERE status = 'unpaid' AND due_date BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 7 DAY)");
    $stats['fee_due_week'] = $stmt->fetch()['count'];

} elseif ($role === 'teacher') {
    // Get teacher info
    $stmt = $db->prepare("SELECT t.*, c.id as class_id, c.class_name, c.section FROM teachers t 
                          LEFT JOIN classes c ON c.teacher_id = t.user_id 
                          WHERE t.user_id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $teacher_info = $stmt->fetch();
    
    // Students in teacher's class
    if ($teacher_info && $teacher_info['class_id']) {
        $stmt = $db->prepare("SELECT COUNT(*) as count FROM students WHERE class_id = ?");
        $stmt->execute([$teacher_info['class_id']]);
        $stats['my_students'] = $stmt->fetch()['count'];
        
        // Today's Attendance
        $stmt = $db->prepare("SELECT 
            COUNT(CASE WHEN status = 'present' THEN 1 END) as present,
            COUNT(CASE WHEN status = 'absent' THEN 1 END) as absent,
            COUNT(*) as total
            FROM attendance WHERE class_id = ? AND date = CURDATE()");
        $stmt->execute([$teacher_info['class_id']]);
        $stats['today_attendance'] = $stmt->fetch();
    } else {
        $stats['my_students'] = 0;
        $stats['today_attendance'] = ['present' => 0, 'absent' => 0, 'total' => 0];
    }
    
    // Active Homework
    $stmt = $db->prepare("SELECT COUNT(*) as count FROM homework WHERE teacher_id = ? AND due_date >= CURDATE() AND status = 'active'");
    $stmt->execute([$_SESSION['user_id']]);
    $stats['active_homework'] = $stmt->fetch()['count'];
    
    // Pending Submissions
    $stmt = $db->prepare("SELECT COUNT(*) as count FROM homework_submissions hs 
                          JOIN homework h ON hs.homework_id = h.id 
                          WHERE h.teacher_id = ? AND hs.status = 'submitted'");
    $stmt->execute([$_SESSION['user_id']]);
    $stats['pending_submissions'] = $stmt->fetch()['count'];
    
    // My Recent Homework
    $stmt = $db->prepare("SELECT h.*, c.class_name, s.subject_name FROM homework h 
                          JOIN classes c ON h.class_id = c.id 
                          JOIN subjects s ON h.subject_id = s.id 
                          WHERE h.teacher_id = ? ORDER BY h.created_at DESC LIMIT 5");
    $stmt->execute([$_SESSION['user_id']]);
    $recent_homework = $stmt->fetchAll();

} else { // student
    // Get student info
    $stmt = $db->prepare("SELECT s.*, c.class_name, c.section FROM students s 
                          LEFT JOIN classes c ON s.class_id = c.id 
                          WHERE s.user_id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $student_info = $stmt->fetch();
    
    if ($student_info) {
        // Attendance percentage
        $stmt = $db->prepare("SELECT 
                              COUNT(*) as total,
                              SUM(CASE WHEN status = 'present' THEN 1 ELSE 0 END) as present
                              FROM attendance WHERE student_id = ?");
        $stmt->execute([$student_info['id']]);
        $att = $stmt->fetch();
        $stats['attendance_percentage'] = $att['total'] > 0 ? round(($att['present'] / $att['total']) * 100, 1) : 0;
        $stats['attendance_days'] = $att;
        
        // Pending Fees
        $stmt = $db->prepare("SELECT COALESCE(SUM(net_amount), 0) as total, COUNT(*) as count FROM fees WHERE student_id = ? AND status IN ('unpaid', 'partial')");
        $stmt->execute([$student_info['id']]);
        $fees = $stmt->fetch();
        $stats['pending_fees'] = $fees['total'];
        $stats['pending_fees_count'] = $fees['count'];
        
        // Pending Homework
        $stmt = $db->prepare("SELECT COUNT(*) as count FROM homework h 
                              LEFT JOIN homework_submissions hs ON h.id = hs.homework_id AND hs.student_id = ?
                              WHERE h.class_id = ? AND h.due_date >= CURDATE() AND h.status = 'active' AND hs.id IS NULL");
        $stmt->execute([$student_info['id'], $student_info['class_id']]);
        $stats['pending_homework'] = $stmt->fetch()['count'];
        
        // Recent Results
        $stmt = $db->prepare("SELECT r.*, e.exam_name, s.subject_name FROM results r 
                              JOIN exams e ON r.exam_id = e.id 
                              JOIN subjects s ON r.subject_id = s.id 
                              WHERE r.student_id = ? ORDER BY r.created_at DESC LIMIT 5");
        $stmt->execute([$student_info['id']]);
        $recent_results = $stmt->fetchAll();
        
        // Today's Timetable
        $today = strtolower(date('l'));
        $stmt = $db->prepare("SELECT t.*, s.subject_name, u.full_name as teacher_name FROM timetable t 
                              JOIN subjects s ON t.subject_id = s.id 
                              LEFT JOIN users u ON t.teacher_id = u.id 
                              WHERE t.class_id = ? AND t.day_of_week = ? 
                              ORDER BY t.start_time");
        $stmt->execute([$student_info['class_id'], $today]);
        $today_timetable = $stmt->fetchAll();
    }
}

// Get announcements
$stmt = $db->query("SELECT * FROM announcements WHERE status = 'active' AND (start_date IS NULL OR start_date <= CURDATE()) AND (end_date IS NULL OR end_date >= CURDATE()) ORDER BY priority DESC, created_at DESC LIMIT 5");
$announcements = $stmt->fetchAll();

// Get notifications
$stmt = $db->prepare("SELECT * FROM notifications WHERE user_id = ? AND is_read = 0 ORDER BY created_at DESC LIMIT 5");
$stmt->execute([$_SESSION['user_id']]);
$notifications = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - <?php echo htmlspecialchars($school_name); ?></title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <div class="dashboard-wrapper">
        <?php include 'includes/sidebar.php'; ?>

        <main class="main-content">
            <?php include 'includes/header.php'; ?>

            <div class="page-content dashboard-page">
                <!-- Welcome Message -->
                <div class="card mb-4">
                    <div class="card-body" style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 15px;">
                        <div>
                            <h2 style="font-size: 26px; margin-bottom: 5px;">
                                Welcome back, <?php echo htmlspecialchars($_SESSION['full_name']); ?>! 👋
                            </h2>
                            <p class="text-muted">
                                <?php echo date('l, F j, Y'); ?> | 
                                <span id="currentTime"></span>
                                <?php if ($role === 'student' && isset($student_info)): ?>
                                    | Class: <?php echo htmlspecialchars($student_info['class_name'] . ' - ' . $student_info['section']); ?>
                                    | Roll: <?php echo htmlspecialchars($student_info['roll_number']); ?>
                                <?php elseif ($role === 'teacher' && isset($teacher_info) && $teacher_info['class_id']): ?>
                                    | Class Teacher: <?php echo htmlspecialchars($teacher_info['class_name'] . ' - ' . $teacher_info['section']); ?>
                                <?php endif; ?>
                            </p>
                        </div>
                        <div>
                            <?php if ($role === 'admin'): ?>
                                <a href="admin/students.php?action=add" class="btn btn-primary">
                                    <i class="fas fa-user-plus"></i> New Admission
                                </a>
                            <?php elseif ($role === 'teacher'): ?>
                                <a href="teacher/attendance.php" class="btn btn-primary">
                                    <i class="fas fa-clipboard-check"></i> Mark Attendance
                                </a>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <!-- Stats Cards -->
                <div class="stats-grid">
                    <?php if ($role === 'admin'): ?>
                        <div class="stat-card">
                            <div class="stat-icon blue"><i class="fas fa-user-graduate"></i></div>
                            <div class="stat-info">
                                <h3><?php echo number_format($stats['students']); ?></h3>
                                <p>Total Students</p>
                            </div>
                        </div>
                        <div class="stat-card">
                            <div class="stat-icon green"><i class="fas fa-chalkboard-teacher"></i></div>
                            <div class="stat-info">
                                <h3><?php echo number_format($stats['teachers']); ?></h3>
                                <p>Total Teachers</p>
                            </div>
                        </div>
                        <div class="stat-card">
                            <div class="stat-icon yellow"><i class="fas fa-school"></i></div>
                            <div class="stat-info">
                                <h3><?php echo number_format($stats['classes']); ?></h3>
                                <p>Active Classes</p>
                            </div>
                        </div>
                        <div class="stat-card">
                            <div class="stat-icon red"><i class="fas fa-exclamation-circle"></i></div>
                            <div class="stat-info">
                                <h3><?php echo number_format($stats['pending_fees_count']); ?></h3>
                                <p>Pending Fees</p>
                            </div>
                        </div>
                        <div class="stat-card">
                            <div class="stat-icon cyan"><i class="fas fa-rupee-sign"></i></div>
                            <div class="stat-info">
                                <h3><?php echo formatCurrency($stats['today_collection']); ?></h3>
                                <p>Today's Collection</p>
                            </div>
                        </div>

                    <?php elseif ($role === 'teacher'): ?>
                        <div class="stat-card">
                            <div class="stat-icon blue"><i class="fas fa-users"></i></div>
                            <div class="stat-info">
                                <h3><?php echo $stats['my_students']; ?></h3>
                                <p>My Students</p>
                            </div>
                        </div>
                        <div class="stat-card">
                            <div class="stat-icon green"><i class="fas fa-check-circle"></i></div>
                            <div class="stat-info">
                                <h3><?php echo $stats['today_attendance']['present'] ?? 0; ?> / <?php echo $stats['today_attendance']['total'] ?? 0; ?></h3>
                                <p>Today's Attendance</p>
                            </div>
                        </div>
                        <div class="stat-card">
                            <div class="stat-icon yellow"><i class="fas fa-tasks"></i></div>
                            <div class="stat-info">
                                <h3><?php echo $stats['active_homework']; ?></h3>
                                <p>Active Homework</p>
                            </div>
                        </div>
                        <div class="stat-card">
                            <div class="stat-icon red"><i class="fas fa-clock"></i></div>
                            <div class="stat-info">
                                <h3><?php echo $stats['pending_submissions']; ?></h3>
                                <p>Pending Reviews</p>
                            </div>
                        </div>

                    <?php else: // student ?>
                        <div class="stat-card">
                            <div class="stat-icon <?php echo ($stats['attendance_percentage'] ?? 0) >= 75 ? 'green' : 'red'; ?>">
                                <i class="fas fa-percentage"></i>
                            </div>
                            <div class="stat-info">
                                <h3><?php echo $stats['attendance_percentage'] ?? 0; ?>%</h3>
                                <p>Attendance</p>
                            </div>
                        </div>
                        <div class="stat-card">
                            <div class="stat-icon <?php echo ($stats['pending_fees'] ?? 0) > 0 ? 'red' : 'green'; ?>">
                                <i class="fas fa-rupee-sign"></i>
                            </div>
                            <div class="stat-info">
                                <h3><?php echo formatCurrency($stats['pending_fees'] ?? 0); ?></h3>
                                <p>Pending Fees</p>
                            </div>
                        </div>
                        <div class="stat-card">
                            <div class="stat-icon yellow"><i class="fas fa-tasks"></i></div>
                            <div class="stat-info">
                                <h3><?php echo $stats['pending_homework'] ?? 0; ?></h3>
                                <p>Pending Homework</p>
                            </div>
                        </div>
                        <div class="stat-card">
                            <div class="stat-icon blue"><i class="fas fa-calendar-check"></i></div>
                            <div class="stat-info">
                                <h3><?php echo ($stats['attendance_days']['present'] ?? 0); ?> / <?php echo ($stats['attendance_days']['total'] ?? 0); ?></h3>
                                <p>Days Present</p>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>

                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(400px, 1fr)); gap: 20px;">
                    <?php if ($role === 'admin'): ?>
                        <!-- Recent Admissions -->
                        <div class="card">
                            <div class="card-header">
                                <h3><i class="fas fa-user-plus"></i> Recent Admissions</h3>
                                <a href="admin/students.php" class="btn btn-sm btn-outline">View All</a>
                            </div>
                            <div class="card-body">
                                <?php if (empty($recent_admissions)): ?>
                                    <div class="empty-state" style="padding: 30px;">
                                        <i class="fas fa-user-graduate" style="font-size: 40px;"></i>
                                        <p>No recent admissions</p>
                                    </div>
                                <?php else: ?>
                                    <table class="table">
                                        <thead>
                                            <tr><th>Roll No</th><th>Name</th><th>Class</th><th>Date</th></tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($recent_admissions as $admission): ?>
                                                <tr>
                                                    <td><strong><?php echo htmlspecialchars($admission['roll_number']); ?></strong></td>
                                                    <td><?php echo htmlspecialchars($admission['full_name']); ?></td>
                                                    <td><?php echo htmlspecialchars(($admission['class_name'] ?? '') . ' ' . ($admission['section'] ?? '')); ?></td>
                                                    <td><?php echo formatDate($admission['created_at'], 'd M'); ?></td>
                                                </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                <?php endif; ?>
                            </div>
                        </div>

                        <!-- Recent Payments -->
                        <div class="card">
                            <div class="card-header">
                                <h3><i class="fas fa-money-bill-wave"></i> Recent Payments</h3>
                                <a href="admin/payments.php" class="btn btn-sm btn-outline">View All</a>
                            </div>
                            <div class="card-body">
                                <?php if (empty($recent_payments)): ?>
                                    <div class="empty-state" style="padding: 30px;">
                                        <i class="fas fa-receipt" style="font-size: 40px;"></i>
                                        <p>No recent payments</p>
                                    </div>
                                <?php else: ?>
                                    <table class="table">
                                        <thead>
                                            <tr><th>Receipt</th><th>Student</th><th>Amount</th></tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($recent_payments as $payment): ?>
                                                <tr>
                                                    <td><?php echo htmlspecialchars($payment['receipt_number']); ?></td>
                                                    <td><?php echo htmlspecialchars($payment['full_name']); ?></td>
                                                    <td class="text-success"><strong><?php echo formatCurrency($payment['amount_paid']); ?></strong></td>
                                                </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                <?php endif; ?>
                            </div>
                        </div>

                    <?php elseif ($role === 'teacher'): ?>
                        <!-- My Recent Homework -->
                        <div class="card">
                            <div class="card-header">
                                <h3><i class="fas fa-tasks"></i> My Recent Homework</h3>
                                <a href="teacher/homework.php" class="btn btn-sm btn-outline">View All</a>
                            </div>
                            <div class="card-body">
                                <?php if (empty($recent_homework)): ?>
                                    <div class="empty-state" style="padding: 30px;">
                                        <i class="fas fa-tasks" style="font-size: 40px;"></i>
                                        <p>No homework assigned yet</p>
                                        <a href="teacher/homework.php?action=add" class="btn btn-primary btn-sm">Add Homework</a>
                                    </div>
                                <?php else: ?>
                                    <?php foreach ($recent_homework as $hw): ?>
                                        <div style="padding: 12px; border-bottom: 1px solid var(--gray-200);">
                                            <div style="display: flex; justify-content: space-between;">
                                                <strong><?php echo htmlspecialchars($hw['title']); ?></strong>
                                                <span class="badge badge-<?php echo strtotime($hw['due_date']) < time() ? 'danger' : 'info'; ?>">
                                                    Due: <?php echo formatDate($hw['due_date'], 'd M'); ?>
                                                </span>
                                            </div>
                                            <small class="text-muted"><?php echo htmlspecialchars($hw['class_name'] . ' - ' . $hw['subject_name']); ?></small>
                                        </div>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </div>
                        </div>

                    <?php else: // student ?>
                        <!-- Today's Timetable -->
                        <div class="card">
                            <div class="card-header">
                                <h3><i class="fas fa-calendar-day"></i> Today's Classes</h3>
                                <a href="student/timetable.php" class="btn btn-sm btn-outline">Full Timetable</a>
                            </div>
                            <div class="card-body">
                                <?php if (empty($today_timetable)): ?>
                                    <div class="empty-state" style="padding: 30px;">
                                        <i class="fas fa-calendar-check" style="font-size: 40px;"></i>
                                        <p>No classes scheduled for today</p>
                                    </div>
                                <?php else: ?>
                                    <?php foreach ($today_timetable as $period): ?>
                                        <div style="padding: 12px; border-bottom: 1px solid var(--gray-200); display: flex; justify-content: space-between; align-items: center;">
                                            <div>
                                                <strong><?php echo htmlspecialchars($period['subject_name']); ?></strong>
                                                <br><small class="text-muted"><?php echo htmlspecialchars($period['teacher_name'] ?? 'TBA'); ?></small>
                                            </div>
                                            <div class="text-right">
                                                <span class="badge badge-info"><?php echo date('h:i A', strtotime($period['start_time'])); ?></span>
                                                <br><small>Room: <?php echo htmlspecialchars($period['room_number'] ?? 'TBA'); ?></small>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </div>
                        </div>

                        <!-- Recent Results -->
                        <div class="card">
                            <div class="card-header">
                                <h3><i class="fas fa-chart-line"></i> Recent Results</h3>
                                <a href="student/results.php" class="btn btn-sm btn-outline">View All</a>
                            </div>
                            <div class="card-body">
                                <?php if (empty($recent_results)): ?>
                                    <div class="empty-state" style="padding: 30px;">
                                        <i class="fas fa-chart-bar" style="font-size: 40px;"></i>
                                        <p>No results available yet</p>
                                    </div>
                                <?php else: ?>
                                    <table class="table">
                                        <thead>
                                            <tr><th>Exam</th><th>Subject</th><th>Marks</th><th>Grade</th></tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($recent_results as $result): ?>
                                                <tr>
                                                    <td><?php echo htmlspecialchars($result['exam_name']); ?></td>
                                                    <td><?php echo htmlspecialchars($result['subject_name']); ?></td>
                                                    <td><?php echo $result['marks_obtained']; ?></td>
                                                    <td><span class="badge badge-<?php echo $result['grade'] == 'F' ? 'danger' : 'success'; ?>"><?php echo $result['grade']; ?></span></td>
                                                </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endif; ?>

                    <!-- Quick Actions -->
                    <div class="card">
                        <div class="card-header">
                            <h3><i class="fas fa-bolt"></i> Quick Actions</h3>
                        </div>
                        <div class="card-body">
                            <div style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 12px;">
                                <?php if ($role === 'admin'): ?>
                                    <a href="admin/students.php?action=add" class="btn btn-primary"><i class="fas fa-user-plus"></i> Add Student</a>
                                    <a href="admin/teachers.php?action=add" class="btn btn-success"><i class="fas fa-user-plus"></i> Add Teacher</a>
                                    <a href="admin/fees.php" class="btn btn-warning"><i class="fas fa-money-bill"></i> Collect Fee</a>
                                    <a href="admin/attendance.php" class="btn btn-info"><i class="fas fa-clipboard-check"></i> Attendance</a>
                                    <a href="admin/reports.php" class="btn btn-secondary"><i class="fas fa-chart-bar"></i> Reports</a>
                                    <a href="admin/settings.php" class="btn btn-outline"><i class="fas fa-cog"></i> Settings</a>
                                <?php elseif ($role === 'teacher'): ?>
                                    <a href="teacher/attendance.php" class="btn btn-primary"><i class="fas fa-clipboard-check"></i> Mark Attendance</a>
                                    <a href="teacher/homework.php?action=add" class="btn btn-success"><i class="fas fa-plus"></i> Add Homework</a>
                                    <a href="teacher/exams.php" class="btn btn-warning"><i class="fas fa-edit"></i> Enter Marks</a>
                                    <a href="teacher/students.php" class="btn btn-info"><i class="fas fa-users"></i> My Students</a>
                                <?php else: ?>
                                    <a href="student/attendance.php" class="btn btn-primary"><i class="fas fa-calendar-check"></i> Attendance</a>
                                    <a href="student/homework.php" class="btn btn-success"><i class="fas fa-tasks"></i> Homework</a>
                                    <a href="student/results.php" class="btn btn-warning"><i class="fas fa-chart-line"></i> Results</a>
                                    <a href="student/fees.php" class="btn btn-info"><i class="fas fa-file-invoice"></i> Fee Status</a>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>

                    <!-- Announcements -->
                    <div class="card">
                        <div class="card-header">
                            <h3><i class="fas fa-bullhorn"></i> Announcements</h3>
                        </div>
                        <div class="card-body">
                            <?php if (empty($announcements)): ?>
                                <div class="empty-state" style="padding: 30px;">
                                    <i class="fas fa-bullhorn" style="font-size: 40px;"></i>
                                    <p>No announcements</p>
                                </div>
                            <?php else: ?>
                                <?php foreach ($announcements as $ann): ?>
                                    <div style="padding: 12px; border-bottom: 1px solid var(--gray-200);">
                                        <div style="display: flex; gap: 10px; align-items: start;">
                                            <div style="width: 40px; height: 40px; border-radius: 50%; background: <?php 
                                                echo $ann['priority'] === 'urgent' ? 'var(--danger)' : 
                                                    ($ann['priority'] === 'high' ? 'var(--warning)' : 'var(--primary)'); 
                                            ?>; display: flex; align-items: center; justify-content: center; color: white; flex-shrink: 0;">
                                                <i class="fas fa-<?php echo $ann['priority'] === 'urgent' ? 'exclamation' : 'info'; ?>"></i>
                                            </div>
                                            <div>
                                                <strong><?php echo htmlspecialchars($ann['title']); ?></strong>
                                                <p style="font-size: 13px; color: var(--gray-600); margin: 5px 0 0;">
                                                    <?php echo htmlspecialchars(substr($ann['content'], 0, 100)) . (strlen($ann['content']) > 100 ? '...' : ''); ?>
                                                </p>
                                            </div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <script src="js/app.js"></script>
    <script>
        function updateTime() {
            const now = new Date();
            document.getElementById('currentTime').textContent = now.toLocaleTimeString('en-US', {
                hour: '2-digit',
                minute: '2-digit',
                second: '2-digit'
            });
        }
        updateTime();
        setInterval(updateTime, 1000);
    </script>
</body>
</html>
