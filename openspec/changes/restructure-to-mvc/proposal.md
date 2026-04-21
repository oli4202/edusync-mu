## Why

The current EduSync project uses a flat file-based structure (`pages/`, `includes/`, `ajax/`) where each PHP page handles its own routing, logic, and HTML rendering. This makes the codebase harder to maintain as it grows. The project needs to be restructured to follow the MVC (Model-View-Controller) pattern used by the reference repository [nkb-bd/metro_wb_lab](https://github.com/nkb-bd/metro_wb_lab), which uses a proper front-controller pattern with a Router, Controllers, Models, Views, and Core classes. This restructuring is required to meet the course project structure guidelines.

## What Changes

- **BREAKING** — Move the project root from `edusync/` to the repository root, matching the reference repo's flat structure
- **BREAKING** — Replace individual page files (`pages/*.php`) with a front-controller `index.php` that routes all requests
- Add `.htaccess` for Apache URL rewriting (all requests → `index.php`)
- Add `router.php` for PHP built-in dev server support
- Create `app/Core/` with Router, Session, Controller, and Mailer classes
- Create `app/Controllers/` — extract logic from each page into controller classes (AuthController, DashboardController, PageController, AjaxController, AdminController, etc.)
- Create `app/Models/` — extract database queries from `includes/auth.php` and page files into model classes (User, Subject, Task, Grade, Attendance, etc.)
- Create `app/Views/` — move HTML templates from `pages/` into view files, with a shared `layout.php`
- Create `app/helpers.php` — consolidate utility functions (clean, timeAgo, flash messages, AI API calls)
- Move `config/database.php` to `config/database.php` (same relative path but at new root)
- Add `config/` with `.env` support for database credentials and API keys
- Add `composer.json` with PSR-4 autoloading for the `App\` namespace
- Keep existing `sql/schema.sql` and `assets/css/` in place
- Add `uploads/` directory for user-uploaded files
- Add `docs/` directory with `proposal.md`, `user-guide.md`, and `db-diagram.png`
- Replicate the Git commit structure style from the reference repo (clean, descriptive commits)

## Capabilities

### New Capabilities
- `front-controller-routing`: Single entry point (`index.php`) with Router class that matches URLs to controller methods via GET/POST route definitions
- `mvc-architecture`: Full MVC separation — Controllers handle request logic, Models handle database queries, Views render HTML templates
- `core-framework`: Core classes (Router, Session, Controller base class, Mailer) providing reusable infrastructure
- `env-config`: Environment-based configuration using `.env` file instead of hardcoded constants
- `composer-autoloading`: PSR-4 autoloading via Composer so classes are loaded automatically by namespace
- `docs-structure`: Project documentation directory with proposal, user guide, and ER diagram

### Modified Capabilities
_(none — this is a structural reorganization of the same features)_

## Impact

- **All PHP files**: Every page, include, and ajax file will be refactored and relocated
- **Entry point**: `edusync/index.php` → root `index.php` (front controller)
- **URLs**: All URLs change from `/pages/dashboard.php` style to `/dashboard` clean URLs
- **Database config**: Moves from hardcoded constants to `.env`-based configuration
- **Dependencies**: Adds Composer for autoloading (and optionally PHPMailer for email)
- **Server config**: Requires `.htaccess` (Apache) or `router.php` (PHP built-in server)
- **Git structure**: Repository root becomes the project root (removes `edusync/` nesting)
- **Assets**: CSS/JS paths update to `/assets/css/style.css` and `/assets/js/main.js`
