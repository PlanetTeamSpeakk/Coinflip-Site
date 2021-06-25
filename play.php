<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, shrink-to-fit=no">
    <title>Coinflip - Play</title>
    <meta name="description" content="Play Coinflip against your friends!">
    <link rel="icon" type="image/svg+xml" sizes="512x512" href="assets/img/coin.svg">
    <link rel="icon" type="image/svg+xml" sizes="512x512" href="assets/img/coin.svg">
    <link rel="icon" type="image/svg+xml" sizes="512x512" href="assets/img/coin.svg">
    <link rel="icon" type="image/svg+xml" sizes="512x512" href="assets/img/coin.svg">
    <link rel="icon" type="image/svg+xml" sizes="512x512" href="assets/img/coin.svg">
    <link rel="stylesheet" href="assets/bootstrap/css/bootstrap.min.css">
    <link rel="manifest" href="manifest.json">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Lato:300,400,700">
    <link rel="stylesheet" href="assets/css/flippingcoin.css">
    <link rel="stylesheet" href="assets/css/frontpage.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/pikaday/1.6.1/css/pikaday.min.css">
    <link rel="stylesheet" href="https://unpkg.com/bootstrap-table@1.18.3/dist/bootstrap-table.min.css">
    <link rel="stylesheet" href="assets/css/main.css">
</head>

<body>
    <nav class="navbar navbar-dark navbar-expand-lg fixed-top bg-white portfolio-navbar gradient">
        <div class="container"><div id="phpcode" hidden>
<?php
// Global code that gets ran on every page.
// Put in a hidden div so I don't have to look at it in BSS.

setlocale(LC_MONETARY, "en_GB.utf8");
// This db variable gets used for all queries in this request.
$db = mysqli_connect();
$db->select_db("coinflip");
// Create temp rank table to get users' rank.
$db->query("CREATE TEMPORARY TABLE IF NOT EXISTS userRanks (PRIMARY KEY (id), INDEX(rank)) AS (SELECT id, ROW_NUMBER() OVER (ORDER BY balance DESC) rank FROM users);");

if (isset($_COOKIE["PHPSESSID"])) {
    // Continue session if one is available and valid.
    session_start();
    session_init();
}

function alert($type, $msg) {
    echo "<div class='container' style='width: 50%; max-width: 720px; position: absolute; left: 50%; transform: translateX(-50%);'><div class='row'><div class='alert alert-$type text-center' role='alert'>$msg</div></div></div>";
}

function format_money($amount) {
    // Only en_GB.utf8 seems to properly work on my Pi, but since that
    // prefixes the result with 'GBP', we remove that here.
    return substr(money_format("%.0i", $amount), 3);
}

function session_init() {
    global $db;
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
                    $_SESSION["rank"] = get_rank($_SESSION["user"]);
                    echo "<script>window.cfSession = JSON.parse('".str_replace("'", "\\'", json_encode($_SESSION))."');</script>";
            }
        } else logout();
    } else {
        session_abort();
        session_destroy();
    }
}

function logout() {
    global $db;
    $sessid = session_id();
    $stmt = $db->prepare("DELETE FROM sessions WHERE id=?;");
    $stmt->bind_param("s", $sessid);
    $stmt->execute();
    session_abort();
    session_destroy();
}

function cf_exists($id) {
    global $db;
    $stmt = $db->prepare("SELECT * FROM coinflips WHERE id=?;");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    return $stmt->get_result()->fetch_assoc() !== NULL;
}

function get_rank($userId) {
    global $db;
    return $db->query("SELECT id as pid, (SELECT rank FROM userRanks WHERE id=pid) AS rank FROM users WHERE id=$userId;")->fetch_assoc()["rank"];
}
?>
    <script>
        document.getElementById("phpcode").remove();
    </script>
</div><a class="navbar-brand logo" href="/">Coinflip</a><button data-bs-toggle="collapse" class="navbar-toggler" data-bs-target="#navbarNav"><span class="visually-hidden">Toggle navigation</span><span class="navbar-toggler-icon"></span></button>
            <div class="collapse navbar-collapse" id="navbarNav"><ul class="navbar-nav ms-auto">
    <?php
        $loggedIn = session_status() === PHP_SESSION_ACTIVE;
    ?>
    <li class="nav-item"><a class="nav-link" href="sign-up.php"<?php if ($loggedIn) echo " hidden"; ?>>Sign up</a></li>
    <li class="nav-item"><a class="nav-link" href="login.php"<?php if ($loggedIn) echo " hidden"; ?>>Login</a></li>
    <li class="nav-item"><a class="nav-link" href="transactions.php"<?php if (!$loggedIn) echo " hidden"; ?>>Balance: $<?php if ($loggedIn) echo format_money($_SESSION["balance"]); ?></a></li>
    <li class="nav-item"><a class="nav-link" href="place-bet.php"<?php if (!$loggedIn) echo " hidden"; ?>>Place bet</a></li>
    <li class="nav-item"><a class="nav-link" href="logout.php"<?php if (!$loggedIn) echo " hidden"; ?>><?php if ($loggedIn) echo $_SESSION["username"]; ?> (logout)</a></li>
