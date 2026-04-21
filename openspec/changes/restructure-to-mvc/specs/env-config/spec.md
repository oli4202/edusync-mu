## ADDED Requirements

### Requirement: Environment File
The system SHALL read configuration from a `.env` file at the project root, parsing `KEY=VALUE` pairs into `getenv()` and `$_ENV`.

#### Scenario: Database config from .env
- **WHEN** the `.env` file contains `DB_HOST=localhost`, `DB_NAME=edusync_mu`, `DB_USER=root`, `DB_PASS=`
- **THEN** `getenv('DB_HOST')` SHALL return `localhost` and the database connection SHALL use these values

#### Scenario: API keys from .env
- **WHEN** the `.env` file contains `GEMINI_API_KEY=abc123`
- **THEN** `getenv('GEMINI_API_KEY')` SHALL return `abc123`

#### Scenario: .env.example template
- **WHEN** the project is cloned fresh
- **THEN** a `.env.example` file SHALL exist with all required keys and placeholder values, and the user SHALL copy it to `.env`

### Requirement: Config Database File
The `config/database.php` file SHALL establish the PDO database connection using values from `.env`.

#### Scenario: Successful connection
- **WHEN** `config/database.php` is loaded and `.env` has valid credentials
- **THEN** a PDO connection SHALL be established and available via a `getDB()` function or similar
