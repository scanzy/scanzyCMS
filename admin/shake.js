//shakes element
function shake(div) { 
    var interval = 80; var dist = 8; var times = 4; div.css('position', 'relative');
    for (var i = 0; i < times + 1; i++) div.animate({ left: ((i % 2 == 0 ? dist : dist * -1)) }, interval);
    div.animate({ left: 0 }, interval);
}