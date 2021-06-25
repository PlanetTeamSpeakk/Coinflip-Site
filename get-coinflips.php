<?php
header("Content-Type: application/json;");
setlocale(LC_MONETARY, "en_GB.utf8");

$db = mysqli_connect();
$db->select_db("coinflip");


$coinflipCount = $db->query("SELECT count(*) FROM coinflips;")->fetch_array()[0];
// Create temp rank table to get users' rank.
$db->query("CREATE TEMPORARY TABLE IF NOT EXISTS userRanks (PRIMARY KEY (id), INDEX(rank)) AS (SELECT id, ROW_NUMBER() OVER (ORDER BY balance DESC) rank FROM users);");

$query = "SELECT id, user, (SELECT username FROM users WHERE id=user) AS username, (SELECT rank FROM userRanks WHERE id=user) AS userrank, side, bet, UNIX_TIMESTAMP(created) AS created FROM coinflips";
if (isset($_GET["sort"]) && in_array($_GET["sort"], array("username", "side", "bet", "created"))) $query = $query." ORDER BY ".$_GET["sort"]." ".$_GET["order"];
if (isset($_GET["limit"]) && ctype_digit($_GET["limit"])) {
	$query = $query." LIMIT ".$_GET["limit"];
	if (isset($_GET["offset"]) && ctype_digit($_GET["offset"])) $query = $query." OFFSET ".$_GET["offset"];
}


$result = $db->query($query)->fetch_all(MYSQLI_ASSOC);
foreach ($result as &$row) {
	$row["created"] = formatTime($row["created"]);
	$row["bet"] = "$".substr(money_format("%.0i", $row["bet"]), 3);
}

echo json_encode(array("total" => $coinflipCount, "totalNotFiltered" => $coinflipCount, "rows" => $result), JSON_NUMERIC_CHECK | JSON_PRETTY_PRINT);

function formatTime($epoch) {
	$now = time();
	if ($now - $epoch < 60) return ($now - $epoch)." second".($now - $epoch == 1 ? "" : "s")." ago";
	else if ($now - $epoch < 3600) return intval(($now - $epoch) / 60)." minute".($now - $epoch < 120 ? "" : "s")." ago";
	else if ($now - $epoch < 86400) return intval(($now - $epoch) / 3600)." hour".($now - $epoch < 7200 ? "" : "s")." ago";
	else return (new DateTime())->setTimestamp($epoch)->format("d-m-Y");
}