<?php
session_start();

if($_SERVER["REQUEST_METHOD"] == "POST"){

    $username = htmlspecialchars($_POST['username']);
    $level = htmlspecialchars($_POST['level']);

    if(!empty($username) && !empty($level)){

        $_SESSION['username'] = $username;
        $_SESSION['level'] = $level;

        header("Location: game.php");
        exit();
    }
}
?>

<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<title>BOMSKUY</title>
<style>

body{
    margin:0;
    font-family: Arial, Helvetica, sans-serif;
    background:#ededed;
    display:flex;
    justify-content:center;
    align-items:center;
    height:100vh;
}

.main{
    text-align:center;
}

.icons img{
    width:65px;
    height:65px;
    object-fit: contain;
}

.logo{
    font-size:60px;
    font-weight:900;
    letter-spacing:3px;
    margin: 0;
    display:flex;
    justify-content:center;
    align-items:center;
}

.logo-o{
    height:55px;
}


.input-select{
    display:block;
    width:330px;
    padding:12px;
    margin:10px auto;
    background:#e03a00;
    color:white;
    border:4px solid black;
    border-radius:6px;
    font-size:14px;
    font-weight: 600;
}

.input-field {
    display:block;
    width:300px;
    padding:12px;
    margin:10px auto;
    background:#e03a00;
    color:white;
    border:4px solid black;
    border-radius:6px;
    font-size:14px;
    font-weight: 600;
}

.input-field::placeholder{
    color:white;
    font-weight: 600;
}


.input-select option{
    color:white;
    font-weight: 600;
}

.btn{
    width:140px;
    padding:10px;
    margin:10px 5px;
    border:none;
    border-radius:6px;
    cursor:pointer;
    background:black;
    color:white;
    font-size:14px;
}

.btn:disabled{
    background:gray;
    cursor:not-allowed;
}

.instruction{
    display:none;
    position:fixed;
    width:420px;
    background:#111;
    color:white;
    padding:20px;
    border-radius:8px;
    box-shadow:0 0 15px rgba(0,0,0,0.5);
    top:50%;
    left:50%;
    transform:translate(-50%,-50%);
    text-align:left;
}

.close{
    float:right;
    background:red;
    padding:4px 9px;
    border-radius:50%;
    cursor:pointer;
}

.countdown{
    display:none;
    position:fixed;
    top:50%;
    left:50%;
    transform:translate(-50%,-50%);
    font-size:100px;
    font-weight:bold;
}

</style>
</head>
<body>

<div class="main">

    <div class="icons">
        <img src="Images/char_down.png">
        <img src="Images/wall.png">
        <img src="Images/dog_down.png">
    </div>

    <h1 class="logo">
        B
        <img src="Images/bomb.png" class="logo-o">
        MSKUY
    </h1>

    <form method="POST" id="gameForm">

        <input type="text" name="username" id="username"
        class="input-field" placeholder="Input Username" required>

        <select name="level" id="level" class="input-select" required>
            <option value="">Select Level</option>
            <option value="Easy">Easy</option>
            <option value="Medium">Medium</option>
            <option value="Hard">Hard</option>
        </select>

        <div>
            <button type="button" id="playBtn" class="btn" disabled>Play</button>
            <button type="button" class="btn" onclick="showInstruction()">Instruction</button>
        </div>

    </form>

</div>

<div class="instruction" id="instructionBox">
    <span class="close" onclick="closeInstruction()">X</span>
    <h3>How to play game</h3>
    <ol>
        <li>Input Username</li>
        <li>Select difficulty level</li>
        <li>Wait for countdown</li>
        <li>Put the bomb</li>
        <li>Blow up enemies</li>
        <li>Enjoy!</li>
    </ol>
</div>

<div class="countdown" id="countdown">3</div>

<script>

    let username = document.getElementById("username");
    let level = document.getElementById("level");
    let playBtn = document.getElementById("playBtn");
    let form = document.getElementById("gameForm");

    username.addEventListener("input", function(){
        playBtn.disabled = username.value.trim() === "";
    });

    playBtn.addEventListener("click", function(){

        if(level.value === ""){
            alert("Please select difficulty level!");
            return;
        }

        form.submit();
    });


    function showInstruction(){
        document.getElementById("instructionBox").style.display = "block";
    }

    function closeInstruction(){
        document.getElementById("instructionBox").style.display = "none";
    }

</script>

</body>
</html>