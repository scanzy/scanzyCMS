//inits files table
filestable = $("#files-list").scanzytable({
    search: { show: true, text: "Search files..." },
    button: { show: true, text: "New file", click: function () { window.location.href = "./newfile"; } },
    columns: {
        "Url": "File path (URL)",
        "ContentId": "File content",
        "Buttons": ""
    },
    request: { url: "./apis/file/get.php", method: "GET", data: null, error: errorPopup,
        complete: function () { translate(document.getElementById("files-list")); }
    },
    fetch: {
        content: {
            'Url': function (x, url) { return '<a href="../' + url + '" target="_blank">/' + url + '</a>'; },
            'Buttons': function (x, y, z, data) {
                return '\
                    <button type="button" class="btn btn-xs btn-success" onclick="window.open(\'../' + data['Url'] + '\');">\
                        <span class="glyphicon glyphicon-eye-open"></span> <span>View</span>\
                    </button> \
                    <button type="button" class="btn btn-xs btn-warning" onclick="editContent(' + data['ContentId'] + ');">\
                        <span class="glyphicon glyphicon-edit"></span> <span>Edit</span>\
                    </button> \
                    <button type="button" class="btn btn-xs btn-danger" onclick="confirmDelFile(\'' + data['Url'] + '\');">\
                        <span class="glyphicon glyphicon-trash"></span> <span>Delete</span>\
                    </button>';
            }
        },
        cell: { "Buttons": { start: function () { return "<td class='right'>"; }, end: function() { return "</td>";} } }
    }
});

//loads items
filestable.loadItems();

//functions called by buttons in table
function confirmDelFile(url) {
    showConfirm("<p><span>Do you really want to delete this file?</span> (URL '/" + url + "')</p>", function (x) {
        if (x == true) ajax("./apis/file/del.php", { url: url }, function () { filestable.loadItems(); });
    });
}

function editContent(id) {
    showError("Not implemented " + id);
}