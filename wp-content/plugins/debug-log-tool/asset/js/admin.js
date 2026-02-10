jQuery(document).ready(function() {

  // log table scripts.
  var table = jQuery('#debug-log-table').DataTable({
      processing: true,
      serverMethod: 'post',
      ajax: { 
        url: wpdebugtool.ajax_url,
        data: {
            'action': 'wpdt_get_debug_logs',
            '_ajax_nonce': wpdebugtool.nonce,
          }									
      },
      'columns': [
        { data: 'type' },
        { data: 'log', orderable: false },
        { data: 'copy', orderable: false, searchable: false, className: 'wpdt-copy-cell' },
        { data: 'links', orderable: false, searchable: false, className: 'wpdt-link-cell' },
        { data: 'date' },
      ],
      'pageLength': 50,
      "lengthMenu": [[20, 50, 100, 200], [20, 50, 100, 200]],
      "order": [[4, "asc"]],
      columnDefs: [
        { width: '130px', targets: 0 },
        { width: '50px', targets: 2 },
        { width: '70px', targets: 3 },
        { width: '150px', targets: 4 },
      ],
      language: {
        emptyTable: "No logs found",
      }
  });

  jQuery('#error-type-filter').on('change', function() {
    table.column(0).search(this.value).draw();
  });

  jQuery('#entriesPerPage').on('change', function() {
    table.page.len(this.value).draw();
  });

  jQuery('#customSearch').on('keyup', function() {
    table.search(this.value).draw();
  });

  jQuery('#debug-log-table').on('click', '.wpdt-view-toggle', function () {
    const container = jQuery(this).prev('.wpdt-log-value');
    container.toggleClass('expanded');
    jQuery(this).text(container.hasClass('expanded') ? 'View Less' : 'View More');
  });

  jQuery('#wpdt_auto_refresh').on('change', function() {
    var isChecked = jQuery(this).is(':checked');
    
    if( isChecked ) {
      wpdt_show_alert('Auto refresh enabled!', 'success');
    }else{
      wpdt_show_alert('Auto refresh disabled!', 'warning');
    }

    jQuery.ajax({
      url: wpdebugtool.ajax_url,
      type: 'POST',
      data: {
        action: 'wpdt_set_auto_refresh',
        auto_refresh: isChecked ? 1 : 0,
        _ajax_nonce: wpdebugtool.nonce
      },
      success: function(response) {
      }
    });
    if (isChecked) {
      startAutoRefresh();
    } else {
      stopAutoRefresh();
    }
  });

  // Auto refresh logic
  var autoRefreshInterval = null;
  function startAutoRefresh() {
    stopAutoRefresh();
    autoRefreshInterval = setInterval(function() {
      table.ajax.reload(null, false);
    }, 60000); // 1 minute
  }
  function stopAutoRefresh() {
    if (autoRefreshInterval) {
      clearInterval(autoRefreshInterval);
      autoRefreshInterval = null;
    }
  }

  // Optionally, check the initial state on page load
  if (jQuery('#wpdt_auto_refresh').is(':checked')) {
    startAutoRefresh();
  }

  // manual refresh button
  jQuery('#wpdb-refresh-log').on('click', function () {
    table.ajax.reload(null, false);
    wpdt_show_alert('Log refreshed!', 'success');
  });

  // server info - cookies table scripts.
  jQuery('#wpdt-cookies-table').DataTable({
    pageLength: 10
  });

  // server info - transient table scripts.
  jQuery('#wpdt-transients-table').DataTable({
    pageLength: 10,
    columnDefs: [
      { width: '350px', targets: 0 },
      { width: '150px', targets: 2 }
    ],
  });

  jQuery('#wpdt-transients-table').on('click', '.wpdt-view-toggle', function () {
    const container = jQuery(this).prev('.wpdt-transient-value');
    container.toggleClass('expanded');
    jQuery(this).text(container.hasClass('expanded') ? 'View Less' : 'View More');
  });

  // server info - cron jobs table scripts.
  jQuery('#wp-cron-table').DataTable({
    pageLength: 10,
    columnDefs: [
      { width: '150px', targets: 2 },
      { width: '150px', targets: 3 }
    ],
  });

  // Copy log to clipboard.
  jQuery('#debug-log-table').on('click', '.wpdt-copy-icon', function () {
    const textToCopy = jQuery(this).data('log');

    navigator.clipboard.writeText(textToCopy).then(() => {
      const $btn = jQuery(this);
      $btn.addClass('copied');

      // Optional: change icon or text briefly
      setTimeout(() => $btn.removeClass('copied'), 1500);
      wpdt_show_alert('Log copied to clipboard!', 'info');
    }).catch(err => {
      alert('Failed to copy: ' + err);
    });
  });

  // Gemini help link.
  jQuery(document).on('click', '.wpdt-gemini-link', function (e) {
    e.preventDefault();
    const log = jQuery(this).data('log');
    navigator.clipboard.writeText(log).then(() => {
        wpdt_show_alert('Log copied to clipboard! Paste it in Gemini.', 'success');
    });
  });

  // Group logs setting.
  jQuery('#wpdt_group_logs').on('change', function() {
    var isChecked = jQuery(this).is(':checked');
    
    if( isChecked ) {
      wpdt_show_alert('Group logs enabled!', 'success');
    }else{
      wpdt_show_alert('Group logs disabled!', 'warning');
    }

    jQuery.ajax({
      url: wpdebugtool.ajax_url,
      type: 'POST',
      data: {
        action: 'wpdt_set_group_logs',
        group_logs: isChecked ? 1 : 0,
        _ajax_nonce: wpdebugtool.nonce
      },
      success: function(response) {
        table.ajax.reload(null, false);
      }
    });
  });
});

function wpdt_save_general_settings(e) {
  
  var dataform = new FormData(jQuery('form.wpdt-general-settings')[0]);
  jQuery.ajax({
    url: wpdebugtool.ajax_url,
    type: 'POST',
    data: dataform,
    processData: false,
    contentType: false,
    error: function (response) {
      console.log(response);
    },
    success: function (response, textStatus, xhr) {
      wpdt_show_alert('Settings saved successfully!', 'success');
    }
  });
}

function wpdt_reset_general_settings(el, nonce) {

	var data = { action: 'wpdt_reset_general_settings', _ajax_nonce: nonce };
	jQuery.post(
		wpdebugtool.ajax_url,
		data,
		function (res) {
      location.reload();
		}
	);
}

function wpdt_show_alert( message, type = 'success' ) {
  const typeClass = {
    success: 'wpdt-alert-success',
    warning: 'wpdt-alert-warning',
    error: 'wpdt-alert-error',
    info: 'wpdt-alert-info',
  }[type] || 'wpdt-alert-success';

  const $alert = jQuery(`<div class="wpdt-alert ${typeClass}">${message}</div>`);
  jQuery('#wpdt-alert-container').append($alert);

  // Auto remove after 3 seconds
  setTimeout(() => {
    $alert.fadeOut(400, function () {
      jQuery(this).remove();
    });
  }, 3000);
}
