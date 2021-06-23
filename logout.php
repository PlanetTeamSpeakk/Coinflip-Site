<!doctype html>
<html> 
 <head> 
  <meta charset="utf-8"> 
  <meta name="viewport" content="width=device-width, initial-scale=1.0, shrink-to-fit=no"> 
  <title>Coinflip - Logout</title> 
  <meta name="description" content="Log out from the Coinflip site."> 
  <link rel="icon" type="image/png" sizes="835x835" href="assets/img/coin.png"> 
  <link rel="icon" type="image/png" sizes="835x835" href="assets/img/coin.png"> 
  <link rel="icon" type="image/png" sizes="835x835" href="assets/img/coin.png"> 
  <link rel="icon" type="image/png" sizes="835x835" href="assets/img/coin.png"> 
  <link rel="icon" type="image/png" sizes="835x835" href="assets/img/coin.png"> 
  <link rel="stylesheet" href="assets/bootstrap/css/bootstrap.min.css"> 
  <link rel="manifest" href="manifest.json"> 
  <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Lato:300,400,700"> 
  <link rel="stylesheet" href="assets/css/frontpage.css"> 
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/pikaday/1.6.1/css/pikaday.min.css"> 
  <link rel="stylesheet" href="https://unpkg.com/bootstrap-table@1.18.3/dist/bootstrap-table.min.css"> 
  <link rel="stylesheet" href="assets/css/main.css"> 
 </head> 
 <body> 
  <nav class="navbar navbar-dark navbar-expand-lg fixed-top bg-white portfolio-navbar gradient"> 
   <div class="container">
    <div id="phpcode" hidden> <!--?php
// Global code that gets ran on every page.
// Put in a hidden div so I don't have to look at it in BSS.

setlocale(LC_MONETARY, "en_GB.utf8");
// This db variable gets used for all queries in this request.
$db = mysqli_connect();
$db--->select_db("coinflip"); if (isset($_COOKIE["PHPSESSID"])) { // Continue session if one is available and valid. session_start(); session_init(); } function alert($type, $msg) { echo "
     <div class="container" style="width: 50%; max-width: 720px; position: absolute; left: 50%; transform: translateX(-50%);">
      <div class="row">
       <div class="alert alert-$type text-center" role="alert">
        $msg
       </div>
      </div>
     </div>"; } function format_money($amount) { // Only en_GB.utf8 seems to properly work on my Pi, but since that // prefixes the result with 'GBP', we remove that here. return substr(money_format("%.0i", $amount), 3); } function session_init() { global $db; $sessid = session_id(); $stmt = $db-&gt;prepare("SELECT * FROM sessions WHERE id=?;"); $stmt-&gt;bind_param("s", $sessid); $stmt-&gt;execute(); $result = $stmt-&gt;get_result()-&gt;fetch_assoc(); if ($result != null &amp;&amp; count($result) &gt; 0) { if (strtotime($result["expires"]) &gt; time()) { $_SESSION["user"] = $result["user"]; $stmt = $db-&gt;prepare("SELECT * FROM users WHERE id=?;"); $stmt-&gt;bind_param("i", $_SESSION["user"]); if ($stmt-&gt;execute()) { $result = $stmt-&gt;get_result()-&gt;fetch_assoc(); $keys = array_keys($result); foreach ($keys as &amp;$key) if ($key != "password") // Ignore the password, obviously. $_SESSION[$key] = htmlspecialchars($result[$key]); echo "
     <script>window.cfSession = JSON.parse('".str_replace("'", "\\'", json_encode($_SESSION))."');</script>"; } } else logout(); } else { session_abort(); session_destroy(); } } function logout() { global $db; $sessid = session_id(); $stmt = $db-&gt;prepare("DELETE FROM sessions WHERE id=?;"); $stmt-&gt;bind_param("s", $sessid); $stmt-&gt;execute(); session_abort(); session_destroy(); } ?&gt; 
     <script>
        document.getElementById("phpcode").remove();
    </script> 
    </div><a class="navbar-brand logo" href="/">Coinflip</a><button data-bs-toggle="collapse" class="navbar-toggler" data-bs-target="#navbarNav"><span class="visually-hidden">Toggle navigation</span><span class="navbar-toggler-icon"></span></button> 
    <div class="collapse navbar-collapse" id="navbarNav">
     <ul class="navbar-nav ms-auto"> <!--?php
        $loggedIn = session_status() === PHP_SESSION_ACTIVE;
    ?--> 
      <li class="nav-item"><a class="nav-link" href="sign-up.php"><!--?php if ($loggedIn) echo " hidden"; ?-->&gt;Sign up</a></li> 
      <li class="nav-item"><a class="nav-link" href="login.php"><!--?php if ($loggedIn) echo " hidden"; ?-->&gt;Login</a></li> 
      <li class="nav-item"><span class="navbar-text"><!--?php if (!$loggedIn) echo " hidden"; ?-->&gt;Balance: $<!--?php if ($loggedIn) echo format_money($_SESSION["balance"]); ?--></span></li> 
      <li class="nav-item"><a class="nav-link" href="place-bet.php"><!--?php if (!$loggedIn) echo " hidden"; ?-->&gt;Place bet</a></li> 
      <li class="nav-item"><a class="nav-link" href="logout.php"><!--?php if (!$loggedIn) echo " hidden"; ?-->&gt;<!--?php if ($loggedIn) echo $_SESSION["username"]; ?--> (logout)</a></li> 
     </ul> 
    </div> 
   </div> 
  </nav> 
  <main class="page"> 
   <section class="portfolio-block"> 
    <div class="container"> 
     <div class="heading" style="margin-bottom: 30px;"> 
      <h2>LOGOUT</h2> 
     </div> 
    </div> 
   </section><!--?php
logout();

echo "<script-->window.location.pathname = '/';"; ?&gt; 
  </main> 
  <footer class="page-footer navbar-static-bottom"> 
   <div class="container"> 
    <p>Copyright Â© 2021 Tygo Mourits &amp; Damian Plomp</p> 
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
 </body>
</html>