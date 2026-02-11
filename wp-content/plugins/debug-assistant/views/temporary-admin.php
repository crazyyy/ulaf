<?php
if ( !current_user_can('manage_options') || !defined( 'ABSPATH' ) ) {
    exit;
}
// default name, email and password
$admin_name = "admin-". rand(1, 10000);
$admin_email = $admin_name. "@". $_SERVER['HTTP_HOST'];
$imlt_user_error = $imlt_user_email_error = $imlt_user_psw_error = "";

$imlt_user_psw = "";
$imlt_admin_cron_time = "";
$current_time = current_time('timestamp');
$user_id = "";
$imlt_users_details="";
if (isset($_POST['imlt_create_admin']) && isset( $_POST['t'] ) && wp_verify_nonce( $_POST['t'], 'das_ta_nonce' ) )
{

    if (isset($_POST['imlt-username']) && isset($_POST['imlt-email'])  && isset($_POST['admin_time_picker']) && isset($_POST['imlt-psw']))
    {

      if (empty($_POST['imlt-username'])) {
        $imlt_user_error =  'Add an username!<br>';

      } else if (empty($_POST['imlt-email'])) {
        $imlt_user_email_error =  'Add an email!<br>';

      } else if (empty($_POST['imlt-psw'])) {
        $imlt_user_psw = $_POST['imlt-psw'];
        $imlt_user_psw_error =  'Add a password!<br>';

      } else
      {
          $imlt_admin_cron_time = $_POST['admin_time_picker'];

    /// create cron job wich chek at every hour

            if( !wp_next_scheduled('imlt_admin_hourly_event') ) {
                wp_schedule_event(time(), 'hourly',  'imlt_admin_hourly_event');
            }

        $imlt_users_details = array(
                                    'user_login' => sanitize_text_field( $_POST['imlt-username'] ),
                                    'first_name' => sanitize_text_field($_POST['imlt-username']),
                                    'user_email' => sanitize_text_field($_POST['imlt-email']),
                                    'user_pass'  => sanitize_text_field($_POST['imlt-psw']),
                                    'role'       =>  'administrator'
            );

        $user_id = wp_insert_user( $imlt_users_details );

            if ($user_id ){

               $imlt_default_value = add_user_meta($user_id, 'set_cron_time_tmp_admin', $_POST['admin_time_picker'] );
               $imlt_tmp_admin_psw = add_user_meta($user_id, 'tmp_admin_psw', $_POST['imlt-psw']);

            }

          $time = date( "H", $_POST['admin_time_picker'] );
          $imlt_sender = get_option('admin_email');
          $imlt_tmp_admin_mail_to = array($imlt_sender, $_POST['imlt-email']);
          $imlt_tmp_admin_mail_subject = 'Wordpress Admin';
          $imlt_tmp_admin_mail_message = "Welcome to " . site_url() . "\n" . "Your temporary admin account was created and will be deleted at " . date( "H:m", $_POST['admin_time_picker'] ) . "\n" .
                                         "Username" . "\n". $_POST['imlt-username'] . "\n" .
                                         "Password:" . "\n". $_POST['imlt-psw'] . "\n" .
                                         "Role:" . "\n" . $imlt_users_details['role'];
          wp_mail($imlt_tmp_admin_mail_to, $imlt_tmp_admin_mail_subject, $imlt_tmp_admin_mail_message);

    }
  }

}


// get all admin user recorded in temporary admin tab

global $wpdb;
$results = $wpdb->get_results( " SELECT * FROM {$wpdb->prefix}usermeta WHERE meta_key='set_cron_time_tmp_admin' " );


// delete user from user table
if(isset($_GET['imlt_delete_Id'])) {

    global $wpdb;
    $imlt_id = sanitize_text_field( $_GET['imlt_delete_Id'] );
    $imlt_query = $wpdb->prepare("DELETE FROM {$wpdb->prefix}usermeta WHERE meta_key='set_cron_time_tmp_admin' AND user_id=%d", $imlt_id);

    $imlt_deleted_admin_from_usr_meta = $wpdb->query($imlt_query);

    wp_delete_user( $imlt_id );

}

 ?>

<?php echo $data['header'];?>
<div class=" imlt-err-card card">
<div class="card-body">
<div class="imlt_title"><h2>TEMPORARY ADMIN</h2></div>

