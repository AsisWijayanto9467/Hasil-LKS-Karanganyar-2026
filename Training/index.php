<?php 
    if(isset($_POST["start"])) {
        $username = trim($_POST["username"]);
        $level = trim($_POST["level"]);

        if($username == "" || $level == "") {
            echo "<script>alert('Wajib mengisis username dan memilih level!');</script>";
        } else {
            $data = $username . "|" . $level;

            file_put_contents("storage/data.txt", $data);

            header("Location: game1.php");
            exit;
        }
    }

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>BombSkuy</title>
</head>
<style>
    body {
        margin: 0;
        font-family: Arial, Helvetica, sans-serif;
        display: flex;
        justify-content: center;
        align-items: center;
        height: 100vh;
        background: white;
    }

    .main {
        text-align: center;
    }

    .logo-img {
        width: 400px;
        margin-bottom: 15px;
    }

    .input-field {
        display: block;
        background: brown;
        padding: 10px;
        border: 5px solid black;
        font-size: 14px;
        font-weight: 600;
        color: white;
        border-radius: 7px;
        margin: 5px auto;
        width: 300px;
    }

    .input-field::placeholder {
        color: white;
        font-size: 14px;
        font-weight: 600;
    }

    .input-select {
        display: block;
        background: brown;
        padding: 10px;
        border: 5px solid black;
        font-size: 14px;
        font-weight: 600;
        color: white;
        border-radius: 7px;
        margin: 10px auto;
        width: 300px;
    }

    .input-select option {
        color: white;
        font-size: 14px;
        font-weight: 600;
    }

    .btn {
        background: black;
        border: none;
        border-radius: 10px;
        color: white;
        width: 140px;
        padding: 8px;
        font-size: 14px;
        font-weight: 600;
        cursor: pointer;

    }

    .btn:disabled {
        background: gray;
    }
    
    .instruction-box {
        display: none;
        position: fixed;
        left: 50%;
        top: 50%;
        transform: translate(-50%, -50%);
        color: white;
        background: black;
        box-shadow: 0 0 20px rgba(0, 0, 0, 0.7);
        border-radius: 10px;
        padding: 10px;
        width: 420px;
    }

    .close {
        background: red;
        float: right;
        border-radius: 50%;
        font-weight: 600;
        padding: 10px;
        cursor: pointer;
    }
</style>
<body>
    <div class="main">
        <img src="./Images/logo.png" class="logo-img" alt="logo">

        <form method="POST" id="gameForm">
            <input type="text" class="input-field" name="username" id="username" placeholder="Masukan Username anda" required>

            <select name="level" id="level" class="input-select">
                <option value="">Pilih Level</option>
                <option value="Easy">Easy</option>
                <option value="Medium">Medium</option>
                <option value="Hard">Hard</option>
            </select>

            <div class="">
                <button class="btn" type="submit" name="start" disabled>Play</button>
                <button class="btn" type="button" onclick="showInstruction()">Instruction</button>
            </div>
        </form>
    </div>

    <div class="instruction-box" id="instructionBox">
        <span class="close" onclick="closeInstruction()">X</span>
        <h1>How to play the Game?</h1>
        <ol>
            <li>Enter Username</li>
            <li>Enter Username</li>
            <li>Enter Username</li>
            <li>Enter Username</li>
            <li>Enter Username</li>
        </ol>
    </div>

    <script>
        const playBtn = document.querySelector("button[name='start']");
        const username = document.getElementById("username");
        const level = document.getElementById("level");
        const form = document.getElementById("gameForm");
        const instruction = document.getElementById("instructionBox");

        username.addEventListener("input", function() {
            playBtn.disabled = username.value.trim() === "";
        });

        form.addEventListener("submit", function(e) {
            if(level.value == "") {
                e.preventDefault();
                alert("please pick the difficulty level");
            }
        });

        function showInstruction() {
            instruction.style.display = "block";
        }
        function closeInstruction() {
            instruction.style.display = "none";
        }
    </script>
</body>
</html>