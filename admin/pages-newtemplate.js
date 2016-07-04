//inits html area
CodeMirror.fromTextArea(document.getElementById("template-html"), 
    {
        mode: "text/html", 
        autoCloseTags: true,
        autoCloseBrackets: true
    });