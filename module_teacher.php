<?php
session_start();

$servername = "localhost";
$username = "root";
$password = "";
$dbname = "database1";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$error = ""; // Initialize error message variable

// Add module_teacher
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add_module_teacher'])) {
    $module_id = $_POST['module_id'];
    $teacher_id = $_POST['teacher_id'];
    $annee_universit = $_POST['annee_universit'];

    // Check if the module_teacher already exists
    $checkSql = "SELECT * FROM module_teacher WHERE module_id='$module_id' AND teacher_id='$teacher_id' AND annee_universit='$annee_universit'";
    $checkResult = $conn->query($checkSql);

    if ($checkResult->num_rows > 0) {
        $_SESSION['error'] = "Module-Teacher association already exists!";
    } else {
        $sql = "INSERT INTO module_teacher (module_id, teacher_id, annee_universit) VALUES ('$module_id', '$teacher_id', '$annee_universit')";
        if ($conn->query($sql) === TRUE) {
            $_SESSION['message'] = "New module-teacher association added successfully";
        } else {
            $_SESSION['error'] = "Error: " . $sql . "<br>" . $conn->error;
        }
    }
    header("Location: " . $_SERVER['PHP_SELF']);
    exit;
}

// Update module_teacher
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_module_teacher'])) {
    $id = $_POST['id'];
    $module_id = $_POST['module_id'];
    $teacher_id = $_POST['teacher_id'];
    $annee_universit = $_POST['annee_universit'];

    $sql = "UPDATE module_teacher SET module_id='$module_id', teacher_id='$teacher_id', annee_universit='$annee_universit' WHERE id='$id'";
    if ($conn->query($sql) === TRUE) {
        $_SESSION['message'] = "Module-Teacher association updated successfully";
    } else {
        $_SESSION['error'] = "Error: " . $sql . "<br>" . $conn->error;
    }
    header("Location: " . $_SERVER['PHP_SELF']);
    exit;
}

// Delete module_teacher
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['delete_module_teacher'])) {
    $id = $_POST['id'];
    $sql = "DELETE FROM module_teacher WHERE id='$id'";
    if ($conn->query($sql) === TRUE) {
        $_SESSION['message'] = "Module-Teacher association deleted successfully";
    } else {
        $_SESSION['error'] = "Error: " . $sql . "<br>" . $conn->error;
    }
    header("Location: " . $_SERVER['PHP_SELF']);
    exit;
}

$sql = "SELECT * FROM module_teacher";
$result = $conn->query($sql);

// Fetch modules and teachers for dropdown lists
$modulesSql = "SELECT * FROM module";
$modulesResult = $conn->query($modulesSql);

