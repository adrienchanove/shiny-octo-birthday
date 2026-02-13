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
    $error = 'Lien d\'invitation invalide.';
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
        $error = 'Invitation introuvable ou invalide.';
    } else {
        // Handle acceptance
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $action = $_POST['action'];
            $guest_message = isset($_POST['guest_message']) ? trim($_POST['guest_message']) : null;
            
            if ($action === 'accept') {
                try {
                    $stmt = $conn->prepare("UPDATE invitations SET status = 'accepted', accepted_at = NOW(), response_updated_at = NOW(), guest_message = ? WHERE id = ?");
                    $stmt->execute([$guest_message, $invitation['id']]);
                    $success = 'Vous avez accepté l\'invitation !';
                    
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
                    $error = 'Échec de l\'acceptation de l\'invitation. Veuillez réessayer.';
                }
            } elseif ($action === 'decline') {
                try {
                    $stmt = $conn->prepare("UPDATE invitations SET status = 'declined', response_updated_at = NOW(), guest_message = ? WHERE id = ?");
                    $stmt->execute([$guest_message, $invitation['id']]);
                    $success = 'Vous avez refusé l\'invitation.';
                    
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
                    $error = 'Échec du refus de l\'invitation. Veuillez réessayer.';
                }
            } elseif ($action === 'uncertain') {
                try {
                    $stmt = $conn->prepare("UPDATE invitations SET status = 'uncertain', response_updated_at = NOW(), guest_message = ? WHERE id = ?");
                    $stmt->execute([$guest_message, $invitation['id']]);
                    $success = 'Vous avez marqué votre réponse comme incertaine.';
                    
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
                    $error = 'Échec de la mise à jour de la réponse. Veuillez réessayer.';
                }
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($invitation) ? htmlspecialchars($invitation['title']) . ' - Invitation' : 'Invitation'; ?> - Gestionnaire de Fêtes</title>
    
    <?php if (isset($invitation)): ?>
    <!-- Open Graph Meta Tags -->
    <meta property="og:title" content="Vous êtes invité à <?php echo htmlspecialchars($invitation['title']); ?>">
    <?php 
        if (!empty($invitation['description'])) {
            $og_description = $invitation['description'];
        } else {
            $event_date_timestamp = strtotime($invitation['event_date']);
            $fallback_desc = 'Rejoignez-nous pour ' . $invitation['title'];
            if ($event_date_timestamp !== false) {
                setlocale(LC_TIME, 'fr_FR.UTF-8', 'fr_FR', 'fra');
                $fallback_desc .= ' le ' . strftime('%e %B %Y', $event_date_timestamp);
            }
            $og_description = $fallback_desc;
        }
    ?>
    <meta property="og:description" content="<?php echo htmlspecialchars($og_description); ?>">
    <meta property="og:type" content="event">
    <meta property="og:url" content="<?php echo htmlspecialchars(SITE_URL . '/accept_invitation.php?project=' . $project_id . '&code=' . $invitation_code); ?>">
    <meta property="og:site_name" content="Gestionnaire de Fêtes">
    
    <!-- Event Specific Open Graph Tags -->
    <?php 
        $start_timestamp = strtotime($invitation['event_date'] . ' ' . ($invitation['event_time'] ?: '00:00:00'));
        if ($start_timestamp !== false): 
    ?>
    <meta property="event:start_time" content="<?php echo date('c', $start_timestamp); ?>">
    <?php endif; ?>
    <?php 
        if (!empty($invitation['event_end_date'])):
            $end_timestamp = strtotime($invitation['event_end_date'] . ' ' . ($invitation['event_end_time'] ?: '23:59:59'));
            if ($end_timestamp !== false):
    ?>
    <meta property="event:end_time" content="<?php echo date('c', $end_timestamp); ?>">
    <?php 
            endif;
        endif; 
    ?>
    <?php if (!empty($invitation['event_location'])): ?>
    <meta property="event:location" content="<?php echo htmlspecialchars($invitation['event_location']); ?>">
    <?php endif; ?>
    
    <!-- Twitter Card Meta Tags -->
    <meta name="twitter:card" content="summary">
    <meta name="twitter:title" content="Vous êtes invité à <?php echo htmlspecialchars($invitation['title']); ?>">
    <meta name="twitter:description" content="<?php echo htmlspecialchars($og_description); ?>">
    <?php endif; ?>
    
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="container">
        <div class="invitation-box">
            <h1>Vous êtes invité !</h1>
            
            <?php if ($error): ?>
                <div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div>
                
                <!-- Manual code entry form when there's an error -->
                <div class="form-container" style="margin-top: 20px; max-width: 100%; padding: 20px;">
                    <h3 style="color: #667eea; margin-bottom: 15px; font-size: 18px;">Entrez votre code d'invitation</h3>
                    <p style="color: #666; margin-bottom: 20px; font-size: 14px;">
                        Si vous avez un code d'invitation, veuillez l'entrer ci-dessous :
                    </p>
                    <form method="POST" action="">
                        <div class="form-group">
                            <label for="manual_code">Code d'invitation (Format : XXXX-XXXX-XXXX)</label>
                            <input 
                                type="text" 
                                id="manual_code" 
                                name="manual_code" 
                                placeholder="ex: AB3X-9KL2-P7Q4" 
                                required
                                pattern="[A-Z0-9]{4}-[A-Z0-9]{4}-[A-Z0-9]{4}"
                                title="Veuillez entrer le code au format : XXXX-XXXX-XXXX (lettres majuscules et chiffres)"
                                style="text-transform: uppercase;"
                            >
                        </div>
                        <button type="submit" class="btn btn-primary">Soumettre le code</button>
                    </form>
                </div>
            <?php elseif ($success): ?>
                <div class="alert alert-success"><?php echo htmlspecialchars($success); ?></div>
            <?php endif; ?>
            
            <?php if (isset($invitation)): ?>
                <div class="invitation-details">
                    <div class="event-type-badge <?php echo htmlspecialchars($invitation['event_type']); ?>">
                        <?php 
                            $type = htmlspecialchars($invitation['event_type']);
                            echo $type === 'party' ? 'Fête' : 'Anniversaire';
                        ?>
                    </div>
                    
                    <h2><?php echo htmlspecialchars($invitation['title']); ?></h2>
                    
                    <div class="invitation-info">
                        <p><strong>Hôte :</strong> <?php echo htmlspecialchars($invitation['host_username']); ?></p>
                        <p><strong>Date :</strong> <?php 
                            setlocale(LC_TIME, 'fr_FR.UTF-8', 'fr_FR', 'fra');
                            echo strftime('%e %B %Y', strtotime($invitation['event_date'])); 
                        ?></p>
                        <?php if (!empty($invitation['event_time'])): ?>
                            <p><strong>Heure :</strong> <?php echo date('H:i', strtotime($invitation['event_time'])); ?></p>
                        <?php endif; ?>
                        <?php if (!empty($invitation['event_end_date'])): ?>
                            <p><strong>Date de fin :</strong> <?php 
                                setlocale(LC_TIME, 'fr_FR.UTF-8', 'fr_FR', 'fra');
                                echo strftime('%e %B %Y', strtotime($invitation['event_end_date'])); 
                            ?></p>
                        <?php endif; ?>
                        <?php if (!empty($invitation['event_end_time'])): ?>
                            <p><strong>Heure de fin :</strong> <?php echo date('H:i', strtotime($invitation['event_end_time'])); ?></p>
                        <?php endif; ?>
                        <?php if (!empty($invitation['event_location'])): ?>
                            <p><strong>Lieu :</strong> <?php echo htmlspecialchars($invitation['event_location']); ?></p>
                        <?php endif; ?>
                        <?php if (!empty($invitation['description'])): ?>
                            <p><strong>Description :</strong> <?php echo htmlspecialchars($invitation['description']); ?></p>
                        <?php endif; ?>
                        <?php if (!empty($invitation['invitee_name'])): ?>
                            <p><strong>Invité :</strong> <?php echo htmlspecialchars($invitation['invitee_name']); ?></p>
                        <?php endif; ?>
                    </div>
                    
                    <div class="invitation-status">
                        <p><strong>Statut :</strong> 
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
                        </p>
                        <?php if (!empty($invitation['guest_message'])): ?>
                            <p><strong>Votre message :</strong> <?php echo nl2br(htmlspecialchars($invitation['guest_message'])); ?></p>
                        <?php endif; ?>
                    </div>
                    
                    <!-- Guest list if enabled by host and guest has accepted -->
                    <?php if ($invitation['show_guest_list'] && $invitation['status'] === 'accepted'): ?>
                        <div class="guest-list-section">
                            <h3>Autres invités présents</h3>
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
                                <p class="empty-message">Aucun autre invité n'a encore accepté.</p>
                            <?php else: ?>
                                <ul class="guest-list">
                                    <?php foreach ($accepted_guests as $guest): ?>
                                        <li><?php echo htmlspecialchars($guest['invitee_name'] ?: 'Invité'); ?></li>
                                    <?php endforeach; ?>
                                </ul>
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>
                    
                    <form method="POST" action="">
                        <div class="form-group">
                            <label for="guest_message">Message à l'hôte (optionnel) :</label>
                            <textarea id="guest_message" name="guest_message" rows="3" placeholder="Laissez un message à l'hôte..."><?php echo htmlspecialchars($invitation['guest_message'] ?? ''); ?></textarea>
                        </div>
                        
                        <div class="invitation-actions">
                            <button type="submit" name="action" value="accept" class="btn btn-success">Accepter</button>
                            <button type="submit" name="action" value="uncertain" class="btn btn-warning">Peut-être</button>
                            <button type="submit" name="action" value="decline" class="btn btn-danger">Refuser</button>
                        </div>
                        
                        <?php if ($invitation['status'] !== 'pending'): ?>
                            <p class="text-center" style="margin-top: 15px; color: #666; font-size: 14px;">
                                Vous pouvez changer votre réponse à tout moment en cliquant sur un bouton différent ci-dessus.
                            </p>
                        <?php endif; ?>
                    </form>
                </div>
            <?php endif; ?>
            
            <?php if (isLoggedIn()): ?>
                <p class="text-center">
                    <a href="index.php">Aller à Mes Projets</a>
                </p>
            <?php else: ?>
                <p class="text-center">
                    Vous voulez gérer vos propres événements ? <a href="register.php">Créer un compte</a>
                </p>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>
