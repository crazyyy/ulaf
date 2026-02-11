<?php
if ( !current_user_can('manage_options') || !defined( 'ABSPATH' ) ) {
    exit;
}
echo $data['header'];?>
<div class="imlt-admin-content">

<h1>System Report</h1>
<div class="imlt-system-report-file">
<div class="exp-sys-doc">Export Report</div>
</div>
<a href='' id='sys_doc_file' target='_self' download>Download</a>

<h3>Wordpress data</h3>
<?php echo $data['env-sis-report-details']; ?>

<h1>PHP Info</h1>
<div class="imlt-php-info-file">
<div class="php-info-button">Export PHP Info</div>
</div>
<a href='' id='php_info_file' target='_self' download>Download</a>
<?php echo $data['php-info']; ?>
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
