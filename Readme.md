# Requirements Management & Project Tracking Application

**Branch for submission:** `main`

## Description
This is a comprehensive Symfony application for managing project requirements and tracking project progress.  
The application includes:

- User management (Responsables) with hashed passwords
- Project management with owners
- Requirements tracking (functional and non-functional)
- Milestones for deliverables
- Task management with dependencies
- Many-to-many relationships between tasks and requirements
- Display of projects, requirements, milestones, and tasks
- Data fixtures for testing

## Database Schema:
- Graphical representation of the database schema is in Graph.mermaid

---

## Prerequisites

- PHP 8.1+
- Composer
- Symfony CLI (optional, but recommended)
- MySQL or another supported database
- Node.js & npm (for frontend assets if needed)

---

## Installation

### 1. Clone the repository and switch to the correct branch

```bash
git clone https://github.com/Firstmars01/AppManagment.git
git checkout main
```

### 2. Install PHP dependencies

```bash
composer install
```

### 3. Configure environment variables

Copy `.env` to `.env.local` and update database credentials:

```bash
DATABASE_URL="mysql://db_user:db_password@127.0.0.1:3306/requirements_db"
```

### 4. Create the database

```bash
php bin/console doctrine:database:create
php bin/console doctrine:migrations:migrate
```

### 5. Load fixtures for testing data

```bash
php bin/console doctrine:fixtures:load
```

Use `--append` if you want to keep existing data.

---

## Running the Project

### 1. Start the Symfony local server

```bash
symfony server:start
```

or with PHP's built-in server:

```bash
php -S 127.0.0.1:8000 -t public
```

### 2. Open your browser at:

```
http://127.0.0.1:8000/projects
```

You should see the list of projects with their owners and requirements.

---

## Project Structure

```
src/
 ├─ Controller/        # Controllers for handling requests
 ├─ Entity/            # Doctrine entities (Project, Requirement, Task, Milestone, etc.)
 ├─ Repository/        # Custom repository methods
 └─ DataFixtures/      # Fixtures for testing data

templates/
 ├─ project/           # Twig templates for projects
 ├─ requirement/       # Twig templates for requirements
 ├─ milestone/         # Twig templates for milestones
 └─ task/              # Twig templates for tasks

public/                # Public assets
```

---

## Database Schema

The application uses the following entities:

### **Responsable** (User/Manager)
- `id` : INT (PK)
- `name` : VARCHAR(255)
- `firstname` : VARCHAR(255)
- `email` : VARCHAR(255) UNIQUE
- `password` : VARCHAR(255) (hashed)

### **Project**
- `id` : UUID (PK)
- `name` : VARCHAR(255)
- `manager_id` : INT (FK → Responsable)
- `created_at` : DATE
- `updated_at` : DATE

### **RequirementType**
- `id` : INT (PK)
- `description` : VARCHAR(255)

### **Requirement**
- `id` : UUID (PK)
- `project_id` : UUID (FK → Project)
- `description` : TEXT
- `is_functional` : BOOLEAN
- `requirement_type_id` : INT (FK → RequirementType)
- `created_at` : DATE
- `updated_at` : DATE

### **Milestone**
- `id` : UUID (PK)
- `project_id` : UUID (FK → Project)
- `label` : VARCHAR(255)
- `manager_id` : INT (FK → Responsable)
- `planned_start_date` : DATE
- `actual_start_date` : DATE

### **Task**
- `id` : UUID (PK)
- `project_id` : UUID (FK → Project)
- `milestone_id` : UUID (FK → Milestone)
- `is_functional` : BOOLEAN
- `label` : VARCHAR(255)
- `manager_id` : INT (FK → Responsable)
- `planned_start_date` : DATE
- `actual_start_date` : DATE
- `days_estimate` : INT
- `previous_task_id` : UUID (FK → Task, nullable)

### **TaskRequirement** (Join Table)
- `task_id` : UUID (PK, FK → Task)
- `requirement_id` : UUID (PK, FK → Requirement)

---

## Features

### Core Functionality
- **User Management**: Responsables with secure password hashing
- **Project Management**: Create and manage projects with owners
- **Requirements Tracking**: Functional and non-functional requirements
- **Milestone Planning**: Group tasks into deliverable milestones
- **Task Management**: Tasks with dependencies and progress tracking
- **Many-to-Many Relations**: Tasks linked to multiple requirements

### Technical Features
- Clean MVC architecture with Symfony best practices
- Doctrine ORM with UUID for primary keys
- Proper entity relationships (OneToMany, ManyToOne, ManyToMany)
- Password hashing using Symfony's PasswordHasher
- Data fixtures for rapid development and testing
- Twig templates for clean view rendering
- Repository pattern for custom queries

---

### Generate Migration
```bash
php bin/console make:migration
php bin/console doctrine:migrations:migrate
```

### Create controllers
```bash
php bin/console make:controller ProjectController
```

### Add testing controllers
```bash
php bin/console make:test ProjectControllerTest --functional
```
---

## Testing

### Load Test Data
```bash
php bin/console doctrine:fixtures:load
```

This will create:
- Sample users (Responsables)
- Sample projects
- Sample requirements (functional and non-functional)
- Sample milestones
- Sample tasks with dependencies
- Task-Requirement associations

---

## Extending the Project

### Add New Features
- Implement filtering by requirement type
- Add pagination for large datasets
- Create dashboard with project statistics
- Add requirement coverage tracking
- Implement task progress percentage
- Add Gantt chart for milestones and tasks
- Create REST API endpoints
- Add user authentication and authorization

### Add New Entities
```bash
php bin/console make:entity NewEntity
php bin/console make:migration
php bin/console doctrine:migrations:migrate
```

---

## Notes

- All passwords are hashed using Symfony's PasswordHasher component
- UUIDs are used for entities requiring global uniqueness (Project, Requirement, Milestone, Task)
- Auto-increment integers are used for Responsable and RequirementType
- Fixtures provide comprehensive test data for development
- The application follows Symfony best practices and PSR standards
- All templates are designed to be simple, readable, and maintainable

---

## Troubleshooting

### Database Connection Issues
```bash
# Verify database credentials in .env.local
# Test connection
php bin/console doctrine:database:create
```

### Migration Issues
```bash
# Reset database (WARNING: destroys data)
php bin/console doctrine:database:drop --force
php bin/console doctrine:database:create
php bin/console doctrine:migrations:migrate
```

### Clear Cache
```bash
php bin/console cache:clear
```

---
