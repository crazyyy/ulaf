<?php
// define( 'WP_DEBUG', false );
// ini_set( 'display_errors', false );
require_once dirname( __FILE__ ) . '/troubleshoot.php';
$app = pd_troubleshoot();

// check if we're attempting to restore the site
if (isset($_GET['restore'])) {
	if($app->delete_maintenance_file()){
		wp_redirect(home_url());
		exit;
	}
}


?>
<!DOCTYPE html>
<html>
  <head>
    <meta charset="utf-8">
    <title>Plugin Detective</title>
		<link rel="stylesheet" href="//fonts.googleapis.com/css?family=Roboto:300,400,500,700,400italic">
		<link rel="stylesheet" href="//fonts.googleapis.com/icon?family=Material+Icons">
		<link href="<?php echo $app->url('app/dist/static/css/app.css' ); ?>" rel="stylesheet">
  </head>
  <body>
    <div id="app"></div>

	<script type='text/javascript'>
    var app = <?php echo json_encode( $app->get_api_params() ); ?>;
    var pd_translations = <?php echo json_encode( $app->get_translations() ); ?>;
	</script>
    <script type=text/javascript src="<?php echo $app->url('app/dist/static/js/manifest.js' ).'?v='.PD_Troubleshoot::VERSION; ?>"></script>
    <script type=text/javascript src="<?php echo $app->url('app/dist/static/js/chunk-vendors.js' ).'?v='.PD_Troubleshoot::VERSION; ?>"></script>
    <script type=text/javascript src="<?php echo $app->url('app/dist/static/js/app.js' ).'?v='.PD_Troubleshoot::VERSION; ?>"></script>
  </body>
</html>
