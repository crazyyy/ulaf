<?php
if ( !current_user_can('manage_options') || !defined( 'ABSPATH' ) ) {
    exit;
}
echo $data['header'];?>
  <style>
  .CodeMirror  {
    height: auto;
    font-size: 15px;
    width: 95%;
  }
  .imlt-blur {
  opacity: 0.8;
  pointer-events: none;
  }
  </style>


<div class=" imlt-err-card card">
<div class="card-body">
<div class="imlt_title"><h2>TAKE A LOOK TO WP-CONFIG FILE DIRECT FROM DASHBOARD</h2></div>
<div class="imlt_separator"></div>
<!-- Code mirror work -->
<?php
  $wp_config_file =  ABSPATH . 'wp-config.php';
  $config_file_string = file_get_contents($wp_config_file);

  $blur_class = "";
/*if(isset($_POST['editor_actions']) || isset($_POST['saveNowEditor'])) {

  $blur_class = "imlt-blur";
}*/

$imlt_hide_btn_cls = "";
if(isset($_POST['saveNowEditor'])) {
  $imlt_hide_btn_cls = "imlt-hide-prf-actions";
}
 ?>
<!--<div class="alert alert-info" role="alert">Before you editing wp-config file you must press <b>Perform Actions</b> button
wich will make a backup to the existing wp-config file.<br> Is important to have restore page open in a new tab for the cases where
something wrong is happening.<br> A backup of <b>wp-config</b> file is sent already on your admin email adress.</div>-->
<form id="imlt-editor-form" class="<?php echo $blur_class; ?>" method="post" action="" enctype="application/x-www-form-urlencoded">
  <textarea id="editor-container" name="editor-container"></textarea>
  <textarea  name='input-stored-textarea' id="input-stored-textarea" value="<?php if ( isset( $_POST['input-stored-textarea'] ) ) echo $_POST['input-stored-textarea'];?>" style="display:none;"></textarea>
  <!--<input id="editor_actions" class ="<?php //echo $imlt_hide_btn_cls; ?> imlt-btn btn btn-outline-primary" type="submit" name="editor_actions" value="Perform Actions">-->
</form>
<?php
      $wpConfigData = htmlspecialchars(stripslashes($config_file_string));

      if ( !empty( $_POST['input-stored-textarea'] ) ){
          $wpConfigData = htmlentities($_POST['input-stored-textarea']);

      }
?>
<div id="indeed_hidden" data-secret-value="<?php echo $wpConfigData; ?>"></div>
<?php //echo $data['edit-str-code-editor']; ?>

<script>

var editor_code = CodeMirror.fromTextArea(document.getElementById("editor-container"), {
    lineNumbers: true,
    mode: "javascript",
    matchBrackets: true,
    theme: "midnight",
    readOnly: true
  });

  // content from wp-config.php file
var editor_code_data = jQuery('#indeed_hidden');

  // set the value in editor content

  editor_code.setValue(editor_code_data.attr('data-secret-value'));

  // set the hidden textarea value with editor value

  var strr = jQuery('#input-stored-textarea').val(editor_code.getValue());

  editor_code.on("change", function() {

    var get_value = editor_code.getValue();
      jQuery('#input-stored-textarea').html( btoa(get_value) );

  });

  // content from wp-config.php file
/*  var editor_code_data = jQuery('#indeed_hidden');
  var editor_code_data_encode = window.btoa(editor_code_data);


  // set the value in editor content

  editor_code.setValue(editor_code_data.attr('data-secret-value'));

  // set the hidden textarea value with editor value
  var strr = jQuery('#input-stored-textarea').val(editor_code.getValue());

  editor_code.on("change", function() {

    var get_value = editor_code.getValue();

      jQuery('#input-stored-textarea').html( btoa(get_value) );

  });
*/



</script>

<!-- End of Code mirror -->
</div>
</div>
</div>
</main>
</div>
</div>
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
