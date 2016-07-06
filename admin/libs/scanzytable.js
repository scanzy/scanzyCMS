$.fn.extend({
    scanzytable: function (options) {

        //options default setup
        options = defaultValues(options, {
            request: { data: {} }, columns: {},
            fetch: {
                row: { start: function () { return "<tr>"; }, end: function () { return "</tr>"; } },
                cell: {}, content: {}
            },
            button: { show: false, text: "New", click: function () { } },
            search: { show: false, text: "Search..." }
        });

        //adds html for searchbar and new item btn
        if (options.search.show || options.button.show) {
            html = '<div class="row">';

            if (options.search.show) //searchbar
                html += '<div class="col-xs-8 col-md-6 col-lg-4"> \
                    <input type="text" class="form-control input-sm items-search" placeholder="' + options.search.text + '"/></div>';

            if (options.button.show) //new item button
                html += '<div class="col-xs-4 col-md-6 col-lg-8 right"> \
                    <button class="btn btn-sm btn-success new-item" type="button"> \
                    <span class="glyphicon glyphicon-plus"></span> <span>' + options.button.text + '</span></button> </div>';

            this.append(html + '</div>');
        }

        //adds html for table
        var thead = ""; for (var i in options.columns) thead += "<th>" + options.columns[i] + "</th>";
        this.append('<div class="table-responsive"><table class="table"><thead><tr>' + thead + '</tr></thead><tbody></tbody></table></div>');

        //adds hidden texts for hints
        this.append('<div class="center-p grey"><p class="no-items" style="display:none;">There are currently no elements</p><p class="loading-items" style="display:none;">Loading data...</p> \
            <p class="loading-items-error" style="display:none;"><span>Error while loading files data</span> <a href="" class="items-load-retry">Retry</a></p> \
            <p class="no-items-results" style="display:none;"><span>No rows matching searched string</span> <a href="" class="items-clear-search">Reset search</a></p></div>');

        //saves root, options and load items function
        var t = { root: this, options: options, loadItems: function (requestdata) {

            this.loader.loadItems(requestdata); //loads items
            this.root.find(".items-search").focus(); //focuses search
        } 
        };

        //inits loader
        t.loader = this.find("tbody").scanzyload({ request: options.request, fetch: function (i, data) {

            //fetches row
            html = options.fetch.row.start(i, data);
            for (var col in options.columns) {

                if (col in options.fetch.cell) { //opens tag
                    if ('start' in options.fetch.cell[col]) html += options.fetch.cell[col].start(col, data[col], i, data);
                } else html += "<td>";

                //puts content
                html += (col in options.fetch.content) ? options.fetch.content[col](col, data[col], i, data) : data[col]; 

                if (col in options.fetch.cell) { //closes tag
                    if('end' in options.fetch.cell[col]) html += options.fetch.cell[col].end(col, data[col], i, data);
                } else html += "</td>"; 
            }
            return html + options.fetch.row.end(i, data);
        },
            loading: t.root.find(".loading-items"),
            error: t.root.find(".loading-items-error"),
            empty: t.root.find(".no-items")
        });

        //handlers for new button, retry loading, reset search
        if (options.button.show) this.find(".new-item").click(options.button.click);
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