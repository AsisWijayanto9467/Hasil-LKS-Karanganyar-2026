<?php
// D2. Watermark - Simple PHP Watermark (Fixed for PHP 8.1+)

// Set error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Process form submission
$outputImage = null;
$error = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['image'])) {
    try {
        // Create images directory if not exists
        if (!file_exists('uploads')) {
            mkdir('uploads', 0777, true);
        }
        
        // Load main image
        $mainImage = $_FILES['image']['tmp_name'];
        $mainInfo = getimagesize($mainImage);
        
        // Create image resource based on type
        switch ($mainInfo['mime']) {
            case 'image/jpeg':
                $img = imagecreatefromjpeg($mainImage);
                break;
            case 'image/png':
                $img = imagecreatefrompng($mainImage);
                break;
            case 'image/gif':
                $img = imagecreatefromgif($mainImage);
                break;
            default:
                throw new Exception('Format gambar tidak didukung. Gunakan JPG, PNG, atau GIF.');
        }
        
        // Create watermark (simple text as logo)
        $watermark = imagecreatetruecolor(150, 50);
        
        // Allocate colors
        $white = imagecolorallocate($watermark, 255, 255, 255);
        $black = imagecolorallocate($watermark, 0, 0, 0);
        $transparent = imagecolorallocatealpha($watermark, 200, 200, 200, 80);
        
        // Fill background with transparent color
        imagefill($watermark, 0, 0, $transparent);
        imagecolortransparent($watermark, $transparent);
        
        // Add text to watermark
        $text = "WATERMARK";
        $fontSize = 5; // built-in font size
        $textWidth = imagefontwidth($fontSize) * strlen($text);
        $textHeight = imagefontheight($fontSize);
        
        // Center text in watermark - fixed for PHP 8.1+
        $x = (int)((imagesx($watermark) - $textWidth) / 2);
        $y = (int)((imagesy($watermark) - $textHeight) / 2);
        
        // Add shadow
        imagestring($watermark, $fontSize, $x + 2, $y + 2, $text, $black);
        // Add main text
        imagestring($watermark, $fontSize, $x, $y, $text, $white);
        
        // Get dimensions
        $mainWidth = imagesx($img);
        $mainHeight = imagesy($img);
        $wmWidth = imagesx($watermark);
        $wmHeight = imagesy($watermark);
        
        // Position watermark at top right (with 10px padding) - fixed for PHP 8.1+
        $destX = (int)($mainWidth - $wmWidth - 10);
        $destY = 10;
        
        // Apply watermark
        imagecopymerge($img, $watermark, $destX, $destY, 0, 0, $wmWidth, $wmHeight, 50);
        
        // Save output image
        $outputPath = 'uploads/watermarked_' . time() . '.png';
        imagepng($img, $outputPath);
        
        // Clean up
        imagedestroy($img);
        imagedestroy($watermark);
        
        $outputImage = $outputPath;
        
    } catch (Exception $e) {
        $error = $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Simple Watermark</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            max-width: 800px;
            margin: 30px auto;
            padding: 20px;
            line-height: 1.6;
        }
        .container {
            border: 1px solid #ddd;
            padding: 30px;
            border-radius: 5px;
            background: #f9f9f9;
        }
        h2 {
            margin-top: 0;
            color: #333;
        }
        .form-group {
            margin-bottom: 20px;
        }
        label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
        }
        input[type="file"] {
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 3px;
            width: 100%;
            background: white;
        }
        button {
            background: #4CAF50;
            color: white;
            padding: 12px 25px;
            border: none;
            border-radius: 3px;
            cursor: pointer;
            font-size: 16px;
        }
        button:hover {
            background: #45a049;
        }
        .error {
            background: #ffebee;
            color: #c62828;
            padding: 10px;
            border-radius: 3px;
            margin: 20px 0;
            border: 1px solid #ffcdd2;
        }
        .result {
            margin-top: 30px;
            text-align: center;
        }
        .result img {
            max-width: 100%;
            border: 1px solid #ddd;
            border-radius: 5px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        }
        .info {
            background: #e3f2fd;
            padding: 15px;
            border-radius: 3px;
            margin: 20px 0;
            border: 1px solid #bbdefb;
        }
        .note {
            font-size: 14px;
            color: #666;
            margin-top: 20px;
        }
    </style>
</head>
<body>
    <div class="container">
        <h2>Watermark Generator</h2>
        
        <div class="info">
            <strong>Info:</strong> Upload gambar dan akan ditambahkan watermark "WATERMARK" di pojok kanan atas.
        </div>
        
        <form method="post" enctype="multipart/form-data">
            <div class="form-group">
                <label for="image">Pilih Gambar:</label>
                <input type="file" name="image" id="image" accept="image/*" required>
            </div>
            <button type="submit">Buat Watermark</button>
        </form>
        
        <?php if ($error): ?>
            <div class="error"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>
        
        <?php if ($outputImage): ?>
            <div class="result">
                <h3>Hasil:</h3>
                <img src="<?php echo htmlspecialchars($outputImage); ?>" alt="Watermarked Image">
                <p>
                    <a href="<?php echo htmlspecialchars($outputImage); ?>" download>Download Gambar</a>
                </p>
            </div>
        <?php endif; ?>
        
        <div class="note">
            <strong>Catatan:</strong> 
            <ul>
                <li>Mendukung format JPG, PNG, dan GIF</li>
                <li>Watermark berupa teks "WATERMARK" di pojok kanan atas</li>
                <li>Gambar hasil disimpan di folder /uploads</li>
            </ul>
        </div>
    </div>
</body>
</html>