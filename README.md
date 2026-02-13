# shiny-octo-birthday
Party/Birthday Manager

A PHP web-based application to manage parties and birthdays with MySQL database. Users can create projects (party/birthday events) and invite people by generating personal invitation links with unique codes.

## Features

- **User Management**: Registration and login system with secure password hashing
- **Project Management**: Create and manage party/birthday events
- **Invitation System**: Generate unique invitation links for each guest
- **Personal Invitation Links**: Each invitation has a unique code in the URL
- **Guest Response Options**: Guests can respond with Accept, Decline, or Maybe (Uncertain)
- **Guest Messages**: Guests can leave optional messages for the host
- **Response Editing**: Guests can change their response at any time
- **Guest List Visibility**: Hosts can optionally show the list of accepted guests to other attendees
- **Invitation Tracking**: Track who accepted, declined, or is uncertain about invitations
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

2. Create environment configuration:
   - Copy `.env.example` to `.env`:
     ```bash
     cp .env.example .env
     ```
   - Edit `.env` and update the configuration values:
     ```bash
     # Database Configuration
     DB_HOST=localhost
     DB_USER=your_username
     DB_PASS=your_password
     DB_NAME=party_manager

     # Site Configuration
     SITE_URL=http://your-domain.com
     ```

3. Create a MySQL database:
   ```bash
   mysql -u root -p
   CREATE DATABASE party_manager;
   ```

4. Import the database schema:
   ```bash
   mysql -u root -p party_manager < database.sql
   ```
   
   **Alternative: Use the database reset script** (drops and recreates the database):
   ```bash
   php reset_database.php
   ```
   
   This is useful for resetting the database to a clean state with the latest schema.

5. Start your web server and navigate to the application URL

## Upgrading from Previous Version

If you're upgrading from a previous version that doesn't include the guest response features, you need to migrate your database:

```bash
php migrate_guest_response.php
```

This will add the new fields required for:
- Guest response options (uncertain/maybe)
- Guest messages
- Response editing
- Guest list visibility

The migration is safe and will not affect existing data.

## Usage

### Creating an Account
1. Navigate to `register.php`
2. Fill in username and password
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
   - Show guest list to attendees (optional checkbox) - When enabled, guests who accept can see who else has accepted
4. Click "Create Project"

### Creating Invitations
1. Go to your project details page
2. Click "Create New Invitation"
3. Enter invitee name
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
3. They can choose from three response options:
   - **Accept**: Confirm attendance
   - **Maybe**: Uncertain about attendance
   - **Decline**: Cannot attend
4. Optionally, they can leave a message for the host
5. Status is updated and visible to the project owner
6. Guests can change their response at any time by visiting the same link again

### Guest List Feature
When a host enables "Show guest list to attendees" for a project:
- Guests who **accept** the invitation can see a list of other guests who have accepted
- This helps guests know who else is attending
- Guests with "pending", "maybe", or "declined" status cannot see the guest list
- The host always sees all invitations and responses regardless of this setting

## File Structure

```
├── .env.example            # Example environment configuration
├── config.php              # Configuration loader and utility functions
├── database.sql            # Database schema
├── reset_database.php      # Console command to reset/recreate database
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

## Database Management

### Resetting the Database

To reset the database and recreate it with the latest schema, use the provided console command:

```bash
php reset_database.php
```

This script will:
- Drop the existing database (if it exists)
- Create a new database
- Execute all statements from `database.sql`
- Verify the tables were created successfully

**Warning**: This will delete all existing data! The script will ask for confirmation before proceeding.

You can also manually reset the database:
```bash
mysql -u root -p -e "DROP DATABASE IF EXISTS party_manager; CREATE DATABASE party_manager;"
mysql -u root -p party_manager < database.sql
```


## Security Features

- Password hashing using PHP's `password_hash()`
- PDO prepared statements to prevent SQL injection
- Session-based authentication
- Unique invitation codes using cryptographically secure random bytes
- Input validation and sanitization
- XSS prevention with `htmlspecialchars()` for all user-generated content including guest messages

## Database Schema

### Users Table
- `id`: Primary key
- `username`: Unique username
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
- `show_guest_list`: Boolean, whether to show accepted guests to other attendees
- `created_at`: Project creation timestamp

### Invitations Table
- `id`: Primary key
- `project_id`: Foreign key to projects table
- `invitation_code`: Unique code (format: XXXX-XXXX-XXXX)
- `invitee_name`: Name of the invitee
- `status`: 'pending', 'accepted', 'declined', or 'uncertain'
- `guest_message`: Optional message from the guest to the host
- `created_at`: Invitation creation timestamp
- `accepted_at`: Timestamp when accepted
- `response_updated_at`: Timestamp when response was last updated

## License

MIT License
