<?php
require_once 'db.php';

$sql = "CREATE TABLE IF NOT EXISTS admins (
    id INT(11) AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(255) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)";

if ($conn->query($sql)) {
    echo "Admins table created successfully.\n";
    
    // Insert default admin if not exists
    $default_user = 'admin';
    $default_pass = password_hash('password', PASSWORD_DEFAULT);
    
    $check = $conn->query("SELECT id FROM admins WHERE username = 'admin'");
    if ($check->num_rows == 0) {
        $insert = "INSERT INTO admins (username, password) VALUES ('$default_user', '$default_pass')";
        if ($conn->query($insert)) {
            echo "Default admin account created. (username: admin, password: password)\n";
        }
    }
} else {
    echo "Error: " . $conn->error . "\n";
}
?>
