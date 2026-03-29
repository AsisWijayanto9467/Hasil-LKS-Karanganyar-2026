<?php

if(isset($_POST['start'])){

    $username = trim($_POST['username']);
    $level = trim($_POST['level']);

    if($username == "" || $level == ""){
        echo "<script>alert('Username dan Level wajib diisi!');</script>";
    } else {

        // Format data yang akan disimpan
        $data = $username . "|" . $level;

        // Simpan ke storage/data.txt
        file_put_contents("storage/data.txt", $data);

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

    .logo-img{
        width:280px;
        margin-bottom:20px;
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
</style>
</head>
<body>

<div class="main">

    <img src="Images/logo.png" class="logo-img">

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
            <button type="submit" name="start" class="btn" disabled>Play</button>
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


<script>
    const username = document.getElementById("username");
    const level = document.getElementById("level");
    const form = document.getElementById("gameForm");
    const playBtn = document.querySelector("button[name='start']");

    username.addEventListener("input", function(){
        playBtn.disabled = username.value.trim() === "";
    });

    form.addEventListener("submit", function(e){

        if(level.value === ""){
            e.preventDefault();
            alert("Please select difficulty level!");
        }

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