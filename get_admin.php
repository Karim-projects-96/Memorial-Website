<?php
require_once 'db.php';

// First, ensure the admins table exists (just in case)
$conn->query("CREATE TABLE IF NOT EXISTS admins (
    id INT(11) AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(255) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)");

$username = 'admin';
$password = 'password';
$hashed_password = password_hash($password, PASSWORD_DEFAULT);

$check = $conn->query("SELECT id FROM admins WHERE username = '$username'");

if ($check && $check->num_rows > 0) {
    // Admin exists, update the password to ensure the user can log in
    $conn->query("UPDATE admins SET password = '$hashed_password' WHERE username = '$username'");
    echo "Admin user already existed. Password has been reset to default.\n";
} else {
    // Create new admin
    $conn->query("INSERT INTO admins (username, password) VALUES ('$username', '$hashed_password')");
    echo "Admin user created successfully.\n";
}

echo "Username: $username\n";
echo "Password: $password\n";
?>
