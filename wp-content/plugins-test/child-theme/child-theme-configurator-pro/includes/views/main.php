<?php
if ( !defined( 'ABSPATH' ) )exit;

// main CTC Page 
?>
<style type="text/css">
    .ctc-step-number {
        background-color: <?php echo $this->colors[ 3];
        ?>;
    }
    
    .ctc-step-letter {
        background-color: <?php echo $this->colors[ 2];
        ?>;
    }
    
    .ctc-step+ strong {
        color: <?php echo $this->colors[ 1];
        ?>;
    }
    
    .ctc-status-icon.success {
        background: url(<?php echo admin_url( 'images/yes.png' );
        ?>) no-repeat;
    }
    
    .ctc-status-icon.failure {
        background: url(<?php echo admin_url( 'images/no.png' );
        ?>) no-repeat;
    }
    
    .ctc-exit {
        background: #f9f9f9 url(<?php echo includes_url( 'images/xit-2x.gif' );
        ?>) left top no-repeat;
    }
    
    .ctc-data-mode-container {
        background-color: #ababab;
        color: #fff;
        margin-bottom: .5em;
        padding: .75em;
    }
    
    .ctc-data-mode-container label {
        margin: .5em 1em .5em 0;
        display: inline-block;
    }
    
    .ctc-data-mode-container h4 {
        margin: 0 0 .75em;
    }
    
    .wp-core-ui .ctc-data-mode-container .button-secondary {
        vertical-align: middle;
    }
</style>
<div class="wrap" id="ctc_main">
    <?php do_action( 'chld_thm_cfg_related_links' ); ?>
    <h2>
        <?php echo apply_filters( 'chld_thm_cfg_header', __( 'Child Theme Configurator', 'child-theme-configurator' ) . ' ' . __( 'version', 'child-theme-configurator' ) . ' ' . CHLD_THM_CFG_VERSION );  ?>
    </h2>
    <?php 
if ( $this->ctc()->is_post && !$this->ctc()->fs ):
    //die( 'in fs prompt' );
    echo $this->ctc()->fs_prompt;
else: 
    if ( $this->configured() ):
        include ( CHLD_THM_CFG_DIR . '/includes/views/data-mode.php' ); 
    endif; ?>
    <div id="ctc_error_notice">
        <?php $this->render_settings_errors(); ?>
    </div>
    <?php 
    // if flag has been set because an action is required, do not render interface
    if ( !$this->ctc()->skip_form ):
        include ( CHLD_THM_CFG_DIR . '/includes/views/tabs.php' ); 
?>
    <div id="ctc_option_panel_wrapper" style="position:relative">
        <div class="ctc-option-panel-container">
            <?php 
        include ( CHLD_THM_CFG_DIR . '/includes/views/parent-child.php' );
        if ( $this->enqueue_is_set() ):
            include ( CHLD_THM_CFG_DIR . '/includes/views/rule-value.php' ); 
            include ( CHLD_THM_CFG_DIR . '/includes/views/query-selector.php' );
            // if ( $this->ctc()->is_theme() ) // v.2.3.0 - no longer using pluginmode
            include ( CHLD_THM_CFG_DIR . '/includes/views/webfonts.php' ); ?>
            <div id="view_child_options_panel" class="ctc-option-panel <?php echo 'view_child_options' == $active_tab ? ' ctc-option-panel-active' : ''; ?>"> </div>
            <div id="view_parnt_options_panel" class="ctc-option-panel <?php echo 'view_parnt_options' == $active_tab ? ' ctc-option-panel-active' : ''; ?>"> </div>
            <?php 
            // if ( $this->ctc()->is_theme() )  // v.2.3.0 - no longer using pluginmode
            include ( CHLD_THM_CFG_DIR . '/includes/views/files.php' );
        endif;
        //if ( $this->enqueue_is_set() ): //|| $this->supports_disable() ):// v.2.3.0 - no longer using pluginmode
            do_action( 'chld_thm_cfg_panels', $active_tab );
        //endif;
    ?>
        </div>
<?php do_action( 'chld_thm_cfg_sidebar' ); ?>
    </div>
<?php
    endif;
endif;
?>
    <div id="ctc_debug_container">
        <?php if ( $this->ctc()->is_debug ): ?>
        <textarea id="ctc_debug_box"><?php echo $this->ctc()->get_debug(); ?></textarea>
        <?php endif; ?>
    </div>
</div>