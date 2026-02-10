jQuery(document).ready( $ => {
    class CommonFilterFunctions {
        constructor() {
            this.daDelay = this.daDelay.bind(this);
            this.daNormalizeStr = this.daNormalizeStr.bind(this);
            this.daFilterByKeyword = this.daFilterByKeyword.bind(this);
        }

        // stackoverflow.com/a/37511463
        daNormalizeStr(str) {
            return str.normalize('NFD').replace(/[\u0300-\u036f]/g, "").toLowerCase();
        }

        daDelay(callback, param) {
            setTimeout(function() {
                callback(param);
            }, 800);
        }

        // stackoverflow.com/a/159140
        daFilterByKeyword(e, context, selectors) {
            var regex = new RegExp('((.|\\n)*)' + e);
            $('tbody#the-list tr', context).hide().filter((i,v) => {
                let combinedText = '';
                selectors.forEach(selector => {
                    combinedText += ' ' + $(v).find(selector).text();
                });
                let normalizedText = this.daNormalizeStr(combinedText);
                return regex.test(normalizedText);
            }).show();
        }
        
        createCustomSelectors() {
            $.expr[":"].containsnames = $.expr.createPseudo(function(arg) {
                return function(elem) {
                    var keywords = arg.split(',').map(function(keyword) {
                        return keyword.trim().toUpperCase();
                    });
                    var text = $(elem).text().toUpperCase();
                    
                    return keywords.every(function(keyword) {
                        return new RegExp('\\b' + keyword + '\\b').test(text);
                    });
                };
            });

            $.expr[":"].notContains = $.expr.createPseudo(function(arg) {
                return function(elem) {
                    var keywords = arg.split(',').map(function(keyword) {
                        return keyword.trim().toUpperCase();
                    });
                    var text = $(elem).text().toUpperCase();
                    
                    return keywords.every(function(keyword) {
                        return !new RegExp('\\b' + keyword + '\\b').test(text);
                    });
                };
            });
        }
    }

    // Make the class available globally
    window.B5F_Common = new CommonFilterFunctions();
});