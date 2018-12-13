<?php
include_once '../includes/db-connect.php';
include_once '../includes/functions.php';

sec_session_start();
if(isset($_POST['authorId'], $_POST['postId'], $_POST['content'])) {
    if (strlen( $_POST[ 'content']) >= 1 && strlen( $_POST[ 'content' ] ) <= 256 ) 
        {
            $content = htmlentities (trim ($_POST['content']), ENT_COMPAT);
            $authorId = preg_replace("/[^0-9]/", "", $_POST['authorId']);
            $postId = preg_replace("/[^0-9]/", "", $_POST['postId']);
            $result = addComment($postId, $content, $authorId, $conn);
            if($result == '0') {
                echo json_encode(["result"=>"true"]);
            }
        } else {
            header('Location: ../post.php?error=18&postId='.$postId);
        }
}
?>