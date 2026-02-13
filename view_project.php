<?php
require_once 'config.php';
requireLogin();

$project_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if (!$project_id) {
    header('Location: index.php');
    exit();
}

$conn = getDBConnection();

// Get project details
$stmt = $conn->prepare("SELECT * FROM projects WHERE id = ? AND user_id = ?");
$stmt->execute([$project_id, getCurrentUserId()]);
$project = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$project) {
    header('Location: index.php');
    exit();
}

// Get invitations for this project
$stmt = $conn->prepare("SELECT * FROM invitations WHERE project_id = ? ORDER BY created_at DESC");
$stmt->execute([$project_id]);
$invitations = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($project['title']); ?> - Party Manager</title>
    
    <!-- Open Graph Meta Tags -->
    <meta property="og:title" content="<?php echo htmlspecialchars($project['title']); ?>">
    <meta property="og:description" content="<?php echo htmlspecialchars($project['description']); ?>">
    <meta property="og:type" content="event">
    <meta property="og:url" content="<?php echo SITE_URL; ?>/view_project.php?id=<?php echo $project_id; ?>">
    <meta property="og:site_name" content="Party Manager">
    
    <!-- Event Specific Open Graph Tags -->
    <meta property="event:start_time" content="<?php echo date('c', strtotime($project['event_date'] . ' ' . ($project['event_time'] ?: '00:00:00'))); ?>">
    <?php if (!empty($project['event_end_date'])): ?>
    <meta property="event:end_time" content="<?php echo date('c', strtotime($project['event_end_date'] . ' ' . ($project['event_end_time'] ?: '23:59:59'))); ?>">
    <?php endif; ?>
    <?php if (!empty($project['event_location'])): ?>
    <meta property="event:location" content="<?php echo htmlspecialchars($project['event_location']); ?>">
    <?php endif; ?>
    
    <!-- Twitter Card Meta Tags -->
    <meta name="twitter:card" content="summary">
    <meta name="twitter:title" content="<?php echo htmlspecialchars($project['title']); ?>">
    <meta name="twitter:description" content="<?php echo htmlspecialchars($project['description']); ?>">
    
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <nav class="navbar">
        <div class="container">
            <h1 class="logo">Party Manager</h1>
            <div class="nav-links">
                <a href="index.php">My Projects</a>
                <a href="create_project.php">Create Project</a>
                <span class="user-info">Welcome, <?php echo htmlspecialchars($_SESSION['username']); ?></span>
                <a href="logout.php">Logout</a>
            </div>
        </div>
    </nav>
    
    <div class="container">
        <div class="project-detail">
            <div class="project-header">
                <h2><?php echo htmlspecialchars($project['title']); ?></h2>
                <span class="badge <?php echo htmlspecialchars($project['event_type']); ?>">
                    <?php echo ucfirst(htmlspecialchars($project['event_type'])); ?>
                </span>
            </div>
            
            <div class="project-info">
                <p><strong>Event Date:</strong> <?php echo date('F j, Y', strtotime($project['event_date'])); ?></p>
                <?php if (!empty($project['event_time'])): ?>
                    <p><strong>Event Time:</strong> <?php echo date('g:i A', strtotime($project['event_time'])); ?></p>
                <?php endif; ?>
                <?php if (!empty($project['event_end_date'])): ?>
                    <p><strong>Event End Date:</strong> <?php echo date('F j, Y', strtotime($project['event_end_date'])); ?></p>
                <?php endif; ?>
                <?php if (!empty($project['event_end_time'])): ?>
                    <p><strong>Event End Time:</strong> <?php echo date('g:i A', strtotime($project['event_end_time'])); ?></p>
                <?php endif; ?>
                <?php if (!empty($project['event_location'])): ?>
                    <p><strong>Location:</strong> <?php echo htmlspecialchars($project['event_location']); ?></p>
                <?php endif; ?>
                <p><strong>Description:</strong> <?php echo htmlspecialchars($project['description']); ?></p>
                <p><strong>Guest List Visible to Accepted Guests:</strong> <?php echo $project['show_guest_list'] ? 'Yes' : 'No'; ?></p>
                <p><strong>Created:</strong> <?php echo date('F j, Y', strtotime($project['created_at'])); ?></p>
            </div>
            
            <div class="section">
                <h3>Invitations</h3>
                <a href="create_invitation.php?project_id=<?php echo $project_id; ?>" class="btn btn-primary">Create New Invitation</a>
                
                <?php if (empty($invitations)): ?>
                    <p class="empty-message">No invitations created yet.</p>
                <?php else: ?>
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Invitee Name</th>
                                <th>Status</th>
                                <th>Message</th>
                                <th>Invitation Link</th>
                                <th>Created</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($invitations as $invitation): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($invitation['invitee_name'] ?: 'N/A'); ?></td>
                                    <td>
                                        <span class="status <?php echo htmlspecialchars($invitation['status']); ?>">
                                            <?php echo ucfirst(htmlspecialchars($invitation['status'])); ?>
                                        </span>
                                    </td>
                                    <td>
                                        <?php if (!empty($invitation['guest_message'])): ?>
                                            <div class="guest-message-preview" title="<?php echo htmlspecialchars($invitation['guest_message']); ?>">
                                                <?php 
                                                    $msg = htmlspecialchars($invitation['guest_message']);
                                                    echo mb_strlen($msg) > 50 ? mb_substr($msg, 0, 50) . '...' : $msg;
                                                ?>
                                            </div>
                                        <?php else: ?>
                                            <span style="color: #999;">-</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <input type="text" class="invitation-link" readonly 
                                               value="<?php echo SITE_URL; ?>/accept_invitation.php?project=<?php echo $project_id; ?>&code=<?php echo htmlspecialchars($invitation['invitation_code']); ?>">
                                        <button class="btn btn-small" onclick="copyLink(this)">Copy</button>
                                    </td>
                                    <td><?php echo date('M j, Y', strtotime($invitation['created_at'])); ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php endif; ?>
            </div>
            
            <div class="project-actions">
                <a href="index.php" class="btn btn-secondary">Back to Projects</a>
            </div>
        </div>
    </div>
    
    <script>
    function copyLink(button) {
        const input = button.previousElementSibling;
        const text = input.value;
        
        // Use modern Clipboard API
        if (navigator.clipboard && navigator.clipboard.writeText) {
            navigator.clipboard.writeText(text).then(() => {
                const originalText = button.textContent;
                button.textContent = 'Copied!';
                setTimeout(() => {
                    button.textContent = originalText;
                }, 2000);
            }).catch(err => {
                console.error('Failed to copy: ', err);
                // Fallback for older browsers (intentionally kept for legacy compatibility)
                input.select();
                try {
                    document.execCommand('copy');
                    const originalText = button.textContent;
                    button.textContent = 'Copied!';
                    setTimeout(() => {
                        button.textContent = originalText;
                    }, 2000);
                } catch (e) {
                    alert('Failed to copy link');
                }
            });
        } else {
            // Fallback for older browsers (intentionally kept for legacy compatibility)
            input.select();
            try {
                document.execCommand('copy');
                const originalText = button.textContent;
                button.textContent = 'Copied!';
                setTimeout(() => {
                    button.textContent = originalText;
                }, 2000);
            } catch (e) {
                alert('Failed to copy link');
            }
        }
    }
    </script>
</body>
</html>
