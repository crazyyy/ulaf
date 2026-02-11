<?php
// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) exit;
?>

<select class="ctc-select" id="<?php echo $id; ?>" name="<?php echo $id; ?>" 
            style="visibility:hidden" <?php // echo $this->ctc()->is_theme() ? '' : ' disabled ';  // v.2.3.0 - no longer using pluginmode ?> autocomplete="off" >
<?php
    foreach ( $themes as $slug => $theme )
        echo '<option value="' . $slug . '"' . ( $slug == $selected ? ' selected' : '' ) . '>' 
            . esc_attr( $theme[ 'Name' ] ) . '</option>' . LF; 
?>
</select>
<div style="display:none">
<?php 
    foreach ( $themes as $slug => $theme )
        include ( CHLD_THM_CFG_DIR . '/includes/views/themepreview.php' ); ?>
</div>
