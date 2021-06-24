<?php
header("Content-Type: application/json;");
setlocale(LC_MONETARY, "en_GB.utf8");

$db = mysqli_connect();
$db->select_db("coinflip");


$coinflipCount = $db->query("SELECT count(*) FROM coinflips;")->fetch_array()[0];
// Create temp rank table to get users' rank.
$db->query("CREATE TEMPORARY TABLE IF NOT EXISTS userRanks (PRIMARY KEY (id), INDEX(rank)) AS (SELECT id, ROW_NUMBER() OVER (ORDER BY balance DESC) rank FROM users);");

$query = "SELECT * FROM coinflips";
if (isset($_GET["sort"]) && in_array($_GET["sort"], array("user", "side", "bet", "created"))) $query = $query." ORDER BY ".$_GET["sort"]." ".$_GET["order"];
if (isset($_GET["limit"]) && ctype_digit($_GET["limit"])) {
	$query = $query." LIMIT ".$_GET["limit"];
	if (isset($_GET["offset"]) && ctype_digit($_GET["offset"])) $query = $query." OFFSET ".$_GET["offset"];
}

$resultRaw = $db->query($query)->fetch_all(MYSQLI_ASSOC);

// Only select the users that have active coinflips and include their rank on the leaderboard.
$query = "SELECT id as pid, username, (SELECT rank FROM userRanks WHERE id=pid) AS rank FROM users WHERE id=";
foreach ($resultRaw as &$row)
	$query = $query.$row["user"]." OR id=";
$query = substr($query, 0, strlen($query)-7)." ORDER BY balance DESC;";

$usersRaw = $db->query($query)->fetch_all(MYSQLI_ASSOC);
$users = array();
foreach ($usersRaw as &$user)
	$users[$user["pid"]] = array("username" => $user["username"], "rank" => $user["rank"]);

	
$rows = array();
foreach ($resultRaw as &$row)
	array_push($rows, array("id" => $row["id"], "user" => $users[$row["user"]]["username"], "userrank" => $users[$row["user"]]["rank"], "side" => $row["side"], "bet" => "$".substr(money_format("%.0i", $row["bet"]), 3), "created" => formatTime(strtotime($row["created"])), "epoch" => strtotime($row["created"])));


$output = array("total" => $coinflipCount, "totalNotFiltered" => $coinflipCount, "rows" => $rows);
echo json_encode($output);

function formatTime($epoch) {
	$now = time();
	if ($now - $epoch < 60) return ($now - $epoch)." second".($now - $epoch == 1 ? "" : "s")." ago";
	else if ($now - $epoch < 3600) return intval(($now - $epoch) / 60)." minute".($now - $epoch < 120 ? "" : "s")." ago";
	else if ($now - $epoch < 86400) return intval(($now - $epoch) / 3600)." hour".($now - $epoch < 7200 ? "" : "s")." ago";
	else return (new DateTime())->setTimestamp($epoch)->format("H:i:s d-m-Y");
}