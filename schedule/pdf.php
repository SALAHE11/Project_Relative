<?php
// Database connection
$conn = new mysqli("localhost", "root", "", "database1");

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Function to get schedules for a specific branch, semester, and year
function getSchedules($branchId, $semester, $year) {
    global $conn;
    $sql = "SELECT s.*, m.name AS module_name, GROUP_CONCAT(t.name SEPARATOR ' / ') AS teachers, p.debut_time, p.fin_time, p.name_periode, ts.name AS type_seance_name, sl.name AS salle_name
            FROM schedule s
            JOIN module m ON s.module_id = m.id
            JOIN module_teacher mt ON m.id = mt.module_id
            JOIN teacher t ON mt.teacher_id = t.id
            JOIN periode p ON s.periode_id = p.id
            JOIN type_seance ts ON s.type_seance_id = ts.id
            JOIN salle sl ON s.salle_id = sl.id
            WHERE s.branch_id = ? AND s.semester = ? AND s.year = ?
            GROUP BY s.id, s.periode_id
            ORDER BY p.debut_time";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("iii", $branchId, $semester, $year);
    $stmt->execute();
    $result = $stmt->get_result();
    return $result->fetch_all(MYSQLI_ASSOC);
}

// Get all branches
$branchQuery = "SELECT id, name FROM branch";
$branches = $conn->query($branchQuery)->fetch_all(MYSQLI_ASSOC);

// Get all years
$yearQuery = "SELECT DISTINCT year FROM schedule ORDER BY year DESC";
$years = $conn->query($yearQuery)->fetch_all(MYSQLI_ASSOC);

// Default values
$selectedBranch = isset($_GET['branch']) ? $_GET['branch'] : $branches[0]['id'];
$selectedSemester = isset($_GET['semester']) ? $_GET['semester'] : 1;
$selectedYear = isset($_GET['year']) ? $_GET['year'] : (isset($years[0]['year']) ? $years[0]['year'] : date('Y'));

// Get schedules
$schedules = getSchedules($selectedBranch, $selectedSemester, $selectedYear);

// Organize schedules by day and time
$organizedSchedules = [];
$days = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'];
$timeSlots = ['8h30-10h30', '10h40-12h40', '13h30-15h30', '15h40-17h40'];

