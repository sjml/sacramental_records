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
		<div id="row-container">
			<div class="entry-row">
				<input type="date" name="sac-date[]" class="sac-date" required>
				<select name="sac-type[]" class="sac-type" required>
					<option value=""></option>
					<option value="Mass">Mass</option>
					<option value="Baptism">Baptism</option>
					<option value="Marriage">Marriage</option>
					<option value="Anointing">Anointing</option>
					<option value="Confession">Confession</option>
					<option value="Confirmation">Confirmation</option>
				</select>
				<input type="text" name="sac-name-or-number[]" class="sac-name-or-number" placeholder="Name or Number">
				<input type="text" name="sac-location[]" class="sac-location" id="sac-location__1" placeholder="Location" required>
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


	<script>
		function setInitialdate(el) {
			el.valueAsDate = new Date();
		}
		setInitialdate(document.querySelector(".sac-date"));

		const rowContainer = document.getElementById("row-container");
		const addButton = document.getElementById("add-row");

		// should always be 1, but let's count for funsies
		let rowCount = document.querySelectorAll(".entry-row").length;

		// toggle to numbers if sacrament is confession
		rowContainer.addEventListener("change", evt => {
			if (evt.target.classList.contains("sac-type")) {
				const nameInput = evt.target.closest(".entry-row").querySelector(".sac-name-or-number");
				if (evt.target.value === "Confession") {
					nameInput.type = "number";
					nameInput.min = 1;
					nameInput.value = 1;
				}
				else {
					nameInput.type = "text";
					nameInput.min = null;
					nameInput.value = "";
				}
			}
		});

		addButton.onclick = () => {
			const rows = document.querySelectorAll(".entry-row");
			const lastRow = rows[rows.length - 1];
			const newRow = lastRow.cloneNode(true);

			rowCount++;

			const locationField = newRow.querySelector(".sac-location");
			locationField.id = `sac-location__${rowCount}`;

			// keep date, type, and location; clear out value and notes
			//    (this is basically for when I'm doing multiple baptisms)
			const lastType = lastRow.querySelector(".sac-type").value;
			newRow.querySelector(".sac-type").value = lastType;

			const newNameOrNumber = newRow.querySelector(".sac-name-or-number");
			if (newNameOrNumber.type === "number") {
				newNameOrNumber.value = 1;
			}
			else {
				newNameOrNumber.value = "";
			}
			newRow.querySelector("input[name='sac-notes[]']").value = "";
			newRow.querySelector(".remove-row").style.display = "inline-block";

			rowContainer.appendChild(newRow);

			initAutoComplete(locationField);

			newNameOrNumber.focus();
		};

		rowContainer.onclick = (evt) => {
			if (evt.target.classList.contains("remove-row")) {
				evt.target.closest(".entry-row").remove();
			}
		};


		const locationData = [
			<?php
				$res = $db->query("SELECT DISTINCT location FROM sacraments");
				while ($row = $res->fetchArray(SQLITE3_ASSOC)) {
					if ($row["location"] !== null) {
						echo '"' . addslashes($row["location"]) . '",' . PHP_EOL;
					}
				}
			?>
		];

		function initAutoComplete(targetElement) {
			const ace = new autoComplete({
				data: {
					src: locationData,
				},
				resultItem: {
					highlight: true,
				},
				selector: `#${targetElement.id}`,
				events: {
					input: {
						selection: (evt) => {
							const sel = evt.detail.selection.value;
							ace.input.value = sel;
						},
						results: (evt) => {
							const list = targetElement.nextElementSibling;
							if (list) {
								const rect = targetElement.getBoundingClientRect();
								const available = window.innerWidth - rect.left - 25;
								list.style.maxWidth = `${available}px`;
							}
						}
					},
				}
			});
		}

		// set up the first row
		initAutoComplete(document.querySelector("#sac-location__1"));
	</script>
</body>
</html>
