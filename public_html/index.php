<?php include('inc/header.php');?>
<?php
$sql = <<<SQL
SELECT * FROM `programmes`
ORDER BY `name`
SQL;

if(!$programmes = $database->query($sql)){
    die('There was an error running the query [' . $db->error . ']');
}

$rslt_user_votes = $database->query("SELECT * FROM `user_votes` WHERE `IP`='".$database->escape_string(get_client_ip())."'");
# Compile a map of programme ids and the users values
$m_user_votes = array();
foreach($rslt_user_votes as $vote) {
	if ( $vote["programme_id"] ) {	
		$m_user_votes[$vote["programme_id"]] = $vote["rating"];
	}
}

if($programmes->num_rows == 0){
	echo "<h3>Oops, it seems we don't have any programmes added yet!<p>";
	echo "We'll get an administrator to <a href='upload.php'>upload something</a> as soon as possible!</h2>";
}

?>

<div class="row">
	<?php foreach($programmes as $item): ?>
	<div class="col-lg-2 col-sm-3 col-xs-4">
		<div class="main-grid-item">
			<img src="<?php echo htmlspecialchars($item["image_url"]); ?>" alt="<?php echo htmlspecialchars($item["description"]); ?>" height="180" data-toggle="tooltip" data-placement="right" title="<?php echo htmlspecialchars($item["description"]); ?>">
			<div class="main-grid-item-name">
				<?php echo $item["name"]; ?>
				<input id="<?php echo $item["id"]; ?>" value="<?php echo $m_user_votes[$item["id"]]; ?>" type="number" class="rating" min="0" max="5" step="0.5" data-size="xs" data-symbol="★" data-show-caption="false" data-show-clear="false">
			</div>
		</div>
	</div>
	<?php endforeach; ?>
</div>

<script>
$("input").on('rating.change', function(event, value, caption) { 
	jQuery.ajax({
		type: "POST",
		url: 'inc/ajax.php',
		dataType: 'json',
		data: {functionname: 'vote', arguments: [this.id, value]}
		});
	} );
</script>
<?php include('inc/footer.php');?>