-- School Management System Database
-- Complete Production Database Schema
-- Run this file in phpMyAdmin

CREATE DATABASE IF NOT EXISTS school_db;
USE school_db;

-- Settings table for school configuration
CREATE TABLE settings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    setting_key VARCHAR(100) UNIQUE NOT NULL,
    setting_value TEXT,
    setting_type ENUM('text', 'textarea', 'image', 'number', 'email', 'phone') DEFAULT 'text',
    setting_group VARCHAR(50) DEFAULT 'general',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Users table for authentication
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) UNIQUE NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    role ENUM('admin', 'teacher', 'student') NOT NULL,
    full_name VARCHAR(100) NOT NULL,
    phone VARCHAR(20),
    address TEXT,
    profile_image VARCHAR(255) DEFAULT 'default.png',
    status ENUM('active', 'inactive') DEFAULT 'active',
    last_login DATETIME,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Classes table
CREATE TABLE classes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    class_name VARCHAR(50) NOT NULL,
    section VARCHAR(10) DEFAULT 'A',
    academic_year VARCHAR(20) NOT NULL,
    teacher_id INT,
    capacity INT DEFAULT 40,
    room_number VARCHAR(20),
    description TEXT,
    status ENUM('active', 'inactive') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (teacher_id) REFERENCES users(id) ON DELETE SET NULL
);

-- Students table
CREATE TABLE students (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT UNIQUE,
    roll_number VARCHAR(20) UNIQUE NOT NULL,
    admission_number VARCHAR(20) UNIQUE NOT NULL,
    class_id INT,
    father_name VARCHAR(100),
    mother_name VARCHAR(100),
    guardian_name VARCHAR(100),
    guardian_relation VARCHAR(50),
    guardian_phone VARCHAR(20),
    guardian_email VARCHAR(100),
    date_of_birth DATE,
    gender ENUM('male', 'female', 'other'),
    blood_group VARCHAR(5),
    religion VARCHAR(50),
    caste VARCHAR(50),
    nationality VARCHAR(50) DEFAULT 'Indian',
    aadhar_number VARCHAR(20),
    admission_date DATE DEFAULT (CURRENT_DATE),
    previous_school VARCHAR(200),
    previous_class VARCHAR(50),
    transfer_certificate VARCHAR(255),
    birth_certificate VARCHAR(255),
    photo VARCHAR(255),
    medical_conditions TEXT,
    emergency_contact VARCHAR(20),
    transport_mode ENUM('bus', 'private', 'walk') DEFAULT 'private',
    bus_route VARCHAR(100),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (class_id) REFERENCES classes(id) ON DELETE SET NULL
);

