//inserts container
$("body").append("<div id='messages' style='position:fixed;bottom:0;text-align:center;width:100%;padding: 0 1em;'></div>");

function showMsg(msg, type)
{
    if (type == undefined) type = "success";
    $("#messages").append('<div class="alert alert-' + type + ' fade in"><a href="#" class="close" data-dismiss="alert" aria-label="close">&times;</a></div>');
    var el = $("#messages > div:last-child"); el.append(msg); //adds message
    el.delay(3000).fadeOut(3000, function () { $(this).remove(); }); //fades msg out
}

//different message types (colors)
function showError(msg){ showMsg(msg, "danger");}
function showWarning(msg){ showMsg(msg, "warning");}
function showInfo(msg){ showMsg(msg, "info");}