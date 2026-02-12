<?php
if ( !current_user_can('manage_options') || !defined( 'ABSPATH' ) ) {
    exit;
}
echo $data["header"];

$imlt_plugins	= get_plugins();
$imlt_active_plugins = get_option( 'active_plugins', array() );
$imlt_count_act_plg = count($imlt_active_plugins);
$imlt_count_inact_plg = count($imlt_plugins) - count($imlt_active_plugins);
 ?>
 <div class="imlt_title"><h2><?php esc_html_e('Debug Assistant', 'debug-assistant');?></h2></div>
<div class="row">
  <div class="col-sm-6 col-lg-3">
    <div class="imlt-crd card">
      <a href="<?php esc_html_e( admin_url('admin.php?page=imlt_manage&tab=debug') ); ?>">
      <div class="card-body p-0 d-flex align-items-center">
        <i class="fa-imtda fa-imtda-bug bg-primary p-4 font-2xl mr-3"></i>
        <div>
          <div class="text-value-sm text-primary"><?php esc_html_e('Debug', 'debug-assistant');?> -
          <?php  echo $imlt_check_db = (defined('WP_DEBUG') && WP_DEBUG == true) ? "<span class='text-value-sm text-success'>ON</span>" : "<span class='text-value-sm text-danger'>OFF</span>"; ?>

          </div>

        </div>
      </div>
      </a>
    </div>
</div>
  <div class="col-sm-6 col-lg-3">
    <div class="imlt-crd card">
      <a href="<?php echo admin_url('admin.php?page=imlt_manage&tab=error_logs'); ?>">
      <div class="card-body p-0 d-flex align-items-center">
        <i class="fa-imtda fa-imtda-exclamation-circle bg-warning p-4 font-2xl mr-3"></i>
        <div>
          <div class="text-value-sm text-warning">Error Display -
            <?php  echo $imlt_check_db = (defined('WP_DEBUG_DISPLAY') && WP_DEBUG_DISPLAY == true) ? "<span class='text-value-sm text-success'>ON</span>" : "<span class='text-value-sm text-danger'>OFF</span>"; ?>
          </div>
        </div>
      </div>
      </a>
    </div>
  </div>

  <div class="col-sm-6 col-lg-3">
    <div class="imlt-crd card">
      <a class="imlt-dsh-link" href="<?php echo admin_url('admin.php?page=imlt_manage&tab=advanced_settings'); ?>">
      <div class="card-body p-0 d-flex align-items-center">
        <i class="icon-clock icons bg-secondary p-4 font-2xl mr-3"></i>
        <div>
          <div class="text-value-sm text-secondary">Disable Cron -
            <?php  echo $imlt_check_db = (defined('DISABLE_WP_CRON') && DISABLE_WP_CRON == true) ? "<span class='text-value-sm text-success'>ON</span>" : "<span class='text-value-sm text-danger'>OFF</span>"; ?>
          </div>
        </div>
      </div>
      </a>
    </div>
  </div>

  <div class="col-sm-6 col-lg-3">
    <div class="imlt-crd card">
      <a href="<?php echo admin_url('admin.php?page=imlt_manage&tab=advanced_settings'); ?>">
      <div class="card-body p-0 d-flex align-items-center">
        <i class="fa-imtda fa-imtda-compress bg-dark p-4 font-2xl mr-3"></i>
        <div>
          <div class="text-value-sm text-dark">Compress Scripts -
            <?php  echo $imlt_check_db = (defined('COMPRESS_SCRIPTS') && COMPRESS_SCRIPTS == true) ? "<span class='text-value-sm text-success'>ON</span>" : "<span class='text-value-sm text-danger'>OFF</span>"; ?>
          </div>
        </div>
      </div>
      </a>
    </div>
</div>
</div>

  <div class="row">
    <div class="col-sm-6 col-md-6">
      <div class="imlt-crd card">

        <div class="card-body p-0 d-flex align-items-center">
          <i class="fa-imtda fa-imtda-database bg-info p-4 font-2xl mr-3"></i>
          <div>
            <div class="text-value-sm text-info">Database</div>
            <div class=" text-dark">Name - <span class="text-dark font-weight-bold medium "><?php global $wpdb; echo $wpdb->dbname; ?></span> </div>
            <div class=" text-dark">Tables - <span class="text-dark font-weight-bold medium"><?php echo imlt_count_all_wp_tables();?></span> tables </div>
          </div>
        </div>

      </div>

      <div class="imlt-crd card">

        <div class="card-body p-0 d-flex align-items-center">
          <i class="fa-imtda fa-imtda-database bg-danger p-4 font-2xl mr-3"></i>
          <div>
            <div class="text-value-sm text-danger">PHP</div>
            <div class=" text-dark">Version - <span class="text-dark font-weight-bold medium "><?php echo PHP_VERSION; ?></span> </div>
          </div>
        </div>

      </div>
    </div>

    <div class="col-sm-6 col-md-6">

      <!-- Active plugins -->

      <div id="imlt_plg" class="imlt-crd card">
        <div class="card-header text-white bg-success" data-toggle="collapse" href="#imlt_collapse"  role="button"  aria-expanded="fa-imtdalse" aria-controls="imlt_collapse"><span class="badge badge-success text-value-sm"><?php echo $imlt_count_act_plg; ?></span><span class="text-value-sm"> Active plugins</span>
          <div class="card-header-actions">
            <a class="card-header-action btn-minimize" >
              <i class="imlt_icon icon-arrow-up"></i>
            </a>
          </div>
    </div>
    <div class="collapse" id="imlt_collapse">
          <div class="card-body"><?php echo $data['dsh-plg-act']; ?></div>
    </div>
  </div>

    <!-- Inactive plugins -->

    <div id="imlt_plg" class="imlt-crd card">

      <div class="card-header text-white bg-primary asis_inac" data-toggle="collapse" href="#imlt_collapse_inactive"  role="button"  aria-expanded="fa-imtdalse" aria-controls="imlt_collapse_inactive"><span class="badge badge-primary text-value-sm"><?php echo $imlt_count_inact_plg; ?></span><span class="text-value-sm">Inactive plugins</span>
        <div class="card-header-actions">
          <a class="card-header-action btn-minimize">
            <i class="imlt_icon_inact icon-arrow-up"></i>
          </a>
        </div>
      </div>
      <div class="collapse" id="imlt_collapse_inactive">
        <div class="card-body"><?php echo $data['dsh-plg-inact']; ?></div>
      </div>
    </div>

  </div>
