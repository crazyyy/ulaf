/* Easy Menu Manager */

(function($) {
    const eamm = $('#toplevel_page_eamm_menu_page');
    if ( $('.eamm-expand').length==0 && $('.eamm-simplify').length==0 ){
        $.ajax({
            url: eamm_x.ajax_url,
            type: 'post',
            data: {action: 'eamm_toggle_option', ta: 0, nonce: eamm_x.nonce},
            dataType: 'json',
            success : function(data,status){
                eamm_menu_manager(data);
            },
            error: function (xhr, status, errorThrown) {
                console.log('FAIL', status + ' | ERR: ' + errorThrown);
            }
        });
    }

    if ( $('#toplevel_page_eamm_menu_page').length > 0 ){
        $('#toplevel_page_eamm_menu_page').on("click", "a.toplevel_page_eamm_menu_page", function(){
            //console.log('CLICK', 'You clicked me you swine!!');
            let eamm = $(this);
            $.ajax({
                url: eamm_x.ajax_url,
                type: 'post',
                data: {action: 'eamm_toggle_option', ta: 1, nonce: eamm_x.nonce},
                dataType: 'json',
                success : function(data,status){
                    if ( data['outcome'] ) {
                        eamm_menu_manager(data);
                    } else {
                        console.log('FAIL', 'NONCE Failure');
                    }
                },
                error: function (xhr, status, errorThrown) {
                    console.log('FAIL', 'AJAX call failed');
                    //$('#status').html('ERROR - AJAX Fail').fadeIn(300).fadeOut(5000);
                }
            });
            return false; //prevent the browser from following the link
        });
    };

    function eamm_menu_manager(my_data){
        //console.log('MY DATA', my_data);
        if ("toggle" in my_data){
            if ( my_data['toggle']==1 ){
                //console.log('SUCCESS', 'Menu Simplified');
                eamm.find('.wp-menu-image').removeClass('eamm-simplify').addClass('eamm-expand');
                eamm.find('.wp-menu-name').text('Show Full Menu');
            } else {
                //console.log('SUCCESS', 'Menu Expanded');
                eamm.find('.wp-menu-image').removeClass('eamm-expand').addClass('eamm-simplify');
                eamm.find('.wp-menu-name').text('Simplify Menu');
            }
        }
        if ("items" in my_data){
            let my_items = my_data['items']
            //console.log('ITEMS', my_items);
            for (const key in my_items) {
                if ( my_data['toggle'] == 1 ){
                    if (my_items[key].hide == 1){
                        //console.log('MENU - HIDE',my_items[key].id);
                        $('#'+my_items[key].id).hide();
                    }
                } else {
                    $('#'+my_items[key].id).show();
                }
            }
        }
    }


})(jQuery);
