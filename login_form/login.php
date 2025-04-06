<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login Page</title>
    <link rel="stylesheet" href="logins.css">
</head>
<body>
    <nav>
        <a href="../logo.png"><img src="../logo.png" alt="logo"></a>
    </nav>
    <div class="form-wrapper">
        <h2>Login</h2>
        <form action="<?php echo $_SERVER['PHP_SELF']; ?>" method="post">
            <div class="form-control">
                <input type="text" name="username" required>
                <label>Username</label>
            </div>
            <div class="form-control">
                <input type="password" name="password" required>
                <label>Password</label>
            </div>
            <button type="submit">Login</button>
        </form>
    </div>

    <?php
    // Database connection
    $servername = "localhost";
    $db_username = "root";
    $db_password = "";
    $dbname = "database1";

    $conn = new mysqli($servername, $db_username, $db_password, $dbname);

    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        $username = $_POST['username'];
        $password = $_POST['password'];

        $sql = "SELECT * FROM users WHERE username = ? AND password = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ss", $username, $password);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            // Login successful
            header("Location: ../MAIN.php");
            exit();
        } else {
            echo "<p>Invalid username or password</p>";
        }

        $stmt->close();
    }

    $conn->close();
    ?>
</body>
</html>