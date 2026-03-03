<?php
    $file = "storage/data_game.txt";

    $username = $_POST['username'] ?? "Player";
    $time = $_POST['time'] ?? "00:00";
    $destroyed = $_POST['wall'] ?? 0;
    $tnt = $_POST['tnt'] ?? 0;
    $ice = $_POST['ice'] ?? 0;

    // JIKA tombol Save diklik
    if(isset($_POST['save_score'])){
        $data = "Username: $username | Time: $time | Wall: $destroyed | TNT: $tnt | Ice: $ice" . PHP_EOL;

        file_put_contents($file, $data, FILE_APPEND);

        echo "<script>
                alert('Score berhasil diupload!');
            </script>";
    }
?>

<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<title>Game Over</title>

<style>
body{
    margin:0;
    font-family: Arial, sans-serif;
    background:#3b3b3b;
    color:white;
    display:flex;
    justify-content:center;
    align-items:center;
    height:100vh;
}

.container{
    text-align:center;
}

h1{
    font-size:60px;
    margin-bottom:10px;
}

.subtitle{
    font-size:20px;
    margin-bottom:40px;
    color:#ddd;
}

.results{
    display:flex;
    justify-content:center;
    align-items:center;
    gap:40px;
    font-size:30px;
    margin-bottom:40px;
}

.result-item{
    display:flex;
    align-items:center;
    gap:15px;
}

.result-item img{
    width:60px;
}

.buttons{
    display:flex;
    justify-content:center;
    gap:20px;
}

button{
    border:none;
    padding:15px 30px;
    font-size:18px;
    font-weight: 700;
    border-radius:6px;
    cursor:pointer;
    color:white;
}

.save{
    background:#d63b00;
}

.leaderboard{
    background:#0d6ea8;
}

button:hover{
    opacity:0.85;
}
</style>
</head>

<body>

<div class="container">

    <h1>Game Over!</h1>

    <div class="subtitle">
        Good job <?php echo htmlspecialchars($username); ?>! your time <?php echo $time; ?> with results:
    </div>

    <div class="results">

        <div class="result-item">
            <img src="Images/wall_crack.png">
            <span>= <?php echo $destroyed; ?></span>
        </div>

        <div class="result-item">
            <img src="Images/tnt.png">
            <span>= <?php echo $tnt; ?></span>
        </div>

        <div class="result-item">
            <img src="Images/ice.png">
            <span>= <?php echo $ice; ?></span>
        </div>

    </div>

    <div class="buttons">
        <form method="POST">
            <input type="hidden" name="username" value="<?php echo htmlspecialchars($username); ?>">
            <input type="hidden" name="time" value="<?php echo $time; ?>">
            <input type="hidden" name="wall" value="<?php echo $destroyed; ?>">
            <input type="hidden" name="tnt" value="<?php echo $tnt; ?>">
            <input type="hidden" name="ice" value="<?php echo $ice; ?>">

            <button type="submit" name="save_score" class="save">
                Save Score
            </button>
        </form>

        <button class="leaderboard" onclick="window.location='leaderboard.php'">
            Leaderboards
        </button>

    </div>
</div>

</body>
</html>