<?php
// E1. XML2JSON Converter - Simple XML to JSON Converter

$jsonResult = '';
$error = '';
$xmlInput = '';

// Default XML example
$defaultXml = '<?xml version="1.0" encoding="UTF-8"?>
<note>
    <to>Tove</to>
    <from>Jani</from>
    <heading>Reminder</heading>
    <body>Don\'t forget me this weekend!</body>
    <nole></nole>
</note>';

// Process form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $xmlInput = $_POST['xml_input'] ?? '';
    
    if (empty($xmlInput)) {
        $error = 'Please enter XML content';
    } else {
        try {
            // Suppress warnings and handle errors properly
            libxml_use_internal_errors(true);
            
            // Load XML string
            $xml = simplexml_load_string($xmlInput);
            
            if ($xml === false) {
                $errors = libxml_get_errors();
                $error = 'Invalid XML: ' . $errors[0]->message;
                libxml_clear_errors();
            } else {
                // Convert SimpleXMLElement to array
                $array = xmlToArray($xml);
                
                // Convert to JSON with pretty print
                $jsonResult = json_encode($array, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
                
                if ($jsonResult === false) {
                    $error = 'Error converting to JSON';
                }
            }
        } catch (Exception $e) {
            $error = 'Error: ' . $e->getMessage();
        }
    }
} else {
    // Set default XML for initial display
    $xmlInput = $defaultXml;
}

// Function to convert XML to array
function xmlToArray($xml) {
    $array = [];
    
    foreach ($xml->children() as $key => $child) {
        // If element has children, recursively convert
        if ($child->count() > 0) {
            $array[$key] = xmlToArray($child);
        } else {
            // Get value, convert to string if empty
            $value = trim((string)$child);
            $array[$key] = $value !== '' ? $value : '';
        }
    }
    
    return $array;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>XML2JSON Converter</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            max-width: 1000px;
            margin: 30px auto;
            padding: 20px;
            background: #f5f5f5;
        }
        .container {
            background: white;
            padding: 25px;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        h2 {
            margin-top: 0;
            color: #333;
            border-bottom: 2px solid #4CAF50;
            padding-bottom: 10px;
        }
        .description {
            background: #e8f5e8;
            padding: 15px;
            border-radius: 5px;
            margin: 20px 0;
            border-left: 4px solid #4CAF50;
        }
        .input-section, .output-section {
            margin: 20px 0;
        }
        label {
            display: block;
            margin-bottom: 8px;
            font-weight: bold;
            color: #555;
        }
        textarea {
            width: 100%;
            padding: 12px;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-family: monospace;
            font-size: 14px;
            resize: vertical;
            box-sizing: border-box;
        }
        textarea:focus {
            outline: none;
            border-color: #4CAF50;
            box-shadow: 0 0 5px rgba(76,175,80,0.3);
        }
        button {
            background: #4CAF50;
            color: white;
            padding: 12px 30px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 16px;
            font-weight: bold;
            margin: 10px 0;
        }
        button:hover {
            background: #45a049;
        }
        .error {
            background: #ffebee;
            color: #c62828;
            padding: 12px;
            border-radius: 5px;
            margin: 15px 0;
            border-left: 4px solid #f44336;
        }
        .result {
            background: #f8f9fa;
            padding: 15px;
            border-radius: 5px;
            border: 1px solid #ddd;
        }
        pre {
            margin: 0;
            font-family: monospace;
            font-size: 14px;
            white-space: pre-wrap;
            word-wrap: break-word;
            color: #333;
        }
        .example-note {
            background: #e3f2fd;
            padding: 10px;
            border-radius: 5px;
            font-size: 14px;
            margin-top: 10px;
        }
        .footer {
            margin-top: 20px;
            text-align: center;
            color: #666;
            font-size: 13px;
        }
    </style>
</head>
<body>
    <div class="container">
        <h2>XML to JSON Converter</h2>
        
        <div class="description">
            <strong>Contoh XML:</strong> Masukkan XML Anda dan klik "Convert!" untuk mengubah ke JSON.
        </div>
        
        <form method="post">
            <div class="input-section">
                <label for="xml_input">XML Input:</label>
                <textarea name="xml_input" id="xml_input" rows="10" placeholder="Masukkan XML di sini..."><?php echo htmlspecialchars($xmlInput); ?></textarea>
            </div>
            
            <button type="submit">Convert!</button>
        </form>
        
        <?php if ($error): ?>
            <div class="error">
                <strong>Error:</strong> <?php echo htmlspecialchars($error); ?>
            </div>
        <?php endif; ?>
        
        <?php if ($jsonResult): ?>
            <div class="output-section">
                <label>JSON Result:</label>
                <div class="result">
                    <pre><?php echo htmlspecialchars($jsonResult); ?></pre>
                </div>
            </div>
        <?php endif; ?>
        
        <div class="footer">
            XML2JSON Converter - Simple PHP Script
        </div>
    </div>
</body>
</html>