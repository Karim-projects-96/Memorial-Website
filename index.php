<?php
ini_set('session.gc_maxlifetime', 2592000);
session_set_cookie_params(2592000);
session_start();
require_once 'db.php';

// Handle Likes
if (isset($_POST['like_photo_id'])) {
    $like_id = (int)$_POST['like_photo_id'];
    // Avoid double counting simple via session (rudimentary)
    if (!isset($_SESSION['liked_'.$like_id])) {
        $conn->query("UPDATE photos SET likes = likes + 1 WHERE id = $like_id");
        $_SESSION['liked_'.$like_id] = true;
    }
    header("Location: index.php#gallery");
    exit();
}

// Handle Farewell Message submission
if (isset($_POST['post_message']) && isset($_SESSION['student_logged_in'])) {
    $msg = $conn->real_escape_string($_POST['message']);
    $usr = $conn->real_escape_string($_SESSION['student_username']);
    $conn->query("INSERT INTO guestbook (student_name, message) VALUES ('$usr', '$msg')");
    header("Location: index.php#guestbook");
    exit();
}

// Fetch approved photos
$sql = "SELECT * FROM photos WHERE status = 'approved' ORDER BY uploaded_at DESC";
$result = $conn->query($sql);

// Fetch guestbook
$g_res = $conn->query("SELECT * FROM guestbook WHERE status = 'approved' ORDER BY created_at DESC");

