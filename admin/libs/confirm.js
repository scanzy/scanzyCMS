//inserts container
$("body").append('<div id="confirm" class="modal fade" role="dialog"><div class="modal-dialog"> \
                  <div class="modal-content"><div class="modal-body"><p></p></div><div class="modal-footer"> \
                  <button id="confirmok" type="button" class="btn btn-default" data-dismiss="modal">OK</button> \
                  <button id="confirmcancel" type="button" class="btn btn-default" data-dismiss="modal">Cancel</button> \
                  </div></div></div></div>');

//inits events and callbacks
confirmCallback = function() {};
$("#confirmok").click(function () { confirmCallback(true); });
$("#confirmcancel").click(function () { confirmCallback(false); })

//shows a confirm dialog
function showConfirm(html, callback) {
    confirmCallback = callback;
    $("#confirm .modal-body").html(html); 
    translate(document.getElementById("confirm"));                
    $("#confirm").modal(); 
}