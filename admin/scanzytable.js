$.fn.extend({
    scanzytable: function (options) {

        //saves root, options and load items function
        var t = { root: this, options: options, loadItems: function (requestdata) {
            var root = this.root;

            //loads request data
            if (requestdata == undefined) requestdata = options['request']['data'];
            else //uses data passed as param to update data stored in object
                for (var attr in options['request']['data']) requestdata[attr] = options['request']['data'][attr];

            //loading message
            root.find(".loading-items").show(); root.find(".loading-items-error").hide();

            //sends request
            $.ajax({ url: options['request']['url'], method: options['request']['method'], data: requestdata })
            .success(function (data) {

                //default no rows check callback
                if (!('check_empty' in options))
                    options['check_empty'] = function (data) { return (data.length == 0); }

                //checks no rows
                if (options['check_empty'](data)) root.find(".no-items").show();
                else {                    

                    //default behaviour (if no callbacks)
                    if (!('fetch' in options)) options['fetch'] = {};
                    if (!('content' in options['fetch'])) options['fetch']['content'] = {};

                    var html = ""; //detects fetch mode
                    if (options['fetch']['content'] instanceof Function) //single function
                        html = options['fetch']['content'](data);
                    else {                        

                        //default behaviour (if not all callbacks specified)
                        for (var col in options['columns_names'])
                            if (!(col in options['fetch']['content']))
                                options['fetch']['content'][col] = function (data) { return data; };
                        
                        //default open-close row tags
                        if (!('row' in options['fetch'])) options['fetch']['row'] = {};
                        if (!('start' in options['fetch']['row'])) options['fetch']['row']['start'] = function() { return "<tr>"; };
                        if (!('end' in options['fetch']['row'])) options['fetch']['row']['end'] = function() { return "</tr>"; };

                        //default open-close cell tags
                        if (!('cell' in options['fetch'])) options['fetch']['cell'] = {};
                        if (!('start' in options['fetch']['cell'])) options['fetch']['cell']['start'] = function() { return "<td>"; };
                        if (!('end' in options['fetch']['cell'])) options['fetch']['cell']['end'] = function() { return "</td>"; };

                        //one function per column                        
                        for (var i = 0; i < data.length; i++) {

                            html += options['fetch']['row']['start'](i, data[i]); //for each row
                            for (var col in options['columns_names']) {
                                var celldata = undefined;
                                if (col in data[i]) celldata = data[i][col]; //for each cell
                                html += '' + //opens tag, puts content and closes tag
                                    options['fetch']['cell']['start'](col, celldata, data[i]) + 
                                    options['fetch']['content'][col](celldata, data[i]) + 
                                    options['fetch']['cell']['end'](col, celldata, data[i]);
                            }

                            html += options['fetch']['row']['end'](i, data[i]);
                        }
                    }

                    //fetches table
                    root.find("tbody").html(html);
                    root.find(".items-search").focus();
                }
            })

            //hides loading text and triggers callback            
            .always(function () {
                root.find(".loading-items").hide();
                if ('done' in options['request']) options['request']['done']();
            })

            //handles errors, shows message and uses callback            
            .fail(function (xhr, text, error) {
                root.find(".loading-items-error").show();
                if ('error' in options['request']) options['request']['error'](xhr, text, error);
            });
        }
        };

        //adds html for searchbar and new item btn
        this.append('<div class="row"><div class="col-xs-8 col-md-6 col-lg-4"> \
        <input type="text" class="form-control input-sm items-search" placeholder="' +
        (('search_placeholder' in options) ? options['search_placeholder'] : "") + '"/></div> \
        <div class="col-xs-4 col-md-6 col-lg-8 right"><button class="btn btn-sm btn-success new-item" type="button"> \
        <span class="glyphicon glyphicon-plus"></span> <span>' +
        (('new_text' in options) ? options['new_text'] : "New") + '</span></button> </div></div>');

        //adds html for table
        var thead = ""; for (var i in options['columns_names']) thead += "<th>" + options['columns_names'][i] + "</th>";
        this.append('<div class="table-responsive"><table class="table"><thead><tr>' + thead + '</tr></thead><tbody></tbody></table></div>');

        //adds hidden texts for hints
        this.append('<div class="center-p grey"><p class="no-items" style="display:none;">There are currently no elements</p><p class="loading-items" style="display:none;">Loading data...</p> \
                        <p class="loading-items-error" style="display:none;"><span>Error while loading files data</span> <a href="" class="items-load-retry"">Retry</a></p> \
                        <p class="no-items-results" style="display:none;"><span>No rows matching searched string</span> <a href="" class="items-clear-search">Reset search</a></p></div>');

        //handlers for new button, retry loading, reset search
        if ('new_click' in options) this.find(".new-item").click(options['new_click']);
        this.find(".items-load-retry").click(function (e) { e.preventDefault(); t.loadItems(); });
        this.find(".items-clear-search").click(function (e) {
            e.preventDefault(); t.root.find(".items-search").val(''); //resets search input (shows all rows)
            t.root.find(".no-items-results").hide(); t.root.find("tr").show(); $(".items-search").focus();
        })

        //handler for searchbar
        this.find(".items-search").bind('input', function () {
            var searchstr = $(this).val().trim().toLowerCase(); //gets input
            if (searchstr == "") t.root.find("tr").show(); //shows all if no input
            t.root.find("tbody tr").each(function () {
                if ($(this).text().toLowerCase().indexOf(searchstr) == -1) //finds elements
                    $(this).hide(); else $(this).show();
            });
            (t.root.find("tbody tr:visible").length == 0) ? // controls "no items found" visibility 
            t.root.find(".no-items-results").show() : t.root.find(".no-items-results").hide();
        });

        return t; //returns table object ref
    }
});