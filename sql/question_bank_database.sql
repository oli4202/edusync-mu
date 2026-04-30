-- ============================================================
-- EduSync Question Bank Database
-- ============================================================
-- Usage:
--   mysql -u root -p < sql/question_bank_database.sql

CREATE DATABASE IF NOT EXISTS edusync_question_bank
CHARACTER SET utf8mb4
COLLATE utf8mb4_unicode_ci;

USE edusync_question_bank;

-- ============================================================
-- 1. USERS
-- ============================================================
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(120) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    role ENUM('student', 'faculty', 'admin') DEFAULT 'student',
    student_id VARCHAR(30) UNIQUE,
    batch VARCHAR(20),
    semester INT DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- ============================================================
-- 2. COURSES
-- ============================================================
CREATE TABLE IF NOT EXISTS courses (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(200) NOT NULL,
    code VARCHAR(30) UNIQUE,
    year INT NOT NULL,
    semester INT NOT NULL,
    batch VARCHAR(20),
    status ENUM('active', 'reserved') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- ============================================================
-- 3. QUESTIONS
-- ============================================================
CREATE TABLE IF NOT EXISTS questions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    course_id INT NOT NULL,
    submitted_by INT NULL,
    question_text LONGTEXT NOT NULL,
    question_type ENUM('short', 'broad', 'mcq', 'problem') DEFAULT 'broad',
    exam_year INT,
    exam_semester ENUM('1st','2nd','3rd','4th','5th','6th','7th','8th'),
    marks INT DEFAULT 10,
    topic VARCHAR(200),
    is_approved BOOLEAN DEFAULT FALSE,
    approved_by INT NULL,
    approved_at TIMESTAMP NULL,
    view_count INT DEFAULT 0,
    image_path VARCHAR(500),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (course_id) REFERENCES courses(id) ON DELETE CASCADE,
    FOREIGN KEY (submitted_by) REFERENCES users(id) ON DELETE SET NULL,
    FOREIGN KEY (approved_by) REFERENCES users(id) ON DELETE SET NULL,
    INDEX idx_questions_course (course_id),
    INDEX idx_questions_topic (topic),
    INDEX idx_questions_exam_year (exam_year)
);

-- ============================================================
-- 4. ANSWERS
-- ============================================================
CREATE TABLE IF NOT EXISTS answers (
    id INT AUTO_INCREMENT PRIMARY KEY,
    question_id INT NOT NULL,
    user_id INT NULL,
    answer_text LONGTEXT NOT NULL,
    compact_answer TEXT,
    solution_steps TEXT,
    ai_compact BOOLEAN DEFAULT FALSE,
    is_approved BOOLEAN DEFAULT FALSE,
    upvotes INT DEFAULT 0,
    approved_by INT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (question_id) REFERENCES questions(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL,
    FOREIGN KEY (approved_by) REFERENCES users(id) ON DELETE SET NULL,
    INDEX idx_answers_question (question_id),
    INDEX idx_answers_user (user_id)
);

-- ============================================================
-- 5. QUESTION BOOKMARKS
-- ============================================================
CREATE TABLE IF NOT EXISTS question_bookmarks (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    question_id INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (question_id) REFERENCES questions(id) ON DELETE CASCADE,
    UNIQUE KEY unique_bookmark (user_id, question_id)
);

-- ============================================================
-- 6. QUESTION TOPICS
-- ============================================================
CREATE TABLE IF NOT EXISTS question_topics (
    id INT AUTO_INCREMENT PRIMARY KEY,
    question_id INT NOT NULL,
    topic_name VARCHAR(100) NOT NULL,
    FOREIGN KEY (question_id) REFERENCES questions(id) ON DELETE CASCADE,
    INDEX idx_question_topics_name (topic_name)
);

-- ============================================================
-- Optional seed data (safe to re-run)
-- ============================================================
INSERT IGNORE INTO courses (name, code, year, semester, batch, status) VALUES
('Web Programming Practice Lab', 'SWE 322', 2, 2, '6', 'active'),
('Artificial Intelligence Lab', 'SWE 316', 2, 2, '6', 'active');
