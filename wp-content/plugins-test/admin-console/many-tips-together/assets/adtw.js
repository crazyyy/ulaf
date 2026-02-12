jQuery(document).ready( $=>{
    function toggleDescription() {
        if ($('#adminbar_custom_enable').val() == '1') {
            $('#b5f_admin_tweaks-adminbar_custom_enable .description').hide();
        } else {
            $('#b5f_admin_tweaks-adminbar_custom_enable .description').show();
        }
    }

    // Initial check on page load
    toggleDescription();

    // Bind the function to the switch change event
    $('#adminbar_custom_enable').on('change', function() {
        toggleDescription();
    });
});