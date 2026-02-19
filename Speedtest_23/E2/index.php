<?php
// E2. Chat Analytics - Simple Message Analytics

// Sample JSON data (simulasi file messages.json)
$jsonData = '[
    {
        "from": "user1",
        "to": "user2",
        "message": "Hello, how are you today?",
        "timestamp": "2024-01-01 10:00:00"
    },
    {
        "from": "user2",
        "to": "user1",
        "message": "I am fine, thank you! And you?",
        "timestamp": "2024-01-01 10:01:00"
    },
    {
        "from": "user1",
        "to": "user2",
        "message": "I am good too. What are you doing?",
        "timestamp": "2024-01-01 10:02:00"
    },
    {
        "from": "user2",
        "to": "user1",
        "message": "Just working on a project. It is interesting.",
        "timestamp": "2024-01-01 10:03:00"
    },
    {
        "from": "user1",
        "to": "user2",
        "message": "Sounds cool! What kind of project?",
        "timestamp": "2024-01-01 10:04:00"
    },
    {
        "from": "user2",
        "to": "user1",
        "message": "AI and machine learning stuff. Very complex but fun.",
        "timestamp": "2024-01-01 10:05:00"
    },
    {
        "from": "user1",
        "to": "user2",
        "message": "Wow, that is amazing! Good luck with it.",
        "timestamp": "2024-01-01 10:06:00"
    },
    {
        "from": "user2",
        "to": "user1",
        "message": "Thanks! Maybe we can collaborate sometime.",
        "timestamp": "2024-01-01 10:07:00"
    }
]';

// Define current user (untuk menentukan sent/received)
$currentUser = 'user1';

// Process analytics
$messages = json_decode($jsonData, true);
$analytics = analyzeMessages($messages, $currentUser);

function analyzeMessages($messages, $currentUser) {
    $result = [
        'sent' => [
            'count' => 0,
            'total_chars' => 0,
            'words' => []
        ],
        'received' => [
            'count' => 0,
            'total_chars' => 0,
            'words' => []
        ]
    ];
    
    foreach ($messages as $msg) {
        $from = $msg['from'];
        $message = $msg['message'];
        $charCount = strlen($message);
        
        // Determine if sent or received
        if ($from === $currentUser) {
            $result['sent']['count']++;
            $result['sent']['total_chars'] += $charCount;
            
            // Count words for sent messages
            $words = str_word_count(strtolower($message), 1);
            foreach ($words as $word) {
                $word = trim($word);
                if (strlen($word) > 1 || in_array($word, ['a', 'i'])) { // Include single letters like 'a', 'i'
                    $result['sent']['words'][$word] = ($result['sent']['words'][$word] ?? 0) + 1;
                }
            }
        } else {
            $result['received']['count']++;
            $result['received']['total_chars'] += $charCount;
            
            // Count words for received messages
            $words = str_word_count(strtolower($message), 1);
            foreach ($words as $word) {
                $word = trim($word);
                if (strlen($word) > 1 || in_array($word, ['a', 'i'])) {
                    $result['received']['words'][$word] = ($result['received']['words'][$word] ?? 0) + 1;
                }
            }
        }
    }
    
    return $result;
}

// Get top 5 words
function getTopWords($wordCounts, $limit = 5) {
    arsort($wordCounts);
    return array_slice($wordCounts, 0, $limit, true);
}

$topSentWords = getTopWords($analytics['sent']['words']);
$topReceivedWords = getTopWords($analytics['received']['words']);

// Calculate averages
$avgSentChars = $analytics['sent']['count'] > 0 
    ? round($analytics['sent']['total_chars'] / $analytics['sent']['count']) 
    : 0;
    
