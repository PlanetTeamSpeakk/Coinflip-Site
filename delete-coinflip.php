<?php
if (isset($_POST["id"]) && isset($_POST["sessionId"])) {
	$db = mysqli_connect();
	$db->select_db("coinflip");
	
	$stmt = $db->prepare("SELECT * FROM sessions WHERE id=? AND expires>CURRENT_TIMESTAMP();");
	$stmt->bind_param("s", $_POST["sessionId"]);
	$stmt->execute();
	$result = $stmt->get_result()->fetch_assoc();
	
	if ($result) {
		$stmt = $db->prepare("DELETE FROM coinflips WHERE id=? AND user=?;");
		$stmt->bind_param("ii", $_POST["id"], $result["user"]);
		$stmt->execute();
		if ($db->affected_rows > 0) {
			echo "ok";
			exit();
		}
	}
}

echo "not ok";
