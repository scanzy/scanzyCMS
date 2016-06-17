
//database connection settings form

//buttons
$("#db-save").click(function (e) {
    e.preventDefault;
    if ($(this).hasClass('disabled')) return false;
    $("#db-msgs > span").addClass('hidden');

    //saves configuration
    ajax({ request: "config", action: "edit",
        host: $("#dbhost").val(),
        name: $("#bdname").val(),
        user: $("#dbuser").val(),
        pwd: $("#dbpwd").val()

    //shows result
    }, function () { $("#db-save-ok").removeClass('hidden'); })
    .fail(function () { shake($("#db-save-error").removeClass('hidden')); });
});

$("#db-cancel").click(function (e) { 
    e.preventDefault;
    if ($(this).hasClass('disabled')) return false;
    $("#db-msgs > span").addClass('hidden');

    resetForm(); //reverts form (reloading data from server)
});

$("#db-test").click(function (e) {
    e.preventDefault;
    if ($(this).hasClass('disabled')) return false;
    $("#db-msgs > span").addClass('hidden');

    //test config
    ajax({ request: "config", action: "test",
        host: $("#dbhost").val(),
        name: $("#bdname").val(),
        user: $("#dbuser").val(),
        pwd: $("#dbpwd").val()

    }, //shows result
    function () { $("#db-test-ok").removeClass('hidden'); })
    .fail(function () { shake($("#db-test-error").removeClass('hidden')); });

    return false;
});

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
        $("#dbhost").val(data['host']);
        $("#dbname").val(data['name']);
        $("#dbuser").val(data['user']);
        $("#dbpwd").val(data['pwd']);

        //handler to detect changes
        $("#dbconn input").on('input', function () {

            //enables save/cancel buttons
            $("#db-save").removeClass('disabled');
            $("#db-cancel").removeClass('disabled');

            $("#dbconn input").off('input'); //removes this handler
        });

    }) //shows error on loading fail
    .fail(function () { shake($("#db-load-error").removeClass('hidden')); })
    .always(function() { $("#db-test").removeClass('disabled'); }); //enables test
}

//inits data
resetForm();