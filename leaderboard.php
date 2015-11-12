<?php include('inc/header.php');?>
<?php
$sql = <<<SQL
SELECT * FROM `programmes`
ORDER BY `rating` DESC, `name` ASC
SQL;

if(!$programmes = $database->query($sql)){
    die('There was an error running the query [' . $db->error . ']');
}

$leaderboard_last_rank = 1;
$leaderboard_last_rating = -1
?>

<table class="table table-striped leaderboard-item">
	<thead>
	  <tr>
		<th class="col-sm-1">#</th>
		<th class="col-sm-2">Programme</th>
		<th class="col-sm-10"></th>
		<th class="col-sm-2">Points</th>
	  </tr>
	</thead>
	<tbody>
	  <?php foreach($programmes as $i => $item): ?>
	  <tr class="leaderboard-entry">
		<?php 
		# If the rating is the same, we assign the same rank
		if ( ($leaderboard_last_rating != -1) && ($item["rating"] == $leaderboard_last_rating) ) {
			echo '<td style="vertical-align:middle">'.($leaderboard_last_rank).'</td>';
		} else {
			echo '<td style="vertical-align:middle">'.($i+1).'</td>';
			$leaderboard_last_rank = $i+1;
		}
		$leaderboard_last_rating = $item["rating"]
		?>
		<td style="vertical-align:middle"><img src="<?php echo $item["image_wide_url"] ?>" alt="<?php echo $item["name"] ?>" height="100"></td>
		<td style="vertical-align:middle"><?php echo $item["name"] ?></td>
		<td style="vertical-align:middle"><?php echo $item["rating"] ?></td>
	  </tr>
	  <?php endforeach; ?>
	</tbody>
</table>

<?php include('inc/footer.php');?>