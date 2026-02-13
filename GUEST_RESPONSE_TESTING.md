# Guest Response Feature - Testing Guide

This document outlines the test cases for the new guest response features.

## New Features Overview

1. **Uncertain Status**: Guests can now respond with "Maybe" in addition to "Accept" and "Decline"
2. **Guest Messages**: Guests can leave optional messages for the host
3. **Response Editing**: Guests can change their response at any time
4. **Guest List Visibility**: Hosts can enable/disable showing the list of accepted guests to other attendees

## Database Migration

Before testing, you need to migrate the database:

### For New Installations:
```bash
php reset_database.php
```

### For Existing Installations:
```bash
php migrate_guest_response.php
```

## Test Cases

### 1. Project Creation with Guest List Setting

**Test 1.1: Create Project with Guest List Enabled**
- [ ] Navigate to Create Project page
- [ ] Fill in all required fields (title, event type, date)
- [ ] Check the "Show guest list to attendees" checkbox
- [ ] Submit the form
- [ ] Verify project is created successfully
- [ ] Open project details
- [ ] Verify "Guest List Visible to Attendees: Yes" is shown

**Test 1.2: Create Project with Guest List Disabled**
- [ ] Navigate to Create Project page
- [ ] Fill in all required fields
- [ ] Leave the "Show guest list to attendees" checkbox unchecked
- [ ] Submit the form
- [ ] Verify project is created successfully
- [ ] Open project details
- [ ] Verify "Guest List Visible to Attendees: No" is shown

### 2. Guest Response - Uncertain Status

**Test 2.1: Accept Invitation with Uncertain Status**
- [ ] Create an invitation and copy the link
- [ ] Open the invitation link (in incognito/different browser)
- [ ] Verify three buttons are visible: "Accept", "Maybe", "Decline"
- [ ] Click the "Maybe" button
- [ ] Verify success message appears
- [ ] Verify status changes to "Uncertain"
- [ ] Return to host's project view
- [ ] Verify invitation status shows "Uncertain" with appropriate styling (yellow/warning color)

### 3. Guest Messages

**Test 3.1: Accept with Message**
- [ ] Open an invitation link as guest
- [ ] Enter a message in the "Message to Host" field (e.g., "Looking forward to it!")
- [ ] Click "Accept"
- [ ] Verify success message appears
- [ ] Return to host's project view
- [ ] Verify the guest's message appears in the "Message" column
- [ ] Hover over truncated messages (>50 chars) to see full text in tooltip

**Test 3.2: Decline with Message**
- [ ] Open an invitation link as guest
- [ ] Enter a message (e.g., "Sorry, I can't make it")
- [ ] Click "Decline"
- [ ] Return to host's project view
- [ ] Verify the message is visible in the Message column

**Test 3.3: Response Without Message**
- [ ] Open an invitation link as guest
- [ ] Leave the message field empty
- [ ] Click "Accept"
- [ ] Return to host's project view
- [ ] Verify the Message column shows "-" for empty messages

### 4. Response Editing

