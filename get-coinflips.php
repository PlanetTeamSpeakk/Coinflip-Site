<?php
header("Content-Type: application/json;");
setlocale(LC_MONETARY, "en_GB.utf8");

$db = mysqli_connect();
$db->select_db("coinflip");


$coinflipCount = $db->query("SELECT count(*) FROM coinflips LIMIT 10 OFFSET ".strval(isset($_GET["offset"]) && is_numeric($_GET["offset"]) ? $_GET["offset"] : 0).";")->fetch_array()[0];
$usersRaw = $db->query("SELECT id, username FROM users;")->fetch_all(MYSQLI_ASSOC);
$users = array();
foreach ($usersRaw as &$user)
	$users[$user["id"]] = $user["username"];

$resultRaw = $db->query("SELECT * FROM coinflips;")->fetch_all(MYSQLI_ASSOC);
$rows = array();
foreach ($resultRaw as &$row)
	array_push($rows, array("id" => $row["id"], "user" => $users[$row["user"]], "side" => $row["side"], "bet" => "$".substr(money_format("%.0i", $row["bet"]), 3), "created" => formatTime(strtotime($row["created"])), "epoch" => strtotime($row["created"])));


$output = array("total" => $coinflipCount, "totalNotFiltered" => $coinflipCount, "rows" => $rows);
echo json_encode($output);

function formatTime($epoch) {
	$now = time();
	if ($now - $epoch < 60) return ($now - $epoch)." seconds ago";
	else if ($now - $epoch < 3600) return intval(($now - $epoch) / 60)." minutes ago";
	else if ($now - $epoch < 86400) return intval(($now - $epoch) / 3600)." hours ago";
	else return (new DateTime())->setTimestamp(strtotime($epoch))->format("H:i:s d-m-Y");
}