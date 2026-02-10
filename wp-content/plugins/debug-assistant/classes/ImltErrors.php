<?php
class ImltErrors
{
  protected $error_message = '';
  // Modify constants values based on on/off button checkbox or values inserted in fields
  public function modify_debug_constants_values()
  {

      $wp_direct_config_file =  ABSPATH . 'wp-config.php';

      $backup_msg = '';
      $imlt_time = time();

          if( isset($_POST['save_const_db_changes']) )
          {
              $backup_msg .=  $this->imlt_backup_wp_config( $imlt_time );
              $backup_msg .= $this->imlt_open_new_tab($imlt_time);
              $backup_msg .= "<div class='imlt-restore-msg alert alert-secondary' role='alert'>  <h4 class='ntc-hd'>By opening <a class=''  href='" . IMLT_DIR_URL . "public/wp-config-restore.php?imltKey=".$imlt_time."' target='_blank'><b>Restore Page</b></a> url  in a new tab any harmful action can be restored. Before any changes a copy of wp-config file was send to admin email adress.</h4>";
              $backup_msg .= "<h4 class='ntc-hd'>If something goes wrong the filesystem path of the directory is <span class='badge-md badge-info'>" .IMLT_DIR_PATH . "backup-config</span></h6>";
              $backup_msg .= "<input type='submit' value='Save Changes' name='saveNowChanges' form='const-checkbox-form' class='imlt-btn  btn  btn-info active'></div>";

          } elseif( isset($_POST['saveNowChanges'] )) {
              $backup_msg .= $this->imlt_tmp_wp_config();
              $backup_msg .= $this->imlt_put_str_tmp();
              $backup_msg .= $this->imlt_save_now_db_changes($imlt_time);
          }

      return $backup_msg;
    }

    public function modify_advances_constants_values()
    {

      $wp_direct_config_file =  ABSPATH . 'wp-config.php';
      $backup_msg = '';
      $imlt_time = time();

          if( isset( $_POST['save_const_adv_changes'] ))
          {
            $backup_msg .=  $this->imlt_backup_wp_config( $imlt_time );
            $backup_msg .= $this->imlt_open_new_tab($imlt_time);
            $backup_msg .= "<div class='imlt-restore-msg alert alert-secondary' role='alert'>  <h4 class='ntc-hd'>By opening <a class=''  href='" . IMLT_DIR_URL . "public/wp-config-restore.php?imltKey=".$imlt_time."' target='_blank'><b>Restore Page</b></a> url  in a new tab any harmful action can be restored. Before any changes a copy of wp-config file was send to admin email adress.</h4>";
            $backup_msg .= "<h4 class='ntc-hd'>If something goes wrong the filesystem path of the directory is <span class='badge-md badge-info'>" .IMLT_DIR_PATH . "backup-config</span></h6>";
            $backup_msg .= "<input type='submit' value='Save Changes' name='saveNowChanges' form='adv-settings-checkbox-form' class='imlt-btn  btn  btn-info active'></div>";

          } elseif( isset($_POST['saveNowChanges']) ) {
            $backup_msg .= $this->imlt_tmp_wp_config();
            $backup_msg .= $this->imlt_put_str_tmp();
            $backup_msg .= $this->imlt_save_now_adv_changes($imlt_time);
          }
      return $backup_msg;
    }

  // Display errors from debug.log file in errors logs tab

