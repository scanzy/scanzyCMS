//inits templates table
templatestable = $("#templates-list").scanzytable({
    search_placeholder: "Search templates...",
    new_text: "New template", new_click: function () { window.location.href = "./newtemplate"; },
    columns_names: {
        "Name": "Template name",
        "ContentId": "Derived from",
        "Buttons": ""
    },
    request: { url: "./apis/template/get.php", method: "GET", data: null, error: errorPopup,
        done: function () { translate(document.getElementById("templates-list")); }
    },
    fetch: {
        content: {
            'Url': function (url) { return '<a href="../' + url + '" target="_blank">/' + url + '</a>'; },
            'Buttons': function (x, data) {
                return '\
                    <button type="button" class="btn btn-xs btn-warning" onclick="editTemplate(' + data['Id'] + ');">\
                        <span class="glyphicon glyphicon-edit"></span> <span>Edit</span>\
                    </button> \
                    <button type="button" class="btn btn-xs btn-danger" onclick="confirmDelTemplate(' + data['Id'] + ', \'' + data['Name'] + '\');">\
                        <span class="glyphicon glyphicon-trash"></span> <span>Delete</span>\
                    </button>';
            }
        },
        cell: { start: function (col) { return (col == "Buttons") ? "<td class='right'>" : "<td>"; } }
    }
});

//loads items
templatestable.loadItems();

//functions called by buttons in table
function confirmDelTemplate(id, name) {
    showConfirm("<p><span>Do you really want to delete this template?</span> ('<span>/" + name + "</span>')</p>", function (x) {
        if (x == true) ajax("./apis/template/del.php", { url: url }, function () { templatestable.loadItems(); });
    });
}

function editTemplate(id) {
    showError("Not implemented " + id);
}