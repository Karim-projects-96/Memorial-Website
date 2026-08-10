<?php
ini_set('session.gc_maxlifetime', 2592000);
session_set_cookie_params(2592000);
session_start();
require_once 'db.php';

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $action = $_POST['action'];
    $username = $conn->real_escape_string($_POST['username']);
    $password = $_POST['password'];

    if ($action == 'register') {
        // Simple hash for password
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        
        $sql = "INSERT INTO students (username, password) VALUES ('$username', '$hashed_password')";
        if ($conn->query($sql) === TRUE) {
            $success = "Registration successful! You may now log in.";
        } else {
            if ($conn->errno == 1062) { // duplicate entry
                $error = "Username already exists. Please choose another.";
            } else {
                $error = "Registration error: " . $conn->error;
            }
        }
    } elseif ($action == 'login') {
        $sql = "SELECT id, username, password FROM students WHERE username = '$username'";
        $result = $conn->query($sql);
        if ($result && $result->num_rows > 0) {
            $row = $result->fetch_assoc();
            if (password_verify($password, $row['password'])) {
                $_SESSION['student_logged_in'] = true;
                $_SESSION['student_username'] = $row['username'];
                header("Location: student_dashboard.php");
                exit();
            } else {
                $error = "Invalid password.";
            }
        } else {
            $error = "User not found.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Portal - Memory Store</title>
    <link rel="stylesheet" href="style.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;800&display=swap" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: 'Inter', sans-serif;
            margin: 0;
            padding: 0;
            overflow-x: hidden;
        }

        /* Ambient floating shapes */
        .ambient-shape { position: absolute; border-radius: 50%; filter: blur(60px); z-index: -1; opacity: 0.6; }
        .shape-1 { width: 300px; height: 300px; background: #ff9a9e; top: 10%; left: 10%; animation: float 6s ease-in-out infinite; }
        .shape-2 { width: 400px; height: 400px; background: #a18cd1; bottom: 10%; right: 5%; animation: float 8s ease-in-out infinite reverse; }

        @keyframes float {
            0% { transform: translateY(0px) rotate(0deg); }
            50% { transform: translateY(-30px) rotate(10deg); }
            100% { transform: translateY(0px) rotate(0deg); }
        }

        .auth-container {
            width: 100%;
            max-width: 900px;
            margin: 100px 20px;
            position: relative;
            z-index: 10;
        }

        .split-card { 
            display: flex; 
            background: rgba(255, 255, 255, 0.85); 
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
            border-radius: 20px; 
            box-shadow: 0 20px 50px rgba(0,0,0,0.1); 
            overflow: hidden;
            border: 1px solid rgba(255,255,255,0.5);
        }
        
        .split-card > div { 
            flex: 1; 
            padding: 50px 40px; 
        }
        
        .login-side { 
            background: rgba(255,255,255,0.9);
            position: relative;
        }

        .register-side {
            background: linear-gradient(135deg, var(--primary) 0%, #2a41b5 100%);
            color: white;
            display: flex;
            flex-direction: column;
            justify-content: center;
        }

        .register-side h3 { color: white; margin-bottom: 5px; }
        .register-side p { color: rgba(255,255,255,0.8); margin-bottom: 25px; font-size: 0.9rem; }

        .auth-title {
            font-size: 2rem;
            font-weight: 800;
            color: var(--dark);
            margin-bottom: 25px;
        }

        .floating-group {
            position: relative;
            margin-bottom: 25px;
        }

        .floating-group input {
            width: 100%;
            padding: 15px 15px 15px 15px;
            border: 2px solid #e1e5eb;
            border-radius: 10px;
            background: transparent;
            font-size: 1rem;
            transition: all 0.3s;
            color: inherit;
        }
        
        .register-side .floating-group input {
            border-color: rgba(255,255,255,0.3);
            color: white;
        }

        .register-side .floating-group input::placeholder {
            color: rgba(255,255,255,0.6);
        }

        .floating-group input:focus {
            outline: none;
            border-color: var(--primary);
            background: white;
        }
        
        .register-side .floating-group input:focus {
            border-color: white;
            background: rgba(255,255,255,0.1);
        }

        .floating-label {
            position: absolute;
            top: 50%;
            left: 15px;
            transform: translateY(-50%);
            color: #999;
            pointer-events: none;
            transition: 0.2s ease all;
            font-size: 1rem;
        }

        .floating-group input:focus ~ .floating-label,
        .floating-group input:not(:placeholder-shown) ~ .floating-label {
            top: -10px;
            left: 10px;
            font-size: 0.8rem;
            background: white;
            padding: 0 5px;
            color: var(--primary);
            border-radius: 4px;
        }

        .register-side .floating-group input:focus ~ .floating-label,
        .register-side .floating-group input:not(:placeholder-shown) ~ .floating-label {
            background: #344cb8;
            color: white;
        }

        .btn-modern {
            width: 100%;
            padding: 14px;
            border-radius: 10px;
            font-weight: 600;
            font-size: 1rem;
            cursor: pointer;
            transition: 0.3s;
            border: none;
        }

        .btn-modern.primary {
            background: var(--primary);
            color: white;
            box-shadow: 0 4px 15px rgba(67, 97, 238, 0.4);
        }

        .btn-modern.primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(67, 97, 238, 0.6);
        }

        .btn-modern.outline {
            background: transparent;
            color: white;
            border: 2px solid white;
        }

        .btn-modern.outline:hover {
            background: white;
            color: var(--primary);
        }

        .home-link {
            position: absolute;
            top: 20px;
            left: 20px;
            text-decoration: none;
            color: var(--text-muted);
            font-weight: 500;
            display: flex;
            align-items: center;
            transition: color 0.3s;
            z-index: 100;
        }
        
        .home-link:hover { color: var(--primary); }

        @media(max-width: 768px) { 
            .split-card { flex-direction: column; } 
            .shape-1, .shape-2 { display: none; }
        }
    </style>
</head>
<body>

    <a href="index.php" class="home-link">← Back to Main Page</a>

    <div class="ambient-shape shape-1"></div>
    <div class="ambient-shape shape-2"></div>

    <div class="auth-container">
        <?php if($error) echo "<div class='msg error' style='margin-bottom:20px; background:rgba(231,76,60,0.1); border:1px solid #e74c3c; color:#e74c3c; padding:15px; border-radius:10px; text-align:center;'>$error</div>"; ?>
        <?php if($success) echo "<div class='msg success' style='margin-bottom:20px; background:rgba(46,204,113,0.1); border:1px solid #2ecc71; color:#2ecc71; padding:15px; border-radius:10px; text-align:center;'>$success</div>"; ?>
        
        <div class="split-card">
            <!-- Login Side -->
            <div class="login-side">
                <h2 class="auth-title">Welcome Back</h2>
                <p style="color:#666; margin-bottom: 30px;">Log in to access your dashboard and upload new memories.</p>

                <form action="student_login.php" method="POST">
                    <input type="hidden" name="action" value="login">
                    
                    <div class="floating-group">
                        <input type="text" name="username" placeholder=" " required>
                        <span class="floating-label">Username</span>
                    </div>

                    <div class="floating-group">
                        <input type="password" name="password" placeholder=" " required>
                        <span class="floating-label">Password</span>
                    </div>

                    <button type="submit" class="btn-modern primary">Sign In</button>
                </form>
            </div>
            
            <!-- Register Side -->
            <div class="register-side">
                <h3>New Here?</h3>
                <p>Join the student portal to start adding your own photos to the global Memory Store.</p>
                
                <form action="student_login.php" method="POST">
                    <input type="hidden" name="action" value="register">
                    
                    <div class="floating-group" style="margin-top: 10px;">
                        <input type="text" name="username" placeholder=" " required>
                        <span class="floating-label" style="color: rgba(255,255,255,0.7);">Choose a Username</span>
                    </div>

                    <div class="floating-group">
                        <input type="password" name="password" placeholder=" " required minlength="6">
                        <span class="floating-label" style="color: rgba(255,255,255,0.7);">Create a Password</span>
                    </div>

                    <button type="submit" class="btn-modern outline">Create Account</button>
                </form>
            </div>
        </div>
    </div>

</body>
</html>
