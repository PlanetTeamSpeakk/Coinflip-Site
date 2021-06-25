<?php
if (isset($_POST["id"]) && ctype_digit($_POST["id"]) && isset($_POST["sessionId"])) {
	$db = mysqli_connect();
	$db->select_db("coinflip");
	
	$cf = $db->query("SELECT * FROM coinflips WHERE id=".$_POST["id"])->fetch_assoc();
	$stmt = $db->prepare("SELECT * FROM sessions WHERE id=?");
	$stmt->bind_param("s", $_POST["sessionId"]);
	$stmt->execute();
	$session = $stmt->get_result()->fetch_assoc();
	$user = $db->query("SELECT id, balance FROM users WHERE id=".$session["user"])->fetch_assoc();
	
	if ($cf && $session && $cf["user"] != $session["user"] && $cf["bet"] <= $user["balance"]) {
		// If 0, HEADS wins, otherwise TAILS wins.
		$win = random_int(0, 1);
		$won = $win == 0 && $cf["side"] == "HEADS" || $win == 1 && $cf["side"] == "TAILS";
		error_log("win: ".$win." ".$won." ".$cf["side"]);
		error_log("UPDATE users SET balance=balance+".strval($cf["bet"])." WHERE id=".($won ? $cf["user"] : $session["user"]));
		$db->query("UPDATE users SET balance=balance+".strval($cf["bet"]*2)." WHERE id=".($won ? $cf["user"] : $session["user"]));
		$db->query("DELETE FROM coinflips WHERE id=".$cf["id"]);
		$stmt = $db->prepare("INSERT INTO transactions (user, opponent, side, bet, won) VALUES (?, ?, ?, ?, ?), (?, ?, ?, ?, ?);");
		$side = $cf["side"] == "HEADS" ? "TAILS" : "HEADS"; // CaNnOt PaSs PaRaMeTeR 4 bY rEfErEnCe
		$won0 = !$won; // See above comment but for parameter 6
		$stmt->bind_param("iisiiiisii", $session["user"], $cf["user"], $side, $cf["bet"], $won0, $cf["user"], $session["user"], $cf["side"], $cf["bet"], $won);
		$stmt->execute();
		echo $win == 0 ? "HEADS" : "TAILS";
		exit();
	}
}

echo "not ok";