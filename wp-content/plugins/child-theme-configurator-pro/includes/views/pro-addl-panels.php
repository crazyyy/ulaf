<?php
// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) exit;
if ( $this->ui()->enqueue_is_set() ):?>

<div id="all_styles_panel" class="ctc-option-panel<?php echo 'all_styles' == $active_tab ? ' ctc-option-panel-active' : ''; ?>" <?php echo $hidechild; ?> >
  <form id="all_styles_filter_form" action="" class="clearfix">
    <input name="ctc_all_styles_filter" id="ctc_all_styles_filter" value="" type="text" placeholder="<?php _e( 'Find Selectors by Text', 'child-theme-configurator' ); ?>" />
    <?php 
    if ( $locations = get_transient( 'ctcp_nav_menus_' . $parent ) ):
        $options    = array();
        foreach ( $locations as $locationid => $locationname ):
            $transient_key = 'ctcp_nav_menu_' . $parent . '_' . $locationid;
            //echo 'transient: ' . $transient_key . LF;
            $matchstr   = array();
            if ( ( $menuargs = get_transient( $transient_key ) ) ):
                if ( !empty( $menuargs[ 'menu_id' ] ) ) $matchstr[] = '#' . $menuargs[ 'menu_id' ];
                if ( !empty( $menuargs[ 'menu_class' ] ) )
                    foreach ( preg_split( "/\s+/", $menuargs[ 'menu_class' ] ) as $class ) $matchstr[] = '.' . $class;
                $options[]  = '<option value="' . implode( '|', $matchstr ) . '">' . $locationname . '</option>' . LF;
                //echo 'nav menu: ' . $locationid . LF;
                //print_r( $menuargs );
            endif;
        endforeach;
        if ( $options ): ?>
    <select id="ctc_all_styles_nav" name="ctc_all_styles_nav" style="margin-top:-2px">
      <option value="">
      <?php _e( 'Find Selectors by Nav Menu', 'child-theme-configurator' ); ?>
      </option>
      <?php echo implode( LF, $options ); ?>
    </select>
    <?php 
        endif; 
    endif; ?>
    <input name="ctc_all_styles_submit" id="ctc_all_styles_submit" value="<?php _e( 'Go', 'child-theme-configurator' ); ?>" type="submit" class="button-primary button"  />
    <?php echo $link; ?>
  </form>
  <div class="ctc-three-col">
    <?php $this->render_all_selectors(); ?>
  </div>
</div>
<?php if ( !is_multisite() || $allowed ):
  ?>
<div id="live_preview_panel" class="ctc-option-panel<?php echo 'live_preview' == $active_tab ? ' ctc-option-panel-active' : ''; ?>" <?php echo $hidechild; ?> >
  <iframe src=""></iframe>
</div>
<?php endif; 
endif; ?>
<div id="register_panel" class="ctc-option-panel<?php echo 'register' == $active_tab ? ' ctc-option-panel-active' : ''; ?>" >
  <div class="ctc-input-row clearfix" id="input_row_update_key">
    <form id="ctc_update_key_form" method="post" action="">
      <?php 
	    $this->fingerprint_field();
        wp_nonce_field( 'ctcp_update_key' ); ?>
      <div class="ctc-input-cell"> <strong>
        <?php _e( 'Register Update Key', 'child-theme-configurator' ); ?>
        </strong>
        <p class="howto">
          <?php _e( 'Enter the update key that was sent with your purchase to enable automatic notifications and downloads of future releases of CTC Pro.', 'child-theme-configurator' ); ?>
        </p>
      </div>
      <div class="ctc-input-cell">
        <input class="ctc_text" id="ctcp_update_key" name="ctcp_update_key"  type="text" 
            value="<?php echo esc_attr( isset( $this->options[ 'update_key' ] ) ? $this->options[ 'update_key' ] :'' ); ?>" 
                placeholder="<?php _e( 'Update Key', 'child-theme-configurator' ); ?>" autocomplete="off" />
      </div>
      <div class="ctc-input-cell">
        <input class="ctc_submit button button-primary" id="ctcp_save_update_key" name="ctcp_save_update_key"  type="submit" 
                value="<?php _e( 'Save', 'child-theme-configurator' ); ?>" disabled />
      </div>
    </form>
  </div>
</div>