$avgReceivedChars = $analytics['received']['count'] > 0 
    ? round($analytics['received']['total_chars'] / $analytics['received']['count']) 
    : 0;
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Chat Analytics</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            max-width: 800px;
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
        .info {
            background: #e8f5e8;
            padding: 15px;
            border-radius: 5px;
            margin: 20px 0;
            border-left: 4px solid #4CAF50;
        }
        .stats-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
            margin: 20px 0;
        }
        .stat-box {
            background: #f8f9fa;
            padding: 15px;
            border-radius: 5px;
            border: 1px solid #ddd;
        }
        .stat-box h3 {
            margin-top: 0;
            color: #4CAF50;
            border-bottom: 1px solid #ddd;
            padding-bottom: 8px;
        }
        .stat-item {
            margin: 10px 0;
            display: flex;
            justify-content: space-between;
        }
        .stat-label {
            font-weight: bold;
            color: #555;
        }
        .stat-value {
            color: #333;
        }
        .word-list {
            list-style: none;
            padding: 0;
            margin: 10px 0;
        }
        .word-list li {
            padding: 5px 0;
            border-bottom: 1px dashed #ddd;
            display: flex;
            justify-content: space-between;
        }
        .word-count {
            background: #4CAF50;
            color: white;
            padding: 2px 8px;
            border-radius: 12px;
            font-size: 12px;
        }
        .summary {
            background: #e3f2fd;
            padding: 15px;
            border-radius: 5px;
            margin-top: 20px;
            text-align: center;
            font-size: 14px;
        }
    </style>
</head>
<body>
    <div class="container">
        <h2>📊 Chat Analytics</h2>
        
        <div class="info">
            <strong>Current User:</strong> <?php echo $currentUser; ?> (user1 = pengirim, user2 = penerima)
        </div>
        
        <div class="stats-grid">
            <!-- Sent Messages Stats -->
            <div class="stat-box">
                <h3>📤 Sent Messages</h3>
                <div class="stat-item">
                    <span class="stat-label">Total messages sent:</span>
                    <span class="stat-value"><?php echo $analytics['sent']['count']; ?></span>
                </div>
                <div class="stat-item">
                    <span class="stat-label">Avg character length sent:</span>
                    <span class="stat-value"><?php echo $avgSentChars; ?></span>
                </div>
                
                <h4 style="margin: 15px 0 5px;">Top 5 sent words:</h4>
                <ul class="word-list">
                    <?php if (empty($topSentWords)): ?>
                        <li>No words found</li>
                    <?php else: ?>
                        <?php foreach ($topSentWords as $word => $count): ?>
                            <li>
                                <span><?php echo $word; ?></span>
                                <span class="word-count"><?php echo $count; ?>x</span>
                            </li>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </ul>
            </div>
            
            <!-- Received Messages Stats -->
            <div class="stat-box">
                <h3>📥 Received Messages</h3>
                <div class="stat-item">
                    <span class="stat-label">Total messages received:</span>
                    <span class="stat-value"><?php echo $analytics['received']['count']; ?></span>
                </div>
                <div class="stat-item">
                    <span class="stat-label">Avg character length received:</span>
                    <span class="stat-value"><?php echo $avgReceivedChars; ?></span>
                </div>
                
                <h4 style="margin: 15px 0 5px;">Top 5 received words:</h4>
                <ul class="word-list">
                    <?php if (empty($topReceivedWords)): ?>
                        <li>No words found</li>
                    <?php else: ?>
                        <?php foreach ($topReceivedWords as $word => $count): ?>
                            <li>
                                <span><?php echo $word; ?></span>
                                <span class="word-count"><?php echo $count; ?>x</span>
                            </li>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
        
        <div class="summary">
            <strong>Summary:</strong> Total <?php echo $analytics['sent']['count'] + $analytics['received']['count']; ?> messages 
            (<?php echo $analytics['sent']['count']; ?> sent, <?php echo $analytics['received']['count']; ?> received)
        </div>
        
        <!-- Raw JSON Data (for reference) -->
        <details style="margin-top: 20px;">
            <summary style="cursor: pointer; color: #666;">📁 View JSON Data</summary>
            <pre style="background: #f5f5f5; padding: 10px; border-radius: 5px; overflow-x: auto; font-size: 12px;"><?php echo json_encode(json_decode($jsonData), JSON_PRETTY_PRINT); ?></pre>
        </details>
    </div>
</body>
</html>