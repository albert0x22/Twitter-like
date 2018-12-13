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
    <link rel="stylesheet" href="assets/css/normalize.css">
    <link rel="stylesheet" href="assets/css/style.css">      
	<script src="https://code.jquery.com/jquery-3.3.1.min.js"
  integrity="sha256-FgpCb/KJQlLNfOu91ta32o/NMZxltwRo8QtmkMRdAu8="
  crossorigin="anonymous"></script> 
    <script type="text/JavaScript" src="js/sha512.js"></script> 
    <script type="text/JavaScript" src="js/functions.js"></script>
    <title>Login</title>
</head>

<body>
<?php
    if (!empty($error)) {
        echo $error;
    }
?>
    <div class="main-container">
        <form class="form login-form" action="./process/process-login.php" method="post" role="form">
            <div class="form-header">
                <div class="link">
                    <a href="./login.php" class="active">Login</a>
                </div>

                <div class="link"><a href="./index.php">Register</a></div>
            </div>
            <hr>

            <div class="form-body">
                    
                <div class="form-group">
                    <label for="username">Username:</label>
                    <input type="text" name="username" id="username" required pattern="[a-zA-Z0-9]+" title="Please use aplhanumeric charaters only."
                        tabindex="1" placeholder="Username" value="" />
                </div>
                <div class="form-group">
                    <label for="password">Password:</label>
                    <input type="password" name="password" id="password" tabindex="2" placeholder="Password" />
                </div>
            </div>
            <div class="form-group">
                <input type="submit" name="login-submit" class="btn-register" id="login" tabindex="3" value="Log In"
                    onclick="return formhash(this.form, this.form.password);">
            </div>
        </form>

        <div class="test">
            <h5>Test IDs</h5>
            <div class="form-group">
                <p>Username:</p>
                <span>test</span>
            </div>
            <div class="form-group">
                <p>Password:</p>
                <span>Un cheval blanc1 (avec espaces et majuscule)</span>
            </div>
        </div>
    </div>

</body>