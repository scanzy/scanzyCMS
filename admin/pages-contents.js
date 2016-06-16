//inits contents table
contentstable = $("#contents-list").scanzytable({
    search_placeholder: "Search contents...",
    new_text: "New content", new_click: function () { showError("Not implemented "); },
    columns_names: ["#", "Name", "Macros", ""],
    request: {
        url: "./", method: "GET", data: { request: "content", action: "get" },
        check_empty: function (data) { return (data.length == 0); },
        fetch: function (data) {
            var html = "";
            for (var i = 0; i < data.length; i++)
                html += '<tr><td><a href="../' + data[i]['Id'] + '" target="_blank">/' + data[i]['Name'] + '</a></td><td>' +
            data[i]['Id'] + '</td><td class="right"><button type="button" class="btn btn-xs btn-success" onclick="window.open(\'../' +
            data[i]['Url'] + '\');"><span class="glyphicon glyphicon-eye-open"></span> <span>View</span></button> <button type="button" class="btn btn-xs btn-warning" onclick="editContent(' +
            data[i]['Id'] + ');"><span class="glyphicon glyphicon-edit"></span> <span>Edit</span></button> <button type="button" class="btn btn-xs btn-danger" onclick="confirmDelFile(\'' +
            data[i]['Url'] + '\');"><span class="glyphicon glyphicon-trash"></span> <span>Delete</span></button></td></tr>';
            return html;
        },
        done: function () { translate(document.getElementById("contents-list")); }, error: errorPopup
    }
});

contentstable.loadItems();