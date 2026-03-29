<?php 
    $file = "storage/data.txt";

    if(!file_exists($file)) {
        echo "<script>alert('File tidak ditemukan!); window.location='index.php'</script>";
    }

    $data = file_get_contents($file);

    if(empty($data)) {
        echo "<script>alert('data tidak ditemukan!'); window.location='index.php'</script>";
    }

    list($username, $level) = explode("|", $data);
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Try</title>
</head>
<style>
    body {
        margin: 0;
        font-family: Arial, Helvetica, sans-serif;
        padding: 15px;
        display: flex;
        justify-content: center;
        align-items: center;
        background: white;
        overflow: hidden;
        box-sizing: border-box;
    }

    .game-container {
        display: flex;
        box-shadow: 0 0 20px rgba(0, 0, 0, 0.3);
    }

    .game-board {
        position: relative;
        height: 657px;
        width: 803px;
    }

    #gameCanvas {
        position: absolute;
        left: 0;
        top: 0;
        width: 100%;
        height: 100%;
        display: block;
    }

    .sidebar {
        background: gray;
        width: 300px;
        box-sizing: border-box;
        padding: 40px;
        color: white;
    }

    .sidebar h1 {
        font-weight: 600;
        font-size: 45px;
    }

    .info {
        font-size: 14px;
    }

    .hearts {
        display: flex;
        margin-bottom: 15px;
    }

    .hearts img {
        width: 45px;
        height: 45px;
    }

    .score-item {
        display: flex;
        align-items: center;
        margin-bottom: 15px;
        gap: 8px;
    }

    .score-item img {
        width: 45px;
        height: 45px;
    }

    .pause-overlay {
        position: fixed;
        inset: 0;
        display: none;
        justify-content: center;
        align-items: center;
        background: rgba(0, 0, 0, 0.7);
        z-index: 999;
    }

    .pause-box {
        background: black;
        padding: 40px 60px;
        color: white;
        justify-content: center;
        align-items: center;
        text-align: center;
        border-radius: 10px;
    }

    .pause-box h1 {
        font-size: 40px;
        margin-bottom: 20px;
    }

    .pause-box button {
        background: red;
        padding: 10px 40px;
        border-radius: 10px;
        color: white;
        border: none;
        font-size: 18px;
        font-weight: 600;
    }
