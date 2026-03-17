<?php
    $host = 'localhost';
    $db   = 'cs3100_project_db';
    $user = 'cs3100user';
    $pass = 'cs3100pass';

    try {
        $pdo = new PDO("mysql:host=$host;dbname=$db", $user, $pass);
        
        $user_firstname = $_POST['firstname'];
        $user_lastname = $_POST['lastname'];
        $user_email = $_POST['email'];
        $user_password = password_hash($_POST['password'], PASSWORD_DEFAULT);

        $sql = "INSERT INTO users (firstname, lastname, email, passwd) VALUES (?, ?, ?, ?)";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$user_firstname, $user_lastname, $user_email, $user_password]);

        echo "Data saved successfully!";
    } 

    catch (PDOException $e) {
        echo "Connection failed: " . $e->getMessage();
    }
?>