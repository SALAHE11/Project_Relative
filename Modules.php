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
$success = ""; // Initialize success message variable

// Fetch branches for dropdown
$branches = [];
$branchSql = "SELECT id, name FROM branch";
$branchResult = $conn->query($branchSql);
while ($row = $branchResult->fetch_assoc()) {
    $branches[] = $row;
}

// Add module
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add_module'])) {
    $name = $_POST['name'];
    $branch_id = $_POST['branch_id'];
    // Check if the module already exists
    $checkSql = "SELECT * FROM module WHERE name='$name' AND branch_id='$branch_id'";
    $checkResult = $conn->query($checkSql);

    if ($checkResult->num_rows > 0) {
        $_SESSION['error'] = "Module already exists in this branch!";
    } else {
        $sql = "INSERT INTO module (name, branch_id) VALUES ('$name', '$branch_id')";
        if ($conn->query($sql) === TRUE) {
            $_SESSION['success'] = "New module added successfully";
        } else {
            $_SESSION['error'] = "Error: " . $sql . "<br>" . $conn->error;
        }
    }
    header("Location: " . $_SERVER['PHP_SELF']);
    exit();
}

// Update module
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_module'])) {
    $id = $_POST['id'];
    $name = $_POST['name'];
    $branch_id = $_POST['branch_id'];
    $sql = "UPDATE module SET name='$name', branch_id='$branch_id' WHERE id=$id";
    if ($conn->query($sql) === TRUE) {
        $_SESSION['success'] = "Module updated successfully";
    } else {
        $_SESSION['error'] = "Error: " . $sql . "<br>" . $conn->error;
    }
    header("Location: " . $_SERVER['PHP_SELF']);
    exit();
}

// Delete module
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['delete_module'])) {
    $id = $_POST['id'];
    $sql = "DELETE FROM module WHERE id=$id";
    if ($conn->query($sql) === TRUE) {
        $_SESSION['success'] = "Module deleted successfully";
    } else {
        $_SESSION['error'] = "Error: " . $sql . "<br>" . $conn->error;
    }
    header("Location: " . $_SERVER['PHP_SELF']);
    exit();
}

// Fetch modules
$sql = "SELECT module.id, module.name, module.branch_id, branch.name as branch_name FROM module INNER JOIN branch ON module.branch_id = branch.id";
$result = $conn->query($sql);

if (isset($_SESSION['error'])) {
    $error = $_SESSION['error'];
    unset($_SESSION['error']);
}

if (isset($_SESSION['success'])) {
    $success = $_SESSION['success'];
    unset($_SESSION['success']);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="Modules.css">
    <title>Manage Modules</title>
</head>
<body>
    <h1>Modules</h1>

    <?php if ($error): ?>
        <div class="error-message" style="color: white;"><?php echo $error; ?></div>
    <?php endif; ?>

    <?php if ($success): ?>
        <div class="success-message" style="color: white;"><?php echo $success; ?></div>
    <?php endif; ?>

    <h2>Add Module</h2>
    <form method="POST" action="">
        <input type="text" name="name" placeholder="Module Name" required>
        <select name="branch_id" required>
            <option value="">Select Branch</option>
            <?php foreach ($branches as $branch): ?>
                <option value="<?php echo $branch['id']; ?>"><?php echo $branch['name']; ?></option>
            <?php endforeach; ?>
        </select>
        <button type="submit" name="add_module" class="buttonsub">Add Module</button>
    </form>

    <h2>Modules List</h2>
    <table>
        <tr>
            <th>ID</th>
            <th>Name</th>
            <th>Branch</th>
            <th>Options</th>
        </tr>
        <?php while($row = $result->fetch_assoc()): ?>
        <tr>
            <form method="POST" action="">
                <td><?php echo $row['id']; ?></td>
                <td>
                    <input type="text" name="name" value="<?php echo $row['name']; ?>" required>
                </td>
                <td>
                    <select name="branch_id" required>
                        <?php foreach ($branches as $branch): ?>
                            <option value="<?php echo $branch['id']; ?>" <?php if ($branch['id'] == $row['branch_id']) echo 'selected'; ?>><?php echo $branch['name']; ?></option>
                        <?php endforeach; ?>
                    </select>
                    <input type="hidden" name="id" value="<?php echo $row['id']; ?>">
                </td>
                <td>
                    <button type="submit" name="update_module" class="buttonup">Update</button>
                    <button type="submit" name="delete_module" class="buttondel">Delete</button>
                </td>
            </form>
        </tr>
        <?php endwhile; ?>
    </table>

    <?php $conn->close(); ?>
</body>
</html>
