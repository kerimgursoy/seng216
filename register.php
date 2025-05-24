<?php
session_start();
if (isset($_SESSION["user"])) {
   header("Location: index.php");
}
?>
<!DOCTYPE html>
<html>
<head>
    <link rel="stylesheet" href="loginregister.css">
</head>
<body>
<nav class="navbar-left">
    <div class="navbar-logo">
      <a href="index.php">MyApp</a>
    </div>
</nav>
<div class="loginregister">
    <?php
        if (isset($_POST["submit"])) {
           $usrname = $_POST["usrname"];
           $email = $_POST["email"];
           $password = $_POST["password"];
           $passwordRepeat = $_POST["repeat_password"];
           
           $passwordHash = password_hash($password, PASSWORD_DEFAULT);

           $errors = array();
           
           if (empty($usrname) OR empty($email) OR empty($password) OR empty($passwordRepeat)) {
            array_push($errors,"All fields are required");
           }
           if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            array_push($errors, "Email is not valid");
           }
           if (strlen($password)<8) {
            array_push($errors,"Password must be at least 8 charactes long");
           }
           if ($password!==$passwordRepeat) {
            array_push($errors,"Password does not match");
           }
           require_once "db.php";
           $sql = "SELECT * FROM users WHERE email = '$email'";
           $result = mysqli_query($conn, $sql);
           $rowCount = mysqli_num_rows($result);
           if ($rowCount>0) {
            array_push($errors,"Email already exists!");
           }
           if (count($errors)>0) {
            foreach ($errors as  $error) {
                echo "<div class='alert alert-danger'>$error</div>";
            }
           }else{
            
            $sql = "INSERT INTO users (usrname, email, password) VALUES ( ?, ?, ? )";
            $stmt = mysqli_stmt_init($conn);
            $prepareStmt = mysqli_stmt_prepare($stmt,$sql);
            if ($prepareStmt) {
                mysqli_stmt_bind_param($stmt,"sss",$usrname, $email, $passwordHash);
                mysqli_stmt_execute($stmt);
                echo "<div class='alert alert-success'>You are registered successfully.</div>";
                header("Location: index.php");
                die();
            }else{
                die("Something went wrong");
            }
           }
        }
        ?>
    <h2>Register</h2>

    <form action="register.php" method="post">
        <label for="usrname">Name:</label><br>
        <input type="text" id="usrname" name="usrname"><br>
        <label for="email">E-Mail:</label><br>
        <input type="email" id="email" name="email"><br>
        <label for="password">Password:</label><br>
        <input type="password" id="password" name="password"><br>
        <label for="repeat-password">Repeat Password:</label><br>
        <input type="password" id="repeat_password" name="repeat_password"><br><br>
        <input type="submit" name="submit" value="Register">
    </form> 
    <div><p>Already Registered <a href="login.php">Login Here</a></p></div>
</div>

</body>
</html>