// Find admin video from database
$hero_video_url = "https://www.w3schools.com/html/mov_bbb.mp4"; // fallback
$video_res = $conn->query("SELECT filename FROM videos ORDER BY id DESC LIMIT 1");
if ($video_res && $video_res->num_rows > 0) {
    if ($row = $video_res->fetch_assoc()) {
        $hero_video_url = 'uploads/' . $row['filename'] . '?v=' . time(); // cache bust
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Farewell Memory Store</title>
    <link rel="stylesheet" href="style.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600;800&family=Playfair+Display:ital,wght@1,600&display=swap" rel="stylesheet">
    <style>
        .farewell-banner { text-align: center; padding: 40px 20px; background: linear-gradient(135deg, #1e3c72 0%, #2a5298 100%); color: white; margin-bottom: 20px;}
        .farewell-title { font-family: 'Playfair Display', serif; font-size: 2.5rem; margin-bottom: 10px; }
        
        .like-btn { background: rgba(255,255,255,0.2); border: none; padding: 5px 10px; border-radius: 5px; color: white; cursor: pointer; backdrop-filter: blur(5px); margin-top: 10px; transition: 0.3s; }
        .like-btn:hover { background: rgba(255,50,50,0.8); }
        
        /* Wall of Memories */
        .msg-board { display: grid; grid-template-columns: repeat(auto-fill, minmax(250px, 1fr)); gap: 20px; margin-top: 30px; }
        .msg-note { background: #fffcf2; border-left: 4px solid #f39c12; padding: 20px; box-shadow: 0 4px 10px rgba(0,0,0,0.05); border-radius: 4px; font-family: 'Inter', sans-serif; font-style: italic; }
        .msg-note strong { font-style: normal; color: #d35400; display: block; margin-top: 10px; font-size: 0.9rem;}
        
        /* New Sleek Navbar Text */
        .navbar .brand { font-family: 'Playfair Display', serif; font-size: 1.8rem; letter-spacing: 2px;}
    </style>
    <!-- AOS Animation Library -->
    <link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">
</head>
<body style="position: relative;">

    <!-- Cinematic Splash Preloader -->
    <div id="splash-screen" style="position: fixed; z-index: 10000; top: 0; left: 50%; transform: translateX(-50%); width: 100%; max-width: 480px; height: 100%; background: #0b0f19; color: white; display: flex; flex-direction: column; align-items: center; justify-content: center; font-family: 'Playfair Display', serif; transition: opacity 1s ease, visibility 1s; padding: 0 20px; text-align: center; box-shadow: 0 0 50px rgba(0,0,0,0.8);">
        <h1 style="font-size: clamp(1.8rem, 8vw, 2.5rem); letter-spacing: 1px; margin-bottom: 10px;">Hold on to the memories...</h1>
        <p style="font-family: 'Inter', sans-serif; opacity: 0.6; margin-top: 10px; font-size: 0.9rem;">Fetching the archive</p>
    </div>

    <!-- Image Lightbox Modal -->
    <div id="lightbox" style="position: fixed; top: 0; left: 50%; transform: translateX(-50%); width: 100%; max-width: 480px; height: 100%; background: rgba(0,0,0,0.95); z-index: 9995; display: none; align-items: center; justify-content: center; backdrop-filter: blur(15px); cursor: zoom-out;" onclick="this.style.display='none'">
        <img id="lightbox-img" style="max-width: 95%; max-height: 80vh; border-radius: 10px; box-shadow: 0 0 40px rgba(0,0,0,0.8);" src="">
    </div>

    <!-- Ultra Mobile Creative Bottom Navigation -->
    <div class="mobile-bottom-nav" id="app-dock" style="opacity: 0; transform: translate(-50%, 100px); transition: all 0.8s cubic-bezier(0.34, 1.56, 0.64, 1);">
        <a href="#gallery" class="mobile-nav-item" id="nav-photos">Photos</a>
        <a href="#guestbook" class="mobile-nav-item" id="nav-messages">Messages</a>
        <?php if (isset($_SESSION['student_logged_in'])): ?>
            <a href="student_dashboard.php" class="mobile-nav-item">Dashboard</a>
            <a href="logout.php" class="mobile-nav-item" style="color:#e74c3c;">Log Out</a>
        <?php else: ?>
            <a href="student_login.php" class="mobile-nav-item">Login</a>
        <?php endif; ?>
    </div>

    <!-- Stunning Inset Video Platform Container -->
    <div class="hero-wrapper">
        <div class="hero-inset">
            <video autoplay muted loop playsinline preload="auto" id="hero-video">
                <source src="<?php echo htmlspecialchars($hero_video_url); ?>" type="video/mp4">
            </video>
            
            <div class="scroll-indicator" id="scroll-prompt">
                <span>Unlock Memories</span>
                <div class="arrow">↓</div>
            </div>
        </div>
    </div>
    
    <div class="farewell-banner" data-aos="zoom-in" data-aos-duration="1000">
        <h2 class="farewell-title">"Thanks for the Memories"</h2>
        <p>A digital vault for our final moments together before moving on to exactly what's next.</p>
    </div>

    <!-- Gallery Section -->
    <section id="gallery" class="section bg-light" style="padding-top: 20px;">
        <div class="container">
            <h2 style="font-family:'Playfair Display', serif;" data-aos="fade-right">Photo Archive</h2>
            <div class="gallery-grid">
                <?php
                if ($result && $result->num_rows > 0) {
                    $delay = 0;
                    while($row = $result->fetch_assoc()) {
                        echo '<div class="gallery-item" data-aos="fade-up" data-aos-delay="'.($delay % 400).'">';
                        echo '<img class="memory-img" src="uploads/' . htmlspecialchars($row['filename']) . '" alt="Memory">';
                        echo '<div class="overlay">';
                        echo '<h3>' . htmlspecialchars($row['student_name']) . '</h3>';
                        echo '<p>' . htmlspecialchars($row['description']) . '</p>';
                        
                        // Like Button Form (AJAX Ready)
                        echo '<form method="POST" style="display:inline-block;" class="ajax-like-form" data-id="'.$row['id'].'">';
                        echo '<input type="hidden" name="like_photo_id" value="'.$row['id'].'">';
                        echo '<button type="submit" class="like-btn" id="btn-like-'.$row['id'].'">❤️ <span class="like-count">' . (int)$row['likes'] . '</span> Appreciations</button>';
                        echo '</form>';
                        
                        echo '</div>';
                        echo '</div>';
                        $delay += 100;
                    }
                } else {
                    echo "<p class='no-photos'>No memories have been archived yet.</p>";
                }
                ?>
            </div>
        </div>
    </section>

    <!-- Farewell Guestbook Section -->
    <section id="guestbook" class="section" style="background: white;">
        <div class="container">
            <h2 style="font-family:'Playfair Display', serif; color: #1e3c72;" data-aos="fade-right">The Farewell Wall</h2>
            <p style="color:#666; margin-bottom: 20px;" data-aos="fade-in">Leave a parting thought or your favorite memory here for everyone to see.</p>
            
            <?php if(isset($_SESSION['student_logged_in'])): ?>
                <form method="POST" style="background: #fdfdfd; padding:20px; border:1px solid #eee; border-radius: 8px; margin-bottom: 40px; max-width: 600px;" data-aos="zoom-in">
                    <textarea name="message" rows="3" required placeholder="Write your farewell message here..." style="width: 100%; padding:10px; border:1px solid #ccc; border-radius:4px; margin-bottom: 10px; font-family: 'Inter', sans-serif; resize:vertical;"></textarea>
                    <button type="submit" name="post_message" class="btn btn-primary" style="padding:10px 25px;">Pin to Wall</button>
                </form>
            <?php else: ?>
                <div style="margin-bottom: 40px; padding: 15px; background: #fdfdfd; border-radius: 8px; border: 1px dashed #ccc; color: #555; max-width: 600px;" data-aos="fade-in">
                    <a href="student_login.php" style="color: #2a5298; font-weight:bold;">Log in as a student</a> to leave a farewell message.
                </div>
            <?php endif; ?>

            <div class="msg-board">
                <?php
                if ($g_res && $g_res->num_rows > 0) {
                    $delay = 0;
                    while($grow = $g_res->fetch_assoc()) {
                        echo '<div class="msg-note" data-aos="fade-up" data-aos-delay="'.($delay % 500).'">"' . htmlspecialchars($grow['message']) . '"';
                        echo '<strong>- ' . htmlspecialchars($grow['student_name']) . '</strong></div>';
                        $delay += 100;
                    }
                } else {
                    echo "<p style='color:#999; font-style:italic;'>The wall is completely empty. Be the first to leave a memory!</p>";
                }
                ?>
            </div>
        </div>
    </section>

    <!-- Join the Vault CTA (Login/Register) -->
    <section class="section" style="background: linear-gradient(135deg, #0b0f19 0%, #1a2542 100%); color: white; text-align: center; padding: 60px 20px;" data-aos="fade-up" data-aos-duration="1000">
        <h2 style="font-family:'Playfair Display', serif; font-size: 2.2rem; color: #fff; margin-bottom: 10px;">Ready to leave your mark?</h2>
        <p style="opacity: 0.8; margin-bottom: 30px; font-size: 1rem; max-width: 400px; margin-left: auto; margin-right: auto;">Join the private portal directly to upload your own photos and pin personal messages to the wall.</p>
        
        <?php if (!isset($_SESSION['student_logged_in'])): ?>
            <div style="display: flex; gap: 15px; justify-content: center; flex-wrap: wrap;">
                <a href="student_login.php" class="btn btn-primary" style="padding: 12px 30px; border-radius: 30px; font-weight: 700; flex: 1; max-width: 200px;">Student Login</a>
                <a href="student_login.php" class="btn" style="padding: 12px 30px; border-radius: 30px; background: transparent; border: 2px solid white; color: white; flex: 1; max-width: 200px;">Register Now</a>
            </div>
        <?php else: ?>
            <a href="student_dashboard.php" class="btn btn-primary" style="padding: 12px 30px; border-radius: 30px; font-weight: 700; display: inline-block;">Go to Dashboard</a>
        <?php endif; ?>
    </section>

    <footer style="background: #111; padding: 40px 20px; text-align: center;">
        <p>&copy; <?php echo date('Y'); ?> Farewell Memory Store. Built for the graduating batch.</p>
        <div style="margin-top: 20px; font-size: 0.85rem;">
            <?php if (isset($_SESSION['student_logged_in'])): ?>
                <a href="student_dashboard.php" style="color: #666; text-decoration: none; margin: 0 10px; transition: color 0.3s;" onmouseover="this.style.color='#fff'" onmouseout="this.style.color='#666'">Student Dashboard</a>
                <a href="logout.php" style="color: #666; text-decoration: none; margin: 0 10px; transition: color 0.3s;" onmouseover="this.style.color='#fff'" onmouseout="this.style.color='#666'">Sign Out</a>
            <?php else: ?>
                <a href="student_login.php" style="color: #666; text-decoration: none; margin: 0 10px; transition: color 0.3s;" onmouseover="this.style.color='#fff'" onmouseout="this.style.color='#666'">Student Access</a>
                <a href="admin.php" style="color: #666; text-decoration: none; margin: 0 10px; transition: color 0.3s;" onmouseover="this.style.color='#fff'" onmouseout="this.style.color='#666'">Admin Access</a>
            <?php endif; ?>
        </div>
    </footer>

    <script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
    <script>
        AOS.init({
            once: true,
            offset: 50,
            duration: 800,
            easing: 'ease-out-cubic',
        });
        
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function (e) {
                e.preventDefault();
                document.querySelector(this.getAttribute('href')).scrollIntoView({
                    behavior: 'smooth'
                });
            });
        });
        
        window.addEventListener('scroll', function() {
            let prompt = document.getElementById('scroll-prompt');
            if(prompt) {
                if (window.scrollY > 150) {
                    prompt.style.opacity = '0';
                } else {
                    prompt.style.opacity = '1';
                }
            }
        });

        // AJAX Seamless Like System
        document.querySelectorAll('.ajax-like-form').forEach(form => {
            form.addEventListener('submit', function(e) {
                e.preventDefault(); // Prevent page reload
                let formData = new FormData(this);
                let btnId = this.getAttribute('data-id');
                let countSpan = document.querySelector('#btn-like-' + btnId + ' .like-count');
                let btn = document.querySelector('#btn-like-' + btnId);
                
                // Optimistic UI Update & Animation
                let currentLikes = parseInt(countSpan.textContent);
                countSpan.textContent = currentLikes + 1;
                btn.style.transform = "scale(1.2)";
                btn.style.background = "rgba(255, 50, 50, 0.9)";
                setTimeout(() => { btn.style.transform = "scale(1)"; }, 200);

                // Background Fetch
                fetch('index.php', {
                    method: 'POST',
                    body: formData
                });
            });
        });

        // Cinematic Splash Screen Fade & Dock Entrance
        window.addEventListener('load', function() {
            setTimeout(() => {
                let splash = document.getElementById('splash-screen');
                splash.style.opacity = '0';
                splash.style.visibility = 'hidden';
                
                // Sweep the bottom dock in gracefully
                let dock = document.getElementById('app-dock');
                if (dock) {
                    dock.style.opacity = '1';
                    dock.style.transform = 'translate(-50%, 0)';
                }
            }, 1200);
        });

        // ScrollSpy Dynamic Navigation Highlighting
        window.addEventListener('scroll', function() {
            let scrollPos = window.scrollY || window.pageYOffset;
            let galleryTop = document.getElementById('gallery').offsetTop - 100;
            let guestbookTop = document.getElementById('guestbook').offsetTop - 200;
            
            let btnPhotos = document.getElementById('nav-photos');
            let btnMessages = document.getElementById('nav-messages');
            
            if (btnPhotos && btnMessages) {
                // Reset styling
                btnPhotos.style.background = 'transparent'; btnPhotos.style.color = '#333';
                btnMessages.style.background = 'transparent'; btnMessages.style.color = '#333';
                
                // Highlight based on current scroll position
                if (scrollPos >= guestbookTop) {
                    btnMessages.style.background = 'var(--primary)'; btnMessages.style.color = 'white';
                } else if (scrollPos >= galleryTop) {
                    btnPhotos.style.background = 'var(--primary)'; btnPhotos.style.color = 'white';
                }
            }
        });

        // Lightbox Interactive Zoom
        document.querySelectorAll('.memory-img').forEach(img => {
            img.style.cursor = "zoom-in";
            img.addEventListener('click', function() {
                document.getElementById('lightbox').style.display = 'flex';
                document.getElementById('lightbox-img').src = this.src;
            });
        });
    </script>
</body>
</html>
