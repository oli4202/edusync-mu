# EduSync MU — Student Management & Study Platform

EduSync MU is a comprehensive web application designed specifically for the Software Engineering Department at Metropolitan University Sylhet. It provides students with a centralized hub for managing tasks, tracking attendance, collaborating in study groups, and utilizing AI-powered study tools.

## 🌟 Key Features

*   **Task & Kanban Board:** Manage assignments visually with To-Do, In-Progress, and Done columns.
*   **Study Analytics:** Track your weekly study hours with interactive charts and visualizations.
*   **Study Groups & Partners:** Form groups, share notes, and connect with other SE students.
*   **AI Study Assistant:** Powered by Claude AI (Summarize notes, generate flashcards, and compact exam answers).
*   **MU Academic Calendar:** Custom tri-semester calendar (Spring, Summer, Fall) integrated with your tasks.
*   **Flashcards & Quizzes:** Auto-generated interactive study tools.
*   **Past Question Bank:** Access and submit previous years' MU exam questions.

---

## 🛠️ Technical Stack
*   **Frontend:** HTML5, Vanilla CSS (Custom Design System), JavaScript (Chart.js, CodeMirror).
*   **Backend:** PHP 8.x
*   **Database:** MySQL (PDO with Prepared Statements for SQL Injection protection).
*   **Security:** Password Hashing (`password_hash`), XSS Protection (`htmlspecialchars`), Session Hijacking prevention.

---

## 🚀 Setup Instructions

Follow these instructions strictly to get the app running on your local machine.

### Prerequisites
1.  Install **XAMPP** (or any AMP stack with PHP 8+ and MySQL).
2.  Clone or download this repository into your XAMPP `htdocs` folder. The folder should be named `edusync`.
    *   Path: `C:\xampp\htdocs\edusync`

### Database Setup
1.  Open the XAMPP Control Panel and start **Apache** and **MySQL**.
2.  Open your browser and navigate to `http://localhost/phpmyadmin`.
3.  Create a new blank database named **`edusync_mu`**.
4.  Navigate to the **Import** tab.
5.  Browse and upload the `sql/schema.sql` file located in the project folder.
6.  Click **Import** to create all tables and insert the seed data (Courses, Announcements, Admin user).

### Configuration
1.  Open the project in your code editor.
2.  Navigate to `config/database.php`.
3.  Verify your database credentials (default is `root` with no password for XAMPP).
4.  *(Optional)* To enable the AI features (Summarizer, Flashcards), replace `'YOUR_CLAUDE_API_KEY_HERE'` with a valid Anthropic Claude 3.5 Sonnet API key. If left blank, the app will gracefully simulate AI responses so the UI remains functional.

---

## 📖 User Guide

### 1. Accessing the App
*   Navigate your browser to `http://localhost/edusync`
*   You will be redirected to the Login page.

### 2. Authentication
*   **Student Account:** Click "Sign up" to create a new student account. You must provide a valid email and password. Your password will be securely hashed.
*   **Admin Account:** You can log in using the pre-seeded admin credentials to test administrative privileges:
    *   **Email:** `admin@edusync.mu`
    *   **Password:** `admin123`

### 3. Navigating the Dashboard
Once logged in, use the left sidebar to access different modules:
*   **Tasks:** Add new assignments and drag them across the Kanban board.
*   **Calendar:** View your upcoming tasks alongside the Metropolitan University academic schedule.
*   **AI Assistant:** Test the Note Summarizer and Chat tool (mock responses provided if no API key).
*   **Analytics:** Log study hours and view your progress in the dynamic charts.

---

## 🔒 Security Implementations Focus

For grading purposes, please note the following security standards applied in this project:
1.  **SQL Queries Protected:** Every database interaction is routed through PHP Data Objects (PDO). We exclusively use prepared statements (`$db->prepare(...)` and `$stmt->execute([...])`) across all CRUD operations to prevent SQL Injection.
2.  **Passwords Stored Safely:** All passwords are hashed using bcrypt via PHP's built-in `password_hash()` before database insertion. Authentication uses `password_verify()`.
3.  **Error Handling:** The `config/database.php` connection uses `PDO::ERRMODE_EXCEPTION` to cleanly catch and handle server errors without exposing stack traces.

---
*Developed for Metropolitan University Sylhet*
