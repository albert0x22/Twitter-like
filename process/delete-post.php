<?php 
include_once '../includes/db-connect.php';
include_once '../includes/functions.php';

sec_session_start();

if($_POST) {
    if(is_numeric($_POST['postId'])) {
        try{
            $id = $_POST['postId'];
            $stmt = $conn->prepare("DELETE FROM posts WHERE id=?");
            $stmt->execute([$id]);
        }catch(PDOException $exception){ 
            logme("N/A",time(),"PDOException","DELETE FROM posts WHERE id=?","Error", $exception, "n/a");
        }
        echo json_encode(["result"=>"true", "msg"=>"Post deleted."]);
    } else {
        echo json_encode(["result"=>"false", "msg"=>"Wrong request."]);
    }

}else {    
    echo json_encode(["result"=>"false", "msg"=>"Wrong request."]);
}