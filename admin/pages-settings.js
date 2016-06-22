
//database connection settings form

//buttons
$("#db-save").click(function (e) {
    e.preventDefault();
    if ($(this).hasClass('disabled')) return false;
    $("#db-msgs > span").addClass('hidden');

    //saves configuration
    ajax({ request: "config", action: "update", DB: {
        host: $("#dbhost").val(),
        name: $("#dbname").val(),
        user: $("#dbuser").val(),
        pwd: $("#dbpwd").val()
    }
        //shows result
    }, function () { $("#db-save-ok").removeClass('hidden'); resetForm(); })
    .fail(function () { shake($("#db-save-error").removeClass('hidden')); });

    return false;
});

$("#db-cancel").click(function (e) {
    e.preventDefault();
    if ($(this).hasClass('disabled')) return false;
    $("#db-msgs > span").addClass('hidden');

    resetForm(); //reverts form (reloading data from server)
    return false;
});

$("#db-test").click(function (e) {
    e.preventDefault();
    if ($(this).hasClass('disabled')) return false;
    $("#db-msgs > span").addClass('hidden');

    //test config
    ajax({ request: "config", action: "test",
        host: $("#dbhost").val(),
        name: $("#dbname").val(),
        user: $("#dbuser").val(),
        pwd: $("#dbpwd").val()

    }, //shows result
    function () { $("#db-test-ok").removeClass('hidden'); })
    .fail(function () { shake($("#db-test-error").removeClass('hidden')); });

    return false;
});

//disables save password dialog
$("#db-conn").submit(function(e) { e.preventDefault(); return false; })

//form reset
function resetForm()
{
    //disables buttons
    $("#db-save").addClass('disabled');
    $("#db-cancel").addClass('disabled');
    $("#db-test").addClass('disabled');

    //loads data from server
    ajax({ request: "config", action: "get" }, function (data) {

        //sets form
        $("#dbhost").val(data['DB']['host']);
        $("#dbname").val(data['DB']['name']);
        $("#dbuser").val(data['DB']['user']);
        $("#dbpwd").val(data['DB']['pwd']);

        //handler to detect changes
        $("#db-conn input").on('input', function () {

            //enables save/cancel buttons
            $("#db-save").removeClass('disabled');
            $("#db-cancel").removeClass('disabled');

            $("#db-conn input").off('input'); //removes this handler
        });

    }) //shows error on loading fail
    .fail(function () { shake($("#db-load-error").removeClass('hidden')); })
    .always(function() { $("#db-test").removeClass('disabled'); }); //enables test
}

//inits data
resetForm();