$teachersSql = "SELECT * FROM teacher";
$teachersResult = $conn->query($teachersSql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="module_teacheeer.css">
    <title>Manage Module-Teacher Associations</title>
    <style>
     @import url('https://fonts.googleapis.com/css2?family=Poppins&display=swap');

body {
    font-family: 'Poppins', sans-serif;
    font-style: normal;
    margin: 0;
    padding: 20px;
    background-image: #f5f7fa;
    background-size: cover;
    background-position: center;
    position: relative;
    height: 100vh;
}

h1 {
    text-align: center;
    color: rgb(50, 50, 50);
}

h2 {
    color: rgb(50, 50, 50);
}

label {
    display: block;
    margin-top: 10px;
    color: rgb(50, 50, 50); /* Set label color to white */
}

input[type="text"], select {
    width: 100%;
    padding: 8px;
    margin-top: 5px;
    margin-bottom: 20px;
    display: inline-block;
    border: 1px solid #ccc;
    border-radius: 4px;
    box-sizing: border-box;
}

.buttonsub {
    width: 100%;
    background-color: black;
    color: white;
    padding: 14px 20px;
    margin: 8px 0;
    border: none;
    border-radius: 4px;
    cursor: pointer;
}
.buttondel {
    width: 100%;
    background-color:red ;
    color: white;
    padding: 14px 20px;
    margin: 8px 0;
    border: none;
    border-radius: 4px;
    cursor: pointer;
}
.buttonup {
    width: 100%;
    background-color: green;
    color: white;
    padding: 14px 20px;
    margin: 8px 0;
    border: none;
    border-radius: 4px;
    cursor: pointer;
}
button:hover {
    background-color: white;
    border: 1px solid black;
    color: black;
}

table {
    width: 100%;
    border-collapse: separate; /* Use separate to allow border-radius to work */
    border-spacing: 0; /* Remove spacing between cells */
    margin-top: 20px;
    background-color: gainsboro;
    border-radius: 8px; /* Apply border-radius to the entire table */
    overflow: hidden; /* Ensure rounded corners are applied */
}

th, td {
    border: 1px solid black;
}
table{
    border: 1px solid black;
}

th, td {
    padding: 8px;
    text-align: center;
}

th {
    background-color: #f2f2f2;
}

/* Apply border-radius to the first and last cells in the rows */
table tr:first-child th:first-child {
    border-top-left-radius: 8px;
}

table tr:first-child th:last-child {
    border-top-right-radius: 8px;
}

table tr:last-child td:first-child {
    border-bottom-left-radius: 8px;
}

table tr:last-child td:last-child {
    border-bottom-right-radius: 8px;
}

.error-message {
    color: red;
    margin-bottom: 20px;
}

.success-message {
    color: white; /* Set success message color to white */
    background-color: green; /* Optional: Add a background color for better visibility */
    padding: 10px;
    border-radius: 4px;
    margin-bottom: 20px;
}

    </style>
</head>
<body>
    <h1>Module-Teacher Associations</h1>

    <h2>Add Module-Teacher Association</h2>
    <?php if (isset($_SESSION['error'])): ?>
        <div class="error-message"><?php echo $_SESSION['error']; unset($_SESSION['error']); ?></div>
    <?php endif; ?>
    <?php if (isset($_SESSION['message'])): ?>
        <div class="success-message"><?php echo $_SESSION['message']; unset($_SESSION['message']); ?></div>
    <?php endif; ?>
    <form method="POST" action="">
        <label for="module_id">Module:</label>
        <select name="module_id" id="module_id" required>
            <?php while($module = $modulesResult->fetch_assoc()): ?>
                <option value="<?php echo $module['id']; ?>"><?php echo $module['name']; ?></option>
            <?php endwhile; ?>
        </select>
        <label for="teacher_id">Teacher:</label>
        <select name="teacher_id" id="teacher_id" required>
            <?php while($teacher = $teachersResult->fetch_assoc()): ?>
                <option value="<?php echo $teacher['id']; ?>"><?php echo $teacher['name']; ?></option>
            <?php endwhile; ?>
        </select>
        <label for="annee_universit">College year :</label>
        <input type="number" name="annee_universit" id="annee_universit" placeholder="Ex : 1, 2, 3"  min="1" max="3" required>
        <button type="submit" name="add_module_teacher" class="buttonsub">Add Association</button>
    </form>

    <h2>Module-Teacher Associations List</h2>
    <table>
        <tr>
            <th>ID</th>
            <th>Module Name</th>
            <th>Teacher Name</th>
            <th>College year</th>
            <th>Options</th>
        </tr>
        <?php while($row = $result->fetch_assoc()): ?>
        <tr>
            <td><?php echo $row['id']; ?></td>
            <td>
                <?php
                $moduleId = $row['module_id'];
                $moduleSql = "SELECT name FROM module WHERE id='$moduleId'";
                $moduleResult = $conn->query($moduleSql);
                $moduleName = $moduleResult->fetch_assoc()['name'];
                echo $moduleName;
                ?>
            </td>
            <td>
                <?php
                $teacherId = $row['teacher_id'];
                $teacherSql = "SELECT name FROM teacher WHERE id='$teacherId'";
                $teacherResult = $conn->query($teacherSql);
                $teacherName = $teacherResult->fetch_assoc()['name'];
                echo $teacherName;
                ?>
            </td>
            <td><?php echo $row['annee_universit']; ?></td>
            <td>
                <form method="POST" action="" class="inline-form" style="display:inline-block;">
                    <input type="hidden" name="id" value="<?php echo $row['id']; ?>">
                    <select name="module_id" required>
                        <?php
                        $modulesResult->data_seek(0); // Reset pointer to the beginning
                        while($module = $modulesResult->fetch_assoc()):
                        ?>
                            <option value="<?php echo $module['id']; ?>" <?php if ($module['id'] == $row['module_id']) echo 'selected'; ?>><?php echo $module['name']; ?></option>
                        <?php endwhile; ?>
                    </select>
                    <select name="teacher_id" required>
                        <?php
                        $teachersResult->data_seek(0); // Reset pointer to the beginning
                        while($teacher = $teachersResult->fetch_assoc()):
                        ?>
                            <option value="<?php echo $teacher['id']; ?>" <?php if ($teacher['id'] == $row['teacher_id']) echo 'selected'; ?>><?php echo $teacher['name']; ?></option>
                        <?php endwhile; ?>
                    </select>
                    <input type="number" name="annee_universit" value="<?php echo $row['annee_universit']; ?>" required>
                    <button type="submit" name="update_module_teacher" class="buttonup">Update</button>
                </form>
                <form method="POST" action="" class="inline-form" style="display:inline-block;">
                    <input type="hidden" name="id" value="<?php echo $row['id']; ?>">
                    <button type="submit" name="delete_module_teacher" class="buttondel">Delete</button>
                </form>
            </td>
        </tr>
        <?php endwhile; ?>
    </table>

    <?php $conn->close(); ?>
</body>
</html>
