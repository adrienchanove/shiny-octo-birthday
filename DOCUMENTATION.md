# Application Flow Documentation

## User Journey

### 1. Registration & Login Flow
```
User → register.php → Create Account → login.php → Dashboard (index.php)
```

### 2. Project Creation Flow
```
Dashboard → create_project.php → Fill Form → Submit → view_project.php
```

### 3. Invitation Creation Flow
```
view_project.php → Create Invitation Button → create_invitation.php → 
Enter Invitee Info → Submit → Invitation Link Generated → view_project.php
```

### 4. Invitation Acceptance Flow
```
Invitee Receives Link → accept_invitation.php?project=ID&code=CODE →
View Event Details → Accept/Decline → Status Updated
```

## Page Descriptions

### Authentication Pages

**register.php**
- New user registration
- Validates username, email, password
- Password hashing with password_hash()
- Minimum 8 character password requirement

**login.php**
- User authentication
- Session creation on success
- Password verification with password_verify()

**logout.php**
- Clears session variables
- Destroys session
- Redirects to login

### Project Management Pages

**index.php** (Dashboard)
- Lists all user's projects
- Shows project cards with:
  - Title
  - Event type (party/birthday)
  - Event date
  - Description
- Links to view details and create invitations
- Requires authentication

**create_project.php**
- Form to create new project
- Fields:
  - Title (required)
  - Event Type: party or birthday (required)
  - Event Date (required)
  - Description (optional)
- Redirects to project detail on success
- Requires authentication

**view_project.php**
- Shows project details
- Lists all invitations for the project
- Displays invitation links with copy button
- Shows invitation status (pending/accepted/declined)
- Requires authentication and ownership verification

### Invitation Pages

**create_invitation.php**
- Form to create invitation for a project
- Fields:
  - Invitee Name (optional)
  - Invitee Email (optional)
- Generates unique invitation code (format: XXXX-XXXX-XXXX)
- Requires authentication and ownership verification

**accept_invitation.php** (Public Page)
- Accessible via unique link (no login required)
- URL format: `?project=ID&code=CODE`
- Shows event details to invitee
- Allows accept/decline actions
- Updates invitation status

### Utility Pages

**setup_check.php**
- Installation verification script
- Checks:
  - PHP version (7.0+)
  - PDO extension
  - PDO MySQL driver
  - Database connection
  - Database tables exist
  - Session support
- Should be deleted after setup for security

## Database Schema

### Tables

**users**
- id (Primary Key)
- username (Unique)
- email (Unique)
- password (Hashed)
- created_at

**projects**
- id (Primary Key)
- user_id (Foreign Key → users.id)
- title
- description
- event_date
- event_type (party|birthday)
- created_at

**invitations**
- id (Primary Key)
- project_id (Foreign Key → projects.id)
- invitation_code (Unique, format: XXXX-XXXX-XXXX)
- invitee_name
- invitee_email
- status (pending|accepted|declined)
- created_at
- accepted_at

## Security Features

1. **Password Security**
   - Passwords hashed with password_hash() (bcrypt)
   - Minimum 8 characters required
   - Verified with password_verify()

2. **SQL Injection Prevention**
   - All queries use PDO prepared statements
   - Parameter binding for all user inputs

3. **Session Security**
   - Session-based authentication
   - Session checked on protected pages
   - Proper session cleanup on logout

4. **Invitation Security**
   - Invitation codes in format: XXXX-XXXX-XXXX (uppercase alphanumeric)
   - Generated with cryptographically secure random_int()
   - Unique constraint in database

5. **XSS Prevention**
   - All output escaped with htmlspecialchars()
   - User input sanitized before display

6. **Error Handling**
   - Generic error messages to users
   - Detailed errors logged server-side
   - No database structure exposed

## File Structure

```
/
├── config.php              # Configuration & utilities
├── config.sample.php       # Sample configuration
├── database.sql            # Database schema
├── register.php            # User registration
├── login.php              # User login
├── logout.php             # Logout handler
├── index.php              # Dashboard
├── create_project.php     # Create project form
├── view_project.php       # Project details
├── create_invitation.php  # Create invitation form
├── accept_invitation.php  # Public invitation page
├── setup_check.php        # Setup verification
├── style.css              # Application styles
├── .gitignore             # Git ignore rules
└── README.md              # Documentation
```

## Configuration

Key settings in `config.php`:

```php
DB_HOST     - Database host (default: localhost)
DB_USER     - Database username
DB_PASS     - Database password
DB_NAME     - Database name (default: party_manager)
SITE_URL    - Full site URL for invitation links
```

## Installation Steps

1. Import `database.sql` into MySQL
2. Copy `config.sample.php` to `config.php`
3. Update database credentials in `config.php`
4. Update `SITE_URL` in `config.php`
5. Visit `setup_check.php` to verify installation
6. Delete `setup_check.php` after verification
7. Register first user at `register.php`
8. Start creating projects!
