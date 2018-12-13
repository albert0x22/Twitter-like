<?php 
if(isset($relationDatas)) {
    $relation = isFriend($relationDatas);
    if(!isset($relationDatas['action_user_id'])){
        $requester = 'none';
    }else {					
        $requester = $relationDatas['action_user_id'];
    }
}

?>
    
<?php if($uid == $profile & $editMode == 'true') : ?> 
    <div class="main-container">
        <form class="form register-form" id="register-form" enctype="multipart/form-data" action="./process/process-user-data.php"
            method="post" role="form">
            <div class="form-body">

            </div>
    	    <div class="form-group">
                <input type="hidden" class="hidden" name='mode' value='update'>
    	    	<label for="username">Username:</label>
    	    	<input type="text" name="username" id="username" required pattern="[a-zA-Z0-9]+" title="Please use aplhanumeric charaters only." tabindex="1" class="form-control" placeholder="Username" value="<?= encrypt_decrypt('decrypt', $requestedProfile['username'] ) ?>">
    	    </div>
    	    <div class="form-group">
    	    	<label for="password">Password:</label>
    	    	<input type="password" required name="password" id="password" tabindex="2"  class="form-control" placeholder="Password">
    	    </div>
    	    <div class="form-group">
    	    	<label for="passwordConfirm">Confirm Password:</label>
    	    	<input type="password" required name="passwordConfirm" id="passwordConfirm" tabindex="2" class="form-control" placeholder="Confirm Password">
    	    </div>
    	    <div class="form-group">
    	    	<label for="email">Email:</label>
    	    	<input type="text" name="email" id="email"  required pattern="[a-z0-9._%+-]+@[a-z0-9.-]+\.[a-z]{2,3}$" title="Please use aplhanumeric charaters only." tabindex="1" class="form-control" placeholder="test@test.com" value="<?= encrypt_decrypt('decrypt', $requestedProfile['email']) ?>">
    	    </div>    
    	    <div class="form-group">
    	    	<label for="date">Date of Birth:</label>
    	    	<input type="text" id="date" required tabindex="3" value="<?= encrypt_decrypt('decrypt', $requestedProfile['dob']) ?>" class="form-control" data-format="DD-MM-YYYY" data-template="DD-MMM-YYYY" name="dob" placeholder="DD-MM-YYYY">
            </div>
            <div class="form-group">
                <label for="file">Profile picture</label>
                <span>Les formats autorisés sont JPG, GIF et PNG.</span>
                <input type="file" name="file" id="customFile">
            </div>
    	    <div class="form-group">
                    <input class="btn-register" type="submit" name="register-submit" id="register-submit" tabindex="4" value="Submit changes" onclick="return regformhash(this.form,
                        this.form.username,
                        this.form.email,
                        this.form.password,
                        this.form.passwordConfirm,
                        this.form.file);">
                </div>
                <div class="form-group">
                    <a href="home.php">Cancel</a>
                </div>
    	</form>
    </div>
<?php endif; ?>

