<?php
    require_once '../config.php'; // Made this ../config.php because it is now one level above the root 
    session_start(); // Start a new session at the beginning of each login

    try {
        $pdo = new PDO("mysql:host=$host;dbname=$db;charset=utf8mb4", $user, $pass); // Add charset to prevent from injection attacks
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION); // Will throw a more specific error message to the catch (PDOException $e)

        $user_email = $_POST['email'];
        $user_password = $_POST['password'];
        
        // Get the user's IP address
        $ip_address = $_SERVER['REMOTE_ADDR'];

        // --- RATE LIMITING CHECK ---
        $time_limit = 15; // Lockout time in minutes
        $max_attempts = 5; // Maximum allowed failed attempts

        // Count failed attempts from this IP within the time limit
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM login_attempts WHERE ip_address = ? AND attempt_time > (NOW() - INTERVAL ? MINUTE) AND success = 0");
        $stmt->execute([$ip_address, $time_limit]);
        $failed_attempts = $stmt->fetchColumn();

        if ($failed_attempts >= $max_attempts) {
            // Block the user if they've exceeded the limit
            die("Too many failed login attempts. Please wait 15 minutes and try again.");
        }
        // ---------------------------

        $sql = "SELECT firstname, passwd FROM users WHERE email = ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$user_email]);
        $result = $stmt->fetch();

        if ($result && password_verify($user_password, $result['passwd'])) {
            
            // --- LOG SUCCESSFUL ATTEMPT ---
            $log_stmt = $pdo->prepare("INSERT INTO login_attempts (ip_address, email_attempted, success) VALUES (?, ?, 1)");
            $log_stmt->execute([$ip_address, $user_email]);
            
            session_regenerate_id(true); // Generate a new session id after each successful login so an attacker cannot utilize the previous one
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
            // --- LOG FAILED ATTEMPT ---
            $log_stmt = $pdo->prepare("INSERT INTO login_attempts (ip_address, email_attempted, success) VALUES (?, ?, 0)");
            $log_stmt->execute([$ip_address, $user_email]);
            
            echo "Invalid email or password!";
        }
    } 

    catch (PDOException $e) {
        echo "Connection failed: " . $e->getMessage();
    }
?>
