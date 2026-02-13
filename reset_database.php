#!/usr/bin/env php
<?php
/**
 * Database Reset Script
 * 
 * This script drops and recreates the database using the latest database.sql schema.
 * Usage: php reset_database.php
 * 
 * WARNING: This will delete all existing data in the database!
 */

// Load configuration
require_once __DIR__ . '/config.php';

// Only accessible from console
if (isset($_SERVER['REQUEST_METHOD'])) {
    die();
    return;
}


echo "================================\n";
echo "Database Reset Script\n";
echo "================================\n\n";

// Ask for confirmation
echo "WARNING: This will DROP the entire database and recreate it.\n";
echo "All existing data will be LOST!\n\n";
echo "Are you sure you want to continue? (yes/no): ";

$handle = fopen("php://stdin", "r");
$line = fgets($handle);
$confirmation = trim(strtolower($line));
fclose($handle);

if ($confirmation !== 'yes') {
    echo "\nOperation cancelled.\n";
    exit(0);
}

echo "\nStarting database reset...\n\n";

try {
    // Connect to MySQL without selecting a database
    $conn = new PDO("mysql:host=" . DB_HOST, DB_USER, DB_PASS);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "✓ Connected to MySQL server\n";
    
    // Drop database if exists
    echo "- Dropping database '" . DB_NAME . "' if it exists...\n";
    $conn->exec("DROP DATABASE IF EXISTS " . DB_NAME);
    echo "✓ Database dropped\n";
    
    // Create database
    echo "- Creating database '" . DB_NAME . "'...\n";
    $conn->exec("CREATE DATABASE " . DB_NAME);
    echo "✓ Database created\n";
    
    // Select the database
    $conn->exec("USE " . DB_NAME);
    
    // Read and execute database.sql
    $sql_file = __DIR__ . '/database.sql';
    
    if (!file_exists($sql_file)) {
        throw new Exception("database.sql file not found at: " . $sql_file);
    }
    
    echo "- Reading database.sql...\n";
    $sql = file_get_contents($sql_file);
    
    // Remove comments and split into individual statements
    $statements = array_filter(
        array_map('trim',
            preg_split('/;[\r\n]+/', $sql)
        ),
        function($stmt) {
            // Filter out empty statements and comment-only statements
            return !empty($stmt) &&
                   !preg_match('/^\/\*/', $stmt) &&
                   strtoupper(substr($stmt, 0, 3)) !== 'USE';
        }
    );
    
    echo "- Executing SQL statements...\n";
    $count = 0;
    foreach ($statements as $statement) {
        if (!empty(trim($statement))) {
            $conn->exec($statement);
            $count++;
        }
    }
    
    echo "✓ Executed $count SQL statements\n";
    
    // Verify tables were created
    $stmt = $conn->query("SHOW TABLES");
    $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
    echo "\n✓ Database reset complete!\n";
    echo "\nCreated tables:\n";
    foreach ($tables as $table) {
        echo "  - $table\n";
    }
    
    echo "\nThe database is now ready to use.\n";
    
} catch(PDOException $e) {
    echo "\n✗ Database error: " . $e->getMessage() . "\n";
    exit(1);
} catch(Exception $e) {
    echo "\n✗ Error: " . $e->getMessage() . "\n";
    exit(1);
}

echo "\n================================\n";
echo "Reset completed successfully!\n";
echo "================================\n";
?>
