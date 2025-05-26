<?php
session_start();
require_once "db.php";

if (isset($_SESSION["user"])) {
    header("Location: index.php");
    exit;
}

// CSRF token create
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
?>

<!DOCTYPE html>
<html>
<head>
    <link rel="stylesheet" href="loginregister.css">
    <meta charset="UTF-8">
    <title>Register</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
</head>
<body>

<nav class="navbar-left">
    <div class="navbar-logo">
      <a href="index.php">MyApp</a>
    </div>
</nav>

<div class="loginregister">
<?php
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["submit"])) {
    $usrname = trim($_POST["usrname"] ?? '');
    $email = trim($_POST["email"] ?? '');
    $password = $_POST["password"] ?? '';
    $passwordRepeat = $_POST["repeat_password"] ?? '';
    $csrf_token = $_POST["csrf_token"] ?? '';

    $errors = [];

    // CSRF control
    if (!hash_equals($_SESSION['csrf_token'], $csrf_token)) {
        $errors[] = "Invalid CSRF token.";
    }

    // validations
    if (empty($usrname) || empty($email) || empty($password) || empty($passwordRepeat)) {
        $errors[] = "All fields are required.";
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Email is not valid.";
    }

    if (strlen($password) < 8) {
        $errors[] = "Password must be at least 8 characters long.";
    }

    if ($password !== $passwordRepeat) {
        $errors[] = "Passwords do not match.";
    }

    // check if email is available
    if (empty($errors)) {
        $sql = "SELECT id FROM users WHERE email = ?";
        $stmt = mysqli_prepare($conn, $sql);
        mysqli_stmt_bind_param($stmt, "s", $email);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_store_result($stmt);

        if (mysqli_stmt_num_rows($stmt) > 0) {
            $errors[] = "Email already exists.";
        }
        mysqli_stmt_close($stmt);
    }

    // errors or add user
    if (!empty($errors)) {
        foreach ($errors as $error) {
            echo "<div class='alert alert-danger'>" . htmlspecialchars($error) . "</div>";
        }
    } else {
        $passwordHash = password_hash($password, PASSWORD_DEFAULT);
        $sql = "INSERT INTO users (usrname, email, password) VALUES (?, ?, ?)";
        $stmt = mysqli_prepare($conn, $sql);
        if ($stmt) {
            mysqli_stmt_bind_param($stmt, "sss", $usrname, $email, $passwordHash);
            mysqli_stmt_execute($stmt);
            mysqli_stmt_close($stmt);

            echo "<div class='alert alert-success'>You are registered successfully. Redirecting...</div>";
            header("Refresh: 2; URL=login.php");
            exit;
        } else {
            echo "<div class='alert alert-danger'>Something went wrong. Please try again.</div>";
        }
    }
}
?>

    <h2>Register</h2>
    <form method="post" action="register.php">
        <label for="usrname">Name:</label><br>
        <input type="text" id="usrname" name="usrname" required><br>

        <label for="email">E-Mail:</label><br>
        <input type="email" id="email" name="email" required><br>

        <label for="password">Password:</label><br>
        <input type="password" id="password" name="password" minlength="8" required><br>

        <label for="repeat_password">Repeat Password:</label><br>
        <input type="password" id="repeat_password" name="repeat_password" minlength="8" required><br><br>

        <!-- CSRF Token -->
        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>">

        <input type="submit" name="submit" value="Register">
    </form> 

    <div><p>Already Registered? <a href="login.php">Login Here</a></p></div>
</div>

</body>
</html>