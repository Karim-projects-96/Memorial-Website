<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Get Your Digital Pass</title>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;500;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary: #38bdf8;
            --primary-hover: #0ea5e9;
            --bg-color: #0c111d;
            --card-bg: rgba(30, 41, 59, 0.8);
            --text: #f8fafc;
        }
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body {
            font-family: 'Outfit', sans-serif;
            background: var(--bg-color);
            color: var(--text);
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            align-items: center;
            background-image: radial-gradient(circle at 10% 20%, rgba(56, 189, 248, 0.15), transparent 50%),
                              radial-gradient(circle at 90% 80%, rgba(139, 92, 246, 0.15), transparent 50%);
            padding: 2rem 1rem;
            overflow-x: hidden;
        }
        
        .header {
            text-align: center;
            margin-bottom: 2rem;
        }
        .header h1 {
            font-size: 2.2rem;
            background: linear-gradient(135deg, #38bdf8, #8b5cf6);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            margin-bottom: 0.5rem;
        }
        .header p {
            color: #94a3b8;
            font-size: 1rem;
        }

        .controls {
            background: var(--card-bg);
            padding: 1.5rem;
            border-radius: 16px;
            width: 100%;
            max-width: 500px;
            border: 1px solid rgba(255,255,255,0.05);
            backdrop-filter: blur(10px);
            margin-bottom: 2rem;
            box-shadow: 0 10px 40px rgba(0,0,0,0.3);
            position: relative;
            z-index: 10;
        }
        
        .input-group {
            margin-bottom: 1.5rem;
        }
        label {
            display: block;
            margin-bottom: 0.5rem;
            font-size: 0.9rem;
            color: #cbd5e1;
            font-weight: 500;
        }
        input[type="text"] {
            width: 100%;
            padding: 1rem;
            background: rgba(15, 23, 42, 0.6);
            border: 1px solid rgba(56, 189, 248, 0.3);
            border-radius: 8px;
            color: #fff;
            font-size: 1.1rem;
            font-family: inherit;
            transition: all 0.3s ease;
        }
        input[type="text"]:focus {
            outline: none;
            border-color: var(--primary);
            box-shadow: 0 0 15px rgba(56, 189, 248, 0.3);
        }

        .range-controls {
            display: flex;
            flex-direction: column;
            gap: 1rem;
            margin-bottom: 1.5rem;
            background: rgba(0,0,0,0.2);
            padding: 1rem;
            border-radius: 8px;
        }
        .range-item {
            display: flex;
            align-items: center;
            gap: 1rem;
        }
        .range-item label {
            margin: 0;
            width: 60px;
            font-size: 0.8rem;
        }
        input[type="range"] {
            flex: 1;
            accent-color: var(--primary);
        }

        button {
            width: 100%;
            padding: 1rem;
            border: none;
            border-radius: 8px;
            background: linear-gradient(135deg, var(--primary), #8b5cf6);
            color: #fff;
            font-size: 1.1rem;
            font-weight: 700;
            cursor: pointer;
            transition: transform 0.2s, box-shadow 0.2s;
            display: flex;
            justify-content: center;
            align-items: center;
            gap: 8px;
        }
        button:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 20px rgba(56, 189, 248, 0.4);
        }
        button:active {
            transform: translateY(0);
        }

        .canvas-container {
            width: 100%;
            max-width: 500px;
            position: relative;
            border-radius: 16px;
            overflow: hidden;
            box-shadow: 0 20px 50px rgba(0,0,0,0.5);
            background: #000;
        }
        
        canvas {
            width: 100%;
            height: auto;
            display: block;
        }

        .error-msg {
            color: #ef4444;
            text-align: center;
            padding: 1rem;
            background: rgba(239, 68, 68, 0.1);
            border-radius: 8px;
            display: none;
            margin-bottom: 1rem;
        }

        .instruction-overlay {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            background: rgba(0,0,0,0.8);
            padding: 10px 20px;
            border-radius: 20px;
            font-size: 0.85rem;
            pointer-events: none;
            opacity: 1;
            transition: opacity 0.5s;
        }
        .hidden { opacity: 0; }
    </style>
</head>
<body>

    <div class="header">
        <h1>Digital Invitation</h1>
        <p>Personalize your access pass instantly</p>
    </div>

    <div class="controls">
        <div id="errorBox" class="error-msg"></div>
        <div class="input-group">
            <label for="userName">Enter Your Full Name</label>
            <input type="text" id="userName" placeholder="e.g. Rahul Sharma" autocomplete="off">
        </div>
        
        <div class="range-controls">
            <div class="range-item">
                <label>Text Size</label>
                <input type="range" id="textSize" min="20" max="100" value="45">
            </div>
            <div class="range-item">
                <label>Vertical</label>
                <input type="range" id="posY" min="0" max="100" value="75">
            </div>
        </div>

        <button id="downloadBtn">
            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path><polyline points="7 10 12 15 17 10"></polyline><line x1="12" y1="15" x2="12" y2="3"></line></svg>
            Download Pass
        </button>
    </div>

    <div class="canvas-container" id="canvasContainer">
        <canvas id="cardCanvas"></canvas>
        <div class="instruction-overlay" id="dragHint">You can use sliders to adjust!</div>
    </div>

    <script>
        const canvas = document.getElementById('cardCanvas');
        const ctx = canvas.getContext('2d');
        const nameInput = document.getElementById('userName');
        const downloadBtn = document.getElementById('downloadBtn');
        const errorBox = document.getElementById('errorBox');
        
        const textSizeSlider = document.getElementById('textSize');
        const posYSlider = document.getElementById('posY');
        const dragHint = document.getElementById('dragHint');

        // Load image
        const img = new Image();
        // The user must place the raw card as template.jpg in the htdocs folder
        // Allow timestamp fetching to prevent caching issues if replaced
        img.src = 'template.jpg?' + new Date().getTime(); 
        
        let imgLoaded = false;

        img.onload = () => {
            imgLoaded = true;
            // Set canvas resolution base on image natural resolution for high quality export
            canvas.width = img.naturalWidth || 1080;
            canvas.height = img.naturalHeight || 1920;
            drawCard();
        };

        img.onerror = () => {
            errorBox.style.display = 'block';
            errorBox.innerHTML = "<strong>Error:</strong> The background image 'template.jpg' was not found.<br><br><em>Admin notice: Please save your invitation image as 'template.jpg' in the current folder.</em>";
        };

        function drawCard() {
            if (!imgLoaded) return;

            // Clear and draw image
            ctx.clearRect(0, 0, canvas.width, canvas.height);
            ctx.drawImage(img, 0, 0, canvas.width, canvas.height);

            const name = nameInput.value.trim();
            if (name) {
                // Style configurations
                const scaleFactor = canvas.width / 1080; // normalize scaling relative to roughly 1080p
                const baseFontSize = parseInt(textSizeSlider.value) * scaleFactor;
                
                ctx.font = `800 ${baseFontSize}px 'Outfit', sans-serif`;
                ctx.textAlign = 'center';
                ctx.textBaseline = 'middle';
                
                const x = canvas.width / 2;
                const percentY = parseInt(posYSlider.value) / 100;
                const y = canvas.height * percentY;

                // Add text shadow for pop
                ctx.shadowColor = 'rgba(0,0,0,0.6)';
                ctx.shadowBlur = 10 * scaleFactor;
                ctx.shadowOffsetX = 2 * scaleFactor;
                ctx.shadowOffsetY = 4 * scaleFactor;

                // Text drawing: Light vibrant color inside, glow around
                ctx.fillStyle = '#ffffff'; 
                ctx.fillText(name.toUpperCase(), x, y);
                
                // Reset shadow
                ctx.shadowBlur = 0;
                ctx.shadowOffsetX = 0;
                ctx.shadowOffsetY = 0;
                
                // Add a cyan stroke
                ctx.lineWidth = 2 * scaleFactor;
                ctx.strokeStyle = '#38bdf8';
                ctx.strokeText(name.toUpperCase(), x, y);
            }
        }

        // Event Listeners for live updating
        nameInput.addEventListener('input', () => {
            drawCard();
            if(nameInput.value.trim().length > 0) {
                dragHint.classList.add('hidden');
            } else {
                dragHint.classList.remove('hidden');
            }
        });
        
        textSizeSlider.addEventListener('input', drawCard);
        posYSlider.addEventListener('input', drawCard);

        downloadBtn.addEventListener('click', () => {
            if (!imgLoaded) {
                alert("Image not loaded properly yet.");
                return;
            }
            if(!nameInput.value.trim()) {
                alert("Please enter your name first!");
                nameInput.focus();
                return;
            }
            
            // Create temporary link to download
            const link = document.createElement('a');
            link.download = `Invitation_${nameInput.value.trim().replace(/\s+/g, '_')}.png`;
            link.href = canvas.toDataURL('image/png');
            link.click();
        });

        // Hide hint after few seconds
        setTimeout(() => {
            dragHint.classList.add('hidden');
        }, 5000);

    </script>
</body>
</html>
