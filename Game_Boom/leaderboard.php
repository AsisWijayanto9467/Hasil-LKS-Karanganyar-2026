<?php
    $filename = "storage/data_game.txt";
    $data = [];

    if(file_exists($filename)){

        $lines = file($filename, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

        foreach($lines as $line){

            $parts = explode(" | ", $line);
            $player = [];

            foreach($parts as $part){
                $keyValue = explode(": ", $part);
                if(count($keyValue) == 2){
                    $player[strtolower($keyValue[0])] = $keyValue[1];
                }
            }

            $data[] = $player;
        }
    }


    function timeToSeconds($time){
        if(!$time) return 0;

        $parts = explode(":", $time);
        if(count($parts) == 2){
            return ((int)$parts[0] * 60) + (int)$parts[1];
        }
        return 0;
    }

    // Urutkan berdasarkan waktu TERLAMA
    usort($data, function($a, $b){
        $timeA = timeToSeconds($a['time'] ?? "00:00");
        $timeB = timeToSeconds($b['time'] ?? "00:00");

        return $timeB - $timeA;
    });
?>

<?php
    if(isset($_POST['reset'])){
        file_put_contents($filename, "");
        header("Location: leaderboard.php");
        exit();
    }
?>

<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<title>Leaderboards</title>

<style>
    body {
        margin: 0;
        font-family: Arial, Helvetica, sans-serif;
        background: #3b3b3b;
        color: white;
        display: flex;
        justify-content: center;
        align-items: center;
        height: 100vh;
    }
    .container {
        text-align: center;
        width: 650px;
    }
    h1 {
        font-size: 48px;
        margin-bottom: 40px;
    }
    .table-header, .row {
        display: grid;
        grid-template-columns: 2fr 1fr 1fr 1fr 1fr;
        align-items: center;
        padding: 10px 0;
    }
    .table-header {
        font-weight: bold;
    }
    .row {
        border-bottom: 1px solid rgba(255,255,255,0.2);
    }
    .col-name {
        text-align: left;
        padding-left: 10px;
    }
    .col-time {
        text-align: center;
    }
    .col-icon img {
        width: 35px;
    }
    .buttons {
        margin-top: 40px;
    }
    button {
        border: none;
        padding: 12px 30px;
        font-size: 18px;
        font-weight: bold;
        border-radius: 6px;
        cursor: pointer;
        margin: 0 15px;
    }
    .btn-play {
        background: #d84315;
        color: white;
    }
    .btn-play:hover {
        background: #bf360c;
    }
    .btn-reset {
        background: #1565c0;
        color: white;
    }
    .btn-reset:hover {
        background: #0d47a1;
    }
    .hidden-form {
        display: none;
    }
</style>
</head>
<body>

<div class="container">

<h1>Leaderboards</h1>

<div class="table-header">
    <div class="col-name">Player Name</div>
    <div class="col-time">Time</div>
    <div class="col-icon"><img src="Images/wall_crack.png"></div>
    <div class="col-icon"><img src="Images/tnt.png"></div>
    <div class="col-icon"><img src="Images/ice.png"></div>
</div>

<?php $rank = 1; ?>
<?php foreach($data as $row): ?>
    <div class="row">
        <div class="col-name"><?= $rank++ ?>. <?= htmlspecialchars($row['username'] ?? '-') ?></div>
        <div class="col-time"><?= $row['time'] ?? '-' ?></div>
        <div><?= $row['wall'] ?? 0 ?></div>
        <div><?= $row['tnt'] ?? 0 ?></div>
        <div><?= $row['ice'] ?? 0 ?></div>
    </div>
<?php endforeach; ?>

<div class="buttons">
    <button class="btn-play" onclick="playAgain()">Play Again</button>
    <form method="POST" style="display:inline;">
        <button name="reset" class="btn-reset">Reset</button>
    </form>
</div>

</div>

<script>
function playAgain(){
    window.location.href = "index.php"; 
}
</script>

</body>
</html>