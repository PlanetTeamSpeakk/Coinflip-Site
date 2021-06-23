<?php
header("Content-Type: application/json;");
setlocale(LC_MONETARY, "en_GB.utf8");

$db = mysqli_connect();
$db->select_db("coinflip");


$coinflipCount = $db->query("SELECT count(*) FROM coinflips;")->fetch_array()[0];
$usersRaw = $db->query("SELECT id, username FROM users;")->fetch_all(MYSQLI_ASSOC);
$users = array();
foreach ($usersRaw as &$user)
	$users[$user["id"]] = $user["username"];

$query = "SELECT * FROM coinflips";
if (isset($_GET["sort"]) && in_array($_GET["sort"], array("user", "side", "bet", "created"))) $query = $query." ORDER BY ".$_GET["sort"]." ".$_GET["order"];
if (isset($_GET["limit"]) && ctype_digit($_GET["limit"])) {
	$query = $query." LIMIT ".$_GET["limit"];
	if (isset($_GET["offset"]) && ctype_digit($_GET["offset"])) $query = $query." OFFSET ".$_GET["offset"];
}

$resultRaw = $db->query($query)->fetch_all(MYSQLI_ASSOC);
$rows = array();
foreach ($resultRaw as &$row)
	array_push($rows, array("id" => $row["id"], "user" => $users[$row["user"]], "side" => $row["side"], "bet" => "$".substr(money_format("%.0i", $row["bet"]), 3), "created" => formatTime(strtotime($row["created"])), "epoch" => strtotime($row["created"])));


$output = array("total" => $coinflipCount, "totalNotFiltered" => $coinflipCount, "rows" => $rows);
echo json_encode($output);

function formatTime($epoch) {
	$now = time();
	if ($now - $epoch < 60) return ($now - $epoch)." second".($now - $epoch == 1 ? "" : "s")." ago";
	else if ($now - $epoch < 3600) return intval(($now - $epoch) / 60)." minute".($now - $epoch < 120 ? "" : "s")." ago";
	else if ($now - $epoch < 86400) return intval(($now - $epoch) / 3600)." hour".($now - $epoch < 7200 ? "" : "s")." ago";
	else return (new DateTime())->setTimestamp(strtotime($epoch))->format("H:i:s d-m-Y");
}