</div>

<div class="row">
  <div class="col-sm-6 col-md-4">
    <div class="imlt-crd card">

      <div class="card-body p-0 d-flex align-items-center">
        <i class="fa-imtda fa-imtda-wordpress bg-primary p-4 font-2xl mr-3"></i>
        <div>
          <div class="text-value-sm text-primary">Wordpress</div>
          <div class=" text-dark">Version - <span class="text-dark font-weight-bold medium "><?php echo get_bloginfo( 'version' ); ?></span> </div>
        </div>
      </div>

    </div>
  </div>

  <div class="col-sm-6 col-md-4">
    <div class="imlt-crd card">

      <div class="card-body p-0 d-flex align-items-center">
        <i class="fa-imtda fa-imtda-paint-brush bg-success p-4 font-2xl mr-3"></i>
        <div>
          <div class="text-value-sm text-success">Theme</div>
          <div class=" text-dark">Current theme - <span class="text-dark font-weight-bold medium "><?php echo wp_get_theme()->Name . '(' .wp_get_theme()->Version .')'; ?></span> </div>
        </div>
      </div>

    </div>
  </div>

  <div class="col-sm-6 col-md-4">
    <div class="imlt-crd card">

      <div class="card-body p-0 d-flex align-items-center">
        <i class="fa-imtda fa-imtda-database bg-warning p-4 font-2xl mr-3"></i>
        <div>
          <div class="text-value-sm text-warning">jQuery</div>
          <div class=" text-dark">Version - <span class="text-dark font-weight-bold medium "><?php echo  $GLOBALS['wp_scripts']->registered['jquery']->ver; ?></span> </div>
        </div>
      </div>

    </div>
  </div>
</div>


  <div id='imlt_crd_full_width' class="imlt-crd card">
      <div class="card-body">
        <div class="row">
          <div class="col-sm-6 col-md-4">
            <div class="card text-white bg-primary">
              <div class="card-body">
                <div class="text-value-lg font-2xl">Mysql</div>
                <div class="imlt_separator"></div>
                  <div class="text-value-md">Version - <?php echo $wpdb->db_version(); ?></div>
              </div>
            </div>
          </div>
          <div class="col-sm-6 col-md-4">
            <div class="card text-white bg-info">
              <div class="card-body">
                <div class="text-value">Server Software</div>
                <div class="imlt_separator"></div>
                <div><?php echo $_SERVER['SERVER_SOFTWARE']; ?></div>
              </div>
            </div>
          </div>
          <div class="col-sm-6 col-md-4">
            <div class="card text-white bg-warning">
              <div class="card-body">
                <div class="text-value">Sessions</div>
                <div class="imlt_separator"></div>
                <div><?php echo PHP_SESSION_DISABLED != session_status()  ? 'enabled' : 'disabled'; ?></div>
              </div>
            </div>
          </div>
        </div>
        <div class="row">
          <div class="col-sm-6 col-md-4">
            <div class="card text-white bg-danger">
              <div class="card-body">
                <div class="text-value">Cookies</div>
                <div class="imlt_separator"></div>
                <div><?php echo ini_get( 'session.use_cookies' ) ? 'on' : 'off'; ?></div>
              </div>
            </div>
          </div>
          <div class="col-sm-6 col-md-4">
            <div class="card text-white bg-primary">
              <div class="card-body">
                <div class="text-value">cURL</div>
                <div class="imlt_separator"></div>
                <div><?php echo function_exists( 'curl_init' ) ? 'on' : 'off'; ?></div>
              </div>
            </div>
          </div>
          <div class="col-sm-6 col-md-4">
            <div class="card text-white bg-success">
              <div class="card-body">
                <div class="text-value">Openssl</div>
                <div class="imlt_separator"></div>
                <div><?php echo extension_loaded('Openssl') ? 'yes' : 'no'; ?></div>
              </div>
            </div>
          </div>
      </div>
  </div>
</div>
</div>
</div>
</main>
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
