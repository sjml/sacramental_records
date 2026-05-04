<?php

$db = new SQLite3("./sacraments.db");

$dates = $_POST["sac-date"] ?? [];
$types = $_POST["sac-type"] ?? [];
$names_or_numbers = $_POST["sac-name-or-number"] ?? [];
$locations = $_POST["sac-location"] ?? [];
$noteses = $_POST["sac-notes"] ?? [];


if (count($dates) > 0) {
	$stmt = $db->prepare("INSERT INTO sacraments (date, sacrament, name_number, location, notes)
						  VALUES(:date, :type, :name_or_number, :location, :notes)"
						);

	$db->exec("BEGIN");

	try {
		for ($i = 0; $i < count($dates); $i++) {
			$date = $dates[$i];
			$type = $types[$i];
			$name_or_number = $names_or_numbers[$i];
			$location = $locations[$i];
			$notes = $noteses[$i];

			$stmt->bindValue(":date", $date, SQLITE3_TEXT);
			$stmt->bindValue(":type", $type, SQLITE3_TEXT);
			$stmt->bindValue(":location", $location, SQLITE3_TEXT);

			if ($type == "Confession") {
				$stmt->bindValue(":name_or_number", $name_or_number, SQLITE3_INTEGER);
			}
			elseif (strlen(trim($name_or_number)) == 0) {
				$stmt->bindValue(":name_or_number", null, SQLITE3_NULL);
			}
			else {
				$stmt->bindValue(":name_or_number", $name_or_number, SQLITE3_TEXT);
			}

			if (strlen(trim($notes)) == 0) {
				$stmt->bindValue(":notes", null, SQLITE3_NULL);
			}
			else {
				$stmt->bindValue(":notes", $notes, SQLITE3_TEXT);
			}

			$stmt->execute();
		}

		$db->exec("COMMIT");

		$count_results = $db->query("SELECT sacrament, COUNT(*) as count FROM sacraments GROUP BY sacrament");
		$stats = [];
		while ($row = $count_results->fetchArray(SQLITE3_ASSOC)) {
			$stats[$row["sacrament"]] = $row["count"];
		}

		file_put_contents("counts.json", json_encode($stats, JSON_PRETTY_PRINT));
	}
	catch (Exception $e) {
		$db->exec("ROLLBACK");
		die("Error saving records: " . $e->getMessage());
	}
}

header("Location: ./");
exit;