**Test 4.1: Change Response from Accept to Decline**
- [ ] Open an invitation link
- [ ] Click "Accept" (with or without message)
- [ ] Verify status changes to "Accepted"
- [ ] Remain on the same page (don't close)
- [ ] Click "Decline"
- [ ] Verify status changes to "Declined"
- [ ] Verify success message updates
- [ ] Return to host's view
- [ ] Verify status is now "Declined"

**Test 4.2: Change Response from Uncertain to Accept**
- [ ] Open an invitation link
- [ ] Click "Maybe"
- [ ] Verify status is "Uncertain"
- [ ] Click "Accept"
- [ ] Verify status changes to "Accepted"
- [ ] Verify "accepted_at" timestamp is set
- [ ] Return to host's view
- [ ] Verify status shows "Accepted"

**Test 4.3: Edit Message After Initial Response**
- [ ] Open an invitation link
- [ ] Enter message "First message" and click "Accept"
- [ ] Edit the message to "Updated message"
- [ ] Click "Accept" again
- [ ] Return to host's view
- [ ] Verify the message shows "Updated message"

**Test 4.4: Change Response Multiple Times**
- [ ] Open an invitation link
- [ ] Click "Accept" → verify status
- [ ] Click "Maybe" → verify status
- [ ] Click "Decline" → verify status
- [ ] Click "Accept" → verify final status
- [ ] Verify each change updates response_updated_at timestamp
- [ ] Return to host's view
- [ ] Verify final status is "Accepted"

### 5. Guest List Visibility

**Test 5.1: Guest List Shown When Enabled and Accepted**
- [ ] Create a project with "Show guest list" enabled
- [ ] Create 3 invitations (Guest A, Guest B, Guest C)
- [ ] As Guest A, accept the invitation
- [ ] Verify "Other Guests Attending" section appears
- [ ] Verify section initially shows "No other guests have accepted yet"
- [ ] As Guest B, accept the invitation
- [ ] As Guest A, refresh the page
- [ ] Verify Guest B appears in the "Other Guests Attending" list
- [ ] As Guest C, accept the invitation
- [ ] As Guest A, refresh the page
- [ ] Verify both Guest B and Guest C appear in the list

**Test 5.2: Guest List Not Shown When Disabled**
- [ ] Create a project with "Show guest list" disabled (unchecked)
- [ ] Create an invitation
- [ ] Open invitation as guest and accept
- [ ] Verify "Other Guests Attending" section does NOT appear
- [ ] Verify only the regular invitation details are shown

**Test 5.3: Guest List Not Shown for Non-Accepted Status**
- [ ] Create a project with "Show guest list" enabled
- [ ] Create two invitations (Guest A, Guest B)
- [ ] As Guest B, accept the invitation
- [ ] As Guest A, open the invitation
- [ ] While status is still "Pending", verify guest list is NOT shown
- [ ] Click "Maybe" (uncertain)
- [ ] Verify guest list is still NOT shown (only for accepted guests)
- [ ] Click "Decline"
- [ ] Verify guest list is still NOT shown
- [ ] Click "Accept"
- [ ] Verify "Other Guests Attending" section NOW appears with Guest B

### 6. Host View Updates

**Test 6.1: Message Column in Invitations Table**
- [ ] Create a project and multiple invitations
- [ ] Have guests respond with various messages
- [ ] Verify the Message column shows:
  - Full message if 50 characters or less
  - Truncated message with "..." if more than 50 characters
  - Tooltip shows full message on hover
  - "-" for empty messages

**Test 6.2: All Status Types Displayed**
- [ ] Create 4 invitations
- [ ] Have guests respond: one accept, one decline, one uncertain, one pending
- [ ] Verify all four status types are displayed correctly:
  - Pending: yellow/warning badge
  - Accepted: green/success badge
  - Declined: red/danger badge
  - Uncertain: yellow/warning badge (slightly different from pending)

### 7. UI/UX Testing

**Test 7.1: Response Change Instructions**
- [ ] Open an invitation link as guest
- [ ] Accept the invitation
- [ ] Verify helpful text appears: "You can change your response at any time by clicking a different button above"
- [ ] Verify all three buttons remain visible and functional

**Test 7.2: Button Styling**
- [ ] Verify "Accept" button is green
- [ ] Verify "Maybe" button is yellow/warning color
- [ ] Verify "Decline" button is red
- [ ] Verify all buttons have hover effects

**Test 7.3: Guest List Styling**
- [ ] Accept an invitation where guest list is enabled
- [ ] Verify guest list section has:
  - Light background to distinguish it from main content
  - Proper heading styling
  - Individual guest items with left border (green)
  - Clean, readable list format

### 8. Edge Cases and Validation

**Test 8.1: Long Guest Messages**
- [ ] Enter a very long message (500+ characters)
- [ ] Submit response
- [ ] Verify message is stored completely
- [ ] Verify host view truncates display appropriately
- [ ] Verify full message is visible on hover

**Test 8.2: Special Characters in Messages**
- [ ] Enter message with special characters: <script>alert('test')</script>
- [ ] Submit response
- [ ] Verify message is displayed as plain text (XSS prevention)
- [ ] Verify no script execution occurs

**Test 8.3: Empty Project with Guest List Enabled**
- [ ] Create project with guest list enabled
- [ ] Create single invitation
- [ ] Accept as guest
- [ ] Verify "No other guests have accepted yet" message shows

**Test 8.4: Simultaneous Response Changes**
- [ ] Open same invitation in two browser tabs
- [ ] In tab 1, click "Accept"
- [ ] In tab 2, click "Decline"
- [ ] Verify both updates are handled correctly
- [ ] Refresh both tabs
- [ ] Verify both show the latest status (last update wins)

### 9. Database Integrity

**Test 9.1: Timestamp Updates**
- [ ] Accept an invitation
- [ ] Check database: verify `accepted_at` is set
- [ ] Check database: verify `response_updated_at` is set
- [ ] Change response to "Decline"
- [ ] Check database: verify `response_updated_at` is updated
- [ ] Verify `accepted_at` is still set (not cleared)

**Test 9.2: Message Storage**
- [ ] Submit response with message
- [ ] Check database: verify message is stored in `guest_message` field
- [ ] Update message and resubmit
- [ ] Verify message is updated in database

### 10. Backward Compatibility

**Test 10.1: Existing Projects**
- [ ] If testing on existing database with old projects
- [ ] Run migration script
- [ ] Verify old projects now have `show_guest_list = 0` (false)
- [ ] Verify old invitations still work
- [ ] Verify guest list is not shown for old projects

**Test 10.2: Existing Invitations**
- [ ] Existing invitations should have `guest_message = NULL`
- [ ] Verify they still display correctly
- [ ] Verify guests can add messages to existing invitations

## Performance Testing

**Test 11.1: Large Guest List**
- [ ] Create project with 50+ invitations
- [ ] Have 30+ guests accept
- [ ] Open invitation as new guest
- [ ] Verify guest list loads quickly
- [ ] Verify guest list is displayed correctly

## Responsive Design Testing

**Test 12.1: Mobile View - Guest Response**
- [ ] Open invitation on mobile device/narrow browser
- [ ] Verify all three buttons are visible and tappable
- [ ] Verify message textarea is appropriately sized
- [ ] Verify guest list (if shown) is readable

**Test 12.2: Mobile View - Host View**
- [ ] Open project details on mobile
- [ ] Verify invitations table is readable
- [ ] Verify message column doesn't break layout
- [ ] Verify all information is accessible

## Security Testing

**Test 13.1: SQL Injection in Guest Message**
- [ ] Enter SQL injection attempt in message field
- [ ] Example: `'; DROP TABLE invitations; --`
- [ ] Verify it's treated as plain text
- [ ] Verify no database errors occur

**Test 13.2: XSS in Guest Message**
- [ ] Enter XSS attempt in message field
- [ ] Example: `<script>alert('XSS')</script>`
- [ ] Verify it's displayed as plain text
- [ ] Verify no script execution occurs

## Success Criteria

All test cases should pass with:
- ✅ No PHP errors or warnings
- ✅ No database errors
- ✅ No JavaScript console errors
- ✅ Proper data validation and sanitization
- ✅ Responsive design working correctly
- ✅ All features working as specified in requirements

## Rollback Plan

If critical issues are found:
1. Revert code changes using git
2. Database can be left as-is (new fields won't break old code)
3. Or revert database by removing new columns (not recommended if data exists)
