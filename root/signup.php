<?php
require_once '../config.php';
session_start();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: signup.html");
    exit;
}

$user_firstname = trim($_POST['firstname'] ?? '');
$user_lastname  = trim($_POST['lastname'] ?? '');
$user_email     = trim($_POST['email'] ?? '');
$user_password  = $_POST['password'] ?? '';
$user_confirm   = $_POST['confirm'] ?? '';

function redirect_with_error($error, $field, $firstname, $lastname, $email) {
    $query = http_build_query([
        'error' => $error,
        'field' => $field,
        'firstname' => $firstname,
        'lastname' => $lastname,
        'email' => $email
    ]);

    header("Location: signup.html?$query");
    exit;
}

// Basic validation
if ($user_firstname === '') {
    header("Location: signup.html?error=missing_firstname&field=firstname");
    exit;
}

if (strlen($user_firstname) > 20) {
    header("Location: signup.html?error=long_firstname&field=firstname");
    exit;
}

if ($user_lastname === '') {
    header("Location: signup.html?error=missing_lastname&field=lastname");
    exit;
}

if (strlen($user_lastname) > 20) {
    header("Location: signup.html?error=long_lastname&field=lastname");
    exit;
}

if ($user_email === '') {
    header("Location: signup.html?error=missing_email&field=email");
    exit;
}

if (strlen($user_email) > 100) {
    header("Location: signup.html?error=long_email&field=email");
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

if (strlen($user_password) > 256) {
    header("Location: signup.html?error=long_password&field=password");
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
    if ($e->getCode() == 23000) {
        redirect_with_error('email_taken', 'email', $user_firstname, $user_lastname, $user_email);
    }

    error_log("Signup error: " . $e->getMessage());
    redirect_with_error('server_error', '', $user_firstname, $user_lastname, $user_email);
}
?>