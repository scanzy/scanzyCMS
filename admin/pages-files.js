//inits files table
filestable = $("#files-list").scanzytable({
    search_placeholder: "Search files...",
    new_text: "New file", new_click: function () { showError("Not implemented "); },
    columns_names: ["File path (URL)", "File content", ""],
    request: {
        url: "./", method: "GET", data: { request: "file", action: "get" },
        check_empty: function (data) { return (data.length == 0); },
        fetch: function (data) {
            var html = "";
            for (var i = 0; i < data.length; i++)
                html += '<tr><td><a href="../' + data[i]['Url'] + '" target="_blank">/' + data[i]['Url'] + '</a></td><td>' +
            data[i]['ContentId'] + '</td><td class="right"><button type="button" class="btn btn-xs btn-success" onclick="window.open(\'../' +
            data[i]['Url'] + '\');"><span class="glyphicon glyphicon-eye-open"></span> <span>View</span></button> <button type="button" class="btn btn-xs btn-warning" onclick="editContent(' +
            data[i]['ContentId'] + ');"><span class="glyphicon glyphicon-edit"></span> <span>Edit</span></button> <button type="button" class="btn btn-xs btn-danger" onclick="confirmDelFile(\'' +
            data[i]['Url'] + '\');"><span class="glyphicon glyphicon-trash"></span> <span>Delete</span></button></td></tr>';
            return html;
        },
        done: function () { translate(document.getElementById("files-list")); }, error: errorPopup
    }
});

//loads items
filestable.loadItems();

//functions called by buttons in table
function confirmDelFile(url) {
    showConfirm("<p><span>Do you really want to delete this file?</span> (URL '/" + url + "')</p>", function (x) {
        if (x == true) ajax({ action: 'del', request: 'file', url: url },
    function () { filestable.loadItems(); });
    });
}

function editContent(id) {
    showError("Not implemented " + id);
}