<form method="POST" action="">
  <fieldset class="form-group">
    <label>Username</label>
    <?php echo $imlt_user_error; ?>
    <div class="input-group">
    <span class="input-group-prepend">
      <span class="input-group-text">
        <i class="icon-user icons font-1xl"></i>
      </span>
    </span>
    <input  type="text" name="imlt-username" value=<?php echo $admin_name; ?>>
  </div>
</fieldset>
<fieldset class="form-group">
  <label>Email</label>
  <?php echo $imlt_user_email_error; ?>
  <div class="input-group">
  <span class="input-group-prepend">
    <span class="input-group-text">
      <i class="icons font-1xl cui-envelope-closed"></i>
    </span>
  </span>
    <input type="email" name="imlt-email" value= <?php echo $admin_email; ?>>
  </div>
</fieldset>
<fieldset class="form-group">
  <label>Select a validation time</label>
  <div class="input-group">
  <span class="input-group-prepend">
    <span class="input-group-text">
      <i class="icon-hourglass icons font-1xl"></i>
    </span>
  </span>
    <select name="admin_time_picker">
      <option value=<?php echo $current_time + 3600 ?>>1 hour</option>
      <option value=<?php echo $current_time + 10800 ?>>3 hours</option>
      <option value=<?php echo $current_time + 36000 ?>>10 hours</option>
      <option value=<?php echo $current_time + 86400 ?>>24 hours</option>
    </select>
    </div>
</fieldset>
<fieldset class="form-group">
  <label>Password</label>
  <?php echo $imlt_user_psw_error; ?>
  <div class="input-group">
  <span class="input-group-prepend">
    <span class="input-group-text">
      <i class="icons font-1xl cui-lock-locked"></i>
      </span>
      </span>
    <input type="hidden" name="t" value="<?php echo wp_create_nonce( 'das_ta_nonce' );?>" />
    <input type="password" name="imlt-psw" id="imlt-psw" value=<?php echo wp_generate_password(15) ?>>
    <button  type="button"  id="imlt-button-face">
    <span class="imlt-face dashicons dashicons-visibility"></span>
      <span id="imlt_show_psw">Show</span>
      </button>
  </div>

</fieldset>


    <input class="imlt-btn btn btn-success" type="submit" name="imlt_create_admin" value="Create">

</form>

<div class="display_admin_users">
  <?php if ( $results ):?>
  <table class="imlt-default-table table table-striped table-bordered datatable">
    <thead>
      <tr>
        <th colspan="4"></th>
        <th colspan="3"style="text-align: center">Actions</th>
      </tr>

      <tr>
        <th>Username</th>
        <th>Name</th>
        <th>Email</th>
        <th>Role</th>
        <th>Password</th>
        <th>Expiration time</th>
        <th>Delete admin</th>
      </tr>
      </thead>
      <tbody>
    <?php



    foreach($results as  $results_value) {

      // Insert meta info about users in wp_usermeta table and based on them set cron
  $imlt_get_user_time     = get_user_meta($results_value->user_id, 'set_cron_time_tmp_admin');
  $imlt_get_admin_tmp_psw = get_user_meta($results_value->user_id, 'tmp_admin_psw');

if(isset( $imlt_get_user_time[0] )) {
  $imlt_mysql_format_time = date('Y-m-d H:i:s', $imlt_get_user_time[0]);
  $imlt_actions_url       = admin_url('admin.php?page=imlt_manage&tab=temporary_admin') . '&imlt_delete_Id=' . $results_value->user_id;

  // get user info from user table
  $user_info = get_userdata($results_value->user_id);


        echo "<tr>";
        echo "<td>" . $user_info->user_login . "</td>";
        echo "<td>" . $user_info->user_login . "</td>";
        echo "<td>" . $user_info->user_email . "</td>";
        echo "<td>administrator</td>";
        echo "<td>". $imlt_get_admin_tmp_psw[0] ."</td>";
        echo "<td>".$imlt_mysql_format_time."</td>";
        echo "<td id='imlt_trash'><a class ='imlt-tgl-clps' href='".$imlt_actions_url."'><i class='icon-trash icons font-2xl'></i></a></td>";
        echo "</tr>";

      }
    }

     ?>
  </tbody>
</table>
  <?php else :?>
      <div class="alert alert-primary" role="alert">No admin user added</div>
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
