var coin = document.getElementById("coin");
if (coin)
    coin.addEventListener("mouseover", event => {
        if (!coin.classList.contains("bounce")) {
            coin.classList.add("bounce");
            setTimeout(() => coin.classList.remove("bounce"), 2000);
        }
    });