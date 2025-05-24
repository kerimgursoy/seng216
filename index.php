
 <!DOCTYPE html>
<html>
<head>
<title>Page Title</title>
<link rel="stylesheet" href="index.css">
</head>
<body>
<nav class="navbar">
    <div class="navbar-logo">
      <a href="index.php">MyApp</a>
    </div>
    <ul class="navbar-links">
      <li><a href="login.php">Login</a></li>
      <li><a href="register.php">Register</a></li>
    </ul>
  </nav>

<?php
include "db.php";

$sql = "SELECT * FROM users";
$result = $conn->query($sql);

while($row = $result->fetch_assoc()) {
    echo $row['username'] . "<br>";
}

$conn->close();
?>



<h1>My First Heading</h1>
<p>My first paragraph.</p>

</body>
<footer>
    <p>Footer</p>
</footer>
</html> 