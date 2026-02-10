<?php
if ( !current_user_can('manage_options') || !defined( 'ABSPATH' ) ) {
    exit;
}
echo $data["header"];

$checked_debug = '';
$checked_debug_log = '';
$checked_debug_display = '';
$checked_debug_script = '';
$checked_debug_queries = '';

// Debug

if(isset($_POST['imlt-debug'])) {

  if($_POST['imlt-debug'] == 1) {

    $checked_debug = 'checked';
  }

}else{
  $checked_debug = ( defined('WP_DEBUG') && WP_DEBUG == true) ? 'checked' : '';

}

// Debug Log

if(isset($_POST['imlt-debug-log'])) {

  if($_POST['imlt-debug-log'] == 1) {
    $checked_debug_log = 'checked';
  }

}else{
  $checked_debug_log = ( defined('WP_DEBUG_LOG') && WP_DEBUG_LOG == true) ? 'checked' : '';
}

// Debug Display
if(isset($_POST['imlt-debug-display'])) {

  if($_POST['imlt-debug-display'] == 1) {
    $checked_debug_display = 'checked';
  }

}else{
  $checked_debug_display = ( defined('WP_DEBUG_DISPLAY') && WP_DEBUG_DISPLAY == true) ? 'checked' : '';
}

// Script Debbuging
if(isset($_POST['imlt-script-debugging'])) {

  if($_POST['imlt-script-debugging'] == 1) {
    $checked_debug_script = 'checked';
  }

}else{
  $checked_debug_script = ( defined('SCRIPT_DEBUG') && SCRIPT_DEBUG == true) ? 'checked' : '';
}

// Save Queries
if(isset($_POST['imlt-save-queries'])) {

  if($_POST['imlt-save-queries'] == 1) {
    $checked_debug_queries = 'checked';
  }

}else{
  $checked_debug_queries = ( defined('SAVEQUERIES') && SAVEQUERIES == true) ? 'checked' : '';
}

$imlt_dis = "";
if(isset($_POST['save_const_db_changes']) || isset($_POST['saveNowChanges'])) {
  $imlt_dis = "disabled";

}

$imlt_hide_btn_cls = "";
if(isset($_POST['saveNowChanges'])) {
  $imlt_hide_btn_cls = "imlt-hide-prf-actions";
}
 ?>

<div class=" imlt-err-card card">
  <div class="card-body">
<div class="imlt_title"><h2>DEBUG</h2></div>

<div class="alert alert-info" role="alert">Backup your wp-config.php file first of all despite any action.</div>
<div class="alert alert-secondary" role="alert">In order to receive error list switch on.</div>
<form id="const-checkbox-form" method="post" action="">
        <table class='imlt-debug-const'>
            <tr>
              <td>Debug</td>
              <?php  $value_debug = ($checked_debug == 'checked') ? 1 : 0;  ?>
              <td><label class='switch switch-label switch-pill switch-primary'>
                <input class="switch-input" type="checkbox"  <?php  echo $checked_debug . " " . $imlt_dis;?> value="<?php echo $value_debug; ?>" onchange="imlt_checkbox_write_value( this, '[name=imlt-debug]', 1, 0 );" >
                <span class="switch-slider" data-checked="On" data-unchecked="Off"></span>
              </label></td>
              <input type="hidden" name='imlt-debug' value="<?php echo $value_debug; ?>" />
            </tr>

            <tr>
              <td>Debug Log</td>
              <?php $value_debug_log = ($checked_debug_log == 'checked') ? 1 : 0; ?>
              <td><label class='switch switch-label switch-pill switch-primary'>
                <input class="switch-input"  type="checkbox"  name='imlt-debug-log' value="<?php echo $value_debug_log; ?>" <?php echo $checked_debug_log . " " . $imlt_dis; ?> onchange="imlt_checkbox_write_value( this, '[name=imlt-debug-log]', 1, 0 );" >
                <span class="switch-slider" data-checked="On" data-unchecked="Off"></span>
              </label>
              </td>
              <input type="hidden" name='imlt-debug-log' value="<?php echo $value_debug_log;?>" />
            </tr>

            <tr>
              <td>Debug Display</td>
              <?php $value_debug_display = ($checked_debug_display == 'checked') ? 1 : 0; ?>
              <td><label class='switch switch-label switch-pill switch-primary'>
                <input class="switch-input"  type='checkbox' <?php echo $checked_debug_display . " " . $imlt_dis; ?> value="<?php echo $value_debug_display; ?>" onchange="imlt_checkbox_write_value( this, '[name=imlt-debug-display]', 1, 0 );" >
                <span class="switch-slider" data-checked="On" data-unchecked="Off"></span>
                </label>
              </td>
              <input type="hidden" name='imlt-debug-display' value="<?php echo $value_debug_display;?>" />
            </tr>

            <tr>
              <td>Script Debugging</td>
              <?php $value_debug_script = ($checked_debug_script == 'checked') ? 1 : 0; ?>
              <td><label class='switch switch-label switch-pill switch-primary'>
                <input class="switch-input"  type='checkbox' <?php echo $checked_debug_script . " " . $imlt_dis; ?> value="<?php echo $value_debug_script; ?>" onchange="imlt_checkbox_write_value( this, '[name=imlt-script-debugging]', 1, 0 );" >
                <span class="switch-slider" data-checked="On" data-unchecked="Off"></span>
                </label>
              </td>
              <input type="hidden" name='imlt-script-debugging' value="<?php echo $value_debug_script;?>" />
            </tr>

            <tr>
              <td>Save Queries</td>
              <?php $value_debug_queries = ($checked_debug_queries == 'checked') ? 1 : 0; ?>
              <td><label class='switch switch-label switch-pill switch-primary'>
                <input class="switch-input"  type='checkbox' <?php echo $checked_debug_queries . " " . $imlt_dis; ?> value="<?php echo $value_debug_queries; ?>" onchange="imlt_checkbox_write_value( this, '[name=imlt-save-queries]', 1, 0 );" >
                <span class="switch-slider" data-checked="On" data-unchecked="Off"></span>
                </label>
              </td>
              <input type="hidden" name='imlt-save-queries' value="<?php echo $value_debug_queries;?>" />
            </tr>

        </table>
        <div class="alert alert-warning" role="alert">Save Queries will have a performance impact on your site, so make sure to turn this off when you aren't debugging.</div>
     <input type='submit' value='Perform Actions' name='save_const_db_changes' class='<?php echo $imlt_hide_btn_cls; ?> imlt-btn btn btn-outline-primary '>
     <?php echo $data['debug-edit']; ?>
</form>
</div>
</div>
</div>
</main>
</div>
<div class="imlt-pos"></div>
<footer class="app-footer">
  <div class="imlt-footer">
    <a href="https://wpindeed.com/">WPIndeed</a>
    <span>© <?php echo date("Y");?>. WPIndeed Development</span>
  </div>
  <div class="ml-auto imlt-footer-right">
    <span>Powered by</span>
    <a href="https://wpindeed.com/">WPIndeed</a>
  </div>
</footer>
