<?php
require_once 'db.php';

// Add likes to photos
$sql1 = "ALTER TABLE photos ADD COLUMN IF NOT EXISTS likes INT(11) DEFAULT 0";
$conn->query($sql1);

// Add guestbook for farewell messages
$sql2 = "CREATE TABLE IF NOT EXISTS guestbook (
    id INT(11) AUTO_INCREMENT PRIMARY KEY,
    student_name VARCHAR(255) NOT NULL,
    message TEXT NOT NULL,
    status ENUM('pending', 'approved', 'rejected') DEFAULT 'approved',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)";
if ($conn->query($sql2)) {
    echo "Farewell features added successfully!\n";
} else {
    echo "Error: " . $conn->error . "\n";
}
?>
