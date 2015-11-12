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
?>

<div class="row">
	<?php foreach($programmes as $item): ?>
	<div class="col-lg-2 col-sm-3 col-xs-4">
		<div class="main-grid-item">
			<img src="<?php echo $item["image_url"]; ?>" alt="<?php echo $item["description"]; ?>" height="180" data-toggle="tooltip" data-placement="right" title="<?php echo $item["description"]; ?>">
			<div class="main-grid-item-name">
				<?php echo $item["name"]; ?>
				<input id="<?php echo $item["id"]; ?>" value="<?php echo $m_user_votes[$item["id"]]; ?>" type="number" class="rating" min="0" max="5" step="0.5" data-size="xs" data-symbol="★" data-show-caption="false" data-show-clear="false">
			</div>
		</div>
	</div>
	<?php endforeach; ?>
</div>

<script>
//https://stackoverflow.com/questions/15757750/php-function-call-using-javascript
$("input").on('rating.change', function(event, value, caption) { 
	console.log(value)
	jQuery.ajax({
		type: "POST",
		url: 'inc/submit_vote.php',
		dataType: 'json',
		data: {functionname: 'vote', arguments: [this.id, value]},
	} );
</script>
<?php include('inc/footer.php');?>