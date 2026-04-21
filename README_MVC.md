# EduSync MU — Lightweight MVC Edition

A modern student portal built with a lightweight custom MVC framework (no external dependencies except Composer autoloading).

## Project Structure

```
edusync-mu-complete/
├── app/
│   ├── Core/              # Framework core classes
│   │   ├── Router.php     # URL routing
│   │   ├── Controller.php # Base controller
│   │   └── Session.php    # Session management
│   ├── Models/            # Data models
│   │   ├── User.php
│   │   └── Subject.php
│   ├── Controllers/       # Request handlers
│   │   ├── AuthController.php
│   │   ├── DashboardController.php
│   │   └── PageController.php
│   ├── Views/             # HTML templates
│   │   ├── auth/
│   │   ├── dashboard/
│   │   ├── pages/
│   │   └── layouts/
│   └── helpers.php        # Utility functions
├── config/
│   └── database.php       # DB connection
├── public/
│   ├── assets/
│   │   ├── css/
│   │   └── js/
│   └── uploads/
├── sql/
│   └── schema.sql         # Database schema
├── vendor/                # Composer packages
├── index.php              # Front controller
├── router.php             # PHP dev server router
├── composer.json
├── .htaccess              # Apache rewrite rules
├── .env.example           # Environment template
└── README.md
```

## Getting Started

### 1. Clone & Setup

```bash
# Install dependencies
composer install

# Copy environment file
cp .env.example .env

# Edit .env with your database credentials
```

### 2. Database

```bash
# Create database and tables
mysql -u root < sql/schema.sql
```

### 3. Run Development Server

**Option A: PHP Built-in Server**
```bash
php -S localhost:8000 router.php
```

**Option B: Apache**
- Ensure `.htaccess` is enabled (`a2enmod rewrite`)
- Point `DocumentRoot` to project root
- Access via `http://localhost/edusync-mu-complete/`

## Architecture

### MVC Pattern

- **Models** (`app/Models/`) — Database operations
  - Encapsulate SQL queries
  - Return structured data
  - No business logic

- **Controllers** (`app/Controllers/`) — Request handling
  - Check authentication
  - Call models for data
  - Pass data to views
  - No SQL queries

- **Views** (`app/Views/`) — HTML templates
  - Display data only
  - Loops and conditionals for output
  - No database calls
  - Wrapped by `layout.php`

### Core Classes

- **Router** — Maps URLs to controller methods
- **Controller** — Base class with `render()`, `json()`, auth helpers
- **Session** — Manages user sessions and authentication

## URL Routing

All URLs are rewritten to `index.php` which dispatches to controllers.

### Examples

```
GET  /login                  → AuthController@login
POST /auth/login             → AuthController@doLogin
GET  /dashboard              → DashboardController@index
GET  /tasks                  → PageController@page('tasks')
GET  /logout                 → AuthController@logout
```

### Adding Routes

Edit `index.php` and add:

```php
$router->get('/path', 'ControllerName@method');
$router->post('/path', 'ControllerName@method');
```

## Creating Controllers

```php
<?php
namespace App\Controllers;
use App\Core\Controller;

class TaskController extends Controller
{
    public function index(): void
    {
        $this->requireLogin();
        $userId = $this->session->userId();
        
        // Fetch data via models
        $tasks = Task::findByUser($userId);
        
        // Render view with data
        $this->render('tasks/index', ['tasks' => $tasks]);
    }
}
```

## Creating Models

```php
<?php
namespace App\Models;
use function App\getDB;

class Task
{
    public static function findById(int $id): ?array
    {
        $db = getDB();
        return $db->prepare("SELECT * FROM tasks WHERE id = ?")
            ->execute([$id])
            ->fetch() ?: null;
    }
}
```

## Creating Views

```php
<?php
// Views are passed data as local variables
// app/Views/tasks/index.php
$currentPage = 'tasks';
?>

<h2>My Tasks</h2>
<?php foreach ($tasks as $task): ?>
    <div>
        <h3><?php echo htmlspecialchars($task['title']); ?></h3>
        <p>Due: <?php echo $task['due_date']; ?></p>
    </div>
<?php endforeach; ?>
```

## Helper Functions

Available in `app/helpers.php`:

```php
getDB()           // Get PDO database connection
clean($input)     // Sanitize user input
timeAgo($date)    // Format time (e.g., "2 hours ago")
redirect($url)    // Redirect to URL
callAI($prompt)   // Call Gemini API
```

## Authentication

### Login Flow

1. User submits `/login` form → `AuthController@doLogin`
2. `User::authenticate()` validates credentials
3. Session data is set via `Session@loginUser()`
4. Redirect to `/dashboard`

### Protecting Routes

```php
$this->requireLogin();  // Redirect if not logged in
$this->requireAdmin();  // Redirect if not admin
```

## Database

- **Tables**: users, subjects, tasks, grades, attendance, study_logs, questions, groups, announcements
- **Connection**: PDO via `getDB()`
- **Queries**: Prepared statements only (prevents SQL injection)

## Best Practices

1. **Never** execute SQL in views or controllers
2. **Always** use models for database access
3. **Always** sanitize user input with `clean()`
4. **Use** prepared statements for all queries
5. **Return** associative arrays from models
6. **Keep** controllers thin — delegate to models

## Deployment

1. Set `APP_ENV=production` in `.env`
2. Disable debug mode: `APP_DEBUG=false`
3. Ensure `.htaccess` is enabled on Apache
4. Use environment variables for sensitive data
5. Keep `vendor/` in `.gitignore`

## Next Steps

- [ ] Create remaining page controllers (subjects, grades, etc.)
- [ ] Create AJAX controller for API endpoints
- [ ] Add admin panel controller
- [ ] Implement file upload handling
- [ ] Add email notifications
- [ ] Create unit tests

## Support

Refer to the original pages in `edusync/pages/` for feature context while refactoring to controllers.

---

**Built with:** PHP 7.4+, PDO, Custom Router, PSR-4 Autoloading
**Architecture:** Lightweight MVC without external frameworks
