# Plano de Cargos e Salários

## Overview
This is a PHP web application for managing job positions, salaries, and organizational hierarchy ("Plano de Cargos e Salários" in Portuguese). The system includes features for:
- Managing job positions (cargos)
- Defining salary ranges and hierarchical levels
- Tracking skills, characteristics, and risks
- User management with role-based permissions
- Audit logging
- PDF report generation

## Project Structure
- **src/**: PHP application code (PSR-4 autoloaded)
  - `Core/`: Database connection and core utilities
  - `Repository/`: Data access layer
  - `Service/`: Business logic (Auth, Audit, etc.)
- **views/**: PHP view files for different modules
- **includes/**: Shared PHP includes (header, footer, functions)
- **css/**: Stylesheets
- **scripts/**: JavaScript files
- **relatorios/**: Report generation scripts
- **sql/**: Database schemas
  - `azukicom_kopplaita.sql`: Original MySQL schema
  - `postgres/schema.sql`: PostgreSQL-converted schema
- **vendor/**: Composer dependencies

## Technology Stack
- **Language**: PHP 8.2
- **Database**: PostgreSQL (converted from MySQL)
- **Dependencies** (via Composer):
  - `dompdf/dompdf`: PDF generation
  - `masterminds/html5`: HTML5 parsing
- **Frontend**: Bootstrap 5, Font Awesome icons
- **Authentication**: Session-based with password hashing
- **Authorization**: Role-based permission system

## Database Setup
The application was originally designed for MySQL but has been adapted for PostgreSQL on Replit.

### Steps to set up the database:
1. **Create a PostgreSQL database** in Replit:
   - Use the Database tool in Replit
   - Click "Create a database"
   - This will automatically create environment variables

2. **Run the schema**:
   - Open the SQL runner in Replit's Database tool
   - Copy and paste the contents of `sql/postgres/schema.sql`
   - Execute the SQL to create all tables

3. **Create initial admin user**:
   ```sql
   -- Create a default admin role first
   INSERT INTO roles (roleName, roleDescription) VALUES ('Admin', 'Administrator role with full access');
   
   -- Create admin user (password: admin123)
   INSERT INTO usuarios (nome, email, senha, ativo) 
   VALUES ('Admin', 'admin@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 1);
   
   -- Link user to admin role
   INSERT INTO user_roles (usuarioId, roleId) 
   VALUES (1, 1);
   ```

## Environment Variables
The application automatically detects Replit's PostgreSQL environment variables:
- `DATABASE_URL`: Full database connection string
- `PGHOST`: PostgreSQL host
- `PGDATABASE`: Database name
- `PGUSER`: Database user
- `PGPASSWORD`: Database password
- `PGPORT`: Database port (default: 5432)

These are set automatically when you create a database in Replit.

## Development
- **Server**: PHP built-in web server on port 5000
- **Workflow**: Configured as "PHP Web Server"
- **Entry point**: `index.php` (dashboard)
- **Login**: `login.php`

## Recent Changes
- **2025-11-06**: Initial Replit setup
  - Converted MySQL schema to PostgreSQL
  - Updated `config.php` to support Replit environment variables
  - Modified `src/Core/Database.php` to support PostgreSQL
  - Created workflow for PHP built-in server on port 5000
  - Added `.gitignore` for PHP project

## User Preferences
- Project language: Portuguese (Brazil)
- Timezone: America/Bahia

## Deployment
The application can be deployed using Replit's deployment feature. The deployment configuration should use:
- Build: None required (PHP is interpreted)
- Run: `php -S 0.0.0.0:5000 -t .`
- Deployment target: `vm` (for stateful session management)

## Next Steps
1. Create a PostgreSQL database in Replit
2. Run the schema from `sql/postgres/schema.sql`
3. Create an initial admin user
4. Log in and start using the application
