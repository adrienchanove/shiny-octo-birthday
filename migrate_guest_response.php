#!/usr/bin/env php
<?php
/**
 * Database Migration Script for Guest Response Feature
 * 
 * This script adds new fields required for the guest response feature:
 * - show_guest_list to projects table
 * - uncertain status to invitations
 * - guest_message to invitations
 * - response_updated_at to invitations
 * 
 * Usage: php migrate_guest_response.php
 */

// Load configuration
require_once __DIR__ . '/config.php';

// Only accessible from console
if (isset($_SERVER['REQUEST_METHOD'])) {
    die();
    return;
}

echo "================================\n";
echo "Guest Response Feature Migration\n";
echo "================================\n\n";

echo "This will update your database to support the new guest response features.\n\n";
echo "Changes to be applied:\n";
echo "  - Add 'show_guest_list' column to projects table\n";
echo "  - Add 'uncertain' status option to invitations\n";
echo "  - Add 'guest_message' column to invitations table\n";
echo "  - Add 'response_updated_at' column to invitations table\n\n";
echo "Are you sure you want to continue? (yes/no): ";

$handle = fopen("php://stdin", "r");
$line = fgets($handle);
$confirmation = trim(strtolower($line));
fclose($handle);

if ($confirmation !== 'yes') {
    echo "\nMigration cancelled.\n";
    exit(0);
}

echo "\nStarting migration...\n\n";

try {
    $conn = getDBConnection();
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Check if show_guest_list column exists
    $stmt = $conn->query("SHOW COLUMNS FROM projects LIKE 'show_guest_list'");
    if ($stmt->rowCount() == 0) {
        echo "- Adding 'show_guest_list' column to projects table...\n";
        $conn->exec("ALTER TABLE projects ADD COLUMN show_guest_list BOOLEAN DEFAULT FALSE AFTER event_type");
        echo "✓ Column added\n";
    } else {
        echo "✓ 'show_guest_list' column already exists\n";
    }
    
    // Check if uncertain status exists in invitations
    $stmt = $conn->query("SHOW COLUMNS FROM invitations WHERE Field = 'status'");
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($row && !stristr($row['Type'], 'uncertain')) {
        echo "- Adding 'uncertain' status to invitations table...\n";
        $conn->exec("ALTER TABLE invitations MODIFY COLUMN status ENUM('pending', 'accepted', 'declined', 'uncertain') DEFAULT 'pending'");
        echo "✓ Status option added\n";
    } else {
        echo "✓ 'uncertain' status already exists\n";
    }
    
    // Check if guest_message column exists
    $stmt = $conn->query("SHOW COLUMNS FROM invitations LIKE 'guest_message'");
    if ($stmt->rowCount() == 0) {
        echo "- Adding 'guest_message' column to invitations table...\n";
        $conn->exec("ALTER TABLE invitations ADD COLUMN guest_message TEXT AFTER status");
        echo "✓ Column added\n";
    } else {
        echo "✓ 'guest_message' column already exists\n";
    }
    
    // Check if response_updated_at column exists
    $stmt = $conn->query("SHOW COLUMNS FROM invitations LIKE 'response_updated_at'");
    if ($stmt->rowCount() == 0) {
        echo "- Adding 'response_updated_at' column to invitations table...\n";
        $conn->exec("ALTER TABLE invitations ADD COLUMN response_updated_at TIMESTAMP NULL AFTER accepted_at");
        echo "✓ Column added\n";
    } else {
        echo "✓ 'response_updated_at' column already exists\n";
    }
    
    echo "\n✓ Migration completed successfully!\n";
    echo "\nYour database now supports the guest response features.\n";
    
} catch(PDOException $e) {
    echo "\n✗ Database error: " . $e->getMessage() . "\n";
    exit(1);
}

echo "\n================================\n";
echo "Migration completed!\n";
echo "================================\n";
?>
