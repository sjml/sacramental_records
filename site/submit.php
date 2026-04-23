<?php

$db = new SQLite3("./sacraments.db");

$date = $_POST["sac-date"];
$type = $_POST["sac-type"];
$name_or_number = $_POST["sac-name-or-number"];
$location = $_POST["sac-location"];
$notes = $_POST["sac-notes"];

$stmt = $db->prepare("INSERT INTO sacraments (date, sacrament, name_number, location, notes) VALUES(:date, :type, :name_or_number, :location, :notes)");
$stmt->bindValue(":date", $date, SQLITE3_TEXT);
$stmt->bindValue(":type", $type, SQLITE3_TEXT);
if ($type == "Confession") {
	$stmt->bindValue(":name_or_number", $name_or_number, SQLITE3_INTEGER);
}
elseif (strlen($type) == 0) {
	$stmt->bindValue(":name_or_number", null, SQLITE3_NULL);
}
else {
	$stmt->bindValue(":name_or_number", $name_or_number, SQLITE3_TEXT);
}
$stmt->bindValue(":location", $location, SQLITE3_TEXT);
if (strlen($notes) == 0) {
	$stmt->bindValue(":notes", null, SQLITE3_NULL);
}
else {
	$stmt->bindValue(":notes", $notes, SQLITE3_TEXT);
}

$stmt->execute();

header("Location: ./");
