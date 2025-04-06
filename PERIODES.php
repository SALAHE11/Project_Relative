<!DOCTYPE html>
<html>
<head>
    <title>Manage Periode</title>
    <style>
    @import url('https://fonts.googleapis.com/css2?family=Poppins&display=swap');

    body {
        font-family: "Poppins", sans-serif;
        font-style: normal;
        margin: 0;
        padding: 20px;
        background-image: #f5f7fa;
        background-size: cover;
        background-position: center;
        position: relative;
        height: 100vh;
    }

    h2 {
        text-align: center;
        color: black;
    }

    form {
        margin-bottom: 20px;
    }

    label {
        display: block;
        margin-top: 10px;
        color: black;
    }

    input[type="text"], input[type="time"], input[type="number"] {
        width: 200px;
        padding: 5px;
        margin-top: 5px;
    }

    input[type="submit"], button {
        padding: 10px 20px;
        font-size: 16px;
        border: none;
        cursor: pointer;
        margin-top: 10px;
        border-radius: 4px;
    }

    .buttonsub {
        background-color: #000000;
        color: #fff;
        border: 1px solid rgb(255, 255, 255);
    }

    .buttondel {
        background-color: #ff0000;
        color: #fff;
        border: 1px solid rgb(255, 255, 255);
    }

    .buttonup {
        background-color: #00cc00;
        color: #fff;
        border: 1px solid rgb(255, 255, 255);
    }

    input[type="submit"]:hover, button:hover {
        opacity: 0.8;
    }

    table {
        width: 100%;
        border-collapse: separate;
        border-spacing: 0;
        margin-top: 20px;
        background-color: gainsboro;
        border-radius: 8px;
        overflow: hidden;
    }

    table, th, td {
        border: 1px solid black;
    }

    th, td {
        padding: 8px;
        text-align: left;
    }

    th {
        background-color: #f2f2f2;
    }

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

    .message {
        color: white;
        margin-top: 20px;
    }

    .id {
        color: black;
    }
</style>
</head>
<body>
    <h2>Add New Periode</h2>
    
    <?php
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

    // Add new periode
    if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add'])) {
        $debut_time = $_POST['debut_time'];
        $fin_time = $_POST['fin_time'];
        $name_periode = $_POST['name_periode'];
        $periode_category = $_POST['periode_category'];
        $stmt = $conn->prepare("INSERT INTO periode (debut_time, fin_time, name_periode, periode_category) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("sssi", $debut_time, $fin_time, $name_periode, $periode_category);
        if ($stmt->execute()) {
            echo "<p class='message'>New periode added successfully.</p>";
        } else {
            echo "<p class='message'>Error: " . $stmt->error . "</p>";
        }
        $stmt->close();
        header("Location: " . $_SERVER['PHP_SELF']);
        exit;
    }

    // Update periode
    if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update'])) {
        $id = $_POST['id'];
        $debut_time = $_POST['debut_time'];
        $fin_time = $_POST['fin_time'];
        $name_periode = $_POST['name_periode'];
        $periode_category = $_POST['periode_category'];
        $stmt = $conn->prepare("UPDATE periode SET debut_time = ?, fin_time = ?, name_periode = ?, periode_category = ? WHERE id = ?");
        $stmt->bind_param("sssii", $debut_time, $fin_time, $name_periode, $periode_category, $id);
        if ($stmt->execute()) {
            echo "<p class='message'>Periode updated successfully.</p>";
        } else {
            echo "<p class='message'>Error: " . $stmt->error . "</p>";
        }
        $stmt->close();
        header("Location: " . $_SERVER['PHP_SELF']);
        exit;
    }

    // Delete periode
    if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['delete'])) {
        $id = $_POST['id'];
        $stmt = $conn->prepare("DELETE FROM periode WHERE id = ?");
        $stmt->bind_param("i", $id);
        if ($stmt->execute()) {
            echo "<p class='message'>Periode deleted successfully.</p>";
        } else {
            echo "<p class='message'>Error: " . $stmt->error . "</p>";
        }
        $stmt->close();
        header("Location: " . $_SERVER['PHP_SELF']);
        exit;
    }

    // Fetch all periodes
    $result = $conn->query("SELECT * FROM periode");
    ?>

    <form action="" method="post">
        <label for="debut_time">Debut Time:</label>
        <input type="time" id="debut_time" name="debut_time" required><br><br>
        
        <label for="fin_time">Fin Time:</label>
        <input type="time" id="fin_time" name="fin_time" required><br><br>
        
        <label for="name_periode">Name Periode:</label>
        <input type="text" id="name_periode" name="name_periode" required><br><br>
        
        <label for="periode_category">Periode Category:</label>
        <input type="number" id="periode_category" name="periode_category" required><br><br>
        
        <input type="submit" name="add" value="Add Periode" class="buttonsub">
    </form>

    <h2>Existing Periodes</h2>
    <table>
        <tr>
            <th>ID</th>
            <th>Debut Time</th>
            <th>Fin Time</th>
            <th>Name Periode</th>
            <th>Periode Category</th>
            <th>Actions</th>
        </tr>
        <?php while ($row = $result->fetch_assoc()): ?>
        <tr>
            <form action="" method="post">
                <td class="id"><?php echo $row['id']; ?><input type="hidden" name="id" value="<?php echo $row['id']; ?>"></td>
                <td><input type="time" name="debut_time" value="<?php echo $row['debut_time']; ?>"></td>
                <td><input type="time" name="fin_time" value="<?php echo $row['fin_time']; ?>"></td>
                <td><input type="text" name="name_periode" value="<?php echo $row['name_periode']; ?>"></td>
                <td><input type="number" name="periode_category" value="<?php echo $row['periode_category']; ?>"></td>
                <td>
                    <input type="submit" name="update" value="Update" class="buttonup">
                    <input type="submit" name="delete" value="Delete" class="buttondel" onclick="return confirm('Are you sure you want to delete this periode?');">
                </td>
            </form>
        </tr>
        <?php endwhile; ?>
    </table>

    <?php $conn->close(); ?>
</body>
</html>
