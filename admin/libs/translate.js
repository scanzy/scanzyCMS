langdata = undefined;

//gets language data and translates body
lang = navigator.language || navigator.userLanguage;
$.ajax({ dataType: "json", url: "locales/" + lang + ".json", cache:true, success: function (data) {
    langdata = data; document.getElementsByTagName("html")[0].setAttribute("lang", lang);
    translatenow(document);
}});

//translates only if lang data loaded
function translate(element) { if (langdata != undefined) translatenow((element != undefined) ? element : document); }

function translatenow(element) {    
    //translates text
    var e = element.querySelectorAll("span, a, h1, h2, h3, h4, h5, th, p, label, button, option");
    for (var i = 0; i < e.length; i++) if (e[i].innerHTML in langdata) e[i].innerHTML = langdata[e[i].innerHTML];

    //translates attributes
    var a = ["alt", "title", "placeholder", "data-original-title"];
    e = element.querySelectorAll("img, input, a, button");
    for (var i = 0; i < e.length; i++) for (var j = 0; j < a.length; j++)
    { var attr = e[i].getAttribute(a[j]); if (attr != null && attr != "") 
        if (attr in langdata) e[i].setAttribute(a[j], langdata[attr]); }
}