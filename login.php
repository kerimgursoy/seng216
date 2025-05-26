<?php
session_start();

if (isset($_SESSION['user_id'])) {
    header('Location: index.php');
    exit();
}

require 'db.php';

// CSRF token create
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

$login_error = "";

if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["login"])) {
    $email = trim($_POST["email"] ?? '');
    $password = $_POST["password"] ?? '';
    $csrf_token = $_POST["csrf_token"] ?? '';

    // CSRF control
    if (!hash_equals($_SESSION["csrf_token"], $csrf_token)) {
        $login_error = "Invalid CSRF token.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $login_error = "Invalid email format.";
    } elseif (strlen($password) < 8) {
        $login_error = "Password must be at least 8 characters.";
    } else {
        // check user
        $sql = "SELECT id, usrname, password FROM users WHERE email = ?";
        $stmt = mysqli_prepare($conn, $sql);
        mysqli_stmt_bind_param($stmt, "s", $email);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $user = mysqli_fetch_assoc($result);

        if ($user && password_verify($password, $user["password"])) {
            $_SESSION["user_id"] = $user["id"];
            $_SESSION["user"] = $user["usrname"];

            // CSRF token unset
            unset($_SESSION["csrf_token"]);

            header("Location: index.php");
            exit();
        } else {
            $login_error = "Email or password is incorrect.";
        }
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Login</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="loginregister.css">
</head>
<body>
<nav class="navbar-left">
    <div class="navbar-logo">
        <a href="index.php">MyApp</a>
    </div>
</nav>

<div class="loginregister">
    <h2>Login</h2>

    <?php if (!empty($login_error)): ?>
        <div class='alert alert-danger'><?= htmlspecialchars($login_error) ?></div>
    <?php endif; ?>

    <form action="login.php" method="post">
        <label for="email">E-Mail:</label><br>
        <input type="email" name="email" required><br>

        <label for="password">Password:</label><br>
        <input type="password" name="password" required><br><br>

        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>">
        <input type="submit" name="login" value="Login">
    </form>

    <div><p>Not registered? <a href="register.php">Register Here</a></p></div>
</div>
</body>
</html>