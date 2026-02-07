<?php
function splash_colorpicker_field_edit_category( $term ) {
    $color = get_term_meta( $term->term_id, '_category_color', true );
    $color = ( ! empty( $color ) ) ? "#{$color}" : '#ffffff';

    ?>

    <tr class="form-field term-colorpicker-wrap">
        <th scope="row"><label for="term-colorpicker">Severity Color</label></th>
        <td>
            <input name="_category_color" value="<?php echo esc_attr($color); ?>" class="colorpicker" id="term-colorpicker" />
            <p class="description">This is the field description where you can tell the user how the color is used in the theme.</p>
        </td>
    </tr>

    <?php


}
add_action( 'category_edit_form_fields', 'splash_colorpicker_field_edit_category' );

function splash_splash_save_termmeta( $term_id ) {

    if( isset( $_POST['_category_color'] ) && ! empty( $_POST['_category_color'] ) ) {
        update_term_meta( $term_id, '_category_color', sanitize_hex_color_no_hash( $_POST['_category_color'] ) );
    } else {
        delete_term_meta( $term_id, '_category_color' );
    }

}
add_action( 'created_category', 'splash_save_termmeta' );
add_action( 'edited_category',  'splash_save_termmeta' );

function splash_category_colorpicker_enqueue( $taxonomy ) {

    if( null !== ( $screen = get_current_screen() ) && 'edit-category' !== $screen->id ) {
        return;
    }

    wp_enqueue_script( 'wp-color-picker' );
    wp_enqueue_style( 'wp-color-picker' );

}
add_action( 'admin_enqueue_scripts', 'splash_category_colorpicker_enqueue' );

function splash_colorpicker_init_inline() {

    if( null !== ( $screen = get_current_screen() ) && 'edit-category' !== $screen->id ) {
        return;
    }

    ?>

    <script>
        jQuery( document ).ready( function( $ ) {

            $( '.colorpicker' ).wpColorPicker();

        } ); // End Document Ready JQuery
    </script>

    <?php

}
add_action( 'admin_print_scripts', 'splash_colorpicker_init_inline', 20 );
?>