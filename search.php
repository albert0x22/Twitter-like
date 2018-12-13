<?php

include_once './includes/db-connect.php';
include_once './includes/functions.php';
 
sec_session_start();

if(isset($_POST['search'])) {
    $input = $_POST['search'];
    if(isValidUsername($input)==1){
        //invalid request
        header('Location: home.php?error=14');
    }    
    $result = search($input, $conn);
    if($result == "9") {
        //no match
        header("Location: home.php?error=9");
    } else {

        header('Location: profile.php?id='.$result['uid']);
    }

}else {
    header('Location: home.php');
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.5.0/css/all.css" integrity="sha384-B4dIYHKNBt8Bc12p+WXckhzcICo0wtJAoU8YZTY5qE0Id1GSseTk6S+L3BlXeVIU"
        crossorigin="anonymous">
    <link rel="stylesheet" href="./assets/css/normalize.css">
    <link rel="stylesheet" href="./assets/css/style.css">

    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.3.1/jquery.min.js"></script>
    <script type="text/JavaScript" src="js/sha512.js"></script> 
    <script type="text/JavaScript" src="js/functions.js"></script>
    <title>search</title>
</head>
<body>
    <?php include_once "templates/nav.php"; ?>
    <div class="main-container">
        <a href=""></a>

    </div>
    
</body>
</html>





