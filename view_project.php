<?php
require_once 'config.php';
requireLogin();

// Set French locale for date formatting
setlocale(LC_TIME, 'fr_FR.UTF-8', 'fr_FR', 'fra');

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
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($project['title']); ?> - Gestionnaire de Fêtes</title>
    
    <!-- Open Graph Meta Tags -->
    <meta property="og:title" content="<?php echo htmlspecialchars($project['title']); ?>">
    <meta property="og:description" content="<?php echo htmlspecialchars($project['description']); ?>">
    <meta property="og:type" content="event">
    <meta property="og:url" content="<?php echo htmlspecialchars(SITE_URL . '/view_project.php?id=' . $project_id); ?>">
    <meta property="og:site_name" content="Gestionnaire de Fêtes">
    
    <!-- Event Specific Open Graph Tags -->
    <?php 
        $start_timestamp = strtotime($project['event_date'] . ' ' . ($project['event_time'] ?: '00:00:00'));
        if ($start_timestamp !== false): 
    ?>
    <meta property="event:start_time" content="<?php echo date('c', $start_timestamp); ?>">
    <?php endif; ?>
    <?php 
        if (!empty($project['event_end_date'])):
            $end_timestamp = strtotime($project['event_end_date'] . ' ' . ($project['event_end_time'] ?: '23:59:59'));
            if ($end_timestamp !== false):
    ?>
    <meta property="event:end_time" content="<?php echo date('c', $end_timestamp); ?>">
    <?php 
            endif;
        endif; 
    ?>
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
        <div class="project-detail">
            <div class="project-header">
                <h2><?php echo htmlspecialchars($project['title']); ?></h2>
                <span class="badge <?php echo htmlspecialchars($project['event_type']); ?>">
                    <?php 
                        $type = htmlspecialchars($project['event_type']);
                        echo $type === 'party' ? 'Fête' : 'Anniversaire';
                    ?>
                </span>
            </div>
            
            <div class="project-info">
                <p><strong>Date de l'événement :</strong> <?php 
                    echo strftime('%e %B %Y', strtotime($project['event_date'])); 
                ?></p>
                <?php if (!empty($project['event_time'])): ?>
                    <p><strong>Heure de l'événement :</strong> <?php echo date('H:i', strtotime($project['event_time'])); ?></p>
                <?php endif; ?>
                <?php if (!empty($project['event_end_date'])): ?>
                    <p><strong>Date de fin :</strong> <?php 
                        echo strftime('%e %B %Y', strtotime($project['event_end_date'])); 
                    ?></p>
                <?php endif; ?>
                <?php if (!empty($project['event_end_time'])): ?>
                    <p><strong>Heure de fin :</strong> <?php echo date('H:i', strtotime($project['event_end_time'])); ?></p>
                <?php endif; ?>
                <?php if (!empty($project['event_location'])): ?>
                    <p><strong>Lieu :</strong> <?php echo htmlspecialchars($project['event_location']); ?></p>
                <?php endif; ?>
                <p><strong>Description :</strong> <?php echo htmlspecialchars($project['description']); ?></p>
                <p><strong>Liste des invités visible par les invités acceptés :</strong> <?php echo $project['show_guest_list'] ? 'Oui' : 'Non'; ?></p>
                <p><strong>Créé le :</strong> <?php 
                    echo strftime('%e %B %Y', strtotime($project['created_at'])); 
                ?></p>
            </div>
            
            <div class="section">
                <h3>Invitations</h3>
                <a href="create_invitation.php?project_id=<?php echo $project_id; ?>" class="btn btn-primary">Créer une Nouvelle Invitation</a>
                
                <?php if (empty($invitations)): ?>
                    <p class="empty-message">Aucune invitation créée pour le moment.</p>
                <?php else: ?>
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Nom de l'invité</th>
                                <th>Statut</th>
                                <th>Message</th>
                                <th>Lien d'invitation</th>
                                <th>Créé le</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($invitations as $invitation): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($invitation['invitee_name'] ?: 'N/A'); ?></td>
                                    <td>
                                        <span class="status <?php echo htmlspecialchars($invitation['status']); ?>">
                                            <?php 
                                                $status = htmlspecialchars($invitation['status']);
                                                $status_fr = [
                                                    'pending' => 'En attente',
                                                    'accepted' => 'Accepté',
                                                    'declined' => 'Refusé',
                                                    'uncertain' => 'Incertain'
                                                ];
                                                echo $status_fr[$status] ?? ucfirst($status);
                                            ?>
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
                                        <button class="btn btn-small" onclick="copyLink(this)">Copier</button>
                                    </td>
                                    <td><?php 
                                        echo strftime('%e %b %Y', strtotime($invitation['created_at'])); 
                                    ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php endif; ?>
            </div>
            
            <div class="project-actions">
                <a href="index.php" class="btn btn-secondary">Retour aux Projets</a>
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
                button.textContent = 'Copié !';
                setTimeout(() => {
                    button.textContent = originalText;
                }, 2000);
            }).catch(err => {
                console.error('Échec de la copie: ', err);
                // Fallback for older browsers (intentionally kept for legacy compatibility)
                input.select();
                try {
                    document.execCommand('copy');
                    const originalText = button.textContent;
                    button.textContent = 'Copié !';
                    setTimeout(() => {
                        button.textContent = originalText;
                    }, 2000);
                } catch (e) {
                    alert('Échec de la copie du lien');
                }
            });
        } else {
            // Fallback for older browsers (intentionally kept for legacy compatibility)
            input.select();
            try {
                document.execCommand('copy');
                const originalText = button.textContent;
                button.textContent = 'Copié !';
                setTimeout(() => {
                    button.textContent = originalText;
                }, 2000);
            } catch (e) {
                alert('Échec de la copie du lien');
            }
        }
    }
    </script>
</body>
</html>
