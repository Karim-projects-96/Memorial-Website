<?php
// Supress MySQL connection warnings during port scan
mysqli_report(MYSQLI_REPORT_OFF);

$host = 'localhost';
$user = 'root';
$pass = ''; // Default XAMPP empty password
$db = 'memory_website';
$port = 3307; // Attempt previously noted active port

// Attempt fast direct connection first
$conn = @mysqli_connect($host, $user, $pass, $db, $port);

// If missing database or wrong port, trigger auto-setup and port discovery!
if (!$conn) {
    $ports = [3307, 3306, 3308, 8889];
    $passwords = ["", "root"];
    
    foreach ($ports as $p) {
        foreach ($passwords as $pw) {
            $conn = @mysqli_connect($host, $user, $pw, "", $p);
            if ($conn) {
                $pass = $pw;
                $port = $p;
                
                // 1. Create the database
                $conn->query("CREATE DATABASE IF NOT EXISTS memory_website");
                $conn->select_db("memory_website");
                
                // 2. Build the Full Site Schema
                $conn->query("CREATE TABLE IF NOT EXISTS photos (
                    id INT(11) AUTO_INCREMENT PRIMARY KEY,
                    filename VARCHAR(255) NOT NULL,
                    student_name VARCHAR(255) NOT NULL,
                    description TEXT,
                    status ENUM('pending', 'approved', 'rejected') DEFAULT 'pending',
                    uploaded_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                    likes INT(11) DEFAULT 0
                )");
                
                $conn->query("CREATE TABLE IF NOT EXISTS students (
                    id INT(11) AUTO_INCREMENT PRIMARY KEY,
                    username VARCHAR(255) NOT NULL UNIQUE,
                    password VARCHAR(255) NOT NULL,
                    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
                )");

                $conn->query("CREATE TABLE IF NOT EXISTS admins (
                    id INT(11) AUTO_INCREMENT PRIMARY KEY,
                    username VARCHAR(255) NOT NULL UNIQUE,
                    password VARCHAR(255) NOT NULL,
                    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
                )");

                $conn->query("CREATE TABLE IF NOT EXISTS settings (
                    setting_key VARCHAR(50) PRIMARY KEY,
                    setting_value VARCHAR(255)
                )");

                $conn->query("CREATE TABLE IF NOT EXISTS videos (
                    id INT(11) AUTO_INCREMENT PRIMARY KEY,
                    filename VARCHAR(255) NOT NULL,
                    uploaded_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
                )");

                $conn->query("CREATE TABLE IF NOT EXISTS guestbook (
                    id INT(11) AUTO_INCREMENT PRIMARY KEY,
                    student_name VARCHAR(255) NOT NULL,
                    message TEXT NOT NULL,
                    status ENUM('pending', 'approved', 'rejected') DEFAULT 'approved',
                    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
                )");

                // 3. Inject Default Admin if not created
                $check = $conn->query("SELECT id FROM admins WHERE username = 'admin'");
                if ($check && $check->num_rows == 0) {
                    $default_pass = password_hash('password', PASSWORD_DEFAULT);
                    $conn->query("INSERT INTO admins (username, password) VALUES ('admin', '$default_pass')");
                }

                // 4. Overwrite this db.php file with a simple cached connection to prevent slow port scanning on every website click!
                $cache_code = "<?php\n\$host='$host';\n\$user='$user';\n\$pass='$pass';\n\$db='$db';\n\$port=$port;\n\n\$conn=@mysqli_connect(\$host,\$user,\$pass,\$db,\$port);\nif(!\$conn){\n    die('Database connection failed. Please ensure MySQL is running.');\n}\n?>";
                @file_put_contents(__FILE__, $cache_code);
                
                break 2; // Break out of port scanning loop uniquely successfully
            }
        }
    }
}

if (!$conn) {
    die("CRITICAL DATABASE ERROR: Ensure XAMPP MySQL is active running.");
}
?>