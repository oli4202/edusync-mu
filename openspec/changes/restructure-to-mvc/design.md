## Context

EduSync MU is a PHP web application for university students built with a flat file structure: each page (`pages/*.php`) contains its own routing, logic, database queries, and HTML rendering. The `includes/auth.php` file is a monolith containing auth functions, AI API calls, and utility helpers. The project must be restructured to match the MVC pattern from the [nkb-bd/metro_wb_lab](https://github.com/nkb-bd/metro_wb_lab) reference repository, which uses:

- A single `index.php` entry point (front controller)
- `.htaccess` URL rewriting for Apache
- `router.php` for PHP built-in dev server
- `app/Core/` — Router, Session, Controller base, Mailer
- `app/Controllers/` — AuthController, DashboardController, etc.
- `app/Models/` — User, Post, etc.
- `app/Views/` — PHP templates with a shared layout
- `config/database.php` — DB connection
- `app/helpers.php` — utility functions
- Composer PSR-4 autoloading

The current project has **26 page files**, **7 AJAX endpoints**, **3 admin pages**, and **2 include files** that must all be reorganized.

## Goals / Non-Goals

**Goals:**
- Match the directory structure from the reference repo exactly
- Implement front-controller routing via `index.php` → Router → Controller → Model → View → Response
- Maintain all existing functionality (auth, dashboard, subjects, grades, AI, flashcards, etc.)
- Use Composer for PSR-4 autoloading (`App\` namespace)
- Support both Apache (`.htaccess`) and PHP built-in server (`router.php`)
- Add `.env` file for configuration (DB credentials, API keys)
- Create `docs/` directory with project documentation
- Produce a clean Git history matching the reference repo's commit style

**Non-Goals:**
- No new features — this is a structural refactor only
- No database schema changes — keep existing `sql/schema.sql` as-is
- No CSS/JS redesign — keep existing `assets/css/style.css` intact
- No PHP framework adoption (Laravel, Symfony) — keep it plain PHP with custom MVC
- No unit testing framework in this change

## Decisions

### 1. Project Root = Repository Root (not `edusync/` subfolder)

**Decision:** Move all project files to the repository root, removing the `edusync/` nesting.

**Rationale:** The reference repo has `index.php`, `.htaccess`, `app/`, `config/`, `assets/`, `sql/` all at the root. This matches the expected structure for XAMPP deployment (`htdocs/edusync/`) and simplifies all path references.

**Alternative considered:** Keep `edusync/` as root → rejected because it doesn't match the reference structure.

### 2. Simple Array-Based Router (not Regex)

**Decision:** Use a simple associative-array Router class identical to the reference repo — `$router->get('/path', [$controller, 'method'])`.

**Rationale:** The reference repo uses this pattern. It's easy to understand for beginners and sufficient for the ~30 routes in this project.

**Alternative considered:** Regex-based routing with parameters (e.g., `/posts/{id}`) → rejected as over-engineered for this project.

### 3. Controller Grouping Strategy

**Decision:** Group controllers by domain area:
- `AuthController` — login, register, logout
- `DashboardController` — dashboard, analytics
- `PageController` — all student-facing pages (subjects, grades, attendance, etc.)
- `AjaxController` — all AJAX endpoints (AI suggestions, bookmarks, upvotes, etc.)
- `AdminController` — admin panel, manage attendance, API settings

**Rationale:** Keeps the number of controller files manageable (5-6 instead of 26). Each page becomes a method in the appropriate controller.

### 4. View Rendering with Shared Layout

**Decision:** Use a `layout.php` view that wraps all pages with the shared sidebar, header, and footer. Individual views only contain the page-specific content.

**Rationale:** Eliminates duplicated sidebar includes across 26 page files. Matches the reference repo's `layout.php` pattern.

### 5. Configuration via `.env` File

**Decision:** Replace hardcoded `define()` constants with `.env` file + `getenv()`, exactly as the reference repo does.

**Rationale:** Matches reference repo. Separates secrets from code. The `.env` file is read in `index.php` before anything else.

### 6. Composer Autoloading Only (No Other Dependencies Initially)

**Decision:** Use `composer.json` solely for PSR-4 autoloading of the `App\` namespace. Add PHPMailer as the only dependency (matching reference repo).

**Rationale:** Keeps things simple. Classes are auto-loaded by namespace → directory mapping (`App\Controllers\AuthController` → `app/Controllers/AuthController.php`).

## Risks / Trade-offs

- **[All URLs break]** → Mitigation: Clean URLs (`/dashboard` instead of `/pages/dashboard.php`) are intentional. Old bookmarks won't work, but this is acceptable for a course project.

- **[Composer dependency]** → Mitigation: Composer is easy to install. The `SETUP.md` will include installation instructions. `composer install` is a one-time step.

- **[Large diff]** → Mitigation: Execute the restructure in logical commits (Core first, then Controllers, then Views) to maintain reviewable Git history.

- **[AJAX endpoints must work with new routing]** → Mitigation: Register AJAX routes in `index.php` the same as page routes. Return JSON from controller methods instead of rendering views.

- **[Existing sidebar/navigation links break]** → Mitigation: Update all `href` attributes in `sidebar.php` (now `layout.php`) to use new clean URLs.
