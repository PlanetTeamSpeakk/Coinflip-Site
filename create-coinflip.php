<?php
if (isset($_POST["side"]) && ($_POST["side"] == "HEADS" || $_POST["side"] == "TAILS") && isset($_POST["bet"]) && isset($_POST["sessionId"])) {
	$side = $_POST["side"];
	$bet = intval($_POST["bet"]);
	
	$db = mysqli_connect();
	$db->select_db("coinflip");
	
	$stmt = $db->prepare("SELECT * FROM sessions WHERE id=? AND expires>CURRENT_TIMESTAMP();");
	$stmt->bind_param("s", $_POST["sessionId"]);
	$stmt->execute();
	$result = $stmt->get_result()->fetch_assoc();
	
	if ($result) {
		$stmt = $db->prepare("SELECT balance FROM users WHERE id=?;");
		$stmt->bind_param("i", $result["user"]);
		$stmt->execute();
		$bal = $stmt->get_result()->fetch_assoc()["balance"];
		
		if ($bal >= $bet) {
			$stmt = $db->prepare("UPDATE users SET balance=balance-? WHERE id=?;");
			$stmt->bind_param("ii", $bet, $result["user"]);
			
			// Make sure to only add the coinflip if removing the balance was successful.
			if ($stmt->execute()) {
				$stmt = $db->prepare("INSERT INTO coinflips (user, side, bet) VALUES (?, ?, ?);");
				$stmt->bind_param("isi", $result["user"], $side, $bet);
				if ($stmt->execute()) {
					echo "ok";
					exit();
				} else echo "coinflip creation unsuccessful";
			} else echo "bal removal unsuccessful";
		} else echo "bal too low ".$bal." ".$bet;
	} else echo "result null";
}

echo "not ok";