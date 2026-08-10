<?php
session_start();
require_once 'db.php';

if (!isset($_SESSION['admin_logged_in'])) {
    header("Location: admin.php");
    exit();
}

// Check if Action is Delete Video
if (isset($_GET['action']) && $_GET['action'] == 'delete') {
    $res = $conn->query("SELECT filename FROM videos ORDER BY id DESC LIMIT 1");
    if ($res && $res->num_rows > 0) {
        $row = $res->fetch_assoc();
        $target_file = 'uploads/' . $row['filename'];
        if (file_exists($target_file)) {
            unlink($target_file);
        }
    }
    $conn->query("DELETE FROM videos");
    header("Location: admin.php?msg=video_deleted");
    exit();
}

// Handle Upload
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_FILES["video"])) {
    $target_dir = "uploads/";
    if (!file_exists($target_dir)) {
        mkdir($target_dir, 0777, true);
    }

    $file_extension = strtolower(pathinfo($_FILES["video"]["name"], PATHINFO_EXTENSION));
    $allowed_extensions = array("mp4", "webm", "ogg");
    
    if(in_array($file_extension, $allowed_extensions)) {
        
        // Remove old videos
        $res = $conn->query("SELECT filename FROM videos");
        if ($res && $res->num_rows > 0) {
            while($row = $res->fetch_assoc()){
                $old_file = $target_dir . $row['filename'];
                if(file_exists($old_file) && !is_dir($old_file)) { unlink($old_file); }
            }
            $conn->query("DELETE FROM videos");
        }
        
        $new_filename = 'hero_video_' . time() . '.' . $file_extension;
        $target_file = $target_dir . $new_filename;
        if (move_uploaded_file($_FILES["video"]["tmp_name"], $target_file)) {
            
            $sql = "INSERT INTO videos (filename) VALUES ('$new_filename')";
            $conn->query($sql);

            header("Location: admin.php?msg=video_success");
            exit();
        } else {
            header("Location: admin.php?msg=video_error");
            exit();
        }
    } else {
        header("Location: admin.php?msg=video_invalid");
        exit();
    }
}
header("Location: admin.php");
exit();
?>
