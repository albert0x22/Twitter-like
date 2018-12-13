<?php
include_once 'includes/db-connect.php';
include_once 'includes/functions.php';
 

sec_session_start();


//Note an SSL connection is required to prevent network sniffing
if(isset($_SESSION['uid'])){
	$uid = preg_replace("/[^0-9]/", "", $_SESSION['uid']); //XSS Security
	if(isUserLoggedIn($uid,$conn)=="true")
		header('Location: ./home.php');
}

//Error handling
$error=null;
if(isset($_GET["error"])){
	if(!is_numeric($_GET["error"])){
		$error="Dont not edit the URL GET var, thanks.";
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
	<link rel="stylesheet" href="./assets/css/normalize.css">
   	<link rel="stylesheet" href="./assets/css/style.css">
	<script src="https://code.jquery.com/jquery-3.3.1.min.js"
  integrity="sha256-FgpCb/KJQlLNfOu91ta32o/NMZxltwRo8QtmkMRdAu8="
  crossorigin="anonymous"></script> 
    <script type="text/JavaScript" src="js/sha512.js"></script> 
    <script type="text/JavaScript" src="js/functions.js"></script>
	<title>Register</title>
</head>

<body>
<?php
    if (!empty($error)) {
        echo $error;
    }
?>
<div class="main-container">
        <form class="form register-form" id="register-form" enctype="multipart/form-data" action="./process/process-user-data.php"
            method="post" role="form">

            <input type="hidden" class="hidden" name='mode' value='register'>
            <div class="form-header">
                <div class="link">
                    <a href="./login.php">Login</a>
                </div>

                <div class="link"><a href="./index.php" class="active">Register</a></div>
            </div>
            <hr>

            <div class="form-body">
                <div class="form-group">
                    <label for="username">Username</label>
                    <input type="text" name="username" id="username" required pattern="[a-zA-Z0-9]+" title="Please use aplhanumeric charaters only."
                        tabindex="1" placeholder="Username" value="" />
                </div>
                <div class="form-group">
                    <label for="password">Password</label>
                    <input type="password" required name="password" id="password" tabindex="2" placeholder="Password" />
                </div>
                <div class="form-group">
                    <label for="password-conf">Password confirm</label>
                    <input type="password" required name="passwordConfirm" id="passwordConfirm" tabindex="2"
                        placeholder="Confirm Password" />
                </div>
                <div class="form-group">
                    <label for="email">Email</label>
                    <input type="text" name="email" id="email" required pattern="[a-z0-9._%+-]+@[a-z0-9.-]+\.[a-z]{2,3}$"
                        title="Please use aplhanumeric charaters only." tabindex="1" placeholder="test@test.com" value="" />
                </div>
                <div class="form-group">
                    <label for="dob">Date of birth</label>
                    <input type="text" id="date" required tabindex="3" data-format="DD-MM-YYYY" data-template="DD-MMM-YYYY"
                        name="dob" placeholder="DD-MM-YYYY" />
                </div>
                <div class="form-group">
                    <label for="file">Profile picture</label>
                    <span>Les formats autorisés sont JPG, GIF et PNG.</span>
                    <input type="file" name='file' id="customFile" />
                </div>
                <div class="form-group">
                    <input class="btn-register" type="submit" name="register-submit" id="register-submit" tabindex="4"
                        value="Register Now" onclick="return regformhash(this.form,
                        this.form.username,
                        this.form.email,
                        this.form.password,
                        this.form.passwordConfirm,
                        this.form.file);">
                </div>
            </div>
        </form>
    </div>

</body>
</html>