var activeBetsTable = $("#active-bets");

// UTILITY FUNCTIONS
function getSessionId() {
    return Object.fromEntries(document.cookie.split("; ").map(s => s.split("=", 2))).PHPSESSID;
}

function deleteCoinflip(cf) {
    $.post("/delete-coinflip.php", {"id": cf, "sessionId": getSessionId()}, res => location.reload());
}

function placeBet() {
    var coinSide = document.getElementById("coin-side");
    coinSide = coinSide.options[coinSide.selectedIndex].value;
    var betAmount = document.getElementById("bet-amount").value;
    if ((coinSide == "HEADS" || coinSide == "TAILS") && betAmount) 
        $.post("/create-coinflip.php", {"side": coinSide, "bet": betAmount, "sessionId": getSessionId()}, res => location.reload());
}

function play(id, bet) {
    if (bet <= parseInt(cfSession.balance)) window.location = window.location.origin + "/play.php?cf=" + id;
}

// FORMATTERS
function removeFormatter(value, row, index) {
    return row.user == cfSession.id ? '<a class="cf-delete" href="javascript:deleteCoinflip(' + row.id + ')" title="Delete"><i class="fa fa-trash" aria-hidden="true"></i></a>' : "";
}

function userFormatter(value, row, index) {
    return "<span " + (row.userrank <= 3 ? "class='cf-" + (row.userrank == 1 ? "first" : row.userrank == 2 ? "second" : "third") + "'" : "") + ">" + row.username + "</span>";
}

function joinFormatter(value, row, index) {
    return row.username == cfSession.username ? "<div style='height: 38px;'></div>" : "<button class='btn btn-outline-primary' type='submit' onclick='play(" + row.id + ", " + row.bet.substr(1).replace(",", "") + ")'>Join</button>";
}

// FOOTER FORMATTERS
function userFooterFormatter(data) {
    return cfSession["username"];
}

function sideFooterFormatter(data) {
    return "<select id='coin-side' class='form-select' aria-label='Select coin side'><option selected>Select coin side</option><option value='HEADS'>HEADS</option><option value='TAILS'>TAILS</option></select>";
}

function betFooterFormatter(data) {
    return "<input id='bet-amount' class='form-control' aria-label='Insert bet amount' type='number' placeholder='Insert bet amount' max='" + cfSession.balance + "' />";
}

function joinFooterFormatter(data) {
    return "<button class='btn btn-outline-primary' type='submit' onclick='placeBet()'>Place Bet</button>";
}

function initActiveBetsTable() {
    if (activeBetsTable)
        activeBetsTable.bootstrapTable('destroy').bootstrapTable({
            columns: [{
                title: 'Remove',
                align: 'center',
                width: 30,
                formatter: removeFormatter
            }, {
                title: 'User',
                field: 'username',
                sortable: true,
                align: 'center',
                formatter: userFormatter,
                footerFormatter: userFooterFormatter
            }, {
                title: 'Side',
                field: 'side',
                sortable: true,
                align: 'center',
                width: 200,
                footerFormatter: sideFooterFormatter
            }, {
                title: 'Bet',
                field: 'bet',
                sortable: true,
                align: 'center',
                footerFormatter: betFooterFormatter
            }, {
                title: 'Created',
                field: 'created',
                sortable: true,
                align: 'center'
            }, {
                title: 'Join',
                align: 'center',
                width: 120,
                formatter: joinFormatter,
                footerFormatter: joinFooterFormatter
            }]
        });
}

$(initActiveBetsTable);