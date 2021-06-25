var transactionsTable = $("#transactions");

function alterQueryParams(params) {
    params.sessionId = getSessionId();
    return params;
}

function opponentFormatter(value, row, index) {
    return "<span " + (row.opponentRank <= 3 ? "class='cf-" + (row.opponentRank == 1 ? "first" : row.opponentRank == 2 ? "second" : "third") + "'" : "") + ">" + row.opponentName + "</span>";
}

function initTransactionsTable() {
    if (transactionsTable)
        transactionsTable.bootstrapTable('destroy').bootstrapTable({
            columns: [{
                title: 'Opponent',
                field: 'opponentName',
                sortable: true,
                align: 'center',
                formatter: opponentFormatter
            }, {
                title: 'Side',
                field: 'side',
                sortable: true,
                align: 'center',
                width: 200
            }, {
                title: 'Bet',
                field: 'bet',
                sortable: true,
                align: 'center'
            }, {
                title: 'Date',
                field: 'date',
                sortable: true,
                align: 'center'
            }]
        });
}

$(initTransactionsTable);