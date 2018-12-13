<?php

expireOutdatedTokensCronJob($conn);

function sec_session_start() {
    define("SECURE", FALSE); 
    $session_name = 'sec_session_id';   // Set a custom session name 
    $secure = SECURE;
    // This stops JavaScript being able to access the session id.
    $httponly = true;
    // Forces sessions to only use cookies.
    if (ini_set('session.use_only_cookies', 1) === FALSE) {
        header("Location: ./error.php?err=Could not initiate a safe session (ini_set)");
        exit();
    }
    // Gets current cookies params.
    $cookieParams = session_get_cookie_params();
    session_set_cookie_params($cookieParams["lifetime"], $cookieParams["path"], $cookieParams["domain"], $secure, $httponly);
    // Sets the session name to the one set above.
    session_name($session_name);
    session_start();            // Start the PHP session 
    session_regenerate_id();    // regenerated the session, delete the old one. 
}

function register($location, $username, $password, $email, $dob, $conn){
    $error=false;
    if(isValidUsername($username)==1){
        $error=true;
        return "3";
    }
    if((!filter_var($email, FILTER_VALIDATE_EMAIL))){
        $error=true;
        return "10";
    }
    if(isValidDateOfBirth($dob)==0){
        $error=true;
        return "11";
    }
    $loc = encrypt_decrypt('encrypt', $location);
    $username_c = encrypt_decrypt('encrypt', $username);    
    $pwd = password_hash($password, PASSWORD_BCRYPT);
    $mail = encrypt_decrypt('encrypt', $email);
    $birth = encrypt_decrypt('encrypt', $dob);
    var_dump($loc);
    //Check that username is not already in use, if it is return an error.
    try{
        $stmt = $conn->prepare("SELECT username FROM users WHERE username=?");
        $stmt->execute([$username_c]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
    }catch(PDOException $exception){ 
        logme('n/a',time(),"PDOException","SELECT username FROM users WHERE username=:username","Error", $exception, "n/a");
    }

    if ($stmt->rowCount() > 0) {
        $error=true;
        return "12";
    }

    //Check that email is not already in use, if it is return an error.
    try{
        $stmt = $conn->prepare("SELECT username FROM users WHERE email=?");
        $stmt->execute([$mail]);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
    }catch(PDOException $exception){ 
        logme('n/a',time(),"PDOException","SELECT username FROM users WHERE email=:email","Error", $exception, "n/a");
    }

    if ($stmt->rowCount() > 0) {
        $error=true;
        return "13";
    }

    //If no errors, continue with registration 
    if($error==false){        
        // prepare sql and bind parameters
        try{            
            $stmt = $conn->prepare("INSERT INTO users (username, password, email, dob, profile_picture)
            VALUES (?, ?, ?, ?, ?)");
            $stmt->execute([$username_c, $pwd, $mail, $birth, $loc]); 
        }catch(PDOException $exception){ 
            logme('n/a',time(),"INSERT INTO users (username, password, email, dob, profile_picture)
            VALUES (?, ?, ?, ?, ?)","Error", $exception, "n/a");
        }
        return "0";
    }
}   

function update($location, $username, $password, $email, $dob, $conn) {
    $error = false;
    if(isValidUsername($username)==1){
        $error=true;
        return "3";
    }
    if((!filter_var($email, FILTER_VALIDATE_EMAIL))){
        $error=true;
        return "10";
    }
    if(isValidDateOfBirth($dob)==0){
        $error=true;
        return "11";
    }
    $loc = encrypt_decrypt('encrypt', $location);
    $username_c = encrypt_decrypt('encrypt', $username);    
    $pwd = password_hash($password, PASSWORD_BCRYPT);
    $mail = encrypt_decrypt('encrypt', $email);
    $birth = encrypt_decrypt('encrypt', $dob);
    $userId = $_SESSION['uid'];
    $user = getUser($userId, $conn);
    //check if user updated his email or username
    //if so we check if it's available
    if($username_c != $user['username']) {
        try{
            $stmt = $conn->prepare("SELECT username FROM users WHERE username=?");
            $stmt->execute([$username_c]);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
        }catch(PDOException $exception){ 
            logme($userId,time(),"SELECT username FROM users WHERE username=?","Error", $exception, "n/a");
            }
        if ($stmt->rowCount() > 0) {
            $error=true;
            return "12";
        }
    }

    if($mail != $user['email']) {
        //Check that email is not already in use, if it is return an error.
        try{
            $stmt = $conn->prepare("SELECT username FROM users WHERE email=?");
            $stmt->execute([$mail]);
            $stmt->execute();
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
        }catch(PDOException $exception){             
            logme($userId,time(),"SELECT username FROM users WHERE email=?","Error", $exception, "n/a");
            }
    
        if ($stmt->rowCount() > 0) {
            $error=true;
            return "13";
        }
    }

    if($error==false){     
        // prepare sql and bind parameters
        try{            
            $stmt = $conn->prepare("UPDATE users SET username = ?, password = ?, email = ?, dob = ?, profile_picture = ? WHERE uid = ?");
            $stmt->execute([$username_c, $pwd, $mail, $birth, $loc, $userId]); 
        }catch(PDOException $exception){ 
            logme($userId,time(),"UPDATE users SET username = ?, password = ?, email = ?, dob = ?, profile_picture = ? WHERE uid = ?","Error", $exception, "n/a");            
        }
        return "0";
    }

}

function uploadFile($file) {
    $filename = $file;
    $location = "upload/".$filename;
    $uploadOk = 1;
    $imageFileType = pathinfo($location,PATHINFO_EXTENSION);

    // Check image format
    if($imageFileType != "jpg" && $imageFileType != "png" && $imageFileType != "jpeg"
     && $imageFileType != "gif" ) {
     $uploadOk = 0;
    }

    if($uploadOk == 0){
        $location = "";
    }else{
        move_uploaded_file($filename,"../".$location);
    }
    return $location;
}


function isValidUsername($username) {
    return preg_match("/[^a-zA-Z0-9 ]+/", $username);
}

function isValidDateOfBirth($dob){
    return preg_match("/^(\d{2})-(\d{2})-(\d{4})$/", $dob);
}

function login($username, $password, $conn) {
    $username = preg_replace("/[^a-zA-Z0-9_\-]+/", "", $username); //XSS Security
    $username_c = encrypt_decrypt('encrypt', $username);

    try{
        $stmt = $conn->prepare("SELECT * FROM users WHERE username=?");
        $stmt->execute([$username_c]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
    }catch(PDOException $exception){ 
        logme($username,time(),"SELECT * FROM users WHERE username=username_c","Error", $exception, "n/a");            

    }


    if ($stmt->rowCount() > 0) {
         if(checkIfLockedOut($result['uid'],$conn)==0){
       
            $hash=$result['password'];
    
            if (password_verify($password, $hash)) {
                                            
                $user_browser = $_SERVER['HTTP_USER_AGENT'];
                $_SESSION['uid']=$result['uid'];
                $_SESSION['login_string']=hash('sha256', $hash . $user_browser);
                $_SESSION['username'] =$result['username'];
                $_SESSION['location'] = $result['profile_picture'];

                return "0";

            }else{
                invalidLoginAttempt($result['uid'], $conn);
                return "1"; //Invalid Password
            }
        } else {
            invalidLoginAttempt($result['uid'], $conn);
            return "2"; //Locked out
        }
    } else {
        return "1"; //Username not found
    }
}

function addPost($wall, $content, $authorId, $location, $videoId, $conn) {
    $timestamp = date('Y-m-d H:i:s');
    if($location == "" && $videoId != "") {
        $reg_exUrl = '/(http|https|ftp|ftps)\:\/\/[a-zA-Z0-9\-\.]+\.[a-zA-Z]{2,3}(\/\S*)?/';
        $text = "http://youtube.com/embded/";
        $id = $videoId;
        $media = (preg_replace($reg_exUrl, "<div class='media-content'><iframe src=https://youtube.com/embed/".$id."></iframe></div> ", $text));
    } elseif($videoId == "" && $location == "") {
        $media = "";
    } else {
        $media = "<div class='media-content'><img src='".$location."'></img></div>";
    }
    try{
        $stmt = $conn->prepare("INSERT INTO posts (wall_id, content, Author_Id, createdAt, media) VALUES (?, ?, ?, ?, ?)");
        $stmt->execute([$wall, $content, $authorId, $timestamp, $media]);
    }catch(PDOException $exception){ 
        logme($authorId,time(),"INSERT INTO posts (wall_id, content, Author_Id, createdAt, media) VALUES (?, ?, ?, ?, ?)","Error", $exception, "n/a");            
    }
    $postId = getPostId($authorId, $timestamp, $conn);
    $result = ['error'=>'0', 'postId'=>$postId];
    return $result;
}

function addComment($postId, $content, $authorId, $conn) {
    try{
        $stmt = $conn->prepare("INSERT INTO comments (content, author_id, post_id) VALUES (?, ?, ?)");
        $stmt->execute([$content, $authorId, $postId]);
    }catch(PDOException $exception){ 
        logme($authorId,time(),"INSERT INTO comments (content, author_id, post_id) VALUES (?, ?, ?)","Error", $exception, "n/a");            
    }
    return "0";
}

function getPostId($authorId, $time, $conn) {
    try{
        $stmt = $conn->prepare("SELECT id FROM posts WHERE createdAt = ? AND Author_Id = ?");
        $stmt->execute([$time, $authorId]);
        $result=$stmt->fetch(PDO::FETCH_ASSOC);
    }catch(PDOException $exception) {
        logme($_SESSION['uid'],time(),"SELECT id FROM posts WHERE createdAt = ? AND Author_Id = ?","Error", $exception, "n/a");            

    }
        
    if ($stmt->rowCount() > 0) {
        return $result['id'];
    } else {
        return "error";
    }
}


function invalidLoginAttempt($uid, $conn) {
    // prepare sql and bind parameters
    try{
        $stmt = $conn->prepare("INSERT INTO login_attempts (uid, time) VALUES (?, ?)");
        $stmt->execute([$uid, time()]);
    }catch(PDOException $exception){        
        logme($uid,time(),"INSERT INTO login_attempts (uid, time) VALUES (?, ?)","Error", $exception, "n/a");            

    }
}

function checkIfLockedOut($uid, $conn) {
    try{
        $stmt = $conn->prepare("SELECT time FROM login_attempts WHERE uid=? ORDER BY time DESC LIMIT 5");
        $stmt->execute([$uid]);
        $result = $stmt->fetchAll();
    }catch(PDOException $exception){         
        logme($uid,time(),"SELECT time FROM login_attempts WHERE uid=? ORDER BY time DESC LIMIT 5","Error", $exception, "n/a");            

    }
    
 
    if($result[4][0]!=""){
    $answer=$result[0][0]-$result[4][0];
 
    if($answer < 300){
        return 1;
    }else{
        return 0;
    }
}else{
    return 0;
}
} 

function getError($errorNum){
    $error="";
    if(isset($_GET["error"])){
        if($_GET["error"]=="1"){
            $username = preg_replace("/[^a-zA-Z0-9_\-]+/", "", $_GET['username']); //XSS Security
            $error='<div class="col-lg-4"><div class="alert alert-warning">The username <strong>'.$username.'</strong> & password combination cannot be authenticated at the moment. </strong></div></div>';
        }
        if($_GET["error"]=="2"){
            $username = preg_replace("/[^a-zA-Z0-9_\-]+/", "", $_GET['username']); //XSS Security
            $error='<div class="col-lg-4"><div class="alert alert-danger"><strong>The username <strong>'.$username.'</strong> has been locked out for too many failed login attempts! Please try again later.. </strong></div></div>';
        }
        if($_GET["error"]=="3"){
            $error='<div class="col-lg-4"><div class="alert alert-warning"><strong>The username you entered is invalid. Please use alphanumerical charaters only. </strong></div></div>';
        }
        if($_GET["error"]=="4"){
            $error='<div class="col-lg-4"><div class="alert alert-warning">Your passwords did not match.</div></div>';
        }
        if($_GET["error"]=="5"){
            $error='<div class="col-lg-4"><div class="alert alert-warning">Your passwords must meet the following criteria: </br></br>           
                    - Must be a minimum of 8 characters</br> 
                    -  Must contain at least 1 number</br> 
                    - Must contain at least one uppercase character</br> 
                    - Must contain at least one lowercase character</br></div></div>';
        }
        if($_GET["error"]=="6"){
            $error='<div class="col-lg-4"><div class="alert alert-warning">Your passwords did not match.</div></div>';
        }
        if($_GET["error"]=="7"){
            $error='<div class="col-lg-4"><div class="alert alert-warning">Your username was not found. Do not edit sessions.</div></div>';
        }
        if($_GET["error"]=="8"){
            $error='<div class="col-lg-4"><div class="alert alert-warning">Email entered did not match records.</div></div>';
        }
        if($_GET["error"]=="9"){
            $error='<div class="col-lg-4"><div class="alert alert-warning">User not found. </div></div>';
        }
        if($_GET["error"]=="10"){
            $error='<div class="col-lg-4"><div class="alert alert-warning">You did not eneter a valid email.</div></div>';
        }
        if($_GET["error"]=="11"){
            $error='<div class="col-lg-4"><div class="alert alert-warning">You need to enter your date of birth in the format DD-MM-YYYY.</div></div>';
        }
        if($_GET["error"]=="12"){
            $error='<div class="col-lg-4"><div class="alert alert-warning">Sorry, that username is already in use.</div></div>';
        }
        if($_GET["error"]=="13"){
            $error='<div class="col-lg-4"><div class="alert alert-warning">Sorry, that email is already in use.</div></div>';
        }
        if($_GET["error"]=="14"){
            $error='<div class="col-lg-4"><div class="alert alert-warning">Invalid request.</div></div>';
        }
        if($_GET["error"]=="15"){
            $error='<div class="col-lg-4"><div class="alert alert-warning">Sorry, the date of birth you entered did not match our records.</div></div>';
        }
        if($_GET["error"]=="16"){
            $error='<div class="col-lg-4"><div class="alert alert-warning">Sorry, that password contained your username, this is not allowed.</div></div>';
        }
        if($_GET["error"]=="17"){
            $error='<div class="col-lg-4"><div class="alert alert-success">Changed profile informations succesfully.</div></div>';
        }
        if($_GET["error"]=="18"){
            $error='<div class="col-lg-4"><div class="alert alert-warning">You must provide a content.<br>
                                                                            -content: 1-256 characters<br>
                                                                            (no tag allowed)</div></div>';
        }
        if($_GET["error"]=="19"){
            $error='<div class="col-lg-4"><div class="alert alert-warning">You cannot post a video AND a photo. Please choose one.</div></div>';
        }
        return $error;
    }
}

function isUserLoggedIn($uid, $conn) {
    $user_browser = $_SERVER['HTTP_USER_AGENT'];

    try{
        $stmt = $conn->prepare("SELECT password FROM users WHERE uid=?");
        $stmt->execute([$uid]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
    }catch(PDOException $exception){ 
        logme($uid,time(),"SELECT password FROM users WHERE uid=?","Error", $exception, "n/a");            

    }
    $login_string=null;
    $login_string=hash('sha256', $result['password'] . $user_browser);


    $login_string_session=null;
    if(isset($_SESSION['login_string']))
        $login_string_session=$_SESSION['login_string'];

    if($login_string==$login_string_session && $uid==$_SESSION['uid']){
        return "true";
    }else{
        return "false";
    }

} 

function encrypt_decrypt($action, $string) {
    $output = false;
    $encrypt_method = "AES-256-CBC";
    $secret_key = 'thisKeyIsTooSecretForYa';
    $secret_iv = 'This is my secret iv';
    // hash
    $key = hash('sha256', $secret_key);    
    // iv - encrypt method AES-256-CBC expects 16 bytes - else you will get a warning
    $iv = substr(hash('sha256', $secret_iv), 0, 16);
    if ( $action == 'encrypt' ) {
        $output = openssl_encrypt($string, $encrypt_method, $key, 0, $iv);
        $output = base64_encode($output);
    } else if( $action == 'decrypt' ) {
        $output = openssl_decrypt(base64_decode($string), $encrypt_method, $key, 0, $iv);
    }
    return $output;
}

function search($username, $conn) {
    $enc_name = encrypt_decrypt('encrypt', $username);
    try{
        $stmt = $conn->prepare("SELECT * FROM users WHERE username = ?");
        $stmt->execute([$enc_name]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
    }catch(PDOException $exception) {
        logme($_SESSION['uid'], time(),"SELECT * FROM users WHERE username = ?' ", "Error", $exception, "n/a");
    }
    if(is_array($result) && count($result) > 0){
        return $result;
    }else {
        return "9";
    }
}

function getRelationDatas($user, $profileVisited, $conn) {
    try{
        $stmt = $conn->prepare("SELECT * FROM `relationships`
        WHERE (`user_one_id` = :user AND `user_two_id` = :uservisited) 
            OR (`user_one_id` = :uservisited AND `user_two_id` = :user)");
        $stmt->bindParam(':user', $user);
        $stmt->bindParam('uservisited', $profileVisited);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
    }catch(PDOException $exception) {
        logme($user,time(),"SELECT * FROM `relationships`
        WHERE (`user_one_id` = :user AND `user_two_id` = :uservisited) 
            OR (`user_one_id` = :uservisited AND `user_two_id` = :user)","Error", $exception, "n/a");            
    }
    //il y a eu une request
    if($stmt->rowCount() > 0) {
        return $result;

    }else {
        $datas['status'] = 'false';
        return $datas;
    }
    
}

function isFriend($relationDatas) {
    switch($relationDatas['status']){
        // pending request //
        case '0':
            return 'pending';
            break;
        // accepted
        case '1':
            return 'accepted';
            break;
        // declined
        case '2':
            return 'declined';
            break;
        // blocked
        case '3':
            return 'blocked';
            break;
        //no relations
        case 'false':
            return 'no relation';
            break;
    }
}

function getFriendsList($user, $conn) {
    try {
        $stmt = $conn->prepare("SELECT `user_one_id`,`user_two_id` FROM `relationships`
        WHERE (`user_one_id` = :user OR `user_two_id` = :user)
        AND `status` = 1");
        $stmt->bindParam(':user', $user);
        $stmt->execute();
        $friends = [];
        
        if($stmt->rowCount() > 1) {
            $result = $stmt->fetchAll(PDO::FETCH_ASSOC);             
            foreach($result as $key=>$value){
                foreach($value as $k=>$val){
                    if($val != $_SESSION['uid']){
                        array_push($friends, $val);
                    }
                }

            }
        } elseif($stmt->rowCount() == 1) {
            $result = $stmt->fetch(PDO::FETCH_ASSOC); 
            foreach($result as $k=>$val){
                if($val != $_SESSION['uid']){
                    array_push($friends, $val);
                }
            }
        } else {
            $friends = 'No friends yet.';
        }

    }catch(PDOException $exception) {
        logme($user,time(),"get friend list","Error", $exception, "n/a");            

    }
    return $friends;
}

function getAllPendingRequests($user, $conn) {
    try {
        $stmt = $conn->prepare("SELECT user_one_id, user_two_id FROM `relationships`
        WHERE (`user_one_id` = :user OR `user_two_id` = :user)
        AND `status` = 0
        AND `action_user_id` != :user");
        $stmt->bindParam(':user', $user);
        $stmt->execute();
        $requests = [];
        if($stmt->rowCount() > 1) {
            $result = $stmt->fetchAll(PDO::FETCH_ASSOC);              
            foreach($result as $key=>$value){
                foreach($value as $k=>$val){
                    if($val != $_SESSION['uid']){
                        $requests[$val] = getUsername($val, $conn);
                    }
                }

            }      
        } elseif($stmt->rowCount() == 1) {
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            foreach($result as $k=>$val){
                if($val != $_SESSION['uid']){
                    $requests[$val] = getUsername($val, $conn);
                }
            }
        } else {
            $requests = false;
        }

    }catch(PDOException $exception) {
        logme($user,time(),"get pending requests","Error", $exception, "n/a");            

    }
    return $requests;
    
}

function addFriend($senderId, $receiverId, $datas, $conn) {
    //check if there's already a relation between the 2, in this case we want to update and not insert
    //We make sure that user_one_id is always smaller than user_two_id
    if(isFriend($datas) == 'no relation') {
        if($senderId < $receiverId) {
            $query = "INSERT INTO `relationships` (`user_one_id`, `user_two_id`, `status`, `action_user_id`)
            VALUES (:sender, :receiver, 0, :sender)";
        } elseif($senderId > $receiverId) {
            $query = "INSERT INTO `relationships` (`user_one_id`, `user_two_id`, `status`, `action_user_id`)
            VALUES (:receiver, :sender, 0, :sender)";
        }
    }else {
        $query = "UPDATE `relationships` SET `status` = 0, `action_user_id` = :sender
        WHERE ((`user_one_id` = :sender AND `user_two_id` = :receiver) OR (`user_one_id` = :receiver AND `user_two_id` = :sender))";
    }

    try{
        $stmt = $conn->prepare($query);
        $stmt->bindParam(':sender', $senderId);
        $stmt->bindParam(':receiver', $receiverId);
        $stmt->execute();
    }catch(PDOException $exception) {
        logme($senderId,time(),"add friend","Error", $exception, "n/a");            
        return 'false';
    }    
    return 'true';    
}

function acceptFriendRequest($userId, $newFriendId, $conn) {
    if($userId < $newFriendId) {
        $query = "UPDATE `relationships` SET `status` = 1, `action_user_id` = :userId
        WHERE (`user_one_id` = :userId AND `user_two_id` = :newFriend)";
    } elseif($userId > $newFriend) {
        $query = "UPDATE `relationships` SET `status` = 1, `action_user_id` = :userId
        WHERE (`user_one_id` = :newFriend AND `user_two_id` = :userId)";
    }

    try{
        $stmt = $conn->prepare($query);
        $stmt->bindParam(':userId', $userId);
        $stmt->bindParam(':newFriend', $newFriendId);
        $stmt->execute();
    }catch(PDOException $exception) {
        logme($userId,time(),"accept friend","Error", $exception, "n/a");            
        return 'false';
    }
    return 'true';    
}

function declineInvitation($uid, $profile, $conn) {
    $query = "UPDATE `relationships` SET `status` = 2, `action_user_id` = :userId
    WHERE (`user_one_id` = :userId AND `user_two_id` = :newFriend) OR (`user_one_id` = :newFriend AND `user_two_id` = :userId)";
    

    try{
        $stmt = $conn->prepare($query);
        $stmt->bindParam(':userId', $uid);
        $stmt->bindParam(':newFriend', $profile);
        $stmt->execute();
    }catch(PDOException $exception) {
        logme($uid,time(),"decline friend","Error", $exception, "n/a");            
        return 'false';
    }
    return 'true';
}

function blockUser($uid, $profile, $conn) {
    $datas = getRelationDatas($uid, $profile, $conn);

    if($datas['status'] == 'false') {
        $query = "INSERT INTO `relationships` (`user_one_id`, `user_two_id`, `status`, `action_user_id`)
        VALUES (:uid, :profile, 3, :uid)";
    } else {
        $query = "UPDATE `relationships` SET `status` = 3
        WHERE (`user_one_id` = :user AND `user_two_id` = :profile) OR (`user_one_id` = :profile AND `user_two_id` = :user)";
    }
    try{
        $stmt = $conn->prepare($query);
        $stmt->bindParam(':user', $uid);
        $stmt->bindParam(':profile', $profile);
        $stmt->execute();
    }catch(PDOException $exception) {
        logme($uid,time(),"block user","Error", $exception, "n/a");            
        return 'false';
    }
    return 'true';
}

function removeFriend($uid, $profile, $conn) {
    try{
        $stmt = $conn->prepare("UPDATE `relationships` SET `status` = 2 
        WHERE (`user_one_id` = :user AND `user_two_id` = :profile) OR (`user_one_id` = :profile AND `user_two_id` = :user)");
        $stmt->bindParam(':user', $uid);
        $stmt->bindParam(':profile', $profile);
        $stmt->execute();
    }catch(PDOException $exception) {
        logme($uid,time(),"remove friend","Error", $exception, "n/a");            
        return 'false';
    }
    return 'true';
}

function deblockUser($uid, $profile, $conn) {
    try{
        $stmt = $conn->prepare("UPDATE `relationships` SET `status` = 2 
        WHERE (`user_one_id` = :user AND `user_two_id` = :profile) OR (`user_one_id` = :profile AND `user_two_id` = :user)");
        $stmt->bindParam(':user', $uid);
        $stmt->bindParam(':profile', $profile);
        $stmt->execute();
    }catch(PDOException $exception) {
        logme($uid,time(),"deblock friend","Error", $exception, "n/a");           
        return 'false';
    }
    return 'true';
}

function cancelInvitation($uid, $profile, $conn) {
    try{
        $stmt = $conn->prepare("UPDATE `relationships` SET `status` = 2 
        WHERE (`user_one_id` = :user AND `user_two_id` = :profile) OR (`user_one_id` = :profile AND `user_two_id` = :user)");
        $stmt->bindParam(':user', $uid);
        $stmt->bindParam(':profile', $profile);
        $stmt->execute();
    }catch(PDOException $exception) {
        logme($uid,time(),"cancel invitation","Error", $exception, "n/a");            

        return 'false';
    }
    return 'true';
}

function getPost($postId, $conn) {
    try{
        $stmt = $conn->prepare("SELECT * FROM posts WHERE id=?");
        $stmt->execute([$postId]);
        $post = $stmt->fetch(PDO::FETCH_ASSOC);
        $post['comments'] = getPostComments($post['id'], $conn);
    }catch(PDOException $exception) {
        logme($_SESSION['uid'],time(),"get post","Error", $exception, "n/a");            

    }

    if($stmt->rowCount() > 0) {
        return $post;
   }else {
       return "Wrong request. This article may have been deleted.";
   }
}

function getUserWall($userWallId, $conn) {
    try{
        $stmt = $conn->prepare("SELECT * FROM posts WHERE wall_id = ?");
        $stmt->execute([$userWallId]);
        if($stmt->rowCount() > 1) {
            $posts = $stmt->fetchAll(PDO::FETCH_ASSOC);
            for($i = 0; $i < count($posts); $i++) {
                $posts[$i]['comments'] = getPostComments($posts[$i]['id'], $conn);
            }
        }elseif($stmt->rowCount() == 1){
            $posts = $stmt->fetch(PDO::FETCH_ASSOC);
            $posts['comments'] = getPostComments($posts['id'], $conn);
        }
    }catch(PDOException $exception) {
        logme($_SESSION['uid'],time(),"get user wall","Error", $exception, "n/a");            

    }

    if($stmt->rowCount() > 0) {
        return $posts;
   }else {
       return "No posts yet";
   }
}

function getPostComments($postId, $conn) {
    try{
        $stmt = $conn->prepare("SELECT * FROM comments WHERE post_id = ?");
        $stmt->execute([$postId]);
        if($stmt->rowCount() > 1) {
            $comments = $stmt->fetchAll(PDO::FETCH_ASSOC);

            for($i = 0; $i < count($comments); $i++) { 
                $author = getUsername($comments[$i]['author_id'], $conn);
                $comments[$i]['author'] = $author; 
            }
        }elseif($stmt->rowCount() == 1){
            $comments = $stmt->fetch(PDO::FETCH_ASSOC);
            $author = getUsername($comments['author_id'], $conn);
            $comments['author'] = $author;
        }
    }catch(PDOException $exception) {
        logme($_SESSION['uid'],time(),"get comments","Error", $exception, "n/a");            

    }

    if($stmt->rowCount() > 0) {    
        return $comments;
   }else {
       return "false";
   }
}

function getUsername($authorId, $conn) {
    try{
        $stmt = $conn->prepare("SELECT username FROM users WHERE uid = ?");
        $stmt->execute([$authorId]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
    }catch(PDOException $exception) {
        logme($_SESSION['uid'],time(),"get username","Error", $exception, "n/a");            
        return 'Wrong id';
    }

    if($stmt->rowCount() > 0) {
        return $result['username'];
   }else {
       return "";
   }
}


function getUser($uid, $conn){
    try{
        $stmt = $conn->prepare("SELECT * FROM users WHERE uid=?");
        $stmt->execute([$uid]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
    }catch(PDOException $exception){ 
        logme($uid,time(),"SELECT * FROM users WHERE uid=:uid","Error", $exception, "n/a");
    }
    return $user;
}

function getLatestPosts($conn) {
    try{
        $stmt = $conn->prepare("SELECT id, content, Author_Id, wall_id, `createdAt` as `creation_date` FROM posts ORDER BY creation_date DESC LIMIT 10 ");
        $stmt->execute();
        if($stmt->rowCount() > 1) {
            $posts = $stmt->fetchAll(PDO::FETCH_ASSOC);
        } else {
            $posts = $stmt->fetch(PDO::FETCH_ASSOC);
        }
    }catch(PDOException $exception){ 
        logme($_SESSION['uid'],time(),"get posts","Error", $exception, "n/a");            
    }

    if ($stmt->rowCount() > 1) {
        $data = array();
        foreach($posts as $post) {            
            $post['author'] = getUsername($post['Author_Id'], $conn);            
            array_push($data, $post);
        }
        return $data;
    }elseif($stmt->rowCount() == 1) {
        $posts['author'] = getUsername($posts['Author_Id'], $conn);
        return $posts;
    }
    else {
        return 'false';
    }
}

function displayComments($comments, $conn) { 
    $data = "";
    //s'il y a plusieurs comments
    if(!empty($comments[1]['content'])) {       
        for($i=0; $i<count($comments); $i++) {
            $author = getUser($comments[$i]['author_id'], $conn);
            $author_name = encrypt_decrypt('decrypt', $author['username']);
            $data = ('
            <div class="comment">
                <div class="comment-avatar">
                    <a href="profile.php?id='. $comments[$i]['author_id'] .'"" class="ratio img-responsive img-circle" style="background-image: url('. encrypt_decrypt('decrypt', $author['profile_picture']) .');">
                    </a>
                </div>
                <div class="comment-content">
                    <div class="body-comment">
                        <p>
                            <a class="profile-link" href="profile.php?id='. $comments[$i]['author_id'] .'">'. $author_name .'</a> '
                            . $comments[$i]['content'] .'
                        </p>
                    </div>
                    <a class="post-link"
                        href="post.php?postId='. $comments[$i]['post_id'] .'" >'.$comments[$i]['createdAt'].'
                    </a>   
                </div>                
            </div>         
            ').$data;
        }
        return $data;
    }
    //s'il n'y a qu'un comment
    elseif($comments != "false" ) {
        $author = getUser($comments['author_id'], $conn);
        $author_name = encrypt_decrypt('decrypt', $author['username']);
        $data = ('
        <div class="comment">
                <div class="comment-avatar">
                    <a href="profile.php?id='. $comments['author_id'] .'"" class="ratio img-responsive img-circle" style="background-image: url('.  encrypt_decrypt('decrypt', $author['profile_picture']) .');">
                    </a>
                </div>
                <div class="comment-content">
                    <div class="body-comment">
                        <p>
                            <a class="profile-link" href="profile.php?id='. $comments['author_id'] .'">'. $author_name .'</a> '
                            . $comments['content'] .'
                        </p>
                    </div>
                    <a class="post-link"
                        href="post.php?postId='. $comments['post_id'] .'" >'.$comments['createdAt'].'
                    </a>   
                </div>                
            </div> 
        ').$data;
        return $data;
    }else {
        return "";
    }
}

function displayPosts($posts, $conn) {
    $data = "";
    //il y a plusieurs posts
    if(is_array($posts) && !empty($posts[1]['content'])) {
        for($i=0; $i<count($posts); $i++) {
            $author = getUser($posts[$i]['Author_Id'], $conn);
            $comments = displayComments($posts[$i]['comments'], $conn);
            //number of comments
            $noc = substr_count($comments, '<div class="comment">');
            //Un user a posté sur le mur d'un autre            
            if($posts[$i]['wall_id'] != $posts[$i]['Author_Id']) {
                $name = getUsername($posts[$i]['wall_id'], $conn);
                $wroteTo = ' a écrit à <a href="profile.php?id='. $posts[$i]['wall_id'] .'">'.encrypt_decrypt('decrypt', $name).'</a>';
            }else {
                $wroteTo = "";
            }

            //Si le user est l'auteur du post ou si le post est sur son mur
            //possibilité de supprimer le post
            if($author['uid'] == $_SESSION["uid"] || $_SESSION['uid'] == $posts[$i]['wall_id']) {
                $option = '
                <div class="options-wrapper">
                    <button class="deletePost">
                        <i class="fas fa-times"></i>
                        <form class="hidden" action="process/delete-post.php">
                            <input name="postId" value="'. $posts[$i]['id'] .'"> 
                        </form>
                    </button>
                </div>';
            }else {
                $option = "";
            }
            if($noc >= 10) {
                //comment-container est hidden, 10+ comments
                $wrapper = '<div class="comment-wrapper">
                <a class="btn-comment-wrap">Show comments</a>
            </div> ';
                $class = 'hidden';
            } else {
                $wrapper = "";
                $class = "";
            }
            $data = (
            '<div class="post">
                <div class="post-heading">
                    <div class="post-avatar">
                            <a href="profile.php?id=' . $posts[$i]['Author_Id'] . '" 
                            class="ratio img-responsive img-circle" 
                            style="background-image: url('. encrypt_decrypt('decrypt', $author['profile_picture']) .');">
                            </a>
                    </div>
                    <div class="user-meta">
                        <p>
                            <a href="profile.php?id=' . $posts[$i]['Author_Id'] . '" class="profile-link" >' . encrypt_decrypt('decrypt', $author['username']) .'</a>'.$wroteTo.'
                        </p>
                        <p>
                            <a href="profile.php?id=' . $posts[$i]['Author_Id'] . '" class="post-link" >' . $posts[$i]['createdAt'] .'</a>
                        </p>
                    </div>'. $option .'
                </div>
                <hr>
                <div class="post-content">'.$posts[$i]['content'].'</div>'. $posts[$i]['media'] .'
                '. $wrapper .'
                <div id="comment-form">
                    <form class="comment-form">
                        <input type="hidden" class="hidden" name="postId" value="'. $posts[$i]['id'].'">
                        <input type="hidden" class="hidden" name="authorId" value="'.$_SESSION['uid'].'">

                        <div class="comment-avatar">
                            <a href="profile.php?id=' . $_SESSION['uid'] . '" class="ratio img-responsive img-circle" style="background-image: url('. encrypt_decrypt('decrypt', $_SESSION['location']) .');">
                            </a>
                        </div>
                        <input type="text" name="content" placeholder="Your comment...">
                    </form>
                </div>
                <div class="comment-container '.$class.'">'. $comments .'</div>
            </div>'
            ).$data;
        }
        return $data;
    }
    //il n'y a qu'un post
    elseif(is_array($posts) && count($posts) > 1 ) {
        $author = getUser($posts['Author_Id'], $conn);
        $comments = displayComments($posts['comments'], $conn);
        //number of comments
        $noc = substr_count($comments, '<div class="comment">');
        
        //Un user a posté sur le mur d'un autre            
        if($posts['wall_id'] != $posts['Author_Id']) {
            $name = getUsername($posts['wall_id'], $conn);
            $wroteTo = ' a écrit à <a href="profile.php?id='. $posts['wall_id'] .'">'.encrypt_decrypt('decrypt', $name).'</a>';
        }else {
            $wroteTo = "";
        }

        if($author['uid'] == $_SESSION["uid"] || $_SESSION['uid'] == $posts['wall_id']) {
            $option = '
            <div class="options-wrapper">
                <button class="deletePost">
                    <i class="fas fa-times"></i>
                    <form class="hidden" action="process/delete-post.php">
                        <input name="postId" value="'. $posts['id'] .'"> 
                    </form>
                </button>
            </div>';
        }else {
            $option = "";
        }
            if($noc >= 10) {
                //comment container est hidden, 10+ comments
                $wrapper = '<div class="comment-wrapper">
                <a class="btn-comment-wrap">Show comments</a>
            </div> ';
                $class = 'hidden';
            } else {
                $wrapper = "";
                $class = "";
            }
        $data = (
        '<div class="post">
            <div class="post-heading">
                <div class="post-avatar">
                    <a href="profile.php?id=' . $posts['Author_Id'] . '" 
                    class="ratio img-responsive img-circle" 
                    style="background-image: url('. encrypt_decrypt('decrypt', $author['profile_picture']) .');">
                    </a>  
                </div>
                <div class="user-meta">
                        <p>
                            <a href="profile.php?id=' . $posts['Author_Id'] . '" class="profile-link" >' . encrypt_decrypt('decrypt', $author['username']) .'</a>'.$wroteTo.'
                        </p>
                        <p>
                            <a href="profile.php?id=' . $posts['Author_Id'] . '" class="post-link" >' . $posts['createdAt'] .'</a>
                        </p>
                    </div>
                    '.$option.'           
            </div>
            <hr>
            <div class="post-content">
                <div class="content">'.$posts['content'].'</div>'.$posts['media'].'
            </div> 
            '. $wrapper .'            
            <div id="comment-form">
                <form class="comment-form">
                    <input type="hidden" class="hidden" name="postId" value="'. $posts['id'].'">
                    <input type="hidden" class="hidden" name="authorId" value="'.$posts['Author_Id'].'">

                    <div class="comment-avatar">
                        <a href="profile.php?id=' . $_SESSION['uid'] . '" class="ratio img-responsive img-circle" style="background-image: url('. encrypt_decrypt('decrypt', $_SESSION['location']) .');">
                        </a>
                    </div>
                    <input type="text" name="content" placeholder="Your comment...">
                </form>
            </div>
            <div class="comment-container '. $class.'">'. $comments .'</div>
            
        </div>'
        ).$data;
        return $data;

    }else {
        echo '<div class="alert alert-warning">'.$posts.'</div>';
    }
}

function getYoutubeId($url) {
    $regex = '~(?:http|https|)(?::\/\/|)(?:www.|)(?:youtu\.be\/|youtube\.com(?:\/embed\/|\/v\/|\/watch\?v=|\/ytscreeningroom\?v=|\/feeds\/api\/videos\/|\/user\S*[^\w\-\s]|\S*[^\w\-\s]))([\w\-]{11})[a-z0-9;:@#?&%=+\/\$_.-]*~i';
    return preg_replace( $regex, '$1', $url );
}

function expireOutdatedTokensCronJob($conn){
    try{
        $unclockTime = time()-300;
        $stmt = $conn->prepare("UPDATE reset_tokens SET tokenExpired='1' WHERE tokenCreatedTimestamp < $unclockTime");
        $stmt->execute();
    }catch(PDOException $exception){ 
        logme("N/A",time(),"PDOException","UPDATE users SET tokenExpired = 1 WHERE tokenCreatedTimestamp >= $unclockTime","Error", $exception, "n/a");
    }
}

function logme($uid,$timestamp,$action,$query,$result,$content){
    $file = fopen("../includes/log.csv", "a");
    $currTime = time();
    $line = encrypt_decrypt("encrypt", $currTime) .  "," . encrypt_decrypt("encrypt",$uid) .  "," . encrypt_decrypt("encrypt",$timestamp) .  "," . encrypt_decrypt("encrypt",$action) .  "," . encrypt_decrypt("encrypt",$query) .  "," . encrypt_decrypt("encrypt",$result) . "," . encrypt_decrypt("encrypt",$content) . PHP_EOL;
    fwrite($file, $line); # $line is an array of string values here
    fclose($file);
}

function readLog(){
    $csvFile = file("./includes/log.csv", FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    $csv = array_map('str_getcsv', $csvFile);
    return $csv;
}
