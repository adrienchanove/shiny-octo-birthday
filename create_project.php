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
        $error = 'Title, date, and event type are required.';
    } elseif (!in_array($event_type, ['party', 'birthday'])) {
        $error = 'Invalid event type.';
    } elseif (!empty($event_end_date) && $event_end_date < $event_date) {
        $error = 'Event end date cannot be before event start date.';
    } else {
        try {
            $conn = getDBConnection();
            $stmt = $conn->prepare("INSERT INTO projects (user_id, title, description, event_date, event_time, event_end_date, event_end_time, event_location, event_type, show_guest_list) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->execute([getCurrentUserId(), $title, $description, $event_date, $event_time, $event_end_date, $event_end_time, $event_location, $event_type, $show_guest_list]);
            
            $project_id = $conn->lastInsertId();
            header('Location: view_project.php?id=' . $project_id);
            exit();
        } catch(PDOException $e) {
            $error = 'Failed to create project. Please try again.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Project - Party Manager</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <nav class="navbar">
        <div class="container">
            <h1 class="logo">Party Manager</h1>
            <div class="nav-links">
                <a href="index.php">My Projects</a>
                <a href="create_project.php" class="active">Create Project</a>
                <span class="user-info">Welcome, <?php echo htmlspecialchars($_SESSION['username']); ?></span>
                <a href="logout.php">Logout</a>
            </div>
        </div>
    </nav>
    
    <div class="container">
        <div class="form-container">
            <h2>Create New Project</h2>
            
            <?php if ($error): ?>
                <div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div>
            <?php endif; ?>
            
            <form method="POST" action="">
                <div class="form-group">
                    <label for="title">Title: *</label>
                    <input type="text" id="title" name="title" required>
                </div>
                
                <div class="form-group">
                    <label for="event_type">Event Type: *</label>
                    <select id="event_type" name="event_type" required>
                        <option value="">Select type</option>
                        <option value="party">Party</option>
                        <option value="birthday">Birthday</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <label for="event_date">Event Date: *</label>
                    <input type="date" id="event_date" name="event_date" required>
                </div>
                
                <div class="form-group">
                    <label for="event_time">Event Time:</label>
                    <input type="time" id="event_time" name="event_time">
                </div>
                
                <div class="form-group">
                    <label for="event_end_date">Event End Date:</label>
                    <input type="date" id="event_end_date" name="event_end_date">
                </div>
                
                <div class="form-group">
                    <label for="event_end_time">Event End Time:</label>
                    <input type="time" id="event_end_time" name="event_end_time">
                </div>
                
                <div class="form-group">
                    <label for="event_location">Event Location:</label>
                    <input type="text" id="event_location" name="event_location" placeholder="e.g., 123 Party St, New York, NY">
                </div>
                
                <div class="form-group">
                    <label for="description">Description:</label>
                    <textarea id="description" name="description" rows="4"></textarea>
                </div>
                
                <div class="form-group">
                    <label style="display: flex; align-items: center; cursor: pointer;">
                        <input type="checkbox" id="show_guest_list" name="show_guest_list" style="margin-right: 10px; width: auto;">
                        <span>Show guest list to attendees (guests can see who else has accepted)</span>
                    </label>
                </div>
                
                <div class="form-actions">
                    <button type="submit" class="btn btn-primary">Create Project</button>
                    <a href="index.php" class="btn btn-secondary">Cancel</a>
                </div>
            </form>
        </div>
    </div>
</body>
</html>