  public function imlt_errors() {
    $str = "";

    require_once IMLT_DIR_PATH . 'classes/ImltConfigFileActions.php';
    $const_edit = new ImltConfigFileActions();
    $configFileData = $const_edit->imlt_getContent();

    // show errors lines from debug.log file
    $imlt_current_page = (empty($_GET['imlt_page'])) ? 1 : (int)$_GET['imlt_page'];
    $itemsPerPage = 40;

    $imlt_error_file = WP_CONTENT_DIR . '/debug.log';


    if ( !file_exists( $imlt_error_file ) ){
        return;
    }

    $error_file_handle = fopen($imlt_error_file, 'r');
    $imlt_error_all_items = explode("\n", file_get_contents($imlt_error_file, true));

    $countAllItems = count($imlt_error_all_items);
    $offset =  $countAllItems - ($imlt_current_page * $itemsPerPage) - 1;
    // for the last page to show correct lines

  if ( $offset<0 ) {
        $offset = 0;
    }

  if ( $itemsPerPage * $imlt_current_page > $countAllItems ) {
        $itemsPerPage = ($countAllItems - ceil($itemsPerPage * ($imlt_current_page - 1))) - 1;
    }
    $imlt_error_items_per_page = array_slice($imlt_error_all_items, $offset, $itemsPerPage, true);
    $itemsPerPage = 40;
    $str .= '<h3>Errors details</h3>';

  foreach ($imlt_error_items_per_page as $id => $imlt_error_string) {
        $error_style = str_replace(array('Notice', 'Warning','Parse error', 'Fatal error', 'line' ),
                                   array(
                                     '<strong style="color:#5cb85c;">Notice</strong>',
                                     '<strong style="color:#ffbf00;">Warning</strong>',
                                     '<strong style="color:#f37735;">Parse error</strong>',
                                     '<strong style="color:#ae0001;">Fatal error</strong>',
                                     '<strong style="color:#005b96;">line</strong>'
                                   ), $imlt_error_string );

        $str .= '<span class="err-msg-line">' . substr($error_style, 31) . '<span><br>';


    }
    fclose($error_file_handle);

    require_once IMLT_DIR_PATH . 'classes/ImltPagination.php';
    $base_url = admin_url('admin.php?page=imlt_manage&tab=error_logs');

    $pag_object = new ImltPagination(array(
                            'base_url'       => $base_url,
                            'param_name'     =>'imlt_page',
                            'total_items'    => $countAllItems,
                            'items_per_page' => $itemsPerPage,
                            'current_page'   => $imlt_current_page
    ));
     $str .= $pag_object->output();
    return $str;
  }

// function for editing wp-config directly from code editor
  public function imlt_direct_edit_wp_conf()
  {

    require_once IMLT_DIR_PATH . 'classes/ImltConfigFileActions.php';

      $direct_edit = '';
      $wp_direct_config_file =  ABSPATH . 'wp-config.php';

      $constants_file = new ImltConfigFileActions();
      $config_constants_file = $constants_file->imlt_getContent();

      $config = htmlspecialchars($config_constants_file);

      $fileDate = time();


    if(isset($_POST['editor_actions'])) {

          // step 1
          $direct_edit .= $this->imlt_backup_wp_config($fileDate);

          $direct_edit .= "<div class='imlt-restore-msg alert alert-secondary' role='alert'>  By opening <a class=''  href='" . IMLT_DIR_URL . "public/wp-config-restore.php?imltKey=".$fileDate."' target='_blank'>Restore Page</a> url  in a new tab any harmful action can be restored.
                            Before any changes a copy of wp-config file was send to admin email adress.";
          $direct_edit .=  "<input type='submit' value='Save Changes' name='saveNowEditor' form='imlt-editor-form' class='imlt-btn imlt-btn-restore btn  btn-info active'></div>";

      } elseif(isset($_POST['saveNowEditor'])) {
          /// step 2
          $input_store_texarea = htmlspecialchars(stripslashes( $_POST['input-stored-textarea']  ) );
          file_put_contents($wp_direct_config_file, $input_store_texarea);// stripslashes($_POST['input-stored-textarea']) );
          $direct_edit .= '<div class="imlt-ssc-msg alert alert-success" role="alert"><i id="imlt-icn" class=" icons font-2xl d-block mt-5 cui-check"></i> <span>Changes was saved</span></div>';
      }

    return $direct_edit;
 }


 public function imlt_backup_wp_config( $timeString='' )
    {
        $backup_msg = "";
        require_once IMLT_DIR_PATH . 'classes/ImltConfigFileActions.php';
        $const_edit = new ImltConfigFileActions();

        // Save previous wp-config version

        $imlt_wp_config_str = $const_edit->imlt_getContent();

        //var_dump($imlt_wp_config_str);
        //file_put_contents(IMLT_DIR_PATH . 'debugg.log' , $imlt_wp_config_str . "\n", FILE_APPEND);
        $wp_config_file_saved = "imlt-wp-config-". $timeString .".php";
        $backupWpDir = IMLT_DIR_PATH . "/backup-config";
        $index_file =  $backupWpDir ."/index.php";
        $indx = "<?php //indeed ?>";

        //create backup-config dir
          if(!file_exists($backupWpDir) ) {
            mkdir(IMLT_DIR_PATH . "/backup-config");
          }
          $backupWpConfig = fopen($backupWpDir ."/". $wp_config_file_saved, "w");
          fwrite($backupWpConfig, $imlt_wp_config_str );
          fclose($backupWpConfig);

        //create index file
          if( !file_exists($index_file) ) {
            $indx_file = fopen($index_file, "w");
            fwrite($indx_file, $indx );
            fclose($indx_file);
          }

      $fileDate_time = time();
      $from = "Debug Assistant Plugin";
      $to = get_option('admin_email');
      $subject = "Backup wp-config file url";
      $message = "You can restore  wp-config.php file here " . site_url() . "/wp-content/plugins/debug-assistant/public/wp-config-restore.php?imltKey=".$fileDate_time."\n";
      $message .= "If something goes wrong the filesystem path of the directory is " .IMLT_DIR_PATH . "temp";
      $headers = "From: Debug Assistant <Plugin@email.com>";
      wp_mail($to, $subject, $message, $headers);

      // end saved prev version

      return $backup_msg;

    }
    public function imlt_tmp_wp_config()
    {
      $imlt_tmp_dir = IMLT_DIR_PATH . "/tmp";

       if(!file_exists($imlt_tmp_dir)) {
           mkdir(IMLT_DIR_PATH . "/tmp");
       }

      $temp_config_file = fopen($imlt_tmp_dir . "/temp-wp-config.php", "w") or die("temp-wp-config can't be created!");

      $index_file =  $imlt_tmp_dir ."/index.php";
      $indx = "<?php //indeed ?>";

      //create index file
        if( !file_exists($index_file) ) {
          $indx_file = fopen($index_file, "w");
          fwrite($indx_file, $indx );
          fclose($indx_file);
        }
    }
    public function imlt_put_str_tmp()
      {
        $wp_direct_config_file =  ABSPATH . 'wp-config.php';
        $get_debug_config_str = file_get_contents($wp_direct_config_file);
        $this->tmp_file = IMLT_DIR_PATH . "/tmp/temp-wp-config.php";
        file_put_contents( $this->tmp_file, $get_debug_config_str );
      }

