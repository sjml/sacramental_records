<?php

$db = new SQLite3("./sacraments.db");

$column_res = $db->query("PRAGMA table_info(sacraments)");
$column_list = [];
while ($col = $column_res->fetchArray(SQLITE3_ASSOC)) {
	$column_list[] = $col["name"];
}

$limit = isset($_GET["limit"]) ? (int)$_GET["limit"] : 50;
$page = isset($_GET["page"]) ? (int)$_GET["page"] : 1;
$offset = ($page - 1) * $limit;
$search = isset($_GET["search"]) ? $_GET["search"] : "";
$sacrament_filter = isset($_GET["sacrament_filter"]) ? $_GET["sacrament_filter"] : "";
$sort = (isset($_GET["sort"]) && in_array($_GET["sort"], $column_list)) ? $_GET["sort"] : "id";
$order = (isset($_GET["order"]) && strtolower($_GET["order"]) === "asc") ? "asc" : "desc";

$conditions = ["1=1"];
if ($search !== "") {
	$conditions[] = "(date LIKE :s OR name_number LIKE :s OR location LIKE :s OR notes LIKE :s)";
}
if ($sacrament_filter !== "") {
	$conditions[] = "sacrament = :sac";
}

$search_clause = implode(" AND ", $conditions);

$count_stmt = $db->prepare("SELECT COUNT(*) as total FROM sacraments WHERE $search_clause");
if ($search !== "") {
	$count_stmt->bindValue(":s", "%$search%", SQLITE3_TEXT);
}
if ($sacrament_filter !== "") {
	$count_stmt->bindValue(":sac", $sacrament_filter, SQLITE3_TEXT);
}
$total_rows = $count_stmt->execute()->fetchArray(SQLITE3_ASSOC)["total"];
$total_pages = ceil($total_rows / $limit);

$query = "SELECT * FROM sacraments WHERE $search_clause ORDER BY $sort $order LIMIT :limit OFFSET :offset";
$stmt = $db->prepare($query);
if ($search !== "") {
	$stmt->bindValue(":s", "%$search%", SQLITE3_TEXT);
}
if ($sacrament_filter !== "") {
	$stmt->bindValue(":sac", $sacrament_filter, SQLITE3_TEXT);
}
$stmt->bindValue(":limit", $limit, SQLITE3_INTEGER);
$stmt->bindValue(":offset", $offset, SQLITE3_INTEGER);
$res = $stmt->execute();

function generateSortUrlParam($col, $current_sort, $current_order) {
	$new_order = ($col === $current_sort && $current_order === "desc") ? "asc" : "desc";
	$params = $_GET;
	$params["sort"] = $col;
	$params["order"] = $new_order;
	$params["page"] = 1;
	return "?" . http_build_query($params);
}

function getSortIcon($col, $current_sort, $current_order) {
	if ($col !== $current_sort) {
		return "";
	}
	return $current_order === "asc" ? " ▲" : " ▼";
}

function generateFilterUrl($value, $type = "search") {
	$params = $_GET;
	if ($type === "sacrament") {
			$params["sacrament_filter"] = $value;
		}
		else {
			$params["search"] = $value;
		}
	$params["page"] = 1; // reset on new search
	return "?" . http_build_query($params);
}

function clean($str) {
	return htmlspecialchars($str ?? "");
}

function renderPagination($page, $total_pages, $total_rows) {
	?>
	<div class="pagination">
		<div><?= $total_rows ?> records found.</div>
		<div>(<a href="./">return to entry</a>)</div>
		<div>
			<?php if ($page > 1): ?>
				<a href="?<?= http_build_query(array_merge($_GET, ["page" => $page - 1])) ?>">Previous</a>
			<?php endif; ?>

			<span>Page <?= $page ?> of <?= $total_pages ?></span>

			<?php if ($page < $total_pages): ?>
				<a href="?<?= http_build_query(array_merge($_GET, ["page" => $page + 1])) ?>">Next</a>
			<?php endif; ?>
		</div>
	</div>
	<?php
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<title>Sacramental Records</title>

	<link rel="stylesheet" href="./style.css">
</head>
<body>
	<h1>Sacramental Records</h1>
	<form method="get">
		<input type="text" name="search" placeholder="Search all fields..." value="<?= clean($search) ?>">

		<select name="sacrament_filter" onchange="this.form.submit();">
			<option value="">-- All Sacraments --</option>
			<?php
				$sacraments = ["Mass", "Baptism", "Marriage", "Anointing", "Confession", "Confirmation"];
				foreach ($sacraments as $sac): ?>
					<option value="<?= $sac ?>" <?= $sacrament_filter === $sac ? "selected" : "" ?>><?= $sac ?></option>
				<?php endforeach; ?>
		</select>

		<label>Show:</label>
		<select name="limit" onchange="this.form.submit();">
			<option value="25" <?= $limit == 25 ? "selected" : "" ?>>25</option>
			<option value="50" <?= $limit == 50 ? "selected" : "" ?>>50</option>
			<option value="100" <?= $limit == 100 ? "selected" : "" ?>>100</option>
			<option value="500" <?= $limit == 500 ? "selected" : "" ?>>500</option>
		</select>

		<input type="hidden" name="sort" value="<?= clean($sort) ?>">
		<input type="hidden" name="order" value="<?= clean($order) ?>">

		<button type="submit">Search</button>
		<a href="table.php" class="clear-link">Clear</a>
	</form>

	<?php renderPagination($page, $total_pages, $total_rows); ?>

	<table class="sacraments-table">
		<thead>
			<tr>
				<th><a href="<?= generateSortUrlParam("date", $sort, $order) ?>">Date<?= getSortIcon("date", $sort, $order) ?></a></th>
				<th><a href="<?= generateSortUrlParam("sacrament", $sort, $order) ?>">Sacrament<?= getSortIcon("sacrament", $sort, $order) ?></a></th>
				<th><a href="<?= generateSortUrlParam("name_number", $sort, $order) ?>">Name / Number<?= getSortIcon("name_number", $sort, $order) ?></a></th>
				<th><a href="<?= generateSortUrlParam("location", $sort, $order) ?>">Location<?= getSortIcon("location", $sort, $order) ?></a></th>
				<th><a href="<?= generateSortUrlParam("notes", $sort, $order) ?>">Notes<?= getSortIcon("notes", $sort, $order) ?></a></th>
			</tr>
		</thead>
		<tbody>
			<?php
				while ($row = $res->fetchArray(SQLITE3_ASSOC)):
			?>
				<tr>
					<td><?= clean($row["date"]) ?></td>
					<td>
						<a href="<?= generateFilterUrl($row["sacrament"], "sacrament") ?>"><?= clean($row["sacrament"]) ?></a>
					</td>
					<td><?= clean($row["name_number"]) ?></td>
					<td>
						<a href="<?= generateFilterUrl($row["location"], "search") ?>"><?= clean($row["location"]) ?></a>
					</td>
					<td><?= clean($row["notes"]) ?></td>
				</tr>
			<?php
				endwhile;
			?>
		</tbody>
	</table>

	<?php renderPagination($page, $total_pages, $total_rows); ?>
</body>
