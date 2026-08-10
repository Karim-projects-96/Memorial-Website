<?php
session_start();
require_once 'db.php';

if (!isset($_SESSION['student_logged_in'])) {
    header("Location: student_login.php");
    exit();
}

$username = $_SESSION['student_username'];

// Fetch upload status
$upload_status = 'enabled';
$res_status = $conn->query("SELECT setting_value FROM settings WHERE setting_key = 'upload_status'");
if ($res_status && $res_status->num_rows > 0) {
    if ($row_st = $res_status->fetch_assoc()) {
        $upload_status = $row_st['setting_value'];
    }
}

// Fetch the student's own requests to show them status
$sql = "SELECT * FROM photos WHERE student_name = '$username' ORDER BY uploaded_at DESC";
$result = $conn->query($sql);

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Dashboard - Memory Store</title>
    <link rel="stylesheet" href="style.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;800&display=swap" rel="stylesheet">
</head>
<body class="bg-light">

    <!-- Navigation Bar -->
    <nav class="navbar" style="margin-bottom: 20px;">
        <div class="nav-container">
            <a href="index.php" class="brand">✨ Memory Store</a>
            <div class="nav-links">
                <a href="index.php" class="nav-item">Home Gallery</a>
                <span style="color:#fff; margin:0 15px;">Welcome, <?php echo htmlspecialchars($username); ?></span>
                <a href="logout.php" class="btn btn-primary" style="padding: 5px 15px;">Logout</a>
            </div>
        </div>
    </nav>

    <div class="container section" style="margin-top: 50px;">
        <div class="dashboard-header" style="margin-bottom:30px;">
            <h2>Your Dashboard</h2>
            <p class="subtitle">Upload new memories and track their approval status.</p>
        </div>

        <div style="display:flex; flex-wrap:wrap; gap:30px;">
            <!-- Upload Box -->
            <div style="flex:1; min-width: 300px;">
                <div class="upload-form" style="margin:0;">
                    <h3 style="margin-bottom: 20px;">Upload a Photo</h3>
                    
                    <?php if($upload_status === 'disabled'): ?>
                        <div style="background: rgba(243, 156, 18, 0.1); border: 1px solid #f39c12; color: #d68910; padding: 20px; border-radius: 10px; text-align: center; font-weight: 600;">
                            ⚠️ Uploads are currently paused by the Administrator. Please try again later.
                        </div>
                    <?php else: ?>
                        <form action="upload.php" method="POST" enctype="multipart/form-data">
                            <div class="form-group">
                                <label for="description">Memory Description</label>
                                <textarea id="description" name="description" rows="3" placeholder="Tell us about this photo..." required></textarea>
                            </div>
                            <div class="form-group">
                                <label for="photo">Select Photo</label>
                                <input type="file" id="photo" name="photo" accept="image/*" required>
                            </div>
                            <button type="submit" class="btn btn-primary" style="width:100%">Submit for Approval</button>
                        </form>
                    <?php endif; ?>
                    
                    <?php
                    if(isset($_GET['msg'])) {
                        if($_GET['msg'] == 'success') echo "<p class='msg success' style='margin-top:10px;'>Photo submitted successfully!</p>";
                        if($_GET['msg'] == 'error') echo "<p class='msg error' style='margin-top:10px;'>Error uploading photo.</p>";
                        if($_GET['msg'] == 'invalid') echo "<p class='msg error' style='margin-top:10px;'>Invalid format.</p>";
                    }
                    ?>
                </div>
            </div>

            <!-- Status Box -->
            <div style="flex:2; min-width: 300px; background: white; padding: 30px; border-radius: 15px; box-shadow: var(--shadow);">
                <h3 style="margin-bottom: 20px;">Your Submissions</h3>
                <div style="overflow-x: auto;">
                    <table style="width:100%; border-collapse:collapse; text-align:left;">
                        <thead>
                            <tr style="border-bottom:2px solid #eee;">
                                <th style="padding:10px;">Photo</th>
                                <th style="padding:10px;">Description</th>
                                <th style="padding:10px;">Status</th>
                                <th style="padding:10px;">Date</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if ($result && $result->num_rows > 0): ?>
                                <?php while($row = $result->fetch_assoc()): ?>
                                    <tr style="border-bottom:1px solid #eee;">
                                        <td style="padding:10px;"><img src="uploads/<?php echo htmlspecialchars($row['filename']); ?>" style="width:60px; border-radius:5px;"></td>
                                        <td style="padding:10px;"><?php echo htmlspecialchars($row['description']); ?></td>
                                        <td style="padding:10px;">
                                            <?php
                                            $st = $row['status'];
                                            if ($st == 'approved') echo "<span style='color:green; font-weight:bold;'>Approved</span>";
                                            elseif ($st == 'rejected') echo "<span style='color:red; font-weight:bold;'>Rejected</span>";
                                            else echo "<span style='color:orange; font-weight:bold;'>Pending</span>";
                                            ?>
                                        </td>
                                        <td style="padding:10px;"><?php echo date('M d, Y', strtotime($row['uploaded_at'])); ?></td>
                                    </tr>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="4" style="padding:20px; text-align:center; color:#888;">You haven't uploaded any photos yet.</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

</body>
</html>
