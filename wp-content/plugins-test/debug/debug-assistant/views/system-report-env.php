<?php
if ( !current_user_can('manage_options') || !defined( 'ABSPATH' ) ) {
    exit;
}
echo $data['header'];?>
<div class=" imlt-err-card card">
<div class="card-body">
<div class="imlt_title"><h2>SYSTEM REPORT</h2></div>

<span class="imlt-system-report-file imlt-btn  btn btn-success">Export Report</span>

<a id="sys_doc_file" class="btn btn-outline-success" href=""   target="_self" download><i class="imlt-cui-cloud-download icons font-2xl cui-cloud-download"></i>Download</a>

<div class="imlt-alert-info alert alert-info" role="alert"><b>Wordpress data</b></div>
<?php echo $data['env-sis-report-details']; ?>
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
