<?php
class ImltMain
{
    public function __construct()
    {
        add_action( 'admin_menu', array( $this, 'imlt_the_menu'), 81 );
        add_action( 'admin_enqueue_scripts', array($this, 'imlt_styles'));
        add_action( 'admin_init', array($this, 'imlt_styles'));
    }

    public function imlt_the_menu()
    {
        add_menu_page ( 'Debug Assistant', 'Debug Assistant', 'manage_options',	'imlt_manage', array( $this, 'imlt_print_the_page'), 'dashicons-admin-tools' );
    }

    public function imlt_styles()
    {

     $imlt_plugin_scripts_load = false;
     if ( isset($_GET['page']) && $_GET['page']=='imlt_manage'){
          $imlt_plugin_scripts_load = true;
     }
     if ( !$imlt_plugin_scripts_load ){

        return;
     }

     wp_enqueue_style('imlt_style', IMLT_DIR_URL . 'assets/css/imlt-style.css');
     wp_enqueue_style('imlt_code-mirror', IMLT_DIR_URL . 'assets/js/CodeMirror/codemirror.css');
     wp_enqueue_style('imlt_code-mirror-midnight', IMLT_DIR_URL . 'assets/js/CodeMirror/midnight.css');
     wp_enqueue_script('jquery');
     wp_enqueue_script('imlt_script', IMLT_DIR_URL . 'assets/js/functions.js');
     wp_enqueue_script('imlt_code-mirror-js', IMLT_DIR_URL . 'assets/js/CodeMirror/codemirror.js');
     wp_enqueue_script('imlt_code-mirror-javascript-js', IMLT_DIR_URL . 'assets/js/CodeMirror/javascript.js');
     wp_enqueue_script('imlt_code-mirror-php-js', IMLT_DIR_URL . 'assets/js/CodeMirror/php.js');

     // Design for the plugin js files
    //wp_enqueue_script('imlt_dsh-jquery-min', IMLT_DIR_URL . 'assets/css/dashboard-template/vendors/jquery/js/jquery.min.js');

     // Design for the plugin css files

     wp_enqueue_style('imlt_dsh-style', IMLT_DIR_URL . 'assets/css/dashboard-template/css/style.css');
     wp_enqueue_style('imlt_dsh-coreui', IMLT_DIR_URL .'assets/css/dashboard-template/vendors/@coreui/icons/css/coreui-icons.min.css');
     wp_enqueue_style('imlt_dsh-flag-icon-css', IMLT_DIR_URL .'assets/css/dashboard-template/vendors/flag-icon-css/css/flag-icon.min.css');
     wp_enqueue_style('imlt_dsh-font-awesome', IMLT_DIR_URL .'assets/css/dashboard-template/vendors/font-awesome/css/font-awesome.min.css');
     wp_enqueue_style('imlt_dsh-simple-line-icons', IMLT_DIR_URL .'assets/css/dashboard-template/vendors/simple-line-icons/css/simple-line-icons.css');
     wp_enqueue_style('imlt_dsh-pace-progress', IMLT_DIR_URL . 'assets/css/dashboard-template/vendors/pace-progress/css/pace.min.css' );
     wp_enqueue_style('imlt_dsh-lada-themeless-css', IMLT_DIR_URL . 'assets/css/dashboard-template/vendors/ladda/css/ladda-themeless.min.css' );
     wp_enqueue_style('imlt-dsh-data-table-css', IMLT_DIR_URL . 'assets/css/dashboard-template/vendors/datatables.net-bs4/css/dataTables.bootstrap4.css' );
     wp_enqueue_style('imlt_dsh-perfect-scrollbar', IMLT_DIR_URL . 'assets/css/dashboard-template/vendors/perfect-scrollbar/css/perfect-scrollbar.css');


     wp_enqueue_script('imlt_dsh-popper-min', IMLT_DIR_URL . 'assets/css/dashboard-template/vendors/popper.js/js/popper.min.js');
     wp_enqueue_script('imlt-dsh-bootstrap-min', IMLT_DIR_URL . 'assets/css/dashboard-template/vendors/bootstrap/js/bootstrap.min.js');
     wp_enqueue_script('imlt_dsh-coreui', IMLT_DIR_URL . 'assets/css/dashboard-template/vendors/@coreui/coreui-pro/js/coreui.min.js');
     wp_enqueue_script('imlt_dsh-pace-progress', IMLT_DIR_URL . 'assets/css/dashboard-template/vendors/pace-progress/js/pace.min.js');
     wp_enqueue_script('imlt_dsh-perfect-scrollbar', IMLT_DIR_URL . 'assets/css/dashboard-template/vendors/perfect-scrollbar/js/perfect-scrollbar.min.js');
     wp_enqueue_script('imlt_dsh-perfect-scrollbar', IMLT_DIR_URL . 'assets/css/dashboard-template/vendors/perfect-scrollbar/js/perfect-scrollbar.js');
     wp_enqueue_script('imlt_dsh-spin-js', IMLT_DIR_URL . 'assets/css/dashboard-template/vendors/ladda/js/spin.min.js');
     wp_enqueue_script('imlt_dsh-ladda-js', IMLT_DIR_URL . 'assets/css/dashboard-template/vendors/ladda/js/ladda.min.js');
     wp_enqueue_script('imlt_dsh-loading-button-js', IMLT_DIR_URL . 'assets/css/dashboard-template/js/loading-buttons.js');


     wp_enqueue_script('imlt_dsh-data-Table-js', IMLT_DIR_URL . 'assets/css/dashboard-template/vendors/datatables.net/js/jquery.dataTables.js');
     wp_enqueue_script('imlt_dsh-data-table-bootstrap4-js', IMLT_DIR_URL . 'assets/css/dashboard-template/vendors/datatables.net-bs4/js/dataTables.bootstrap4.js');
     //wp_enqueue_script('imlt_dsh-data-tablejs', IMLT_DIR_URL . 'assets/css/dashboard-template/js/src/datatables.js');

    }



