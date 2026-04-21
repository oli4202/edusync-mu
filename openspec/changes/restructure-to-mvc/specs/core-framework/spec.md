## ADDED Requirements

### Requirement: Router Class
The `App\Core\Router` class SHALL provide `get()`, `post()`, and `dispatch()` methods to register and resolve routes.

#### Scenario: Router dispatches GET request
- **WHEN** `$router->dispatch('/dashboard', 'GET')` is called and a GET route for `/dashboard` is registered
- **THEN** the Router SHALL invoke the registered callback

### Requirement: Session Class
The `App\Core\Session` class SHALL provide static methods to start sessions, set/get/delete session values, and manage flash messages.

#### Scenario: Session start
- **WHEN** `Session::start()` is called
- **THEN** a PHP session SHALL be started if not already active

#### Scenario: Flash message
- **WHEN** `Session::flash('success', 'Login successful')` is called
- **THEN** the message SHALL be available for one subsequent request and then auto-deleted

### Requirement: Base Controller Class
The `App\Core\Controller` class SHALL provide a `view()` helper method that loads a View file with passed data and wraps it in the layout.

#### Scenario: Rendering a view
- **WHEN** a controller calls `$this->view('dashboard', ['stats' => $stats])`
- **THEN** the base Controller SHALL load `app/Views/dashboard.php`, extract the data as variables, and include the layout

### Requirement: Mailer Class
The `App\Core\Mailer` class SHALL provide email sending functionality using PHPMailer or PHP's `mail()` function.

#### Scenario: Sending a test email
- **WHEN** `Mailer::send($to, $subject, $body)` is called with valid parameters
- **THEN** the Mailer SHALL attempt to send an email and return a success/failure status
