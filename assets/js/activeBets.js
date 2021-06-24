var $table = $('#table');

// UTILITY FUNCTIONS
function getSessionId() {
    return Object.fromEntries(document.cookie.split("; ").map(s => s.split("=", 2))).PHPSESSID;
}

function deleteCoinflip(cf) {
    $.post("/delete-coinflip.php", {"id": cf, "sessionId": getSessionId()}, res => location.reload());
}

function placeBet() {
    // TODO confirm side is selected and bet is filled in before sending, if not, show alert that disappears after 2 seconds.
    var coinSide = document.getElementById("coin-side");
    coinSide = coinSide.options[coinSide.selectedIndex].value;
    var betAmount = document.getElementById("bet-amount").value;
    $.post("/create-coinflip.php", {"side": coinSide, "bet": betAmount, "sessionId": getSessionId()}, res => location.reload());
}

// FORMATTERS
function removeFormatter(value, row, index) {
    return row.user == cfSession.username ? '<a class="cf-delete" href="javascript:deleteCoinflip(' + row.id + ')" title="Delete"><i class="fa fa-trash" aria-hidden="true"></i></a>' : "";
}

function userFormatter(value, row, index) {
    return "<span " + (row.userrank <= 3 ? "class='cf-" + (row.userrank == 1 ? "first" : row.userrank == 2 ? "second" : "third") + "'" : "") + ">" + row.user + "</span>";
}

function joinFormatter(value, row, index) {
    return "<button class='btn btn-outline-primary' type='submit' onclick=''>Join</button>";
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

function initTable() {
    $table.bootstrapTable('destroy').bootstrapTable({
        columns: [{
            title: 'Remove',
            align: 'center',
            width: 30,
            formatter: removeFormatter
        }, {
            title: 'User',
            field: 'user',
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

$(initTable);