    public function imlt_print_the_page()
    {

      $tab = isset($_GET['tab']) ? $_GET['tab'] : '';

      $data = array(
                      'pluginName'              => 'WordPress Assistant',
                      'header'                  => $this->header_menu()

      );

      switch($tab) {
        case 'dashboard':
            $data['dsh-plg-act'] = $this->imlt_get_plugins('activate');
            $data['dsh-plg-inact'] = $this->imlt_get_plugins('inactivate');
            $pathToTemplate = IMLT_DIR_PATH . "views/dashboard.php";
            break;
        case 'debug':
            $data['debug-edit'] = $this->imlt_wp_config_edit_constants();
            $pathToTemplate = IMLT_DIR_PATH . "views/debug.php";
            break;
        case 'database':
            if ( !empty($_GET['query']) ){
                return $this->databaseCustomQuery();
            } else {
                if ( empty($_GET['table']) ){
                    return $this->database();
                } else {
                    return $this->databaseShowTable();
                }
            }
            break;
        case 'environment':
            $pathToTemplate = IMLT_DIR_PATH . "views/enviroment.php";
            break;
        case 'system_report_env':
            $data['env-sis-report-details']  = $this->env_sis_report();
            $pathToTemplate = IMLT_DIR_PATH . "views/system-report-env.php";
            break;
        case 'php_info_env':
            $data['php-info'] = $this->get_phpdetails_info();
            $pathToTemplate = IMLT_DIR_PATH . "views/php-info-env.php";
            break;
        case 'raw_editor':
            $data['edit-str-code-editor'] =  $this->edit_wp_config_from_codEditor();
            $pathToTemplate = IMLT_DIR_PATH . "views/raw-editor.php";
            break;
        case 'crons':
            return $this->crons();
            break;

        /*case 'queries':
            $pathToTemplate = IMLT_DIR_PATH . "views/queries.php";
            break;
        */
        case 'hooks_and_actions':
              return $this->hooksAndActions();
            break;
        case 'temporary_admin':
              $pathToTemplate = IMLT_DIR_PATH . "views/temporary-admin.php";
            break;
        case 'logged_users':
            return $this->loggedUsers();
            break;
        case 'speed_test':
              return $this->speedTest();
            break;
        case 'advanced_settings':
              $data['advanced-settings-edit'] = $this->advancedSettings();
              $pathToTemplate = IMLT_DIR_PATH . "views/advanced-settings.php";
            break;
        case 'error_logs':
              $data['error'] = $this->imlt_display_my_errors();
              $pathToTemplate = IMLT_DIR_PATH . "views/error-logs.php";
            break;
        default:
            case 'dashboard';
            $data['dsh-plg-act'] = $this->imlt_get_plugins('activate');
            $data['dsh-plg-inact'] = $this->imlt_get_plugins('inactivate');
            $pathToTemplate = IMLT_DIR_PATH . "views/dashboard.php";

      }


      $viewObject = new IndeedView();
      $viewObject->setTemplate($pathToTemplate);
      $viewObject->setContentData($data);
      echo $viewObject->getOutput();


   }

   private function imlt_get_plugins( $imlt_act ) {

     $viewObject = new ImltEnviroment();

     if($imlt_act == 'activate')
     {

     return $viewObject->imlt_display_plugins('active');

     }

     if($imlt_act == 'inactivate') {

        return $viewObject->imlt_display_plugins('inactive');
    }


    $pathToTemplate = IMLT_DIR_PATH . "views/dashboard.php";
    $pathToTemplate = IMLT_DIR_PATH . 'classes/ImltEnviroment.php';
      return $viewObject->setTemplate($pathToTemplate);

   }

