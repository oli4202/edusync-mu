## ADDED Requirements

### Requirement: Composer JSON Configuration
A `composer.json` file SHALL define PSR-4 autoloading mapping the `App\` namespace to the `app/` directory.

#### Scenario: Autoloading a controller
- **WHEN** `index.php` includes `vendor/autoload.php` and references `App\Controllers\AuthController`
- **THEN** PHP SHALL automatically load `app/Controllers/AuthController.php`

#### Scenario: Autoloading a model
- **WHEN** code references `App\Models\User`
- **THEN** PHP SHALL automatically load `app/Models/User.php`

### Requirement: Vendor Autoload
The `index.php` entry point SHALL require `vendor/autoload.php` as its first include to enable class autoloading.

#### Scenario: Composer install generates autoloader
- **WHEN** `composer install` is run in the project root
- **THEN** `vendor/autoload.php` SHALL be generated and all `App\` namespace classes SHALL be autoloadable
