//inits html area
CodeMirror.fromTextArea(document.getElementById("template-html"), 
    {
        mode: "text/html", 
        matchTags:true,
        autoCloseTags: true,
        autoCloseBrackets: true
    });