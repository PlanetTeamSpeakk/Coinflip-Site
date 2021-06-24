<?php
$db = mysqli_connect();
$db->select_db("coinflip");

if (isset($_COOKIE["PHPSESSID"])) {
    // Continue session if one is available and valid.
    session_start();
    $sessid = session_id();
    $stmt = $db->prepare("SELECT * FROM sessions WHERE id=?;");
    $stmt->bind_param("s", $sessid);
    $stmt->execute();
    $result = $stmt->get_result()->fetch_assoc();
    if ($result != null && count($result) > 0) {
        if (strtotime($result["expires"]) > time()) {
            $_SESSION["user"] = $result["user"];
            $stmt = $db->prepare("SELECT * FROM users WHERE id=?;");
            $stmt->bind_param("i", $_SESSION["user"]);
            if ($stmt->execute()) {
                $result = $stmt->get_result()->fetch_assoc();
                $keys = array_keys($result);
                foreach ($keys as &$key)
                    if ($key != "password") // Ignore the password, obviously.
                        $_SESSION[$key] = htmlspecialchars($result[$key]);
                    echo "<script>window.cfSession = JSON.parse('".str_replace("'", "\\'", json_encode($_SESSION))."');</script>";
            }
        } else logout();
    } else {
        session_abort();
        session_destroy();
    }
}

$sessid = session_id();
$stmt = $db->prepare("DELETE FROM sessions WHERE id=?;");
$stmt->bind_param("s", $sessid);
$stmt->execute();
session_abort();
session_destroy();

header("Location: /");