  //Open wp-config backup in a new broswer window
  public function imlt_open_new_tab($imlt_tab) {

    $imlt_adress = IMLT_DIR_URL . "public/wp-config-restore.php?imltKey=".$imlt_tab;
    $imlt_conf_tab = "<script>window.open('".$imlt_adress."');</script>";
    return $imlt_conf_tab;
  }



  public function imlt_save_now_db_changes( $fileDate = '' )
  {
    require_once IMLT_DIR_PATH . 'classes/ImltConfigFileActions.php';
     $const_edit = new ImltConfigFileActions();

    //WP_DEBUG
    if ( isset($_POST['imlt-debug']) ){

        $desiredValue = $_POST['imlt-debug'];
        $const_edit->setConstantType( 'WP_DEBUG' );
        $const_edit->setConstantInFile( $desiredValue, 'bool' );
        $const_edit->wp_config_final_str();

    }

    //WP_DEBUG_LOG
    if ( isset($_POST['imlt-debug-log']) ){

        $desiredValue = $_POST['imlt-debug-log'];
        $const_edit->setConstantType( 'WP_DEBUG_LOG' );
        $const_edit->setConstantInFile( $desiredValue, 'bool' );
        $const_edit->wp_config_final_str();

    }

    //WP_DEBUG_DISPLAY
    if ( isset($_POST['imlt-debug-display']) ){

        $desiredValue = $_POST['imlt-debug-display'];
        $const_edit->setConstantType( 'WP_DEBUG_DISPLAY' );
        $const_edit->setConstantInFile( $desiredValue, 'bool' );
        $const_edit->wp_config_final_str();
    }

    //SCRIPT_DEBUG
    if ( isset($_POST['imlt-script-debugging']) ){

        $desiredValue = $_POST['imlt-script-debugging'];
        $const_edit->setConstantType( 'SCRIPT_DEBUG' );
        $const_edit->setConstantInFile( $desiredValue, 'bool' );
        $const_edit->wp_config_final_str();
    }

    // SAVEQUERIES
    if ( isset($_POST['imlt-save-queries']) ){

        $desiredValue = $_POST['imlt-save-queries'];
        $const_edit->setConstantType( 'SAVEQUERIES' );
        $const_edit->setConstantInFile( $desiredValue, 'bool' );
        $const_edit->wp_config_final_str();
    }
    $this->error_message = '<div class="imlt-ssc-msg alert alert-success" role="alert"><i id="imlt-icn" class=" icons font-2xl d-block mt-5 cui-check"></i> <span>Changes was saved</span></div>';
    return $this->error_message;

  }

