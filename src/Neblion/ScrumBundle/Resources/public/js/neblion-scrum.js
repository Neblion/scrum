/*
 * Neblion/scrum javascript jquery plugins
 */


(function($){
    $.fn.extend({
        refreshCumul: function(selector, attrName) {
            var elmt = $(this);
            var cumul = 0;
            
            if (typeof(selector) == "string" && typeof(attrName) == "string") {
                $(selector).each(function(index) {
                    cumul += parseInt($(this).attr(attrName));
                });
            } else if (typeof(selector) == "string" && typeof(attrName) == "undefined") {
                $(selector).each(function(index) {
                    cumul += parseInt($(this).text());
                });
            }
            
            if (typeof(attrName) == "undefined") {
                elmt.text(cumul);
            } else {
                elmt.attr(attrName, cumul);
            }
        },
        refreshProgressBar: function(elementValue, elementTotal, attrName) {
            var elmt = $(this);
            
            if (typeof(elementValue) == "string" && typeof(attrName) == "string") {
                var value = $(elementValue).attr(attrName);
            } else if (typeof(elementValue) == "string" && typeof(attrName) == "undefined") {
                var value = $(elementValue).text();
            } else {
                var value = 0;
            }
            
            if (typeof(elementTotal) == "string" && typeof(attrName) == "string") {
                var total = $(elementTotal).attr(attrName);
            } else if (typeof(elementTotal) == "string" && typeof(attrName) == "undefined") {
                var total = $(elementTotal).text();
            } else {
                var total = 0;
            }
            
            if (total == 0) {
                elmt.css("width", width + '%');
            } else {
                var width = Math.floor(value / total * 100);
                elmt.css("width", width + '%');
            }
        }
    });
 
})(jQuery);



