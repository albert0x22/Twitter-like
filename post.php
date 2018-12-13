<?php
include_once 'includes/db-connect.php';
include_once 'includes/functions.php';

sec_session_start();

if(isset($_SESSION['uid'])){
    $uid = preg_replace("/[^0-9]/", "", $_SESSION['uid']); //XSS Security
	if(isUserLoggedIn($uid,$conn)=="false") {
        header('Location: ./index.php');
    }
} 

if(isset($_GET['postId']) & is_numeric($_GET['postId'])) {
    $postId = preg_replace("/[^0-9]/", "", $_GET['postId']);
    $post = getPost($postId, $conn); 
}else {
    header('Location: home.php');
}

$error=null;
if(isset($_GET["error"])){
	if(!is_numeric($_GET["error"])){
		$error="Do not edit the URL GET var, thanks.";
	}else{
		$error = getError($_GET["error"]);
	}
}
?>
<html>

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
</head>
<body>
    <?php include 'templates/nav.php'; ?>
    <div class="main-container">
        <?php echo displayPosts($post, $conn); ?>
    </div>
</body>
</html>