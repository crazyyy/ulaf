jQuery( document ).ready( function(){
  // Ajax for error file
  jQuery('.imlt-error-file').on('click', function() {
    var ajaxUrl = window.location.protocol + "//" + window.location.host + "/" + "wp-admin/admin-ajax.php";
    jQuery.ajax({
      type : "POST",
      url : ajaxUrl,
      data : {

          action: "imlt_return_error_log_file"

  },

  success: function (response) {

      if(response) {

        jQuery('.imlt-donwld-container').css('display', 'inline');
        jQuery('#error_doc_file').attr('href', response).css('display', 'inline');


      }
  }

});

  });

  // Display form fields from Database table view

  var ImltExplodeTable = {
      triggerA               : '',
      triggerB               : '',
      triggerC               : '',
      selectorContentA       : '',
      selectorContentB       : '',
      selectorContentC       : '',

      init: function(args){
          var obj = this
          obj.setAttributes(obj, args)
          jQuery( obj.triggerA ).on('click', function(){
              obj.hide( obj.selectorContentC )
              obj.hide( obj.selectorContentB )
              obj.show( obj.selectorContentA )

          })
          jQuery( obj.triggerB ).on('click', function(){
              obj.hide( obj.selectorContentA )
              obj.show( obj.selectorContentB )
              obj.hide( obj.selectorContentC )
          })
          jQuery( obj.triggerC ).on('click', function(){
              obj.hide( obj.selectorContentA )
              obj.hide( obj.selectorContentB )
              obj.show( obj.selectorContentC )
          })
      },

      setAttributes: function(obj, args){
          for (var key in args) {
            obj[key] = args[key]
          }
      },

      show: function( selector ){
          jQuery( selector ).toggle( 300 )
      },

      hide: function( selector ){
          jQuery( selector ).fadeOut( 300 )
      }

  }
  jQuery(document).ready(function(){
      ImltExplodeTable.init({
          triggerA               : '#imlt_simple_query_trigger',
          triggerB               : '#imlt_custom_query_trigger',
          triggerC               : '#imlt_tabel_details_trigger',
          selectorContentA       : '#imlt_simple_query_content',
          selectorContentB       : '#imlt_custom_query_content',
          selectorContentC       : '#imlt_table_details_content'
      })
  })


  // Ajax for system report file

  jQuery('.imlt-system-report-file').on('click', function() {
     ajaxUrl = window.location.protocol + "//" + window.location.host + "/" + "wp-admin/admin-ajax.php";
    jQuery.ajax({

      type : "POST",
      url : ajaxUrl,
      data : {

          action: "imlt_export_system_report"

  },

  success: function (response) {

      if(response) {

        jQuery('#sys_doc_file').attr('href', response).css('display', 'inline');


      }
  }

});

  });

  // Ajax for phpinfo report file

  jQuery('.imlt-php-info-file').on('click', function() {
     ajaxUrl = window.location.protocol + "//" + window.location.host + "/" + "wp-admin/admin-ajax.php";

    jQuery.ajax({

      type : "POST",
      url : ajaxUrl,
      data : {

          action: "imlt_export_php_info"

  },

  success: function (response) {

      if(response) {

        jQuery('#php_info_file').attr('href', response).css('display', 'inline');


      }
  }

});

  });

  // Show password for temporary admin form


  jQuery("#imlt-button-face").click(function() {
    jQuery(".imlt-face").removeClass('dashicons-visibility').addClass('dashicons-hidden');
      if( jQuery("#imlt-psw").attr('type') === "password") {


          jQuery("#imlt-psw").prop({type: "text"});
          jQuery("#imlt_show_psw").text('Hide');

      } else if(jQuery("#imlt-psw").attr('type') === "text") {
        jQuery("#imlt-psw").prop({type: "password"});
        jQuery(".imlt-face").removeClass('dashicons-hidden').addClass('dashicons-visibility');


        jQuery("#imlt_show_psw").text('Show');
      }
  });

  jQuery('.js-do-delete-cron').on( 'click', function(){
      jQuery.ajax({
        type : "POST",
        url : window.location.protocol + "//" + window.location.host + "/" + "wp-admin/admin-ajax.php",
        data : {
            action    : "imlt_delete_cron_job",
            cronName  : jQuery(this).attr('data-cron_name')
        },
        success: function (response) {
            if (response!='0') {
                jQuery( '.js-'+ response ).remove()
            }
        }
      })
  })

  jQuery('.js-do-run-cron').on( 'click', function(){
      jQuery.ajax({
        type : "POST",
        url : window.location.protocol + "//" + window.location.host + "/" + "wp-admin/admin-ajax.php",
        data : {
            action    : "imlt_fire_cron_job",
            cronName  : jQuery(this).attr('data-cron_name')
        },
        success: function (response) {
            location.reload()
        }
      })
  })

  jQuery( '.js-speed-test-clear-history' ).on( 'click', function(){
      jQuery.ajax({
        type : "POST",
        url : window.location.protocol + "//" + window.location.host + "/" + "wp-admin/admin-ajax.php",
        data : {
            action    : "imlt_speed_test_clear_history"
        },
        success: function (response) {
            location.reload()
        }
      })
  })

  jQuery( '.js-hooks-and-actions-change-source' ).on( 'change', function(){
      var sourceValue = jQuery( this ).val()
      if ( sourceValue && sourceValue!='0' ){
          var targetUrl = jQuery(this).attr('data-base_url') + '&source=' + sourceValue
      } else {
          var targetUrl = jQuery(this).attr('data-base_url')
      }
      window.location.href = targetUrl
  })



  jQuery( "#imlt_table_names" ).change(function() {

    jQuery(location).attr('href', jQuery(this).val());

    });


    //dashboard header first button
jQuery('.navbar-toggler').click( function() {

  jQuery('.imlt-full').toggleClass('sidebar-lg-show' );

  });

jQuery('.card-header').click(function() {
  jQuery('.imlt_icon').toggleClass('icon-arrow-down');
  jQuery('.imlt_icon').toggleClass('icon-arrow-up');
});


jQuery('.asis_inac').click(function() {
  jQuery('.imlt_icon_inact').toggleClass('icon-arrow-down');
  jQuery('.imlt_icon_inact').toggleClass('icon-arrow-up');

});

//define const container for scrollbar sidebar menu
const container = document.querySelector('.sidebar-nav');
const ps = new PerfectScrollbar(container);

});
function imlt_checkbox_write_value( checkSelector, targetSelector, valueOnCheck, valueOnUncheck )
{
    if ( jQuery( checkSelector ).is(':checked') ){
        jQuery( targetSelector ).val( valueOnCheck );
    } else {
        jQuery( targetSelector ).val( valueOnUncheck );
    }
}
