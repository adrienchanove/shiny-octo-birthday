<?php
require_once 'config.php';
requireLogin();

$project_id = isset($_GET['project_id']) ? intval($_GET['project_id']) : 0;
$error = '';
$success = '';

if (!$project_id) {
    header('Location: index.php');
    exit();
}

$conn = getDBConnection();

// Verify project belongs to current user
$stmt = $conn->prepare("SELECT * FROM projects WHERE id = ? AND user_id = ?");
$stmt->execute([$project_id, getCurrentUserId()]);
$project = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$project) {
    header('Location: index.php');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $invitee_name = trim($_POST['invitee_name']);
    $invitee_email = trim($_POST['invitee_email']);
    
    if (empty($invitee_name) && empty($invitee_email)) {
        $error = 'Please provide at least a name or email.';
    } else {
        try {
            $invitation_code = generateInvitationCode();
            $stmt = $conn->prepare("INSERT INTO invitations (project_id, invitation_code, invitee_name, invitee_email) VALUES (?, ?, ?, ?)");
            $stmt->execute([$project_id, $invitation_code, $invitee_name, $invitee_email]);
            
            header('Location: view_project.php?id=' . $project_id);
            exit();
        } catch(PDOException $e) {
            $error = 'Failed to create invitation. Please try again.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Invitation - Party Manager</title>
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
        <div class="form-container">
            <h2>Create Invitation</h2>
            <p class="subtitle">Project: <strong><?php echo htmlspecialchars($project['title']); ?></strong></p>
            
            <?php if ($error): ?>
                <div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div>
            <?php endif; ?>
            
            <form method="POST" action="">
                <div class="form-group">
                    <label for="invitee_name">Invitee Name:</label>
                    <input type="text" id="invitee_name" name="invitee_name" placeholder="Enter person's name">
                </div>
                
                <div class="form-group">
                    <label for="invitee_email">Invitee Email:</label>
                    <input type="email" id="invitee_email" name="invitee_email" placeholder="Enter person's email (optional)">
                </div>
                
                <p class="note">Note: A unique invitation link will be generated. You can share this link with the invitee.</p>
                
                <div class="form-actions">
                    <button type="submit" class="btn btn-primary">Create Invitation</button>
                    <a href="view_project.php?id=<?php echo $project_id; ?>" class="btn btn-secondary">Cancel</a>
                </div>
            </form>
        </div>
    </div>
</body>
</html>
