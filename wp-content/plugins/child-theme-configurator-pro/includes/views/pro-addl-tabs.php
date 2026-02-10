<?php
// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) exit;
if ( $this->ui()->enqueue_is_set() ):
?>

<a id="all_styles" href="" 
                    class="nav-tab <?php echo $this->ui()->configured() ? '' : 'ctc-disabled'; echo 'all_styles' == $active_tab ? ' nav-tab-active' : ''; ?>" <?php echo $this->ui()->configured() ? '' : 'disabled'; ?> >
<?php _e( 'All Styles', 'child-theme-configurator' ); ?>
</a>
<?php if ( current_user_can( 'switch_themes' ) && ( !is_multisite() || $allowed ) ): ?>
<a id="live_preview" href="" 
                    class="nav-tab  <?php echo $this->ui()->configured() ? '' : 'ctc-disabled'; echo 'live_preview' == $active_tab ? ' nav-tab-active' : ''; ?>" <?php echo $this->ui()->configured() ? '' : 'disabled'; ?> >
<?php _e( 'Preview', 'child-theme-configurator' ); ?>
</a>
<?php endif; 
endif; ?>
<a id="register" href="" 
                    class="nav-tab <?php echo 'register' == $active_tab ? ' nav-tab-active' : ''; ?>" >
<?php _e( 'Register', 'child-theme-configurator' ); ?>
</a><?php if ( $this->ui()->enqueue_is_set() ): ?><a id="recent_edits" class="ctc-recent-tab" href="#">
<?php _e('Recent Edits', 'child-theme-configurator'); ?>
</a> <?php endif;