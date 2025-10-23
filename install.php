<?php
/**
 * StopNow Installation Script
 * Run this script to set up the application
 */

// Check PHP version
if (version_compare(PHP_VERSION, '7.4.0', '<')) {
    die('PHP 7.4 or higher is required. Current version: ' . PHP_VERSION);
}

// Check required extensions
$required_extensions = ['pdo', 'pdo_mysql', 'openssl', 'json'];
$missing_extensions = [];

foreach ($required_extensions as $ext) {
    if (!extension_loaded($ext)) {
        $missing_extensions[] = $ext;
    }
}

if (!empty($missing_extensions)) {
    die('Missing required PHP extensions: ' . implode(', ', $missing_extensions));
}

echo "<h1>StopNow Installation</h1>\n";
echo "<p>Welcome to StopNow installation script!</p>\n";

// Step 1: Database Configuration
echo "<h2>Step 1: Database Configuration</h2>\n";

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['step']) && $_POST['step'] === 'database') {
    $host = $_POST['db_host'] ?? 'localhost';
    $name = $_POST['db_name'] ?? 'stopnow';
    $user = $_POST['db_user'] ?? 'root';
    $pass = $_POST['db_pass'] ?? '';
    
    // Test database connection
    try {
        $pdo = new PDO("mysql:host=$host", $user, $pass);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        
        // Create database if it doesn't exist
        $pdo->exec("CREATE DATABASE IF NOT EXISTS `$name` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
        $pdo->exec("USE `$name`");
        
        // Import schema
        $schema = file_get_contents('database/schema.sql');
        $pdo->exec($schema);
        
        // Update config file
        $config_content = file_get_contents('config/config.php');
        $config_content = str_replace("define('DB_HOST', 'localhost');", "define('DB_HOST', '$host');", $config_content);
        $config_content = str_replace("define('DB_NAME', 'stopnow');", "define('DB_NAME', '$name');", $config_content);
        $config_content = str_replace("define('DB_USER', 'root');", "define('DB_USER', '$user');", $config_content);
        $config_content = str_replace("define('DB_PASS', '');", "define('DB_PASS', '$pass');", $config_content);
        
        file_put_contents('config/config.php', $config_content);
        
        echo "<p style='color: green;'>✅ Database configured successfully!</p>\n";
        
        // Step 2: Create directories
        echo "<h2>Step 2: Creating Directories</h2>\n";
        
        $directories = ['uploads', 'logs'];
        foreach ($directories as $dir) {
            if (!is_dir($dir)) {
                mkdir($dir, 0755, true);
                echo "<p style='color: green;'>✅ Created directory: $dir</p>\n";
            } else {
                echo "<p style='color: blue;'>ℹ️ Directory already exists: $dir</p>\n";
            }
        }
        
        // Step 3: Set permissions
        echo "<h2>Step 3: Setting Permissions</h2>\n";
        
        $permissions = [
            'uploads' => 0755,
            'logs' => 0755,
            '.htaccess' => 0644
        ];
        
        foreach ($permissions as $file => $perm) {
            if (file_exists($file)) {
                chmod($file, $perm);
                echo "<p style='color: green;'>✅ Set permissions for: $file</p>\n";
            }
        }
        
        // Step 4: Test application
        echo "<h2>Step 4: Testing Application</h2>\n";
        
        // Test database connection with new config
        require_once 'config/config.php';
        require_once 'config/database.php';
        
        try {
            $database = new Database();
            $conn = $database->connect();
            
            if ($conn) {
                echo "<p style='color: green;'>✅ Database connection test successful!</p>\n";
                
                // Test if tables exist
                $tables = ['users', 'parking_spots', 'bookings', 'reviews'];
                $existing_tables = [];
                
                foreach ($tables as $table) {
                    $stmt = $conn->query("SHOW TABLES LIKE '$table'");
                    if ($stmt->rowCount() > 0) {
                        $existing_tables[] = $table;
                    }
                }
                
                if (count($existing_tables) === count($tables)) {
                    echo "<p style='color: green;'>✅ All database tables created successfully!</p>\n";
                } else {
                    echo "<p style='color: red;'>❌ Some tables are missing. Please check the database schema.</p>\n";
                }
            }
        } catch (Exception $e) {
            echo "<p style='color: red;'>❌ Database connection test failed: " . $e->getMessage() . "</p>\n";
        }
        
        echo "<h2>Installation Complete!</h2>\n";
        echo "<p style='color: green; font-weight: bold;'>🎉 StopNow has been successfully installed!</p>\n";
        echo "<p>You can now access your application at: <a href='index.php'>index.php</a></p>\n";
        echo "<p><strong>Default test accounts:</strong></p>\n";
        echo "<ul>\n";
        echo "<li>Email: joao@email.com | Password: 123456</li>\n";
        echo "<li>Email: maria@email.com | Password: 123456</li>\n";
        echo "<li>Email: pedro@email.com | Password: 123456</li>\n";
        echo "</ul>\n";
        echo "<p><strong>Important:</strong> Delete this install.php file for security reasons!</p>\n";
        
    } catch (Exception $e) {
        echo "<p style='color: red;'>❌ Database connection failed: " . $e->getMessage() . "</p>\n";
        echo "<p>Please check your database credentials and try again.</p>\n";
    }
} else {
    // Show database configuration form
    echo "<form method='POST'>\n";
    echo "<input type='hidden' name='step' value='database'>\n";
    echo "<table>\n";
    echo "<tr><td>Database Host:</td><td><input type='text' name='db_host' value='localhost' required></td></tr>\n";
    echo "<tr><td>Database Name:</td><td><input type='text' name='db_name' value='stopnow' required></td></tr>\n";
    echo "<tr><td>Database User:</td><td><input type='text' name='db_user' value='root' required></td></tr>\n";
    echo "<tr><td>Database Password:</td><td><input type='password' name='db_pass'></td></tr>\n";
    echo "<tr><td colspan='2'><input type='submit' value='Install StopNow' style='background: #fed504; color: #46424d; padding: 10px 20px; border: none; border-radius: 5px; cursor: pointer;'></td></tr>\n";
    echo "</table>\n";
    echo "</form>\n";
}
?>

<style>
body { font-family: Arial, sans-serif; margin: 40px; background: #f5f5f5; }
h1, h2 { color: #46424d; }
table { background: white; padding: 20px; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); }
input[type="text"], input[type="password"] { padding: 8px; margin: 5px; border: 1px solid #ddd; border-radius: 4px; width: 200px; }
input[type="submit"] { background: #fed504; color: #46424d; padding: 10px 20px; border: none; border-radius: 5px; cursor: pointer; font-weight: bold; }
input[type="submit"]:hover { background: #e6c200; }
</style>





