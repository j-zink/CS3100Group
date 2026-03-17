<?php
    require_once 'config.php';

    try {
        $pdo = new PDO("mysql:host=$host;dbname=$db", $user, $pass);
        
        $user_email = $_POST['email'];
        $user_password = $_POST['password'];

        $sql = "SELECT firstname, passwd FROM users WHERE email = ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$user_email]);
        $result = $stmt->fetch();

        if ($result && password_verify($user_password, $result['passwd'])) {
            session_start();
            $_SESSION['email'] = $user_email;
            $_SESSION['firstname'] = $result['firstname'];
            
            $firstname = $result['firstname'];
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
                    <h1>Welcome <?php echo htmlspecialchars($firstname); ?>, you've successfully logged in!</h1>
                </div>
            </body>
            </html>
            <?php
            exit;
        } else {
            echo "Invalid email or password!";
        }
    } 

    catch (PDOException $e) {
        echo "Connection failed: " . $e->getMessage();
    }
?>
