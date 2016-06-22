<?php
header('Content-Type: application/json');

include('database.php');

#https://stackoverflow.com/questions/15757750/php-function-call-using-javascript

$aResult = array();

if( !isset($_POST['functionname']) ) { $aResult['error'] = 'No function name!'; }

if( !isset($_POST['arguments']) ) { $aResult['error'] = 'No function arguments!'; }

if( !isset($aResult['error']) ) {

	switch($_POST['functionname']) {
		case 'vote':
		   if( !is_array($_POST['arguments']) || (count($_POST['arguments']) < 2) ) {
			   $aResult['error'] = 'Error in arguments!';
		   }
		   else {
				$programme_id = $_POST['arguments'][0];
				$rating = $_POST['arguments'][1];
				$user = get_client_ip();
				
				if (!$user)
				{
					break;
				}
				
				# Make sure rating isn't out of bounds
				$rating = min($rating,5);
				$rating = max($rating,0);
				
				# Check if the entry already exists...
				$vote_query = $database->prepare("SELECT EXISTS(SELECT 1 FROM `user_votes` WHERE `IP`=? AND `programme_id`=?)");
				$vote_query->bind_param("si",$user,$programme_id);
				$vote_query->execute();
				$vote_query->bind_result($aResult['result']);
				$vote_query->fetch();
				if ($aResult['result'] == 0) {
					$vote_query->free_result();
					$vote_query = $database->prepare("INSERT INTO `user_votes` (`id`, `IP`, `programme_id`, `rating`) VALUES (NULL, ?, ?, ?)");
					$vote_query->bind_param("sid",$user,$programme_id,$rating);
					$vote_query->execute();
					$vote_query->bind_result($aResult['result']);
					$vote_query->fetch();
					$vote_query->free_result();
				} else {
					$vote_query->free_result();
					$vote_query = $database->prepare("UPDATE `user_votes` SET `rating`=? WHERE `IP`=? AND `programme_id`=?");
					$vote_query->bind_param("dsi",$rating,$user,$programme_id);
					$vote_query->execute();
					$vote_query->bind_result($aResult['result']);
					$vote_query->fetch();
					$vote_query->free_result();
				}
				
				# Finally, update our main programmes table with a sum of our new ratings
				# Future thoughts: for scalability, this should happen periodically rather than as soon as a user votes
				$programme_id_esc = $database->escape_string($_POST['arguments'][0]);
				$vote_query = $database->prepare("UPDATE `programmes` SET `rating`=(SELECT SUM(`rating`) FROM `user_votes` WHERE `programme_id`=?) WHERE `id`=?");
				$vote_query->bind_param("ii",$programme_id,$programme_id);
				$vote_query->execute();
				$vote_query->bind_result($aResult['result']);
				$vote_query->fetch();
				$vote_query->free_result();
		   }
		   break;
		case 'tmdb_search':
			$tmdb_api = "";
			$tmdb_data = file_get_contents ( "http://api.themoviedb.org/3/search/multi?api_key=".$tmdb_api."&page=1&query=".urlencode($_POST['arguments'][0]) );
			if ($tmdb_data) {
				 $tmdb_data = json_decode($tmdb_data,true);
				 $data = array();
				 foreach ($tmdb_data["results"] as $result)
				 {
					if ($result["media_type"] == "movie" || $result["media_type"] == "tv")
					{
						$year = $result["first_air_date"] ? date("Y", strtotime($result["first_air_date"]) ) : date("Y", strtotime($result["release_date"]) );
						$title = $result["title"] ? $result["title"] : $result["name"];
						$data[] = array( strtoupper ($result["media_type"]).": ".$title." (".$year.")", $result["media_type"]."@".$result["id"] );
					}
				 }
				 $aResult['result'] = $data;
			}
			break;
		default:
		   $aResult['error'] = 'Not found function '.$_POST['functionname'].'!';
		   break;
	}

}

echo json_encode($aResult);

$vote_query = NULL;
$vote_query_update = NULL;
	
$database->close();

?>