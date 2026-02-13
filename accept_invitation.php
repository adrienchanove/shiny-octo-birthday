<?php
require_once 'config.php';

$project_id = isset($_GET['project']) ? intval($_GET['project']) : 0;
$invitation_code = isset($_GET['code']) ? $_GET['code'] : '';
$error = '';
$success = '';

if (!$project_id || !$invitation_code) {
    $error = 'Invalid invitation link.';
} else {
    $conn = getDBConnection();
    
    // Get invitation details
    $stmt = $conn->prepare("
        SELECT i.*, p.title, p.description, p.event_date, p.event_type, u.username as host_username
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
            
            if ($action === 'accept') {
                try {
                    $stmt = $conn->prepare("UPDATE invitations SET status = 'accepted', accepted_at = NOW() WHERE id = ?");
                    $stmt->execute([$invitation['id']]);
                    $success = 'You have accepted the invitation!';
                    
                    // Refresh invitation data
                    $stmt = $conn->prepare("
                        SELECT i.*, p.title, p.description, p.event_date, p.event_type, u.username as host_username
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
                    $stmt = $conn->prepare("UPDATE invitations SET status = 'declined' WHERE id = ?");
                    $stmt->execute([$invitation['id']]);
                    $success = 'You have declined the invitation.';
                    
                    // Refresh invitation data
                    $stmt = $conn->prepare("
                        SELECT i.*, p.title, p.description, p.event_date, p.event_type, u.username as host_username
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
                    </div>
                    
                    <?php if ($invitation['status'] === 'pending'): ?>
                        <form method="POST" action="">
                            <div class="invitation-actions">
                                <button type="submit" name="action" value="accept" class="btn btn-success">Accept Invitation</button>
                                <button type="submit" name="action" value="decline" class="btn btn-danger">Decline</button>
                            </div>
                        </form>
                    <?php endif; ?>
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