   private function header_menu()
   {
      $viewObject = new IndeedView();
      $viewObject->setTemplate(IMLT_DIR_PATH . 'views/header.php');
      $viewObject->setContentData(array());
      return  $viewObject->getOutput();
   }




   private function imlt_display_my_errors() {
     $viewObject = new ImltErrors();
      return $viewObject->imlt_errors();

   }


   private function imlt_wp_config_edit_constants() {
     $viewObject = new ImltErrors();
     return $viewObject->modify_debug_constants_values();

   }

   private function database()
   {
        require_once IMLT_DIR_PATH . 'classes/ImltDatabase.php';
        $db = new ImltDatabase();
        $data = [
                    'header'        => $this->header_menu(),
                    'allTables'     => $db->getAllTables()
        ];
        $pathToTemplate = IMLT_DIR_PATH . "views/database.php";

        $viewObject = new IndeedView();
        echo $viewObject->setTemplate($pathToTemplate)->setContentData($data)->getOutput();
   }


   private function databaseShowTable()
   {

      $tableName = isset($_GET['table']) ? esc_sql($_GET['table']) : '';
      $offset = isset($_GET['p']) ? esc_sql($_GET['p']) : 0;
      $where = '';
      $db = new ImltDatabase();
      $totalItems = $db->countTableData( $tableName );
      $limit = 10;
      require_once IMLT_DIR_PATH . 'classes/ImltDatabase.php';
      if ( $offset<1 ) $currentPage = 1;
      else $currentPage = $offset;

      require_once IMLT_DIR_PATH . 'classes/ImltPagination.php';
      $pagination = new ImltPagination([
                'base_url'                => admin_url('admin.php?page=imlt_manage&tab=database&table=' . $tableName),
                'param_name'              => 'p',
                'total_items'             => $totalItems,
                'items_per_page'          => $limit,
                'current_page'            => $currentPage,
      ]);

      if ($offset>1){
  			$offset = ( $offset - 1 ) * $limit;
  		} else {
  			$offset = 0;
  		}
      if ($offset + $limit>$totalItems){
        $limit = $totalItems - $offset;
      }

      $data = [
                  'header'            => $this->header_menu(),
                  'tableName'         => $tableName,
                  'items'             => $db->selectDataFromTable( $tableName, $limit, $offset, $where ),
                  'query'             => $db->getQueryForSelectDataFromTable( $tableName, $limit, $offset, $where ),
                  'pagination'        => $pagination->output(),
                  'tableDetails'      => $db->getTableDetails( $tableName ),
                  'allTablesList'     => $db->getAllTables(),
                  ''


      ];


      $pathToTemplate = IMLT_DIR_PATH . "views/database-view-table.php";
      $viewObject = new IndeedView();
      echo $viewObject->setTemplate($pathToTemplate)->setContentData($data)->getOutput();
   }

   private function databaseCustomQuery()
   {

        $db = new ImltDatabase();
        $tableName = isset($_GET['table']) ? esc_sql($_GET['table']) : '';
        $query = '';

          if(@$_POST['imlt-select'] && @$_POST['imlt-where']  && @$_POST['imlt-operator']  && @$_POST['imlt-value'] && @$_POST['imlt-order-by'] && @$_POST['imlt-sorting']) {
            $query = "SELECT " . $_POST["imlt-select"] . " FROM $tableName WHERE " . $_POST["imlt-where"] . " " . $_POST['imlt-operator'] . " '" . trim($_POST['imlt-value']) ."' ORDER BY " . $_POST["imlt-order-by"] . " " . $_POST['imlt-sorting'];


          }  elseif ( !empty($_POST['single_query']) ){

              $query = $_POST['single_query'];

          }

        $query = stripslashes($query);
        if ( stripos( $query, $tableName )===FALSE ){
            $tableName = $db->extractTableNameFromQuery( $query );

        }


        $data = [
                    'header'            => $this->header_menu(),
                    'tableName'         => $tableName,
                    'items'             => $db->getTableDataByQuery( $query ),
                    'query'             => $query,
                    'pagination'        => '',
                    'tableDetails'      => $db->getTableDetails( $tableName ),
                    'allTablesList'     => $db->getAllTables(),


        ];


        $pathToTemplate = IMLT_DIR_PATH . "views/database-view-table.php";
        $viewObject = new IndeedView();
        echo $viewObject->setTemplate($pathToTemplate)->setContentData($data)->getOutput();
   }

