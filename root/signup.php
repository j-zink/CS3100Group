<?php
<<<<<<< HEAD
require_once 'config.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: signup.html");
    exit;
}
=======
    require_once 'config.php';
    session_start();

    try {
        $pdo = new PDO("mysql:host=$host;dbname=$db;charset=utf8mb4", $user, $pass); // Add charset to prevent from injection attacks
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION); // Will throw a more specific error message to the catch (PDOException $e)

        $user_firstname = $_POST['firstname'];
        $user_lastname = $_POST['lastname'];
        $user_email = $_POST['email'];
        $user_password = $_POST['password'];
        $user_confirm = $_POST['confirm'];
>>>>>>> ded04a9f9859d496e37376c6060eb4d2178600f6

$user_firstname = trim($_POST['firstname'] ?? '');
$user_lastname  = trim($_POST['lastname'] ?? '');
$user_email     = trim($_POST['email'] ?? '');
$user_password  = $_POST['password'] ?? '';
$user_confirm   = $_POST['confirm'] ?? '';

// Basic validation
if ($user_firstname === '') {
    header("Location: signup.html?error=missing_firstname&field=firstname");
    exit;
}

if ($user_lastname === '') {
    header("Location: signup.html?error=missing_lastname&field=lastname");
    exit;
}

if ($user_email === '') {
    header("Location: signup.html?error=missing_email&field=email");
    exit;
}

if (!filter_var($user_email, FILTER_VALIDATE_EMAIL)) {
    header("Location: signup.html?error=invalid_email&field=email");
    exit;
}

if ($user_password === '') {
    header("Location: signup.html?error=missing_password&field=password");
    exit;
}

if ($user_confirm === '') {
    header("Location: signup.html?error=missing_confirm&field=confirm");
    exit;
}

if ($user_password !== $user_confirm) {
    header("Location: signup.html?error=password_mismatch&field=confirm");
    exit;
}

try {
    $pdo = new PDO(
        "mysql:host=$host;dbname=$db;charset=utf8mb4",
        $user,
        $pass,
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );

    $hashed_password = password_hash($user_password, PASSWORD_DEFAULT);

    $sql = "INSERT INTO users (firstname, lastname, email, passwd) VALUES (?, ?, ?, ?)";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        $user_firstname,
        $user_lastname,
        $user_email,
        $hashed_password
    ]);

    header("Location: index.html?signup=success");
    exit;
} catch (PDOException $e) {
    // 23000 = integrity constraint violation, commonly duplicate unique value
    if ($e->getCode() == 23000) {
        header("Location: signup.html?error=email_taken&field=email");
        exit;
    }

    error_log("Signup error: " . $e->getMessage());
    header("Location: signup.html?error=server_error");
    exit;
}
?>