<?php 

include_once '../includes/db-connect.php';
include_once '../includes/functions.php';
 
sec_session_start(); // Our custom secure way of starting a PHP session.
 
if (isset($_POST['username'], $_POST['p'])) {
    $password = $_POST['p']; // The hashed password.
    $username = preg_replace("/[^a-zA-Z0-9_\-]+/", "", $_POST['username']); //XSS Security   

    $login=login($username, $password, $conn);

    if ($login == "0") {
        // Login success 
        header('Location: ../home.php');
    } else  if ($login == "1") {
        // Invalid pwd
        header('Location: ../login.php?error=1&username='.$username);
    }else {
        // Locked out
        header('Location: ../login.php?error=2&username='.$username);
    }
} else {
    // The correct POST variables were not sent to this page. 
    echo 'Invalid request.';
}