<?php if($request == 'visit'): ?>
    <div class="col-lg-4 friendship">
        <?php switch($relation):
        /* requete en attente */
        case 'pending':  ?>
            <!-- le visiteur a fait la demande d'amitié au profil visité --->
            <?php if($requester == $uid) : ?>
                <div class="status">
                    <ul>
                        <li>
                            Invitation envoyée          
                        </li>
                        <li>
                            <a href="process/process-relationships?id=<?= $uid ?>&profile=<?= $profile ?>&action=cancel">Annuler l'invitation</a>
                        </li>
                    </ul>
                </div>
                        
            <!--  le visiteur a reçu la demande d'amitié du profil visité  --->
            <?php else : ?>
                <div class="status">
                    <ul>
                        <li><a href="process/process-relationships?id=<?= $uid ?>&profile=<?= $profile ?>&action=accept">Accepter l'invitation</a></li>
                        <li><a href="process/process-relationships?id=<?= $uid ?>&profile=<?= $profile ?>&action=decline">Décliner l'invitation</a></li>
                        <li><a href="process/process-relationships?id=<?= $uid ?>&profile=<?= $profile ?>&action=block">Bloquer cet utilisateur</a></li>
                    </ul>
                </div>
            <?php endif; ?>
        <?php break; ?>
                
        <!-- requête acceptée --->
        <?php case 'accepted': ?>
        <div class="status">
            <ul>
                <li><a href="process/process-relationships?id=<?= $uid ?>&profile=<?= $profile ?>&action=remove">Retirer de la liste d'amis</a></li>
                <li><a href="process/process-relationships?id=<?= $uid ?>&profile=<?= $profile ?>&action=block">Bloquer cet utilisateur</a></li>
            </ul>
        </div>
        <?php break; ?>

        <!-- requête déclinée --->
        <?php case 'declined': ?>
            <ul>
                <li>
                    <a href="process/process-relationships?id=<?= $uid ?>&profile=<?= $profile ?>&action=add">Ajouter à la liste d'amis</a>
                </li>
                <li>
                    <a href="process/process-relationships?id=<?= $uid ?>&profile=<?= $profile ?>&action=block">Bloquer cet utilisateur</a>
                </li>
            </ul>
        <?php break; ?>

            <!-- utilisateur bloqué --->
        <?php case 'blocked': ?>
            <!-- Le visiteur a bloqué cet utilisateur --->
            <?php if($requester == $uid) : ?>
                <ul>
                    <li>Vous avez bloqué cet utilisateur</li>
                    <li><a href="process/process-relationships?id=<?= $uid ?>&profile=<?= $profile ?>&action=deblock">Débloquer</a></li>
                </ul>
                
            <!-- cet utilisateur a bloqué le visiteur --->
            <?php else : ?>
                <ul>
                    <li>Cet utilisateur vous a bloqué.</li>
                    <li><a href="home.php">Retour au profil</a></li>
                </ul>
                
            <?php endif; ?>
        <?php break; ?>

        <!-- no relations --->
        <?php case 'no relation': ?>
            <ul>
                <li>
                    <a href="process/process-relationships?id=<?= $uid ?>&profile=<?= $profile ?>&action=add">Ajouter à la liste d'amis</a>
                </li>
                <li>
                    <a href="process/process-relationships?id=<?= $uid ?>&profile=<?= $profile ?>&action=block">Bloquer cet utilisateur</a>
                </li>
            </ul>
        <?php break; ?>

        <?php endswitch; ?>
    </div>

    <div class="main-container">
        <?php if($relation != 'blocked') : ?>
            <div class="aside">
                <div class="user-infos">
                    <div class="name">
                        <h5><?=  encrypt_decrypt('decrypt', $requestedProfile['username'] ) ?></h5>
                    </div>
                    <div>
                        <div class="img-container">
                        <a href="profile.php?id=<?= $profile ?>" 
                                class="ratio img-responsive img-circle" 
                                style="background-image: url(<?= encrypt_decrypt('decrypt', $requestedProfile['profile_picture'] ) ?>);">
                                </a>
                        </div>
                        <div class="col">
                            <h5>
                                <small>FRIENDS</small>
                                <a href="#"><?= $friendCount ?></a>
                            </h5>
                            <h5>
                                <small>POSTS</small>
                                <a href="#"><?= $postCount ?></a>
                            </h5>
                        </div>
                    </div>
                </div>
            </div>
            <div class="main-content">
                <div class="write-post">
                    <div class="post-avatar">
                        <a  class="ratio img-responsive img-circle" style="background-image: url(<?= encrypt_decrypt('decrypt', $_SESSION['location'] ) ?>);">
                        </a>
                    </div>
                    <form class="form-post" id="postForm" enctype="multipart/form-data" action="process/add-post.php" method="POST">
                    
                        <input type="hidden" class="hidden" name="wallId" value="<?= $profile ?>">
                        <input type="hidden" class="hidden" name="authorId" value="<?= $uid ?>">

                        <input type="text" class="form-post-content" name="content" placeholder="Write something to <?= encrypt_decrypt('decrypt', $requestedProfile['username']) ?>">
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
                    <?php echo displayPosts($requestedProfileWall, $conn); ?> 
                </div>
            </div>
        <?php endif; ?>
    </div>
<?php endif; ?>