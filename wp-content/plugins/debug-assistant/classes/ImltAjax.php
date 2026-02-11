<?php

class ImltAjax
{
  public function __construct()
  {
    /*** export button for error logs ***/
    add_action('wp_ajax_imlt_return_error_log_file', array($this, 'imlt_return_error_log_file'));
    add_action('wp_ajax_nopriv_imlt_return_error_log_file', array($this,'imlt_return_error_log_file'));

    /*** export button for system report ***/
    add_action('wp_ajax_imlt_export_system_report', array($this, 'imlt_export_system_report'));
    add_action('wp_ajax_nopriv_imlt_export_system_report', array($this,'imlt_export_system_report'));

    /*** export button for phpinfo report ***/
    add_action('wp_ajax_imlt_export_php_info', array($this, 'imlt_export_php_info'));
    add_action('wp_ajax_nopriv_imlt_export_php_info', array($this,'imlt_export_php_info'));

    /// delete cron job
    add_action( 'wp_ajax_imlt_delete_cron_job', array( $this, 'imlt_delete_cron_job') );

    /// fire cron job
    add_action( 'wp_ajax_imlt_fire_cron_job', array( $this, 'imlt_fire_cron_job') );

    //
    add_action( 'wp_ajax_imlt_speed_test_clear_history', array( $this, 'imlt_speed_test_clear_history' ) );
  }

  public function imlt_return_error_log_file()
  {


    $imlt_file_debug =  content_url('debug.log');

    if(!$imlt_file_debug) {
      echo 'No file here';
    }
    echo $imlt_file_debug . "\n";
    die();

  }

  public function imlt_export_system_report()
  {
    $imlt_sys_rep_file_path = IMLT_DIR_PATH . "public/imlt-system-report-file.txt";
    $imlt_sys_rep_file_url = IMLT_DIR_URL . "public/imlt-system-report-file.txt";

    $get_system_report = new ImltEnviroment();
    $imlt_system_clean_string = strip_tags($get_system_report->sistem_report_details());
    file_put_contents($imlt_sys_rep_file_path, $imlt_system_clean_string);
    echo $imlt_sys_rep_file_url;
    die();

  }

  public function imlt_export_php_info() {

    $imlt_php_file_path = IMLT_DIR_PATH . "public/imlt-php-info-file.txt";
    $imlt_php_file_url = IMLT_DIR_URL . "public/imlt-php-info-file.txt";

    $get_phpinfo_data = new ImltEnviroment();
    $imlt_php_clean_string = strip_tags($get_phpinfo_data->imlt_phpinfo_details());
    file_put_contents($imlt_php_file_path, $imlt_php_clean_string );
    echo $imlt_php_file_url;
    die();


  }

  public function imlt_delete_cron_job()
  {
      if ( empty($_POST['cronName']) ){
          echo 0;
          die;
      }
      wp_clear_scheduled_hook( esc_sql( $_POST['cronName'] ) );
      echo $_POST['cronName'];
      die;
  }

  public function imlt_fire_cron_job()
  {
      if ( empty($_POST['cronName']) ){
          echo 0;
          die;
      }
      require_once IMLT_DIR_PATH . 'classes/ImltDatabase.php';
      $db = new ImltDatabase();
      $cronData = $db->getCronData( esc_sql($_GET['cronName']) );
      if ( !$cronData ){
          return;
      }
      wp_schedule_single_event( time()-1, $cronData['name'], $cronData['args'] );
      echo $_POST['cronName'];
      die;
  }

  public function imlt_speed_test_clear_history()
  {
      require_once IMLT_DIR_PATH . 'classes/ImltDatabase.php';
      $db = new ImltDatabase();
      return $db->clearSpeedTestData();
      die;
  }

}
