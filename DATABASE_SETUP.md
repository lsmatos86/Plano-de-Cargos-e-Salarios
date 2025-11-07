# Database Setup Instructions

## Important: You Need to Create a PostgreSQL Database

This application requires a PostgreSQL database to function. The database **cannot** be created automatically - you must create it manually using Replit's Database tool.

## Step 1: Create the Database

1. Click on the **"Database"** icon in the left sidebar of Replit
2. Click **"Create a database"**
3. Select **PostgreSQL**
4. Replit will automatically set up the database and create environment variables

## Step 2: Run the Schema

Once the database is created:

1. Open the **SQL Runner** in the Database tool
2. Copy the entire contents of the file: `sql/postgres/schema.sql`
3. Paste it into the SQL Runner
4. Click **"Execute"** or **"Run"**

This will create all necessary tables for the application.

## Step 3: Create an Admin User

After running the schema, create an initial admin user by running this SQL:

```sql
-- Create admin role
INSERT INTO roles (roleName, roleDescription) 
VALUES ('Admin', 'Administrator with full system access');

-- Create admin user (email: admin@example.com, password: admin123)
INSERT INTO usuarios (nome, email, senha, ativo) 
VALUES ('Admin User', 'admin@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 1);

-- Link user to admin role (assumes first role ID is 1 and first user ID is 1)
INSERT INTO user_roles (usuarioId, roleId) 
VALUES (1, 1);

-- Grant all permissions to admin role
INSERT INTO permissions (permissionName, permissionDescription) VALUES 
('cargos:view', 'View job positions'),
('cargos:create', 'Create job positions'),
('cargos:edit', 'Edit job positions'),
('cargos:delete', 'Delete job positions'),
('usuarios:view', 'View users'),
('usuarios:create', 'Create users'),
('usuarios:edit', 'Edit users'),
('usuarios:delete', 'Delete users');

-- Link all permissions to admin role
INSERT INTO role_permissions (roleId, permissionId)
SELECT 1, permissionId FROM permissions;
```

## Step 4: Log In

Once the database is set up and the admin user is created:

1. Visit your application
2. Log in with:
   - **Email**: `admin@example.com`
   - **Password**: `admin123`

**Important**: Change the admin password immediately after first login!

## Troubleshooting

### "Connection Error" Message
- Make sure you have created a PostgreSQL database in Replit
- Check that the database is running (green status in Database tool)
- Verify the schema has been executed successfully

### Can't Log In
- Make sure you ran the admin user creation SQL
- Verify the password is exactly: `admin123`
- Check that the user's `ativo` field is set to `1`

### Tables Not Found
- You need to run the schema from `sql/postgres/schema.sql`
- Make sure all SQL executed without errors

## Database Schema Location

The PostgreSQL schema is located at:
```
sql/postgres/schema.sql
```

This was automatically converted from the original MySQL schema in:
```
sql/azukicom_kopplaita.sql
```
