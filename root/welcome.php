<?php
session_start();

if (!isset($_SESSION['email']) || !isset($_SESSION['firstname'])) {
    header("Location: index.html");
    exit;
}

$firstname = $_SESSION['firstname'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <link rel="stylesheet" href="mainstyle.css">
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Welcome</title>
</head>
<body>
    <div class="slogin-container">
        <h1>Welcome, <?php echo htmlspecialchars($firstname); ?>!</h1>
        <p>You have successfully logged in.</p>

        <div class="welcome-actions">
            <a href="logout.php" class="logout-link">Log Out</a>
        </div>
    </div>
</body>
</html>