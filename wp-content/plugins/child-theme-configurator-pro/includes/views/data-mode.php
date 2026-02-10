<?php
// Exit if accessed directly
if ( !defined( 'ABSPATH' ) )exit;
?>

<?php if ( $this->ctc()->get_theme_count( 'child', TRUE ) ): ?>
<div class="clearfix ctc-data-mode-container">
    <form id="ctc_mode_form" method="post" action="">
        <div class="ctc-input-cell">
            <h4>
                <?php _e( 'Configure Child Theme:', 'child-theme-configurator' ); ?>
            </h4>
            <?php
            $this->ctc()->fingerprint_field();
            wp_nonce_field( 'ctc_update' );
            $this->render_theme_menu( 'child', $this->ctc()->state()->get_property( 'theme' ), 'ctc_theme_data', TRUE );
            ?>
        </div>
        <div class="ctc-input-cell-wide">
            <h4>
                <?php _e( 'Apply styles to:', 'child-theme-configurator' ); ?>
            </h4>

            <label><input type="radio" id="ctc_data_mode_stylesheet" name="ctc_data_mode" value="stylesheet" <?php checked( 'stylesheet', $this->ctc()->get_mode() ); ?> autocomplete="off" /> <?php _e( 'Theme Stylesheet', 'child-theme-configurator' ); ?></label>
            <label><input type="radio" id="ctc_data_mode_inline" name="ctc_data_mode" value="inline" <?php checked( 'inline', $this->ctc()->get_mode() ); ?> autocomplete="off" /> <?php _e( 'Custom CSS (Customizer)', 'child-theme-configurator' ); ?></label>
            <input type="submit" id="ctc_data_reload" name="ctc_data_reload" class="button button-secondary" value="<?php _e( 'Change View', 'child-theme-configurator' ); ?>" style="float:right"/>
        </div>
    </form>
</div>
<?php
endif;