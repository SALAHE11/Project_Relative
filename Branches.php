<?php
session_start();

$servername = "localhost";
$username = "root";
$password = "";
$dbname = "database1"; // Change this to your database name

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Add new branch
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add'])) {
    $name = $_POST['name'];
    $max_classes = $_POST['max_classes'];
    $stmt = $conn->prepare("INSERT INTO Branch (name, max_classes) VALUES (?, ?)");
    $stmt->bind_param("si", $name, $max_classes);
    if ($stmt->execute()) {
        $_SESSION['message'] = "New branch added successfully.";
    } else {
        $_SESSION['message'] = "Error: " . $stmt->error;
    }
    $stmt->close();
    header("Location: " . $_SERVER['PHP_SELF']);
    exit();
}

// Update branch
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update'])) {
    $id = $_POST['id'];
    $name = $_POST['name'];
    $max_classes = $_POST['max_classes'];
    $stmt = $conn->prepare("UPDATE Branch SET name = ?, max_classes = ? WHERE id = ?");
    $stmt->bind_param("sii", $name, $max_classes, $id);
    if ($stmt->execute()) {
        $_SESSION['message'] = "Branch updated successfully.";
    } else {
        $_SESSION['message'] = "Error: " . $stmt->error;
    }
    $stmt->close();
    header("Location: " . $_SERVER['PHP_SELF']);
    exit();
}

// Delete branch
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['delete'])) {
    $id = $_POST['id'];

    // Check for related modules
    $checkStmt = $conn->prepare("SELECT COUNT(*) AS count FROM module WHERE branch_id = ?");
    $checkStmt->bind_param("i", $id);
    $checkStmt->execute();
    $checkResult = $checkStmt->get_result();
    $count = $checkResult->fetch_assoc()['count'];
    $checkStmt->close();

    if ($count > 0) {
        $_SESSION['message'] = "Cannot delete branch because it has associated modules.";
    } else {
        $stmt = $conn->prepare("DELETE FROM Branch WHERE id = ?");
        $stmt->bind_param("i", $id);
        if ($stmt->execute()) {
            $_SESSION['message'] = "Branch deleted successfully.";
        } else {
            $_SESSION['message'] = "Error: " . $stmt->error;
        }
        $stmt->close();
    }
    header("Location: " . $_SERVER['PHP_SELF']);
    exit();
}

// Fetch all branches
$result = $conn->query("SELECT * FROM Branch");

// Retrieve and clear message
$message = "";
if (isset($_SESSION['message'])) {
    $message = $_SESSION['message'];
    unset($_SESSION['message']);
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Manage Branches</title>
    <link rel="stylesheet" href="Branches.css">
</head>
<body>
    <h2>Add New Branch</h2>
    
    <?php if ($message): ?>
        <p class="message"><?php echo $message; ?></p>
    <?php endif; ?>

    <form action="" method="post">
        <label for="name">Branch Name:</label>
        <input type="text" id="name" name="name" placeholder="Type Branche Name" required><br><br>
        
        <label for="max_classes">Max Classes:</label>
        <input type="number" id="max_classes" name="max_classes" placeholder="Type maximum Number of Classes" min="4" max="6" required><br><br>
        
        <input type="submit" name="add" value="Add Branch" class="buttonsub">
    </form>

    <h2>Branches List</h2>
    <table>
        <tr>
            <th>ID</th>
            <th>Name</th>
            <th>Max Classes</th>
            <th>Actions</th>
        </tr>
        <?php while ($row = $result->fetch_assoc()): ?>
        <tr>
            <form action="" method="post">
                <td><?php echo $row['id']; ?><input type="hidden" name="id" value="<?php echo $row['id']; ?>"></td>
                <td><input type="text" name="name" value="<?php echo $row['name']; ?>"></td>
                <td><input type="number" name="max_classes" value="<?php echo $row['max_classes']; ?>"></td>
                <td>
                    <input type="submit" name="update" value="Update" class="buttonup">
                    <input type="submit" name="delete" value="Delete" class="buttondel" onclick="return confirm('Are you sure you want to delete this branch?');">
                </td>
            </form>
        </tr>
        <?php endwhile; ?>
    </table>

    <?php $conn->close(); ?>
</body>
</html>
