<?php
include_once '../includes/db-connect.php';
include_once '../includes/functions.php';
 
sec_session_start(); // Our custom secure way of starting a PHP session.

if (isset($_POST['username'], $_POST['p'], $_POST['email'], $_FILES, $_POST['mode'])) {
    /* Getting file name */
    $location = uploadFile('file');

    $username = $_POST['username'];
    $password = $_POST['p']; 
    $email = $_POST['email'];
    $dob = $_POST['dob']; 
    $mode = $_POST['mode'];
    switch($mode) {
        case $mode == 'update':
            $update = update($location, $username, $password, $email, $dob, $conn);
            if($update ==  "0") {
                login($username, $password, $conn);
                header('Location: ../home.php?error=17');
            }else if($update == "3"){
                // register failed 
                header('Location: ../profile.php?error=3&id='.$_SESSION['uid']);
            }else if($update == "10"){
                // register failed 
                header('Location: ../profile.php?error=10&id='.$_SESSION['uid']);
            }else  if($update == "11"){
                // register failed 
                header('Location: ../profile.php?error=11&id='.$_SESSION['uid']);
            }else  if($update == "12"){
                // register failed 
                header('Location: ../profile.php?error=12&id='.$_SESSION['uid']);
            } else if($update == "13") {
                header('Location: ../profile.php?error=13&id='.$_SESSION['uid']);
            }
            break;
        case $mode == 'register':
            $result = register($location, $username, $password, $email, $dob, $conn);
            if ($result == "0") {
                // register success 
                header('Location: ../login.php');
            } else  if($result == "3"){
                // register failed 
                header('Location: ../index.php?error=3');
            } else  if($result == "4"){
                // register failed 
                header('Location: ../index.php?error=4');
            }else  if($result == "5"){
                // register failed 
                header('Location: ../index.php?error=5');
            }else  if($result == "10"){
                // register failed 
                header('Location: ../index.php?error=10');
            }else  if($result == "11"){
                // register failed 
                header('Location: ../index.php?error=11');
            }else  if($result == "12"){
                // register failed 
                header('Location: ../index.php?error=12');
            }
            break;
    }   

} else {
    // The correct POST variables were not sent to this page. 
    echo 'Invalid Request';
}