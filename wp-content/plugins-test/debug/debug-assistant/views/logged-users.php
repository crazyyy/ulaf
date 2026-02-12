<?php
if ( !current_user_can('manage_options') || !defined( 'ABSPATH' ) ) {
    exit;
}
echo $data['header'];?>

<div class=" imlt-err-card card">
<div class="card-body">
  <div class="imlt_title"><h2>LOGGED USERS</h2></div>

    <div class="imlt-logged-alert alert alert-primary" role="alert">To see logged users in site enable the option below.</div>
    <form method="post" action="">

            <label>Enable</label>
            <label class='switch switch-label switch-pill switch-primary'>
                <input class="switch-input" type='checkbox' <?php echo $data['imlt_track_user'] ? 'checked' : '';?> onChange="imlt_checkbox_write_value( this, '[name=imlt_track_user]', 1, 0 );" >
                <span class="switch-slider" data-checked="On" data-unchecked="Off"></span>
            </label>
            <input type="hidden" name='imlt_track_user' value="<?php echo $data['imlt_track_user'];?>" />

        <div>
            <input type='submit' value='Save' name='imlt-save-lgd-users' class='imlt-btn btn btn-info active'>
        </div>
    </form>
    <div class="imlt-logged-alert alert alert-warning" role="alert">This action consuming resources. Keep it OFF when is not necessary.</div>
    <div>
        <?php if ($data['imlt_track_user'] == 1) : ?>
        <?php if ($data['users']):  ?>
            <table class="imlt-default-table table table-striped table-bordered datatable">
              <thead>
                <tr>
                    <th>Username</th>
                    <th>E-mail</th>
                    <th>Last Login</th>
                </tr>
              </thead>
            <tbody>
                <?php foreach ( $data['users'] as $user ):?>
                    <tr>
                        <td><?php echo $user->user_login;?></td>
                        <td><?php echo $user->user_email;?></td>
                        <td><?php echo indeed_print_date_like_wp($user->value);?></td>
                    </tr>
                <?php endforeach;?>
              </tbody>
            </table>
          <?php else : ?>
              <h3>No users logged in last 30minutes!</h3>
        <?php endif;?>
      <?php endif; ?>
    </div>
  </div>
  </div>
  </div>
  </main>
  </div>
</div>
<div class="imlt-pos"></div>
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
