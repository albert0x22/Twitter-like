<?php
$host="localhost"; 
$root="root"; 
$root_password=""; 
$db="Blog"; 
if($_SERVER["REQUEST_METHOD"] == "POST"){
        $dbh = new PDO("mysql:host=$host", $root, $root_password);
		$dbh->exec("DROP DATABASE IF EXISTS `$db`;");
		$dbh->exec("CREATE DATABASE `$db`;");
        $conn = new PDO("mysql:host=$host;dbname=$db", $root, $root_password);
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
		$conn->exec("DROP TABLE IF EXISTS `comments`;
			CREATE TABLE IF NOT EXISTS `comments` (
			`id` int(10) NOT NULL AUTO_INCREMENT,
			`author_id` int(10) NOT NULL,
			`post_id` int(10) NOT NULL,
			`content` text NOT NULL,
			`createdAt` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
			PRIMARY KEY (`id`),
			KEY `author_id` (`author_id`),
			KEY `post_id` (`post_id`)
		  ) ENGINE=InnoDB DEFAULT CHARSET=utf8;");
        $conn->exec("DROP TABLE IF EXISTS `login_attempts`;
		CREATE TABLE IF NOT EXISTS `login_attempts` (
		  `field` int(11) NOT NULL AUTO_INCREMENT,
		  `uid` int(255) NOT NULL,
		  `time` varchar(30) NOT NULL,
		  PRIMARY KEY (`field`)
		) ENGINE=InnoDB AUTO_INCREMENT=38 DEFAULT CHARSET=utf8;");  
		$conn->exec("DROP TABLE IF EXISTS `posts`;
		CREATE TABLE IF NOT EXISTS `posts` (
		  `id` int(10) NOT NULL AUTO_INCREMENT,
		  `content` text NOT NULL,
          `createdAt` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
          `media` text NOT NULL,
		  `Author_Id` int(10) NOT NULL,
		  `wall_id` int(20) DEFAULT NULL,
		  PRIMARY KEY (`id`),
		  KEY `Author_Id` (`Author_Id`),
		  KEY `FK_WALL_ID` (`wall_id`)
		) ENGINE=InnoDB DEFAULT CHARSET=utf8;");  
		$conn->exec("
		DROP TABLE IF EXISTS `relationships`;
		CREATE TABLE IF NOT EXISTS `relationships` (
		  `user_one_id` int(10) UNSIGNED NOT NULL,
		  `user_two_id` int(10) UNSIGNED NOT NULL,
		  `status` int(10) UNSIGNED NOT NULL DEFAULT '0',
		  `action_user_id` int(10) UNSIGNED NOT NULL,
		  UNIQUE KEY `unique_users_id` (`user_one_id`,`user_two_id`),
		  KEY `user_two_id` (`user_two_id`),
		  KEY `action_user_id` (`action_user_id`)
		) ENGINE=InnoDB DEFAULT CHARSET=utf8;");  
		$conn->exec("
		DROP TABLE IF EXISTS `reset_tokens`;
		CREATE TABLE IF NOT EXISTS `reset_tokens` (
		  `uid` int(11) NOT NULL,
		  `token` varchar(255) NOT NULL,
		  `tokenCreatedTimestamp` varchar(255) NOT NULL,
		  `tokenExpired` int(1) NOT NULL,
		  PRIMARY KEY (`uid`),
		  UNIQUE KEY `uid` (`uid`)
		) ENGINE=InnoDB DEFAULT CHARSET=utf8;");   
		$conn->exec("DROP TABLE IF EXISTS `users`;
		CREATE TABLE IF NOT EXISTS `users` (
		  `uid` int(11) NOT NULL AUTO_INCREMENT,
		  `profile_picture` varchar(255) DEFAULT NULL,
		  `username` varchar(255) NOT NULL,
		  `email` varchar(255) NOT NULL,
		  `password` char(128) NOT NULL,
		  `dob` varchar(255) NOT NULL,
		  PRIMARY KEY (`uid`)
		) ENGINE=InnoDB AUTO_INCREMENT=33 DEFAULT CHARSET=utf8;
		ALTER TABLE `posts`
              ADD CONSTRAINT `FK_WALL_ID` FOREIGN KEY (`wall_id`) REFERENCES `users` (`uid`) ON DELETE CASCADE ON UPDATE CASCADE;");  
        $conn->exec("INSERT INTO `users` (`uid`, `profile_picture`, `username`, `email`, `password`, `dob`) VALUES
              (33, 'TjVRNWpvaDFTZk4yanBRVjdWeUpadz09', 'TUNuRS84VGhUS1lTNnZNYVZieXpSdz09', 'OWlhVlNUOUpmK3lQQzY5Qk5RQkY2Zz09', '$2y$10\$N7yzDkjVf1kOisxaqVAwBOIRDk3.75dS7qKp3cbAlbS9LEEiBOsba', 'NitHdE43Yy9ZZk5kUVF1QUtlZi9ZQT09'),
              (34, 'QnB5N0c4TlJQV1BneGd0VkMrWXpXNDZ6QzJLZjU3ZzliZ0FyMVp3b0JuWT0=', 'c1V5VStsRUFOeE1GVnpuaE9ITkxkQT09', 'cWNYdWVnZ1JGSDhOOVhiRi95QUhpUT09', '$2y$10\$HGEReDh7ymt1uGitKhLwOuYpWDOPekwvE3LYoPu6f61reHa9VZOHy', 'STRDTE9yNE1FRkNMMmhFendnVlI3QT09'),
              (35, 'QzdYd2tsdStsQ1EzWGtINUw1eEp5b2V6SEwyMm5TdmgwUjVCL3JJanJ6eEdkTFUzQzJIWUwyL21kVVQ5QVVWV0xYeS9FWUkyenFGSUJOTkd0WlZ0NUE9PQ==', 'VW9hbTBPYllXdzFQMURkcU91WWQvQT09', 'Y1dRSjl4QzVhYW9jd3FxekdWTk81Q1kzYXg4UGNOL3A3TngrTU9hbHo5ND0=', '$2y$10\$pTP1jqZ7wBRSYLyFV5AmueJ59v1FX32oaC7bAB4zFyJy..0az2dGq', 'NitHdE43Yy9ZZk5kUVF1QUtlZi9ZQT09');
              COMMIT;");  
        $conn->exec("INSERT INTO `posts` (`id`, `content`, `createdAt`, `media`, `Author_Id`, `wall_id`) VALUES
            (1, 'Hello world!', '2018-12-13 14:06:58', '', 34, 33),
            (3, 'hello world', '2018-12-13 14:10:37', '', 35, 35);");
        $conn->exec("INSERT INTO `relationships` (`user_one_id`, `user_two_id`, `status`, `action_user_id`) VALUES
        (33, 34, 1, 33),
        (33, 35, 0, 35);");
        
	    header('Location: ./index.php');
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
       <title>Setup</title>

</head>

<body>
    <div class="main-container">
        <div class="setup">
            <div class="setup-head">
                <h5>Database setup</h5>
            </div>
            <div class="setup-button">
                <form class="setup-form" method="POST" role="form">
                    <input type="submit" value="Build DB" id="setup" class="btn-register" tabindex="1" />
                </form>
            </div>
            <div>
        <p>Veuillez remplir les variables dans <strong>/App/setup.php</strong> et <strong>/App/includes/db-connect.php</strong> pour la connection à la base de données.
            La base de donnée créée s'appellera Blog.
            La suppression de toute bdd s'appellant "Blog" s'effectuera avant le build.
        </p>
    </div>
        </div>
    </div>
</body>
</html>