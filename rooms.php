<?php
session_start();

$servername = "localhost";
$username = "root";
$password = "Zoro*2222";
$dbname = "database1";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Initialize error message variable
if (isset($_SESSION['error'])) {
    $error = $_SESSION['error'];
    unset($_SESSION['error']);
} else {
    $error = "";
}

// Add salle
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add_salle'])) {
    $name = $_POST['name'];
    // Check if the salle already exists
    $checkSql = "SELECT * FROM salle WHERE name='$name'";
    $checkResult = $conn->query($checkSql);

    if ($checkResult->num_rows > 0) {
        $_SESSION['error'] = "Salle already exists!";
    } else {
        $sql = "INSERT INTO salle (name) VALUES ('$name')";
        if ($conn->query($sql) === TRUE) {
            echo "New salle added successfully";
        } else {
            echo "Error: " . $sql . "<br>" . $conn->error;
        }
    }
    header("Location: ".$_SERVER['PHP_SELF']);
    exit();
}

// Update salle
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_salle'])) {
    $id = $_POST['id'];
    $name = $_POST['name'];
    $sql = "UPDATE salle SET name='$name' WHERE id=$id";
    if ($conn->query($sql) === TRUE) {
        echo "Salle updated successfully";
    } else {
        echo "Error: " . $sql . "<br>" . $conn->error;
    }
    header("Location: ".$_SERVER['PHP_SELF']);
    exit();
}

// Delete salle
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['delete_salle'])) {
    $id = $_POST['id'];
    $sql = "DELETE FROM salle WHERE id=$id";
    if ($conn->query($sql) === TRUE) {
        echo "Salle deleted successfully";
    } else {
        echo "Error: " . $sql . "<br>" . $conn->error;
    }
    header("Location: ".$_SERVER['PHP_SELF']);
    exit();
}

$sql = "SELECT * FROM salle";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="room.css">
    <title>Manage Salles</title>
</head>
<body>
    <h1>Rooms</h1>

    <h2>Add Rooms</h2>
    <?php if ($error): ?>
        <div class="error-message"><?php echo $error; ?></div>
    <?php endif; ?>
    <form method="POST" action="">
        <input type="text" name="name" placeholder="Type Room Name" required>
        <button type="submit" name="add_salle" class="buttonsub">Add Salle</button>
    </form>

    <h2>Rooms List</h2>
    <table>
        <tr>
            <th>ID</th>
            <th>Name</th>
            <th>Options</th>
        </tr>
        <?php while($row = $result->fetch_assoc()): ?>
        <tr>
            <td><?php echo $row['id']; ?></td>
            <td><?php echo $row['name']; ?></td>
            <td>
                <form method="POST" action="" class="inline-form" style="display:inline-block;">
                    <input type="hidden" name="id" value="<?php echo $row['id']; ?>">
                    <input type="text" name="name" value="<?php echo $row['name']; ?>" required>
                    <button type="submit" name="update_salle" class="buttonup">Update</button>
                </form>
                <form method="POST" action="" class="inline-form" style="display:inline-block;">
                    <input type="hidden" name="id" value="<?php echo $row['id']; ?>">
                    <button type="submit" name="delete_salle" class="buttondel">Delete</button>
                </form>
            </td>
        </tr>
        <?php endwhile; ?>
    </table>

    <?php $conn->close(); ?>
</body>
</html>