</style>
<body>
    <div>
        <div class="game-container">
            <div class="game-board">
                <canvas id="gameCanvas" height="657" width="803"></canvas>
            </div>

            <div class="sidebar">
                <h1>BombSkuy</h1>

                <div class="info">
                    <p><strong>Player</strong> : <?php echo $username ?></p>
                    <p><strong>Timer</strong> : <span id="timer">00:00</span></p>
                </div>

                <div class="hearts">
                    <img src="./Images/heart.png" class="heart" alt="heart" id="heart1">
                    <img src="./Images/heart.png" class="heart" alt="heart" id="heart2">
                    <img src="./Images/heart.png" class="heart" alt="heart" id="heart3">
                </div>

                <div class="score-item">
                    <img src="./Images/wall_crack.png" alt="wall_crack">
                    <span id="destroyedCount">= 0</span>
                </div>
                <div class="score-item">
                    <img src="./Images/tnt.png" alt="tnt">
                    <span id="tntCount">= 0</span>
                </div>
                <div class="score-item">
                    <img src="./Images/ice.png" alt="ice">
                    <span id="iceCount">= 0</span>
                </div>
            </div>
        </div>
    </div>

    <div class="pause-overlay" id="pauseOverlay">
        <div class="pause-box">
            <h1>Game Paused</h1>
            <button onclick="togglePause()">Continue</button>
        </div>
    </div>

    <script>
        const canvas = document.getElementById("gameCanvas");
        const ctx = canvas.getContext("2d");

        const images = {
            background: new Image(),
            char_up: new Image(),
            char_down: new Image(),
            char_left: new Image(),
            char_right: new Image(),
            dog_up: new Image(),
            dog_left: new Image(),
            dog_down: new Image(),
            dog_right: new Image(),
            wall: new Image(),
            bomb: new Image(),
            tnt: new Image(),
            ice: new Image(),
            explode: new Image(),
        }


        images.background.src = "images/background.jpg";
        images.char_up.src = "images/char_up.png";
        images.char_down.src = "images/char_down.png";
        images.char_left.src = "images/char_left.png";
        images.char_right.src = "images/char_right.png";
        images.dog_up.src = "images/dog_up.png";
        images.dog_down.src = "images/dog_down.png";
        images.dog_left.src = "images/dog_left.png";
        images.dog_right.src = "images/dog_right.png";
        images.wall.src = "images/wall.png";
        images.ice.src = "images/ice.png";
        images.bomb.src = "images/bomb.png";
        images.tnt.src = "images/tnt.png";
        images.explode.src = "images/explode.png";

        const tileSize = 73;
        const cols = 11;
        const rows = 9;

        const forbidden = [
            "2-2", "2-4", "2-6", "2-8",
            "4-2", "4-4", "4-6", "4-8",
            "6-2", "6-4", "6-6", "6-8"
        ];

        function posKey(row, col) {
            return row + "-" + col;
        }

        function isBorder(row, col) {
            return row === 0 || col === 0 || row === rows - 1 || col === cols - 1;
        }

        function isForbidden(row, col) {
            return forbidden.includes(posKey(row, col));
        }

        let wallPositions = [];
        let dogs = [];
        let playerRow = 1;
        let playerCol = 1;

        function isOccupied(row, col) {
            const key = posKey(row, col);
            const dogHere = dogs.some(d => posKey(d.row, d.col) === key);
            return wallPositions.includes(key) || dogHere || key === posKey(playerRow, playerCol);
        }

        function isDogAt(row, col, currentDog = null) {
            return dogs.some(d => d !== currentDog && d.row === row && d.col === col);
        }

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

        function sendToGameOver() {
            const form = document.createElement("form");
            const time = document.getElementById("timer").innerText;
            form.method = "POST";
            form.action = "gameOver.php";

            const data = {
                username: "<?php echo $username ?>",
                time: time,
                wall: destroyedWalls,
                tnt: playerTNT,
                ice: iceCollected
            }

            for(let ket in data) {
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
            playerLives--;
            const hearts = document.querySelectorAll(".heart");

            if(hearts[playerLives]) {
                hearts[playerLives].src = "./Images/heart_broke.png"
            }

            if(playerLives <= 0) {
                gameOver = true;
                clearInterval(timerInterval);
                clearInterval(dogInterval);
                cancelAnimationFrame(gameLoop);
                sendToGameOver();
            }
        }
        
        function spawnWall() {
            let row, col;
            do {
                row = Math.floor(Math.random() * rows);
                col = Math.floor(Math.random() * cols);
            } while(isForbidden(row, col) || isOccupied(row, col) || isBorder(row, col));

            wallPositions.push(posKey(row, col));
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
            mapBomb = {
                row : row,
                col : col
            }
        }

        function spawnDog() {
            const level = "<?php echo $level ?>";
            const totalDog = level === "Medium" ? 2 : level === "Hard" ? 3 : 1;

            for(let i = 0; i < totalDog; i++) {
                let row, col;
                do {
                    row = Math.floor(Math.random() * rows);
                    col = Math.floor(Math.random() * cols);
                } while(isBorder(row, col) || isForbidden(row, col) || isOccupied(row, col));

                dogs.push({
                    row : row,
                    col : col,
                    direction : "down"
                });
            }
        }

        function moveDogs() {
            dogs.forEach(dog => {
                let newRow = dog.row;
                let newCol = dog.col;

                if(playerRow < dog.row) {
                    newRow--;
                    dog.direction = "up";
                } else if (playerRow > dog.row) {
                    newRow++;
                    dog.direction = "down";
                } else if(playerCol < dog.col) {
                    newCol--;
                    dog.direction = "left";
                } else if(playerCol > dog.col) {
                    newCol++;
                    dog.direction = "right";
                }

                if(isBorder(newRow, newCol) || isForbidden(newRow, newCol) || wallPositions.includes(posKey(newRow, newCol)) || isDogAt(newRow, newCol)) {
                    return;
                }

                dog.row = newRow;
                dog.col = newCol;

                if(dog.row === playerRow && dog.col === playerCol) {
                    console.log("aw");
                }
            })
        }


        let explosions = [];
        let items = [];
        let destroyedWalls = 0;
        function explodeBomb(row, col, isTNT = false) {
            const radius = isTNT ? 2 : 1;
            const centerKey = posKey(row, col);

            const index = wallPositions.indexOf(centerKey);
            if(index > -1) wallPositions.splice(index, 1);

            const directions = [
                {r: 0, c:0},
                {r: -1, c:0},
                {r: 1, c:0},
                {r: 0, c:-1},
                {r: 0, c:1},
            ]

            directions.forEach(dir => {
                for(let i = 0; i  <= radius; i++) {
                    const newRow = row + (dir.r * i);
                    const newCol = col + (dir.c * i);

                    if(isBorder(newRow, newCol) || isForbidden(newRow, newCol)) {
                        break;
                    }

                    const key = posKey(newRow, newCol);

                    explosions.push({
                        row: newRow,
                        col:  newCol,
                        timer: 10
                    });

                    if(newRow ===  playerRow && newCol === playerCol) {
                        console.log("aw");
                    }

                    const wallIndex = wallPositions.indexOf(key);
                    if(wallIndex > -1) {
                        wallPositions.splice(wallIndex, 1);
                        destroyedWalls++;

                        const random = Math.floor(Math.random() * 100);
                        if(random < 30) {
                            items.push({
                                row: newRow,
                                col: newCol,
                                type: "tnt"
                            });
                        } else if(random < 90) {
                            items.push({
                                row: newRow,
                                col: newCol,
                                type: "ice"
                            });
                        }
                    }

                    updateSidebar();
                }
            });
            if(mapBomb && mapBomb.row === row && mapBomb.col === col) {
                bombPosition = null;
                mapBomb = null;
            }
        }

        

        let gameLoop;
        function draw() {
            ctx.clearRect(0, 0, canvas.width, canvas.height);

            ctx.drawImage(images.background, 0, 0, canvas.width, canvas.height);

            wallPositions.forEach(pos => {
                const [row, col] = pos.split("-").map(Number);
                ctx.drawImage(images.wall, col * tileSize, row * tileSize, tileSize, tileSize);
            });

            if(mapBomb) {
                ctx.drawImage(images.bomb, mapBomb.col * tileSize, mapBomb.row * tileSize, tileSize, tileSize);
            }

            if(activeBomb) {
                const img = activeBomb.type === "tnt" ? images.tnt : images.bomb;

                ctx.drawImage(img, activeBomb.col * tileSize, activeBomb.row * tileSize, tileSize, tileSize);
            }

            items.forEach(item => {
                const img = item.type === "tnt" ? images.tnt : images.ice;

                ctx.drawImage(img, item.col * tileSize, item.row * tileSize, tileSize, tileSize);
            })

            ctx.globalAlpha = 0.5;
            let playerImg;
            switch(playerDirection) {
                case "up" : playerImg = images.char_up; break;
                case "down" : playerImg = images.char_down; break;
                case "left" : playerImg = images.char_left; break;
                case "right" : playerImg = images.char_right; break;
                default: player = images.char_down;
            }
            ctx.drawImage(playerImg, playerCol * tileSize, playerRow * tileSize, tileSize, tileSize);
            ctx.globalAlpha = 1.0;

            dogs.forEach(dog => {
                let img;
                switch(dog.direction) {
                    case "up": img = images.dog_up; break;
                    case "down": img = images.dog_down; break;
                    case "left": img = images.dog_left; break;
                    case "right": img = images.dog_right; break;
                    default: img = images.dog_down;
                }
                ctx.drawImage(img, dog.col * tileSize, dog.row * tileSize, tileSize, tileSize);
            });

            explosions = explosions.filter(exp => {
                ctx.drawImage(images.explode, exp.col * tileSize, exp.row * tileSize, tileSize, tileSize);
                exp.timer--;
                return exp.timer > 0;
            })
            
            gameLoop = requestAnimationFrame(draw);
        }


        let isPaused = false;
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
                dogInterval = setInterval(moveDogs(), 600);
                gameLoop = requestAnimationFrame(draw);
            }
        }
        
        
        const totalWall = 10;
        let dogInterval;
        function init() {
            for(let i = 0; i < totalWall; i ++) {
                spawnWall();
            }

            spawnBomb();
            startTimer();
            spawnDog();

            dogInterval = setInterval(moveDogs, 600);
            gameLoop = requestAnimationFrame(draw);
        }

        let loadedImages = 0;
        const totalImages = Object.keys(images).length;

        for(let key in images) {
            images[key].onload = () => {
                loadedImages++;
                if(loadedImages === totalImages) {
                    init();
                }
            }
        }

        function updateSidebar() {
            document.getElementById("destroyedCount").innerText = "= " + destroyedWalls;
            document.getElementById("tntCount").innerText = "= " + playerTNT;
            document.getElementById("iceCount").innerText = "= " + iceCollected;
        }

        let playerDirection = "down";
        
        let playerBombCount = 0;
        let playerTNT = 0;
        let iceCollected = 0;
        let activeBomb = null;
        let isFrozen = false;

        let lastPosition = posKey(playerRow, playerCol);
        document.addEventListener("keydown", function(e) 
        {
            if(e.key === "Escape") {
                togglePause();
                return;
            }

            if(isPaused || gameOver ||isFrozen) return;

            let newRow = playerRow;
            let newCol = playerCol;

            if(e.key === "w" || e.key === "W") {
                newRow--;
                playerDirection = "up";
            } else if(e.key === "s" || e.key === "S") {
                newRow++;
                playerDirection = "down";
            } else if(e.key === "a" || e.key === "A") {
                newCol--;
                playerDirection = "left";
            } else if(e.key === "d" || e.key === "D") {
                newCol++;
                playerDirection = "right";
            }

                        
            if(isForbidden(newRow, newCol) || isBorder(newRow, newCol) || wallPositions.includes(posKey(newRow, newCol))) {
                return;
            }

            if(e.code === "Space") {
                if(isFrozen) return;
                if(playerBombCount <= 0 && playerTNT <= 0);

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
                    }

                    setTimeout(() => {
                        explodeBomb(bombRow, bombCol, true);
                        activeBomb = null;
                    }, 5000);
                }  else {
                    playerBombCount--;
                    activeBomb = {
                        row: bombRow,
                        col: bombCol,
                        type: "bomb"
                    }

                    setTimeout(() => {
                        explodeBomb(bombRow, bombCol, false);
                        activeBomb = null;
                    }, 5000);
                }

                updateSidebar();
                return;
            }


            lastPosition = posKey(playerRow, playerCol);
            playerRow = newRow;
            playerCol = newCol;

            if(mapBomb && playerRow  === mapBomb.row && playerCol === mapBomb.col ) {
                playerBombCount++;
                mapBomb = null;
                bombPosition = null;
            }

            const itemIndex = items.findIndex(item => item.row === playerRow && item.col === playerCol);
            if(itemIndex > -1) {
                const item = items[itemIndex];

                if(item.type = "tnt") {
                    palyerTNT++;
                } else if(item.type = "ice") {
                    iceCollected++;

                    if(!isFrozen) {
                        isFrozen = true;
                        setTimeout(() => {
                            isFrozen = false
                        }, 5000);
                    }
                }

                items.splice(itemIndex, 1);
                updateSidebar();
            }
        })

        

        // function draw() {
        //     ctx.clearRect(0, 0, canvas.width, canvas.height);

        //     ctx.drawImage(images.background, 0, 0, canvas.width, canvas.height);
        // }

        // function init() {
        //     gameLoop = requestAnimationFrame(draw)
        // }

        // let loadedImages = 0;
        // const totalImages = Object.keys(images).length;

        // for(let key in images) {
        //     images[key].onload = () => {
        //         loadedImages++;
        //         if(loadedImages === totalImages) {
        //             init();
        //         }
        //     }
        // }
    </script>
</body>
</html>