</ul>
</div>
        </div>
    </nav><?php
if (!isset($_GET["cf"]) || !cf_exists($_GET["cf"]) || !$loggedIn) echo "<script>location.pathname = '/';</script>";
else {
    // Already checked in the condition if the coinflip exists,
    // so no harm in just chucking the id from the query in the sql query.
    $cf = $db->query("SELECT * FROM coinflips WHERE id=".$_GET["cf"])->fetch_assoc();
    if ($_SESSION["balance"] < $cf["bet"] || $_SESSION["user"] == $cf["user"]) {
        echo "<script>location.pathname = '/';</script>";
        exit();
    } else {
        $db->query("UPDATE users SET balance=balance-".strval($cf["bet"])." WHERE id=".$_SESSION["user"]);
        echo "<script>var cf = JSON.parse('".str_replace("'", "\\'", json_encode($cf))."');</script>";
        
        $opponent = $db->query("SELECT id, username, balance FROM users WHERE id=".$cf["user"])->fetch_assoc();
        if ($opponent["id"] == $_SESSION["id"]) {
            echo "<script>location.pathname = '/';</script>";
            exit();
        } else $opponent["rank"] = get_rank($opponent["id"]);
    }
}
?>
    <main class="page">
        <section class="portfolio-block">
            <div class="container">
                <div class="heading" style="margin-bottom: 30px;"><h2>Play against <span style="color: var(--bs-orange);"><?php echo $opponent["username"]; ?></span> for <span style="color: var(--bs-orange);">$<?php echo format_money(intval($cf["bet"])); ?></span></h2></div>
                <div class="row align-items-center">
                    <div class="col text-center"><h4 <?php echo $_SESSION["rank"] <= 3 ? "class='cf-".($_SESSION["rank"] == 1 ? "first" : ($_SESSION["rank"] == 2 ? "second" : "third"))."'" : ""; ?>><?php echo $_SESSION["username"]; ?></h4>
<h6>$<?php echo format_money($_SESSION["balance"]); ?></h6>
<h6><?php echo $cf["side"] == "HEADS" ? "TAILS" : "HEADS"; ?></h6></div>
                    <div class="col d-flex justify-content-center">
                        <div id="cf-coin"></div>
                    </div>
                    <div class="col text-center"><h4 <?php echo $opponent["rank"] <= 3 ? "class='cf-".($opponent["rank"] == 1 ? "first" : ($opponent["rank"] == 2 ? "second" : "third"))."'" : ""; ?>><?php echo $opponent["username"]; ?></h4>
<h6>$<?php echo format_money($opponent["balance"]); ?></h6>
<h6><?php echo $cf["side"]; ?></h6></div>
                </div>
            </div>
        </section>
    </main>
    <footer class="page-footer navbar-static-bottom">
        <div class="container">
            <p style="margin-bottom: 0;">Copyright © 2021 Tygo &amp; Damian</p><a href="https://github.com/PlanetTeamSpeakk/Coinflip-Site" style="font-size: 1rem;">Source Code</a>
        </div>
    </footer>
    <script src="assets/js/jquery.min.js"></script>
    <script src="assets/bootstrap/js/bootstrap.min.js"></script>
    <script src="assets/js/bs-init.js"></script>
    <script src="assets/js/bounce.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pikaday/1.6.1/pikaday.min.js"></script>
    <script src="https://unpkg.com/bootstrap-table@1.18.3/dist/bootstrap-table.min.js"></script>
    <script src="https://unpkg.com/bootstrap-table@1.18.3/dist/extensions/cookie/bootstrap-table-cookie.min.js"></script>
    <script src="https://unpkg.com/tableexport.jquery.plugin/tableExport.min.js"></script>
    <script src="https://kit.fontawesome.com/7e8ccfcda5.js"></script>
    <script src="assets/js/theme.js"></script>
    <script src="assets/js/activeBets.js"></script>
    <script src="assets/js/flippingcoin.js"></script>
    <script src="assets/js/transactions.js"></script>
</body>

</html>