<?php
if (isset($_POST["id"]) && ctype_digit($_POST["id"]) && isset($_POST["sessionId"])) {
	$db = mysqli_connect();
	$db->select_db("coinflip");
	
	$stmt = $db->prepare("SELECT * FROM sessions WHERE id=? AND expires>CURRENT_TIMESTAMP();");
	$stmt->bind_param("s", $_POST["sessionId"]);
	$stmt->execute();
	$result = $stmt->get_result()->fetch_assoc();
	
	if ($result) {
		$cf = $db->query("SELECT * FROM coinflips WHERE id=".$_POST["id"])->fetch_assoc();
		if ($cf) {
			$stmt = $db->prepare("DELETE FROM coinflips WHERE id=? AND user=?;");
			$stmt->bind_param("ii", $_POST["id"], $result["user"]);
			$stmt->execute();
			
			$stmt = $db->prepare("UPDATE users SET balance=balance+? WHERE id=?;");
			$stmt->bind_param("ii", $cf["bet"], $result["user"]);
			$stmt->execute();
			
			if ($db->affected_rows > 0) {
				echo "ok";
				exit();
			}
		}
	}
}

echo "not ok";
