<?php
require_once '../config.php';
session_start();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: index.html");
    exit;
}

$user_email = trim($_POST['email'] ?? '');
$user_password = $_POST['password'] ?? '';

function redirect_with_error($error, $field, $email = '') {
    $url = "index.html?error=" . urlencode($error)
         . "&field=" . urlencode($field)
         . "&email=" . urlencode($email);

    header("Location: $url");
    exit;
}

if ($user_email === '') {
    redirect_with_error('missing_email', 'email', $user_email);
}

if (!filter_var($user_email, FILTER_VALIDATE_EMAIL)) {
    redirect_with_error('invalid_email', 'email', $user_email);
}

if ($user_password === '') {
    redirect_with_error('missing_password', 'password', $user_email);
}

try {
    $pdo = new PDO(
        "mysql:host=$host;dbname=$db;charset=utf8mb4",
        $user,
        $pass,
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );

    $ip_address = $_SERVER['REMOTE_ADDR'];

    $time_limit = 5;
    $max_attempts = 5;

    $stmt = $pdo->prepare("
        SELECT COUNT(*)
        FROM login_attempts
        WHERE ip_address = ?
          AND attempt_time > (NOW() - INTERVAL ? MINUTE)
          AND success = 0
    ");
    $stmt->execute([$ip_address, $time_limit]);
    $failed_attempts = $stmt->fetchColumn();

    if ($failed_attempts >= $max_attempts) {
        redirect_with_error('too_many_attempts', 'password', $user_email);
    }

    $sql = "SELECT firstname, passwd FROM users WHERE email = ?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$user_email]);
    $result = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($result && password_verify($user_password, $result['passwd'])) {
        $log_stmt = $pdo->prepare("
            INSERT INTO login_attempts (ip_address, email_attempted, success)
            VALUES (?, ?, 1)
        ");
        $log_stmt->execute([$ip_address, $user_email]);

        session_regenerate_id(true);
        $_SESSION['email'] = $user_email;
        $_SESSION['firstname'] = $result['firstname'];

        header("Location: welcome.php");
        exit;
    } else {
        $log_stmt = $pdo->prepare("
            INSERT INTO login_attempts (ip_address, email_attempted, success)
            VALUES (?, ?, 0)
        ");
        $log_stmt->execute([$ip_address, $user_email]);

        redirect_with_error('invalid_login', 'password', $user_email);
    }
} catch (PDOException $e) {
    error_log("Login error: " . $e->getMessage());
    redirect_with_error('server_error', '', $user_email);
}
?>