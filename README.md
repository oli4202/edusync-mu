# EduSync MU

EduSync MU is a PHP and MySQL student portal built for the Software Engineering Department at Metropolitan University Sylhet. It combines academic tracking, study tools, campus information, question practice, AI-assisted learning, and career resources in one dashboard-style web app.

## Overview

The project is structured as a classic PHP application with page-based modules under `pages/`, shared helpers in `includes/`, configuration in `config/`, AJAX endpoints in `ajax/`, and a small admin area in `admin/`.

Core areas currently available in the app:

- Dashboard with study, task, and group summaries
- Tasks and Kanban workflow
- Subjects with year and semester grouping
- Attendance tracking
- Grades and results
- MU result lookup
- Announcements and prospectus access
- Fee payment page
- Study groups and partner discovery
- AI assistant, flashcards, exam suggestions, and YouTube learning
- Question bank and question submission
- Code playground
- Internship and job listings
- Admin panel with API key settings

## Tech Stack

- Backend: PHP 8.x
- Database: MySQL / MariaDB with PDO
- Frontend: HTML, CSS, vanilla JavaScript
- Charts/UI libraries: Chart.js, CodeMirror
- AI integrations:Groq,gemini

## Project Structure

```text
edusync/
|-- admin/              # admin dashboard and API settings
|-- ajax/               # async endpoints
|-- assets/             # CSS, JS, images, icons
|-- config/             # database and API configuration
|-- includes/           # auth, shared helpers, sidebar
|-- pages/              # main student-facing modules
|-- sql/                # schema and seed data
|-- index.php           # app entry point
```

## Main Pages

Student-facing pages under `pages/`:

- `dashboard.php`
- `tasks.php`
- `subjects.php`
- `analytics.php`
- `attendance.php`
- `grades.php`
- `result-lookup.php`
- `announcements.php`
- `prospectus.php`
- `fees.php`
- `groups.php`
- `partners.php`
- `ai.php`
- `flashcards.php`
- `question-bank.php`
- `submit-question.php`
- `suggestions.php`
- `learn.php`
- `playground.php`
- `jobs.php`

Authentication pages:

- `login.php`
- `signup.php`
- `logout.php`

Admin pages:

- `admin/index.php`
- `admin/api-settings.php`

## Setup

### Prerequisites

1. Install XAMPP or another local stack that includes PHP 8+ and MySQL.
2. Make sure PHP and MySQL are available locally.
3. Place the project in a writable local folder.

### Database Setup

1. Start MySQL from XAMPP.
2. Create a database named `edusync_mu` if it does not already exist.
3. Import `sql/schema.sql`.

The schema file already includes:

- table creation
- seed course data
- a seeded admin user

Seeded admin account:

- Email: `admin@edusync.mu`
- Password: `admin123`

### Configuration

Update [`config/database.php`](C:/Users/Oli/Downloads/edusync-mu-complete/edusync/config/database.php) if your local database credentials differ from the defaults:

- `DB_HOST`
- `DB_NAME`
- `DB_USER`
- `DB_PASS`
- `SITE_URL`

Current local defaults in the project:

- Host: `localhost`
- Database: `edusync_mu`
- User: `root`
- Password: empty
- Site URL: `http://localhost:8000`

### AI API Keys

API keys are stored in [`config/api-keys.php`](C:/Users/Oli/Downloads/edusync-mu-complete/edusync/config/api-keys.php).

Supported providers:

- Google Gemini
- Hugging Face
- Anthropic Claude

You can configure them in either of these ways:

1. Edit `config/api-keys.php` manually.
2. Log in as admin and open `admin/api-settings.php`.

The app tries providers in this order:

1. Gemini
2. Hugging Face
3. Claude

If no valid key is configured, AI features fail gracefully and show a helpful message instead of crashing the app.

## Running Locally

### Option 1: PHP built-in server

From the project root:

```powershell
C:\xampp\php\php.exe -S localhost:8000
```

Then open:

`http://localhost:8000`

### Option 2: Apache through XAMPP

If you place the project under `C:\xampp\htdocs\edusync`, you can also run it through Apache and open:

`http://localhost/edusync`

If you use this route, update `SITE_URL` in `config/database.php` so redirects stay correct.

## Authentication

- Students can create accounts from `pages/signup.php`
- Returning users can sign in from `pages/login.php`
- Protected pages use session-based authentication from [`includes/auth.php`](C:/Users/Oli/Downloads/edusync-mu-complete/edusync/includes/auth.php)
- Admin-only access is enforced for admin pages

## Database Highlights

The schema includes tables for:

- users
- subjects
- tasks and subtasks
- study logs
- grades
- study groups and group messages
- shared notes
- flashcards
- follows
- courses
- questions and answers
- bookmarks

This supports both personal productivity features and collaborative study workflows.

## Security Notes

The project currently uses:

- PDO prepared statements for database access
- `password_hash()` and `password_verify()` for credentials
- output escaping with `htmlspecialchars()`
- session-based login protection
- role checks for admin-only pages

## Notes For Development

- The app is page-driven rather than framework-based.
- Shared auth and helper logic lives in `includes/auth.php`.
- Shared navigation lives in `includes/sidebar.php`.
- Some features depend on seeded database content from `sql/schema.sql`.
- Local server log files such as `php-server.err.log` and `php-server.out.log` are runtime artifacts and should not be committed.

## Default Test Flow

After setup, a quick manual test path is:

1. Start MySQL.
2. Start the PHP server.
3. Open `http://localhost:8000`.
4. Sign in with the seeded admin account or create a student account.
5. Visit dashboard, tasks, subjects, question bank, AI tools, and admin API settings.

## Status

This README now reflects the current project layout and local configuration used by the app in this repository.
