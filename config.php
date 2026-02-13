<?php
// Load environment variables from .env file
function loadEnvFile($path) {
    if (!file_exists($path)) {
        die("Error: .env file not found at $path. Please copy .env.example to .env and configure it.");
    }
    
    $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        $trimmed = trim($line);
        
        // Skip comments and empty lines
        if ($trimmed === '' || strpos($trimmed, '#') === 0) {
            continue;
        }
        
        // Parse KEY=VALUE format
        if (strpos($trimmed, '=') !== false) {
            list($key, $value) = explode('=', $trimmed, 2);
            $key = trim($key);
            $value = trim($value);
            
            // Validate key is not empty and follows naming conventions
            if (empty($key) || !preg_match('/^[A-Z_][A-Z0-9_]*$/i', $key)) {
                continue;
            }
            
            // Remove quotes if present (handles both single and double quotes)
            // Note: Does not handle escaped quotes within values
            if (strlen($value) >= 2) {
                if ((substr($value, 0, 1) === '"' && substr($value, -1) === '"') ||
                    (substr($value, 0, 1) === "'" && substr($value, -1) === "'")) {
                    $value = substr($value, 1, -1);
                }
            }
            
            // Set in $_ENV superglobal
            $_ENV[$key] = $value;
            putenv("$key=$value");
        }
    }
}

// Load .env file
loadEnvFile(__DIR__ . '/.env');

// Validate required environment variables
$required = ['DB_HOST', 'DB_USER', 'DB_PASS', 'DB_NAME', 'SITE_URL'];
$missing = [];
foreach ($required as $var) {
    if (!isset($_ENV[$var])) {
        $missing[] = $var;
    }
}
if (!empty($missing)) {
    die("Error: Missing required environment variables: " . implode(', ', $missing));
}

// Database configuration
define('DB_HOST', $_ENV['DB_HOST']);
define('DB_USER', $_ENV['DB_USER']);
define('DB_PASS', $_ENV['DB_PASS']);
define('DB_NAME', $_ENV['DB_NAME']);

// Site configuration
define('SITE_URL', $_ENV['SITE_URL']);

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
    $length = strlen($characters);
    $code = '';
    
    for ($i = 0; $i < 3; $i++) {
        if ($i > 0) {
            $code .= '-';
        }
        for ($j = 0; $j < 4; $j++) {
            $code .= $characters[random_int(0, $length - 1)];
        }
    }
    
    return $code;
}
?>
