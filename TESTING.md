# Testing Guide

This document provides a comprehensive testing checklist for the Party/Birthday Manager application.

## Prerequisites

- Web server (Apache/Nginx) running
- PHP 7.0+ installed
- MySQL database created and schema imported
- Application configured (`config.php` set up correctly)

## Testing Checklist

### 1. Setup Verification

- [ ] Navigate to `setup_check.php`
- [ ] Verify PHP version is 7.0 or higher
- [ ] Verify PDO extension is loaded
- [ ] Verify PDO MySQL driver is loaded
- [ ] Verify database connection is successful
- [ ] Verify all three tables exist (users, projects, invitations)
- [ ] Verify session support is working

### 2. User Registration

**Test Case 2.1: Successful Registration**
- [ ] Navigate to `register.php`
- [ ] Enter valid username (e.g., "testuser")
- [ ] Enter password (minimum 8 characters)
- [ ] Enter matching confirm password
- [ ] Click "Register"
- [ ] Verify success message appears
- [ ] Verify user can see login link

**Test Case 2.2: Duplicate Username**
- [ ] Try to register with existing username
- [ ] Verify error message about duplicate username

**Test Case 2.3: Password Validation**
- [ ] Try password less than 8 characters
- [ ] Verify error message about minimum length
- [ ] Try mismatched passwords
- [ ] Verify error message about passwords not matching

**Test Case 2.4: Empty Fields**
- [ ] Try to submit with empty username
- [ ] Verify error message
- [ ] Try to submit with empty password
- [ ] Verify error message

### 3. User Login

**Test Case 3.1: Successful Login**
- [ ] Navigate to `login.php`
- [ ] Enter valid username
- [ ] Enter correct password
- [ ] Click "Login"
- [ ] Verify redirect to dashboard (`index.php`)
- [ ] Verify username displayed in navigation

**Test Case 3.2: Invalid Credentials**
- [ ] Try to login with wrong password
- [ ] Verify error message
- [ ] Try to login with non-existent username
- [ ] Verify error message

**Test Case 3.3: Empty Fields**
- [ ] Try to submit with empty username
- [ ] Verify error message
- [ ] Try to submit with empty password
- [ ] Verify error message

### 4. Dashboard (index.php)

**Test Case 4.1: Empty Dashboard**
- [ ] Login as new user with no projects
- [ ] Verify empty state message appears
- [ ] Verify "Create Your First Project" button is visible
- [ ] Verify navigation bar is present with correct links

**Test Case 4.2: Dashboard with Projects**
- [ ] After creating projects, return to dashboard
- [ ] Verify all projects are listed
- [ ] Verify each project card shows:
  - [ ] Event type badge
  - [ ] Title
  - [ ] Description
  - [ ] Event date
  - [ ] "View Details" button
  - [ ] "Create Invitation" button

**Test Case 4.3: Authentication Check**
- [ ] Logout
- [ ] Try to access `index.php` directly
- [ ] Verify redirect to login page

### 5. Project Creation

**Test Case 5.1: Successful Project Creation**
- [ ] Click "Create Project" in navigation
- [ ] Enter project title (e.g., "John's Birthday Party")
- [ ] Select event type (Party or Birthday)
- [ ] Enter future event date
- [ ] Enter description (optional)
- [ ] Click "Create Project"
- [ ] Verify redirect to project detail page
- [ ] Verify all entered information is displayed correctly

**Test Case 5.2: Required Fields Validation**
- [ ] Try to submit without title
- [ ] Verify error message
- [ ] Try to submit without event type
- [ ] Verify error message
- [ ] Try to submit without event date
- [ ] Verify error message

**Test Case 5.3: Optional Description**
- [ ] Create project without description
- [ ] Verify project is created successfully

### 6. Project Details View

**Test Case 6.1: View Project Details**
- [ ] Click "View Details" on a project
- [ ] Verify project title is displayed
- [ ] Verify event type badge is shown
- [ ] Verify event date is formatted correctly
- [ ] Verify description is shown
- [ ] Verify created date is shown
- [ ] Verify "Create New Invitation" button exists
- [ ] Verify "Back to Projects" button exists

**Test Case 6.2: Empty Invitations List**
- [ ] View project with no invitations
- [ ] Verify "No invitations created yet" message

**Test Case 6.3: Authorization Check**
- [ ] Try to access another user's project (if possible)
- [ ] Verify redirect or access denied

### 7. Invitation Creation

**Test Case 7.1: Create Invitation with Name**
- [ ] Click "Create New Invitation" on project detail page
- [ ] Enter invitee name (e.g., "Jane Doe")
- [ ] Click "Create Invitation"
- [ ] Verify redirect back to project details
- [ ] Verify new invitation appears in list
- [ ] Verify invitation link is displayed

**Test Case 7.2: Empty Invitation**
- [ ] Try to create invitation without name
- [ ] Verify error message

**Test Case 7.3: Invitation Link Format**
- [ ] Verify invitation link contains:
  - [ ] Correct domain (SITE_URL)
  - [ ] `accept_invitation.php` page
  - [ ] `project` parameter with correct ID
  - [ ] `code` parameter with 64-character hex string

**Test Case 7.4: Copy Link Button**
- [ ] Click "Copy" button next to invitation link
- [ ] Verify button text changes to "Copied!"
- [ ] Paste into notepad/browser
- [ ] Verify correct link was copied

### 8. Invitation Acceptance (Public Page)

