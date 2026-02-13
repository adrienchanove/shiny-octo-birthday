<?php
require_once 'config.php';
requireLogin();

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['title']);
    $description = trim($_POST['description']);
    $event_date = $_POST['event_date'];
    $event_time = !empty($_POST['event_time']) ? $_POST['event_time'] : null;
    $event_end_date = !empty($_POST['event_end_date']) ? $_POST['event_end_date'] : null;
    $event_end_time = !empty($_POST['event_end_time']) ? $_POST['event_end_time'] : null;
    $event_location = !empty(trim($_POST['event_location'])) ? trim($_POST['event_location']) : null;
    $event_type = $_POST['event_type'];
    $show_guest_list = isset($_POST['show_guest_list']) ? 1 : 0;
    
    if (empty($title) || empty($event_date) || empty($event_type)) {
        $error = 'Le titre, la date et le type d\'événement sont requis.';
    } elseif (!in_array($event_type, ['party', 'birthday'])) {
        $error = 'Type d\'événement invalide.';
    } elseif (!empty($event_end_date) && $event_end_date < $event_date) {
        $error = 'La date de fin ne peut pas être antérieure à la date de début.';
    } else {
        try {
            $conn = getDBConnection();
            $stmt = $conn->prepare("INSERT INTO projects (user_id, title, description, event_date, event_time, event_end_date, event_end_time, event_location, event_type, show_guest_list) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->execute([getCurrentUserId(), $title, $description, $event_date, $event_time, $event_end_date, $event_end_time, $event_location, $event_type, $show_guest_list]);
            
            $project_id = $conn->lastInsertId();
            header('Location: view_project.php?id=' . $project_id);
            exit();
        } catch(PDOException $e) {
            $error = 'Échec de la création du projet. Veuillez réessayer.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Créer un Projet - Gestionnaire de Fêtes</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <nav class="navbar">
        <div class="container">
            <h1 class="logo">Gestionnaire de Fêtes</h1>
            <div class="nav-links">
                <a href="index.php">Mes Projets</a>
                <a href="create_project.php" class="active">Créer un Projet</a>
                <span class="user-info">Bienvenue, <?php echo htmlspecialchars($_SESSION['username']); ?></span>
                <a href="logout.php">Déconnexion</a>
            </div>
        </div>
    </nav>
    
    <div class="container">
        <div class="form-container">
            <h2>Créer un Nouveau Projet</h2>
            
            <?php if ($error): ?>
                <div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div>
            <?php endif; ?>
            
            <form method="POST" action="">
                <div class="form-group">
                    <label for="title">Titre : *</label>
                    <input type="text" id="title" name="title" required>
                </div>
                
                <div class="form-group">
                    <label for="event_type">Type d'événement : *</label>
                    <select id="event_type" name="event_type" required>
                        <option value="">Sélectionner le type</option>
                        <option value="party">Fête</option>
                        <option value="birthday">Anniversaire</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <label for="event_date">Date de l'événement : *</label>
                    <input type="date" id="event_date" name="event_date" required>
                </div>
                
                <div class="form-group">
                    <label for="event_time">Heure de l'événement :</label>
                    <input type="time" id="event_time" name="event_time">
                </div>
                
                <div class="form-group">
                    <label for="event_end_date">Date de fin :</label>
                    <input type="date" id="event_end_date" name="event_end_date">
                </div>
                
                <div class="form-group">
                    <label for="event_end_time">Heure de fin :</label>
                    <input type="time" id="event_end_time" name="event_end_time">
                </div>
                
                <div class="form-group">
                    <label for="event_location">Lieu de l'événement :</label>
                    <input type="text" id="event_location" name="event_location" placeholder="ex: 123 Rue de la Fête, Paris, France">
                </div>
                
                <div class="form-group">
                    <label for="description">Description :</label>
                    <textarea id="description" name="description" rows="4"></textarea>
                </div>
                
                <div class="form-group">
                    <label style="display: flex; align-items: center; cursor: pointer;">
                        <input type="checkbox" id="show_guest_list" name="show_guest_list" style="margin-right: 10px; width: auto;">
                        <span>Afficher la liste des invités aux participants (les invités peuvent voir qui d'autre a accepté)</span>
                    </label>
                </div>
                
                <div class="form-actions">
                    <button type="submit" class="btn btn-primary">Créer le Projet</button>
                    <a href="index.php" class="btn btn-secondary">Annuler</a>
                </div>
            </form>
        </div>
    </div>
</body>
</html>
