<?php 
    $file = "storage/data.txt";

    if(!file_exists($file)) {
        echo "<script>alert('file tidak ditemukan'); window.location = 'index.php';</script>";
    }

    $data = file_get_contents($file);

    if(empty($data)) {
        echo "<script>alert('data file kosong'); window.location = 'index.php';</script>";
    }

    list($username, $level) = explode("|", $data);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>game 2</title>
    <style>
        body {
            margin: 0;
            font-family: Arial, Helvetica, sans-serif;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 90vh;
            padding: 15px;
            background: white;
            box-sizing: border-box;
        }

        .main-container {
            display: flex;
            box-shadow: 0 0 20px rgba(0, 0, 0, 0.7);
            margin-top: 70px;
        }

        .main-board {
            height: 657px;
            width: 803px;
            box-sizing: border-box;
        }

        #canvas {
            left: 0;
            top: 0;
            height: 100%;
            width: 100%;
        }

        .sidebar {
            width: 300px;
            background: gray;
            padding: 20px;
            color: white;
        }

        .sidebar h1 {
            text-align: center;
            font-size: 45px;
            font-weight: 600;
        }

        .hearts {
            display: flex;
            gap: 8px;
            margin-bottom: 20px;
        }

        .heart {
            width: 45px;
            height: 45px;
        }

        .info {
            margin-bottom : 10px;
        }

        .items {
            display: flex;
            flex-direction: column;
            gap: 15px;
        }


        .score-item {
            display: flex;
            align-items: center;
            gap: 16px;
        }

        .score-item img {
            width: 45px;
            margin-bottom: 10px;
        }

        .overlay {
            display: none;
            position: absolute;
            left: 50%;
            top: 50%;
            transform: translate(-50%, -50%);
        }

        .overlay-box {
            position: relative;
            text-align: center;
            width: 360px;
            height: 200px;
            background: black;
            border-radius: 10px;
            box-shadow: 0 0 20 rgba(0, 0, 0, 0.7);
            color: white;
            padding: 20px;
        }

        .overlay-box h1 {
            margin-top: 65px;
        }

        .btn-continue {
            background: red;
            width: 250px;
            border: none;
            padding: 8px;
            border-radius: 10px;
            font-size: 14px;
            font-weight: 600;
            color: white;
        }
    </style>
</head>
<body>
    <div class="">
        <div class="main-container">
            <div class="main-board">
                <canvas height="657" width="803" id="canvas"></canvas>
            </div>

            <div class="sidebar">
                <h1>BombsKuy</h1>

                <div class="info">
                    <p><strong>Player</strong> : <?php echo $username ?></p> 
                    <p><strong>Time</strong> : <span id="timer">00:00</span></p> 
                </div>

                <div class="hearts">
                    <img src="./Images/heart.png" alt="heart" id="heart1" class="heart">
                    <img src="./Images/heart.png" alt="heart" id="heart2" class="heart">
                    <img src="./Images/heart.png" alt="heart" id="heart3" class="heart">
                </div>

                <div class="items">
                    <div class="score-item">
                        <img src="./Images/wall_crack.png" alt="wall_crack" id="destroyedCount">
                        <span>= 0</span>
                    </div>
                    <div class="score-item">
                        <img src="./Images/tnt.png" alt="tnt" id="tntCount">
                        <span>= 0</span>
                    </div>
                    <div class="score-item">
                        <img src="./Images/ice.png" alt="ice" id="iceCount">
                        <span>= 0</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="overlay" id="pauseOverlay">
        <div class="overlay-box">
            <h1>Game Paused</h1>
            <button class="btn-continue">Continue</button>
        </div>
    </div>


    <script>
        const canvas = document.getElementById("canvas");
        const ctx = canvas.getContext("2d");

        const images = {
            background: new Image(),
            char_up: new Image(),
            char_down: new Image(),
            char_left: new Image(),
            char_right: new Image(),
            dog_up: new Image(),
            dog_down: new Image(),
            dog_left: new Image(),
            dog_right: new Image(),
            tnt: new Image(),
            ice: new Image(),
            bomb: new Image(),
            wall: new Image(),
            wall_crack: new Image(),
        }

        images.background.src = "./images/background.jpg";
        images.char_up.src = "./images/char_up.png";
        images.char_down.src = "./images/char_down.png";
        images.char_left.src = "./images/char_left.png";
        images.char_right.src = "./images/char_right.png";
        images.dog_up.src = "./images/dog_up.png";
        images.dog_down.src = "./images/dog_down.png";
        images.dog_left.src = "./images/dog_left.png";
        images.dog_right.src = "./images/dog_right.png";
        images.ice.src = "./images/ice.png";
        images.tnt.src = "./images/tnt.png";
        images.bomb.src = "./images/bomb.png";
        images.wall.src = "./images/wall.png";
        images.wall_crack.src = "./images/wall_crack.png";

        const tileSize = 73;
        const rows = 9;
        const cols = 11;

        let playerRow = 1;
        let playerCol = 1;

        const forbidden = [
            "2-2", "2-4", "2-6", "2-8",
            "4-2", "4-4", "4-6", "4-8",
            "6-2", "6-4", "6-6", "6-8"
        ];

        function posKey(row, col) {
            return row + "-" + col;
        }

        function isBorder(row, col) {
            return row === 0 || col === 0 || row === rows -1 || col === cols -1;
        }

        function isForbidden(row, col) {
            return forbidden.includes(posKey(row, col));
        }

        let dogs = [];
        let wallPositions = [];
        function isOccupied(row, col) {
            const key = posKey(row, col);
            const dogHere = dogs.some(d => posKey(d.row, d.col) === key);
            return wallPositions.includes(posKey(row, col)) || dogHere || key === posKey(playerRow, playerCol);
        }

        function isDogAt(row, col, currentDog = null) {
            return dogs.some(d => d !== currentDog && d.row === row && d.col === col)
        }

        function spawnWall() {
            let row, col;
            do {
                row = Math.floor(Math.random() * rows);
                col = Math.floor(Math.random() * cols);
            } while(isBorder(row, col) || isForbidden(row, col) || isOccupied(row, col));

            wallPositions.push(posKey(row, col));
        }

        function spawnDog() {
            const level = "<?php echo $level ?>";
            const totalDog = level === "Medium" ? 2: level === "Hard" ? 3 : 1;

            for(let i = 0; i < totalDog; i++) {
                let row, col;
                do {
                    row = Math.floor(Math.random() * rows);
                    col = Math.floor(Math.random() * cols);
                } while(isBorder(row, col) || isForbidden(row, col) || isOccupied(row, col));

                dogs.push({
                    row: row,
                    col: col,
                    direction: "down"
                });
            }
        }

        let gameLoop;
        function draw() {
            ctx.clearRect(0, 0, canvas.width, canvas.height);

            ctx.drawImage(images.background, 0, 0, canvas.width, canvas.height);

            wallPositions.forEach(pos => {
                const [row, col] = pos.split("-").map(Number);
                ctx.drawImage(images.wall, col *  tileSize, row * tileSize, tileSize, tileSize);
            })
            gameLoop = requestAnimationFrame(draw);
        }

        const totalWall = 10;
        function init() {
            for(let i = 0; i < totalWall; i++) {
                spawnWall();
            }

            spawnDog();

            draw();
        }

        let loadedImage = 0;
        let totalImage = Object.keys(images).length;
        for(let key in images) {
            images[key].onload = () => {
                loadedImage++;
                if(loadedImage === totalImage) {
                    init();
                }
            }
        }


    </script>
</body>
</html>