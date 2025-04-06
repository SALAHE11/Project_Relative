<?php
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

// Add teacher
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add_teacher'])) {
    $name = $_POST['name'];
    
    // Check if the teacher already exists
    $check_sql = "SELECT * FROM teacher WHERE name='$name'";
    $check_result = $conn->query($check_sql);
    if ($check_result->num_rows > 0) {
        echo "Teacher with the name '$name' already exists.";
    } else {
        $sql = "INSERT INTO teacher (name) VALUES ('$name')";
        if ($conn->query($sql) === TRUE) {
            header("Location: " . $_SERVER['PHP_SELF']);
            exit();
        } else {
            echo "Error: " . $sql . "<br>" . $conn->error;
        }
    }
}

// Update teacher
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_teacher'])) {
    $id = $_POST['id'];
    $name = $_POST['name'];
    
    // Check if the updated name already exists for a different teacher
    $check_sql = "SELECT * FROM teacher WHERE name='$name' AND id != $id";
    $check_result = $conn->query($check_sql);
    if ($check_result->num_rows > 0) {
        echo "Another teacher with the name '$name' already exists.";
    } else {
        $sql = "UPDATE teacher SET name='$name' WHERE id=$id";
        if ($conn->query($sql) === TRUE) {
            header("Location: " . $_SERVER['PHP_SELF']);
            exit();
        } else {
            echo "Error: " . $sql . "<br>" . $conn->error;
        }
    }
}

// Delete teacher
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['delete_teacher'])) {
    $id = $_POST['id'];
    $sql = "DELETE FROM teacher WHERE id=$id";
    if ($conn->query($sql) === TRUE) {
        header("Location: " . $_SERVER['PHP_SELF']);
        exit();
    } else {
        echo "Error: " . $sql . "<br>" . $conn->error;
    }
}

$sql = "SELECT * FROM teacher";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="Teachers.css">
    <title>Manage Teachers</title>
</head>
<body>
    <div class="container">
        <h1>Teachers</h1>

        <h2>Add Teacher</h2>
        <form method="POST" action="">
            <input type="text" name="name" placeholder="Type Teacher Name" required>
            <button type="submit" name="add_teacher" class="buttonsub">Add Teacher</button>
        </form>

        <h2>Teachers List</h2>
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
                    <form method="POST" action="" style="display:inline-block;">
                        <input type="hidden" name="id" value="<?php echo $row['id']; ?>">
                        <input type="text" name="name" value="<?php echo $row['name']; ?>" required>
                        <button type="submit" name="update_teacher" class="buttonup">Update</button>
                    </form>
                    <form method="POST" action="" style="display:inline-block;">
                        <input type="hidden" name="id" value="<?php echo $row['id']; ?>">
                        <button type="submit" name="delete_teacher" class="buttondel">Delete</button>
                    </form>
                </td>
            </tr>
            <?php endwhile; ?>
        </table>
    </div>

    <?php $conn->close(); ?>
</body>
</html>