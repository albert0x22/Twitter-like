<?php 
include_once 'includes/db-connect.php';
include_once 'includes/functions.php';

sec_session_start();

if(isset($_SESSION['uid'])){
	$request = null;
	$editMode = null;
	$uid = preg_replace("/[^0-9]/", "", $_SESSION['uid']);
	if(isUserLoggedIn($uid,$conn)=="true") {
        if(isset($_GET['id']) & is_numeric($_GET['id'])) {
            $profile = preg_replace("/[^0-9]/", "", $_GET['id']);
			$requestedProfile = getUser($profile,$conn);
			if($requestedProfile == false) {
                //wrong id
				header('Location: ./profile.php?error=9');
			}
			//si le user visite un profil
			if($uid != $profile) {
				$request = 'visit';
				$relationDatas = getRelationDatas($uid, $profile, $conn);	
				$requestedProfileWall = getUserWall($profile, $conn);
                $friends = getFriendsList($profile, $conn);
                
                if(is_array($friends) && count($friends)>1){
                    $friendCount = count($friends);
                }elseif(is_string($friends)){
                    $friendCount = 0;
                }else {
                    $friendCount = 1;
        
                }
        
                if(!empty($requestedProfileWall[1]['content'])){
                    $postCount = count($requestedProfileWall);
                } elseif(is_string($requestedProfileWall)) {
                    $postCount = 0;
                } else {
                    $postCount = 1;
                }
			}
			//si le user veut editer son profil
			else {
				if($_GET['edit'] == 'true') {
					$editMode = 'true';
				} else {
					header('Location: ./home.php');
				}
			}
        } else {
            //wrong request
			header('Location: ./profile.php?error=14');
		}
		
    } else {
		header('Location: ./login.php');
    }
} else {
	header('Location: ./login.php');
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
</head>
<body>
    <header></header>
	<?php include_once 'templates/nav.php'; ?>
    <?php
        if (!empty($error)) {
            echo $error;
        }
    ?>
	<?php include_once 'templates/profileTemplate.php'; ?>  

</body>
</html>