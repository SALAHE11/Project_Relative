<?php
// Database connection
$conn = new mysqli("localhost", "root", "", "database1");

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Function to get available modules for a branch
function getModules($branchId) {
    global $conn;
    $sql = "SELECT id, name FROM module WHERE branch_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $branchId);
    $stmt->execute();
    $result = $stmt->get_result();
    return $result->fetch_all(MYSQLI_ASSOC);
}

// Function to check teacher availability
function areTeachersAvailable($moduleId, $day, $periodeId, $semester, $year, $excludeScheduleId = null) {
    global $conn;
    
    // Get all teachers for the module
    $teacherQuery = "SELECT teacher_id FROM module_teacher WHERE module_id = ?";
    $stmt = $conn->prepare($teacherQuery);
    $stmt->bind_param("i", $moduleId);
    $stmt->execute();
    $teacherResult = $stmt->get_result();
    $teacherIds = $teacherResult->fetch_all(MYSQLI_ASSOC);

    // Determine interacting semesters
    $interactingSemesters = $semester % 2 == 1 ? [1, 3, 5] : [2, 4, 6];

    // Check availability for each teacher
    foreach ($teacherIds as $teacherData) {
        $teacherId = $teacherData['teacher_id'];
        
        $sql = "SELECT s.id 
                FROM schedule s
                JOIN module_teacher mt ON s.module_id = mt.module_id
                JOIN periode p1 ON s.periode_id = p1.id
                JOIN periode p2 ON p2.id = ?
                WHERE mt.teacher_id = ? 
                AND s.$day = 1 
                AND s.semester IN (?, ?, ?)
                AND s.year = ?
                AND (
                    (p1.debut_time < p2.fin_time AND p1.fin_time > p2.debut_time)
                    OR (p2.debut_time < p1.fin_time AND p2.fin_time > p1.debut_time)
                )";
        
        if ($excludeScheduleId) {
            $sql .= " AND s.id != ?";
        }
        
        $stmt = $conn->prepare($sql);
        
        if ($excludeScheduleId) {
            $stmt->bind_param("iiiiiii", $periodeId, $teacherId, $interactingSemesters[0], $interactingSemesters[1], $interactingSemesters[2], $year, $excludeScheduleId);
        } else {
            $stmt->bind_param("iiiiii", $periodeId, $teacherId, $interactingSemesters[0], $interactingSemesters[1], $interactingSemesters[2], $year);
        }
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            return false; // At least one teacher is not available
        }
    }
    
    return true; // All teachers are available
}

