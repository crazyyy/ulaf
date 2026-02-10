jQuery( document ).ready(function() {
	jQuery('body').on('click', '.wdfi_upload_image_button', function(e) {
        e.preventDefault();
 
        var button = jQuery(this);
        var button_parent = jQuery(this).closest('.makelosielse');
        custom_uploader = wp.media({
            title: 'Insert image',
            library : {
                type : 'image'
            },
            button: {
                text: 'Set As Featured Image' // button label text
            },
            multiple: false // for multiple image selection set to true
        }).on('select', function() { // it also has "open" and "close" events 
            var attachment = custom_uploader.state().get('selection').toJSON();
            console.log(attachment);
            var htmlswork =''; 
            jQuery( attachment ).each(function( key,value ) {
			  console.log( value.id );
			  htmlswork +='<div class="wdfi_pdf_logo_prvw_image">';
			  htmlswork +='<img src="'+value.url+'" width="150"/>';
			  htmlswork +='<input type="hidden" name="'+button.attr('cname')+'" value="'+value.id+'" />';
			  htmlswork +='</div>';
              button_parent.find(".wdfi_remove_image_button").toggleClass('showbtnlcia');
			});
			button_parent.find(".wdfi_pdf_logo_prvw_image_main").html(htmlswork);
        })
        .open();
	});

	jQuery('body').on('click', '.wdfi_remove_image_button', function(e) {
        e.preventDefault();
        jQuery(this).closest('.makelosielse').find('.wdfi_pdf_logo_prvw_image').remove();
         jQuery(this).closest('.makelosielse').find(".wdfi_remove_image_button").toggleClass('showbtnlcia');
    });	
    jQuery('body').on('click', '.wdfi_addnewrule', function(e) {
        jQuery('.target_wdfi_addnewrule').show();
        return false;
    });
    jQuery('body').on('change', '.wdfi_addnewrule_posttype', function(e) {
        //jQuery('.target_wdfi_addnewrule').show();
        //return false;
        var wdfi_addnewrule_posttype = jQuery(this).val();
        if(wdfi_addnewrule_posttype==''){
            return false;
        }
        jQuery.ajax({
            url: ajax_object.ajaxurl,
            type: 'POST',
            data: {
                action: 'wdfi_addnewrule_get_taxonmy',
                post_type: wdfi_addnewrule_posttype,
                security: ajax_object.nonce 
            },
            success: function(response) {
                // handle the response from the server
                jQuery('.wdfi_makerespoe').html(response);
            },
            error: function(xhr, status, error) {
                // handle errors
            }
        });
    });

    jQuery('body').on('click', '.wdfi_savenewrule', function(e) {
        //jQuery('.target_wdfi_addnewrule').show();
        //return false;
        var wdfi_advacdedfilter_form = jQuery( "#wdfi_advacdedfilter_form" ).serialize();
        //console.log(wdfi_advacdedfilter_form['wdfi_addnewrule_posttype']);
        /*if(wdfi_addnewrule_posttype==''){
            return false;
        }*/
        jQuery.ajax({
            url: ajax_object.ajaxurl,
            type: 'POST',
            data: wdfi_advacdedfilter_form,
            success: function(response) {
                const obj = JSON.parse(response);
                if(obj.status=='failed'){
                    alert(obj.msg);
                }else{
                    alert('successfully added');
                    location.reload();
                }
                // handle the response from the server
               // jQuery('.wdfi_makerespoe').html(response);
            },
            error: function(xhr, status, error) {
                // handle errors
            }
        });
        return false;
    });
});