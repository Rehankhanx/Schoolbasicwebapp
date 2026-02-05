<?php
/**
 * Password Reset Script
 * Run this file ONCE to fix admin password
 * DELETE THIS FILE AFTER USE!
 * 
 * URL: http://localhost/schoolapp/reset_password.php
 */

// Database connection
$host = 'localhost';
$user = 'root';
$pass = '';
$db = 'school_db';

echo "<!DOCTYPE html>
<html lang='en'>
<head>
    <meta charset='UTF-8'>
    <meta name='viewport' content='width=device-width, initial-scale=1.0'>
    <title>Password Reset - School Management System</title>
    <link rel='stylesheet' href='https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css'>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { 
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; 
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            padding: 40px 20px;
        }
        .container {
            max-width: 900px;
            margin: 0 auto;
            background: white;
            border-radius: 20px;
            box-shadow: 0 25px 50px rgba(0,0,0,0.2);
            overflow: hidden;
        }
        .header {
            background: linear-gradient(135deg, #4f46e5 0%, #7c3aed 100%);
            color: white;
            padding: 30px;
            text-align: center;
        }
        .header h1 {
            font-size: 28px;
            margin-bottom: 10px;
        }
        .header p {
            opacity: 0.9;
        }
        .content {
            padding: 40px;
        }
        .success { 
            background: linear-gradient(135deg, #d4edda 0%, #c3e6cb 100%);
            color: #155724; 
            padding: 20px; 
            border-radius: 12px; 
            margin: 15px 0;
            border-left: 5px solid #28a745;
            display: flex;
            align-items: center;
            gap: 15px;
        }
        .success i { font-size: 24px; }
        .error { 
            background: linear-gradient(135deg, #f8d7da 0%, #f5c6cb 100%);
            color: #721c24; 
            padding: 20px; 
            border-radius: 12px; 
            margin: 15px 0;
            border-left: 5px solid #dc3545;
            display: flex;
            align-items: center;
            gap: 15px;
        }
        .error i { font-size: 24px; }
        .info { 
            background: linear-gradient(135deg, #cce5ff 0%, #b8daff 100%);
            color: #004085; 
            padding: 20px; 
            border-radius: 12px; 
            margin: 15px 0;
            border-left: 5px solid #007bff;
        }
        .warning { 
            background: linear-gradient(135deg, #fff3cd 0%, #ffeeba 100%);
            color: #856404; 
            padding: 20px; 
            border-radius: 12px; 
            margin: 15px 0;
            border-left: 5px solid #ffc107;
            display: flex;
            align-items: center;
            gap: 15px;
        }
        .warning i { font-size: 24px; }
        table { 
            width: 100%; 
            border-collapse: collapse; 
            margin: 20px 0;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }
        th, td { 
            padding: 15px 20px; 
            text-align: left; 
        }
        th { 
            background: linear-gradient(135deg, #4f46e5 0%, #7c3aed 100%);
            color: white;
            font-weight: 600;
        }
        tr:nth-child(even) { background: #f8f9fa; }
        tr:hover { background: #e9ecef; }
        .btn { 
            display: inline-flex;
            align-items: center;
            gap: 10px;
            padding: 15px 30px; 
            background: linear-gradient(135deg, #4f46e5 0%, #7c3aed 100%);
            color: white; 
            text-decoration: none; 
            border-radius: 10px; 
            margin: 10px 10px 10px 0;
            font-weight: 600;
            font-size: 16px;
            transition: all 0.3s ease;
            border: none;
            cursor: pointer;
        }
        .btn:hover { 
            transform: translateY(-2px);
            box-shadow: 0 10px 20px rgba(79, 70, 229, 0.3);
        }
        .btn-success {
            background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
        }
        .btn-success:hover {
            box-shadow: 0 10px 20px rgba(40, 167, 69, 0.3);
        }
        .btn-danger {
            background: linear-gradient(135deg, #dc3545 0%, #c82333 100%);
        }
        code { 
            background: #2d3748;
            color: #68d391;
            padding: 4px 10px; 
            border-radius: 6px;
            font-family: 'Courier New', monospace;
            font-size: 14px;
        }
        .credentials-box {
            background: linear-gradient(135deg, #1a202c 0%, #2d3748 100%);
            border-radius: 15px;
            padding: 30px;
            margin: 20px 0;
        }
        .credentials-box h3 {
            color: white;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .credentials-box table {
            margin: 0;
        }
        .credentials-box th {
            background: #4f46e5;
        }
        .credentials-box td {
            background: white;
        }
        .step-number {
            display: inline-flex;
            width: 30px;
            height: 30px;
            background: #4f46e5;
            color: white;
            border-radius: 50%;
            align-items: center;
            justify-content: center;
            font-weight: bold;
            margin-right: 10px;
        }
        .steps {
            margin: 20px 0;
        }
        .steps li {
            padding: 10px 0;
            border-bottom: 1px solid #e2e8f0;
            list-style: none;
        }
        .icon-box {
            width: 60px;
            height: 60px;
            background: linear-gradient(135deg, #4f46e5 0%, #7c3aed 100%);
            border-radius: 15px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 24px;
            margin-bottom: 15px;
        }
        .card-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin: 30px 0;
        }
        .card {
            background: #f8f9fa;
            border-radius: 15px;
            padding: 25px;
            text-align: center;
            transition: all 0.3s ease;
        }
        .card:hover {
            transform: translateY(-5px);
            box-shadow: 0 15px 30px rgba(0,0,0,0.1);
        }
        .card h4 {
            margin: 10px 0;
            color: #1a202c;
        }
        .card p {
            color: #718096;
            font-size: 14px;
        }
    </style>
</head>
<body>
    <div class='container'>";

echo "<div class='header'>
        <h1><i class='fas fa-graduation-cap'></i> School Management System</h1>
        <p>Password Reset & Account Recovery Tool</p>
      </div>";

echo "<div class='content'>";

try {
    // Try to connect to database
    $conn = new mysqli($host, $user, $pass, $db);
    
    if ($conn->connect_error) {
        throw new Exception("Database connection failed: " . $conn->connect_error);
    }
    
    echo "<div class='success'>
            <i class='fas fa-check-circle'></i>
            <div>
                <strong>Database Connected Successfully!</strong><br>
                Connected to database: <code>$db</code>
            </div>
          </div>";
    
    // Check if reset is requested
    if (isset($_GET['action']) && $_GET['action'] === 'reset') {
        
        // Generate proper password hashes
        $admin_password = 'admin123';
        $teacher_password = 'teacher123';
        $student_password = 'student123';
        
        $admin_hash = password_hash($admin_password, PASSWORD_DEFAULT);
        $teacher_hash = password_hash($teacher_password, PASSWORD_DEFAULT);
        $student_hash = password_hash($student_password, PASSWORD_DEFAULT);
        
        $created_users = [];
        $updated_users = [];
        
        // Check if admin exists
        $check = $conn->query("SELECT id FROM users WHERE username = 'admin'");
        if ($check && $check->num_rows > 0) {
            // Update admin password
            $stmt = $conn->prepare("UPDATE users SET password = ?, status = 'active' WHERE username = 'admin'");
            $stmt->bind_param("s", $admin_hash);
            $stmt->execute();
            $updated_users[] = 'admin';
        } else {
            // Create admin user
            $stmt = $conn->prepare("INSERT INTO users (username, email, password, role, full_name, status) VALUES ('admin', 'admin@school.com', ?, 'admin', 'System Administrator', 'active')");
            $stmt->bind_param("s", $admin_hash);
            $stmt->execute();
            $created_users[] = 'admin';
        }
        
        // Check if teacher exists
        $check = $conn->query("SELECT id FROM users WHERE username = 'teacher'");
        if ($check && $check->num_rows > 0) {
            $stmt = $conn->prepare("UPDATE users SET password = ?, status = 'active' WHERE username = 'teacher'");
            $stmt->bind_param("s", $teacher_hash);
            $stmt->execute();
            $updated_users[] = 'teacher';
        } else {
            $stmt = $conn->prepare("INSERT INTO users (username, email, password, role, full_name, status) VALUES ('teacher', 'teacher@school.com', ?, 'teacher', 'Demo Teacher', 'active')");
            $stmt->bind_param("s", $teacher_hash);
            $stmt->execute();
            $created_users[] = 'teacher';
            
            // Also create teacher record
            $teacher_user_id = $conn->insert_id;
            $emp_id = 'EMP' . date('Y') . '0001';
            $stmt = $conn->prepare("INSERT INTO teachers (user_id, employee_id, qualification, specialization, joining_date, salary) VALUES (?, ?, 'M.Sc., B.Ed.', 'Mathematics', CURDATE(), 45000) ON DUPLICATE KEY UPDATE employee_id = employee_id");
            $stmt->bind_param("is", $teacher_user_id, $emp_id);
            $stmt->execute();
        }
        
        // Check if student exists
        $check = $conn->query("SELECT id FROM users WHERE username = 'student'");
        if ($check && $check->num_rows > 0) {
            $stmt = $conn->prepare("UPDATE users SET password = ?, status = 'active' WHERE username = 'student'");
            $stmt->bind_param("s", $student_hash);
            $stmt->execute();
            $updated_users[] = 'student';
        } else {
            $stmt = $conn->prepare("INSERT INTO users (username, email, password, role, full_name, status) VALUES ('student', 'student@school.com', ?, 'student', 'Demo Student', 'active')");
            $stmt->bind_param("s", $student_hash);
            $stmt->execute();
            $created_users[] = 'student';
            
            // Also create student record
            $student_user_id = $conn->insert_id;
            $roll = date('Y') . '01001';
            $adm = 'ADM' . date('Y') . '0001';
            $stmt = $conn->prepare("INSERT INTO students (user_id, roll_number, admission_number, class_id, father_name, mother_name, date_of_birth, gender) VALUES (?, ?, ?, 1, 'Demo Father', 'Demo Mother', '2010-01-01', 'male') ON DUPLICATE KEY UPDATE roll_number = roll_number");
            $stmt->bind_param("iss", $student_user_id, $roll, $adm);
            $stmt->execute();
        }
        
        // Show results
        if (!empty($created_users)) {
            echo "<div class='success'>
                    <i class='fas fa-user-plus'></i>
                    <div>
                        <strong>Users Created:</strong><br>
                        " . implode(', ', $created_users) . "
                    </div>
                  </div>";
        }
        
        if (!empty($updated_users)) {
            echo "<div class='success'>
                    <i class='fas fa-user-check'></i>
                    <div>
                        <strong>Passwords Updated:</strong><br>
                        " . implode(', ', $updated_users) . "
                    </div>
                  </div>";
        }
        
        echo "<div class='credentials-box'>
                <h3><i class='fas fa-key'></i> Your Login Credentials</h3>
                <table>
                    <tr>
                        <th><i class='fas fa-user'></i> Role</th>
                        <th><i class='fas fa-at'></i> Username</th>
                        <th><i class='fas fa-lock'></i> Password</th>
                        <th><i class='fas fa-info-circle'></i> Access Level</th>
                    </tr>
                    <tr>
                        <td><span style='color:#dc3545;font-weight:bold;'>Admin</span></td>
                        <td><code>admin</code></td>
                        <td><code>admin123</code></td>
                        <td>Full System Access</td>
                    </tr>
                    <tr>
                        <td><span style='color:#28a745;font-weight:bold;'>Teacher</span></td>
                        <td><code>teacher</code></td>
                        <td><code>teacher123</code></td>
                        <td>Class Management</td>
                    </tr>
                    <tr>
                        <td><span style='color:#007bff;font-weight:bold;'>Student</span></td>
                        <td><code>student</code></td>
                        <td><code>student123</code></td>
                        <td>View Own Data</td>
                    </tr>
                </table>
              </div>";
        
        echo "<div class='warning'>
                <i class='fas fa-exclamation-triangle'></i>
                <div>
                    <strong>Security Warning!</strong><br>
                    Please DELETE this file (<code>reset_password.php</code>) after successful login for security reasons.
                </div>
              </div>";
        
        echo "<div style='margin-top: 30px;'>
                <a href='login.php' class='btn btn-success'>
                    <i class='fas fa-sign-in-alt'></i> Go to Login Page
                </a>
                <a href='reset_password.php' class='btn'>
                    <i class='fas fa-redo'></i> Reset Again
                </a>
              </div>";
        
    } else {
        // Show current users and reset option
        echo "<h2 style='margin-bottom: 20px;'><i class='fas fa-users'></i> Current Users in Database</h2>";
        
        $result = $conn->query("SELECT id, username, email, role, full_name, status, last_login FROM users ORDER BY id");
        
        if ($result && $result->num_rows > 0) {
            echo "<table>
                    <tr>
                        <th>ID</th>
                        <th>Username</th>
                        <th>Email</th>
                        <th>Role</th>
                        <th>Full Name</th>
                        <th>Status</th>
                    </tr>";
            while ($row = $result->fetch_assoc()) {
                $status_color = $row['status'] === 'active' ? '#28a745' : '#dc3545';
                echo "<tr>
                        <td>{$row['id']}</td>
                        <td><code>{$row['username']}</code></td>
                        <td>{$row['email']}</td>
                        <td><span style='text-transform:capitalize;'>{$row['role']}</span></td>
                        <td>{$row['full_name']}</td>
                        <td><span style='color:{$status_color};font-weight:bold;'>{$row['status']}</span></td>
                      </tr>";
            }
            echo "</table>";
        } else {
            echo "<div class='warning'>
                    <i class='fas fa-exclamation-circle'></i>
                    <div>
                        <strong>No Users Found!</strong><br>
                        The users table is empty. Click the reset button below to create default accounts.
                    </div>
                  </div>";
        }
        
        echo "<div class='card-grid'>
                <div class='card'>
                    <div class='icon-box' style='margin: 0 auto;'><i class='fas fa-sync-alt'></i></div>
                    <h4>Reset All Passwords</h4>
                    <p>This will reset admin, teacher, and student passwords to defaults.</p>
                </div>
                <div class='card'>
                    <div class='icon-box' style='margin: 0 auto; background: linear-gradient(135deg, #28a745 0%, #20c997 100%);'><i class='fas fa-user-plus'></i></div>
                    <h4>Create Missing Users</h4>
                    <p>Creates any missing default user accounts automatically.</p>
                </div>
                <div class='card'>
                    <div class='icon-box' style='margin: 0 auto; background: linear-gradient(135deg, #ffc107 0%, #fd7e14 100%);'><i class='fas fa-shield-alt'></i></div>
                    <h4>Secure Hashing</h4>
                    <p>Uses PHP password_hash() for secure password storage.</p>
                </div>
              </div>";
        
        echo "<div style='margin-top: 30px; padding: 30px; background: #f8f9fa; border-radius: 15px; text-align: center;'>
                <h3 style='margin-bottom: 20px;'><i class='fas fa-mouse-pointer'></i> Ready to Reset?</h3>
                <p style='color: #666; margin-bottom: 20px;'>Click the button below to reset/create all user accounts with default passwords.</p>
                <a href='?action=reset' class='btn' style='font-size: 18px; padding: 20px 40px;'>
                    <i class='fas fa-magic'></i> Reset Passwords Now
                </a>
              </div>";
        
        echo "<div class='info' style='margin-top: 30px;'>
                <h4><i class='fas fa-info-circle'></i> Already have credentials?</h4>
                <p style='margin-top: 10px;'>If you know your login details, you can go directly to the login page:</p>
                <a href='login.php' class='btn btn-success' style='margin-top: 15px;'>
                    <i class='fas fa-sign-in-alt'></i> Go to Login
                </a>
              </div>";
    }
    
    $conn->close();
    
} catch (Exception $e) {
    echo "<div class='error'>
            <i class='fas fa-times-circle'></i>
            <div>
                <strong>Connection Error!</strong><br>
                " . $e->getMessage() . "
            </div>
          </div>";
    
    echo "<div class='info'>
            <h3><i class='fas fa-tools'></i> Troubleshooting Steps</h3>
            <ol class='steps'>
                <li><span class='step-number'>1</span> <strong>Start XAMPP:</strong> Open XAMPP Control Panel and make sure both Apache and MySQL are running (green)</li>
                <li><span class='step-number'>2</span> <strong>Create Database:</strong> Go to <a href='http://localhost/phpmyadmin' target='_blank'>phpMyAdmin</a> and create a database named <code>school_db</code></li>
                <li><span class='step-number'>3</span> <strong>Import Tables:</strong> Click on <code>school_db</code>, then Import, then select <code>database.sql</code> file</li>
                <li><span class='step-number'>4</span> <strong>Retry:</strong> Refresh this page after completing above steps</li>
            </ol>
          </div>";
    
    echo "<div style='margin-top: 20px;'>
            <a href='http://localhost/phpmyadmin' target='_blank' class='btn'>
                <i class='fas fa-database'></i> Open phpMyAdmin
            </a>
            <a href='reset_password.php' class='btn btn-success'>
                <i class='fas fa-redo'></i> Retry Connection
            </a>
          </div>";
}

echo "</div>"; // End content

echo "<div style='background: #f8f9fa; padding: 20px; text-align: center; border-top: 1px solid #e2e8f0;'>
        <p style='color: #666; margin: 0;'>
            <i class='fas fa-graduation-cap'></i> School Management System v2.0.0 | 
            <i class='fas fa-clock'></i> " . date('F j, Y - g:i A') . "
        </p>
      </div>";

echo "</div>"; // End container
echo "</body></html>";
?>
