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
    
    if (empty($invitee_name)) {
        $error = 'Veuillez fournir un nom.';
    } else {
        try {
            $invitation_code = generateInvitationCode();
            $stmt = $conn->prepare("INSERT INTO invitations (project_id, invitation_code, invitee_name) VALUES (?, ?, ?)");
            $stmt->execute([$project_id, $invitation_code, $invitee_name]);
            
            header('Location: view_project.php?id=' . $project_id);
            exit();
        } catch(PDOException $e) {
            $error = 'Échec de la création de l\'invitation. Veuillez réessayer.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Créer une Invitation - Gestionnaire de Fêtes</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <nav class="navbar">
        <div class="container">
            <h1 class="logo">Gestionnaire de Fêtes</h1>
            <div class="nav-links">
                <a href="index.php">Mes Projets</a>
                <a href="create_project.php">Créer un Projet</a>
                <span class="user-info">Bienvenue, <?php echo htmlspecialchars($_SESSION['username']); ?></span>
                <a href="logout.php">Déconnexion</a>
            </div>
        </div>
    </nav>
    
    <div class="container">
        <div class="form-container">
            <h2>Créer une Invitation</h2>
            <p class="subtitle">Projet : <strong><?php echo htmlspecialchars($project['title']); ?></strong></p>
            
            <?php if ($error): ?>
                <div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div>
            <?php endif; ?>
            
            <form method="POST" action="">
                <div class="form-group">
                    <label for="invitee_name">Nom de l'invité :</label>
                    <input type="text" id="invitee_name" name="invitee_name" placeholder="Entrez le nom de la personne" required>
                </div>
                
                <p class="note">Note : Un lien d'invitation unique sera généré. Vous pourrez partager ce lien avec l'invité.</p>
                
                <div class="form-actions">
                    <button type="submit" class="btn btn-primary">Créer l'Invitation</button>
                    <a href="view_project.php?id=<?php echo $project_id; ?>" class="btn btn-secondary">Annuler</a>
                </div>
            </form>
        </div>
    </div>
</body>
</html>