// Function to get available periods
function getPeriods($branchId, $day, $salleId, $semester, $year, $moduleId, $excludeScheduleId = null) {
    global $conn;
    
    // Get max_classes for the branch
    $maxClassesQuery = "SELECT max_classes FROM branch WHERE id = ?";
    $stmt = $conn->prepare($maxClassesQuery);
    $stmt->bind_param("i", $branchId);
    $stmt->execute();
    $maxClassesResult = $stmt->get_result();
    $maxClasses = $maxClassesResult->fetch_assoc()['max_classes'];

    // Determine interacting semesters
    $interactingSemesters = $semester % 2 == 1 ? [1, 3, 5] : [2, 4, 6];

    $sql = "SELECT p.id, p.name_periode 
            FROM periode p
            WHERE p.periode_category = ? 
            AND p.id NOT IN (
                SELECT s.periode_id 
                FROM schedule s 
                WHERE (s.branch_id = ? OR s.salle_id = ?)
                AND s.$day = 1 
                AND s.semester IN (?, ?, ?)
                AND s.year = ?";
    
    if ($excludeScheduleId) {
        $sql .= " AND s.id != ?";
    }
    
    $sql .= ")";
    
    $stmt = $conn->prepare($sql);
    if ($excludeScheduleId) {
        $stmt->bind_param("iiiiiiii", $maxClasses, $branchId, $salleId, $interactingSemesters[0], $interactingSemesters[1], $interactingSemesters[2], $year, $excludeScheduleId);
    } else {
        $stmt->bind_param("iiiiiii", $maxClasses, $branchId, $salleId, $interactingSemesters[0], $interactingSemesters[1], $interactingSemesters[2], $year);
    }
    $stmt->execute();
    $result = $stmt->get_result();
    $periods = $result->fetch_all(MYSQLI_ASSOC);

    // Filter periods based on teacher availability
    $availablePeriods = array_filter($periods, function($period) use ($moduleId, $day, $semester, $year, $excludeScheduleId) {
        return areTeachersAvailable($moduleId, $day, $period['id'], $semester, $year, $excludeScheduleId);
    });

    return array_values($availablePeriods);
}

// Function to get all schedules
function getAllSchedules() {
    global $conn;
    $sql = "SELECT s.id, b.name AS branch_name, m.name AS module_name, 
                   CASE
                       WHEN s.Monday = 1 THEN 'Monday'
                       WHEN s.Tuesday = 1 THEN 'Tuesday'
                       WHEN s.Wednesday = 1 THEN 'Wednesday'
                       WHEN s.Thursday = 1 THEN 'Thursday'
                       WHEN s.Friday = 1 THEN 'Friday'
                       WHEN s.Saturday = 1 THEN 'Saturday'
                   END AS day,
                   sa.name AS salle_name, p.name_periode, ts.name AS type_seance_name, s.semester, s.year
            FROM schedule s
            JOIN branch b ON s.branch_id = b.id
            JOIN module m ON s.module_id = m.id
            JOIN salle sa ON s.salle_id = sa.id
            JOIN periode p ON s.periode_id = p.id
            JOIN type_seance ts ON s.type_seance_id = ts.id
            ORDER BY s.id DESC";
    $result = $conn->query($sql);
    return $result->fetch_all(MYSQLI_ASSOC);
}

// Handle AJAX requests
if (isset($_GET['action'])) {
    if ($_GET['action'] == 'getModules' && isset($_GET['branch_id'])) {
        $modules = getModules($_GET['branch_id']);
        echo json_encode($modules);
        exit;
    } elseif ($_GET['action'] == 'getPeriods' && isset($_GET['branch_id']) && isset($_GET['day']) && isset($_GET['salle_id']) && isset($_GET['semester']) && isset($_GET['year']) && isset($_GET['module_id'])) {
        $excludeScheduleId = isset($_GET['exclude_schedule_id']) ? $_GET['exclude_schedule_id'] : null;
        $periods = getPeriods($_GET['branch_id'], $_GET['day'], $_GET['salle_id'], $_GET['semester'], $_GET['year'], $_GET['module_id'], $excludeScheduleId);
        echo json_encode($periods);
        exit;
    }
}

$message = '';

// Handle form submission for creating and updating schedules
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['create_schedule']) || isset($_POST['update_schedule'])) {
        $semester = $_POST['semester'];
        $year = $_POST['year'];
        $branchId = $_POST['branch'];
        $moduleId = $_POST['module'];
        $day = $_POST['day'];
        $salleId = $_POST['salle'];
        $periodeId = $_POST['periode'];
        $typeSeanceId = $_POST['type_seance'];

        if (isset($_POST['create_schedule'])) {
            // Check if the schedule already exists
            $checkSql = "SELECT id FROM schedule WHERE branch_id = ? AND semester = ? AND year = ? AND $day = 1 AND periode_id = ?";
            $checkStmt = $conn->prepare($checkSql);
            $checkStmt->bind_param("iiis", $branchId, $semester, $year, $periodeId);
            $checkStmt->execute();
            $checkResult = $checkStmt->get_result();

            if ($checkResult->num_rows > 0) {
                $message = "Error: A schedule for this branch, semester, year, day, and period already exists.";
            } elseif (areTeachersAvailable($moduleId, $day, $periodeId, $semester, $year)) {
                $sql = "INSERT INTO schedule (branch_id, salle_id, periode_id, type_seance_id, module_id, semester, year, $day) 
                        VALUES (?, ?, ?, ?, ?, ?, ?, 1)";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("iiiiiii", $branchId, $salleId, $periodeId, $typeSeanceId, $moduleId, $semester, $year);
                
                if ($stmt->execute()) {
                    $message = "Schedule created successfully";
                } else {
                    $message = "Error creating schedule: " . $conn->error;
                }
            } else {
                $message = "Error: One or more teachers are not available for this period.";
            }
        } elseif (isset($_POST['update_schedule'])) {
            $scheduleId = $_POST['schedule_id'];
            if (areTeachersAvailable($moduleId, $day, $periodeId, $semester, $year, $scheduleId)) {
                $sql = "UPDATE schedule SET branch_id = ?, salle_id = ?, periode_id = ?, type_seance_id = ?, module_id = ?, semester = ?, year = ?, 
                        Monday = 0, Tuesday = 0, Wednesday = 0, Thursday = 0, Friday = 0, Saturday = 0, $day = 1 
                        WHERE id = ?";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("iiiiiiii", $branchId, $salleId, $periodeId, $typeSeanceId, $moduleId, $semester, $year, $scheduleId);
                
                if ($stmt->execute()) {
                    $message = "Schedule updated successfully";
                } else {
                    $message = "Error updating schedule: " . $conn->error;
                }
            } else {
                $message = "Error: One or more teachers are not available for this period.";
            }
        }
    } elseif (isset($_POST['delete_schedule'])) {
        $scheduleId = $_POST['schedule_id'];
        $sql = "DELETE FROM schedule WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $scheduleId);
        
        if ($stmt->execute()) {
            $message = "Schedule deleted successfully";
        } else {
            $message = "Error deleting schedule: " . $conn->error;
        }
    }

    // Redirect to prevent form resubmission
    header("Location: " . $_SERVER['PHP_SELF'] . "?message=" . urlencode($message));
    exit();
}

