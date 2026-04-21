## ADDED Requirements

### Requirement: Controller Layer
Each Controller class SHALL handle HTTP request logic, validate input, call Models for data, and render Views for output.

#### Scenario: Page request handled by controller
- **WHEN** the Router dispatches a request to a controller method (e.g., `DashboardController::index()`)
- **THEN** the controller SHALL check authentication, fetch data via Models, and render the appropriate View

#### Scenario: Controller requires authentication
- **WHEN** a controller method requires the user to be logged in and the user is not authenticated
- **THEN** the controller SHALL redirect to the login page

### Requirement: Model Layer
Each Model class SHALL encapsulate all database queries for its domain entity, using PDO prepared statements.

#### Scenario: Model queries database
- **WHEN** a controller calls a Model method (e.g., `User::findByEmail($email)`)
- **THEN** the Model SHALL execute a prepared SQL query and return the result

#### Scenario: Model returns structured data
- **WHEN** a Model method retrieves data
- **THEN** it SHALL return associative arrays (via `PDO::FETCH_ASSOC`)

### Requirement: View Layer
Views SHALL be PHP template files containing only HTML and display logic (loops, conditionals for output). Views SHALL NOT contain business logic or direct database calls.

#### Scenario: View renders with data
- **WHEN** a controller passes data to a View (e.g., `$this->view('dashboard', ['user' => $user])`)
- **THEN** the View file SHALL have access to the data as local variables and render HTML

#### Scenario: Shared layout wraps views
- **WHEN** a View is rendered
- **THEN** it SHALL be wrapped in `layout.php` which provides the sidebar, header, and common HTML structure

### Requirement: Directory Structure
The project SHALL organize files as: `app/Controllers/`, `app/Models/`, `app/Views/`, with each class in its own file.

#### Scenario: Controller file location
- **WHEN** a controller class `AuthController` exists
- **THEN** it SHALL be located at `app/Controllers/AuthController.php` in the `App\Controllers` namespace

#### Scenario: Model file location
- **WHEN** a model class `User` exists
- **THEN** it SHALL be located at `app/Models/User.php` in the `App\Models` namespace
