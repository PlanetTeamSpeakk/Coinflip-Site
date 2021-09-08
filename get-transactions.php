<?php
header("Content-Type: application/json;");
setlocale(LC_MONETARY, "en_GB.utf8");

if (isset($_GET["sessionId"])) {
	$db = mysqli_connect();
	$db->select_db("coinflip");
	
	$stmt = $db->prepare("SELECT * FROM sessions WHERE id=?;");
	$stmt->bind_param("s", $_GET["sessionId"]);
	$stmt->execute();
	$session = $stmt->get_result()->fetch_assoc();
	
	if ($session) {
		// Create temp rank table to get users' rank.
		$db->query("CREATE TEMPORARY TABLE IF NOT EXISTS userRanks (PRIMARY KEY (id), INDEX(rank)) AS (SELECT id, ROW_NUMBER() OVER (ORDER BY balance DESC) rank FROM users);");
		
		$query = "SELECT id, user, opponent, (SELECT username FROM users WHERE id=opponent) AS opponentName, (SELECT rank FROM userRanks WHERE id=opponent) AS opponentRank, side, bet, won, UNIX_TIMESTAMP(date) AS date FROM transactions WHERE user=".$session["user"];
		if (isset($_GET["sort"]) && in_array($_GET["sort"], array("opponentName", "side", "bet", "date", "won"))) $query = $query." ORDER BY ".$_GET["sort"]." ".$_GET["order"];
		if (isset($_GET["limit"]) && ctype_digit($_GET["limit"])) {
			$query = $query." LIMIT ".$_GET["limit"];
			if (isset($_GET["offset"]) && ctype_digit($_GET["offset"])) $query = $query." OFFSET ".$_GET["offset"];
		}
		
		$count = $db->query("SELECT count(*) FROM transactions WHERE user=".$session["user"])->fetch_array()[0];
		$result = $db->query($query)->fetch_all(MYSQLI_ASSOC);
		
		foreach ($result as &$row) {
			$row["date"] = formatTime($row["date"]);
			$row["bet"] = "$".substr(money_format("%.0i", $row["bet"]), 3);
			$row["won"] = $row["won"] ? "Yes" : "No";
		}
		
		echo json_encode(array("total" => $count, "totalNotFiltered" => $count, "rows" => $result), JSON_NUMERIC_CHECK | JSON_PRETTY_PRINT);
		exit();
	}
}

echo "not ok";

function formatTime($epoch) {
	$now = time();
	if ($now - $epoch < 60) return ($now - $epoch)." second".($now - $epoch == 1 ? "" : "s")." ago";
	else if ($now - $epoch < 3600) return intval(($now - $epoch) / 60)." minute".($now - $epoch < 120 ? "" : "s")." ago";
	else if ($now - $epoch < 86400) return intval(($now - $epoch) / 3600)." hour".($now - $epoch < 7200 ? "" : "s")." ago";
	else return (new DateTime())->setTimestamp($epoch)->format("d-m-Y");
}