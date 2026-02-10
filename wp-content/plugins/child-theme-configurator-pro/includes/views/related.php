<span style="float:right;margin-left:2em;margin-top:-6px;text-shadow:1px 2px 0 #fff"><strong><?php _e( 'New user?', 'child-theme-configurator' ); ?> <span style="color:#F1823B"><?php _e( 'Click help', 'child-theme-configurator' ); ?></span></strong> <i class="dashicons dashicons-arrow-right-alt" style="color:#F1823B"></i></span><a href="<?php echo CHLD_THM_CFG_DOCS_URL; ?>/child-theme-configurator-pro/" target="_blank" title="<?php _e( 'Get CTC Pro and other tools', 'child-theme-configurator' ); ?>" style="float:right"><img src="<?php echo CHLD_THM_CFG_URL; ?>css/lilaea-logo.png" height="36" width="145" alt="<?php _e( 'Lilaea Media - Responsive Tools for a Mobile World', 'child-theme-configurator' ); ?>" /></a>

  <label for="ctc_is_debug" style="font-size:9px;float:right;clear:both;margin-top:-1em">
    <input class="ctc_checkbox" id="ctc_is_debug" name="ctc_is_debug"  type="checkbox" 
            value="1" <?php echo checked( $this->ctc()->is_debug, 1 ); ?> autocomplete="off" />
    <?php _e( 'Debug', 'child-theme-configurator' ); ?>
  </label>