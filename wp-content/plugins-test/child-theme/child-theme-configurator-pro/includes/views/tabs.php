<?php  
if ( !defined( 'ABSPATH' ) ) exit;
// Tabs Bar
$active_tab = isset( $_GET[ 'tab' ] ) ? $_GET[ 'tab' ] : ( $this->configured() && $this->enqueue_is_set() ? 'query_selector_options' : 'parent_child_options' ); 
?>

<h2 class="nav-tab-wrapper clearfix">
<a id="parent_child_options" href="" 
                    class="nav-tab<?php echo 'parent_child_options' == $active_tab ? ' nav-tab-active' : ''; ?>">
<?php _e( 'Theme Config', 'child-theme-configurator' ); ?>
</a><a id="query_selector_options" href="" 
                    class="nav-tab <?php echo $this->configured() ? '' : 'ctc-disabled'; echo 'query_selector_options' == $active_tab ? ' nav-tab-active' : ''; ?>" <?php echo $this->configured() ? '' : 'disabled'; ?> >
<?php _e( 'Query/ Selector', 'child-theme-configurator' ); ?>
</a><a id="rule_value_options" href="" 
                    class="nav-tab <?php echo $this->configured() ? '' : 'ctc-disabled'; echo 'rule_value_options' == $active_tab ? ' nav-tab-active' : ''; ?>" <?php echo $this->configured() ? '' : 'disabled'; ?> >
<?php _e( 'Property/ Value', 'child-theme-configurator' ); ?>
</a><?php
    // if ( $this->ctc()->is_theme() ):   // v.2.3.0 - no longer using pluginmode
    ?><a id="import_options" href="" 
                    class="nav-tab <?php echo $this->configured() ? '' : 'ctc-disabled'; echo 'import_options' == $active_tab ? ' nav-tab-active' : ''; ?>" <?php echo $this->configured() ? '' : 'disabled'; ?> >
<?php _e( 'Web Fonts/ Ext. CSS', 'child-theme-configurator' ); ?>
</a><?php 
    //endif;  // v.2.3.0 - no longer using pluginmode ?>
    <a id="view_parnt_options" href="" 
                    class="nav-tab <?php echo $this->configured() ? '' : 'ctc-disabled'; echo 'view_parnt_options' == $active_tab ? ' nav-tab-active' : ''; ?>" <?php echo $this->configured() ? '' : 'disabled'; ?> >
<?php _e( 'Baseline Styles', 'child-theme-configurator' ); ?>
</a><a id="view_child_options" href="" 
                    class="nav-tab <?php echo $this->configured() ? '' : 'ctc-disabled'; echo 'view_child_options' == $active_tab ? ' nav-tab-active' : ''; ?>" <?php echo $this->configured() ? '' : 'disabled'; ?> >
<?php _e( 'Child Styles', 'child-theme-configurator' ); ?>
</a><?php 
    // if ( $this->ctc()->is_theme() ):   // v.2.3.0 - no longer using pluginmode
    ?><a id="file_options" href="" class="nav-tab <?php echo $this->configured() ? '' : 'ctc-disabled'; echo 'file_options' == $active_tab ? ' nav-tab-active' : ''; ?>" <?php echo $this->configured() ? '' : 'disabled'; ?> >
<?php _e( 'Files', 'child-theme-configurator' ); ?>
</a><?php 
    // endif;  // v.2.3.0 - no longer using pluginmode
    //if ( $this->enqueue_is_set() ): // || $this->supports_disable() ):// v.2.3.0 - no longer using pluginmode
        do_action( 'chld_thm_cfg_tabs', $active_tab );
    //endif;
?>
  <i id="ctc_status_preview"></i>
</h2>