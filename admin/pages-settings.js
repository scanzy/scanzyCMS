
//database connection settings form

//clears messages
function clearDBMsgs() { $("#db-msgs > div").addClass('hidden'); }

//form reset
function resetForm()
{
    //hides load errors
    $("#db-load-error").addClass('hidden');

    //shows loading info
    $("#db-load").removeClass('hidden');    

    enableOnInput(); //adds handler
    
    //loads data from server
    return ajax("./apis/config/get.php", null, function (data) {

        //sets form
        $("#dbhost").val(data['DB']['host']);
        $("#dbname").val(data['DB']['name']);
        $("#dbuser").val(data['DB']['user']);
        $("#dbpwd").val(data['DB']['pwd']);

    }) //shows error on loading fail
    .fail(function () { shake($("#db-load-error").removeClass('hidden')); })
    .always(function () { $("#db-load").addClass('hidden'); }); //hides loading info
}

//test connection
function testConnection() {
    //shows testing info
    $("#db-test").addClass('hidden');
    $("#db-test-ok").addClass('hidden');
    $("#db-test-error").addClass('hidden');
    $("#db-testing").removeClass('hidden');

    //removes all tooltips
    hideAllTooltips();

    //test config
    return ajax("./apis/config/test.php", {
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
    ajax("./apis/config/update.php", {
        DB: {
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

        clearDBMsgs();

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

    clearDBMsgs();
    resetForm(); 
    return false;
});

$(".db-test").click(function (e) {
    e.preventDefault();
    if ($(this).hasClass('disabled')) return false;

    testConnection();
    return false;
});

//database setup reset form

//clears messages
function clearDBMsgs2() { $("#db-msgs2 > div").addClass('hidden'); }

//setups database
function setupDatabase()
{
    clearDBMsgs2(); //shows setting up info
    $("#db-setting-up").removeClass('hidden');
    $(".db-button").addClass('disabled');

    //sends request and shows result
    return ajax("./apis/db/setup.php", null, function() { $("#db-setup-ok").removeClass('hidden'); })
    .always(function() { 
        $(".db-button").removeClass('disabled');
        $("#db-setting-up").addClass('hidden');
    })
    .fail(function() { shake($("#db-setup-error").removeClass('hidden')); })
}

//resets database
function resetDatabase()
{
    clearDBMsgs2(); //shows resetting info
    $("#db-resetting").removeClass('hidden');
    $(".db-button").addClass('disabled');

    //sends request and shows result
    return ajax("./apis/db/reset.php", null, function() { $("#db-reset-ok").removeClass('hidden'); })
    .always(function() { 
        $(".db-button").removeClass('disabled');
        $("#db-resetting").addClass('hidden'); 
    })
    .fail(function() { shake($("#db-reset-error").removeClass('hidden')); })
}

//tests database
function testDatabase()
{
    clearDBMsgs2(); //shows testing info
    $("#db-testing2").removeClass('hidden');
    $(".db-button").addClass('disabled');

    //sends request and shows result
    return ajax("./apis/db/test.php", null, function() { $("#db-test2-ok").removeClass('hidden'); })
    .always(function() { 
        $(".db-button").removeClass('disabled'); 
        $("#db-testing2").addClass('hidden');
    })
    .fail(function() { shake($("#db-test2-error").removeClass('hidden')); })
}

//buttons callbacks
$("#db-setup").click(function(e) {
    e.preventDefault();
    if ($(this).hasClass('disabled')) return false;

    setupDatabase();
    return false;
});

$("#db-test2").click(function(e) {
    e.preventDefault();
    if ($(this).hasClass('disabled')) return false;

    testDatabase();
    return false;
});

$("#db-reset").click(function(e) {
    e.preventDefault();
    if ($(this).hasClass('disabled')) return false;

    showConfirm("<p>Are you sure to reset database? This would ERASE ALL DATA</p>", function(x){
        if (x == true) resetDatabase();
    });
    return false;
});

//inits data and tests conn and test db
resetForm().success(testConnection.success(testDatabase));