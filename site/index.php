<?php
	$db = new SQLite3("./sacraments.db");
?>
<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<title>Sacramental Record Entry</title>

	<link rel="stylesheet" href="./style.css">
</head>
<body>
	<h1>Sacramental Record Entry</h1>
	<datalist id="location_set">
		<?php
			$res = $db->query("SELECT DISTINCT location FROM sacraments");
			while ($row = $res->fetchArray(SQLITE3_ASSOC)) {
				if ($row["location"] !== null) {
					echo "<option>" . $row["location"] . "</option>" . PHP_EOL;
				}
			}
		?>
	</datalist>
	<form action="submit.php" method="post">
		<div id="row-container">
			<div class="entry-row">
				<input type="date" name="sac-date[]" class="sac-date" required>
				<select name="sac-type[]" class="sac-type" required>
					<option></option>
					<option>Mass</option>
					<option>Baptism</option>
					<option>Marriage</option>
					<option>Anointing</option>
					<option>Confession</option>
					<option>Confirmation</option>
				</select>
				<input type="text" name="sac-name-or-number[]" class="sac-name-or-number" placeholder="Name or Number">
				<input type="text" name="sac-location[]" class="sac-location" id="sac-location__1" placeholder="Location" required list="location_set">
				<input type="text" name="sac-notes[]" class="sac-notes" placeholder="Notes">
				<button type="button" class="remove-row" style="display:none;">✕</button>
			</div>
		</div>

		<div class="controls">
			<button type="button" id="add-row">+</button>
			<input type="submit" value="Submit">
		</div>
	</form>

	<p class="table-label">(Last 50 entries, reverse ordered by time; <a href="./table.php">view full data</a>)</p>
	<table class="sacraments-table">
		<thead>
			<tr>
				<th>Date</th>
				<th>Sacrament</th>
				<th>Name / Number</th>
				<th>Location</th>
				<th>Notes</th>
			</tr>
		</thead>
		<tbody>
			<?php
				$res = $db->query("SELECT * FROM sacraments ORDER BY id DESC LIMIT 50");
				while ($row = $res->fetchArray(SQLITE3_ASSOC)) {
					echo "<tr>";
					echo "<td>" . $row["date"] . "</td><td>" . $row["sacrament"] . "</td><td>" . $row["name_number"] . "</td><td>" . $row["location"] . "</td><td>" . $row["notes"] . "</td>";
					echo "</tr>";
				}
			?>
		</tbody>
	</table>

	<script src="./sacraments.js"></script>
</body>
</html>
