<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Setup Check - Party Manager</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            max-width: 800px;
            margin: 50px auto;
            padding: 20px;
            background-color: #f5f5f5;
        }
        .container {
            background: white;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        h1 {
            color: #667eea;
        }
        .check-item {
            padding: 15px;
            margin: 10px 0;
            border-radius: 5px;
            border-left: 4px solid #ddd;
        }
        .success {
            background-color: #d4edda;
            border-color: #28a745;
            color: #155724;
        }
        .error {
            background-color: #f8d7da;
            border-color: #dc3545;
            color: #721c24;
        }
        .warning {
            background-color: #fff3cd;
            border-color: #ffc107;
            color: #856404;
        }
        .status {
            font-weight: bold;
            margin-right: 10px;
        }
        code {
            background: #f4f4f4;
            padding: 2px 6px;
            border-radius: 3px;
            font-family: monospace;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Party Manager - Setup Check</h1>
        <p>This page will help you verify that your installation is configured correctly.</p>
        
        <?php
        $checks = [];
        
        // Check PHP version
        $phpVersion = phpversion();
        $checks[] = [
            'name' => 'PHP Version',
            'status' => version_compare($phpVersion, '7.0.0', '>=') ? 'success' : 'error',
            'message' => "PHP version: $phpVersion " . (version_compare($phpVersion, '7.0.0', '>=') ? '✓' : '✗ (Requires PHP 7.0+)')
        ];
        
        // Check PDO extension
        $checks[] = [
            'name' => 'PDO Extension',
            'status' => extension_loaded('pdo') ? 'success' : 'error',
            'message' => extension_loaded('pdo') ? 'PDO extension is loaded ✓' : 'PDO extension is not loaded ✗'
        ];
        
        // Check PDO MySQL driver
        $checks[] = [
            'name' => 'PDO MySQL Driver',
            'status' => extension_loaded('pdo_mysql') ? 'success' : 'error',
            'message' => extension_loaded('pdo_mysql') ? 'PDO MySQL driver is loaded ✓' : 'PDO MySQL driver is not loaded ✗'
        ];
        
        // Check config file
        if (file_exists('config.php')) {
            $checks[] = [
                'name' => 'Configuration File',
                'status' => 'success',
                'message' => 'config.php exists ✓'
            ];
            
            // Try to include config and test database connection
            try {
                require_once 'config.php';
                
                try {
                    $conn = getDBConnection();
                    $checks[] = [
                        'name' => 'Database Connection',
                        'status' => 'success',
                        'message' => 'Successfully connected to database ✓'
                    ];
                    
                    // Check if tables exist
                    $tables = ['users', 'projects', 'invitations'];
                    $tableStatus = [];
                    foreach ($tables as $table) {
                        $stmt = $conn->query("SHOW TABLES LIKE '$table'");
                        if ($stmt->rowCount() > 0) {
                            $tableStatus[] = "$table ✓";
                        } else {
                            $tableStatus[] = "$table ✗";
                        }
                    }
                    
                    $allTablesExist = !in_array(false, array_map(function($s) { return strpos($s, '✓') !== false; }, $tableStatus));
                    $checks[] = [
                        'name' => 'Database Tables',
                        'status' => $allTablesExist ? 'success' : 'warning',
                        'message' => 'Tables: ' . implode(', ', $tableStatus) . ($allTablesExist ? '' : '<br>Run database.sql to create missing tables.')
                    ];
                    
                } catch (Exception $e) {
                    $checks[] = [
                        'name' => 'Database Connection',
                        'status' => 'error',
                        'message' => 'Failed to connect to database ✗<br>Error: ' . htmlspecialchars($e->getMessage()) . '<br>Please check your database credentials in config.php'
                    ];
                }
            } catch (Exception $e) {
                $checks[] = [
                    'name' => 'Configuration',
                    'status' => 'error',
                    'message' => 'Error loading config.php ✗<br>' . htmlspecialchars($e->getMessage())
                ];
            }
        } else {
            $checks[] = [
                'name' => 'Configuration File',
                'status' => 'error',
                'message' => 'config.php not found ✗'
            ];
        }
        
        // Check if session is working
        if (session_status() === PHP_SESSION_ACTIVE) {
            $checks[] = [
                'name' => 'Session Support',
                'status' => 'success',
                'message' => 'PHP sessions are working ✓'
            ];
        } else {
            $checks[] = [
                'name' => 'Session Support',
                'status' => 'warning',
                'message' => 'Session status unclear - may need configuration'
            ];
        }
        
        // Display all checks
        foreach ($checks as $check) {
            echo '<div class="check-item ' . htmlspecialchars($check['status']) . '">';
            echo '<span class="status">' . htmlspecialchars($check['name']) . ':</span>';
            echo $check['message'];
            echo '</div>';
        }
        ?>
        
        <div style="margin-top: 30px; padding: 20px; background: #e7f3ff; border-radius: 5px;">
            <h3>Next Steps:</h3>
            <ol>
                <li>If all checks pass, delete this <code>setup_check.php</code> file for security</li>
                <li>Navigate to <a href="register.php">register.php</a> to create your first account</li>
                <li>Start creating projects and inviting guests!</li>
            </ol>
        </div>
    </div>
</body>
</html>
