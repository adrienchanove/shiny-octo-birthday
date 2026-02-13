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
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mes Projets - Gestionnaire de Fêtes</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <nav class="navbar">
        <div class="container">
            <h1 class="logo">Gestionnaire de Fêtes</h1>
            <div class="nav-links">
                <a href="index.php" class="active">Mes Projets</a>
                <a href="create_project.php">Créer un Projet</a>
                <span class="user-info">Bienvenue, <?php echo htmlspecialchars($_SESSION['username']); ?></span>
                <a href="logout.php">Déconnexion</a>
            </div>
        </div>
    </nav>
    
    <div class="container">
        <h2>Mes Projets</h2>
        
        <?php if (empty($projects)): ?>
            <div class="empty-state">
                <p>Vous n'avez pas encore de projets.</p>
                <a href="create_project.php" class="btn btn-primary">Créer Votre Premier Projet</a>
            </div>
        <?php else: ?>
            <div class="projects-grid">
                <?php foreach ($projects as $project): ?>
                    <div class="project-card">
                        <div class="project-type <?php echo htmlspecialchars($project['event_type']); ?>">
                            <?php 
                                $type = htmlspecialchars($project['event_type']);
                                echo $type === 'party' ? 'Fête' : 'Anniversaire';
                            ?>
                        </div>
                        <h3><?php echo htmlspecialchars($project['title']); ?></h3>
                        <p class="project-description"><?php echo htmlspecialchars($project['description']); ?></p>
                        <p class="project-date">
                            <strong>Date:</strong> <?php 
                                setlocale(LC_TIME, 'fr_FR.UTF-8', 'fr_FR', 'fra');
                                echo strftime('%e %B %Y', strtotime($project['event_date'])); 
                            ?>
                        </p>
                        <div class="project-actions">
                            <a href="view_project.php?id=<?php echo $project['id']; ?>" class="btn btn-small">Voir Détails</a>
                            <a href="create_invitation.php?project_id=<?php echo $project['id']; ?>" class="btn btn-small btn-success">Créer une Invitation</a>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</body>
</html>
