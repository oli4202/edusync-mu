-- ============================================================
--  EduSync MU Sylhet Edition — Complete Database Schema
--  Metropolitan University Sylhet, Software Engineering Dept
-- ============================================================

CREATE DATABASE IF NOT EXISTS edusync_mu;
USE edusync_mu;

-- ============================================================
-- 1. USERS
-- ============================================================
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(150) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    avatar VARCHAR(255) DEFAULT 'default.png',
    student_id VARCHAR(30),
    batch VARCHAR(20),
    semester INT DEFAULT 1,
    department VARCHAR(100) DEFAULT 'Software Engineering',
    bio TEXT,
    streak INT DEFAULT 0,
    last_active DATE,
    role ENUM('student','faculty','admin') DEFAULT 'student',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE student_batch_memberships (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    student_id VARCHAR(30) NOT NULL,
    batch VARCHAR(20) NOT NULL,
    semester INT NOT NULL DEFAULT 1,
    label VARCHAR(50) DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY unique_student_batch_membership (student_id, batch, semester),
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- ============================================================
-- 2. SUBJECTS
-- ============================================================
CREATE TABLE subjects (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    name VARCHAR(150) NOT NULL,
    code VARCHAR(30),
    color VARCHAR(10) DEFAULT '#4f46e5',
    year INT,
    semester INT,
    target_hours_per_week DECIMAL(4,1) DEFAULT 5.0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- ============================================================
-- 3. TASKS
-- ============================================================
CREATE TABLE tasks (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    subject_id INT,
    title VARCHAR(255) NOT NULL,
    description TEXT,
    priority ENUM('low','medium','high') DEFAULT 'medium',
    status ENUM('todo','in_progress','done') DEFAULT 'todo',
    due_date DATE,
    is_recurring BOOLEAN DEFAULT FALSE,
    recur_type ENUM('daily','weekly','none') DEFAULT 'none',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (subject_id) REFERENCES subjects(id) ON DELETE SET NULL
);

-- ============================================================
-- 4. SUBTASKS
-- ============================================================
CREATE TABLE subtasks (
    id INT AUTO_INCREMENT PRIMARY KEY,
    task_id INT NOT NULL,
    title VARCHAR(255) NOT NULL,
    is_done BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (task_id) REFERENCES tasks(id) ON DELETE CASCADE
);

-- ============================================================
-- 5. STUDY LOGS
-- ============================================================
CREATE TABLE study_logs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    subject_id INT,
    hours DECIMAL(4,1) NOT NULL,
    notes TEXT,
    logged_date DATE NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (subject_id) REFERENCES subjects(id) ON DELETE SET NULL
);

-- ============================================================
-- 6. GRADES
-- ============================================================
CREATE TABLE grades (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    subject_id INT,
    title VARCHAR(150) NOT NULL,
    score DECIMAL(5,2) NOT NULL,
    max_score DECIMAL(5,2) DEFAULT 100,
    exam_date DATE,
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (subject_id) REFERENCES subjects(id) ON DELETE SET NULL
);

-- ============================================================
-- 7. STUDY GROUPS
-- ============================================================
CREATE TABLE study_groups (
    id INT AUTO_INCREMENT PRIMARY KEY,
    subject_id INT,
    creator_id INT NOT NULL,
    name VARCHAR(150) NOT NULL,
    description TEXT,
    is_public BOOLEAN DEFAULT TRUE,
    max_members INT DEFAULT 20,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (creator_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (subject_id) REFERENCES subjects(id) ON DELETE SET NULL
);

-- ============================================================
-- 8. GROUP MEMBERS
-- ============================================================
CREATE TABLE group_members (
    id INT AUTO_INCREMENT PRIMARY KEY,
    group_id INT NOT NULL,
    user_id INT NOT NULL,
    role ENUM('admin','member') DEFAULT 'member',
    joined_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (group_id) REFERENCES study_groups(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    UNIQUE KEY unique_membership (group_id, user_id)
);

-- ============================================================
-- 9. GROUP MESSAGES
-- ============================================================
CREATE TABLE group_messages (
    id INT AUTO_INCREMENT PRIMARY KEY,
    group_id INT NOT NULL,
    user_id INT NOT NULL,
    message TEXT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (group_id) REFERENCES study_groups(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- ============================================================
-- 10. SHARED NOTES
-- ============================================================
CREATE TABLE shared_notes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    group_id INT,
    user_id INT NOT NULL,
    subject_id INT,
    title VARCHAR(255) NOT NULL,
    content LONGTEXT,
    is_pinned BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (group_id) REFERENCES study_groups(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (subject_id) REFERENCES subjects(id) ON DELETE SET NULL
);

-- ============================================================
-- 11. FLASHCARDS
-- ============================================================
CREATE TABLE flashcards (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    subject_id INT,
    deck_name VARCHAR(150) DEFAULT 'General',
    question TEXT NOT NULL,
    answer TEXT NOT NULL,
    ai_generated BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (subject_id) REFERENCES subjects(id) ON DELETE SET NULL
);

-- ============================================================
-- 12. FOLLOWS
-- ============================================================
CREATE TABLE follows (
    id INT AUTO_INCREMENT PRIMARY KEY,
    follower_id INT NOT NULL,
    following_id INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (follower_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (following_id) REFERENCES users(id) ON DELETE CASCADE,
    UNIQUE KEY unique_follow (follower_id, following_id)
);

-- ============================================================
-- 13. COURSES (MU Sylhet SE Department)
-- ============================================================
CREATE TABLE courses (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(200) NOT NULL,
    code VARCHAR(30) UNIQUE,
    year INT NOT NULL,
    semester INT NOT NULL,
    batch VARCHAR(20),
    department VARCHAR(100) DEFAULT 'Software Engineering',
    description TEXT,
    status ENUM('active','reserved') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- ============================================================
-- 14. QUESTIONS (Past Exam Questions)
-- ============================================================
CREATE TABLE questions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    course_id INT NOT NULL,
    submitted_by INT,
    question_text LONGTEXT NOT NULL,
    question_type ENUM('short','broad','mcq','problem') DEFAULT 'broad',
    exam_year INT,
    exam_semester ENUM('1st','2nd','3rd','4th','5th','6th','7th','8th'),
    marks INT DEFAULT 10,
    topic VARCHAR(200),
    is_approved BOOLEAN DEFAULT FALSE,
    approved_by INT,
    approved_at TIMESTAMP NULL,
    view_count INT DEFAULT 0,
    image_path VARCHAR(500),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (course_id) REFERENCES courses(id) ON DELETE CASCADE,
    FOREIGN KEY (submitted_by) REFERENCES users(id) ON DELETE SET NULL,
    FOREIGN KEY (approved_by) REFERENCES users(id) ON DELETE SET NULL
);

-- ============================================================
-- 15. ANSWERS (Answers & Solutions for Questions)
-- ============================================================
CREATE TABLE answers (
    id INT AUTO_INCREMENT PRIMARY KEY,
    question_id INT NOT NULL,
    user_id INT,
    answer_text LONGTEXT NOT NULL,
    compact_answer TEXT,
    solution_steps TEXT,
    ai_compact BOOLEAN DEFAULT FALSE,
    is_approved BOOLEAN DEFAULT FALSE,
    upvotes INT DEFAULT 0,
    approved_by INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (question_id) REFERENCES questions(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL,
    FOREIGN KEY (approved_by) REFERENCES users(id) ON DELETE SET NULL
);

-- ============================================================
-- 16. QUESTION BOOKMARKS
-- ============================================================
CREATE TABLE question_bookmarks (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    question_id INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (question_id) REFERENCES questions(id) ON DELETE CASCADE,
    UNIQUE KEY unique_bookmark (user_id, question_id)
);

-- ============================================================
-- 17. QUESTION TOPICS (Tag system)
-- ============================================================
CREATE TABLE question_topics (
    id INT AUTO_INCREMENT PRIMARY KEY,
    question_id INT NOT NULL,
    topic_name VARCHAR(100) NOT NULL,
    FOREIGN KEY (question_id) REFERENCES questions(id) ON DELETE CASCADE
);

-- ============================================================
-- SEED DATA — MU Sylhet SE Courses (Complete 4-Year Curriculum)
-- ============================================================
INSERT IGNORE INTO courses (name, code, year, semester, batch, status) VALUES
-- Semester 1 (Batch 10 & 11)
('Communicative English Language I', 'GED 101', 1, 1, '11', 'active'),
('Differential and Integral Calculus', 'MAT 111', 1, 1, '10,11', 'active'),
('Introduction to Software Engineering', 'SWE 111', 1, 1, '10,11', 'active'),
('Bangladesh Studies', 'GED 105', 1, 1, '10,11', 'active'),
('Structured Programming', 'SWE 121', 1, 1, '10,11', 'active'),
('Structured Programming Lab', 'SWE 122', 1, 1, '10,11', 'active'),
('Discrete Mathematics', 'MAT 113', 1, 1, '10,11', 'active'),
('Basic Physics', 'PHY 111', 1, 1, '10,11', 'active'),

-- Semester 2 (Batch 9)
('Data Structures', 'SWE 123', 1, 2, '9', 'active'),
('Data Structures Lab', 'SWE 124', 1, 2, '9', 'active'),
('Digital Logic Design', 'SWE 215', 1, 2, '9', 'active'),
('Digital Logic Design Lab', 'SWE 216', 1, 2, '9', 'active'),
('Management Information Systems', 'SWE 133', 1, 2, '9', 'active'),
('Project on Python Development', 'SWE 182', 1, 2, '9', 'active'),

-- Semester 3 (Batch 8)
('Algorithms', 'SWE 221', 1, 3, '8', 'active'),
('Algorithms Lab', 'SWE 222', 1, 3, '8', 'active'),
('Database Management Systems', 'SWE 225', 1, 3, '8', 'active'),
('Database Management Systems Lab', 'SWE 226', 1, 3, '8', 'active'),
('Theory of Computation', 'SWE 311', 1, 3, '8', 'active'),
('Software Requirement Engineering', 'SWE 231', 1, 3, '8', 'active'),

-- Semester 4 (Batch 5)
('Machine Learning', 'SWE 317', 2, 1, '5', 'active'),
('Machine Learning Lab', 'SWE 318', 2, 1, '5', 'active'),
('Computer Networking', 'SWE 313', 2, 1, '5', 'active'),
('Computer Networking Lab', 'SWE 314', 2, 1, '5', 'active'),
('Digital Marketing', 'SWE 449', 2, 1, '5', 'active'),
('Software Project Management', 'SWE 431', 2, 1, '5', 'active'),

-- Semester 5 (Batch 6)
('Artificial Intelligence', 'SWE 315', 2, 2, '6', 'active'),
('Artificial Intelligence Lab', 'SWE 316', 2, 2, '6', 'active'),
('Web Programming Practice Lab', 'SWE 322', 2, 2, '6', 'active'),
('Software UI & UX Design Practice Lab', 'SWE 324', 2, 2, '6', 'active'),
('Basic Statistics and Probability', 'SWE 341', 2, 2, '6', 'active'),

-- Semester 6 (Batch 7)
('Software Architecture and Design Patterns', 'SWE 233', 2, 3, '7', 'active'),
('Software Architecture and Design Patterns Lab', 'SWE 234', 2, 3, '7', 'active'),
('Numerical Analysis', 'MAT 211', 2, 3, '7', 'active'),
('Problem Solving with Competitive Programming Lab-I', 'SWE 381', 2, 3, '7', 'active'),
('Project on Java GUI Development', 'SWE 282', 2, 3, '7', 'active'),

-- Semester 7 (Batch 4)
('Embedded System & IoT', 'SWE 465', 3, 1, '4', 'active'),
('Embedded System & IoT Lab', 'SWE 466', 3, 1, '4', 'active'),
('Introduction to Cryptography', 'SWE 461', 3, 1, '4', 'active'),
('Mobile App Development Practice Lab', 'SWE 422', 3, 1, '4', 'active'),
('Entrepreneurship Development', 'SWE 443', 3, 1, '4', 'active'),

-- Semester 8 (Batch 3)
('Final Year Project', 'SWE 482', 3, 2, '3', 'active'),
('Digital Marketing (Retake/Advanced)', 'SWE 449-B', 3, 2, '3', 'active'),
('Software Project Management (Advanced)', 'SWE 431-B', 3, 2, '3', 'active'),

-- Reserved Subjects (Previous Curriculum/Electives)
('Basic Electrical and Electronic Circuits', 'SWE 111-R', 4, 1, NULL, 'reserved'),
('Linear Algebra & Differential Equations', 'MAT 112', 4, 1, NULL, 'reserved'),
('Object Oriented Programming', 'SWE 223', 4, 2, NULL, 'reserved'),
('Object Oriented Programming Lab', 'SWE 224', 4, 2, NULL, 'reserved'),
('Operating Systems', 'SWE 213', 4, 3, NULL, 'reserved'),
('Operating Systems Lab', 'SWE 214', 4, 3, NULL, 'reserved'),
('Data Science Fundamentals', 'SWE 451', 5, 1, NULL, 'reserved'),
('Data Science Lab', 'SWE 452', 5, 1, NULL, 'reserved'),
('Cloud Computing', 'SWE 319', 5, 2, NULL, 'reserved'),
('Cybersecurity Fundamentals', 'SWE 462', 5, 2, NULL, 'reserved'),
('Internship', 'SWE 484', 5, 3, NULL, 'reserved'),
('Advanced Machine Learning', 'SWE 457', 6, 1, NULL, 'reserved'),
('Advanced Machine Learning Lab', 'SWE 458', 6, 1, NULL, 'reserved'),
('Natural Language Processing', 'SWE 459', 6, 2, NULL, 'reserved'),
('Computer Graphics', 'SWE 453', 6, 3, NULL, 'reserved'),
('Distributed Systems', 'SWE 464', 7, 1, NULL, 'reserved'),
('Professional Ethics', 'SWE 447', 7, 2, NULL, 'reserved'),
('Research Methodology', 'SWE 483', 7, 3, NULL, 'reserved'),
('English II', 'GED 102', 8, 1, NULL, 'reserved'),
('Functional Bangla', 'GED 103', 8, 1, NULL, 'reserved'),
('History of Bangladesh', 'GED 104', 8, 1, NULL, 'reserved'),
('Meteorology', 'MAT 212', 8, 2, NULL, 'reserved'),
('Complex Analysis', 'MAT 213', 8, 2, NULL, 'reserved'),
('Compiler Design', 'SWE 321', 8, 3, NULL, 'reserved'),
('Software Testing', 'SWE 333', 8, 3, NULL, 'reserved'),
('E-Commerce Systems', 'SWE 343', 8, 3, NULL, 'reserved'),
('Engineering Economics', 'SWE 445', 8, 3, NULL, 'reserved'),
('Ethics & Cyber Law', 'SWE 447-R', 8, 3, NULL, 'reserved');

-- ============================================================
-- SEED DATA — Admin user (password: admin123)
-- ============================================================
INSERT INTO users (name, email, password, role) VALUES
('Admin', 'admin@edusync.mu', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.ucrm3a', 'admin');

-- ============================================================
-- 18. ATTENDANCE
-- ============================================================
CREATE TABLE IF NOT EXISTS attendance (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    course_id INT NOT NULL,
    class_date DATE NOT NULL,
    status ENUM('present','absent','late','excused') DEFAULT 'present',
    notes VARCHAR(255),
    marked_by INT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (course_id) REFERENCES courses(id) ON DELETE CASCADE,
    UNIQUE KEY unique_att (user_id, course_id, class_date)
);

-- ============================================================
-- 19. ANNOUNCEMENTS
-- ============================================================
CREATE TABLE IF NOT EXISTS announcements (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    title VARCHAR(255) NOT NULL,
    content TEXT NOT NULL,
    type ENUM('general','exam','assignment','event','urgent') DEFAULT 'general',
    target_semester INT DEFAULT 0,
    is_pinned BOOLEAN DEFAULT FALSE,
    expires_at DATE NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- ============================================================
-- SEED — Sample Announcements
-- ============================================================
INSERT INTO announcements (user_id, title, content, type, is_pinned) VALUES
(1, 'Welcome to EduSync MU!', 'Welcome to EduSync — the official study platform for Metropolitan University Sylhet, Software Engineering Department.\n\nUse the Question Bank to find past exam questions, the AI Assistant for exam prep, and track your attendance and grades here.', 'general', 1),
(1, 'Mid-Term Exam Schedule Released', 'Mid-term examinations for Spring 2026 semester will commence from next week. Please check your exam schedule on the official MU portal and prepare accordingly.\n\nUse the AI Exam Suggestions tool for topic predictions!', 'exam', 1);
