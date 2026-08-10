<?php
session_start();
require_once 'db.php';

if (!isset($_SESSION['admin_logged_in'])) {
    header("Location: admin.php");
    exit();
}

if (isset($_GET['action']) && $_GET['action'] === 'toggle_upload') {
    $current = 'enabled';
    $res = $conn->query("SELECT setting_value FROM settings WHERE setting_key = 'upload_status'");
    if ($res && $res->num_rows > 0) {
        $row = $res->fetch_assoc();
        $current = $row['setting_value'];
    }
    $new_status = ($current === 'enabled') ? 'disabled' : 'enabled';
    $conn->query("INSERT INTO settings (setting_key, setting_value) VALUES ('upload_status', '$new_status') ON DUPLICATE KEY UPDATE setting_value='$new_status'");
    header("Location: admin.php?msg=status_updated");
    exit();
}

if (isset($_GET['id']) && isset($_GET['action'])) {
    $id = (int)$_GET['id'];
    $action = $_GET['action'];
    
    if ($action === 'approve') {
        $status = 'approved';
        $msg = 'approved';
        $sql = "UPDATE photos SET status = '$status' WHERE id = $id";
        $conn->query($sql);
    } elseif ($action === 'reject') {
        $status = 'rejected';
        $msg = 'rejected';
        
        $sql = "SELECT filename FROM photos WHERE id = $id";
        $result = $conn->query($sql);
        if ($result && $result->num_rows > 0) {
            $row = $result->fetch_assoc();
            $filepath = 'uploads/' . $row['filename'];
            if (file_exists($filepath)) {
                unlink($filepath);
            }
        }
        $sql = "UPDATE photos SET status = '$status' WHERE id = $id";
        $conn->query($sql);
    } elseif ($action === 'delete') {
        $msg = 'deleted';
        
        $sql = "SELECT filename FROM photos WHERE id = $id";
        $result = $conn->query($sql);
        if ($result && $result->num_rows > 0) {
            $row = $result->fetch_assoc();
            $filepath = 'uploads/' . $row['filename'];
            if (file_exists($filepath)) {
                unlink($filepath);
            }
        }
        $sql = "DELETE FROM photos WHERE id = $id";
        $conn->query($sql);
    } else {
        header("Location: admin.php?msg=error");
        exit();
    }
    
    header("Location: admin.php?msg=$msg");
} else {
    header("Location: admin.php");
}
exit();
?>
