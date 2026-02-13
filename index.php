<?php
require_once 'config.php';
requireLogin();

$conn = getDBConnection();

// Get user's projects
$stmt = $conn->prepare("SELECT * FROM projects WHERE user_id = ? ORDER BY event_date DESC, created_at DESC");
$stmt->execute([getCurrentUserId()]);
$projects = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Projects - Party Manager</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <nav class="navbar">
        <div class="container">
            <h1 class="logo">Party Manager</h1>
            <div class="nav-links">
                <a href="index.php" class="active">My Projects</a>
                <a href="create_project.php">Create Project</a>
                <span class="user-info">Welcome, <?php echo htmlspecialchars($_SESSION['username']); ?></span>
                <a href="logout.php">Logout</a>
            </div>
        </div>
    </nav>
    
    <div class="container">
        <h2>My Projects</h2>
        
        <?php if (empty($projects)): ?>
            <div class="empty-state">
                <p>You don't have any projects yet.</p>
                <a href="create_project.php" class="btn btn-primary">Create Your First Project</a>
            </div>
        <?php else: ?>
            <div class="projects-grid">
                <?php foreach ($projects as $project): ?>
                    <div class="project-card">
                        <div class="project-type <?php echo htmlspecialchars($project['event_type']); ?>">
                            <?php echo ucfirst(htmlspecialchars($project['event_type'])); ?>
                        </div>
                        <h3><?php echo htmlspecialchars($project['title']); ?></h3>
                        <p class="project-description"><?php echo htmlspecialchars($project['description']); ?></p>
                        <p class="project-date">
                            <strong>Date:</strong> <?php echo date('F j, Y', strtotime($project['event_date'])); ?>
                        </p>
                        <div class="project-actions">
                            <a href="view_project.php?id=<?php echo $project['id']; ?>" class="btn btn-small">View Details</a>
                            <a href="create_invitation.php?project_id=<?php echo $project['id']; ?>" class="btn btn-small btn-success">Create Invitation</a>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</body>
</html>
