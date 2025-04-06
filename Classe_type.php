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

// Add new type_seance
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add'])) {
    $name = $_POST['name'];
    $stmt = $conn->prepare("INSERT INTO type_seance (name) VALUES (?)");
    $stmt->bind_param("s", $name);
    if ($stmt->execute()) {
        $_SESSION['message'] = "New classe type added successfully.";
    } else {
        $_SESSION['message'] = "Error: " . $stmt->error;
    }
    $stmt->close();
    header("Location: " . $_SERVER['PHP_SELF']);
    exit();
}

// Update type_seance
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update'])) {
    $id = $_POST['id'];
    $name = $_POST['name'];
    $stmt = $conn->prepare("UPDATE type_seance SET name = ? WHERE id = ?");
    $stmt->bind_param("si", $name, $id);
    if ($stmt->execute()) {
        $_SESSION['message'] = "classe type updated successfully.";
    } else {
        $_SESSION['message'] = "Error: " . $stmt->error;
    }
    $stmt->close();
    header("Location: " . $_SERVER['PHP_SELF']);
    exit();
}

// Delete type_seance
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['delete'])) {
    $id = $_POST['id'];
    $stmt = $conn->prepare("DELETE FROM type_seance WHERE id = ?");
    $stmt->bind_param("i", $id);
    if ($stmt->execute()) {
        $_SESSION['message'] = "classe type deleted successfully.";
    } else {
        $_SESSION['message'] = "Error: " . $stmt->error;
    }
    $stmt->close();
    header("Location: " . $_SERVER['PHP_SELF']);
    exit();
}

// Fetch all type_seance
$result = $conn->query("SELECT * FROM type_seance");

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
    <title>Manage Type Seance</title>
    <link rel="stylesheet" href="Classe_types.css">
</head>
<body>
    <h2>Add New Classe Type</h2>
    
    <?php if ($message): ?>
        <p class="message"><?php echo $message; ?></p>
    <?php endif; ?>

    <form action="" method="post">
        <label for="name">Classe Type Name:</label>
        <input type="text" id="name" name="name" required><br><br>
        
        <input type="submit" name="add" value="Add Classe Type" class="buttonsub">
    </form>

    <h2>Existing Classe Types</h2>
    <table>
        <tr>
            <th>ID</th>
            <th>Name</th>
            <th>Actions</th>
        </tr>
        <?php while ($row = $result->fetch_assoc()): ?>
        <tr>
            <form action="" method="post">
                <td><?php echo $row['id']; ?><input type="hidden" name="id" value="<?php echo $row['id']; ?>"></td>
                <td><input type="text" name="name" value="<?php echo $row['name']; ?>"></td>
                <td>
                    <input type="submit" name="update" value="Update" class="buttonup">
                    <input type="submit" name="delete" value="Delete" class="buttondel" onclick="return confirm('Are you sure you want to delete this classe type?');">
                </td>
            </form>
        </tr>
        <?php endwhile; ?>
    </table>

    <?php $conn->close(); ?>
</body>
</html>
