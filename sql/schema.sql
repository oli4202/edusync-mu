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
    role ENUM('student','admin') DEFAULT 'student',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
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
    department VARCHAR(100) DEFAULT 'Software Engineering',
    description TEXT,
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
-- SEED DATA — MU Sylhet SE Courses
-- ============================================================
INSERT INTO courses (name, code, year, semester) VALUES
-- Year 1
('Introduction to Programming (C)', 'SWE101', 1, 1),
('Discrete Mathematics', 'MATH101', 1, 1),
('Digital Logic Design', 'SWE102', 1, 2),
('English & Communication', 'ENG101', 1, 2),
-- Year 2
('Data Structures & Algorithms', 'SWE201', 2, 3),
('Object Oriented Programming (Java)', 'SWE202', 2, 3),
('Database Management Systems', 'SWE203', 2, 4),
('Computer Architecture & Organization', 'SWE204', 2, 4),
('Numerical Methods', 'MATH201', 2, 4),
-- Year 3
('Software Engineering', 'SWE301', 3, 5),
('Operating Systems', 'SWE302', 3, 5),
('Computer Networks', 'SWE303', 3, 6),
('Web Technologies', 'SWE304', 3, 6),
('Theory of Computation', 'SWE305', 3, 6),
('Artificial Intelligence', 'SWE306', 3, 6),
-- Year 4
('Software Project Management', 'SWE401', 4, 7),
('Compiler Design', 'SWE402', 4, 7),
('Information Security', 'SWE403', 4, 7),
('Machine Learning', 'SWE404', 4, 8),
('Distributed Systems', 'SWE405', 4, 8),
('Mobile Application Development', 'SWE406', 4, 8);

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
