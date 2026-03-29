<?php 
session_start();

// Jika tidak ada user yang login, tendang balik ke index
if(!isset($_SESSION['active_user'])) {
    header("Location: index.php");
    exit();
}

$currentUser = $_SESSION['active_user'];
$filePath = "../storage/data.txt";
$userProgress = [];

// Ambil progress milik user yang sedang aktif
if(file_exists($filePath)) {
    $allData = json_decode(file_get_contents($filePath), true);
    if(isset($allData[$currentUser])) {
        $userProgress = $allData[$currentUser]['progress'];
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Select Level - Tower Defense</title>
    <style>
        body {
            margin: 0;
            padding: 20px;
            font-family: Arial, Helvetica, sans-serif;
            display: flex;
            flex-direction: column;
            align-items: center;
            min-height: 100vh;
            background: #1a1a1a;
            color: white;
        }

        h1 {
            margin-bottom: 30px;
            text-transform: uppercase;
            letter-spacing: 3px;
        }

        /* Container Grid untuk Level */
        .level-grid {
            display: grid;
            grid-template-columns: repeat(5, 1fr); /* 5 kolom */
            gap: 20px;
            width: 100%;
            max-width: 900px;
            margin-bottom: 40px;
        }

        /* Kotak Level */
        .level-card {
            background: #252525;
            border: 3px solid gray;
            border-radius: 15px;
            padding: 20px;
            text-align: center;
            cursor: pointer;
            transition: 0.3s;
            position: relative;
            overflow: hidden;
        }

        .level-card:hover {
            border-color: blue;
            transform: translateY(-5px);
            background: #2a2a2a;
        }

        .level-number {
            font-size: 24px;
            font-weight: bold;
            display: block;
            margin-bottom: 10px;
        }

        /* Container Bintang */
        .stars-container {
            display: flex;
            justify-content: center;
            gap: 5px;
        }

        .star-icon {
            width: 20px;
            height: 20px;
            object-fit: contain;
        }

        /* Tombol Kembali */
        .btn-back {
            background: #333;
            border: none;
            border-radius: 10px;
            color: white;
            width: 200px;
            padding: 12px;
            font-size: 14px;
            font-weight: 600;
            cursor: pointer;
            text-decoration: none;
            text-align: center;
            transition: 0.3s;
        }

        .btn-back:hover {
            background: black;
            opacity: 0.9;
        }

        /* Bintang Default (Redup/Gelap) */
        .star-icon.star-dim {
            filter: grayscale(100%) brightness(30%);
            opacity: 0.5;
        }

        /* Bintang Menyala (Tanpa filter) */
        .star-icon {
            width: 20px;
            height: 20px;
            object-fit: contain;
            transition: 0.3s; /* Animasi halus saat nanti menyala */
        }

        /* Responsif untuk HP */
        @media (max-width: 600px) {
            .level-grid {
                grid-template-columns: repeat(2, 1fr); 
            }
        }
    </style>
</head>
<body>

    <h1>Select Level</h1>
    <p>Player: <strong><?php echo htmlspecialchars($currentUser); ?></strong></p>

    <div class="level-grid">
        <?php 
        for ($i = 1; $i <= 20; $i++) {
            // Ambil bintang dari data user, jika tidak ada set 0
            $starsEarned = isset($userProgress[(string)$i]) ? $userProgress[(string)$i] : 0;
            
            echo "<div class='level-card' onclick=\"location.href='game.php?level=$i'\">";
            echo "<span class='level-number'>$i</span>";
            echo "<div class='stars-container'>";
            
            for ($s = 1; $s <= 3; $s++) {
                $dimClass = ($s > $starsEarned) ? "star-dim" : "";
                echo "<img src='../images/star.png' class='star-icon $dimClass' alt='star'>";
            }
            
            echo "</div>";
            echo "</div>";
        }
        ?>
    </div>

    <a href="index.php" class="btn-back">Logout / Back</a>

</body>
</html>