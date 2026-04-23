<?php
	$db = new SQLite3("./sacraments.db");
?>

<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<title>Sacramental Record Entry</title>

	<script src="./lib/autoComplete.js-10.2.10/dist/autoComplete.min.js"></script>
	<link rel="stylesheet" href="./style.css">
</head>
<body>
	<h1>Sacramental Record Entry</h1>
	<form action="submit.php" method="post">
		<input type="date" name="sac-date" id="sac-date">
		<select name="sac-type" id="sac-type" required>
			<option value=""></option>
			<option value="mass">Mass</option>
			<option value="baptism">Baptism</option>
			<option value="marriage">Marriage</option>
			<option value="anointing">Anointing</option>
			<option value="confession">Confession</option>
			<option value="confirmation">Confirmation</option>
		</select>
		<input type="text" name="sac-name-or-number" id="sac-name-or-number" placeholder="Name or Number">
		<input type="text" name="sac-location" id="sac-location" placeholder="Location" required>
		<input type="text" name="sac-notes" id="sac-notes" placeholder="Notes">
		<input type="submit" value="Submit">
	</form>

	<table>
		<tr>
			<th>Date</th>
			<th>Sacrament</th>
			<th>Name / Number</th>
			<th>Location</th>
			<th>Notes</th>
		</tr>
		<?php
			$res = $db->query("SELECT * FROM sacraments ORDER BY date DESC LIMIT 50");
			while ($row = $res->fetchArray(SQLITE3_ASSOC)) {
				echo "<tr>";
				echo "<td>" . $row["date"] . "</td><td>" . $row["sacrament"] . "</td><td>" . $row["name_number"] . "</td><td>" . $row["location"] . "</td><td>" . $row["notes"] . "</td>";
				echo "</tr>";
			}
		?>
	</table>


	<script>
		const datePicker = document.getElementById("sac-date");
		datePicker.valueAsDate = new Date(); // set to today

		const sacPicker = document.getElementById("sac-type");
		const sacData = document.getElementById("sac-name-or-number");
		sacPicker.onchange = (evt) => {
			if (evt.target.value === "confession") {
				sacData.type = "number";
				sacData.min = 1;
				sacData.value = 1;
			}
			else {
				sacData.type = "text";
				sacData.min = null;
				sacData.value = "";
			}
		};

		const autoCompleteEngine = new autoComplete({
			data: {
				src: [
					<?php
					$res = $db->query("SELECT DISTINCT location FROM sacraments");
					$locations = [];
					while ($row = $res->fetchArray(SQLITE3_ASSOC)) {
						if ($row["location"] !== null) {
							$locations[] = $row["location"];
						}
					}
					foreach($locations as $loc) {
						echo '"' . $loc . '",' . PHP_EOL;
					}?>
				]
			},
			resultItem: {
				highlight: true,
			},
			selector: "#sac-location",
			events: {
				input: {
					selection: (event) => {
						const selection = event.detail.selection.value;
						autoCompleteEngine.input.value = selection;
					}
				}
			}
		});
	</script>
</body>
</html>
