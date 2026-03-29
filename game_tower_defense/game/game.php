<?php
session_start();
$level = $_GET['level'] ?? 1;

// HANDLE SAVE PROGRESS
if(isset($_POST['save_progress'])) {

    if(!isset($_SESSION['active_user'])) {
        echo "No user";
        exit();
    }

    $username = $_SESSION['active_user'];
    $level = (int)$_POST['level'];
    $stars = (int)$_POST['stars'];

    $filePath = "../storage/data.txt";

    $data = [];
    if(file_exists($filePath)) {
        $data = json_decode(file_get_contents($filePath), true);
    }

    // Pastikan user ada
    if(!isset($data[$username])) {
        $data[$username] = [
            "progress" => array_fill(1, 20, 0)
        ];
    }

    // Update hanya jika lebih besar
    if($stars > $data[$username]['progress'][$level]) {
        $data[$username]['progress'][$level] = $stars;
    }

    file_put_contents($filePath, json_encode($data));

    echo "success";
    exit(); // PENTING biar tidak lanjut render HTML
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tower Defense - Tactical Map</title>
    <style>
        :root {
            --sidebar-bg: #252525;
            --accent-blue: #007bff;
            --danger-red: #ff4d4d;
            --gold: #ffcc00;
        }

        body {
            margin: 0;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: #121212;
            color: white;
            height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            overflow: hidden;
        }

        .main-wrapper {
            display: flex;
            background: #1a1a1a;
            border: 4px solid #333;
            border-radius: 12px;
            box-shadow: 0 0 50px rgba(0, 0, 0, 0.8);
            overflow: hidden;
        }

        .game-view {
            position: relative;
            background: #000;
            width: 1000px;
            height: 750px;
            cursor: default;
        }

        #gameCanvas {
            display: block;
            width: 100%;
            height: 100%;
        }

        .sidebar {
            width: 320px;
            background: var(--sidebar-bg);
            border-left: 2px solid #444;
            display: flex;
            flex-direction: column;
            padding: 25px;
            box-sizing: border-box;
        }

        .timer-display {
            font-family: 'Courier New', Courier, monospace;
            font-size: 32px;
            color: #00ff00;
            text-align: center;
            background: #000;
            padding: 10px;
            border-radius: 5px;
            margin-bottom: 20px;
            border: 1px solid #333;
        }

        .stat-box {
            background: #1e1e1e;
            padding: 10px;
            border-radius: 8px;
            text-align: center;
            border: 1px solid #444;
            margin-bottom: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
        }

        .health-display {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            margin-bottom: 15px;
            padding: 5px;
            background: rgba(255, 77, 77, 0.1);
            border-radius: 5px;
        }

        .health-display img { width: 25px; height: 25px; }
        .health-display span { font-size: 20px; font-weight: bold; color: var(--danger-red); }

        .section-title {
            font-size: 11px;
            color: #888;
            text-transform: uppercase;
            letter-spacing: 1px;
            margin-bottom: 8px;
            border-bottom: 1px solid #333;
            padding-bottom: 3px;
        }

        .build-menu {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 10px;
        }

        .build-btn {
            background: #444;
            border: 2px solid transparent;
            border-radius: 8px;
            padding: 10px;
            cursor: pointer;
            text-align: center;
            transition: 0.2s;
        }

        .build-btn.active {
            border-color: var(--gold);
            background: #555;
            box-shadow: 0 0 10px rgba(255, 204, 0, 0.3);
        }

        .build-btn:hover { border-color: var(--accent-blue); }
        .build-btn img { width: 40px; height: 40px; margin-bottom: 5px; object-fit: contain; }

        .pause-overlay {
            position: fixed;
            inset: 0;
            background: rgba(0, 0, 0, 0.7);
            display: none;
            justify-content: center;
            align-items: center;
            z-index: 999;
        }

        .pause-box {
            background: #2e2e2e;
            padding: 40px 60px;
            border-radius: 10px;
            text-align: center;
            color: white;
        }

        .pause-box button {
            padding: 12px 30px;
            font-size: 18px;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            background: var(--accent-blue);
            color: white;
        }

        .btn-quit {
            padding: 12px 30px;
            font-size: 18px;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            background: red;
            color: white;
            text-decoration: none;
        }

        /* Styling untuk Overlay Hasil Akhir */
        .result-content {
            background: #2e2e2e;
            padding: 30px;
            border-radius: 15px;
            text-align: center;
            min-width: 350px;
            border: 4px solid var(--gold);
            box-shadow: 0 0 30px rgba(0,0,0,0.5);
        }

        .result-victory { border-color: var(--gold); }
        .result-defeated { border-color: var(--danger-red); }

        .star-rating {
            margin: 20px 0;
            font-size: 50px;
        }

        .star {
            width: 60px;
            height: 60px;
            margin: 0 5px;
            filter: drop-shadow(0 0 5px rgba(0,0,0,0.5));
        }

        /* Bintang redup/mati */
        .star.off {
            filter: grayscale(1) brightness(0.3);
            opacity: 0.5;
        }

        .stat-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 15px;
            margin: 20px 0;
            background: rgba(0,0,0,0.2);
            padding: 15px;
            border-radius: 10px;
            text-align: left;
        }

        .stat-item label { color: #888; font-size: 12px; display: block; }
        .stat-item span { font-weight: bold; font-size: 18px; color: #fff; }

        .result-actions {
            display: flex;
            gap: 10px;
            justify-content: center;
        }

        .btn-main { background: var(--accent-blue); }
        .btn-next { background: var(--gold); color: black; font-weight: bold; }
        .btn-retry { background: var(--danger-red); }

        button {
            padding: 10px 20px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-family: inherit;
            transition: 0.3s;
        }

        button:hover { transform: scale(1.05); filter: brightness(1.2); }
    </style>
</head>

<body>

    <div class="main-wrapper">
        <div class="game-view" id="gameView">
            <canvas id="gameCanvas" width="1000" height="750"></canvas>
        </div>

        <div class="sidebar">
            <h1>TACTICAL MAP</h1>
            <div class="timer-display" id="timer">00:00</div>

            <div class="section-title">Resources</div>
            <div class="stat-box">
                <img src="../images/coin.png" style="width:20px"> 
                <span id="coinCount">500</span>
            </div>

            <div class="section-title">Base Status</div>
            <div class="health-display">
                <img src="../images/heart.png" alt="Life">
                <span id="lifeCount">x 30</span>
            </div>

            <div class="section-title">Construction</div>
            <div class="build-menu">
                <div class="build-btn" id="btn-cannon" onclick="selectTower('cannon')">
                    <img src="../images/cannon.png" alt="Cannon"><br>
                    <label>Cannon</label><br>
                    <small style="color: var(--gold)">$100</small>
                </div>
            </div>
            <p style="font-size: 10px; color: #666; margin-top: 10px;">Press ESC to Cancel Build</p>
        </div>
    </div>

    <div class="pause-overlay" id="pauseOverlay">
        <div class="pause-box">
            <h2>Game Paused</h2>
            <button onclick="togglePause()">Continue</button>
            <a href="level.php" class="btn-quit">Quit</a>
        </div>
    </div>

    <div class="pause-overlay" id="resultOverlay">
        <div class="result-content" id="resultBox">
            <h1 id="resultTitle" style="margin:0; letter-spacing: 2px;">VICTORY</h1>
            
            <div class="star-rating" id="starRating">
                <img src="../images/star.png" class="star" id="star1">
                <img src="../images/star.png" class="star" id="star2">
                <img src="../images/star.png" class="star" id="star3">
            </div>

            <div class="stat-grid">
                <div class="stat-item">
                    <label>TIME ELAPSED</label>
                    <span id="resTime">00:00</span>
                </div>
                <div class="stat-item">
                    <label>MONSTERS KILLED</label>
                    <span id="resKilled">0</span>
                </div>
                <div class="stat-item">
                    <label>COINS EARNED</label>
                    <span id="resCoins">0</span>
                </div>
                <div class="stat-item">
                    <label>LIVES LEFT</label>
                    <span id="resLives">0</span>
                </div>
            </div>

            <div class="result-actions">
                <button class="btn-main" onclick="location.href='level.php'">Menu Level</button>
                <button class="btn-next" onclick="nextLevel()">Lanjut</button>
            </div>
        </div>
    </div>

    <script>
        const canvas = document.getElementById('gameCanvas');
        const ctx = canvas.getContext('2d');

        // Pengaturan Grid yang dipisahkan
        const cols = 16;
        const rows = 13;
        const tileW = canvas.width / cols;   // 1000 / 16 = 62.5
        const tileH = canvas.height / rows;  // 750 / 13 ≈ 57.69
        const currentLevel = <?php echo $level; ?>;

        // Koordinat Jalan (Forbidden Tiles) tetap menggunakan format "col-row"
        const forbiddenTiles = new Set([
            "15-3","14-3","13-3",
            "13-4","13-5", "13-6",
            "14-6","14-7","14-8","14-9","14-10","14-11",
            "13-11","12-11","11-11","10-11",
            "10-10","10-9",
            "9-9","8-9","7-9","6-9","5-9",
            "5-10","5-11",
            "4-11","3-11","2-11","1-11",
            "1-10","1-9","1-8",
            "2-8","3-8",
            "3-7","3-6",
            "2-6","1-6",
            "1-6","1-5","1-4","1-3","1-2","1-1",
            "2-1","3-1","4-1","5-1",
            "5-2","5-3","5-4","5-5","5-6",
            "6-6","7-6","8-6","9-6","10-6",
            "10-5","10-4","10-3","10-2","10-1","10-0",
        ]);

        // Urutan jalan monster (dari kanan ke kiri sesuai urutan tile kamu)
        const enemyPath = [
            {c: 15, r: 3}, {c: 14, r: 3}, {c: 13, r: 3}, {c: 13, r: 4}, {c: 13, r: 5}, {c: 13, r: 6},
            {c: 14, r: 6}, {c: 14, r: 7}, {c: 14, r: 8}, {c: 14, r: 9}, {c: 14, r: 10}, {c: 14, r: 11},
            {c: 13, r: 11}, {c: 12, r: 11}, {c: 11, r: 11}, {c: 10, r: 11}, {c: 10, r: 10}, {c: 10, r: 9},
            {c: 9, r: 9}, {c: 8, r: 9}, {c: 7, r: 9}, {c: 6, r: 9}, {c: 5, r: 9}, {c: 5, r: 10}, {c: 5, r: 11},
            {c: 4, r: 11}, {c: 3, r: 11}, {c: 2, r: 11}, {c: 1, r: 11}, {c: 1, r: 10}, {c: 1, r: 9}, {c: 1, r: 8},
            {c: 2, r: 8}, {c: 3, r: 8}, {c: 3, r: 7}, {c: 3, r: 6}, {c: 2, r: 6}, {c: 1, r: 6}, {c: 1, r: 5},
            {c: 1, r: 4}, {c: 1, r: 3}, {c: 1, r: 2}, {c: 1, r: 1}, {c: 2, r: 1}, {c: 3, r: 1}, {c: 4, r: 1},
            {c: 5, r: 1}, {c: 5, r: 2}, {c: 5, r: 3}, {c: 5, r: 4}, {c: 5, r: 5}, {c: 5, r: 6}, {c: 6, r: 6},
            {c: 7, r: 6}, {c: 8, r: 6}, {c: 9, r: 6}, {c: 10, r: 6}, {c: 10, r: 5}, {c: 10, r: 4}, {c: 10, r: 3},
            {c: 10, r: 2}, {c: 10, r: 1}, {c: 10, r: 0}
        ];

        const images = {
            background: new Image(),
            cannon: new Image(),
            monster: new Image(),
            projectile: new Image(),
            explode: new Image(),
            star: new Image(),
            giant: new Image(),
            laserBeam: new Image(),
            laserCannon: new Image()
        };

        images.background.src = '../images/map.png';
        images.cannon.src = '../images/cannon.png';
        images.monster.src = "../images/zombie.png";
        images.giant.src = "../images/giant.png";
        images.projectile.src = "../images/projectile.png";
        images.explode.src = "../images/explode.png";
        images.star.src = "../images/star.png";
        images.laserBeam.src = "../images/laserBeam.png";
        images.laserCannon.src = "../images/laser.png";

        function nextLevel() {
            const next = currentLevel + 1;
            window.location.href = `game.php?level=${next}`;
        }

        let secondsElapsed = 0;
        let timerInterval;

        function startTimer() {
            if (timerInterval) clearInterval(timerInterval);

            timerInterval = setInterval(() => {
                if (!isPaused && !gameOver) {
                    secondsElapsed++;
                    
                    // Format menit dan detik agar selalu 2 digit (contoh: 01:05)
                    const minutes = Math.floor(secondsElapsed / 60).toString().padStart(2, '0');
                    const seconds = (secondsElapsed % 60).toString().padStart(2, '0');
                    
                    document.getElementById('timer').innerText = `${minutes}:${seconds}`;
                }
            }, 1000);
        }

        let selectedTowerType = null;
        let placedTowers = []; 
        let mousePos = { col: -1, row: -1 };
        let gameOver = false;
        let isPaused = false;
        let gameLoop;
        

        // Tracking Mouse menggunakan tileW dan tileH yang terpisah
        canvas.addEventListener('mousemove', (e) => {
            const rect = canvas.getBoundingClientRect();
            const x = e.clientX - rect.left;
            const y = e.clientY - rect.top;
            mousePos.col = Math.floor(x / tileW);
            mousePos.row = Math.floor(y / tileH);
        });

        canvas.addEventListener('mousedown', () => {
            if (!selectedTowerType || isPaused) return;

            const posKey = `${mousePos.col}-${mousePos.row}`;
            const isForbidden = forbiddenTiles.has(posKey);
            const isOccupied = placedTowers.some(t => t.col === mousePos.col && t.row === mousePos.row);
            
            // Ambil jumlah koin saat ini
            let currentCoins = parseInt(document.getElementById('coinCount').innerText);

            if (!isForbidden && !isOccupied) {
                // CEK APAKAH KOIN CUKUP
                if (currentCoins >= TOWER_COST) {
                    placedTowers.push({
                        col: mousePos.col,
                        row: mousePos.row,
                        x: mousePos.col * tileW + tileW / 2,
                        y: mousePos.row * tileH + tileH / 2,
                        type: selectedTowerType,
                        range: 150,
                        cooldown: 0,
                        fireRate: 60,
                    });

                    // KURANGI KOIN
                    currentCoins -= TOWER_COST;
                    document.getElementById('coinCount').innerText = currentCoins;
                } else {
                    alert("Koin tidak cukup!");
                }
            }
        });

        function selectTower(type) {
            selectedTowerType = (selectedTowerType === type) ? null : type;
            updateBuildUI();
        }

        function updateBuildUI() {
            document.querySelectorAll('.build-btn').forEach(btn => btn.classList.remove('active'));
            if (selectedTowerType) {
                document.getElementById(`btn-${selectedTowerType}`).classList.add('active');
                document.getElementById('gameView').style.cursor = 'crosshair';
            } else {
                document.getElementById('gameView').style.cursor = 'default';
            }
        }

        let projectiles = [];
        const TOWER_COST = 100; // Harga satu tower

        class Projectile {
            constructor(x, y, target, damage) {
                this.x = x;
                this.y = y;
                this.target = target;
                this.damage = damage;
                this.speed = 5;
                this.reached = false;
            }

            update() {
                const dx = this.target.x - this.x;
                const dy = this.target.y - this.y;
                const distance = Math.sqrt(dx * dx + dy * dy);

                if (distance < 5) {
                    this.target.takeDamage(this.damage);
                    this.reached = true;
                } else {
                    this.x += (dx / distance) * this.speed;
                    this.y += (dy / distance) * this.speed;
                }
            }

            draw() {
                ctx.drawImage(images.projectile, this.x - 10, this.y - 10, 20, 20);
            }
        }

        function getRandomSpawnTime() {
            return Math.floor(Math.random() * 120) + 30;
            // 30 - 150 frame (cepat sampai agak lama)
        }
        
        let enemies = [];
        let spawnInterval = 100; // Muncul setiap 100 frame
        let frameCounter = 0;

        const maxEnemies = 20 + (currentLevel * 3);
        let totalSpawned = 0;
        let spawnTimer = 0;
        let nextSpawnTime = getRandomSpawnTime();

        let totalKilled = 0;
        let initialLives = 30; // Sesuaikan dengan nyawa awal kamu

        const monsterSize = 0.6;
        class Monster {
            constructor(path, type = "zombie") {
                this.path = path;
                this.type = type; // "zombie" atau "giant"
                this.pathIndex = 0;
                this.x = path[0].c * tileW + tileW / 2;
                this.y = path[0].r * tileH + tileH / 2;
                this.walkCycle = 0;
                this.isDead = false;
                this.deathTimer = 0;
                this.reachedFinish = false;

                // --- PENGATURAN STATISTIK BERDASARKAN TIPE ---
                if (this.type === "giant") {
                    this.hp = 500;           // Darah lebih tebal (5x zombie)
                    this.maxHp = 500;
                    this.speed = 0.6;        // Lebih lambat (zombie 1.5)
                    this.sizeMult = 1.4;     // Lebih besar secara visual
                    this.reward = 100;       // Koin lebih banyak jika mati
                } else {
                    this.hp = 100;
                    this.maxHp = 100;
                    this.speed = 1.5;
                    this.sizeMult = 0.8;
                    this.reward = 25;
                }
            }

            takeDamage(amount) {
                this.hp -= amount;
                if (this.hp <= 0 && !this.isDead) {
                    this.isDead = true;
                    totalKilled++;
                    totalCoinsEarned += this.reward;
                    let coins = parseInt(document.getElementById('coinCount').innerText);
                    document.getElementById('coinCount').innerText = coins + this.reward;
                }
            }

            // Update & Draw tetap sama, hanya sesuaikan ukurannya di draw()
            update() {
                if (this.isDead) { this.deathTimer++; return; }

                const currentCol = Math.floor(this.x / tileW);
                const currentRow = Math.floor(this.y / tileH);
                const currentPosKey = `${currentCol}-${currentRow}`;
                const damageZone = ["10-3", "10-2", "10-1", "10-0"];

                if (damageZone.includes(currentPosKey)) {
                    this.reachedFinish = true;
                    return;
                }

                if (this.pathIndex < this.path.length - 1) {
                    const target = this.path[this.pathIndex + 1];
                    const targetX = target.c * tileW + tileW / 2;
                    const targetY = target.r * tileH + tileH / 2;
                    const dx = targetX - this.x;
                    const dy = targetY - this.y;
                    const distance = Math.sqrt(dx * dx + dy * dy);

                    if (distance > this.speed) {
                        this.x += (dx / distance) * this.speed;
                        this.y += (dy / distance) * this.speed;
                    } else {
                        this.pathIndex++;
                    }
                    this.walkCycle += (this.type === "giant" ? 0.05 : 0.15); // Ayunan jalan giant lebih lambat
                } else {
                    this.reachedFinish = true;
                }
            }

            draw() {
                if (this.isDead) {
                    ctx.drawImage(images.explode, this.x - tileW/2, this.y - tileH/2, tileW, tileH);
                    return;
                }

                ctx.save();
                const hopY = Math.abs(Math.sin(this.walkCycle)) * (this.type === "giant" ? 4 : 8);
                const rockAngle = Math.sin(this.walkCycle) * 0.1;

                ctx.translate(this.x, this.y - hopY);
                ctx.rotate(rockAngle);
                
                // Gunakan gambar sesuai tipe dan ukuran multiplier
                const img = this.type === "giant" ? images.giant : images.monster;
                const w = tileW * this.sizeMult;
                const h = tileH * this.sizeMult;
                
                ctx.drawImage(img, -w/2, -h/2, w, h);
                ctx.restore();

                // Health Bar
                ctx.fillStyle = "red";
                ctx.fillRect(this.x - 25, this.y - 50, 50, 6);
                ctx.fillStyle = "green";
                ctx.fillRect(this.x - 25, this.y - 50, (this.hp / this.maxHp) * 50, 6);
            }
        }

        let totalCoinsEarned = 0; // Tambahkan ini di bagian variabel global
        function showResult(isWin) {
            gameOver = true;
            clearInterval(timerInterval); // Berhentikan timer

            const overlay = document.getElementById('resultOverlay');
            const box = document.getElementById('resultBox');
            const title = document.getElementById('resultTitle');
            
            const sisaNyawa = parseInt(document.getElementById('lifeCount').innerText.replace('x ', ''));
            const waktu = document.getElementById('timer').innerText;

            // Tampilkan statistik di Overlay
            document.getElementById('resTime').innerText = waktu;
            document.getElementById('resKilled').innerText = totalKilled;
            document.getElementById('resCoins').innerText = totalCoinsEarned; // Menampilkan total yang didapat
            document.getElementById('resLives').innerText = sisaNyawa;

            if (isWin) {
                title.innerText = "MISSION ACCOMPLISHED";
                title.style.color = "var(--gold)";
                box.className = "result-content result-victory";

                // Logika Bintang
                let stars = 1;
                if (sisaNyawa >= 30) stars = 3;
                else if (sisaNyawa > 15) stars = 2;

                document.getElementById('star1').classList.remove('off');
                document.getElementById('star2').classList.toggle('off', stars < 2);
                document.getElementById('star3').classList.toggle('off', stars < 3);

                if (!alreadySaved) {
                    alreadySaved = true;
                    fetch("game.php", {
                        method: "POST",
                        headers: { "Content-Type": "application/x-www-form-urlencoded" },
                        body: `save_progress=1&level=${currentLevel}&stars=${stars}`
                    });
                }
            } else {
                title.innerText = "DEFENSE BREACHED";
                title.style.color = "var(--danger-red)";
                box.className = "result-content result-defeated";
                document.querySelectorAll('.star').forEach(s => s.classList.add('off'));
            }

            overlay.style.display = "flex";
        }
            
        const towerSize = 0.8;
        function draw() {
            if (isPaused) return;

            if (gameOver) {
                cancelAnimationFrame(gameLoop);
                return;
            }

            ctx.clearRect(0, 0, canvas.width, canvas.height);

            ctx.drawImage(images.background, 0, 0, canvas.width, canvas.height);

            placedTowers.forEach(t => {
                // Menggambar Radius (Dibuat lebih terlihat dengan alpha 0.1)
                ctx.beginPath();
                ctx.arc(t.x, t.y, t.range, 0, Math.PI * 2);
                ctx.fillStyle = "rgba(0, 123, 255, 0.1)"; 
                ctx.fill();
                ctx.strokeStyle = "rgba(0, 123, 255, 0.4)";
                ctx.stroke();

                // Logika Menembak
                if (t.cooldown > 0) t.cooldown--;
                if (t.cooldown <= 0) {
                    for (let enemy of enemies) {
                        if (enemy.isDead) continue;
                        const dist = Math.sqrt(Math.pow(enemy.x - t.x, 2) + Math.pow(enemy.y - t.y, 2));
                        if (dist <= t.range) {
                            projectiles.push(new Projectile(t.x, t.y, enemy, 15));
                            t.cooldown = t.fireRate;
                            break;
                        }
                    }
                }

                // Gambar Tower
                ctx.drawImage(images[t.type], t.col * tileW + 5, t.row * tileH + 5, tileW - 10, tileH - 10);
            });

            for (let i = projectiles.length - 1; i >= 0; i--) {
                const p = projectiles[i];
                p.update();
                p.draw();
                
                // Hapus proyektil jika sudah sampai target
                if (p.reached) {
                    projectiles.splice(i, 1);
                }
            }

            // 3. Draw Placement Overlay
            if (selectedTowerType) {
                for (let r = 0; r < rows; r++) {
                    for (let c = 0; c < cols; c++) {
                        const posKey = `${c}-${r}`;
                        const isForbidden = forbiddenTiles.has(posKey);
                        const isOccupied = placedTowers.some(t => t.col === c && t.row === r);

                        if (isForbidden) {
                            ctx.fillStyle = "rgba(255, 0, 0, 0.3)";
                        } else if (isOccupied) {
                            ctx.fillStyle = "rgba(255, 255, 0, 0.3)";
                        } else {
                            ctx.fillStyle = "rgba(0, 255, 0, 0.15)";
                        }
                        
                        // Menggunakan tileW dan tileH untuk kotak overlay
                        ctx.fillRect(c * tileW, r * tileH, tileW - 1, tileH - 1);
                    }
                }

                // 4. Highlight & Ghost Preview
                if (mousePos.col >= 0 && mousePos.col < cols && mousePos.row >= 0 && mousePos.row < rows) {
                    ctx.strokeStyle = "white";
                    ctx.lineWidth = 2;
                    ctx.strokeRect(mousePos.col * tileW, mousePos.row * tileH, tileW, tileH);
                    
                    ctx.globalAlpha = 0.5;
                    ctx.drawImage(
                        images[selectedTowerType], 
                        mousePos.col * tileW + 5, 
                        mousePos.row * tileH + 5, 
                        tileW - 10, 
                        tileH - 10
                    );
                    ctx.globalAlpha = 1.0;
                }
            }

            spawnTimer++;
            if (spawnTimer >= nextSpawnTime && totalSpawned < maxEnemies) {
                // Tentukan tipe monster: 15% peluang Giant, 85% Zombie
                const spawnType = Math.random() < 0.15 ? "giant" : "zombie";
                
                enemies.push(new Monster(enemyPath, spawnType));
                totalSpawned++;

                spawnTimer = 0;
                nextSpawnTime = getRandomSpawnTime(); 
            }

            // 4. Update & Draw Monster
            for (let i = enemies.length - 1; i >= 0; i--) {
                const enemy = enemies[i];
                enemy.update();
                enemy.draw();

                // Hapus jika sudah meledak (setelah 30 frame)
                if (enemy.isDead && enemy.deathTimer > 30) {
                    enemies.splice(i, 1);
                    continue;
                }

                if (enemy.reachedFinish) {
                    enemies.splice(i, 1);
                    let currentLife = parseInt(document.getElementById('lifeCount').innerText.replace('x ', ''));
                    document.getElementById('lifeCount').innerText = `x ${Math.max(0, currentLife - 1)}`;
                }
            }

            if (totalSpawned >= maxEnemies && enemies.length === 0 && !gameOver) {
                showResult(true);
            }

            // Cek Kalah: Nyawa habis
            let currentLife = parseInt(document.getElementById('lifeCount').innerText.replace('x ', ''));
            if (currentLife <= 0 && !gameOver) {
                showResult(false);
            }

            gameLoop = requestAnimationFrame(draw);
        }

        function togglePause() {
            isPaused = !isPaused;
            document.getElementById("pauseOverlay").style.display = isPaused ? "flex" : "none";
            if (!isPaused) draw();
        }

        
        let alreadySaved = false;

        function init() {
            updateBuildUI();
            startTimer();
            draw();
        }

        let loadedImages = 0;
        const totalImages = Object.keys(images).length;
        for (let key in images) {
            images[key].onload = () => {
                loadedImages++;
                if (loadedImages === totalImages) init();
            };
        }

        window.addEventListener('keydown', (e) => {
            if (e.key === "Escape") {
                if (selectedTowerType) {
                    selectedTowerType = null;
                    updateBuildUI();
                } else {
                    togglePause();
                }
            }
        });
    </script>
</body>
</html>