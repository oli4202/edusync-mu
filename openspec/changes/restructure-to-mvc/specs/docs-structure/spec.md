## ADDED Requirements

### Requirement: Documentation Directory
A `docs/` directory SHALL exist at the project root containing project documentation files.

#### Scenario: Required documentation files
- **WHEN** the project is submitted
- **THEN** the `docs/` directory SHALL contain at minimum: `proposal.md`, `user-guide.md`, and `db-diagram.png`

### Requirement: Project Proposal
The `docs/proposal.md` file SHALL describe the project's purpose, target users, features, and tech stack.

#### Scenario: Proposal content
- **WHEN** a reviewer reads `docs/proposal.md`
- **THEN** it SHALL contain: project name, description, list of features, tech stack, and team members

### Requirement: User Guide
The `docs/user-guide.md` file SHALL explain how to use the application from a user's perspective.

#### Scenario: User guide content
- **WHEN** a new user reads `docs/user-guide.md`
- **THEN** it SHALL contain step-by-step instructions for registration, login, and using key features

### Requirement: Database Diagram
The `docs/db-diagram.png` file SHALL show an Entity-Relationship diagram of the database schema.

#### Scenario: ER diagram
- **WHEN** a reviewer views `docs/db-diagram.png`
- **THEN** it SHALL show all tables, their columns, and relationships (foreign keys)
