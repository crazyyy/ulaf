<?php
if ( !current_user_can('manage_options') || !defined( 'ABSPATH' ) ) {
    exit;
}
echo $data['header'];?>
<?php

$imlt_dis = "";
  if (isset($_POST['save_const_changes']) || isset($_POST['saveNowChanges'])) {
    $imlt_dis = "disabled";
}
$imlt_hide_btn_cls = "";
  if (isset($_POST['saveNowChanges'])) {
    $imlt_hide_btn_cls = "imlt-hide-prf-actions";
  }

 ?>

<div class=" imlt-err-card card">
  <div class="card-body">
  <div class="imlt_title"><h2>ADVANCED SETTINGS</h2></div>
    <div class="alert alert-secondary" role="alert">Speed up your site by enable this constants.</div>
    <form id="adv-settings-checkbox-form" method="post" action="">
      <table class='imlt-debug-const'>
    <tr>
      <td>Disallow File Edit</td>
      <?php
          $value = defined('DISALLOW_FILE_EDIT') && DISALLOW_FILE_EDIT ? 1 : 0;
          if ( isset($_POST['imlt-disallow-file-edit']) ){
              $value = ($_POST['imlt-disallow-file-edit'] == '1') ? 1 : 0;
          }
      ?>
      <td><label class='switch switch-label switch-pill switch-primary'>
        <input class="switch-input" <?php echo $imlt_dis; ?> type='checkbox' <?php echo $value ? 'checked' : '';?> onChange="imlt_checkbox_write_value( this, '[name=imlt-disallow-file-edit]', 1, 0 );" >
        <span class="switch-slider" data-checked="On" data-unchecked="Off"></span>
      </label></td>
      <input type="hidden" name='imlt-disallow-file-edit' value="<?php echo $value;?>" />
    </tr>
    <tr>
      <td>Concatenate Scripts</td>
      <?php
          $value = defined('CONCATENATE_SCRIPTS') && CONCATENATE_SCRIPTS ? 1 : 0;
          if ( isset($_POST['imlt-concatenate-scripts']) ){
              $value = ($_POST['imlt-concatenate-scripts'] == '1') ? 1 : 0;
          }
      ?>
      <td><label class='switch switch-label switch-pill switch-primary'>
        <input class="switch-input" <?php echo $imlt_dis; ?> type='checkbox' <?php echo $value ? 'checked' : '';?> onChange="imlt_checkbox_write_value( this, '[name=imlt-concatenate-scripts]', 1, 0 );"; >
        <span class='switch-slider' data-checked="On" data-unchecked="Off"></span>
      </label></td>
      <input type="hidden" name='imlt-concatenate-scripts' value="<?php echo $value;?>" />
    </tr>
    <tr>
      <td>Compress Scripts</td>
      <?php
          $value = defined('COMPRESS_SCRIPTS') && COMPRESS_SCRIPTS ? 1 : 0;
          if ( isset($_POST['imlt-compress-scripts']) ){
              $value = ($_POST['imlt-compress-scripts'] == '1') ? 1 : 0;
          }
      ?>
      <td><label class='switch switch-label switch-pill switch-primary'>
        <input class="switch-input" <?php echo $imlt_dis; ?> type='checkbox' <?php echo $value ? 'checked' : '';?> onChange="imlt_checkbox_write_value( this, '[name=imlt-compress-scripts]', 1, 0 );"; >
        <span class='switch-slider' data-checked="On" data-unchecked="Off"></span>
      </label></td>
      <input type="hidden" name='imlt-compress-scripts' value="<?php echo $value;?>" />
    </tr>
    <tr>
      <td>Enforce GZIP</td>
      <?php
          $value = defined('ENFORCE_GZIP') && ENFORCE_GZIP ? 1 : 0;
          if ( isset($_POST['imlt-enforce-gzip']) ){
              $value = ($_POST['imlt-enforce-gzip'] == '1') ? 1 : 0;
          }
      ?>
      <td><label class='switch switch-label switch-pill switch-primary'>
        <input class="switch-input" <?php echo $imlt_dis; ?> type='checkbox' <?php echo $value ? 'checked' : '';?> onChange="imlt_checkbox_write_value( this, '[name=imlt-enforce-gzip]', 1, 0 );"; >
        <span class='switch-slider' data-checked="On" data-unchecked="Off"></span>
      </label></td>
      <input type="hidden" name='imlt-enforce-gzip' value="<?php echo $value;?>" />
    </tr>
    <!--<tr>
      <td>Wordpress Default Theme</td>
      <?php
          /*$value = defined('WP_DEFAULT_THEME') && WP_DEFAULT_THEME ? WP_DEFAULT_THEME : '';
          if ( isset($_POST['imlt-default-theme']) ){
              $value = $_POST['imlt-default-theme'];
          }*/
      ?>
      <td>
        <input type='text' <?php echo $imlt_dis; ?> name='imlt-default-theme' value="<?php echo $value;?>" />
      </td>
    </tr>-->
    <tr>
      <td>Wordpress Mail Interval</td>
      <?php
         $value = defined('WP_MAIL_INTERVAL') && WP_MAIL_INTERVAL ? (int)WP_MAIL_INTERVAL : '300';
          if ( isset($_POST['imlt-mail-interval']) ){
              $value = $_POST['imlt-mail-interval'];
          }

      ?>
      <td>
        <input type='number' min="300" name='imlt-mail-interval' <?php echo $imlt_dis; ?> value="<?php echo $value;?>" />
        <?php if ( $imlt_dis=='disabled' ):?>
        <input type='hidden' min="300" name='imlt-mail-interval' value="<?php echo $value;?>" />
        <?php endif;?>
        <span>Default time in seconds is 300</span>
      </td>
    </tr>
    <!--<tr>
      <td>Set Mail Interval time in seconds</td>
      <?php
        /*  $value = defined('WP_MAIL_INTERVAL') && WP_MAIL_INTERVAL ? WP_MAIL_INTERVAL : '';
          if ( isset($_POST['imlt-mail-interval-nr']) ){
              $value = $_POST['imlt-mail-interval-nr'];
          } */
      ?>
      <td><label class=''>
        <input type='number' name='imlt-mail-interval-nr' <?php //echo $imlt_dis; ?> value="<?php //echo $value;?>" min='100' max='100000'>
      </label></td>
    </tr>-->
    <tr>
      <td>Wordpress Memory Limit</td>
      <?php
          $value = (defined('WP_MEMORY_LIMIT') && WP_MEMORY_LIMIT) ? (int)WP_MEMORY_LIMIT : '';

          if ( isset($_POST['imlt-memoryLimit']) ){
              $value = $_POST['imlt-memoryLimit'];
          }
      ?>
      <td><label class=''>
        <input type='number' name='imlt-memoryLimit' <?php echo $imlt_dis; ?> value='<?php echo $value;?>' />
        <?php if ($imlt_dis=='disabled') :?>
          <input type='hidden' name='imlt-memoryLimit'  value='<?php echo $value;?>' />
        <?php endif; ?>
      </label></td>
    </tr>
    <tr>
      <td>Wordpress Max Memory Limit</td>
      <?php
        $value = defined('WP_MAX_MEMORY_LIMIT') && WP_MAX_MEMORY_LIMIT ? (int)WP_MAX_MEMORY_LIMIT : '';

        if ( isset($_POST['imlt-max-memoryLimit']) ){
            $value = $_POST['imlt-max-memoryLimit'];
        }
      ?>
      <td><label class=''>
        <input type='number' name='imlt-max-memoryLimit' <?php echo $imlt_dis; ?> value='<?php echo $value;?>'>
        <?php if ($imlt_dis=='disabled') :?>
          <input type='hidden' name='imlt-max-memoryLimit'  value='<?php echo $value;?>' />
        <?php endif; ?>
      </label>
      <span>Default memory is 256M</span>
    </td>
    </tr>

    <tr>
      <td>Disable Cron</td>
      <?php
          $value = defined('DISABLE_WP_CRON') && DISABLE_WP_CRON ? 1 : 0;
          if ( isset($_POST['imlt-disable-cron']) ){
              $value = ($_POST['imlt-disable-cron'] == '1') ? 1 : 0;
          }
      ?>
      <td>
        <label class='switch switch-label switch-pill switch-primary'>
            <input class="switch-input" type='checkbox' <?php echo $imlt_dis; ?> <?php echo $value ? 'checked' : '';?> onChange="imlt_checkbox_write_value( this, '[name=imlt-disable-cron]', 1, 0 );" />
            <span class='switch-slider' data-checked="On" data-unchecked="Off"></span>
        </label>
        <input type="hidden" name='imlt-disable-cron' value="<?php echo $value;?>" />
      </td>
    </tr>
    <tr>
      <td>Alternate Cron</td>
      <?php
          $value = defined('ALTERNATE_WP_CRON') && ALTERNATE_WP_CRON ? 1 : 0;
          if ( isset($_POST['imlt-alternate-cron']) ){
              $value = ($_POST['imlt-alternate-cron'] == '1') ? 1 : 0;
          }
      ?>
      <td>
          <label class='switch switch-label switch-pill switch-primary'>
              <input class="switch-input" type='checkbox' <?php echo $imlt_dis; ?> <?php echo $value ? 'checked' : '';?> onChange="imlt_checkbox_write_value( this, '[name=imlt-alternate-cron]', 1, 0 );" />
              <span class='switch-slider' data-checked="On" data-unchecked="Off"></span>
          </label>
          <input type="hidden" name='imlt-alternate-cron' value="<?php echo $value;?>" />
      </td>
    </tr>
    <tr>
      <td>Cron Lock Timeout</td>
      <td><label class=''>
        <?php
            $value = (defined('WP_CRON_LOCK_TIMEOUT') && WP_CRON_LOCK_TIMEOUT) ? (int)WP_CRON_LOCK_TIMEOUT : '';

            if ( isset($_POST['imlt_cron_lock_timeout']) ){
                $value = $_POST['imlt_cron_lock_timeout'];
            }
        ?>
        <input type='number' name='imlt_cron_lock_timeout' <?php echo $imlt_dis; ?> value="<?php echo $value;?>" />
        <?php if ( $imlt_dis=='disabled' ):?>
        <input type='hidden' name='imlt_cron_lock_timeout' value="<?php echo $value;?>" />
      <?php endif; ?>
        <span>Default time is 60 seconds</span>
      </label></td>
    </tr>
  </table>
  <input type='submit' value='Perform actions' name='save_const_adv_changes' class='<?php echo $imlt_hide_btn_cls; ?> imlt-btn btn btn-outline-primary'>
      <?php echo $data['advanced-settings-edit'];?>
</form>
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
