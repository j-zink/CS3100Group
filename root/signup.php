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
    $url = "signup.html?error=" . urlencode($error)
         . "&field=" . urlencode($field)
         . "&firstname=" . urlencode($firstname)
         . "&lastname=" . urlencode($lastname)
         . "&email=" . urlencode($email);

    header("Location: $url");
    exit;
}

// Basic validation
if ($user_firstname === '') {
    redirect_with_error('missing_firstname', 'firstname', $user_firstname, $user_lastname, $user_email);
}

if (strlen($user_firstname) > 20) {
    redirect_with_error('long_firstname', 'firstname', $user_firstname, $user_lastname, $user_email);
}

if ($user_lastname === '') {
    redirect_with_error('missing_lastname', 'lastname', $user_firstname, $user_lastname, $user_email);
}

if (strlen($user_lastname) > 20) {
    redirect_with_error('long_lastname', 'lastname', $user_firstname, $user_lastname, $user_email);
}

if ($user_email === '') {
    redirect_with_error('missing_email', 'email', $user_firstname, $user_lastname, $user_email);
}

if (strlen($user_email) > 100) {
    redirect_with_error('long_email', 'email', $user_firstname, $user_lastname, $user_email);
}

if (!filter_var($user_email, FILTER_VALIDATE_EMAIL)) {
    redirect_with_error('invalid_email', 'email', $user_firstname, $user_lastname, $user_email);
}

if ($user_password === '') {
    redirect_with_error('missing_password', 'password', $user_firstname, $user_lastname, $user_email);
}

if (strlen($user_password) > 256) {
    redirect_with_error('long_password', 'password', $user_firstname, $user_lastname, $user_email);
}

if ($user_confirm === '') {
    redirect_with_error('missing_confirm', 'confirm', $user_firstname, $user_lastname, $user_email);
}

if ($user_password !== $user_confirm) {
    redirect_with_error('password_mismatch', 'confirm', $user_firstname, $user_lastname, $user_email);
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