foreach ($schedules as $schedule) {
    foreach ($days as $day) {
        if ($schedule[$day] == 1) {
            $timeSlot = ($schedule['debut_time'] < '13:00:00') ? ($schedule['debut_time'] < '10:40:00' ? '8h30-10h30' : '10h40-12h40') : ($schedule['debut_time'] < '15:40:00' ? '13h30-15h30' : '15h40-17h40');
            $organizedSchedules[$day][$timeSlot] = $schedule;
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PDFs</title>
    <style>
        body {
            font-family: Arial, sans-serif;
        }
        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
        }
        .header {
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 20px;
            border-bottom: 2px solid #000;
            padding-bottom: 10px;
            text-align: center;
        }
        .logo {
            width: 110px;
            height: 110px;
            margin-right: 20px;
        }
        .university-info h2, .university-info h3 {
            margin: 5px 0;
        }
        .schedule-info {
            font-size: 18px;
            font-weight: bold;
            text-align: center;
            margin-bottom: 10px;
        }
        .form-container {
            text-align: center;
            margin-bottom: 20px;
        }
        select, input[type="submit"] {
            padding: 10px;
            font-size: 16px;
            margin: 0 10px;
            border-radius: 5px;
            border: 1px solid #ccc;
        }
        input[type="submit"] {
            background-color: #4CAF50;
            color: white;
            border: none;
            cursor: pointer;
        }
        input[type="submit"]:hover {
            background-color: #45a049;
        }
        table {
            border-collapse: collapse;
            width: 100%;
            table-layout: fixed;
        }
        th, td {
            border: 1px solid black;
            padding: 8px;
            text-align: center;
            vertical-align: middle;
            height: 50px;
        }
        th {
            background-color: #f2f2f2;
        }
        th:first-child {
            width: 10%;
        }
        tr:nth-child(odd) {
            background-color: lightgray;
        }
        tr:nth-child(even) {
            background-color: #e0e0e0;
        }
        tr:nth-child(2) {
            background-color: lightcyan;
        }
        tr:nth-child(3) {
            background-color: lightgoldenrodyellow;
        }
        tr:nth-child(4) {
            background-color: lightblue;
        }
        tr:nth-child(5) {
            background-color: lightsalmon;
        }
        tr:nth-child(6) {
            background-color: linen;
        }
        .download-btn {
            display: block;
            width: 200px;
            margin: 20px auto;
            padding: 10px;
            background-color: #4CAF50;
            color: white;
            text-align: center;
            text-decoration: none;
            border-radius: 5px;
        }
        @media print {
            @page {
                margin: 20px;
            }
            body * {
                visibility: hidden;
            }
            .container, .container * {
                visibility: visible;
            }
            .container {
                position: absolute;
                left: 0;
                top: 0;
                width: 100%;
                max-width: none;
                padding: 0;
            }
            .form-container, .download-btn {
                display: none;
            }
            table {
                width: 100% !important;
                page-break-inside: avoid;
            }
            
            th, td {
                font-size: 12px;
                padding: 4px;
                vertical-align: middle;
            }
            
            tr:nth-child(odd) {
                background-color: lightgray !important;
                -webkit-print-color-adjust: exact;
                color-adjust: exact;
            }
            tr:nth-child(even) {
                background-color: #e0e0e0 !important;
                -webkit-print-color-adjust: exact;
                color-adjust: exact;
            }
            tr:nth-child(2) {
                background-color: lightcyan !important;
                -webkit-print-color-adjust: exact;
                color-adjust: exact;
            }
            tr:nth-child(3) {
                background-color: lightgoldenrodyellow !important;
                -webkit-print-color-adjust: exact;
                color-adjust: exact;
            }
            tr:nth-child(4) {
                background-color: lightblue !important;
                -webkit-print-color-adjust: exact;
                color-adjust: exact;
            }
            tr:nth-child(5) {
                background-color: lightsalmon !important;
                -webkit-print-color-adjust: exact;
                color-adjust: exact;
            }
            tr:nth-child(6) {
                background-color: linen !important;
                -webkit-print-color-adjust: exact;
                color-adjust: exact;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <img src="logoest.png" alt="University Logo" class="logo">
            <div class="university-info">
                <h2>Université Sultan Moulay Slimane</h2>
                <h3>Ecole Supérieure de Technologie</h3>
                <h3>Fkih Ben Salah</h3>
            </div>
        </div>

        <div class="schedule-info">
            Emploi du temps (Year <?php echo $selectedYear; ?>, Semester <?php echo $selectedSemester; ?>) : 
            <?php 
            $selectedBranchName = '';
            foreach ($branches as $branch) {
                if ($branch['id'] == $selectedBranch) {
                    $selectedBranchName = $branch['name'];
                    break;
                }
            }
            echo $selectedBranchName;
            ?>
        </div>
        
        <div class="form-container">
            <form method="get">
                <label for="branch">Branch:</label>
                <select name="branch" id="branch">
                    <?php foreach ($branches as $branch): ?>
                        <option value="<?php echo $branch['id']; ?>" <?php echo ($branch['id'] == $selectedBranch) ? 'selected' : ''; ?>>
                            <?php echo $branch['name']; ?>
                        </option>
                    <?php endforeach; ?>
                </select>

                <label for="year">Year:</label>
                <select name="year" id="year">
                    <?php foreach ($years as $year): ?>
                        <option value="<?php echo $year['year']; ?>" <?php echo ($selectedYear == $year['year']) ? 'selected' : ''; ?>>
                            <?php echo $year['year']; ?>
                        </option>
                    <?php endforeach; ?>
                </select>

                <label for="semester">Semester:</label>
                <select name="semester" id="semester">
                    <?php for ($i = 1; $i <= 6; $i++): ?>
                        <option value="<?php echo $i; ?>" <?php echo ($selectedSemester == $i) ? 'selected' : ''; ?>>
                            Semester <?php echo $i; ?>
                        </option>
                    <?php endfor; ?>
                </select>

                <input type="submit" value="View Schedule">
            </form>
        </div>

        <table>
            <tr>
                <th></th>
                <?php foreach ($timeSlots as $slot): ?>
                    <th><?php echo $slot; ?></th>
                <?php endforeach; ?>
            </tr>
            <?php foreach ($days as $day): ?>
                <tr>
                    <th><?php echo $day; ?></th>
                    <?php foreach ($timeSlots as $slot): ?>
                        <td>
                            <?php if (isset($organizedSchedules[$day][$slot])): ?>
                                <?php $schedule = $organizedSchedules[$day][$slot]; ?>
                                <strong><?php echo $schedule['module_name']; ?></strong><br>
                                <?php echo $schedule['teachers']; ?><br>
                                <?php echo $schedule['salle_name'] . " - " . $schedule['type_seance_name']; ?>
                            <?php endif; ?>
                        </td>
                    <?php endforeach; ?>
                </tr>
            <?php endforeach; ?>
        </table>

        <a href="#" class="download-btn" onclick="window.print(); return false;">Download</a>
    </div>

    <script>
        // This script ensures that ctrl+p also triggers our custom print styling
        document.addEventListener('keydown', function(e) {
            if (e.ctrlKey && e.key === 'p') {
                e.preventDefault();
                window.print();
            }
        });
    </script>
</body>
</html>