//inits templates table
templatestable = $("#templates-list").scanzytable({
    search: { show: true, text: "Search templates..." },
    button: { show: true, text: "New template", click: function () { window.location.href = "./newtemplate"; } },
    columns: {
        "Name": "Template name",
        "ContentId": "Derived from",
        "Buttons": ""
    },
    request: { url: "./apis/template/get.php", method: "GET", data: null, error: errorPopup,
        complete: function () { translate(document.getElementById("templates-list")); }
    },
    fetch: {
        content: {
            'Url': function (x, url) { return '<a href="../' + url + '" target="_blank">/' + url + '</a>'; },
            'Buttons': function (x, y, z, data) {
                return '\
                    <button type="button" class="btn btn-xs btn-warning" onclick="editTemplate(' + data['Id'] + ');">\
                        <span class="glyphicon glyphicon-edit"></span> <span>Edit</span>\
                    </button> \
                    <button type="button" class="btn btn-xs btn-danger" onclick="confirmDelTemplate(' + data['Id'] + ', \'' + data['Name'] + '\');">\
                        <span class="glyphicon glyphicon-trash"></span> <span>Delete</span>\
                    </button>';
            }
        },
        cell: { "Buttons": { start: function () { return "<td class='right'>"; } } }
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