   private function edit_wp_config_from_codEditor() {
     $viewObject = new ImltErrors();
     return $viewObject->imlt_direct_edit_wp_conf();

   }

   private function env_sis_report() {
     $viewObject = new ImltEnviroment();
     return $viewObject->sistem_report_details();

   }
   private function get_phpdetails_info() {
     $viewObject = new ImltEnviroment();
     return $viewObject->imlt_phpinfo_details();
   }

   private function crons()
   {
       require_once IMLT_DIR_PATH . 'classes/ImltDatabase.php';
       $db = new ImltDatabase();
       $data = array(
          'header'        => $this->header_menu(),
          'cronJobs'      => $db->getCronJobsList()
       );
       require_once IMLT_DIR_PATH . 'classes/IndeedView.php';
       $viewObject = new IndeedView();
       echo $viewObject->setTemplate( IMLT_DIR_PATH . 'views/crons.php' )->setContentData( $data )->getOutput();
   }


   private function advancedSettings()
   {
      //require_once IMLT_DIR_PATH . 'classes/ImltErrors.php';
      $imltErrors = new ImltErrors();
      return $imltErrors->modify_advances_constants_values();
   }

   private function loggedUsers()
   {
      if ( isset($_POST['imlt-save-lgd-users']) ){
          update_option( 'imlt_track_user', esc_sql($_POST['imlt_track_user']) );
      }
      $doTrackUsers = get_option( 'imlt_track_user' );

      if ( empty($doTrackUsers) ){
          $doTrackUsers = 0;
      }
      require_once IMLT_DIR_PATH . 'classes/ImltDatabase.php';
      $db = new ImltDatabase();
      $data = array(
          'header'            => $this->header_menu(),
          'users'             => $db->selectUsersByLastLoggedTime( time() - 10 * 60 ),
          'imlt_track_user'   => $doTrackUsers,
      );


      $viewObject = new IndeedView();
      echo $viewObject->setTemplate( IMLT_DIR_PATH . 'views/logged-users.php' )->setContentData( $data )->getOutput();
   }

   private function speedTest()
   {
       global $wpdb;
       if ( isset($_POST['save']) ){
           // modified since version 1.5
           $enabled = (int)( sanitize_text_field( $_POST['imtl_test_speed_enabled'] ) ) === 1 ? 1 : 0;
           update_option( 'imtl_test_speed_enabled', $enabled );
       }
       $enable = get_option( 'imtl_test_speed_enabled' );
       $offset = isset($_GET['p']) ? esc_sql($_GET['p']) : 0;

       if ( empty($enable) ){
           $enable = 0;
       }
       require_once IMLT_DIR_PATH . 'classes/ImltDatabase.php';
       $db = new ImltDatabase();
       $totalItems = $db->countTableData( $wpdb->prefix . 'imlt_speed_tests' );

       $limit = 10;
       if ( $offset<1 ) $currentPage = 1;
       else $currentPage = $offset;

        require_once IMLT_DIR_PATH . 'classes/ImltPagination.php';
        $pagination = new ImltPagination([
                  'base_url'                => admin_url('admin.php?page=imlt_manage&tab=speed_test'),
                  'param_name'              => 'p',
                  'total_items'             => $totalItems,
                  'items_per_page'          => $limit,
                  'current_page'            => $currentPage,
        ]);

        if ($offset>1){
          $offset = ( $offset - 1 ) * $limit;
        } else {
          $offset = 0;
        }
        if ($offset + $limit>$totalItems){
          $limit = $totalItems - $offset;
        }

       $data = array(
            'header'            => $this->header_menu(),
            'items'             => $db->getSpeedTestData( $limit, $offset ),
            'enabled'           => $enable,
            'pagination'        => $pagination->output(),
       );

       require_once IMLT_DIR_PATH . 'classes/IndeedView.php';
       $viewObject = new IndeedView();
       echo $viewObject->setTemplate( IMLT_DIR_PATH . 'views/speed-test.php' )->setContentData( $data )->getOutput();
   }

   private function hooksAndActions()
   {
      require_once IMLT_DIR_PATH . 'classes/ImltHooksAndActionsList.php';
      $db = new ImltHooksAndActionsList();
      $source = isset($_GET['source']) ? $_GET['source'] : 0;

      $data = array(
           'header'                 => $this->header_menu(),
           'sourceList'             => $db->getSourceList(),
           'source'                 => $source,
      );
      if ( empty($source) ){
          $data['components'] = $db->getAll();
      } else {
          $data['components'] = $db->getAllBySource( esc_sql($_GET['source']) );
      }
      $viewObject = new IndeedView();
      echo $viewObject->setTemplate( IMLT_DIR_PATH . 'views/hooks-and-actions.php' )->setContentData( $data )->getOutput();
   }

}