**Test Case 8.1: View Invitation as Guest**
- [ ] Copy invitation link
- [ ] Open in new browser or incognito mode
- [ ] Navigate to invitation link
- [ ] Verify event details are displayed:
  - [ ] Event type badge
  - [ ] Project title
  - [ ] Host username
  - [ ] Event date
  - [ ] Description
  - [ ] Invitee name (if provided)
- [ ] Verify status shows "Pending"
- [ ] Verify "Accept Invitation" button exists
- [ ] Verify "Decline" button exists

**Test Case 8.2: Accept Invitation**
- [ ] Click "Accept Invitation" button
- [ ] Verify success message appears
- [ ] Verify status changes to "Accepted"
- [ ] Verify buttons are no longer shown
- [ ] Return to project owner's view
- [ ] Verify invitation status updated to "Accepted"
- [ ] Verify accepted date is shown

**Test Case 8.3: Decline Invitation**
- [ ] Create new invitation
- [ ] Open invitation link as guest
- [ ] Click "Decline" button
- [ ] Verify status changes to "Declined"
- [ ] Return to project owner's view
- [ ] Verify invitation status updated to "Declined"

**Test Case 8.4: Invalid Invitation Link**
- [ ] Try accessing with invalid project ID
- [ ] Verify error message
- [ ] Try accessing with invalid code
- [ ] Verify error message
- [ ] Try accessing with no parameters
- [ ] Verify error message

**Test Case 8.5: Already Responded Invitation**
- [ ] Access previously accepted invitation link
- [ ] Verify current status is displayed
- [ ] Verify accept/decline buttons are hidden

### 9. Invitation Tracking

**Test Case 9.1: Multiple Invitations**
- [ ] Create 3+ invitations for same project
- [ ] Have some accepted, some declined, some pending
- [ ] Verify all invitations show in table
- [ ] Verify status badges have correct colors:
  - [ ] Pending: yellow/warning
  - [ ] Accepted: green/success
  - [ ] Declined: red/danger
- [ ] Verify each has unique invitation code
- [ ] Verify created dates are shown

### 10. Logout

**Test Case 10.1: Successful Logout**
- [ ] Click "Logout" in navigation
- [ ] Verify redirect to login page
- [ ] Try to access `index.php` directly
- [ ] Verify redirect back to login (session destroyed)

### 11. Navigation and UI

**Test Case 11.1: Navigation Bar**
- [ ] Verify logo/title is present
- [ ] Verify "My Projects" link works
- [ ] Verify "Create Project" link works
- [ ] Verify username is displayed
- [ ] Verify "Logout" link works
- [ ] Verify active page is highlighted

**Test Case 11.2: Responsive Design**
- [ ] Resize browser to mobile width
- [ ] Verify layout adapts appropriately
- [ ] Verify all functionality still works

**Test Case 11.3: Styling**
- [ ] Verify consistent color scheme (purple/blue theme)
- [ ] Verify buttons have hover effects
- [ ] Verify form inputs have focus effects
- [ ] Verify cards have hover effects on dashboard

### 12. Security Testing

**Test Case 12.1: SQL Injection Prevention**
- [ ] Try entering SQL code in username: `admin' OR '1'='1`
- [ ] Verify it's treated as literal text, not executed
- [ ] Try in other form fields
- [ ] Verify no SQL errors appear

**Test Case 12.2: XSS Prevention**
- [ ] Try entering `<script>alert('XSS')</script>` in project title
- [ ] Verify it's displayed as text, not executed
- [ ] Try in other fields (description, name, etc.)

**Test Case 12.3: Session Security**
- [ ] Login
- [ ] Copy session cookie
- [ ] Logout
- [ ] Try to use old session cookie
- [ ] Verify access is denied

**Test Case 12.4: Direct Access Protection**
- [ ] Without login, try to access:
  - [ ] `index.php` - should redirect to login
  - [ ] `create_project.php` - should redirect to login
  - [ ] `view_project.php?id=1` - should redirect to login
  - [ ] `create_invitation.php?project_id=1` - should redirect to login
- [ ] `accept_invitation.php` should work without login

**Test Case 12.5: Password Security**
- [ ] Register new user
- [ ] Check database directly
- [ ] Verify password is hashed (not plain text)
- [ ] Verify hash starts with `$2y$` (bcrypt)

### 13. Database Integrity

**Test Case 13.1: Foreign Key Constraints**
- [ ] Create user with projects and invitations
- [ ] Delete user from database
- [ ] Verify projects are also deleted (CASCADE)
- [ ] Verify invitations are also deleted (CASCADE)

**Test Case 13.2: Unique Constraints**
- [ ] Try to register with duplicate username
- [ ] Verify error is handled gracefully
- [ ] Verify no database errors shown to user

## Test Results Template

Create a copy of this template to record test results:

```
Test Date: ____________
Tester: ____________
Environment: ____________

| Test Case | Pass/Fail | Notes |
|-----------|-----------|-------|
| 2.1       |           |       |
| 2.2       |           |       |
| ...       |           |       |

Overall Result: PASS / FAIL
Issues Found: ____________
```

## Common Issues and Solutions

**Issue**: Database connection fails
- **Solution**: Check database credentials in `config.php`

**Issue**: Session errors
- **Solution**: Ensure web server has write permissions for session directory

**Issue**: Invitation links don't work
- **Solution**: Verify `SITE_URL` is correctly set in `config.php`

**Issue**: CSS not loading
- **Solution**: Verify `style.css` is in the same directory as PHP files

**Issue**: Can't register/login
- **Solution**: Verify database tables were created correctly
