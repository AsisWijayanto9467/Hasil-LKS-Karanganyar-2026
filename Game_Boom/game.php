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
<title>BOMSKUY</title>

<style>
    body {
        margin: 0;
        font-family: Arial, sans-serif;
        background: #ffffff;
    }

    /* WRAPPER */
    .wrapper {
        min-height: 100vh;
        display: flex;
        justify-content: center;
        align-items: center;
        padding: 15px;
        box-sizing: border-box;
    }

    /* CONTAINER */
    .game-container {
        display: flex;
    }

    /* =========================
    GAME BOARD
    ========================= */
    .game-board {
        position: relative;
    }

    .game-board img.bg {
        height: 95vh;
        display: block;
    }

    /* WALL SPAWN */
    .wall {
        position: absolute;
        width: 75px;   /* <-- ukuran wall bisa kamu ubah disini */
        height: 75px;
    }

    /* =========================
    SIDEBAR
    ========================= */
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
    }

    .hearts img {
        width: 40px;
        margin-right: 8px;
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


    .pause-overlay{
        position: fixed;
        inset: 0;
        background: rgba(0,0,0,0.7);
        display: none;
        justify-content: center;
        align-items: center;
        z-index: 999;
    }

    .pause-box{
        background: #2e2e2e;
        padding: 40px 60px;
        border-radius: 10px;
        text-align: center;
        color: white;
    }

    .pause-box h2{
        font-size: 40px;
        margin-bottom: 20px;
    }

    .pause-box button{
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

        <!-- GAME BOARD -->
        <div class="game-board">
            <img src="Images/background.jpg" class="bg">
        </div>

        <!-- SIDEBAR -->
        <div class="sidebar">
            <h1>BOMSKUY</h1>

            <div class="info">
                <p><strong>Player</strong> : <?php echo $username; ?></p>
                <p><strong>Time</strong> : <span id="timer">00:00</span></p>
            </div>

            <div class="hearts">
                <img src="images/heart.png" class="heart">
                <img src="images/heart.png" class="heart">
                <img src="images/heart.png" class="heart">
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
    const board = document.querySelector(".game-board");

    const tileSize = 73;
    const rows = 9;
    const cols = 11;
    const totalWall = 10;

    const forbidden = [
        "2-2","4-2","6-2",
        "2-4","4-4","6-4",
        "2-6","4-6","6-6",
        "2-8","4-8","6-8"
    ];

    const wallPositions = [];
    const dogs = [];
    let bombPosition = null;

    let playerBombCount = 0;

    let playerRow = 1;
    let playerCol = 1;
    let player;

    const itemPositions = {}; 
    let playerTNT = 0;
    let playerIce = 0;

    let isFrozen = false;
    let lastPosition = posKey(playerRow, playerCol);

    let destroyedWalls = 0;
    let iceCollected = 0;

    let playerLives = 3;
    let gameOver = false;

    let seconds = 0;
    let timerInterval;

    let isPaused = false;
    let dogInterval;

    /* =========================
    HELPER
    ========================= */

    function posKey(row,col){
        return row + "-" + col;
    }

    function isBorder(row,col){
        return row === 0 || col === 0 || row === rows-1 || col === cols-1;
    }

    function isForbidden(row,col){
        return forbidden.includes(posKey(row,col));
    }

    function isOccupied(row,col){
        const key = posKey(row,col);
        const dogHere = dogs.some(d => posKey(d.row,d.col) === key);
        return wallPositions.includes(key) ||
            dogHere ||
            key === posKey(playerRow,playerCol);
    }

    function isDogAt(row, col, currentDog = null){
        return dogs.some(d => 
            d !== currentDog &&
            d.row === row &&
            d.col === col
        );
    }

    /* =========================
    Timer
    ========================= */

    function startTimer(){
        timerInterval = setInterval(()=>{
            if(gameOver) return;

            seconds++;

            let mins = Math.floor(seconds / 60);
            let secs = seconds % 60;

            if(mins < 10) mins = "0" + mins;
            if(secs < 10) secs = "0" + secs;

            document.getElementById("timer").innerText = mins + ":" + secs;

        },1000);
    }

    function togglePause(){
        if(gameOver) return;

        isPaused = !isPaused;

        const overlay = document.getElementById("pauseOverlay");

        if(isPaused){

            overlay.style.display = "flex";
            clearInterval(timerInterval);
            clearInterval(dogInterval);

        }else{

            overlay.style.display = "none";
            startTimer();
            dogInterval = setInterval(moveDogs, 600);
        }
    }

    // Save Game gameOver

    function sendToGameOver(){
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

    /* =========================
    Take Damage
    ========================= */

    function takeDamage(){
        if(gameOver) return;

        playerLives--;

        const hearts = document.querySelectorAll(".heart");

        if(hearts[playerLives]){
            hearts[playerLives].src = "Images/heart_broke.png";
        }

        if(playerLives <= 0){
            gameOver = true;
            clearInterval(timerInterval);

            sendToGameOver();
        }
    }


    /* =========================
    SPAWN WALL
    ========================= */

    function spawnWall(){
        let row,col;

        do{
            row = Math.floor(Math.random()*rows);
            col = Math.floor(Math.random()*cols);
        }
        while(
            isBorder(row,col) ||
            isForbidden(row,col) ||
            isOccupied(row,col)
        );

        wallPositions.push(posKey(row,col));

        const wall = document.createElement("img");
        wall.src = "images/wall.png";
        wall.classList.add("wall");
        wall.style.left = (col*tileSize)+"px";
        wall.style.top  = (row*tileSize)+"px";

        board.appendChild(wall);
    }

    /* =========================
    SPAWN PLAYER
    ========================= */

    function spawnPlayer(){
        player = document.createElement("img");
        player.src = "images/char_down.png";
        player.classList.add("wall");
        updatePlayerPosition();
        board.appendChild(player);
    }

    function updatePlayerPosition(){
        player.style.left = (playerCol*tileSize)+"px";
        player.style.top  = (playerRow*tileSize)+"px";
    }

    /* =========================
    SPAWN DOG
    ========================= */

    const level = "<?php echo $level; ?>";
    let totalDog = 1;

    if(level === "Medium") totalDog = 2;
    if(level === "Hard") totalDog = 3;

    function spawnDog(){
        let row,col;

        do{
            row = Math.floor(Math.random()*rows);
            col = Math.floor(Math.random()*cols);
        }
        while(
            isBorder(row,col) ||
            isForbidden(row,col) ||
            isOccupied(row,col) ||
            isDogAt(row,col)
        );

        const dog = document.createElement("img");
        dog.src = "images/dog_down.png";
        dog.classList.add("wall");
        dog.style.left = (col*tileSize)+"px";
        dog.style.top  = (row*tileSize)+"px";

        board.appendChild(dog);

        dogs.push({
            row: row,
            col: col,
            element: dog
        });
    }

    function moveDogs(){
        dogs.forEach(dog => {

            let newRow = dog.row;
            let newCol = dog.col;

            // PRIORITAS GERAK VERTIKAL DULU
            if(playerRow < dog.row){
                newRow--;
                dog.element.src = "images/dog_up.png";
            }
            else if(playerRow > dog.row){
                newRow++;
                dog.element.src = "images/dog_down.png";
            }
            else if(playerCol < dog.col){
                newCol--;
                dog.element.src = "images/dog_left.png";
            }
            else if(playerCol > dog.col){
                newCol++;
                dog.element.src = "images/dog_right.png";
            }

            // CEK TABRAKAN
            if(
                isBorder(newRow,newCol) ||
                isForbidden(newRow,newCol) ||
                wallPositions.includes(posKey(newRow,newCol)) ||
                isDogAt(newRow,newCol,dog)
            ){
                return;
            }

            dog.row = newRow;
            dog.col = newCol;

            dog.element.style.left = (dog.col*tileSize)+"px";
            dog.element.style.top  = (dog.row*tileSize)+"px";

            // CEK KENA PLAYER
            if(dog.row === playerRow && dog.col === playerCol){
                takeDamage();
            }

        });
    }

    /* =========================
    SPAWN BOMB (MAP)
    ========================= */

    function spawnBomb(){
        let row,col;

        do{
            row = Math.floor(Math.random()*rows);
            col = Math.floor(Math.random()*cols);
        }
        while(
            isBorder(row,col) ||
            isForbidden(row,col) ||
            isOccupied(row,col)
        );

        bombPosition = posKey(row,col);

        const bomb = document.createElement("img");
        bomb.src = "images/bomb.png";
        bomb.classList.add("wall");
        bomb.id = "mapBomb";
        bomb.style.left = (col*tileSize)+"px";
        bomb.style.top  = (row*tileSize)+"px";

        board.appendChild(bomb);
    }

    function explodeBomb(row, col, bombElement, radius = 1){

        // hapus bomb dari map
        bombElement.remove();
        wallPositions.splice(wallPositions.indexOf(posKey(row,col)),1);

        const directions = [
            {r:0, c:0},   // tengah
            {r:-1, c:0},  // atas
            {r:1, c:0},   // bawah
            {r:0, c:-1},  // kiri
            {r:0, c:1}    // kanan
        ];

        directions.forEach(dir => {

            for(let i = 0; i <= radius; i++){

                const newRow = row + (dir.r * i);
                const newCol = col + (dir.c * i);

                if(
                    isBorder(newRow,newCol) ||
                    isForbidden(newRow,newCol)
                ){
                    break;
                }

                const key = posKey(newRow,newCol);

                // Jika player terkena ledakan
                if(newRow === playerRow && newCol === playerCol){
                    takeDamage();
                }

                const explode = document.createElement("img");
                explode.src = "images/explode.png";
                explode.classList.add("wall");
                explode.style.left = (newCol * tileSize) + "px";
                explode.style.top  = (newRow * tileSize) + "px";

                board.appendChild(explode);

                // =========================
                // JIKA KENA WALL
                // =========================
                if(wallPositions.includes(key)){

                    wallPositions.splice(wallPositions.indexOf(key),1);

                    destroyedWalls++;
                    updateSidebar();

                    const walls = document.querySelectorAll(".wall");
                    walls.forEach(w => {
                        if(
                            w.style.left === (newCol * tileSize) + "px" &&
                            w.style.top === (newRow * tileSize) + "px"
                        ){
                            w.remove();
                        }
                    });

                    // =========================
                    // RANDOM DROP SYSTEM (TETAP ADA)
                    // =========================
                    const random = Math.floor(Math.random() * 100);
                    let dropType = null;

                    if(random < 30){
                        dropType = "tnt";
                    }
                    else if(random < 90){
                        dropType = "ice";
                    }

                    if(dropType){

                        const item = document.createElement("img");

                        if(dropType === "tnt"){
                            item.src = "images/tnt.png";
                        }else{
                            item.src = "images/ice.png";
                        }

                        item.classList.add("wall");
                        item.style.left = (newCol * tileSize) + "px";
                        item.style.top  = (newRow * tileSize) + "px";

                        board.appendChild(item);

                        itemPositions[key] = dropType;
                    }
                }

                // hapus animasi ledakan
                setTimeout(()=>{
                    explode.remove();
                },500);
            }

        });
    }

    function updateSidebar(){
        document.getElementById("destroyedCount").innerText = "= " + destroyedWalls;
        document.getElementById("tntCount").innerText = "= " + playerTNT;
        document.getElementById("iceCount").innerText = "= " + iceCollected;
    }

    /* =========================
    INIT GAME
    ========================= */

    spawnPlayer();

    for(let i=0;i<totalWall;i++){
        spawnWall();
    }

    for(let i=0;i<totalDog;i++){
        spawnDog();
    }

    spawnBomb();
    updateSidebar();
    startTimer();
    dogInterval = setInterval(moveDogs, 600);

    /* =========================
    MOVEMENT
    ========================= */

    document.addEventListener("keydown",function(e){
        if(e.key === "Escape"){
            togglePause();
            return;
        }

        if(isPaused) return;
        
        if(isFrozen) return;

        let newRow = playerRow;
        let newCol = playerCol;

        if(e.key==="w"||e.key==="W"){
            newRow--;
            player.src="images/char_up.png";
        }
        else if(e.key==="s"||e.key==="S"){
            newRow++;
            player.src="images/char_down.png";
        }
        else if(e.key==="a"||e.key==="A"){
            newCol--;
            player.src="images/char_left.png";
        }
        else if(e.key==="d"||e.key==="D"){
            newCol++;
            player.src="images/char_right.png";
        }

        // DROP BOMB
        if(e.code === "Space"){

            if(isFrozen) return;

            if(playerBombCount <= 0 && playerTNT <= 0) return;

            const bombRow = playerRow;
            const bombCol = playerCol;
            const key = posKey(bombRow, bombCol);

            if(
                wallPositions.includes(key) ||
                bombPosition === key
            ){
                return;
            }

            let radius = 1;
            let type = "bomb";

            const bomb = document.createElement("img");

            // PRIORITAS TNT
            if(playerTNT > 0){

                playerTNT--;
                radius = 2;
                type = "tnt";

                bomb.src = "images/tnt.png";
            }
            else{
                playerBombCount--;
                bomb.src = "images/bomb.png";
            }

            bomb.classList.add("wall");
            bomb.style.left = (bombCol * tileSize) + "px";
            bomb.style.top  = (bombRow * tileSize) + "px";

            board.appendChild(bomb);
            wallPositions.push(key);

            setTimeout(()=>{
                explodeBomb(bombRow, bombCol, bomb, radius);
            },5000);

            return;
        }

        // Stop jika wall
        if(
            isBorder(newRow,newCol) ||
            isForbidden(newRow,newCol) ||
            wallPositions.includes(posKey(newRow,newCol))
        ){
            return;
        }

        lastPosition = posKey(playerRow, playerCol);
        playerRow=newRow;
        playerCol=newCol;
        updatePlayerPosition();
        /* =========================
        CEK AMBIL BOMB
        ========================= */
        if(bombPosition === posKey(playerRow, playerCol)){

            playerBombCount++;

            const mapBomb = document.getElementById("mapBomb");
            if(mapBomb) mapBomb.remove();

            bombPosition = null;
        }

        const currentKey = posKey(playerRow, playerCol);

        if(itemPositions[currentKey] && currentKey !== lastPosition){
            const type = itemPositions[currentKey];

            if(type === "tnt"){
                playerTNT++;
                updateSidebar();
            } else if(type === "ice"){
                iceCollected++;
                updateSidebar();

                if(isFrozen) return;

                isFrozen = true;

                player.style.opacity = "0.5";

                setTimeout(()=>{
                    isFrozen = false;
                    player.style.opacity = "1";
                },5000);
            }

            // hapus dari map
            delete itemPositions[currentKey];

            const items = document.querySelectorAll(".wall");
            items.forEach(i=>{
                if(
                    i.style.left === (playerCol * tileSize) + "px" &&
                    i.style.top === (playerRow * tileSize) + "px"
                ){
                    if(i.src.includes("tnt") || i.src.includes("ice")){
                        i.remove();
                    }
                }
            });
        }
    });

</script>

</body>
</html>