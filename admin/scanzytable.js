$.fn.extend({
    scanzytable: function (options) {
        var t = { root: this, options: options, loadItems: function (requestdata) { //saves root, options and load items function
            var root = this.root;
            if (requestdata == undefined) requestdata = options['request']['data'];
            else //uses data passed as param to update data stored in object
                for (var attr in options['request']['data']) requestdata[attr] = options['request']['data'][attr];

            root.find(".loading-items").show(); root.find(".loading-items-error").hide();
            $.ajax({ url: options['request']['url'], method: options['request']['method'], data: requestdata })
            .success(function (data) { //sends request
                if (options['request']['check_empty'](data)) root.find(".no-items").show();
                else { //fetches table
                    root.find("tbody").html(options['request']['fetch'](data));
                    root.find(".items-search").focus();
                }
            }).always(function () { //hides loading text
                root.find(".loading-items").hide(); if ('done' in options['request']) options['request']['done']();
            }).fail(function (xhr, text, error) {
                root.find(".loading-items-error").show(); //handles errors
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
        var thead = ""; for (var i = 0; i < options['columns_names'].length; i++) thead += "<th>" + options['columns_names'][i] + "</th>";
        this.append('<div class="table-responsive"><table class="table"><thead><tr>' + thead + '</tr></thead><tbody></tbody></table></div>');

        //adds hidden texts for hints
        this.append('<div class="center-p grey"><p class="no-items" style="display:none;">There are currently no elements</p><p class="loading-items" style="display:none;">Loading data...</p> \
                        <p class="loading-items-error" style="display:none;"><span>Error while loading files data</span> <a href="" class="items-load-retry"">Retry</a></p> \
                        <p class="no-items-results" style="display:none;"><span>No rows matching searched string</span> <a href="" class="items-clear-search">Reset search</a></p></div>');

        //adds handlers
        this.find(".new-item").click(options['new_click']);
        this.find(".items-load-retry").click(function (e) { e.preventDefault(); t.loadItems(); });
        this.find(".items-clear-search").click(function (e) {
            e.preventDefault(); t.root.find(".items-search").val(''); //resets search input (shows all rows)
            t.root.find(".no-items-results").hide(); t.root.find("tr").show(); $(".items-search").focus();
        })
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