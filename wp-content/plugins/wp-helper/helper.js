	function ChangeColor(id,color) {
		jQuery(id).css(
                "background-color", color);
	};
        
        
    function cpf_add_mceEditor_javascript(id) {
            jQuery(document).ready(function() {
                jQuery("#"+id).addClass("mceEditor");
                if ( typeof( tinyMCE ) == "object" && typeof( tinyMCE.execCommand ) == "function" ) {
                    tinyMCE.execCommand("mceAddControl", false, id);
                    tinyMCE.init({
                        mode : "textareas",
                        language : "en"
                     });
                }
            });      
      }
        
        jQuery(document).ready(function($) {
			// add more file
			$(".admin-add-file").click(function(){
				var $first = $(this).parent().find(".file-input:first");
				$first.clone().insertAfter($first).show();
				return false;
			});
            // use color picker
            jQuery(".datepicker").datepicker(); 
         
	});
