## 1. Project Root & Config Setup

- [ ] 1.1 Create `composer.json` at repo root with PSR-4 autoload mapping `App\` → `app/`
- [ ] 1.2 Create `.env.example` with all config keys (DB_HOST, DB_NAME, DB_USER, DB_PASS, DB_CHARSET, SITE_NAME, SITE_URL, BASE_PATH, GEMINI_API_KEY, HF_API_KEY)
- [ ] 1.3 Create `.env` from `.env.example` with current database values
- [ ] 1.4 Create `.htaccess` with Apache rewrite rules (RewriteEngine On, forward non-file/non-dir to index.php)
- [ ] 1.5 Create `.gitignore` with vendor/, .env, uploads/*, *.log entries
- [ ] 1.6 Create `.editorconfig` with consistent coding style rules
- [ ] 1.7 Run `composer install` to generate `vendor/autoload.php`

## 2. Core Framework Classes

- [ ] 2.1 Create `app/Core/Router.php` — Router class with `get()`, `post()`, `dispatch()` methods
- [ ] 2.2 Create `app/Core/Session.php` — Session class with `start()`, `get()`, `set()`, `flash()` static methods
- [ ] 2.3 Create `app/Core/Controller.php` — Base Controller with `view()` method that loads views with layout
- [ ] 2.4 Create `app/Core/Mailer.php` — Mailer class for email sending functionality

## 3. Config & Database

- [ ] 3.1 Create `config/database.php` — PDO connection using `getenv()` values from .env
- [ ] 3.2 Move API key configuration into `.env` (GEMINI_API_KEY, HF_API_KEY)

## 4. Helpers

- [ ] 4.1 Create `app/helpers.php` — consolidate utility functions: `clean()`, `timeAgo()`, `callAI()`, `callGeminiAPI()`, `callHuggingFaceAPI()`, `getDB()`, `base_url()`, `redirect()`, `asset()`

## 5. Models

- [ ] 5.1 Create `app/Models/User.php` — user CRUD, authentication (findByEmail, register, updateStreak, etc.)
- [ ] 5.2 Create `app/Models/Subject.php` — subject queries
- [ ] 5.3 Create `app/Models/Task.php` — task CRUD and kanban operations
- [ ] 5.4 Create `app/Models/Grade.php` — grade/result queries
- [ ] 5.5 Create `app/Models/Attendance.php` — attendance records
- [ ] 5.6 Create `app/Models/Announcement.php` — announcements CRUD
- [ ] 5.7 Create `app/Models/Question.php` — question bank queries (questions, upvotes, bookmarks)
- [ ] 5.8 Create `app/Models/Flashcard.php` — flashcard/deck operations
- [ ] 5.9 Create `app/Models/Fee.php` — fee payment records
- [ ] 5.10 Create `app/Models/Group.php` — study group operations
- [ ] 5.11 Create `app/Models/Job.php` — job/internship listings

## 6. Controllers

- [ ] 6.1 Create `app/Controllers/AuthController.php` — showLogin, login, showRegister, register, logout methods
- [ ] 6.2 Create `app/Controllers/DashboardController.php` — index (dashboard), analytics methods
- [ ] 6.3 Create `app/Controllers/PageController.php` — methods for: subjects, grades, attendance, announcements, calendar, fees, groups, partners, ai, flashcards, questionBank, questionDetail, submitQuestion, suggestions, learn, playground, jobs, resultLookup, prospectus, tasks
- [ ] 6.4 Create `app/Controllers/AjaxController.php` — methods for: aiCompact, aiSuggest, bookmark, upvote, muResult, runPython, runSql
- [ ] 6.5 Create `app/Controllers/AdminController.php` — index, manageAttendance, apiSettings methods

## 7. Views

- [ ] 7.1 Create `app/Views/layout.php` — shared layout with sidebar, header, footer (extracted from current sidebar.php)
- [ ] 7.2 Create `app/Views/auth/login.php` — login page template
- [ ] 7.3 Create `app/Views/auth/register.php` — registration page template
- [ ] 7.4 Create `app/Views/dashboard.php` — dashboard view
- [ ] 7.5 Create `app/Views/analytics.php` — analytics view
- [ ] 7.6 Create `app/Views/subjects.php`, `grades.php`, `attendance.php`, `announcements.php` views
- [ ] 7.7 Create `app/Views/calendar.php`, `fees.php`, `groups.php`, `partners.php` views
- [ ] 7.8 Create `app/Views/ai.php`, `flashcards.php`, `question-bank.php`, `question-detail.php` views
- [ ] 7.9 Create `app/Views/submit-question.php`, `suggestions.php`, `learn.php`, `playground.php` views
- [ ] 7.10 Create `app/Views/jobs.php`, `result-lookup.php`, `prospectus.php`, `tasks.php` views
- [ ] 7.11 Create `app/Views/admin/index.php`, `admin/manage-attendance.php`, `admin/api-settings.php` views
- [ ] 7.12 Create `app/Views/404.php` — 404 error page view

## 8. Entry Point & Router Setup

- [ ] 8.1 Create root `index.php` — load autoloader, parse .env, start session, instantiate controllers, register all routes, dispatch
- [ ] 8.2 Create `router.php` — PHP built-in server router (serve static files directly, forward rest to index.php)
- [ ] 8.3 Register all GET routes (/, /login, /register, /dashboard, /analytics, /subjects, /grades, /attendance, /announcements, /calendar, /fees, /groups, /partners, /ai, /flashcards, /question-bank, /question/:id, /submit-question, /suggestions, /learn, /playground, /jobs, /result-lookup, /prospectus, /tasks, /logout)
- [ ] 8.4 Register all POST routes (/login, /register, /submit-question, /tasks, etc.)
- [ ] 8.5 Register AJAX routes (/ajax/ai-compact, /ajax/ai-suggest, /ajax/bookmark, /ajax/upvote, /ajax/mu-result, /ajax/run-python, /ajax/run-sql)
- [ ] 8.6 Register admin routes (/admin, /admin/manage-attendance, /admin/api-settings)

## 9. Assets & Static Files

- [ ] 9.1 Move `edusync/assets/css/` → `assets/css/` at project root
- [ ] 9.2 Create `assets/js/main.js` for shared JavaScript
- [ ] 9.3 Create `uploads/` directory with `.gitkeep`

## 10. Documentation

- [ ] 10.1 Create `docs/proposal.md` — project proposal document
- [ ] 10.2 Create `docs/user-guide.md` — user guide with screenshots/instructions
- [ ] 10.3 Create `docs/db-diagram.png` — ER diagram of the database schema
- [ ] 10.4 Create/update `README.md` at project root with setup instructions and project overview
- [ ] 10.5 Create `SETUP.md` with detailed setup instructions (XAMPP, Composer, database import)

## 11. SQL & Database

- [ ] 11.1 Move `edusync/sql/schema.sql` → `sql/schema.sql` at project root
- [ ] 11.2 Verify schema.sql has all CREATE TABLE statements needed

## 12. Git Cleanup & Commits

- [ ] 12.1 Remove old `edusync/` directory structure after migration
- [ ] 12.2 Create clean, descriptive Git commits matching reference repo style (Init → Setup → Refactor)
- [ ] 12.3 Verify project works with `php -S localhost:8000 router.php`
