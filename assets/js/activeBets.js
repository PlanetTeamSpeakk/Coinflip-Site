var $table = $('#table');

function getSessionId() {
    return Object.fromEntries(document.cookie.split("; ").map(s => s.split("=", 2))).PHPSESSID;
}

function deleteCoinflip(cf) {
    $.post("/delete-coinflip.php", {"id": cf, "sessionId": getSessionId()}, res => {console.log(res); initTable();});
}

function responseHandler(res) {
    $.each(res.rows, (i, row) => row.test = "yes");
    return res;
}

function operateFormatter(value, row, index) {
    return [
        '<a class="like" href="javascript:void(0)" title="Like">',
        '<i class="fa fa-heart"></i>',
        '</a>  ',
        '<a class="remove" href="javascript:void(0)" title="Remove">',
        '<i class="fa fa-trash"></i>',
        '</a>'
    ].join('');
}

function removeFormatter(value, row, index) {
    // console.log(value, row, index);
    return row.user == cfSession.username ? '<a class="cf-delete" href="javascript:deleteCoinflip(' + row.id + ')" title="Delete"><i class="fa fa-trash" aria-hidden="true"></i></a>' : "";
}

function userFooterFormatter(data) {
    return cfSession["username"];
}

function sideFooterFormatter(data) {
    return "<select class='form-select' aria-label='Select coin side'><option selected>Select coin side</option><option value='HEADS'>HEADS</option><option value='TAILS'>TAILS</option></select>";
}

function betFooterFormatter(data) {
    
}

function playFooterFormatter(data) {
    
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
            footerFormatter: userFooterFormatter
        }, {
            title: 'Side',
            field: 'side',
            sortable: true,
            align: 'center',
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
            title: 'Play',
            align: 'center',
            clickToSelect: false,
            events: window.operateEvents,
            formatter: operateFormatter,
            footerFormatter: playFooterFormatter
        }]
    });
}

$(initTable);