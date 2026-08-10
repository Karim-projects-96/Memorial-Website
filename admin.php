<?php
ini_set('session.gc_maxlifetime', 2592000);
session_set_cookie_params(2592000);
session_start();
require_once 'db.php';

// Database Admin Login Check
if (isset($_POST['login'])) {
    $input_username = $conn->real_escape_string($_POST['username']);
    $input_password = $_POST['password'];

    $hashed_input = md5($input_password);

    $sql = "SELECT id, username, password FROM admins WHERE username = '$input_username'";
    $result = $conn->query($sql);
    
    if ($result && $result->num_rows > 0) {
        $row = $result->fetch_assoc();
        if ($hashed_input === $row['password']) {
            $_SESSION['admin_logged_in'] = true;
            $_SESSION['admin_username'] = $row['username'];
        } else {
            $login_error = "Invalid password!";
        }   
    } else {
        $login_error = "Admin account not found!";
    }
}

if (isset($_GET['logout'])) {
    session_destroy();
    header("Location: admin.php");
    exit();
}

$hero_video_exists = false;
if (isset($_SESSION['admin_logged_in'])) {
    $res = $conn->query("SELECT filename FROM videos LIMIT 1");
    if ($res && $res->num_rows > 0) {
        $hero_video_exists = true;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Panel - Memory Store</title>
    <link rel="stylesheet" href="style.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;800&display=swap" rel="stylesheet">
    <style>
        body { background-color: #f4f7f6; }
        
        /* Dashboard Container */
        .admin-container { max-width: 1000px; margin: 50px auto; padding: 30px; background: white; border-radius: 12px; box-shadow: 0 10px 25px rgba(0,0,0,0.05); }
        .admin-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; }
        
        /* Table Styles */
        .admin-table { width: 100%; border-collapse: collapse; margin-bottom: 30px; background: white; box-shadow: 0 4px 6px rgba(0,0,0,0.02); border-radius: 8px; overflow: hidden; }
        .admin-table th { background: #fdfdfd; padding: 15px; border-bottom: 2px solid #eee; text-align: left; color: #555; }
        .admin-table td { padding: 15px; border-bottom: 1px solid #f1f1f1; text-align: left; }
        .admin-table tr:last-child td { border-bottom: none; }
        .admin-table img { max-width: 100px; border-radius: 6px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); }
        
        /* Buttons */
        .action-btn { padding: 8px 15px; border: none; border-radius: 6px; cursor: pointer; color: white; text-decoration: none; font-size: 13px; margin-right: 5px; font-weight: 600; transition: transform 0.2s; display: inline-block; }
        .action-btn:hover { transform: translateY(-2px); }
        .btn-approve { background-color: #2ecc71; box-shadow: 0 4px 10px rgba(46, 204, 113, 0.3); }
        .btn-reject { background-color: #f39c12; box-shadow: 0 4px 10px rgba(243, 156, 18, 0.3); }
        .btn-delete { background-color: #e74c3c; box-shadow: 0 4px 10px rgba(231, 76, 60, 0.3); }
        
        .section-title { padding-bottom: 15px; border-bottom: 2px solid #eee; margin-bottom: 20px; margin-top: 40px; color: #2b2d42; }
        
        /* Video Upload Box */
        .video-box { background: linear-gradient(135deg, #fdfdfd 0%, #f4f7f6 100%); padding: 25px; border-radius: 12px; margin-bottom: 30px; border: 1px solid #e1e5eb; }
        .video-box input[type="file"] { background: white; padding: 10px; border-radius: 6px; border: 1px solid #ddd; }
        
        /* Gorgeous Admin Login Wrapper */
        .admin-login-wrapper {
            position: fixed; top: 0; left: 0; width: 100%; min-height: 100vh;
            display: flex; align-items: center; justify-content: center;
            background: linear-gradient(135deg, #091E3A 0%, #2F80ED 50%, #2D9EE0 100%);
            z-index: 10000;
        }
        .admin-login-box {
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
            border: 1px solid rgba(255, 255, 255, 0.2);
            padding: 50px 40px;
            border-radius: 20px;
            box-shadow: 0 20px 50px rgba(0,0,0,0.3);
            width: 100%;
            max-width: 420px;
            text-align: center;
            color: white;
        }
        .admin-login-box h2 { color: white; margin-bottom: 10px; font-size: 2rem; }
        .admin-login-box p { color: rgba(255,255,255,0.7); margin-bottom: 30px; }
        .admin-login-box input {
            width: 100%;
            padding: 15px;
            margin-bottom: 15px;
            background: rgba(255,255,255,0.05);
            border: 1px solid rgba(255,255,255,0.2);
            color: white;
            border-radius: 10px;
            font-size: 1rem;
            transition: all 0.3s;
        }
        .admin-login-box input::placeholder { color: rgba(255,255,255,0.5); }
        .admin-login-box input:focus { outline: none; border-color: white; background: rgba(255,255,255,0.15); }
        .admin-login-box button {
            width: 100%;
            padding: 15px;
            background: white;
            color: #091E3A;
            border: none;
            border-radius: 10px;
            font-size: 1.1rem;
            font-weight: 800;
            cursor: pointer;
            transition: all 0.3s;
            margin-top: 10px;
        }
        .admin-login-box button:hover { transform: translateY(-3px); box-shadow: 0 10px 20px rgba(0,0,0,0.2); }
        .admin-login-box a { color: rgba(255,255,255,0.6); text-decoration: none; transition: 0.3s; }
        .admin-login-box a:hover { color: white; }
        
        /* Dashboard Container Mobile Tweaks */
        .admin-container { padding: 15px; margin-top: 80px; width: 100%; border-radius: 0; }
        .table-responsive-wrapper {
            width: 100%;
            overflow-x: auto;
            -webkit-overflow-scrolling: touch;
            background: white;
            border-radius: 8px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.02);
            margin-bottom: 30px;
        }
        .admin-table { margin-bottom: 0; min-width: 600px; /* Forces sideways scroll inside constraint */ }
        
        @media (max-width: 768px) {
            .navbar .nav-links { flex-direction: column; width: 100%; gap: 10px; margin-top: 15px; }
            .nav-item { display: block; margin: 0; text-align: center; }
            .btn-primary { width: 100%; display: block; margin-left: 0 !important; }
        }
    </style>
</head>
<body>

<?php if (!isset($_SESSION['admin_logged_in'])): ?>
    <div class="admin-login-wrapper">
        <div class="admin-login-box">
            <h2>Admin Sign In</h2>
            <p>Secure access strictly for memory store staff.</p>
            <?php if(isset($login_error)) echo "<div style='background:rgba(231,76,60,0.2); border:1px solid #e74c3c; color:#ffbaba; padding:12px; border-radius:8px; margin-bottom:20px;'>$login_error</div>"; ?>
            <form method="POST">
                <input type="text" name="username" placeholder="Admin Username" required>
                <input type="password" name="password" placeholder="Admin Password" required>
                <button type="submit" name="login">Authorize</button>
            </form>
            <div style="margin-top: 25px;"><a href="index.php">← Return to Website</a></div>
        </div>
    </div>
<?php else: ?>
    <!-- Priority Dashboard Top Navbar -->
    <nav class="navbar" style="margin-bottom: 20px;">
        <div class="nav-container">
            <a href="admin.php" class="brand">✨ Admin Storage</a>
            <div class="nav-links">
                <a href="index.php" class="nav-item" target="_blank">Live Site</a>
                <a href="admin.php?logout=1" class="btn btn-primary" style="padding: 8px 20px; font-size: 14px; margin-left: 15px;">Secure Logout</a>
            </div>
        </div>
    </nav>

    <div class="admin-container" style="margin-top: 120px;">
        <?php
        if(isset($_GET['msg'])) {
            if($_GET['msg'] == 'approved') echo "<div style='background:#f0fff4; border:1px solid #c6f6d5; color:#2f855a; padding:15px; border-radius:8px; margin-bottom:20px;'>✅ Photo successfully approved!</div>";
            if($_GET['msg'] == 'rejected') echo "<div style='background:#fffaf0; border:1px solid #feebc8; color:#c05621; padding:15px; border-radius:8px; margin-bottom:20px;'>Photo request rejected.</div>";
            if($_GET['msg'] == 'deleted') echo "<div style='background:#fff5f5; border:1px solid #fed7d7; color:#c53030; padding:15px; border-radius:8px; margin-bottom:20px;'>🗑 Photo permanently deleted.</div>";
            if($_GET['msg'] == 'error') echo "<div style='background:#fff5f5; border:1px solid #fed7d7; color:#c53030; padding:15px; border-radius:8px; margin-bottom:20px;'>An error occurred.</div>";
            if($_GET['msg'] == 'video_success') echo "<div style='background:#f0fff4; border:1px solid #c6f6d5; color:#2f855a; padding:15px; border-radius:8px; margin-bottom:20px;'>✅ Hero video updated in Database!</div>";
            if($_GET['msg'] == 'video_deleted') echo "<div style='background:#fff5f5; border:1px solid #fed7d7; color:#c53030; padding:15px; border-radius:8px; margin-bottom:20px;'>Hero video removed from Database.</div>";
            if($_GET['msg'] == 'video_error') echo "<div style='background:#fff5f5; border:1px solid #fed7d7; color:#c53030; padding:15px; border-radius:8px; margin-bottom:20px;'>Error processing video.</div>";
            if($_GET['msg'] == 'video_invalid') echo "<div style='background:#fff5f5; border:1px solid #fed7d7; color:#c53030; padding:15px; border-radius:8px; margin-bottom:20px;'>Invalid format. Only MP4/WebM/OGG.</div>";
        }
        $upload_status = 'enabled';
        $res = $conn->query("SELECT setting_value FROM settings WHERE setting_key = 'upload_status'");
        if ($res && $res->num_rows > 0) {
            $row = $res->fetch_assoc();
            $upload_status = $row['setting_value'];
        }
        ?>

        <!-- Admin Global Controls -->
        <div style="background: white; border: 2px solid #e1e5eb; padding: 20px; border-radius: 12px; margin-bottom: 25px; display:flex; justify-content:space-between; align-items:center;">
            <div>
                <h3 style="margin-bottom: 5px; color: #333;">Global Website Controls</h3>
                <p style="color: #666; font-size: 14px;">Allow or restrict students from uploading new photos.</p>
            </div>
            <div>
                <?php if($upload_status === 'enabled'): ?>
                    <span style="margin-right: 15px; font-weight: bold; color: green;">Status: Enabled</span>
                    <a href="admin_action.php?action=toggle_upload" class="action-btn btn-reject" style="padding: 10px 15px;">Pause Uploads</a>
                <?php else: ?>
                    <span style="margin-right: 15px; font-weight: bold; color: red;">Status: Disabled</span>
                    <a href="admin_action.php?action=toggle_upload" class="action-btn btn-approve" style="padding: 10px 15px;">Resume Uploads</a>
                <?php endif; ?>
            </div>
        </div>

        <!-- Video Management Section -->
        <div class="video-box">
            <h3 style="margin-bottom: 15px; color: #333;">Website Hero Video</h3>
            <div style="display:flex; justify-content:space-between; align-items:center; flex-wrap: wrap; gap: 15px;">
                <form action="admin_video_upload.php" method="POST" enctype="multipart/form-data" style="display:flex; gap:10px; align-items:center;">
                    <input type="file" name="video" accept="video/mp4,video/webm,video/ogg" required>
                    <button type="submit" class="action-btn btn-approve" style="padding: 10px 20px; height: 100%;">Upload to Database</button>
                </form>
                <?php if($hero_video_exists): ?>
                    <a href="admin_video_upload.php?action=delete" class="action-btn btn-delete" style="padding: 10px 20px;" onclick="return confirm('Delete video from database entirely?');">🗑 Delete DB Video</a>
                <?php else: ?>
                    <span style="color:#777; font-size:14px; font-style:italic;">Showing default fallback video.</span>
                <?php endif; ?>
            </div>
        </div>

        <!-- Pending Photos -->
        <h3 class="section-title">Pending Approvals</h3>
        <?php
        $sql = "SELECT * FROM photos WHERE status = 'pending' ORDER BY uploaded_at DESC";
        $result = $conn->query($sql);
        ?>
        <div class="table-responsive-wrapper">
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>Media</th>
                        <th>Student Details</th>
                        <th>Description</th>
                        <th>Date</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($result && $result->num_rows > 0): ?>
                        <?php while($row = $result->fetch_assoc()): ?>
                            <tr>
                                <td><a href="uploads/<?php echo htmlspecialchars($row['filename']); ?>" target="_blank"><img src="uploads/<?php echo htmlspecialchars($row['filename']); ?>" alt="Photo"></a></td>
                                <td><strong><?php echo htmlspecialchars($row['student_name']); ?></strong></td>
                                <td><?php echo htmlspecialchars($row['description']); ?></td>
                                <td><?php echo date('M d, Y h:i A', strtotime($row['uploaded_at'])); ?></td>
                                <td style="white-space:nowrap;">
                                    <a href="admin_action.php?id=<?php echo $row['id']; ?>&action=approve" class="action-btn btn-approve">Approve</a>
                                    <a href="admin_action.php?id=<?php echo $row['id']; ?>&action=reject" class="action-btn btn-reject">Reject</a>
                                    <a href="admin_action.php?id=<?php echo $row['id']; ?>&action=delete" class="action-btn btn-delete" onclick="return confirm('Delete completely?');">Delete</a>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr><td colspan="5" style="text-align:center; padding:30px; color:#aaa;">No pending requests.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <!-- All Other Photos -->
        <h3 class="section-title">Published & Rejected Media</h3>
        <?php
        $sql_all = "SELECT * FROM photos WHERE status != 'pending' ORDER BY uploaded_at DESC";
        $result_all = $conn->query($sql_all);
        ?>
        <div class="table-responsive-wrapper">
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>Media</th>
                        <th>Student Name</th>
                        <th>Status</th>
                        <th>Date Handled</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($result_all && $result_all->num_rows > 0): ?>
                        <?php while($row = $result_all->fetch_assoc()): ?>
                            <tr>
                                <td><a href="uploads/<?php echo htmlspecialchars($row['filename']); ?>" target="_blank"><img src="uploads/<?php echo htmlspecialchars($row['filename']); ?>" alt="Photo"></a></td>
                                <td><?php echo htmlspecialchars($row['student_name']); ?></td>
                                <td>
                                    <?php if($row['status'] == 'approved'): ?>
                                        <span style="color: green; font-weight: bold; background: #e6fffa; padding: 4px 8px; border-radius: 4px; font-size:12px;">PUBLISHED</span>
                                    <?php else: ?>
                                        <span style="color: #c53030; font-weight: bold; background: #fff5f5; padding: 4px 8px; border-radius: 4px; font-size:12px;">REJECTED</span>
                                    <?php endif; ?>
                                </td>
                                <td><?php echo date('M d, Y', strtotime($row['uploaded_at'])); ?></td>
                                <td>
                                    <a href="admin_action.php?id=<?php echo $row['id']; ?>&action=delete" class="action-btn btn-delete" onclick="return confirm('Permanently delete this photo from the database?');">🗑 Delete</a>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr><td colspan="5" style="text-align:center; padding:30px; color:#aaa;">No processed media available.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
<?php endif; ?>

</body>
</html>