-- Teachers table
CREATE TABLE teachers (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT UNIQUE,
    employee_id VARCHAR(20) UNIQUE NOT NULL,
    qualification VARCHAR(200),
    specialization VARCHAR(100),
    experience_years INT DEFAULT 0,
    joining_date DATE,
    contract_type ENUM('permanent', 'contract', 'temporary') DEFAULT 'permanent',
    salary DECIMAL(12,2),
    bank_account VARCHAR(50),
    bank_name VARCHAR(100),
    ifsc_code VARCHAR(20),
    pan_number VARCHAR(20),
    aadhar_number VARCHAR(20),
    emergency_contact VARCHAR(20),
    blood_group VARCHAR(5),
    date_of_birth DATE,
    gender ENUM('male', 'female', 'other'),
    marital_status ENUM('single', 'married', 'divorced', 'widowed'),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Subjects table
CREATE TABLE subjects (
    id INT AUTO_INCREMENT PRIMARY KEY,
    subject_name VARCHAR(100) NOT NULL,
    subject_code VARCHAR(20) UNIQUE NOT NULL,
    class_id INT,
    teacher_id INT,
    subject_type ENUM('theory', 'practical', 'both') DEFAULT 'theory',
    total_marks INT DEFAULT 100,
    passing_marks INT DEFAULT 33,
    credit_hours INT DEFAULT 4,
    description TEXT,
    status ENUM('active', 'inactive') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (class_id) REFERENCES classes(id) ON DELETE CASCADE,
    FOREIGN KEY (teacher_id) REFERENCES users(id) ON DELETE SET NULL
);

-- Attendance table
CREATE TABLE attendance (
    id INT AUTO_INCREMENT PRIMARY KEY,
    student_id INT NOT NULL,
    class_id INT NOT NULL,
    date DATE NOT NULL,
    status ENUM('present', 'absent', 'late', 'half_day', 'leave') NOT NULL,
    check_in_time TIME,
    check_out_time TIME,
    remarks TEXT,
    marked_by INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (student_id) REFERENCES students(id) ON DELETE CASCADE,
    FOREIGN KEY (class_id) REFERENCES classes(id) ON DELETE CASCADE,
    FOREIGN KEY (marked_by) REFERENCES users(id) ON DELETE SET NULL,
    UNIQUE KEY unique_attendance (student_id, date)
);

-- Teacher Attendance
CREATE TABLE teacher_attendance (
    id INT AUTO_INCREMENT PRIMARY KEY,
    teacher_id INT NOT NULL,
    date DATE NOT NULL,
    status ENUM('present', 'absent', 'late', 'half_day', 'leave') NOT NULL,
    check_in_time TIME,
    check_out_time TIME,
    remarks TEXT,
    marked_by INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (teacher_id) REFERENCES teachers(id) ON DELETE CASCADE,
    FOREIGN KEY (marked_by) REFERENCES users(id) ON DELETE SET NULL,
    UNIQUE KEY unique_teacher_attendance (teacher_id, date)
);

-- Exams table
CREATE TABLE exams (
    id INT AUTO_INCREMENT PRIMARY KEY,
    exam_name VARCHAR(100) NOT NULL,
    exam_type ENUM('unit_test', 'midterm', 'final', 'practical', 'quarterly', 'half_yearly', 'annual') NOT NULL,
    class_id INT,
    start_date DATE,
    end_date DATE,
    academic_year VARCHAR(20),
    total_marks INT DEFAULT 100,
    passing_percentage INT DEFAULT 33,
    description TEXT,
    status ENUM('upcoming', 'ongoing', 'completed', 'cancelled') DEFAULT 'upcoming',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (class_id) REFERENCES classes(id) ON DELETE CASCADE
);

-- Exam Schedule
CREATE TABLE exam_schedule (
    id INT AUTO_INCREMENT PRIMARY KEY,
    exam_id INT NOT NULL,
    subject_id INT NOT NULL,
    exam_date DATE NOT NULL,
    start_time TIME,
    end_time TIME,
    room_number VARCHAR(20),
    max_marks INT DEFAULT 100,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (exam_id) REFERENCES exams(id) ON DELETE CASCADE,
    FOREIGN KEY (subject_id) REFERENCES subjects(id) ON DELETE CASCADE
);

-- Results table
CREATE TABLE results (
    id INT AUTO_INCREMENT PRIMARY KEY,
    student_id INT NOT NULL,
    exam_id INT NOT NULL,
    subject_id INT NOT NULL,
    marks_obtained DECIMAL(5,2),
    practical_marks DECIMAL(5,2) DEFAULT 0,
    grade VARCHAR(5),
    grade_points DECIMAL(3,2),
    remarks TEXT,
    is_absent BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (student_id) REFERENCES students(id) ON DELETE CASCADE,
    FOREIGN KEY (exam_id) REFERENCES exams(id) ON DELETE CASCADE,
    FOREIGN KEY (subject_id) REFERENCES subjects(id) ON DELETE CASCADE,
    UNIQUE KEY unique_result (student_id, exam_id, subject_id)
);

-- Homework table
CREATE TABLE homework (
    id INT AUTO_INCREMENT PRIMARY KEY,
    class_id INT NOT NULL,
    subject_id INT NOT NULL,
    teacher_id INT NOT NULL,
    title VARCHAR(200) NOT NULL,
    description TEXT,
    instructions TEXT,
    due_date DATE NOT NULL,
    max_marks INT DEFAULT 10,
    attachment VARCHAR(255),
    attachment_type VARCHAR(50),
    status ENUM('active', 'expired', 'cancelled') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (class_id) REFERENCES classes(id) ON DELETE CASCADE,
    FOREIGN KEY (subject_id) REFERENCES subjects(id) ON DELETE CASCADE,
    FOREIGN KEY (teacher_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Homework submissions
CREATE TABLE homework_submissions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    homework_id INT NOT NULL,
    student_id INT NOT NULL,
    submission_text TEXT,
    attachment VARCHAR(255),
    submitted_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    marks_obtained DECIMAL(5,2),
    grade VARCHAR(10),
    feedback TEXT,
    status ENUM('submitted', 'late', 'graded', 'returned') DEFAULT 'submitted',
    graded_by INT,
    graded_at DATETIME,
    FOREIGN KEY (homework_id) REFERENCES homework(id) ON DELETE CASCADE,
    FOREIGN KEY (student_id) REFERENCES students(id) ON DELETE CASCADE,
    FOREIGN KEY (graded_by) REFERENCES users(id) ON DELETE SET NULL,
    UNIQUE KEY unique_submission (homework_id, student_id)
);

-- Fee Categories
CREATE TABLE fee_categories (
    id INT AUTO_INCREMENT PRIMARY KEY,
    category_name VARCHAR(100) NOT NULL,
    description TEXT,
    status ENUM('active', 'inactive') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Fee structure table
CREATE TABLE fee_structure (
    id INT AUTO_INCREMENT PRIMARY KEY,
    class_id INT,
    category_id INT,
    fee_type ENUM('tuition', 'admission', 'exam', 'transport', 'library', 'sports', 'laboratory', 'computer', 'uniform', 'books', 'other') NOT NULL,
    amount DECIMAL(12,2) NOT NULL,
    frequency ENUM('monthly', 'quarterly', 'half_yearly', 'yearly', 'one_time') DEFAULT 'monthly',
    academic_year VARCHAR(20),
    due_day INT DEFAULT 10,
    late_fee_per_day DECIMAL(10,2) DEFAULT 0,
    description TEXT,
    status ENUM('active', 'inactive') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (class_id) REFERENCES classes(id) ON DELETE CASCADE,
    FOREIGN KEY (category_id) REFERENCES fee_categories(id) ON DELETE SET NULL
);

-- Fees table (student fee records)
CREATE TABLE fees (
    id INT AUTO_INCREMENT PRIMARY KEY,
    student_id INT NOT NULL,
    fee_structure_id INT,
    amount DECIMAL(12,2) NOT NULL,
    discount DECIMAL(12,2) DEFAULT 0,
    fine DECIMAL(12,2) DEFAULT 0,
    net_amount DECIMAL(12,2) NOT NULL,
    due_date DATE NOT NULL,
    status ENUM('paid', 'unpaid', 'partial', 'overdue', 'waived') DEFAULT 'unpaid',
    month VARCHAR(20),
    academic_year VARCHAR(20),
    remarks TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (student_id) REFERENCES students(id) ON DELETE CASCADE,
    FOREIGN KEY (fee_structure_id) REFERENCES fee_structure(id) ON DELETE SET NULL
);

-- Payments table
CREATE TABLE payments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    fee_id INT NOT NULL,
    student_id INT NOT NULL,
    amount_paid DECIMAL(12,2) NOT NULL,
    payment_date DATE DEFAULT (CURRENT_DATE),
    payment_method ENUM('cash', 'card', 'bank_transfer', 'cheque', 'upi', 'online', 'dd') DEFAULT 'cash',
    transaction_id VARCHAR(100),
    cheque_number VARCHAR(50),
    cheque_date DATE,
    bank_name VARCHAR(100),
    receipt_number VARCHAR(50) UNIQUE,
    remarks TEXT,
    received_by INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (fee_id) REFERENCES fees(id) ON DELETE CASCADE,
    FOREIGN KEY (student_id) REFERENCES students(id) ON DELETE CASCADE,
    FOREIGN KEY (received_by) REFERENCES users(id) ON DELETE SET NULL
);

-- Expenses table
CREATE TABLE expenses (
    id INT AUTO_INCREMENT PRIMARY KEY,
    category VARCHAR(100) NOT NULL,
    description TEXT,
    amount DECIMAL(12,2) NOT NULL,
    expense_date DATE NOT NULL,
    payment_method ENUM('cash', 'bank_transfer', 'cheque', 'upi') DEFAULT 'cash',
    receipt_number VARCHAR(50),
    vendor_name VARCHAR(100),
    approved_by INT,
    created_by INT,
    attachment VARCHAR(255),
    status ENUM('pending', 'approved', 'rejected') DEFAULT 'pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (approved_by) REFERENCES users(id) ON DELETE SET NULL,
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL
);

-- Notifications table
CREATE TABLE notifications (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT,
    title VARCHAR(200) NOT NULL,
    message TEXT NOT NULL,
    type ENUM('info', 'warning', 'success', 'error', 'reminder') DEFAULT 'info',
    link VARCHAR(255),
    is_read BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Announcements table
CREATE TABLE announcements (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(200) NOT NULL,
    content TEXT NOT NULL,
    target_audience ENUM('all', 'students', 'teachers', 'parents', 'staff') DEFAULT 'all',
    class_id INT,
    priority ENUM('low', 'normal', 'high', 'urgent') DEFAULT 'normal',
    start_date DATE,
    end_date DATE,
    attachment VARCHAR(255),
    created_by INT,
    status ENUM('active', 'inactive', 'draft') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (class_id) REFERENCES classes(id) ON DELETE SET NULL,
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL
);

-- Events table
CREATE TABLE events (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(200) NOT NULL,
    description TEXT,
    event_type ENUM('holiday', 'exam', 'meeting', 'sports', 'cultural', 'other') DEFAULT 'other',
    start_date DATE NOT NULL,
    end_date DATE,
    start_time TIME,
    end_time TIME,
    venue VARCHAR(200),
    organizer VARCHAR(100),
    target_audience ENUM('all', 'students', 'teachers', 'parents') DEFAULT 'all',
    status ENUM('upcoming', 'ongoing', 'completed', 'cancelled') DEFAULT 'upcoming',
    created_by INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL
);

-- Timetable table
CREATE TABLE timetable (
    id INT AUTO_INCREMENT PRIMARY KEY,
    class_id INT NOT NULL,
    subject_id INT NOT NULL,
    teacher_id INT,
    day_of_week ENUM('monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday') NOT NULL,
    period_number INT NOT NULL,
    start_time TIME NOT NULL,
    end_time TIME NOT NULL,
    room_number VARCHAR(20),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (class_id) REFERENCES classes(id) ON DELETE CASCADE,
    FOREIGN KEY (subject_id) REFERENCES subjects(id) ON DELETE CASCADE,
    FOREIGN KEY (teacher_id) REFERENCES users(id) ON DELETE SET NULL,
    UNIQUE KEY unique_slot (class_id, day_of_week, period_number)
);

-- Library Books
CREATE TABLE library_books (
    id INT AUTO_INCREMENT PRIMARY KEY,
    book_title VARCHAR(200) NOT NULL,
    author VARCHAR(100),
    isbn VARCHAR(20) UNIQUE,
    publisher VARCHAR(100),
    edition VARCHAR(50),
    category VARCHAR(100),
    quantity INT DEFAULT 1,
    available_quantity INT DEFAULT 1,
    rack_number VARCHAR(20),
    price DECIMAL(10,2),
    status ENUM('available', 'issued', 'damaged', 'lost') DEFAULT 'available',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Library Issue
CREATE TABLE library_issues (
    id INT AUTO_INCREMENT PRIMARY KEY,
    book_id INT NOT NULL,
    user_id INT NOT NULL,
    issue_date DATE NOT NULL,
    due_date DATE NOT NULL,
    return_date DATE,
    fine DECIMAL(10,2) DEFAULT 0,
    status ENUM('issued', 'returned', 'overdue', 'lost') DEFAULT 'issued',
    remarks TEXT,
    issued_by INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (book_id) REFERENCES library_books(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (issued_by) REFERENCES users(id) ON DELETE SET NULL
);

-- Transport Routes
CREATE TABLE transport_routes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    route_name VARCHAR(100) NOT NULL,
    route_code VARCHAR(20) UNIQUE,
    vehicle_number VARCHAR(20),
    driver_name VARCHAR(100),
    driver_phone VARCHAR(20),
    conductor_name VARCHAR(100),
    conductor_phone VARCHAR(20),
    capacity INT DEFAULT 40,
    monthly_fee DECIMAL(10,2),
    status ENUM('active', 'inactive') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Transport Stops
CREATE TABLE transport_stops (
    id INT AUTO_INCREMENT PRIMARY KEY,
    route_id INT NOT NULL,
    stop_name VARCHAR(100) NOT NULL,
    pickup_time TIME,
    drop_time TIME,
    distance_km DECIMAL(5,2),
    fare DECIMAL(10,2),
    sequence_order INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (route_id) REFERENCES transport_routes(id) ON DELETE CASCADE
);

-- Hostel Rooms
CREATE TABLE hostel_rooms (
    id INT AUTO_INCREMENT PRIMARY KEY,
    room_number VARCHAR(20) NOT NULL,
    hostel_block VARCHAR(50),
    room_type ENUM('single', 'double', 'triple', 'dormitory') DEFAULT 'double',
    capacity INT DEFAULT 2,
    occupied INT DEFAULT 0,
    monthly_fee DECIMAL(10,2),
    facilities TEXT,
    status ENUM('available', 'full', 'maintenance') DEFAULT 'available',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Hostel Allocations
CREATE TABLE hostel_allocations (
    id INT AUTO_INCREMENT PRIMARY KEY,
    student_id INT NOT NULL,
    room_id INT NOT NULL,
    bed_number VARCHAR(10),
    allocation_date DATE NOT NULL,
    vacate_date DATE,
    status ENUM('active', 'vacated') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (student_id) REFERENCES students(id) ON DELETE CASCADE,
    FOREIGN KEY (room_id) REFERENCES hostel_rooms(id) ON DELETE CASCADE
);

-- Activity Log
CREATE TABLE activity_log (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT,
    action VARCHAR(100) NOT NULL,
    description TEXT,
    ip_address VARCHAR(50),
    user_agent TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
);

-- SMS/Email Templates
CREATE TABLE message_templates (
    id INT AUTO_INCREMENT PRIMARY KEY,
    template_name VARCHAR(100) NOT NULL,
    template_type ENUM('sms', 'email', 'notification') DEFAULT 'sms',
    subject VARCHAR(200),
    content TEXT NOT NULL,
    variables TEXT,
    status ENUM('active', 'inactive') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Messages Sent Log
CREATE TABLE messages_log (
    id INT AUTO_INCREMENT PRIMARY KEY,
    template_id INT,
    recipient_type ENUM('student', 'teacher', 'parent', 'all') NOT NULL,
    recipient_id INT,
    recipient_contact VARCHAR(100),
    message_type ENUM('sms', 'email', 'notification') DEFAULT 'sms',
    subject VARCHAR(200),
    content TEXT NOT NULL,
    status ENUM('pending', 'sent', 'failed', 'delivered') DEFAULT 'pending',
    sent_at DATETIME,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (template_id) REFERENCES message_templates(id) ON DELETE SET NULL
);

-- Insert Default Settings
INSERT INTO settings (setting_key, setting_value, setting_type, setting_group) VALUES 
('school_name', 'EduManage School', 'text', 'general'),
('school_tagline', 'Excellence in Education', 'text', 'general'),
('school_email', 'info@edumanage.school', 'email', 'general'),
('school_phone', '+91 1234567890', 'phone', 'general'),
('school_mobile', '+91 9876543210', 'phone', 'general'),
('school_address', '123 Education Street, Knowledge City', 'textarea', 'general'),
('school_city', 'Knowledge City', 'text', 'general'),
('school_state', 'State Name', 'text', 'general'),
('school_pincode', '100001', 'text', 'general'),
('school_country', 'India', 'text', 'general'),
('school_website', 'www.edumanage.school', 'text', 'general'),
('school_logo', 'logo.png', 'image', 'general'),
('school_favicon', 'favicon.ico', 'image', 'general'),
('school_established', '1990', 'text', 'general'),
('school_affiliation', 'CBSE', 'text', 'academic'),
('school_affiliation_no', 'CBSE/AFF/2024/001', 'text', 'academic'),
('school_registration_no', 'REG/2024/001', 'text', 'academic'),
('current_academic_year', '2024-2025', 'text', 'academic'),
('current_session_start', '2024-04-01', 'text', 'academic'),
('current_session_end', '2025-03-31', 'text', 'academic'),
('admission_open', '1', 'text', 'academic'),
('currency_symbol', '₹', 'text', 'finance'),
('currency_code', 'INR', 'text', 'finance'),
('late_fee_per_day', '10', 'number', 'finance'),
('fee_due_day', '10', 'number', 'finance'),
('attendance_start_time', '08:00', 'text', 'attendance'),
('attendance_end_time', '14:00', 'text', 'attendance'),
('late_arrival_time', '08:30', 'text', 'attendance'),
('working_days', 'monday,tuesday,wednesday,thursday,friday,saturday', 'text', 'attendance'),
('grading_system', 'percentage', 'text', 'academic'),
('passing_percentage', '33', 'number', 'academic'),
('result_publish_mode', 'manual', 'text', 'academic'),
('sms_enabled', '0', 'text', 'notification'),
('email_enabled', '1', 'text', 'notification'),
('smtp_host', '', 'text', 'email'),
('smtp_port', '587', 'text', 'email'),
('smtp_username', '', 'text', 'email'),
('smtp_password', '', 'text', 'email'),
('smtp_encryption', 'tls', 'text', 'email'),
('principal_name', 'Dr. Principal Name', 'text', 'general'),
('principal_signature', '', 'image', 'general'),
('school_stamp', '', 'image', 'general');

-- Insert default admin user (password: admin123)
-- Hash generated using: password_hash('admin123', PASSWORD_DEFAULT)
INSERT INTO users (username, email, password, role, full_name, phone, status) VALUES 
('admin', 'admin@school.com', '$2y$10$HfzIhGCCaxqyaIdGgjARSuOKAcm1Uy82YfLuNaajn6JrjLWy9Sj/W', 'admin', 'System Administrator', '1234567890', 'active');

-- Insert sample teacher (password: teacher123)
INSERT INTO users (username, email, password, role, full_name, phone, status) VALUES 
('teacher', 'teacher@school.com', '$2y$10$HfzIhGCCaxqyaIdGgjARSuOKAcm1Uy82YfLuNaajn6JrjLWy9Sj/W', 'teacher', 'John Smith', '9876543210', 'active');

INSERT INTO teachers (user_id, employee_id, qualification, specialization, joining_date, salary) VALUES 
(2, 'EMP20240001', 'M.Sc., B.Ed.', 'Mathematics', '2024-01-01', 45000.00);

-- Insert sample classes
INSERT INTO classes (class_name, section, academic_year, capacity, room_number) VALUES 
('Class 1', 'A', '2024-2025', 40, 'R101'),
('Class 1', 'B', '2024-2025', 40, 'R102'),
('Class 2', 'A', '2024-2025', 40, 'R103'),
('Class 2', 'B', '2024-2025', 40, 'R104'),
('Class 3', 'A', '2024-2025', 40, 'R105'),
('Class 4', 'A', '2024-2025', 40, 'R106'),
('Class 5', 'A', '2024-2025', 40, 'R107'),
('Class 6', 'A', '2024-2025', 35, 'R201'),
('Class 7', 'A', '2024-2025', 35, 'R202'),
('Class 8', 'A', '2024-2025', 35, 'R203'),
('Class 9', 'A', '2024-2025', 35, 'R204'),
('Class 10', 'A', '2024-2025', 35, 'R205');

-- Insert fee categories
INSERT INTO fee_categories (category_name, description) VALUES 
('Tuition Fee', 'Monthly tuition fee'),
('Admission Fee', 'One time admission fee'),
('Exam Fee', 'Examination fees'),
('Transport Fee', 'School bus transport fee'),
('Library Fee', 'Library access fee'),
('Sports Fee', 'Sports and games fee'),
('Computer Fee', 'Computer lab fee'),
('Laboratory Fee', 'Science laboratory fee');

-- Insert sample fee structure
INSERT INTO fee_structure (class_id, fee_type, amount, frequency, academic_year, description) VALUES 
(1, 'tuition', 2000.00, 'monthly', '2024-2025', 'Monthly Tuition Fee'),
(1, 'admission', 5000.00, 'one_time', '2024-2025', 'Admission Fee'),
(2, 'tuition', 2000.00, 'monthly', '2024-2025', 'Monthly Tuition Fee'),
(3, 'tuition', 2200.00, 'monthly', '2024-2025', 'Monthly Tuition Fee'),
(4, 'tuition', 2400.00, 'monthly', '2024-2025', 'Monthly Tuition Fee'),
(5, 'tuition', 2600.00, 'monthly', '2024-2025', 'Monthly Tuition Fee'),
(6, 'tuition', 2800.00, 'monthly', '2024-2025', 'Monthly Tuition Fee'),
(7, 'tuition', 3000.00, 'monthly', '2024-2025', 'Monthly Tuition Fee'),
(8, 'tuition', 3200.00, 'monthly', '2024-2025', 'Monthly Tuition Fee'),
(9, 'tuition', 3500.00, 'monthly', '2024-2025', 'Monthly Tuition Fee'),
(10, 'tuition', 3500.00, 'monthly', '2024-2025', 'Monthly Tuition Fee'),
(11, 'tuition', 4000.00, 'monthly', '2024-2025', 'Monthly Tuition Fee'),
(12, 'tuition', 4000.00, 'monthly', '2024-2025', 'Monthly Tuition Fee');

-- Insert sample subjects
INSERT INTO subjects (subject_name, subject_code, class_id, subject_type, total_marks, passing_marks) VALUES 
('English', 'ENG101', 1, 'theory', 100, 33),
('Hindi', 'HIN101', 1, 'theory', 100, 33),
('Mathematics', 'MAT101', 1, 'theory', 100, 33),
('Environmental Science', 'EVS101', 1, 'theory', 100, 33),
('General Knowledge', 'GK101', 1, 'theory', 50, 17),
('English', 'ENG601', 8, 'theory', 100, 33),
('Hindi', 'HIN601', 8, 'theory', 100, 33),
('Mathematics', 'MAT601', 8, 'theory', 100, 33),
('Science', 'SCI601', 8, 'both', 100, 33),
('Social Science', 'SSC601', 8, 'theory', 100, 33),
('Computer Science', 'CS601', 8, 'both', 100, 33);

-- Create indexes for better performance
CREATE INDEX idx_students_class ON students(class_id);
CREATE INDEX idx_students_roll ON students(roll_number);
CREATE INDEX idx_attendance_date ON attendance(date);
CREATE INDEX idx_attendance_student ON attendance(student_id);
CREATE INDEX idx_attendance_status ON attendance(status);
CREATE INDEX idx_results_student ON results(student_id);
CREATE INDEX idx_results_exam ON results(exam_id);
CREATE INDEX idx_fees_student ON fees(student_id);
CREATE INDEX idx_fees_status ON fees(status);
CREATE INDEX idx_fees_due_date ON fees(due_date);
CREATE INDEX idx_payments_student ON payments(student_id);
CREATE INDEX idx_payments_date ON payments(payment_date);
CREATE INDEX idx_homework_class ON homework(class_id);
CREATE INDEX idx_homework_due ON homework(due_date);
CREATE INDEX idx_users_role ON users(role);
CREATE INDEX idx_users_status ON users(status);
CREATE INDEX idx_settings_key ON settings(setting_key);
CREATE INDEX idx_settings_group ON settings(setting_group);

-- Create views for common queries
CREATE VIEW v_student_details AS
SELECT s.*, u.full_name, u.email, u.phone, u.status as user_status, 
       c.class_name, c.section, c.academic_year
FROM students s 
JOIN users u ON s.user_id = u.id 
LEFT JOIN classes c ON s.class_id = c.id;

CREATE VIEW v_teacher_details AS
SELECT t.*, u.full_name, u.email, u.phone, u.status as user_status
FROM teachers t 
JOIN users u ON t.user_id = u.id;

CREATE VIEW v_fee_summary AS
SELECT f.*, s.roll_number, u.full_name as student_name, c.class_name, c.section,
       (SELECT COALESCE(SUM(amount_paid), 0) FROM payments WHERE fee_id = f.id) as paid_amount
FROM fees f 
JOIN students s ON f.student_id = s.id 
JOIN users u ON s.user_id = u.id 
LEFT JOIN classes c ON s.class_id = c.id;

CREATE VIEW v_attendance_summary AS
SELECT a.*, s.roll_number, u.full_name as student_name, c.class_name, c.section
FROM attendance a 
JOIN students s ON a.student_id = s.id 
JOIN users u ON s.user_id = u.id 
LEFT JOIN classes c ON a.class_id = c.id;
