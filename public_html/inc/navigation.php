<?php
$navigation = array( 	array( 	"name" => 	"Rate our content", 
								"URL" => 		"index.php",
						),
						array( 	"name" => 	"Content Leaderboard", 
								"URL" => 		"leaderboard.php",
						),
						array( "name" => 	"Upload", 
								"URL" => 	"upload.php",
						)
             );
			 
echo '<ul class="nav nav-pills" role="tablist">';
foreach($navigation as $item)
{
	if (basename($_SERVER['PHP_SELF']) == $item["URL"]){
		echo '<li role="presentation" class="active"><a href="#">'.$item["name"].'</a></li>';
	} else {
		echo '<li role="presentation"><a href="'.$item["URL"].'">'.$item["name"].'</a></li>';
	}
}
echo '</ul>';
?>
