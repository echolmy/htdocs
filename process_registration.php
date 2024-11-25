<?php

// TODO: Extract $_POST variables, check they're OK, and attempt to create
// an account. Notify user of success/failure and redirect/give navigation 
// options.

// Start a session for storing user information or error messages.
session_start();

// define('DB_HOST', 'localhost');     // or '127.0.0.1'
// define('DB_USER', 'root');      
// define('DB_PASS', '');      
// define('DB_NAME', 'auction_system'); // name of database

// connect mySQL
try {
    // connect database
    $connection = new PDO('mysql:host=localhost;dbname=auction_system', 'root', '');
    $connection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Connection failed. Please try again later.");
}


if($_SERVER["REQUEST_METHOD"] == "POST"){
    // get POST data
    $email = filter_input(INPUT_POST, 'email', FILTER_VALIDATE_EMAIL);
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';
    $password_confirmation = $_POST['passwordConfirmation'] ?? '';
    $account_type = $_POST['accountType'] ?? '';

    $errors = [];

    // validation
    // 1. email
    if (empty($email)) {
        $errors[] = "Email is required";
    } 
    elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Invalid email format";
    }
    else {
            // check email whether exists already
            $result = $connection->prepare("SELECT userid FROM user WHERE email = ?");
            $result->execute([$email]);
            if ($result->rowCount() > 0) {
                $errors[] = "Email already exists";
                $_SESSION['errors'] = $errors;
                header("Location: register.php");
                exit();
            }
    }

    // 2. username
    if (empty($username)) {
        $errors[] = "Username is required";
    }
    elseif (strlen($username) < 3 || strlen($username) > 20) {
        $errors[] = "Username must be between 3 and 20 characters";
    } 
    elseif (!preg_match('/^[a-zA-Z0-9_]+$/', $username)) {
        $errors[] = "Username can only contain letters, numbers and underscores";
    }
    else {
        // check username whether exists already
        $result = $connection->prepare("SELECT username FROM user WHERE username = ?");
        $result->execute([$username]);
        
        if ($result->num_rows > 0) {
            $errors[] = "Username already exists";
        }
    }

    // 3.password
    if (empty($password)) {
        $errors[] = "Password is required";
    } 
    // elseif (strlen($password) < 8) {
    //     $errors[] = "Password must be at least 8 characters long";
    // }    

    // 4. password_confirmation
    if (empty($password_confirmation)) {
        $errors[] = "Please repeat password again";
    } 
    if ($password_confirmation !== $password) {
        $errors[] = "Passwords do not match";
    }

    if (!in_array($account_type, ['buyer', 'seller'])) {
        $errors[] = "Invalid account type";
    }
    
    if (empty($errors)) {
        try {
            $connection->beginTransaction();

            // enhance password
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);

            // insert user primary table
            $result = $connection->prepare("INSERT INTO user (email, username, password, register_date) VALUES (?, ?, ?, NOW())");
            $result->execute([$email, $username, $hashed_password]);

            // get userid
            $userid = $connection->lastInsertId();

            // insert buyer table or seller table
            if ($account_type === 'buyer') {
                $sub_result = $connection->prepare("INSERT INTO buyer (userid) VALUES (?)");
                $sub_result->execute([$userid]);
            } 
            elseif ($account_type === 'seller') {
                $sub_result = $connection->prepare("INSERT INTO seller (userid) VALUES (?)");
                $sub_result->execute([$userid]);
            }
            
            $connection->commit();

            // set session
            $_SESSION['logged_in'] = true;
            $_SESSION['userid'] = $userid;
            $_SESSION['email'] = $email;
            $_SESSION['username'] = $username;
            $_SESSION['account_type'] = $account_type;
            

            header("Location: index.php");
            exit();
            
        } 
        catch (PDOException $e) {
            if ($connection->inTransaction()) {
                $connection->rollBack();
            }
            
            $errors[] = "Registration failed. Please try again later.";
            $_SESSION['errors'] = $errors;
            // file_put_contents("debug.txt", print_r($errors, true), FILE_APPEND);
            header("Location: register.php");
            exit();
        }
    } 
    else {
        // file_put_contents("debug.txt", print_r($errors, true), FILE_APPEND);
        // if validation failed, go back to register.php
        $_SESSION['errors'] = $errors;
        header("Location: register.php");
        exit();
    }
} else {
    // NOT a POST request
    header("Location: register.php");
    // file_put_contents("debug.txt", "not a POST request\n", FILE_APPEND);
    exit();
}

?>