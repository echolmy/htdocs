<?php

// TODO: Extract $_POST variables, check they're OK, and attempt to login.
// Notify user of success/failure and redirect/give navigation options.

// For now, I will just set session variables and redirect.

session_start();

// Set error report
error_reporting(E_ALL);
ini_set('display_errors', 1);

try {
    $connection = new PDO('mysql:host=localhost;dbname=auction_system', 'root', '');
    $connection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // get table data
    $email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
    $password = $_POST['password'] ?? '';

    $errors = [];

    // validation
    if (empty($email)) {
        $errors[] = "Email is required";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Invalid email format";
    }
    
    if (empty($password)) {
        $errors[] = "Password is required";
    }

    // if pass validation, process further
    if (empty($errors)) {
        try {
            // Query user
            $stmt = $connection->prepare("
                SELECT u.userid, u.username, u.password, u.email,
                CASE 
                    WHEN b.userid IS NOT NULL THEN 'buyer'
                    WHEN s.userid IS NOT NULL THEN 'seller'
                END as account_type
                FROM user u
                LEFT JOIN buyer b ON u.userid = b.userid
                LEFT JOIN seller s ON u.userid = s.userid
                WHERE u.email = ?
            ");
            $stmt->execute([$email]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($user && password_verify($password, $user['password'])) {
                // login in sucessfully
                $_SESSION['logged_in'] = true;
                $_SESSION['userid'] = $user['userid'];
                $_SESSION['username'] = $user['username'];
                $_SESSION['email'] = $user['email'];
                $_SESSION['account_type'] = $user['account_type'];
                
                // redirect
                ?>
                <!DOCTYPE html>
                <html>
                <head>
                    <title>Login Success</title>
                    <link rel="stylesheet" href="css/bootstrap.min.css">
                </head>
                <body>
                    <div class="container mt-5">
                        <div class="alert alert-success text-center">
                            Login successful! Welcome back, <?php echo htmlspecialchars($user['username']); ?>!
                            <br>You will be redirected to the homepage in 3 seconds.
                        </div>
                    </div>
                </body>
                </html>
                <?php
                header("refresh:3;url=index.php");
                exit();
            }
            else{
                $errors[] = "Login in failed. Invalid email or password";
                showErrorPage($errors);
                exit();
            }
        } catch (PDOException $e) {
            $errors[] = "Login failed: " . $e->getMessage();
            showErrorPage($errors);
            exit();
        }
    }
    else{
        showErrorPage($errors);
        exit();
    }

}

// Not a POST request
else{
    header("Location: index.php");
    exit();
}

// echo('<div class="text-center">You are now logged in! You will be redirected shortly.</div>');

// // Redirect to index after 5 seconds
// header("refresh:5;url=index.php");
function showErrorPage($errors) {
    ?>
    <!DOCTYPE html>
    <html>
    <head>
        <title>Login Failed</title>
        <link rel="stylesheet" href="css/bootstrap.min.css">
    </head>
    <body>
        <div class="container mt-5">
            <div class="alert alert-danger">
                <h4>Login failed:</h4>
                <ul>
                    <?php foreach ($errors as $error): ?>
                        <li><?php echo htmlspecialchars($error); ?></li>
                    <?php endforeach; ?>
                </ul>
                <a href="javascript:history.back()" class="btn btn-primary">Go Back</a>
            </div>
        </div>
    </body>
    </html>
    <?php
}

?>