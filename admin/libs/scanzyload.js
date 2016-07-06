
//adds missing functions/objects to some object, following proto structure
function defaultValues(data, proto)
{
    //loops through all properties
    for(var i in proto)
    {        
        if (!(i in data)) data[i] = proto[i]; //copies default proto value/object if not found        
        if (proto[i] instanceof Object) data[i] = defaultValues(data[i], proto[i]); //recursively sets defaults                    
    }
    return data; //returns modified data
}

$.fn.extend({ //extends jquery 
    scanzyload: function (options) {

        //options default setup
        options = defaultValues(options, {
            request: { data: {} }, fetch: function () { },
            loading: { show: function () { }, hide: function () { } },
            error: { show: function () { }, hide: function () { } },
            empty: { show: function () { }, hide: function () { } }
        });

        //saves element root, options and load items function
        var x = { root: this, options: options, loadItems: function (requestdata) {
            var root = this.root;

            //loads request data
            if (requestdata == undefined) requestdata = options.request.data;
            else //uses data passed as param to update data stored in object
                for (var i in options.request.data) requestdata[i] = options.request.data[i];

            //shows loading, hides error/empty
            options.loading.show();
            options.error.hide();
            options.empty.hide();

            //sends request
            return $.ajax(options.request).success(function (data) {

                //checks empty                       
                if (data != null && data != "") {
                    if (data instanceof Object) {
                        if (Object.keys(data).length <= 0) { options.empty.show(); return; }
                    }
                    else if (data instanceof Array)
                        if (data.length <= 0) { options.empty.show(); return; }
                }
                
                var html = ""; //fetches data
                for (var i in data) html += options.fetch(i, data[i]);

                root.html(html); //and adds html to page
            })
            .fail(function () { options.error.show(); }) //shows error
            .always(function () { options.loading.hide(); }); //hides loading
        }
        };
        return x; //returns scanzyload object ref
    }
});