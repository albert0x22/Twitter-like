<?php 

include_once '../includes/db-connect.php';
include_once '../includes/functions.php';

sec_session_start();

if(isset($_GET['action'], $_GET['id'], $_GET['profile'])) {
    if(is_numeric($_GET['id']) & is_numeric($_GET['profile'])) {
        $uid =  preg_replace("/[^0-9]/", "", $_GET['id']);
        $profile = preg_replace("/[^0-9]/", "", $_GET['profile']);
        $action = $_GET['action'];
        $datas = getRelationDatas($uid, $profile, $conn);
        
        if ($action == 'add') {
            addFriend($uid, $profile, $datas, $conn);
            header('Location: ../profile.php?id='.$profile);
        }elseif($action == 'accept'){
            acceptFriendRequest($uid, $profile, $conn);
            header('Location: ../profile.php?id='.$profile);
        }elseif($action == 'decline'){
            declineInvitation($uid, $profile, $conn);
            header('Location: ../profile.php?id='.$profile);            
        }elseif($action == 'block'){
            blockUser($uid, $profile, $conn);
            header('Location: ../profile.php?id='.$uid);
        }elseif($action == 'remove'){
            removeFriend($uid, $profile, $conn);
            header('Location: ../profile.php?id='.$profile);
        }elseif($action == 'deblock'){
            deblockUser($uid, $profile, $conn);
            header('Location: ../profile.php?id='.$profile);
        }elseif($action == 'cancel'){
            cancelInvitation($uid, $profile, $conn);
            header('Location: ../profile.php?id='.$profile);      
        }
    }
}


?>