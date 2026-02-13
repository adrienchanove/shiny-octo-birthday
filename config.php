<?php
// Database configuration
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'party_manager');

// Site configuration
define('SITE_URL', 'http://localhost');

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Database connection
function getDBConnection() {
    try {
        $conn = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME, DB_USER, DB_PASS);
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        return $conn;
    } catch(PDOException $e) {
        // Log the error for debugging (in production, log to a file)
        error_log("Database connection failed: " . $e->getMessage());
        // Show generic error to user
        die("Connection failed. Please contact the administrator.");
    }
}

// Check if user is logged in
function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

// Redirect if not logged in
function requireLogin() {
    if (!isLoggedIn()) {
        header('Location: login.php');
        exit();
    }
}

// Get current user ID
function getCurrentUserId() {
    return isset($_SESSION['user_id']) ? $_SESSION['user_id'] : null;
}

// Generate random invitation code
// Format: XXXX-XXXX-XXXX (3 sequences of 4 uppercase alphanumeric chars)
function generateInvitationCode() {
    $characters = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
    $code = '';
    
    for ($i = 0; $i < 3; $i++) {
        if ($i > 0) {
            $code .= '-';
        }
        for ($j = 0; $j < 4; $j++) {
            $code .= $characters[random_int(0, strlen($characters) - 1)];
        }
    }
    
    return $code;
}
?>
