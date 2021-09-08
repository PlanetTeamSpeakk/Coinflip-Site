var flipCoin = document.getElementById("cf-coin");
if (flipCoin) {
    $.post("/play-coinflip.php", {"id": parseInt(cf.id), "sessionId": getSessionId()}, res => window.cfRes = res);
    flipCoin.addEventListener("animationend", () => {
        flipCoin.style.backgroundImage = "url('/assets/img/" + cfRes.toLowerCase() + ".svg')";
        var heading = document.createElement("div");
        heading.classList.add("heading");
        heading.innerHTML = "<h2>You have <span style='margin-top: 30px; color: " + (cf.side == cfRes ? "firebrick" : "limegreen") + "'>" + (cf.side == cfRes ? "LOST" : "WON") + "</span></h2>";
        document.getElementsByClassName("container")[1].appendChild(heading);
        setTimeout(() => history.back(), 2000);
    });
}