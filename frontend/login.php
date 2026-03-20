
<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
?><?php


if($_SERVER["REQUEST_METHOD"] == "POST"){
    $username = $_POST['username'];
    $password = $_POST['password'];

    if($username === "admin" && password_hash() / password_verify()){
        $_SESSION['user'] = $username;
        header("Location: dashboard.php");
        exit();
    } else {
        $error = "Invalid Username or Password";
    }
}
?>

<!DOCTYPE html>
<html>
<head>
<link rel="stylesheet" href="styles.css">
</head>

<body>

<div class="container">
<div class="glass">

<h2>🔐 Login</h2>

<?php if(isset($error)) echo "<p style='color:red;'>$error</p>"; ?>

<form method="POST">
    <input name="username" placeholder="Username" required>
    <input type="password" name="password" placeholder="Password" required>
    <button>Login</button>
</form>

</div>
</div>
<link rel="stylesheet" href="style.css">

</body>
</html>