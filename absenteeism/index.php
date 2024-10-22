<?php
// Database connection
$servername = "localhost";
$username = "root"; // replace with your database username
$password = ""; // replace with your database password
$dbname = "dbabsentism";

$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Query to get absenteeism data
// Query to get absenteeism data with gender
$sql = "SELECT r.date, 
               SUM(CASE WHEN r.status = 0 THEN 1 ELSE 0 END) AS absences,
               SUM(CASE WHEN r.status = 0 AND s.gender = 1 THEN 1 ELSE 0 END) AS male_absences,
               SUM(CASE WHEN r.status = 0 AND s.gender = 2 THEN 1 ELSE 0 END) AS female_absences
        FROM records r
        JOIN student s ON r.student_id = s.id
        GROUP BY r.date
        ORDER BY r.date";
$result = $conn->query($sql);

$dates = [];
$absences = [];
$totalAbsences = 0;
$totalMaleAbsences = 0;
$totalFemaleAbsences = 0;

if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $dates[] = $row['date'];
        $absences[] = (int)$row['absences'];
        $totalAbsences += (int)$row['absences'];
        $totalMaleAbsences += (int)$row['male_absences'];
        $totalFemaleAbsences += (int)$row['female_absences'];
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Absenteeism Dashboard</title>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
            margin: 0;
            padding: 20px;
        }
        .container {
            max-width: 1200px;
            margin: 0 auto;
            background: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }
        h1 {
            text-align: center;
            color: #333;
        }
        .chart-container {
            position: relative;
            height: 60vh; /* Enlarge the graph height */
            width: 100%; /* Full width */
        }
        canvas {
            max-width: 100%;
            height: 100%;
        }
        .summary {
            margin-top: 20px;
            font-size: 18px;
            text-align: center;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Absenteeism Frequency Over Time</h1>
        <div class="chart-container">
            <canvas id="absenteeismChart"></canvas>
        </div>
        
        <div class="summary">
            <strong>Total Absences:</strong> <?php echo $totalAbsences; ?><br>
            <strong>Male Absences:</strong> <?php echo $totalMaleAbsences; ?><br>
            <strong>Female Absences:</strong> <?php echo $totalFemaleAbsences; ?>
        </div>
    </div>
    
    <script>
        const ctx = document.getElementById('absenteeismChart').getContext('2d');
        const absenteeismChart = new Chart(ctx, {
            type: 'line',
            data: {
                labels: <?php echo json_encode($dates); ?>,
                datasets: [{
                    label: 'Total Absences',
                    data: <?php echo json_encode($absences); ?>,
                    borderColor: 'rgba(255, 99, 132, 1)',
                    backgroundColor: 'rgba(255, 99, 132, 0.2)',
                    borderWidth: 2,
                    fill: true
                }]
            },
            options: {
                responsive: true,
                scales: {
                    y: {
                        beginAtZero: true,
                        title: {
                            display: true,
                            text: 'Number of Absences'
                        }
                    },
                    x: {
                        title: {
                            display: true,
                            text: 'Date'
                        }
                    }
                }
            }
        });
    </script>
</body>
</html>
