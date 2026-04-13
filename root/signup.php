<?php
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

        if ($user_password !== $user_confirm) {
            echo "Error: Passwords do not match!";
            exit;
        }

        // hashes the password using bcrypt
        $user_password = password_hash($user_password, PASSWORD_DEFAULT);

        $sql = "INSERT INTO users (firstname, lastname, email, passwd) VALUES (?, ?, ?, ?)";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$user_firstname, $user_lastname, $user_email, $user_password]);

        echo "Data saved successfully!";
    } 

    catch (PDOException $e) {
        echo "Connection failed: " . $e->getMessage();
    }
?>