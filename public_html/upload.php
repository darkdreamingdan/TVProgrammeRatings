<?php include('inc/header.php');?>
<?php
//turn on php error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);
$tmdb_api = "";
$default_redirect = true;

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_FILES['file'])) {
	// http://www.startutorial.com/articles/view/php_file_upload_tutorial_part_1 
	$name     = $_FILES['file']['name'];
	$tmpName  = $_FILES['file']['tmp_name'];
	$error    = $_FILES['file']['error'];
	$size     = $_FILES['file']['size'];
	$valid = false;
	$ext      = strtolower(pathinfo($name, PATHINFO_EXTENSION));
	$response = "";
	
	$default_redirect = isset($_POST["redirect"]) ? true : false;
	
	switch ($error) {
		case UPLOAD_ERR_OK:
			//validate file extension
			if ( $ext != "xml" ) {
				$response = 'Invalid file extension.';
				break;
			}
			
			$xml = simplexml_load_file($tmpName);
			
			if (!$xml) {
				$response = "Invalid XML file.  Check for errors.";
				break;
			}
			
			//Process our XML and add to SQL
			foreach ($xml as $programme) {
				$type = $programme["type"] ? $programme["type"] : NULL;
				$name = $programme->name ? $programme->name : NULL;
				$img_poster = $programme->img_poster ? $programme->img_poster : NULL;
				$img_wide = $programme->img_wide ? $programme->img_wide : NULL;
				$tmdb_id = $programme->tmdb_id ? $programme->tmdb_id : NULL;
				$description = $programme->description ? $programme->description : NULL;
				
				if (!$name || ($type != 'tv' && $type !='movie') ) {
					continue;
				}
				
				// Try and grab missing info from TMDB.
				// NB: We can only add 40 items in 10 seconds from TMDB
				if ( $tmdb_id ) {
					$tmdb_data = file_get_contents ( "http://api.themoviedb.org/3/".$type."/".urlencode($tmdb_id)."?api_key=".$tmdb_api );
					if ($tmdb_data) {
						$tmdb_data = json_decode($tmdb_data,true);

						
						$name_safe = preg_replace("/[^a-zA-Z0-9.]/", "", $name);
						if (!$img_poster && $tmdb_data["poster_path"]) {
							$target = "img/".$name_safe.".jpg";
							copy("http://image.tmdb.org/t/p/w154/".$tmdb_data["poster_path"], $_SERVER['DOCUMENT_ROOT']."/".$target);
							$img_poster = $target;
						}
						if (!$img_wide && $tmdb_data["backdrop_path"]) {
							$target = "img/".$name_safe."_wide.jpg";
							copy("http://image.tmdb.org/t/p/w300/".$tmdb_data["backdrop_path"], $_SERVER['DOCUMENT_ROOT']."/".$target);
							$img_wide = $target;
						}
						if (!$description && $tmdb_data["overview"]) {
							$description = $tmdb_data["overview"];
						}
					}
				}
			    
				$exists = $database->query("SELECT EXISTS(SELECT 1 FROM `programmes` WHERE `name`='".$database->escape_string($name)."')");
				$exists = $exists->fetch_array()[0];
				if (!$exists) {
					$vote_query = $database->prepare("INSERT INTO `programmes` (`id`, `name`, `image_url`, `image_wide_url`, `description`, `tmdb_id`, `type` ) VALUES (NULL, ?, ?, ?, ?, ?, ?)");
					$vote_query->bind_param("ssssis",$name,$img_poster,$img_wide,$description,$tmdb_id,$type);
					$vote_query->execute();
					$vote_query->close();
				}
				
			}			
			
			if (isset($_POST["redirect"]))
			{
				header('Location: index.php');
				die();
			}
			
			echo '<div class="alert alert-success" role="alert">';
			echo '<strong>Upload successful!</strong> The file was successfully uploaded and processed!';
			echo '</div>';
			
			$valid = true;
			
			break;
		case UPLOAD_ERR_INI_SIZE:
			$response = 'The uploaded file exceeds the upload_max_filesize directive in php.ini.';
			break;
		case UPLOAD_ERR_FORM_SIZE:
			$response = 'The uploaded file exceeds the MAX_FILE_SIZE directive that was specified in the HTML form.';
			break;
		case UPLOAD_ERR_PARTIAL:
			$response = 'The uploaded file was only partially uploaded.';
			break;
		case UPLOAD_ERR_NO_FILE:
			$response = 'No file was uploaded.';
			break;
		case UPLOAD_ERR_NO_TMP_DIR:
			$response = 'Missing a temporary folder. Introduced in PHP 4.3.10 and PHP 5.0.3.';
			break;
		case UPLOAD_ERR_CANT_WRITE:
			$response = 'Failed to write file to disk. Introduced in PHP 5.1.0.';
			break;
		case UPLOAD_ERR_EXTENSION:
			$response = 'File upload stopped by extension. Introduced in PHP 5.2.0.';
			break;
		default:
			$response = 'Unknown error';
		break;
	}
	
	if (!$valid)
	{
		echo '<div class="alert alert-danger" role="alert">';
		echo '<strong>Upload failed :(  </strong>'.$response;
		echo '</div>';		
	}
} elseif ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST["submit"])) {
	$tmdb_id = explode ( "@", $_POST["tmdb_selection"], 2 )[1];
	$type = explode ( "@", $_POST["tmdb_selection"], 2 )[0];
	if ( $tmdb_id ) {
		$tmdb_data = file_get_contents ( "http://api.themoviedb.org/3/".$type."/".urlencode($tmdb_id)."?api_key=".$tmdb_api );
		if ($tmdb_data) {
			$tmdb_data = json_decode($tmdb_data,true);

			$name = isset($tmdb_data["title"]) ? $tmdb_data["title"] : $tmdb_data["name"];
			
			if ($name)
			{
				$name_safe = preg_replace("/[^a-zA-Z0-9.]/", "", $name);
				if ($tmdb_data["poster_path"]) {
					$target = "img/".$name_safe.".jpg";
					copy("http://image.tmdb.org/t/p/w154/".$tmdb_data["poster_path"], $_SERVER['DOCUMENT_ROOT']."/".$target);
					$img_poster = $target;
				}
				if ($tmdb_data["backdrop_path"]) {
					$target = "img/".$name_safe."_wide.jpg";
					copy("http://image.tmdb.org/t/p/w300/".$tmdb_data["backdrop_path"], $_SERVER['DOCUMENT_ROOT']."/".$target);
					$img_wide = $target;
				}
				if ($tmdb_data["overview"]) {
					$description = $tmdb_data["overview"];
				}
						
				$exists = $database->query("SELECT EXISTS(SELECT 1 FROM `programmes` WHERE `name`='".$database->escape_string($name)."')");
				$exists = $exists->fetch_array()[0];
				if (!$exists) {
					$vote_query = $database->prepare("INSERT INTO `programmes` (`id`, `name`, `image_url`, `image_wide_url`, `description`, `tmdb_id`, `type` ) VALUES (NULL, ?, ?, ?, ?, ?, ?)");
					$vote_query->bind_param("ssssis",$name,$img_poster,$img_wide,$description,$tmdb_id,$type);
					$vote_query->execute();
					$vote_query->close();
					echo '<div class="alert alert-success" role="alert">';
					echo '<strong>Programme successfully added!</strong> The programme "'.$name.'" was added successfully.';
					echo '</div>';
				} else {
					echo '<div class="alert alert-danger" role="alert">';
					echo '<strong>A programme with the same name already exists!</strong>';
					echo '</div>';					
				}
			}
			else
			{
				echo '<div class="alert alert-danger" role="alert">';
				echo '<strong>There was an error adding the programme!</strong>';
				echo '</div>';	
			}
		}
	}
	
}
?>
<h2>Admin Panel</h2>
<div class="panel panel-primary">
	<div class="panel-heading">
		<h3 class="panel-title">Upload programme file</h3>
	</div>
	<div class="panel-body">
	   <form action="upload.php" method="post" enctype="multipart/form-data">
		  <div class="form-group">
			<label for="file">Please provide an XML programme information file:</label>
			<input type="file" name="file">
			<p class="help-block">Only a .xml file is allowed.</p>
		  </div>
		  <input type="submit" class="btn btn-lg btn-primary" value="Upload">
		  <div class="checkbox">
			<label><input name="redirect" type="checkbox" <?php echo $default_redirect ? ('checked="checked" value="Yes"') : 'value="No"';  ?> >Redirect me back to the content page on successful upload</label>
		  </div>
		</form>
	</div>	
