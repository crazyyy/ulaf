# WP Data Logger

Logging vars and events for fast debug WordPress site.

## Description

1. Insert the hook `do_action( 'logger', $data );` in your code
2. Go to Tools > WP Logger

## Additional buttons
Use the `wpdl_add_button( string $name, callable $clb, string $btnClass = '' )` function to add a button to the logger page.
* $name - text for the button;
* $clb - callback function;
* $btnClass - class for the button (optional).

Click on the button to execute the function via WP-AJAX. If an Exception or Error occurs, it will be logged as well.

## Additional hooks
* wp_logger_button_panel
* wp_logger_inline_css
* wp_logger_inline_js
* wp_data_logger_print_data

## Constants
* WPDL_DISPLAY_LIMIT