
//database connection settings form

//clears messages
function clearMsgs() { $("#db-msgs > div").addClass('hidden'); }

//form reset
function resetForm()
{
    //hides load errors
    $("#db-load-error").addClass('hidden');

    //shows loading info
    $("#db-load").removeClass('hidden');    

    enableOnInput(); //adds handler
    
    //loads data from server
    return ajax({ request: "config", action: "get" }, function (data) {

        //sets form
        $("#dbhost").val(data['DB']['host']);
        $("#dbname").val(data['DB']['name']);
        $("#dbuser").val(data['DB']['user']);
        $("#dbpwd").val(data['DB']['pwd']);

    }) //shows error on loading fail
    .fail(function () { shake($("#db-load-error").removeClass('hidden')); })
    .always(function () { $("#db-load").addClass('hidden'); }); //hides loading info
}

//shows testing info
function testConnection() {
    $("#db-test").addClass('hidden');
    $("#db-test-ok").addClass('hidden');
    $("#db-test-error").addClass('hidden');
    $("#db-testing").removeClass('hidden');

    //removes all tooltips
    hideAllTooltips();

    //test config
    ajax({ request: "config", action: "test",
        host: $("#dbhost").val(),
        name: $("#dbname").val(),
        user: $("#dbuser").val(),
        pwd: $("#dbpwd").val()

    }, //shows result
    function () { $("#db-test-ok").removeClass('hidden'); })
    .fail(function () { shake($("#db-test-error").removeClass('hidden')); })
    .always(function () { $("#db-testing").addClass('hidden'); });//removes testing info
}

//saves configuration
function saveConfig() {

    //disables buttons
    $("#db-save").addClass('disabled');
    $("#db-cancel").addClass('disabled');

    //shows saving info
    $("#db-saving").removeClass('hidden');

    $("#db-save").addClass('disabled');
    $("#db-cancel").addClass('disabled');

    //saves configuration
    ajax({ request: "config", action: "update", DB: {
        host: $("#dbhost").val(),
        name: $("#dbname").val(),
        user: $("#dbuser").val(),
        pwd: $("#dbpwd").val()
    }        
    }, function () { $("#db-save-ok").removeClass('hidden'); enableOnInput(); }) //shows result
    .fail(function () { 

        //shows error
        shake($("#db-save-error").removeClass('hidden')); 

        //enables buttons to retry/cancel
        $("#db-save").removeClass('disabled');
        $("#db-cancel").removeClass('disabled');
    })
    .always(function () { $("#db-saving").addClass('hidden'); }); //removes saving info
}

//enables buttons on input
function enableOnInput() {

    //disables buttons
    $("#db-save").addClass('disabled');
    $("#db-cancel").addClass('disabled');

    //adds handler 
    $("#db-conn input").on('input', function() {

        clearMsgs();

        //enables buttons
        $("#db-save").removeClass('disabled');
        $("#db-cancel").removeClass('disabled');

        //resets test
        $("#db-test").removeClass('hidden');
        $("#db-test-ok").addClass('hidden');
        $("#db-test-error").addClass('hidden');
        $("#db-testing").addClass('hidden');

        $("#db-conn input").off('input'); //removes this handler
    })
}
//disables save password dialog
$("#db-conn").submit(function(e) { e.preventDefault(); return false; })

//buttons callbacks
$("#db-save").click(function (e) {
    e.preventDefault();
    if ($(this).hasClass('disabled')) return false;

    saveConfig();
    return false;
});

$("#db-cancel").click(function (e) {
    e.preventDefault();
    if ($(this).hasClass('disabled')) return false;

    clearMsgs();
    resetForm(); 
    return false;
});

$(".db-test").click(function (e) {
    e.preventDefault();
    if ($(this).hasClass('disabled')) return false;

    testConnection();
    return false;
});

//inits data and tests conn
resetForm().success(testConnection);