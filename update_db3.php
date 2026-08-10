<?php
require_once 'db.php';

$sql = "CREATE TABLE IF NOT EXISTS videos (
    id INT(11) AUTO_INCREMENT PRIMARY KEY,
    filename VARCHAR(255) NOT NULL,
    uploaded_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)";
if ($conn->query($sql)) {
    echo "Videos table created successfully.\n";
} else {
    echo "Error: " . $conn->error . "\n";
}
?>
