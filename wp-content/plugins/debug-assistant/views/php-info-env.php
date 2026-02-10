<?php
if ( !current_user_can('manage_options') || !defined( 'ABSPATH' ) ) {
    exit;
}
echo $data['header'];?>
<div class=" imlt-err-card card">
<div class="card-body">
<div class="imlt_title"><h2>PHP Info</h2></div>

<div class="imlt-btn imlt-btn-restore btn btn-success imlt-php-info-file">Export PHP Info</div>
<a href="" id="php_info_file" class="btn btn-outline-success" target="_self" download><i class="imlt-cui-cloud-download icons font-2xl cui-cloud-download"></i>Download</a>
<?php echo $data['php-info']; ?>
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
  <div class="ml-auto imlt-footer">
    <span>Powered by</span>
    <a href="https://wpindeed.com/">WPIndeed</a>
  </div>
</footer>