</div>

<div class="panel panel-primary">
	<div class="panel-heading">
		<h3 class="panel-title">Manually add programme</h3>
	</div>
	
	<div class="panel-body">
		Please specify the TV show or movie you'd like to add:
		<div class="input-group col-lg-4">
		   <input id="search_query" type="text" class="form-control">
		   <span class="input-group-btn">
				<button id="do_search" class="btn btn-default" type="button">Search</button>
				<img id="loadinggif" src="libraries/img/loading.gif" style="display:none" />
		   </span>
		</div>
		<p>
		<form action="" method="post">
		   <div id="search_results_display" class="input-group col-lg-4" style="display:none";>
				  <label for="search_results">Select result:</label>
				  <select name="tmdb_selection" class="form-control" id="search_results"></select>
			</div>
			<input id="search_add" name="submit" type="submit" class="btn btn-primary" style="display:none" value="Add">
		</form>
	</div>
</div>

<script>
$("#search_query").keyup(function(e){
    if(e.keyCode == 13)
    {
        $("#do_search").trigger("click");
    }
});

$("#do_search").click(function(){
	console.log("1");
	$("#loadinggif").show();
	jQuery.ajax({
			type: "POST",
			url: 'inc/ajax.php',
			dataType: 'json',
			data: {functionname: 'tmdb_search', arguments: [$("#search_query").val()] },
			success: function (obj, textstatus) {
				  if( !('error' in obj) ) {
					console.log("success");

					var $selectResults = $("#search_results");
					$selectResults.html("");  //Clear current options
					console.log(obj.result);
					var arrayLength = obj.result.length;
					for (var i = 0; i < arrayLength; i++) {
						
						$selectResults.append($("<option>", { value: obj.result[i][1], html: obj.result[i][0] }));
					}
					$("#search_add").show();
					$("#search_results_display").show();
					$("#loadinggif").hide();
				  }
				  else {
					  console.log(obj.error);
				  }
			}
		});
	} );
</script>

<?php include('inc/footer.php');?>