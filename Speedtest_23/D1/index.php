<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>PHP Calendar</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            max-width: 600px;
            margin: 30px auto;
            padding: 20px;
        }
        .calendar {
            border: 1px solid #ddd;
            padding: 20px;
            border-radius: 5px;
        }
        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }
        .month-year {
            font-size: 24px;
            font-weight: bold;
            text-transform: uppercase;
        }
        .nav-btn {
            padding: 8px 15px;
            font-size: 18px;
            cursor: pointer;
            background: #f0f0f0;
            border: 1px solid #ddd;
            border-radius: 3px;
        }
        .nav-btn:hover {
            background: #e0e0e0;
        }
        table {
            width: 100%;
            border-collapse: collapse;
        }
        th {
            background: #f0f0f0;
            padding: 10px;
            font-weight: bold;
        }
        td {
            text-align: center;
            padding: 12px;
            border: 1px solid #ddd;
        }
        .today {
            background: #ffcccc;
            font-weight: bold;
        }
        .empty {
            background: #f9f9f9;
        }
    </style>
</head>
<body>
    <div class="calendar">
        <?php
        // Get current month and year from URL parameters
        $month = isset($_GET['month']) ? $_GET['month'] : date('n');
        $year = isset($_GET['year']) ? $_GET['year'] : date('Y');
        
        // Handle navigation
        if(isset($_GET['action'])) {
            if($_GET['action'] == 'prev') {
                $month--;
                if($month < 1) {
                    $month = 12;
                    $year--;
                }
            } elseif($_GET['action'] == 'next') {
                $month++;
                if($month > 12) {
                    $month = 1;
                    $year++;
                }
            }
        }
        
        // Get month name
        $monthNames = [
            1 => 'January', 2 => 'February', 3 => 'March', 4 => 'April',
            5 => 'May', 6 => 'June', 7 => 'July', 8 => 'August',
            9 => 'September', 10 => 'October', 11 => 'November', 12 => 'December'
        ];
        $monthName = $monthNames[$month];
        
        // Get first day of month (0 = Sunday, 1 = Monday, etc)
        $firstDay = date('w', strtotime("$year-$month-01"));
        
        // Get total days in month
        $totalDays = date('t', strtotime("$year-$month-01"));
        
        // Get today's date
        $today = date('j');
        $currentMonth = date('n');
        $currentYear = date('Y');
        ?>
        
        <div class="header">
            <a href="?action=prev&month=<?php echo $month; ?>&year=<?php echo $year; ?>" class="nav-btn">←</a>
            <div class="month-year"><?php echo $monthName . ' ' . $year; ?></div>
            <a href="?action=next&month=<?php echo $month; ?>&year=<?php echo $year; ?>" class="nav-btn">→</a>
        </div>
        
        <table>
            <tr>
                <th>SUN</th>
                <th>MON</th>
                <th>TUE</th>
                <th>WED</th>
                <th>THU</th>
                <th>FRI</th>
                <th>SAT</th>
            </tr>
            <tr>
                <?php
                // Fill empty cells before first day
                for($i = 0; $i < $firstDay; $i++) {
                    echo '<td class="empty"></td>';
                }
                
                // Fill the days
                for($day = 1; $day <= $totalDays; $day++) {
                    // Check if this is today
                    $todayClass = ($day == $today && $month == $currentMonth && $year == $currentYear) ? 'today' : '';
                    
                    echo "<td class='$todayClass'>$day</td>";
                    
                    // Start new row after Saturday (6)
                    if(($firstDay + $day) % 7 == 0) {
                        echo '</tr><tr>';
                    }
                }
                
                // Fill remaining empty cells
                $remainingCells = 7 - (($firstDay + $totalDays) % 7);
                if($remainingCells < 7) {
                    for($i = 0; $i < $remainingCells; $i++) {
                        echo '<td class="empty"></td>';
                    }
                }
                ?>
            </tr>
        </table>
    </div>
    
    <div style="margin-top: 20px; text-align: center; color: #666; font-size: 14px;">
        * Tanggal <?php echo $today; ?> (hari ini) ditandai dengan warna merah
    </div>
</body>
</html>