<?php
include_once 'includes/db-connect.php';
include_once 'includes/functions.php';
sec_session_start();
if(isset($_SESSION['uid'])){
    $uid = preg_replace("/[^0-9]/", "", $_SESSION['uid']); //XSS Security
    $sessionUser = encrypt_decrypt('decrypt', $_SESSION['username']);
	if(isUserLoggedIn($uid,$conn)=="false") {
        header('Location: ./index.php');
    } else {
        //timeline
        $posts = getLatestPosts($conn);
        $userWall = getUserWall($uid, $conn);
        $friends = getFriendsList($uid, $conn);
        $pendingRequests = getAllPendingRequests($uid, $conn);

        if(is_array($friends) && count($friends)>1){
            $friendCount = count($friends);
        }elseif(is_string($friends)){
            $friendCount = 0;
        }else {
            $friendCount = 1;

        }

        if(!empty($userWall[1]['content'])){
            $postCount = count($userWall);
        } elseif(is_string($userWall)) {
            $postCount = 0;
        } else {
            $postCount = 1;
        }

    }
} else {
    header('Location: login.php');
}

$error=null;
if(isset($_GET["error"])){
	if(!is_numeric($_GET["error"])){
		$error="Dont edit the URL GET var, thanks.";
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
        <title>Login</title>       
    </head>

    <body>
        <header></header>
        <?php include 'templates/nav.php'; ?>
        <?php if(!empty($error)){
            echo $error; 
        }
        ?>
        <div class="main-container">
        <div class="aside">
            <div class="user-infos">
                    <div class="name">
                        <h5><?=  encrypt_decrypt('decrypt', $_SESSION['username'] ) ?></h5>
                    </div>
                <div class="img-container">
                    <a href="home.php" class="ratio img-responsive img-circle" style="background-image: url(<?= encrypt_decrypt('decrypt', $_SESSION['location'] ) ?>);">
                    </a>
                </div>
                <div class="col">
                    <h5>
                        <small>POSTS</small>
                        <a href="#"><?= $postCount ?></a>
                    </h5>
                    <h5>
                        <small>FRIENDS</small>
                        <a href="#"><?= $friendCount ?></a>
                    </h5>
                </div>
            </div>
            <div class="requests">
                <?php if($pendingRequests == false) : ?>
                    <h4>No friend requests</h4>
                <?php endif; ?>
                
                <?php if(is_array($pendingRequests)) {
                    forEach($pendingRequests as $key=>$val){
                        echo '<a href="profile.php?id='.$key.'">'. encrypt_decrypt('decrypt', $val) .' is waiting for your response!</a>';
                    }
                }
                
                
                ?>
            </div>
        </div>

        
            <div class="main-content">
                <div class="write-post">
                    <div class="post-avatar">
                        <a href="home.php" class="ratio img-responsive img-circle" style="background-image: url(<?= encrypt_decrypt('decrypt', $_SESSION['location'] ) ?>);">
                        </a>
                    </div>
                    <form class="form-post" id="postForm" enctype="multipart/form-data" action="process/add-post.php" method="POST">
                        <input type="hidden" class="hidden" name="wallId" value="<?= $uid ?>">
                        <input type="hidden" class="hidden" name="authorId" value="<?= $uid ?>">
                        <input type="text" class="form-post-content" name="content" placeholder="Share what's on your mind">
                    
                    <div class="media">
                        <div>
                            <label for="img">Upload an image</label>
                            <input type="file" name="img" value="">
                        </div>
                        <div>
                            <label for="video">Link a Youtube video</label>
                            <input type="text" name="video" value="">
                        </div>
                    </div>
                    </form>

                </div>  
                <div class="wall">
                    <?php echo displayPosts($userWall, $conn); ?>  
                </div>
            </div> 

            <div class="timeline">
                <h5>Latest posts</h5>
                <hr>
                <ul>
                    <!--  plusieurs posts  --->
                    <?php if(isset($posts[1]['content'])) : ?>
                        <?php  foreach($posts as $post) : ?>
                        <li>
                            <a href="post.php?id=<?= $uid ?>&postId=<?= $post['id'] ?>">
                                <strong><?= $post['creation_date']  ?></strong> :  Par <?= encrypt_decrypt('decrypt', $post['author']) ?>.
                            </a>
                        </li>
                        <?php endforeach; ?>
                    
                    <!--  aucun post  --->
                    <?php elseif($posts == 'false') : ?>
                        <strong>No posts yet.</strong>
                    <!--  un seul post  --->
                    <?php else : ?>
                        <li>
                            <a href="post.php?id=<?= $uid ?>&postId=<?= $posts['id'] ?>">
                                <strong><?= $posts['creation_date']  ?></strong> :  Par <?= encrypt_decrypt('decrypt', $posts['author']) ?>.
                            </a>
                        </li>
                    <?php endif; ?>
                    
                </ul>
            </div>
        </div>
    </body>
</html>