<?php
require_once 'db.php';

$sql = "CREATE TABLE IF NOT EXISTS settings (
    setting_key VARCHAR(50) PRIMARY KEY,
    setting_value VARCHAR(255)
)";
if ($conn->query($sql)) {
    echo "Settings table created successfully.\n";
} else {
    echo "Error: " . $conn->error . "\n";
}
?>
