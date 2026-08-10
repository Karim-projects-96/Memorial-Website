<?php
$protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http";

// Handle local IP detection for mobile scanning
$host = $_SERVER['HTTP_HOST'];
if (strpos($host, 'localhost') !== false) {
    if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
        $ip = gethostbyname(gethostname());
    } else {
        $ip = $_SERVER['SERVER_ADDR'];
    }
    // As a fallback, if getting hostname returns local, you can hardcode the IP:
    // $ip = "10.238.155.148"; 
    $host = $ip;
}

// Ensure the correct port is included if needed
$port = $_SERVER['SERVER_PORT'];
if ($port != "80" && $port != "443" && strpos($host, ':') === false) {
    $host .= ":" . $port;
}

$url = $protocol . "://" . $host . "/htdocs/get_card.php";
$qr_api_url = "https://api.qrserver.com/v1/create-qr-code/?size=350x350&data=" . urlencode($url);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Invitation QR Code</title>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@400;600;800&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary: #38bdf8;
            --background: #0f172a;
            --surface: rgba(30, 41, 59, 0.7);
        }
        body {
            font-family: 'Outfit', sans-serif;
            background-color: var(--background);
            color: #f8fafc;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            margin: 0;
            background-image: radial-gradient(circle at top right, rgba(56, 189, 248, 0.1), transparent 40%),
                              radial-gradient(circle at bottom left, rgba(14, 165, 233, 0.1), transparent 40%);
        }
        .container {
            background: var(--surface);
            padding: 2.5rem;
            border-radius: 20px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.5);
            text-align: center;
            border: 1px solid rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(10px);
            max-width: 90vw;
            width: 450px;
        }
        h1 {
            color: var(--primary);
            font-size: 1.8rem;
            margin-bottom: 0.5rem;
            text-transform: uppercase;
            letter-spacing: 2px;
        }
        p {
            color: #94a3b8;
            margin-bottom: 2rem;
            line-height: 1.6;
        }
        .qr-wrapper {
            background: #ffffff;
            padding: 1.5rem;
            border-radius: 15px;
            display: inline-block;
            box-shadow: 0 0 20px rgba(56, 189, 248, 0.2);
            transition: transform 0.3s ease;
        }
        .qr-wrapper:hover {
            transform: scale(1.02);
            box-shadow: 0 0 30px rgba(56, 189, 248, 0.4);
        }
        .qr-wrapper img {
            display: block;
            max-width: 100%;
            height: auto;
        }
        .url-link {
            display: block;
            margin-top: 2rem;
            color: var(--primary);
            text-decoration: none;
            word-break: break-all;
            background: rgba(56, 189, 248, 0.1);
            padding: 1rem;
            border-radius: 8px;
            font-size: 0.9rem;
            transition: all 0.3s ease;
        }
        .url-link:hover {
            background: rgba(56, 189, 248, 0.2);
            color: #fff;
        }
        .instruction {
            font-size: 0.85rem;
            color: #64748b;
            margin-top: 1.5rem;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Scan QR Code</h1>
        <p>Ask attendees to scan this code with their phones to get their customized invitation pass.</p>
        
        <div class="qr-wrapper">
            <img src="<?php echo htmlspecialchars($qr_api_url); ?>" alt="QR Code connecting to <?php echo htmlspecialchars($url); ?>">
        </div>
        
        <a href="<?php echo htmlspecialchars($url); ?>" class="url-link" target="_blank">
            <?php echo htmlspecialchars($url); ?>
        </a>
        
        <div class="instruction">
            *Ensure your devices are connected to the same network.
        </div>
    </div>
</body>
</html>
