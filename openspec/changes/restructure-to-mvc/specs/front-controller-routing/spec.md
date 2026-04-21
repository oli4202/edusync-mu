## ADDED Requirements

### Requirement: Single Entry Point
The system SHALL route all HTTP requests through a single `index.php` file located at the project root.

#### Scenario: Browser requests a page
- **WHEN** a browser requests any URL (e.g., `/dashboard`, `/login`, `/grades`)
- **THEN** the web server (Apache or PHP built-in) SHALL forward the request to `index.php`

#### Scenario: Static file request
- **WHEN** a browser requests a static file that exists on disk (e.g., `/assets/css/style.css`, `/assets/js/main.js`)
- **THEN** the web server SHALL serve the file directly without going through `index.php`

### Requirement: Route Registration
The system SHALL allow registering routes with HTTP method (GET/POST) and a controller-method callback.

#### Scenario: GET route registration
- **WHEN** a GET route is registered as `$router->get('/dashboard', [$controller, 'index'])`
- **THEN** the Router SHALL store this mapping and invoke `$controller->index()` when a GET request matches `/dashboard`

#### Scenario: POST route registration
- **WHEN** a POST route is registered as `$router->post('/login', [$auth, 'login'])`
- **THEN** the Router SHALL store this mapping and invoke `$auth->login()` when a POST request matches `/login`

### Requirement: Route Dispatch
The Router SHALL match the current request URI and method to a registered route and invoke the corresponding controller method.

#### Scenario: Matching route found
- **WHEN** a request is dispatched with a URI and method that matches a registered route
- **THEN** the Router SHALL call the registered callback and pass control to the controller

#### Scenario: No matching route
- **WHEN** a request URI does not match any registered route
- **THEN** the Router SHALL return a 404 response

### Requirement: Apache Rewrite Rules
An `.htaccess` file SHALL configure Apache to forward non-file, non-directory requests to `index.php`.

#### Scenario: Apache serves the app
- **WHEN** the project is deployed under Apache (e.g., XAMPP `htdocs/edusync/`)
- **THEN** `.htaccess` SHALL use `RewriteEngine On` with conditions `!-f` and `!-d` to route to `index.php`

### Requirement: PHP Built-in Server Support
A `router.php` file SHALL allow running the app with `php -S localhost:8000 router.php`.

#### Scenario: Dev server routing
- **WHEN** the app is started with `php -S localhost:8000 router.php`
- **THEN** `router.php` SHALL serve existing files directly and forward all other requests to `index.php`
