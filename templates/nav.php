<?php 

if(isset($_SESSION['uid'])){
    if(isUserLoggedIn($_SESSION['uid'], $conn) == 'true'){
        $sessionUser = encrypt_decrypt('decrypt', $_SESSION['username']);
        switch($_SERVER['REQUEST_URI']) {
            case preg_match('/home.php/', $_SERVER['REQUEST_URI']) == 1:
                $link1 = 'profile.php?edit=true&id='. $uid;
                $link2='logs.php';
                $link3='logout.php';
                $class1 ='';
                $class2='';     
                $class3='';           
                $content1='Edit profile';
                $content2='Read logs';
                $content3='Logout';
                break;
            default:          
                $link1 = 'home.php';
                $link2 = 'logs.php';
                $link3='logout.php';
                $class1 ='';
                $class2 = ''  ;  
                $class3='';               
                $content1=$sessionUser;
                $content2 = "Read logs";
                $content3='Logout';
                break;
        }
    }
}

?>
<nav class="topnav">
    <ul>
        <li>
            <div class="dropdown">
                <button class="dropbtn"><?= $sessionUser ?></button>
                <div class="dropdown-content">          
                    <a href="<?= $link1 ?>" class="<?= $class1 ?>"> <?= $content1 ?> </a>
                    <a href="<?= $link2 ?>" class="<?= $class2 ?>"> <?= $content2 ?> </a>
                    <a href="<?= $link3 ?>" class="<?= $class3 ?>"> <?= $content3 ?> </a>
                </div>
            </div>
        </li>  
        <li>
            <div class="search-container">
                <form action="./search.php" method="POST">
                    <input type="text" placeholder="Search someone.." name="search">
                    <button type="submit">Submit</button>
                </form>
            </div>
        </li>      
    </ul>
    
    
    
</nav>