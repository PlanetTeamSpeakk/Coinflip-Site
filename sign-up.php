<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, shrink-to-fit=no">
    <title>Coinflip - Sign Up</title>
    <meta name="description" content="Create an account on our Coinflip website.">
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
    </nav>
    <main class="page"><?php
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    // All these checks are theoretically unnecessary, but better safe than sorry.
    if ($_POST["email"] != $_POST["emailConfirm"]) alert("danger", "Emails do not match.");
    else if ($_POST["password"] != $_POST["passwordConfirm"]) alert("danger", "Passwords do not match.");
    else if (strlen($_POST["password"]) < 8) alert("danger", "Your password must be at least 8 characters.");
    else if (!isset($_POST["tos"])) alert("danger", "You must agree with our Terms of Service.");
    else if (!ctype_alnum($_POST["username"])) alert("danger", "Your username must be alphanumeric.");
    else {
        $db->select_db("coinflip");
        if (checkExists($db, "email", $_POST["email"])) alert("danger", "An account with that email address has already been registered.<br>Please login <a href='/login.php' class='alert-link'>here</a> instead.");
        else if (checkExists($db, "username", $_POST["username"])) alert("danger", "That username is already taken, please pick another.");
        else {
            $stmt = $db->prepare("INSERT INTO users (email, username, password) VALUES (?, ?, ?);");
            $stmt->bind_param("sss", $_POST["email"], $_POST["username"], password_hash($_POST["password"], PASSWORD_BCRYPT));
            if (!$stmt->execute()) alert("danger", "An unknown error occurred while creating your account.");
            else alert("success", "Your account has been registered.");
        }
    }
}

function checkExists($db, $column, $value) {
    $stmt = $db->prepare("SELECT * FROM users WHERE LOWER($column)=?;");
    $stmt->bind_param("s", strtolower($value));
    $stmt->execute();
    $result = $stmt->get_result()->fetch_assoc();
    return count($result);
}
?>
        <section class="portfolio-block">
            <div class="container">
                <div class="heading" style="margin-bottom: 30px;">
                    <h2>SIGN UP</h2>
                </div>
                <form oninput="emailConfirm.setCustomValidity(emailConfirm.value != email.value ? &#39;Emails do not match.&#39; : &#39;&#39;); passwordConfirm.setCustomValidity(passwordConfirm.value != password.value ? &#39;Passwords do not match.&#39; : &#39;&#39;); tos.setCustomValidity(!tos.checked ? &#39;You must agree with our Terms of Service.&#39; : &#39;&#39;);" method="post">
                    <div>
                        <div class="row">
                            <div class="col">
                                <div class="mb-3"><label class="form-label">Username</label><input class="form-control" type="text" data-bs-toggle="tooltip" data-bss-tooltip="" data-bs-placement="left" id="username" required="" minlength="3" maxlength="16" name="username" pattern="^[a-zA-Z0-9_]*$" title="Must be alphanumeric"></div>
                            </div>
                        </div>
                    </div>
                    <div>
                        <div class="row">
                            <div class="col">
                                <div class="mb-3"><label class="form-label" for="email">Email</label><input class="form-control" type="email" id="email" required="" name="email"></div>
                            </div>
                            <div class="col">
                                <div class="mb-3"><label class="form-label" for="email-confirm">Email confirmation</label><input class="form-control" type="email" id="email-confirm" required="" name="emailConfirm"></div>
                            </div>
                        </div>
                    </div>
                    <div>
                        <div class="row">
                            <div class="col">
                                <div class="mb-3"><label class="form-label" for="email">Password</label><input class="form-control" type="password" id="password" required="" minlength="8" name="password"></div>
                            </div>
                            <div class="col">
                                <div class="mb-3"><label class="form-label" for="email-confirm">Password confirmation</label><input class="form-control" type="password" id="password-confirm" required="" name="passwordConfirm"></div>
                            </div>
                        </div>
                    </div>
                    <div class="mb-3">
                        <div class="form-check"><input class="form-check-input" type="checkbox" id="tos" required="" name="tos"><label class="form-check-label" data-bs-toggle="tooltip" data-bss-tooltip="" data-bs-placement="bottom" for="tos" title="By ticking this box you agree to everything that is written below this message. Grading this assignment under an 8.0 is not allowed. Using this website is completely at your own risk, we are NOT responsible for your money.">I agree with the Terms of Service.</label></div>
                    </div>
                    <div class="mb-3">
                        <div class="row justify-content-center">
                            <div class="col d-flex justify-content-center"><button class="btn btn-primary d-block w-50 align-self-center" type="submit">Sign Up</button></div>
                        </div>
                    </div>
                </form>
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