// Display message if set
if (isset($_GET['message'])) {
    $message = $_GET['message'];
}

// Fetch branches
$branchQuery = "SELECT id, name FROM branch";
$branches = $conn->query($branchQuery)->fetch_all(MYSQLI_ASSOC);

// Fetch salles
$salleQuery = "SELECT id, name FROM salle";
$salles = $conn->query($salleQuery)->fetch_all(MYSQLI_ASSOC);

// Fetch type_seance
$typeSeanceQuery = "SELECT id, name FROM type_seance";
$typeSeances = $conn->query($typeSeanceQuery)->fetch_all(MYSQLI_ASSOC);

// Fetch all schedules
$schedules = getAllSchedules();

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Schedule Management</title>
    <style>
    body {
        font-family: Arial, sans-serif;
        line-height: 1.6;
        margin: 0;
        padding: 20px;
        background-color: #dfe0e0;
    }
    h1 {
        text-align: center;
        color: black;
    }
    h2 {
        color: black;
    }
    form, .schedule-table {
        background-color: #fff;
        padding: 20px;
        border-radius: 5px;
        box-shadow: 0 0 10px rgba(0,0,0,0.1);
        margin-bottom: 20px;
    }
    label {
        display: inline-block;
        margin-bottom: 5px;
    }
    select, input[type="number"] {
        width: 100%;
        padding: 8px;
        margin-bottom: 10px;
        border: 1px solid #ddd;
        border-radius: 4px;
    }
    input[type="submit"], .edit-btn, .delete-btn {
        padding: 10px 15px;
        border: none;
        border-radius: 4px;
        cursor: pointer;
        transition: background-color 0.3s, color 0.3s;
    }
    .delete-btn{
        background-color: red;
        color: white;
     }
    input[type="submit"]:hover {
        background-color: whitesmoke;
        color: black;
        border: 1px solid black;
    }
    .message {
        text-transform: uppercase;
        margin-top: 20px;
        padding: 10px;
        background-color: #07da63;
        border: 1px solid black;
        border-radius: 4px;
        text-align: center;
        color: white;
    }
    table {
        width: 100%;
        border-collapse: collapse;
    }
    th, td {
        padding: 10px;
        border: 1px solid #ddd;
        text-align: left;
    }
    th {
        background-color: #f2f2f2;
    }
    .edit-btn, .delete-btn {
        padding: 5px 10px;
        margin: 2px;
        color: white;
    }
    .edit-btn {
        background-color: #4CAF50;
    }
    .edit-btn:hover {
        background-color: white;
        color: black;
        border: 1px solid black;
    }
    .delete-btn {
        background-color: #d32f2f;
    }
    .delete-btn:hover {
        background-color: #d32f2f;
    }
    td form {
        display: inline;
        background: none;
        padding: 0;
        margin: 0;
        box-shadow: none;
    }
