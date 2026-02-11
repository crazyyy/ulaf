(function($) {
    function copyToClipboard(element) {
        var $temp = $("<input>");
        
        $("body").append($temp);
        
        $temp.val($(element).text()).select();
        
        document.execCommand("copy");
        
        $temp.remove();

        $('.bucs-notification').addClass('show');
        $('.bucs-notification').html('Text successfully copied to clipboard.');

        setTimeout(function() {
            $('.bucs-notification').removeClass('show');
        }, 1500)
    }

    // Add overlay html
    if($('.submit-inline').length) {
        $('.submit-inline').prepend(ajaxObj.overlay_html);
    }

    $(window).load(function() {
        // Setup for history snippets
        setTimeout(function() {
            var _i = 1;

            // Check element code snippets exists or not
            if($('.bucs-codemirror').length) {
                $('.bucs-codemirror').each(function() {
                    var _id_editor = $(this).attr('id');
            
                    // Initialize editor
                    var editor = CodeMirror.fromTextArea(document.getElementById(_id_editor), {
                        lineNumbers: true,
                        readOnly: true,
                    });
                    
                    // Save or display editor
                    editor.save();

                    // Remove overlay and another element when completed
                    if(_i === $('.bucs-codemirror').length) {
                        $('.bucs-tab-editor').each(function() {
                            if($(this).data('hidden') == 'hidden') {
                                $(this).addClass('hidden');
                            }
                        })
                        
                        $('.bucs-wrapper').addClass('hidden');
                        $('.bucs-overlay').addClass('hidden');
                    }

                    _i++;
                })
            } else { // If element code snippets empty
                // Remove overlay and another element when completed
                $('.bucs-tab-editor').each(function() {
                    if($(this).data('hidden') == 'hidden') {
                        $(this).addClass('hidden');
                    }
                })

                $('.bucs-wrapper').addClass('hidden');
                $('.bucs-overlay').addClass('hidden');
            }
        }, 1500)
        
        // Add button html
        if($('.submit-inline').length) {
            $('.submit-inline').prepend(ajaxObj.btn_html);
        }

        // If button "See History" clicked
        if($('#seeHistory').length) {
            $('#seeHistory').on('click', (function(e) {
                e.preventDefault();

                // Show wrapper
                $('.bucs-wrapper').removeClass('hidden');
                $('.bucs-wrapper').addClass('show');

                // Close wrapper / history snippets
                $(document).find('#hideHistory').click(function() {
                    e.preventDefault();
        
                    $('.bucs-wrapper').removeClass('show');
                    $('.bucs-wrapper').addClass('hidden');
                })

                // When navigation of tab changed
                $(document).find('#bucs-navtab-editor').on('change', (function() {
                    $('.bucs-tab-editor').addClass('hidden');
                    $('.bucs-tab-editor[data-id="' + $(this).val() + '"]').removeClass('hidden');
                }))

                // Close wrapper / history snippets
                $(document).find('.bucs-hide-history').click(function() {
                    e.preventDefault();
        
                    $('.bucs-wrapper').removeClass('show');
                    $('.bucs-wrapper').addClass('hidden');
                })

                // Pending feature
                // $(document).find('.bucs-copy-to-clipboard').on('click', (function() {
                //     copyToClipboard('#' + $(this).data('id'));
                // }))
            }))
        }

        // Add main element html
        if($('#wpbody').length) {
            $('#wpbody').prepend(ajaxObj.main_html);
        }
    })
})(jQuery);