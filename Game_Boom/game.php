<?php
    $file = "storage/data.txt";

    if(!file_exists($file)){
        echo "<script>alert('Data tidak ditemukan!'); window.location='index.php';</script>";
        exit();
    }

    $data = file_get_contents($file);

    if(empty($data)){
        echo "<script>alert('Data kosong!'); window.location='index.php';</script>";
        exit();
    }

    list($username, $level) = explode("|", $data);

?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>BOMSKUY - Canvas Version</title>

<style>
    body {
        margin: 0;
        font-family: Arial, Helvetica, sans-serif;
        background: #fff;
        overflow:hidden;
        height: 100vh;
        display: flex;
        justify-content: center;
        align-items: center;
        padding: 15px;
        box-sizing: border-box;
    }

    .game-container {
        display: flex;
        box-shadow: 0 0 20px rgba(0,0,0,0.3);
    }

    .game-board {
        position: relative;
        width: 803px;  /* 11 * 73 */
        height: 657px;  /* 9 * 73 */
    }

    #gameCanvas {
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        display: block;
    }

    .sidebar {
        width: 300px;
        background: #3a3a3a;
        color: white;
        padding: 40px;
        box-sizing: border-box;
    }

    .sidebar h1 {
        text-align: right;
        font-size: 45px;
        margin-top: 0;
        margin-bottom: 40px;
    }

    .info {
        font-size: 18px;
        margin-bottom: 20px;
    }

    .hearts {
        margin: 20px 0;
        display: flex;
        gap: 8px;
    }

    .hearts img {
        width: 40px;
        height: 40px;
    }

    .score-item {
        display: flex;
        align-items: center;
        margin-top: 25px;
        font-size: 20px;
    }

    .score-item img {
        width: 45px;
        margin-right: 12px;
    }

    .pause-overlay {
        position: fixed;
        inset: 0;
        background: rgba(0,0,0,0.7);
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

    .pause-box h2 {
        font-size: 40px;
        margin-bottom: 20px;
    }

    .pause-box button {
        padding: 12px 30px;
        font-size: 18px;
        border: none;
        border-radius: 6px;
        cursor: pointer;
        background: #d84315;
        color: white;
    }
</style>
</head>

<body>

<div class="wrapper">
    <div class="game-container">
        <div class="game-board">
            <canvas id="gameCanvas" width="803" height="657"></canvas>
        </div>

        <div class="sidebar">
            <h1>BOMSKUY</h1>

            <div class="info">
                <p><strong>Player</strong> : <?php echo $username; ?></p>
                <p><strong>Time</strong> : <span id="timer">00:00</span></p>
            </div>

            <div class="hearts">
                <img src="images/heart.png" class="heart" id="heart1">
                <img src="images/heart.png" class="heart" id="heart2">
                <img src="images/heart.png" class="heart" id="heart3">
            </div>

            <div class="score-item">
                <img src="images/wall_crack.png">
                <span id="destroyedCount">= 0</span>
            </div>

            <div class="score-item">
                <img src="images/tnt.png">
                <span id="tntCount">= 0</span>
            </div>

            <div class="score-item">
                <img src="images/ice.png">
                <span id="iceCount">= 0</span>
            </div>
        </div>
    </div>
</div>

<div class="pause-overlay" id="pauseOverlay">
    <div class="pause-box">
        <h2>Game Paused</h2>
        <button onclick="togglePause()">Continue</button>
    </div>
</div>

<script>
    const canvas = document.getElementById('gameCanvas');
    const ctx = canvas.getContext('2d');

    const tileSize = 73;
    const rows = 9;
    const cols = 11;

    const forbidden = [
        "2-2","4-2","6-2",
        "2-4","4-4","6-4",
        "2-6","4-6","6-6",
        "2-8","4-8","6-8"
    ];
    
    let playerRow = 1;
    let playerCol = 1;

    // Images
    const images = {
        background: new Image(),
        char_down: new Image(),
        char_up: new Image(),
        char_left: new Image(),
        char_right: new Image(),
        dog_down: new Image(),
        dog_up: new Image(),
        dog_left: new Image(),
        dog_right: new Image(),
        wall: new Image(),
        bomb: new Image(),
        tnt: new Image(),
        ice: new Image(),
        explode: new Image()
    };

    // Set image sources
    images.background.src = 'Images/background.jpg';
    images.char_down.src = 'images/char_down.png';
    images.char_up.src = 'images/char_up.png';
    images.char_left.src = 'images/char_left.png';
    images.char_right.src = 'images/char_right.png';
    images.dog_down.src = 'images/dog_down.png';
    images.dog_up.src = 'images/dog_up.png';
    images.dog_left.src = 'images/dog_left.png';
    images.dog_right.src = 'images/dog_right.png';
    images.wall.src = 'images/wall.png';
    images.bomb.src = 'images/bomb.png';
    images.tnt.src = 'images/tnt.png';
    images.ice.src = 'images/ice.png';
    images.explode.src = 'images/explode.png';

    /* =========================
    HELPER FUNCTIONS
    ========================= */
    function posKey(row, col) {
        return row + "-" + col;
    }

    function isBorder(row, col) {
        return row === 0 || col === 0 || row === rows-1 || col === cols-1;
    }

    function isForbidden(row, col) {
        return forbidden.includes(posKey(row, col));
    }

    let wallPositions = [];
    let dogs = [];
    
    function isOccupied(row, col) {
        const key = posKey(row, col);
        const dogHere = dogs.some(d => posKey(d.row, d.col) === key);
        return wallPositions.includes(key) || dogHere || key === posKey(playerRow, playerCol);
    }

    function isDogAt(row, col, currentDog = null) {
        return dogs.some(d => d !== currentDog && d.row === row && d.col === col);
    }

    /* =========================
    TIMER
    ========================= */
    let seconds = 0;
    let timerInterval;
    function startTimer() {
        timerInterval = setInterval(() => {
            if(gameOver) return;
            seconds++;
            let mins = Math.floor(seconds / 60);
            let secs = seconds % 60;
            document.getElementById("timer").innerText = 
                (mins < 10 ? "0" + mins : mins) + ":" + 
                (secs < 10 ? "0" + secs : secs);
        }, 1000);
    }

    /* =========================
    GAME OVER
    ========================= */
    function sendToGameOver() {
        const time = document.getElementById("timer").innerText;
        const form = document.createElement("form");
        form.method = "POST";
        form.action = "gameover.php";
        
        const data = {
            username: "<?php echo $username; ?>",
            time: time,
            wall: destroyedWalls,
            tnt: playerTNT,
            ice: iceCollected
        };

        for (const key in data) {
            const input = document.createElement("input");
            input.type = "hidden";
            input.name = key;
            input.value = data[key];
            form.appendChild(input);
        }

        document.body.appendChild(form);
        form.submit();
    }

    let playerLives = 3;
    let gameOver = false;
    function takeDamage() {
        if(gameOver) return;
        playerLives--;
        
        const hearts = document.querySelectorAll(".heart");
        if(hearts[playerLives]) {
            hearts[playerLives].src = "Images/heart_broke.png";
        }

        if(playerLives <= 0) {
            gameOver = true;
            clearInterval(timerInterval);
            clearInterval(dogInterval);
            cancelAnimationFrame(gameLoop);
            sendToGameOver();
        }
    }

    /* =========================
    SPAWN FUNCTIONS
    ========================= */
    function spawnWall() {
        let row, col;
        do {
            row = Math.floor(Math.random() * rows);
            col = Math.floor(Math.random() * cols);
        } while(isBorder(row, col) || isForbidden(row, col) || isOccupied(row, col));
        
        wallPositions.push(posKey(row, col));
    }

    function spawnDog() {
        const level = "<?php echo $level; ?>";
        let totalDog = level === "Medium" ? 2 : level === "Hard" ? 3 : 1;
        
        for(let i = 0; i < totalDog; i++) {
            let row, col;
            do {
                row = Math.floor(Math.random() * rows);
                col = Math.floor(Math.random() * cols);
            } while(isBorder(row, col) || isForbidden(row, col) || isOccupied(row, col) || isDogAt(row, col));
            
            dogs.push({
                row: row,
                col: col,
                direction: 'down'
            });
        }
    }

    let bombPosition = null;
    let mapBomb = null;
    function spawnBomb() {
        let row, col;
        do {
            row = Math.floor(Math.random() * rows);
            col = Math.floor(Math.random() * cols);
        } while(isBorder(row, col) || isForbidden(row, col) || isOccupied(row, col));
        
        bombPosition = posKey(row, col);
        mapBomb = { row: row, col: col };
    }

    /* =========================
    GAME MECHANICS
    ========================= */
    function moveDogs() {
        dogs.forEach(dog => {
            let newRow = dog.row;
            let newCol = dog.col;

            // Prioritize vertical movement
            if(playerRow < dog.row) {
                newRow--;
                dog.direction = 'up';
            } else if(playerRow > dog.row) {
                newRow++;
                dog.direction = 'down';
            } else if(playerCol < dog.col) {
                newCol--;
                dog.direction = 'left';
            } else if(playerCol > dog.col) {
                newCol++;
                dog.direction = 'right';
            }

            // Check collision
            if(isBorder(newRow, newCol) || 
               isForbidden(newRow, newCol) || 
               wallPositions.includes(posKey(newRow, newCol)) || 
               isDogAt(newRow, newCol, dog)) {
                return;
            }

            dog.row = newRow;
            dog.col = newCol;

            // Check if dog hits player
            if(dog.row === playerRow && dog.col === playerCol) {
                takeDamage();
            }
        });
    }
    

    let explosions = [];
    let items = [];
    let destroyedWalls = 0;
    function explodeBomb(row, col, isTNT = false) {
        const radius = isTNT ? 2 : 1;
        const centerKey = posKey(row, col);
        
        // Remove from wall positions if it was placed there
        const index = wallPositions.indexOf(centerKey);
        if(index > -1) wallPositions.splice(index, 1);
        
        const directions = [
            {r:0, c:0}, {r:-1, c:0}, {r:1, c:0}, {r:0, c:-1}, {r:0, c:1}
        ];

        directions.forEach(dir => {
            for(let i = 0; i <= radius; i++) {
                const newRow = row + (dir.r * i);
                const newCol = col + (dir.c * i);

                if(isBorder(newRow, newCol) || isForbidden(newRow, newCol)) {
                    break;
                }

                const key = posKey(newRow, newCol);

                // Add explosion animation
                explosions.push({
                    row: newRow,
                    col: newCol,
                    timer: 10
                });

                // Check if player is hit
                if(newRow === playerRow && newCol === playerCol) {
                    takeDamage();
                }

                // Check if wall is hit
                const wallIndex = wallPositions.indexOf(key);
                if(wallIndex > -1) {
                    wallPositions.splice(wallIndex, 1);
                    destroyedWalls++;
                    
                    // Random drop
                    const random = Math.floor(Math.random() * 100);
                    if(random < 30) {
                        items.push({
                            row: newRow,
                            col: newCol,
                            type: 'tnt'
                        });
                    } else if(random < 90) {
                        items.push({
                            row: newRow,
                            col: newCol,
                            type: 'ice'
                        });
                    }
                    
                    updateSidebar();
                }
            }
        });
        
        // Clear map bomb if it was the one exploding
        if(mapBomb && mapBomb.row === row && mapBomb.col === col) {
            mapBomb = null;
            bombPosition = null;
        }
    }


    
    /* =========================
    DRAWING FUNCTIONS
    ========================= */

    
    
    function draw() {
        if(isPaused || gameOver) return;
        
        // Clear canvas
        ctx.clearRect(0, 0, canvas.width, canvas.height);
        
        // Draw background
        ctx.drawImage(images.background, 0, 0, canvas.width, canvas.height);
        
        // Draw walls
        wallPositions.forEach(pos => {
            const [row, col] = pos.split('-').map(Number);
            ctx.drawImage(images.wall, col * tileSize, row * tileSize, tileSize, tileSize);
        });
        
        // Draw items
        items.forEach(item => {
            const img = item.type === 'tnt' ? images.tnt : images.ice;
            ctx.drawImage(img, item.col * tileSize, item.row * tileSize, tileSize, tileSize);
        });
        
        // Draw map bomb
        if(mapBomb) {
            ctx.drawImage(images.bomb, mapBomb.col * tileSize, mapBomb.row * tileSize, tileSize, tileSize);
        }
        
        // Draw player bomb
        if(activeBomb){
            const img = activeBomb.type === "tnt" ? images.tnt : images.bomb;

            ctx.drawImage(
                img,
                activeBomb.col * tileSize,
                activeBomb.row * tileSize,
                tileSize,
                tileSize
            );
        }

        // Draw dogs
        dogs.forEach(dog => {
            let img;
            switch(dog.direction) {
                case 'up': img = images.dog_up; break;
                case 'down': img = images.dog_down; break;
                case 'left': img = images.dog_left; break;
                case 'right': img = images.dog_right; break;
                default: img = images.dog_down;
            }
            ctx.drawImage(img, dog.col * tileSize, dog.row * tileSize, tileSize, tileSize);
        });
        
        // Draw player with frozen effect
        if(isFrozen) {
            ctx.globalAlpha = 0.5;
        }
        let playerImg;
        switch(playerDirection) {
            case 'up': playerImg = images.char_up; break;
            case 'down': playerImg = images.char_down; break;
            case 'left': playerImg = images.char_left; break;
            case 'right': playerImg = images.char_right; break;
            default: playerImg = images.char_down;
        }
        ctx.drawImage(playerImg, playerCol * tileSize, playerRow * tileSize, tileSize, tileSize);
        ctx.globalAlpha = 1.0;
        
        // Draw explosions
        explosions = explosions.filter(exp => {
            ctx.drawImage(images.explode, exp.col * tileSize, exp.row * tileSize, tileSize, tileSize);
            exp.timer--;
            return exp.timer > 0;
        });
        
        gameLoop = requestAnimationFrame(draw);
    }

    let isPaused = false;
    let gameLoop;
    function togglePause() {
        if(gameOver) return;
        isPaused = !isPaused;
        const overlay = document.getElementById("pauseOverlay");
        
        if(isPaused) {
            overlay.style.display = "flex";
            clearInterval(timerInterval);
            clearInterval(dogInterval);
            cancelAnimationFrame(gameLoop);
        } else {
            overlay.style.display = "none";
            startTimer();
            dogInterval = setInterval(moveDogs, 600);
            gameLoop = requestAnimationFrame(draw);
        }
    }


    /* =========================
    INITIALIZATION
    ========================= */

    const totalWall = 10;
    let dogInterval;
    function init() {
        // Spawn initial objects
        for(let i = 0; i < totalWall; i++) {
            spawnWall();
        }
        
        spawnDog();
        spawnBomb();
        
        updateSidebar();
        startTimer();
        dogInterval = setInterval(moveDogs, 600);
        gameLoop = requestAnimationFrame(draw);
    }

    // Wait for images to load before starting
    let loadedImages = 0;
    const totalImages = Object.keys(images).length;
    
    for(let key in images) {
        images[key].onload = () => {
            loadedImages++;
            if(loadedImages === totalImages) {
                init();
            }
        };
    }

    function updateSidebar() {
        document.getElementById("destroyedCount").innerText = "= " + destroyedWalls;
        document.getElementById("tntCount").innerText = "= " + playerTNT;
        document.getElementById("iceCount").innerText = "= " + iceCollected;
    }


    /* =========================
    MOVEMENT CONTROLS
    ========================= */

    let playerBombCount = 0;
    let activeBomb = null;
    let isFrozen = false;
    let playerTNT = 0;
    let iceCollected = 0;
    
    let lastPosition = posKey(playerRow, playerCol);
    let playerDirection = 'down';
    document.addEventListener("keydown", function(e) {
        if(e.key === "Escape") {
            togglePause();
            return;
        }

        if(isPaused || gameOver || isFrozen) return;

        let newRow = playerRow;
        let newCol = playerCol;

        if(e.key === "w" || e.key === "W") {
            newRow--;
            playerDirection = 'up';
        } else if(e.key === "s" || e.key === "S") {
            newRow++;
            playerDirection = 'down';
        } else if(e.key === "a" || e.key === "A") {
            newCol--;
            playerDirection = 'left';
        } else if(e.key === "d" || e.key === "D") {
            newCol++;
            playerDirection = 'right';
        }

        // Place bomb
        if(e.code === "Space") {
            if(isFrozen) return;
            if(playerBombCount <= 0 && playerTNT <= 0) return;

            const bombRow = playerRow;
            const bombCol = playerCol;
            const key = posKey(bombRow, bombCol);

            if(wallPositions.includes(key) || (mapBomb && posKey(mapBomb.row, mapBomb.col) === key)) {
                return;
            }

            if(playerTNT > 0) {
                playerTNT--;
                activeBomb = {
                    row: bombRow,
                    col: bombCol,
                    type: "tnt"
                };

                setTimeout(() => {
                    explodeBomb(bombRow, bombCol, true);
                    activeBomb = null;
                }, 5000);

            } else {
                playerBombCount--;

                activeBomb = {
                    row: bombRow,
                    col: bombCol,
                    type: "bomb"
                };

                setTimeout(() => {
                    explodeBomb(bombRow, bombCol, false);
                    activeBomb = null;
                }, 5000);
            }
                        
            updateSidebar();
            return;
        }

        // Check movement collision
        if(isBorder(newRow, newCol) || 
           isForbidden(newRow, newCol) || 
           wallPositions.includes(posKey(newRow, newCol))) {
            return;
        }

        lastPosition = posKey(playerRow, playerCol);
        playerRow = newRow;
        playerCol = newCol;

        // Check bomb collection
        if(mapBomb && playerRow === mapBomb.row && playerCol === mapBomb.col) {
            playerBombCount++;
            mapBomb = null;
            bombPosition = null;
        }

        // Check item collection
        const itemIndex = items.findIndex(item => item.row === playerRow && item.col === playerCol);
        if(itemIndex > -1) {
            const item = items[itemIndex];
            if(item.type === 'tnt') {
                playerTNT++;
            } else if(item.type === 'ice') {
                iceCollected++;
                
                if(!isFrozen) {
                    isFrozen = true;
                    setTimeout(() => {
                        isFrozen = false;
                    }, 5000);
                }
            }
            items.splice(itemIndex, 1);
            updateSidebar();
        }
    });

</script>

</body>
</html>