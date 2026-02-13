# shiny-octo-birthday
Party/Birthday Manager

A PHP web-based application to manage parties and birthdays with MySQL database. Users can create projects (party/birthday events) and invite people by generating personal invitation links with unique codes.

## Features

- **User Management**: Registration and login system with secure password hashing
- **Project Management**: Create and manage party/birthday events
- **Invitation System**: Generate unique invitation links for each guest
- **Personal Invitation Links**: Each invitation has a unique code in the URL
- **Invitation Tracking**: Track who accepted or declined invitations
- **Responsive Design**: Works on desktop and mobile devices

## Requirements

- PHP 7.0 or higher
- MySQL 5.6 or higher
- Web server (Apache/Nginx)
- PDO PHP extension

## Installation

1. Clone the repository:
   ```bash
   git clone https://github.com/adrienchanove/shiny-octo-birthday.git
   cd shiny-octo-birthday
   ```

2. Create a MySQL database:
   ```bash
   mysql -u root -p
   CREATE DATABASE party_manager;
   ```

3. Import the database schema:
   ```bash
   mysql -u root -p party_manager < database.sql
   ```
   
   **For existing installations**: If you already have the database set up, run the migration script to add the new time and location fields:
   ```bash
   mysql -u root -p party_manager < migration_add_time_location.sql
   ```

4. Configure the database connection:
   - Open `config.php`
   - Update the database credentials:
     ```php
     define('DB_HOST', 'localhost');
     define('DB_USER', 'your_username');
     define('DB_PASS', 'your_password');
     define('DB_NAME', 'party_manager');
     ```
   - Update the site URL:
     ```php
     define('SITE_URL', 'http://your-domain.com');
     ```

5. Start your web server and navigate to the application URL

## Usage

### Creating an Account
1. Navigate to `register.php`
2. Fill in username, email, and password
3. Click "Register"

### Creating a Project
1. Log in to your account
2. Click "Create Project"
3. Enter project details:
   - Title (required)
   - Event Type (Party or Birthday) (required)
   - Event Date (required)
   - Event Time (optional)
   - Event End Date (optional)
   - Event End Time (optional)
   - Event Location (optional)
   - Description (optional)
4. Click "Create Project"

### Creating Invitations
1. Go to your project details page
2. Click "Create New Invitation"
3. Enter invitee information:
   - Name
   - Email (optional)
4. A unique invitation link will be generated
5. Copy and share the invitation link with your guest

### Invitation Link Format
```
http://your-domain.com/accept_invitation.php?project=1&code=ABCD-EFGH-IJKL
```

Each invitation link contains:
- `project`: The project ID
- `code`: A unique invitation code (format: XXXX-XXXX-XXXX, uppercase alphanumeric)

### Accepting an Invitation
1. Guest clicks on the invitation link
2. They can see event details
3. They can accept or decline the invitation
4. Status is updated and visible to the project owner

## File Structure

```
├── config.php              # Database configuration and utility functions
├── database.sql            # Database schema
├── register.php            # User registration page
├── login.php              # User login page
├── logout.php             # Logout functionality
├── index.php              # Dashboard - list of user's projects
├── create_project.php     # Create new project page
├── view_project.php       # View project details and invitations
├── create_invitation.php  # Create new invitation for a project
├── accept_invitation.php  # Public page for accepting invitations
├── style.css              # Application styles
└── README.md              # This file
```

## Security Features

- Password hashing using PHP's `password_hash()`
- PDO prepared statements to prevent SQL injection
- Session-based authentication
- Unique invitation codes using cryptographically secure random bytes
- Input validation and sanitization

## Database Schema

### Users Table
- `id`: Primary key
- `username`: Unique username
- `email`: Unique email address
- `password`: Hashed password
- `created_at`: Account creation timestamp

### Projects Table
- `id`: Primary key
- `user_id`: Foreign key to users table
- `title`: Project title
- `description`: Project description
- `event_date`: Date of the event
- `event_time`: Time of the event (optional)
- `event_end_date`: End date of the event (optional)
- `event_end_time`: End time of the event (optional)
- `event_location`: Location of the event (optional)
- `event_type`: 'party' or 'birthday'
- `created_at`: Project creation timestamp

### Invitations Table
- `id`: Primary key
- `project_id`: Foreign key to projects table
- `invitation_code`: Unique code (format: XXXX-XXXX-XXXX)
- `invitee_name`: Name of the invitee
- `invitee_email`: Email of the invitee
- `status`: 'pending', 'accepted', or 'declined'
- `created_at`: Invitation creation timestamp
- `accepted_at`: Timestamp when accepted

## License

MIT License
