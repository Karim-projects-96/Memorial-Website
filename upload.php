<?php
session_start();
require_once 'db.php';

if (!isset($_SESSION['student_logged_in'])) {
    header("Location: student_login.php");
    exit();
}

$upload_status = 'enabled';
$res = $conn->query("SELECT setting_value FROM settings WHERE setting_key = 'upload_status'");
if ($res && $res->num_rows > 0) {
    if ($row = $res->fetch_assoc()) {
        $upload_status = $row['setting_value'];
    }
}

if ($upload_status === 'disabled') {
    header("Location: student_dashboard.php?msg=upload_disabled");
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $student_name = $conn->real_escape_string($_SESSION['student_username']);
    $description = $conn->real_escape_string($_POST['description']);
    
    // File upload handling
    $target_dir = "uploads/";
    if (!file_exists($target_dir)) {
        mkdir($target_dir, 0777, true);
    }
    
    $file_extension = strtolower(pathinfo($_FILES["photo"]["name"], PATHINFO_EXTENSION));
    $allowed_extensions = array("jpg", "jpeg", "png", "gif", "webp");
    
    if(in_array($file_extension, $allowed_extensions)) {
        $new_filename = uniqid() . '.' . $file_extension;
        $target_file = $target_dir . $new_filename;
        
        if (move_uploaded_file($_FILES["photo"]["tmp_name"], $target_file)) {
            $sql = "INSERT INTO photos (filename, student_name, description, status) VALUES ('$new_filename', '$student_name', '$description', 'pending')";
            if ($conn->query($sql) === TRUE) {
                header("Location: student_dashboard.php?msg=success");
                exit();
            } else {
                header("Location: student_dashboard.php?msg=error");
                exit();
            }
        } else {
            header("Location: student_dashboard.php?msg=error");
            exit();
        }
    } else {
        header("Location: student_dashboard.php?msg=invalid");
        exit();
    }
} else {
    header("Location: student_dashboard.php");
    exit();
}
?>
