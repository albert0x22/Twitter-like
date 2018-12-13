<?php
include_once '../includes/db-connect.php';
include_once '../includes/functions.php';

sec_session_start();

if(isset($_POST['authorId'], $_POST['content'], $_POST['wallId'])) {
    
    if (strlen( $_POST[ 'content']) >= 1 
        && strlen( $_POST[ 'content' ] ) <= 256
        && is_numeric($_POST['wallId']) ) 
        {
            if(!empty($_POST['img'])) {
                $img = $_POST['img'];
                $location = uploadFile($img);
            } else {
                $location = "";
            }
            if(!empty($_POST['video'])) {       
                $videoId = strip_tags(getYoutubeId($_POST['video']));                  
            } else {
                $videoId = "";
            }
            if($location != "" && !empty($videoId)) {
                echo json_encode(['result'=>'false', 'msg'=>'Do not try to post a photo AND a video in the same post.']);
                die();
            }
            $wall = preg_replace("/[^0-9]/", "", $_POST['wallId']);
            $content = htmlentities (trim ($_POST['content']), ENT_COMPAT);
            $authorId = preg_replace("/[^0-9]/", "", $_POST['authorId']);
            $result = addPost($wall, $content, $authorId, $location, $videoId, $conn);
            if($result['error'] == '0') {
                echo json_encode(['result'=>'true']);
            }
        } else {
            echo json_encode(['result'=>'false']);
        }
}
?>