  public function imlt_save_now_adv_changes( $fileDate = '' )
  {
    require_once IMLT_DIR_PATH . 'classes/ImltConfigFileActions.php';

     $const_edit = new ImltConfigFileActions();

    if (empty($_POST['imlt-mail-interval']) || empty($_POST['imlt-memoryLimit']) || empty($_POST['imlt-max-memoryLimit']) || empty($_POST['imlt_cron_lock_timeout']))
      {
        $this->error_message = "<div class='imlt-ssc-msg alert alert-danger' role='alert'>One or more fields are empty!</div>";
        return $this->error_message;
      }
      // DISALLOW FILE EDIT
   if ( isset($_POST['imlt-disallow-file-edit']) ){

          $desiredValue = $_POST['imlt-disallow-file-edit'];
          $const_edit->setConstantType( 'DISALLOW_FILE_EDIT' );
          $const_edit->setConstantInFile( $desiredValue, 'bool' );
          $const_edit->wp_config_final_str();
      }

      // CONCATENATE SCRIPTS
      if ( isset($_POST['imlt-concatenate-scripts']) ){

          $desiredValue = $_POST['imlt-concatenate-scripts'];
          $const_edit->setConstantType( 'CONCATENATE_SCRIPTS' );
          $const_edit->setConstantInFile( $desiredValue, 'bool' );
          $const_edit->wp_config_final_str();
      }

      // COMPRESS SCRIPTS
      if ( isset($_POST['imlt-compress-scripts']) ){

          $desiredValue = $_POST['imlt-compress-scripts'];
          $const_edit->setConstantType( 'COMPRESS_SCRIPTS' );
          $const_edit->setConstantInFile( $desiredValue, 'bool' );
          $const_edit->wp_config_final_str();
      }

      /// ENFORCE_GZIP
      if ( isset($_POST['imlt-enforce-gzip']) ){

          $desiredValue = $_POST['imlt-enforce-gzip'];
          $const_edit->setConstantType( 'ENFORCE_GZIP' );
          $const_edit->setConstantInFile( $desiredValue, 'bool' );
          $const_edit->wp_config_final_str();
      }

      /// WP_DEFAULT_THEME
      /*if ( isset($_POST['imlt-default-theme']) && $_POST['imlt-default-theme']!='' ) {

              $desiredValue = esc_sql($_POST['imlt-default-theme']);
              $const_edit->setConstantType( 'WP_DEFAULT_THEME' );
              $const_edit->setConstantInFile( $desiredValue, 'string' );
              $const_edit->wp_config_final_str();
      } */

      /// WP_MAIL_INTERVAL
      if (isset($_POST['imlt-mail-interval']) ){

              $desiredValue = "'" .$_POST['imlt-mail-interval']. "M'";
              $const_edit->setConstantType( 'WP_MAIL_INTERVAL' );
              $const_edit->setConstantInFile( $desiredValue, 'number');
              $const_edit->wp_config_final_str();
          }

          // WP MEMORY LIMIT
      if (isset($_POST['imlt-memoryLimit'])) {
              $desiredValue = "'" .$_POST['imlt-memoryLimit']. "M'";
              $const_edit->setConstantType( 'WP_MEMORY_LIMIT' );
              $const_edit->setConstantInFile( $desiredValue, 'number' );
              $const_edit->wp_config_final_str();
        }
        // Wordpress MAX MEMORY LIMIT
      if ( isset($_POST['imlt-max-memoryLimit']) ){
              $desiredValue = "'" .$_POST['imlt-max-memoryLimit']. "M'";
              $const_edit->setConstantType( 'WP_MAX_MEMORY_LIMIT' );
              $const_edit->setConstantInFile( $desiredValue, 'number' );
              $const_edit->wp_config_final_str();
        }
        /// DISABLE_WP_CRON
      if ( isset($_POST['imlt-disable-cron']) ){

          $desiredValue = $_POST['imlt-disable-cron'];
          $const_edit->setConstantType( 'DISABLE_WP_CRON' );
          $const_edit->setConstantInFile( $desiredValue, 'bool' );
          $const_edit->wp_config_final_str();
        }
      /// ALTERNATE_WP_CRON
      if ( isset($_POST['imlt-alternate-cron']) ){

          $desiredValue = $_POST['imlt-alternate-cron'];
          $const_edit->setConstantType( 'ALTERNATE_WP_CRON' );
          $const_edit->setConstantInFile( $desiredValue, 'bool' );
          $const_edit->wp_config_final_str();
      }



      /// WP_CRON_LOCK_TIMEOUT
      if ( isset($_POST['imlt_cron_lock_timeout']) ){
          $desiredValue = "'" .$_POST['imlt_cron_lock_timeout']. "M'";
          $const_edit->setConstantType( 'WP_CRON_LOCK_TIMEOUT' );
          $const_edit->setConstantInFile( $desiredValue, 'number' );
          $const_edit->wp_config_final_str();
      }
      $this->error_message = '<div class="imlt-ssc-msg alert alert-success" role="alert"><i id="imlt-icn" class=" icons font-2xl d-block mt-5 cui-check"></i> <span>Changes was saved</span></div>';
      return $this->error_message;

  }

}
