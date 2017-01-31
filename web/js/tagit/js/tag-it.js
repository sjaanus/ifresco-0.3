

(function($) {

	$.fn.tagit = function(options) {
        var items = [];
		var el = this;

		var BACKSPACE		= 8;
		var ENTER			= 13;
		var SPACE			= 32;
		var COMMA			= 44;

		// add the tagit CSS class.
		el.addClass("tagit");
        
        if (options.submitname == null || options.submitname.length == 0 || typeof options.submitname == 'undefined')
            options.submitname = "tagit-values";
            
        if (options.submitid == null || options.submitid.length == 0 || typeof options.submitid == 'undefined')
            options.submitid = "tagit-values";
            
        if (options.values == null || options.values.length == 0 || typeof options.values == 'undefined')   
            options.values = [];

		// create the input field.
		var html_input_field = "<li class=\"tagit-new\"><input name=\""+options.submitname+"\" id=\""+options.submitid+"\" type=\"hidden\" style=\"\" /><input class=\"tagit-input\" type=\"text\" style=\"\" /></li>\n";
		el.html(html_input_field);

		tag_input = el.children(".tagit-new").children(".tagit-input");
        
        if (options.values.length > 0) {
            for (var i=0; i < options.values.length; i++) {
            var obj =  options.values[i];
             var typed = obj.name;

            //var typed = tag_input.val();
                typed = typed.replace(/,+$/,"");
                typed = typed.trim();

                if (typed != "") {
                    if (is_new (typed)) {
                        var currentval = $("#"+options.submitid).val();
                        if (currentval.length > 0) {
                            currentval += ",";
                        }
                        currentval += typed;
                        $("#"+options.submitid).val(currentval);
                        create_choice (typed);
                    }
                    // Cleaning the input.
                    //tag_input.val("");
                } 
            }   
        }

		$(this).click(function(e){

			if (e.target.tagName == 'A') {
				// Removes a tag when the little 'x' is clicked.
				// Event is binded to the UL, otherwise a new tag (LI > A) wouldn't have this event attached to it.
				$(e.target).parent().remove();
                var tagName = $(e.target).parent().attr("name");
                remove(tagName);
			}
			else {
				// Sets the focus() to the input field, if the user clicks anywhere inside the UL.
				// This is needed because the input field needs to be of a small size.
				tag_input.focus();
			}
		});
        
        

		tag_input.keypress(function(event){

			if (event.which == BACKSPACE) {
				if (tag_input.val() == "") {
					// When backspace is pressed, the last tag is deleted.
					$(el).children(".tagit-choice:last").remove();
                    var tagName = $(el).children(".tagit-choice:last").attr("name");
                    remove(tagName);
				}
			}
			// Comma/Space/Enter are all valid delimiters for new tags.
			else if (event.which == COMMA || event.which == SPACE || event.which == ENTER) {
				event.preventDefault();

				var typed = tag_input.val();
				typed = typed.replace(/,+$/,"");
				typed = typed.trim();

				if (typed != "") {
					if (is_new (typed)) {
                        //alert($("#"+options.submitid));
                        var currentval = $("#"+options.submitid).val();
                        if (currentval.length > 0) {
                            currentval += ",";
                        }
                        currentval += typed;
                        $("#"+options.submitid).val(currentval);
						create_choice (typed);
					}
					// Cleaning the input.
					tag_input.val("");
				}
			}
		});

		/*tag_input.autocomplete({
            
			//source: options.availableTags, 
            source: function(request, response) {

                $.getJSON(options.url, {
                    term: extractLast(request.term)
                }, response);
            },
			select: function(event,ui){
				if (is_new (ui.item.value)) {
					create_choice (ui.item.value);
				}
				// Cleaning the input.
				tag_input.val("");

				// Preventing the tag input to be update with the chosen value.
				return false;
			}
		});*/
        
        tag_input.autocomplete(options.url, {
            delay: 150,
            parse: function(data) {
                var rows = new Array();
                data = data.replace(/\[/,"");
                data = data.replace(/\]/,"");
                var split = data.split(","); 
                for (var i = 0; i < split.length; i++) {
                    var value = split[i];
                    value = value.replace(/"/,"");
                    value = value.replace(/\"/,"");
                    if (value != "" && value != null) {
                        rows[i] = { data:value, value:value, result:value }
                    }
                }
                return rows;
            },

            formatItem: function(data, i, n, value) {
                return data;
            }
        }).result(function(data, value) {
            if (is_new (value)) {
                var currentval = $("#"+options.submitid).val();
                if (currentval.length > 0) {
                    currentval += ",";
                }
                currentval += value;
                $("#"+options.submitid).val(currentval);                
                create_choice (value);
            }
            // Cleaning the input.
            tag_input.val("");

            // Preventing the tag input to be update with the chosen value.
            return false;
        });
        
        
        function remove(name) {
            var currentVal = $("#"+options.submitid).val();
            
            var splitVal = split(currentVal);
            for (var i= 0; i < splitVal.length; i++) {
                if (splitVal[i] == name) {
                    //splitVal.remove(i);
                    splitVal.splice(i,1);
                }
            }  

            var valueNew = splitVal.join(",");  
            
            $("#"+options.submitid).val(valueNew);       
        }
        
        function split(val) {
            return val.split(/,\s*/);
        }

        function extractLast(term) {

            return split(term).pop();
        } 
        
        function stripHTML(oldString) {
            return oldString.replace(/<&#91;^>&#93;*>/g, "");
        }

		function is_new (value){
			var is_new = true;
			this.tag_input.parents("ul").children(".tagit-choice").each(function(i){
				n = $(this).children("input").val();
				if (value == n) {
					is_new = false;
				}
			})
			return is_new;
		}
		function create_choice (value){
			var el = "";
			el  = "<li class=\"tagit-choice\" name=\""+value+"\">\n";
			el += value + "\n";
			el += "<a class=\"close\">x</a>\n";
			el += "<input type=\"hidden\" style=\"display:none;\" id=\"tagField\" value=\""+value+"\" name=\"item[tags][]\">\n";
			el += "</li>\n";
			var li_search_tags = this.tag_input.parent();
			$(el).insertBefore (li_search_tags);
			this.tag_input.val("");
		}
        
        function returnElements() {

            return items;
            
        }
        return this;
	};

	String.prototype.trim = function() {
		return this.replace(/^\s+|\s+$/g,"");
	};

})(jQuery);
