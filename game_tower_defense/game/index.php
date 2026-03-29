<?php 
    session_start(); 
    if(isset($_POST["start"])) {
        $username = trim($_POST["username"]);

        if($username == "") {
            echo "<script>alert('Wajib mengisi username');</script>";
        } else {
            $filePath = "../storage/data.txt";
            
            // 1. Ambil data lama atau buat array kosong jika file belum ada
            $allUsers = [];
            if(file_exists($filePath)) {
                $content = file_get_contents($filePath);
                $allUsers = json_decode($content, true) ?: [];
            }

            // 2. Cek apakah user sudah ada (Case Insensitive)
            $found = false;
            $currentUserData = null;
            $usernameKey = strtolower($username); // Ubah ke kecil untuk pengecekan

            foreach($allUsers as $key => $data) {
                if(strtolower($key) === $usernameKey) {
                    $found = true;
                    $username = $key; // Gunakan nama asli yang tersimpan (misal: Budi)
                    break;
                }
            }

            // 3. Jika user baru, buat data default
            if(!$found) {
                // Progress default: Level 1-20 dimulai dengan 0 bintang
                $allUsers[$username] = [
                    "progress" => array_fill(1, 20, 0) 
                ];
                file_put_contents($filePath, json_encode($allUsers));
            }

            // 4. Simpan username ke Session untuk digunakan di halaman level
            $_SESSION['active_user'] = $username;
            
            header("Location: level.php");
            exit();
        }
    }
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tower Defense</title>
    <style>
        body {
            margin: 0;
            font-family: Arial, Helvetica, sans-serif;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            background: #1a1a1a;
        }

        .main {
            text-align: center;
            width: 100%;
        }

        .logo-img {
            width: 440px;
            margin-bottom: 10px;
        }

        #gameForm {
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 10px; 
        }

        .input-field, .input-select {
            display: block;
            background: blue;
            padding: 12px;
            border: 3px solid gray;
            font-size: 14px;
            font-weight: 600;
            color: white;
            border-radius: 7px;
            width: 400px;
            box-sizing: border-box;
        }

        .input-field::placeholder {
            color: rgba(255, 255, 255, 0.7);
        }

        .btn {
            background: black;
            border: none;
            border-radius: 10px;
            color: white;
            width: 400px; 
            padding: 12px;
            font-size: 14px;
            font-weight: 600;
            cursor: pointer;
            transition: 0.3s;
        }

        .btn:hover {
            opacity: 0.8;
        }

        .btn:disabled {
            background: gray;
            cursor: not-allowed;
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
            padding: 20px;
            width: 80%;
            max-width: 400px;
            z-index: 100;
        }

        .close {
            background: red;
            float: right;
            border-radius: 50%;
            font-weight: bold;
            padding: 5px 10px;
            cursor: pointer;
        }
    </style>
</head>
<body>

    <div class="main">
        <img src="../images/new-logo.png" class="logo-img" alt="logo">

        <form method="POST" id="gameForm">
            <input type="text" class="input-field" name="username" id="username" placeholder="Masukan Username anda" required>

            <button class="btn" type="submit" name="start" id="playBtn" disabled>Play</button>
            
            <button class="btn" type="button" onclick="showInstruction()" style="background: #333;">Instruction</button>
        </form>
    </div>

    <div class="instruction-box" id="instructionBox">
        <span class="close" onclick="closeInstruction()">X</span>
        <h2>How to Play</h2>
        <ol>
            <li>Masukkan nama user kamu.</li>
            <li>Pilih tingkat kesulitan level.</li>
            <li>Klik tombol Play untuk memulai.</li>
            <li>Bertahanlah dari serangan musuh!</li>
        </ol>
    </div>

    <script>
        const playBtn = document.getElementById("playBtn");
        const username = document.getElementById("username");
        const instruction = document.getElementById("instructionBox");

        username.addEventListener("input", function() {
            playBtn.disabled = username.value.trim() === "";
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