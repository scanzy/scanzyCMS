//inits files table
filestable = $("#files-list").scanzytable({
    search_placeholder: "Search files...",
    new_text: "New file", new_click: function () { showError("Not implemented "); },
    columns_names: {
        "Url": "File path (URL)",
        "ContentId": "File content",
        "Buttons": ""
    },
    request: { url: "./", method: "GET", data: { request: "file", action: "get" }, error: errorPopup,
        done: function () { translate(document.getElementById("files-list")); }
    },
    fetch: {
        content: {
            'Url': function (url) { return '<a href="../' + url + '" target="_blank">/' + url + '</a>'; },
            'Buttons': function (x, data) {
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
        cell: { start: function (col) { return (col == "Buttons") ? "<td class='right'>" : "<td>"; } }
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