</style>
</head>
<body>
    <h1>Schedule Management</h1>
    <?php if ($message): ?>
        <div class="message"><?php echo $message; ?></div>
    <?php endif; ?>

    <h2>Create Schedule</h2>
    <form method="post" action="" id="scheduleForm">
        <input type="hidden" id="schedule_id" name="schedule_id">
        <label for="semester">Semester:</label>
        <input type="number" id="semester" name="semester" min="1" max="6" required><br><br>

        <label for="year">Year:</label>
        <input type="number" id="year" name="year" min="2023" max="2100" required><br><br>

        <label for="branch">Branch:</label>
        <select id="branch" name="branch" required>
            <option value="">Select Branch</option>
            <?php foreach ($branches as $branch): ?>
                <option value="<?php echo $branch['id']; ?>"><?php echo $branch['name']; ?></option>
            <?php endforeach; ?>
        </select><br><br>

        <label for="module">Module:</label>
        <select id="module" name="module" required>
            <option value="">Select Module</option>
        </select><br><br>

        <label for="day">Day:</label>
        <select id="day" name="day" required>
            <option value="">Select Day</option>
            <option value="Monday">Monday</option>
            <option value="Tuesday">Tuesday</option>
            <option value="Wednesday">Wednesday</option>
            <option value="Thursday">Thursday</option>
            <option value="Friday">Friday</option>
            <option value="Saturday">Saturday</option>
        </select><br><br>

        <label for="salle">Salle:</label>
        <select id="salle" name="salle" required>
            <option value="">Select Salle</option>
            <?php foreach ($salles as $salle): ?>
                <option value="<?php echo $salle['id']; ?>"><?php echo $salle['name']; ?></option>
            <?php endforeach; ?>
        </select><br><br>

        <label for="periode">Periode:</label>
        <select id="periode" name="periode" required>
            <option value="">Select Periode</option>
        </select><br><br>

        <label for="type_seance">Type Seance:</label>
        <select id="type_seance" name="type_seance" required>
            <option value="">Select Type Seance</option>
            <?php foreach ($typeSeances as $typeSeance): ?>
                <option value="<?php echo $typeSeance['id']; ?>"><?php echo $typeSeance['name']; ?></option>
            <?php endforeach; ?>
        </select><br><br>

        <input style="background:lightblue;" type="submit" id="submitBtn" name="create_schedule" value="Create Schedule">
    </form>

    <h2>All Schedules</h2>
    <div class="schedule-table">
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Branch</th>
                    <th>Module</th>
                    <th>Day</th>
                    <th>Salle</th>
                    <th>Periode</th>
                    <th>Type Seance</th>
                    <th>Semester</th>
                    <th>Year</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($schedules as $schedule): ?>
                    <tr>
                        <td><?php echo $schedule['id']; ?></td>
                        <td><?php echo $schedule['branch_name']; ?></td>
                        <td><?php echo $schedule['module_name']; ?></td>
                        <td><?php echo $schedule['day']; ?></td>
                        <td><?php echo $schedule['salle_name']; ?></td>
                        <td><?php echo $schedule['name_periode']; ?></td>
                        <td><?php echo $schedule['type_seance_name']; ?></td>
                        <td><?php echo $schedule['semester']; ?></td>
                        <td><?php echo $schedule['year']; ?></td>
                        <td>
                            <button class="edit-btn" onclick="editSchedule(<?php echo htmlspecialchars(json_encode($schedule)); ?>)">Edit</button>
                            <form method="post" action="" style="display:inline;">
                                <input type="hidden" name="schedule_id" value="<?php echo $schedule['id']; ?>">
                                <input type="submit" class="delete-btn" name="delete_schedule" value="Delete" onclick="return confirm('Are you sure you want to delete this schedule?');">
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <script>
    function updateModules(branchId, selectedModuleId = '') {
        fetch(`?action=getModules&branch_id=${branchId}`)
            .then(response => response.json())
            .then(data => {
                let moduleSelect = document.getElementById('module');
                moduleSelect.innerHTML = '<option value="">Select Module</option>';
                data.forEach(module => {
                    let option = document.createElement('option');
                    option.value = module.id;
                    option.textContent = module.name;
                    if (module.id == selectedModuleId) {
                        option.selected = true;
                    }
                    moduleSelect.appendChild(option);
                });
                updatePeriods();
            });
    }

    function updatePeriods() {
        let branchId = document.getElementById('branch').value;
        let day = document.getElementById('day').value;
        let salleId = document.getElementById('salle').value;
        let semester = document.getElementById('semester').value;
        let year = document.getElementById('year').value;
        let moduleId = document.getElementById('module').value;
        let scheduleId = document.getElementById('schedule_id').value;
        if (branchId && day && salleId && semester && year && moduleId) {
            let url = `?action=getPeriods&branch_id=${branchId}&day=${day}&salle_id=${salleId}&semester=${semester}&year=${year}&module_id=${moduleId}`;
            if (scheduleId) {
                url += `&exclude_schedule_id=${scheduleId}`;
            }
            fetch(url)
                .then(response => response.json())
                .then(data => {
                    let periodeSelect = document.getElementById('periode');
                    periodeSelect.innerHTML = '<option value="">Select Periode</option>';
                    data.forEach(periode => {
                        let option = document.createElement('option');
                        option.value = periode.id;
                        option.textContent = periode.name_periode;
                        periodeSelect.appendChild(option);
                    });
                });
        }
    }

    document.getElementById('branch').addEventListener('change', function() {
        updateModules(this.value);
    });
    document.getElementById('day').addEventListener('change', updatePeriods);
    document.getElementById('salle').addEventListener('change', updatePeriods);
    document.getElementById('semester').addEventListener('change', updatePeriods);
    document.getElementById('year').addEventListener('change', updatePeriods);
    document.getElementById('module').addEventListener('change', updatePeriods);

    function editSchedule(schedule) {
        document.getElementById('schedule_id').value = schedule.id;
        document.getElementById('semester').value = schedule.semester;
        document.getElementById('year').value = schedule.year;
        document.getElementById('branch').value = schedule.branch_id;
        document.getElementById('day').value = schedule.day;
        document.getElementById('salle').value = schedule.salle_id;
        document.getElementById('type_seance').value = schedule.type_seance_id;
        
        updateModules(schedule.branch_id, schedule.module_id);
        
        // Change button text and name
        document.getElementById('submitBtn').value = 'Update Schedule';
        document.getElementById('submitBtn').name = 'update_schedule';
    }
    </script>
</body>
</html>