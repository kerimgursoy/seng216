<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8" />
    <link rel="stylesheet" href="loginregister.css">
    <title>Login</title>
</head>
<body>
<nav class="navbar-left">
    <div class="navbar-logo">
      <a href="index.php">MyApp</a>
    </div>
</nav>

<div class="loginregister">
<h2>Login</h2>

<form id="loginForm">
  <label for="email">E-Mail:</label><br>
  <input type="email" id="email" name="email" required><br>
  <label for="password">Password:</label><br>
  <input type="password" id="password" name="password" required><br><br>
  <input type="submit" value="Login">
</form> 

<div id="responseMessage" style="margin-top:10px; color: red;"></div>
</div>

<script>
document.getElementById("loginForm").addEventListener("submit", function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);

    fetch("action_login.php", {
        method: "POST",
        body: formData
    })
    .then(res => res.text())
    .then(data => {
        if (data.trim() === "success") {
            window.location.href = "index.php";
        } else {
            document.getElementById("responseMessage").innerText = data;
        }
    });
});
</script>
</body>
</html>
