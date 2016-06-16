//shared js code for admin framework

//gets GET param from url
function GetParam(p) {                 
    var params = window.location.search.substring(1).split('&');
    for (var i = 0; i < params.length; i++)
        if (p == params[i].split('=')[0])
            return params[i].split('=')[1];
}

//shows error popup (using messages.js)
function errorPopup(xhr, text, error) { showError("<strong>" + xhr.status + " " + error + ":</strong> " + xhr.responseText); }

//sends ajax post request showing popup on error
function ajax(data, callback) { 
    return $.post("./", data, function (data) { if (callback != undefined) callback(data); }).fail(errorPopup);
}

//logout button
$("#logout").click(function () { ajax({ action: 'logout' }, function () { window.location = "./login.php" }); });

//$("#topbarcontent").removeClass('in'); //collapses nav (if mobile mode)

$("a").each(function () //highlights navigation buttons elements
{ $(this).attr('href') == window.location.hash ? $(this).addClass("active") : $(this).removeClass("active"); });       