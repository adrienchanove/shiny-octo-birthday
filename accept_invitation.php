<?php
require_once 'config.php';

// Handle both GET (link) and POST (manual entry form)
$project_id = isset($_GET['project']) ? intval($_GET['project']) : 0;
$invitation_code = isset($_GET['code']) ? $_GET['code'] : '';

// Check if manual code entry was submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['manual_code'])) {
    $invitation_code = strtoupper(trim($_POST['manual_code']));
    
    // Validate code format before processing (format: XXXX-XXXX-XXXX)
    if (preg_match('/^[A-Z0-9]{4}-[A-Z0-9]{4}-[A-Z0-9]{4}$/', $invitation_code)) {
        // Always look up project_id from database for security
        // This prevents manipulation of the hidden project_id field
        $conn = getDBConnection();
        $stmt = $conn->prepare("SELECT project_id FROM invitations WHERE invitation_code = ?");
        $stmt->execute([$invitation_code]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($result) {
            header("Location: accept_invitation.php?project=" . $result['project_id'] . "&code=" . urlencode($invitation_code));
            exit;
        } else {
            // Code not found, continue with error display
            $project_id = 0;
        }
    } else {
        // Invalid code format, continue with error display
        $project_id = 0;
        $invitation_code = '';
    }
}

$error = '';
$success = '';

