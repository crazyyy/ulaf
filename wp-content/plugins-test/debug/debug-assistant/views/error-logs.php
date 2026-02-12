<?php
if ( !current_user_can('manage_options') || !defined( 'ABSPATH' ) ) {
    exit;
}

echo $data['header'];
?>

<div class="imlt-err-card card">
  <div class="card-body">
<div class="imlt_title"><h2>ERROR LOGS</h2></div>


<span class="imlt-btn btn btn-info active  imlt-error-file" style="z-index: 10;">Export</span>
<a class="btn btn-outline-success" href='' id='error_doc_file'  target='_self' download><i class="imlt-cui-cloud-download icons font-2xl cui-cloud-download"></i>Download</a>
<div class="imlt-error">
  <?php if(defined('WP_DEBUG_DISPLAY') && WP_DEBUG_DISPLAY == true ) :?>
  <span class="imlt-main-wrapper"><?php echo $data["error"]; ?></span>
<?php else : ?>
  <div class="alert alert-warning" role="alert">Enabled Debug Display from Debug page to see error list</div>
<?php endif; ?>

</div>
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
