<?php
session_start();
if (isset($_SESSION['user_id'])) {
    header('Location: index.php');
    exit();
}

require 'db.php';
if (isset($_POST["login"])) {
    $email = $_POST["email"];
    $password = $_POST["password"];

    $sql = "SELECT * FROM users WHERE email = ?";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "s", $email);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $user = mysqli_fetch_assoc($result);

    if ($user && password_verify($password, $user["password"])) {
        $_SESSION["user_id"] = $user["id"];
        $_SESSION["user"] = $user["usrname"];

        header("Location: index.php");
        exit();
    } else {
        $login_error = "Email or password incorrect";
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Login</title>
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

    <?php if (isset($login_error)): ?>
        <div class='alert alert-danger'><?= htmlspecialchars($login_error) ?></div>
    <?php endif; ?>

    <form action="login.php" method="post">
        <label for="email">E-Mail:</label><br>
        <input type="email" name="email" required><br>
        <label for="password">Password:</label><br>
        <input type="password" name="password" required><br><br>
        <input type="submit" name="login" value="Login">
    </form>

    <div><p>Not registered? <a href="register.php">Register Here</a></p></div>
</div>


</body>
</html>