if (!$project_id || !$invitation_code) {
    $error = 'Invalid invitation link.';
} else {
    $conn = getDBConnection();
    
    // Get invitation details
    $stmt = $conn->prepare("
        SELECT i.*, p.title, p.description, p.event_date, p.event_time, p.event_end_date, p.event_end_time, p.event_location, p.event_type, p.show_guest_list, u.username as host_username
        FROM invitations i
        JOIN projects p ON i.project_id = p.id
        JOIN users u ON p.user_id = u.id
        WHERE i.project_id = ? AND i.invitation_code = ?
    ");
    $stmt->execute([$project_id, $invitation_code]);
    $invitation = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$invitation) {
        $error = 'Invitation not found or invalid.';
    } else {
        // Handle acceptance
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $action = $_POST['action'];
            $guest_message = isset($_POST['guest_message']) ? trim($_POST['guest_message']) : null;
            
            if ($action === 'accept') {
                try {
                    $stmt = $conn->prepare("UPDATE invitations SET status = 'accepted', accepted_at = NOW(), response_updated_at = NOW(), guest_message = ? WHERE id = ?");
                    $stmt->execute([$guest_message, $invitation['id']]);
                    $success = 'You have accepted the invitation!';
                    
                    // Refresh invitation data
                    $stmt = $conn->prepare("
                        SELECT i.*, p.title, p.description, p.event_date, p.event_time, p.event_end_date, p.event_end_time, p.event_location, p.event_type, p.show_guest_list, u.username as host_username
                        FROM invitations i
                        JOIN projects p ON i.project_id = p.id
                        JOIN users u ON p.user_id = u.id
                        WHERE i.project_id = ? AND i.invitation_code = ?
                    ");
                    $stmt->execute([$project_id, $invitation_code]);
                    $invitation = $stmt->fetch(PDO::FETCH_ASSOC);
                } catch(PDOException $e) {
                    $error = 'Failed to accept invitation. Please try again.';
                }
            } elseif ($action === 'decline') {
                try {
                    $stmt = $conn->prepare("UPDATE invitations SET status = 'declined', response_updated_at = NOW(), guest_message = ? WHERE id = ?");
                    $stmt->execute([$guest_message, $invitation['id']]);
                    $success = 'You have declined the invitation.';
                    
                    // Refresh invitation data
                    $stmt = $conn->prepare("
                        SELECT i.*, p.title, p.description, p.event_date, p.event_time, p.event_end_date, p.event_end_time, p.event_location, p.event_type, p.show_guest_list, u.username as host_username
                        FROM invitations i
                        JOIN projects p ON i.project_id = p.id
                        JOIN users u ON p.user_id = u.id
                        WHERE i.project_id = ? AND i.invitation_code = ?
                    ");
                    $stmt->execute([$project_id, $invitation_code]);
                    $invitation = $stmt->fetch(PDO::FETCH_ASSOC);
                } catch(PDOException $e) {
                    $error = 'Failed to decline invitation. Please try again.';
                }
            } elseif ($action === 'uncertain') {
                try {
                    $stmt = $conn->prepare("UPDATE invitations SET status = 'uncertain', response_updated_at = NOW(), guest_message = ? WHERE id = ?");
                    $stmt->execute([$guest_message, $invitation['id']]);
                    $success = 'You have marked your response as uncertain.';
                    
                    // Refresh invitation data
                    $stmt = $conn->prepare("
                        SELECT i.*, p.title, p.description, p.event_date, p.event_time, p.event_end_date, p.event_end_time, p.event_location, p.event_type, p.show_guest_list, u.username as host_username
                        FROM invitations i
                        JOIN projects p ON i.project_id = p.id
                        JOIN users u ON p.user_id = u.id
                        WHERE i.project_id = ? AND i.invitation_code = ?
                    ");
                    $stmt->execute([$project_id, $invitation_code]);
                    $invitation = $stmt->fetch(PDO::FETCH_ASSOC);
                } catch(PDOException $e) {
                    $error = 'Failed to update response. Please try again.';
                }
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Invitation - Party Manager</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="container">
        <div class="invitation-box">
            <h1>You're Invited!</h1>
            
            <?php if ($error): ?>
                <div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div>
                
                <!-- Manual code entry form when there's an error -->
                <div class="form-container" style="margin-top: 20px; max-width: 100%; padding: 20px;">
                    <h3 style="color: #667eea; margin-bottom: 15px; font-size: 18px;">Enter Your Invitation Code</h3>
                    <p style="color: #666; margin-bottom: 20px; font-size: 14px;">
                        If you have an invitation code, please enter it below:
                    </p>
                    <form method="POST" action="">
                        <div class="form-group">
                            <label for="manual_code">Invitation Code (Format: XXXX-XXXX-XXXX)</label>
                            <input 
                                type="text" 
                                id="manual_code" 
                                name="manual_code" 
                                placeholder="e.g., AB3X-9KL2-P7Q4" 
                                required
                                pattern="[A-Z0-9]{4}-[A-Z0-9]{4}-[A-Z0-9]{4}"
                                title="Please enter code in format: XXXX-XXXX-XXXX (uppercase letters and numbers)"
                                style="text-transform: uppercase;"
                            >
                        </div>
                        <button type="submit" class="btn btn-primary">Submit Code</button>
                    </form>
                </div>
            <?php elseif ($success): ?>
                <div class="alert alert-success"><?php echo htmlspecialchars($success); ?></div>
            <?php endif; ?>
            
            <?php if (isset($invitation)): ?>
                <div class="invitation-details">
                    <div class="event-type-badge <?php echo htmlspecialchars($invitation['event_type']); ?>">
                        <?php echo ucfirst(htmlspecialchars($invitation['event_type'])); ?>
                    </div>
                    
                    <h2><?php echo htmlspecialchars($invitation['title']); ?></h2>
                    
                    <div class="invitation-info">
                        <p><strong>Host:</strong> <?php echo htmlspecialchars($invitation['host_username']); ?></p>
                        <p><strong>Date:</strong> <?php echo date('F j, Y', strtotime($invitation['event_date'])); ?></p>
                        <?php if (!empty($invitation['event_time'])): ?>
                            <p><strong>Time:</strong> <?php echo date('g:i A', strtotime($invitation['event_time'])); ?></p>
                        <?php endif; ?>
                        <?php if (!empty($invitation['event_end_date'])): ?>
                            <p><strong>End Date:</strong> <?php echo date('F j, Y', strtotime($invitation['event_end_date'])); ?></p>
                        <?php endif; ?>
                        <?php if (!empty($invitation['event_end_time'])): ?>
                            <p><strong>End Time:</strong> <?php echo date('g:i A', strtotime($invitation['event_end_time'])); ?></p>
                        <?php endif; ?>
                        <?php if (!empty($invitation['event_location'])): ?>
                            <p><strong>Location:</strong> <?php echo htmlspecialchars($invitation['event_location']); ?></p>
                        <?php endif; ?>
                        <?php if (!empty($invitation['description'])): ?>
                            <p><strong>Description:</strong> <?php echo htmlspecialchars($invitation['description']); ?></p>
                        <?php endif; ?>
                        <?php if (!empty($invitation['invitee_name'])): ?>
                            <p><strong>Invited:</strong> <?php echo htmlspecialchars($invitation['invitee_name']); ?></p>
                        <?php endif; ?>
                    </div>
                    
                    <div class="invitation-status">
                        <p><strong>Status:</strong> 
                            <span class="status <?php echo htmlspecialchars($invitation['status']); ?>">
                                <?php echo ucfirst(htmlspecialchars($invitation['status'])); ?>
                            </span>
                        </p>
                        <?php if (!empty($invitation['guest_message'])): ?>
                            <p><strong>Your Message:</strong> <?php echo nl2br(htmlspecialchars($invitation['guest_message'])); ?></p>
                        <?php endif; ?>
                    </div>
                    
                    <!-- Guest list if enabled by host and guest has accepted -->
                    <?php if ($invitation['show_guest_list'] && $invitation['status'] === 'accepted'): ?>
                        <div class="guest-list-section">
                            <h3>Other Guests Attending</h3>
                            <?php
                                $stmt = $conn->prepare("
                                    SELECT invitee_name 
                                    FROM invitations 
                                    WHERE project_id = ? AND status = 'accepted' AND id != ?
                                    ORDER BY invitee_name
                                ");
                                $stmt->execute([$project_id, $invitation['id']]);
                                $accepted_guests = $stmt->fetchAll(PDO::FETCH_ASSOC);
                                
                                if (empty($accepted_guests)):
                            ?>
                                <p class="empty-message">No other guests have accepted yet.</p>
                            <?php else: ?>
                                <ul class="guest-list">
                                    <?php foreach ($accepted_guests as $guest): ?>
                                        <li><?php echo htmlspecialchars($guest['invitee_name'] ?: 'Guest'); ?></li>
                                    <?php endforeach; ?>
                                </ul>
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>
                    
                    <form method="POST" action="">
                        <div class="form-group">
                            <label for="guest_message">Message to Host (optional):</label>
                            <textarea id="guest_message" name="guest_message" rows="3" placeholder="Leave a message for the host..."><?php echo htmlspecialchars($invitation['guest_message'] ?? ''); ?></textarea>
                        </div>
                        
                        <div class="invitation-actions">
                            <button type="submit" name="action" value="accept" class="btn btn-success">Accept</button>
                            <button type="submit" name="action" value="uncertain" class="btn btn-warning">Maybe</button>
                            <button type="submit" name="action" value="decline" class="btn btn-danger">Decline</button>
                        </div>
                        
                        <?php if ($invitation['status'] !== 'pending'): ?>
                            <p class="text-center" style="margin-top: 15px; color: #666; font-size: 14px;">
                                You can change your response at any time by clicking a different button above.
                            </p>
                        <?php endif; ?>
                    </form>
                </div>
            <?php endif; ?>
            
            <?php if (isLoggedIn()): ?>
                <p class="text-center">
                    <a href="index.php">Go to My Projects</a>
                </p>
            <?php else: ?>
                <p class="text-center">
                    Want to manage your own events? <a href="register.php">Create an account</a>
                </p>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>
