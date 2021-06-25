var coin = document.getElementById("coin");
if (coin) {
    coin.addEventListener("mouseover", event => {
        if (!coin.classList.contains("bounce")) {
            coin.classList.add("bounce");
            setTimeout(() => coin.classList.remove("bounce"), 2000);
        }
    });
    coin.addEventListener("click", event => {
        if (event.isTrusted) {
            // TODO make clicking the button on the home page give one coin.
            // Might add later, won't do now, though.
        }
    });
}