<?php
/**
 * Plugin Name: Tools4WP
 * Plugin URI: https://tools4wp.com
 * Description: Add functions to other WordPress plugins.
 * Version: 1.7.5.12
 * Author: Tools4WP
 * Author URI: https://tools4wp.com/
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Version Number
update_option( 'tools4wp_version', '1.7.5.12' );
// update_option('updraftplus_addons', false);
// update_option('mailpoet_version_license', false);
// update_option('bricksforge_license', false);

if (is_admin()) {
	$url = $_SERVER['REQUEST_URI'];
	if (strpos($url, 'localhost') !== false) {
		die('This plugin cannot be used on localhost.');
	}
}

// Load addons

    include("addons/wordfence.php");
    include("addons/reset_pro.php");
    include("addons/auto_spinner.php");
    include("addons/breakdance.php");
    include("addons/greenshift.php");
    include("addons/thrive_suite.php");
    include("addons/updraftplus.php");
    include("addons/bricksforge.php");
    include("addons/mailpoet.php");
	include("addons/bricks.php");
	include("addons/bricks_extras.php");
    // include("addons/automatic.php");
    // include("addons/wp_indeed.php");
    include("addons/bultimate.php");
    // include("addons/smushit.php");


// Check if update available.
$tools4wp_version_number = 'aHR0cHM6Ly90b29sczR3cC5jb20vdG9vbHM0d3BfdmVyc2lvbi5qc29u';
$urlParts = parse_url(base64_decode($tools4wp_version_number));
if (array_key_exists('scheme', $urlParts)) {
  $ch = curl_init();
  curl_setopt($ch, CURLOPT_URL, $urlParts['scheme'] . '://' . $urlParts['host'] . $urlParts['path']);
  curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
  $file = curl_exec($ch);
  curl_close($ch);

	$data = json_decode($file, true);
	if($data) {
		$tt_ver = $data['ver_num'];
		$tt_up_down = $data['tt_up_do'];
		// $update_notice = $data['update_notice'];
        $bricksforge_license_url = $data['bricksforge_url'];
        $bricksforge_ver = $data['bricksforge_ver'];
        $greenshift_license_url = $data['greenshift'];
        $updraftplus_addons_url = $data['updraftplus'];
        $mailpoet_version_url = $data['mailpoet'];
	}
}


	add_filter( 'plugin_action_links', 'add_license_link', 10, 2 );
	function add_license_link( $links, $file ) {
		if ( strpos( $file, 'tools4wp' ) !== false ) {
			$links[] = '<a href="' . admin_url( 'tools.php?page=tools4wp' ) . '">Settings</a>';
		}
		return $links;
	}


	// Create the Tools4WP admin page
	function tools4wp_admin_page() {
		add_submenu_page(
			'tools.php',
			'Tools4WP',
			'Tools4WP',
			'manage_options',
			'tools4wp',
			'tools4wp_admin_page_callback'
		);
	}
	add_action( 'admin_menu', 'tools4wp_admin_page' );


// Create the Tools4WP page.
function tools4wp_admin_page_callback() {
	if ( ! current_user_can( 'manage_options' ) )  {
		wp_die( __( 'You do not have sufficient permissions to access this page.' ) );
	}
    chmod(WP_CONTENT_DIR . '/plugins/tools4wp/files', 0755);

    if (!extension_loaded('zip')) {
        echo '<div class="updated"><p>Zip extension is disabled. Please enabled.</p></div>';
        return false;
    }

    ?>


     <!-- Tab links -->
    <div class="tab">
		<button class="tablinks" onclick="openproduct(event, 'welcome')" id="defaultOpen">Welcome</button>
        <button class="tablinks" onclick="openproduct(event, 'breakdance')">Breakdance</button>
		<button class="tablinks" onclick="openproduct(event, 'bricks')">Bricks Heads</button>
		<button class="tablinks" onclick="openproduct(event, 'divi')">Elegant Themes</button>
		<button class="tablinks" onclick="openproduct(event, 'greenshift')">Greenshift</button>
		<button class="tablinks" onclick="openproduct(event, 'mailpoet')">Mailpoet</button>
		<button class="tablinks" onclick="openproduct(event, 'reset_pro')">Reset Pro</button>
		<button class="tablinks" onclick="openproduct(event, 'Thrive_Suite')">Thrive Suite</button>
		<button class="tablinks" onclick="openproduct(event, 'updraftplus')">Updraftplus</button>
        <button class="tablinks" onclick="openproduct(event, 'Wordfence')">Wordfence</button>
        <button class="tablinks" onclick="openproduct(event, 'auto_spinner')">WP Auto Spinner</button>
    </div>

    <!-- Tab menu content -->
<div id="welcome" class="tabcontent">
	<div class="wrap">
        <img src="..\wp-content\plugins\tools4wp\images\tools4wp_logo.png" alt="Tools4wp logo" style="width:50px;height:50px;padding-right:10px;">
        <?php echo "<h1>Tools 4 WordPress </h1> Version: ".get_option('tools4wp_version'); ?>
        <div style="width: 100%; display: flex; flex-wrap: wrap; ">

        </div>
    </div>
</div>

    <div id="Thrive_Suite" class="tabcontent">
        <?php
        if( is_plugin_active('thrive-product-manager/thrive-product-manager.php') ) {
            $uemail = isset( $_POST['uemail'] ) ? sanitize_text_field( $_POST['uemail'] ) : '';
        	$channel = isset( $_POST['channel'] ) ? sanitize_text_field( $_POST['channel'] ) : '';
        	$tt_license_number = isset( $_POST['tt_license_number'] ) ? sanitize_text_field( $_POST['tt_license_number'] ) : '';

        	if ( isset( $_POST['submit'] ) ) {
        		if ( $uemail == '' || $channel == '' ) {
        			echo '<div class="error"><p>Error: All fields must be filled in.</p></div>';
        		} else {
                    // Update the options
                    update_option('thrive_themes_suite', true);
                    update_option( 'tt_license_number', $tt_license_number );
                    update_option( 'tve_update_option', $channel );
                    update_option( 'tpm_license_notice', '1' );

                    if (get_option('tpm_license_notice') == 1) {
                        $lic_not = 'License valid until ';
                        $lic_not = $lic_not.''.$date1;
                    }
                // Output a success message
                Global $date1;
                echo '<div class="updated"><p>Success! Your license is connected and will expire on ' . $date1 . '</p></div>';
                }
            }

        	// Output the admin page HTML
        	Global $uemail;
        	//Global $channel;
        	Global $lic_not;
        	Global $lic_avail;
        	//Global $tt_license_number;
        	$tt_license_number = get_option('tt_license_number');
        	$channel = get_option( 'tve_update_option' );

             ?>
        		<div class="wrap">
        			<h2>Thrive Suite License</h2>
        			<?php
        				if (!empty($lic_not)) {
        					echo "<h4>".$lic_not."</h4>";
        				}
        			?>
        			<div style="width: 100%; display: flex; flex-wrap: wrap; margin-right: -50px; margin-left: -50px;">
            			<div style="width: 50%; padding-right: 50px; padding-left: 50px;">
            				<form method="post" action="">
            					<table class="form-table">
            						<tbody>
            							<tr>
            								<th scope="row"><label for="uemail">Account Email</label></th>
            								<td>
            								<input name="uemail" type="email" id="uemail" value="<?php echo $uemail; ?>" class="regular-text" readonly>
            								</td>
            							</tr>
            							<tr>
            								<th scope="row"><label for="channel">Upgrade Path</label></th>
                							<td>
                								<input type="radio" name="channel" value="stable" <?php if($channel == 'stable') echo 'checked'; ?>> Stable<br>
                								<input type="radio" name="channel" value="beta" <?php if($channel == 'beta') echo 'checked'; ?>> Beta<br>
                							</td>
            						    </tr>
            						<tr>
            							<th scope="row"><label for="tt_license_number">License key</label></th>
            							<td>
            								<input name="tt_license_number" type="password" id="tt_license_number" value="<?php echo $tt_license_number; ?>" class="regular-text" >
            							</td>
            						</tr>
            					</tbody>
            				</table>
            					<p class="submit">
            						<input type="checkbox" name="terms" id="terms" required > I agree to never share this plugin. <br><br>
            						<input type="submit" name="submit" id="submit" class="button button-primary" value="Confirm details" >
            						<input type="button" name="cancel" id="cancel" class="button" value="Install Products" onclick="location.href='/wp-admin/admin.php?page=thrive_product_manager'" />
            					</p>
            				</form>
            			</div>
            				<div style="width: 30%; padding-right: 50px; padding-left: 50px;">
            					<a href="https://tools4wp.com/thrive-suite/" target="_blank" ><img src="..\wp-content\plugins\tools4wp\images\thrive_ad.gif" width="254" height="331" /></a>
            				</div>
        			</div>
        		</div>
    	<?php } else { ?>
            <h3>Activate Thrive Product Manager to change options</h3>
            <?php if (file_exists(WP_PLUGIN_DIR . '/thrive-product-manager/thrive-product-manager.php')) {  ?>

            <?php } else { ?>
    		<input type="button" name="cancel" id="cancel" class="button" value="Download TPM" onclick="window.open('https://9fba8fa71256a6495f82-41873bbf94a0f18ee40f3b2aa324e2ee.ssl.cf5.rackcdn.com/thrive-product-manager-1.11.zip', '_blank');" />
            <?php } ?>
    		<input type="button" name="cancel" id="cancel" class="button" value="Download companion themes" onclick="window.open('https://mega.nz/folder/xWJy0Y7B#stcBk4G3ylD_2oPEOiNIQg', '_blank');" />
        <?php } ?>
    </div>

    <div id="Wordfence" class="tabcontent">

    <?php if( is_plugin_active('wordfence/wordfence.php') ) { ?>
		<div style="width: calc(50% - 50px); padding: 0 7px; margin: 0 7px 7px; border: 0px solid #ccc;">

        <?php
        if ( isset( $_POST['wf_license_submit'] ) ) {
        	update_option( 'wf_license', true );
        }
        ?>
			<div class="wrap">
				<h2>WordFence</h2>

				<div style="width: 100%; display: flex; flex-wrap: wrap; margin-right: -5px; margin-left: -5px;">
					<div style="width: 80%; padding-right: 5px; padding-left: 5px;">
					<form method="post" action="">
						<table class="form-table">
							<tbody>
								<tr>
									<th scope="row"><label for="wf_license_submit">Convert WordFence to Premium?</label></th>
								</tr>
							</tbody>
						</table>
						<p class="submit">
							<input type="checkbox" name="terms" id="terms" required > I agree to never share this plugin. <br><br>
							<input type="submit" name="wf_license_submit" id="wf_license_submit" class="button button-primary" value="Yes, please." >
				<input type="button" name="cancel" id="cancel" class="button" value="Open WordFence settings" onclick="location.href='/wp-admin/admin.php?page=Wordfence'" />
						</p>
					</form>
					</div>
				</div>
			</div>
		</div>
	<?php } else { ?>
        <h3>Activate Wordfence to change options</h3>
		<input type="button" name="cancel" id="cancel" class="button" value="Download Wordfence" onclick="window.open('https://downloads.wordpress.org/plugin/wordfence.7.6.2.zip', '_blank');" />
    <?php } ?>
    </div>

    <div id="auto_spinner" class="tabcontent">
    <?php if( is_plugin_active('wp-auto-spinner/wp-auto-spinner.php') ) { ?>
    	<div style="width: calc(50% - 50px); padding: 0 7px; margin: 0 7px 7px; border: 0px solid #ccc;">

    		<?php
    		if ( isset( $_POST['auto_spinner_license_submit'] ) ) {
    			update_option( 'auto_spinner_license', true );
    		}
    		?>

			<div class="wrap">
				<h2>WP Auto Spinner</h2>

				<div style="width: 100%; display: flex; flex-wrap: wrap; margin-right: -5px; margin-left: -5px;">
					<div style="width: 80%; padding-right: 5px; padding-left: 5px;">
    					<form method="post" action="">
    						<table class="form-table">
    							<tbody>
    								<tr>
    									<th scope="row"><label for="auto_spinner_license_submit">Get access to plugin settings?</label></th>
    								</tr>
    							</tbody>
    						</table>
    						<p class="submit">
    							<input type="checkbox" name="terms" id="terms" required > I agree to never share this plugin. <br><br>
    							<input type="submit" name="auto_spinner_license_submit" id="auto_spinner_license_submit" class="button button-primary" value="Yes, please." >
    							<input type="button" name="cancel" id="cancel" class="button" value="Open Auto Spinner Settings" onclick="location.href='/wp-admin/admin.php?page=wp_auto_spinner_settings'" />
    						</p>
    					</form>
					</div>
				</div>
			</div>
    	</div>
	<?php } else { ?>
        <h3>Activate WP Auto Spinner to change options</h3>
		<input type="button" name="cancel" id="cancel" class="button" value="Download WP Auto Spinner" onclick="window.open('https://babia.to/resources/wordpress-auto-spinner-articles-rewriter-by-valvepress.49/', '_blank');" />
    <?php } ?>
    </div>

    <div id="breakdance" class="tabcontent">
    <?php if( is_plugin_active('breakdance/plugin.php') ) { ?>
		<div style="width: calc(50% - 50px); padding: 0 7px; margin: 0 7px 7px; border: 0px solid #ccc;">

			<?php
			if ( isset( $_POST['breakdance_license_submit'] ) ) {
				update_option( 'breakdance_license', true );
			}
			?>
			<div class="wrap">
				<h2>Breakdance</h2>

				<div style="width: 100%; display: flex; flex-wrap: wrap; margin-right: -5px; margin-left: -5px;">
    				<div style="width: 80%; padding-right: 5px; padding-left: 5px;">
        				<form method="post" action="">
        					<table class="form-table">
        						<tbody>
        							<tr>
        								<th scope="row"><label for="breakdance_license_submit">Get free access to Pro features?</label></th>
        							</tr>
        						</tbody>
        					</table>
        					<p class="submit">
        						<input type="checkbox" name="terms" id="terms" required > I agree to never share this plugin. <br><br>
        						<input type="submit" name="breakdance_license_submit" id="breakdance_license_submit" class="button button-primary" value="Yes, Please." >
        						<input type="button" name="cancel" id="cancel" class="button" value="Open Breakdance settings" onclick="location.href='/wp-admin/admin.php?page=breakdance_settings'" />
        					</p>
        				</form>
    				</div>
				</div>
			</div>
		</div>
	<?php } else { ?>
        <h3>Activate Breakdance to change options</h3>
		<input type="button" name="cancel" id="cancel" class="button" value="Download Breakdance Free" onclick="window.open('https://breakdance.com/free', '_blank');" />
    <?php } ?>
    </div>

    <div id="greenshift" class="tabcontent">
		<?php if( is_plugin_active('greenshift-animation-and-page-builder-blocks/plugin.php') ) {
		    Global $greenshift_license_url; ?>
			<div style="width: calc(50% - 50px); padding: 0 7px; margin: 0 7px 7px; border: 0px solid #ccc;">

				<?php
				    // Check if the button was pressed
					if ( isset( $_POST['gspb_license_submit'] ) ) {
						update_option('gspb_license', true);
                        update_option( 'greenshift_url', $greenshift_license_url);
                    }
				?>
					<div class="wrap">
						<h2>GreenShift</h2>

						<div style="width: 100%; display: flex; flex-wrap: wrap; margin-right: -5px; margin-left: -5px;">
    						<div style="width: 80%; padding-right: 5px; padding-left: 5px;">
        						<form action="" method="post">
        							<table class="form-table">
        								<tbody>
        									<tr>
        										<th scope="row"><label for="gspb_license_submit">Enable the use of addons?</label></th>
        									</tr>
        								</tbody>
        							</table>
        							<p class="submit">
        								<input type="checkbox" name="terms" id="terms" required > I agree to never share this plugin. <br><br>
        								<input type="submit" name="gspb_license_submit" value="Yes, Please." class="button-primary">
        								<input type="button" name="cancel" id="cancel" class="button" value="Download addons" onclick="location.href='/wp-admin/admin.php?page=greenshift_dashboard-addons'" />
        							</p>
        						</form>
    					    </div>
				        </div>
			        </div>
		    </div>
		<?php } else { ?>
        <h3>Activate Greenshift to change options</h3>
        <?php } ?>
    </div>

    <div id="updraftplus" class="tabcontent">
      	<?php if( is_plugin_active('updraftplus/updraftplus.php') ) {
	    Global $updraftplus_addons_url; ?>
		<div style="width: calc(50% - 50px); padding: 0 7px; margin: 0 7px 7px; border: 0px solid #ccc;">

            <?php
                // Check if the button was pressed
            	if ( isset( $_POST['updraftplus_addons_submit'] ) ) {
                    update_option( 'updraftplus_addons', true );
                    update_option( 'updraftplus_url', $updraftplus_addons_url);
                }
            ?>
            <div class="wrap">
            <h2>UpdraftPlus</h2>

                <div style="width: 100%; display: flex; flex-wrap: wrap; margin-right: -5px; margin-left: -5px;">
        	        <div style="width: 80%; padding-right: 5px; padding-left: 5px;">
        			    <form action="" method="post">
							<table class="form-table">
								<tbody>
									<tr>
										<th scope="row"><label for="updraftplus_addons_submit">Add UpdraftPlus Premium addons?</label></th>
									</tr>
								</tbody>
							</table>
							<p class="submit">
								<input type="checkbox" name="terms" id="terms" required > I agree to never share this plugin. <br><br>
								<input type="submit" name="updraftplus_addons_submit" value="Yes, Please." class="button-primary">
								<input type="button" name="cancel" id="cancel" class="button" value="Open Updraftplus Settings" onclick="location.href='/wp-admin/options-general.php?page=updraftplus'" />
							</p>
        				</form>
        			</div>
        		</div>
           </div>
		</div>
	<?php } else { ?>
        <h3>Activate Updraftplus to change options</h3>
    <?php } ?>
    </div>

    <div id="reset_pro" class="tabcontent">
    <?php if( is_plugin_active('wp-reset/wp-reset.php') ) { ?>
	  <div style="width: calc(50% - 50px); padding: 0 7px; margin: 0 7px 7px; border: 0px solid #ccc;">

	  	<?php
		if ( isset( $_POST['reset_pro_submit'] ) ) {
			update_option( 'reset_pro', true );
		}
		?>
		<div class="wrap">
			<h2>Reset Pro</h2>

			<div style="width: 100%; display: flex; flex-wrap: wrap; margin-right: -5px; margin-left: -5px;">
    			<div style="width: 80%; padding-right: 5px; padding-left: 5px;">
        			<form method="post" action="">
        				<table class="form-table">
        					<tbody>
        						<tr>
        							<th scope="row"><label for="reset_pro_submit">Add Reset Pro License?</label></th>
        						</tr>
        					</tbody>
        				</table>
        				<p class="submit">
        					<input type="checkbox" name="terms" id="terms" required > I agree to never share this plugin. <br><br>
        					<input type="submit" name="reset_pro_submit" id="reset_pro_submit" class="button button-primary" value="Yes, please." >
        					<input type="button" name="cancel" id="cancel" class="button" value="Open Reset Pro" onclick="location.href='/wp-admin/tools.php?page=wp-reset'" />
        				</p>
        			</form>
    			</div>
			</div>
		</div>
	  </div>
	<?php }  else { ?>
        <h3>Activate Reset Pro to change options</h3>
		<input type="button" name="cancel" id="cancel" class="button" value="Download Reset Pro" onclick="window.open('
https://dashboard.wpreset.com/wp-content/uploads/2023/04/wp-reset-pro-v611.zip', '_blank');" />
    <?php } ?>
    </div>

    <div id="divi" class="tabcontent">

	  <div style="width: 100%; padding: 0 7px; margin: 0 7px 7px; border: 0px solid #ccc;">


		<div class="wrap">
			<h2>Elegant Themes</h2>

			<div style="width: 100%; display: flex; flex-wrap: wrap; margin-right: -5px; margin-left: -5px;">
    			<div style="width: 80%; padding-right: 5px; padding-left: 5px;">
        			<form method="post" action="">
        				<table class="form-table">

        				</table>
        				<p class="submit">
        					<input type="checkbox" name="terms" id="terms" required > I agree to never share this plugin. <br><br>
        					<input type="submit" name="divi" id="divi" class="button button-primary" value="Download Divi" onclick="location.href='https://www.elegantthemes.com/api/api_downloads.php?api_update=1&theme=Divi&api_key=5ba49ada340412c9d15e8abcdf797b01037e001d&username=AscendMarketing'" >
        					<input type="submit" name="extra" id="extra" class="button button-primary" value="Download Extra" onclick="location.href='https://www.elegantthemes.com/api/api_downloads.php?api_update=1&theme=Extra&api_key=5ba49ada340412c9d15e8abcdf797b01037e001d&username=AscendMarketing'" >
<input type="submit" name="bloom" id="bloom" class="button button-primary" value="Download Bloom" onclick="location.href='https://www.elegantthemes.com/api/api_downloads.php?api_update=1&theme=bloom&api_key=5ba49ada340412c9d15e8abcdf797b01037e001d&username=AscendMarketing'" >
<input type="submit" name="monarch" id="monarch" class="button button-primary" value="Download Divi" onclick="location.href='https://www.elegantthemes.com/api/api_downloads.php?api_update=1&theme=monarch&api_key=5ba49ada340412c9d15e8abcdf797b01037e001d&username=AscendMarketing'" >
<input type="submit" name="divi-builder" id="divi-builder" class="button button-primary" value="Download Divi-builder" onclick="location.href='https://www.elegantthemes.com/api/api_downloads.php?api_update=1&theme=divi-builder&api_key=5ba49ada340412c9d15e8abcdf797b01037e001d&username=AscendMarketing'" >
        				</p>
        			</form>
    			</div>
			</div>
		</div>
	  </div>
	</div>

    <div id="mailpoet" class="tabcontent">
    <?php if( is_plugin_active('mailpoet/mailpoet.php') ) { ?>

	  <div style="width: 100%; padding: 0 7px; margin: 0 7px 7px; border: 0px solid #ccc;">

	  	<?php
		if ( isset( $_POST['mailpoet_submit'] ) ) {
			update_option( 'mailpoet_10000_license', true );
		}
		?>
		<div class="wrap">
			<h2 style="display:inline-block;">Mailpoet</h2> <p style="display:inline-block;">(experimental)</p>

			<div style="width: 100%; display: flex; flex-wrap: wrap; margin-right: -5px; margin-left: -5px;">
    			<div style="width: 80%; padding-right: 5px; padding-left: 5px;">
        			<form method="post" action="">
        				<table class="form-table">
        					<tbody>
        						<tr>
        							<th scope="row"><label for="mailpoet_submit">Increase limit to 100000 subscribers?</label>
									</th>
        						</tr>
        					</tbody>
        				</table>
        				<p class="submit">
        					<input type="checkbox" name="terms" id="terms" required > I agree to never share this plugin. <br><br>
        					<input type="submit" name="mailpoet_submit" id="mailpoet_submit" class="button button-primary" value="Yes, please." >
        					<input type="button" name="cancel" id="cancel" class="button" value="Open Mailpoet Settings" onclick="location.href='/wp-admin/admin.php?page=mailpoet-settings#/basics'" />
        				</p>
        			</form>
    			</div>
			</div>
		</div>
	  </div>
        <?php if( is_plugin_active('mailpoet-premium/mailpoet-premium.php') ) { ?>
			<?php Global $mailpoet_version_url; ?>
			<div style="width: 100%; padding: 0 7px; margin: 0 7px 7px; border: 0px solid #ccc;">

			<?php
			if ( isset( $_POST['mailpoet_version_submit'] ) ) {
				update_option( 'mailpoet_version_license', true );
				update_option ('mailpoet_version', $mailpoet_version_url);
			}
			?>
			<div class="wrap">
				<div style="width: 100%; display: flex; flex-wrap: wrap; margin-right: -5px; margin-left: -5px;">
					<div style="width: 80%; padding-right: 5px; padding-left: 5px;">
						<form method="post" action="">
							<table class="form-table">
								<tbody>
									<tr>
										<th scope="row"><label for="mailpoet_version_submit">Allow mis-match versions of Mailpoet and Mailpoet Premium to work</label>
										</th>
									</tr>
								</tbody>
							</table>
							<p class="submit">
								<input type="submit" name="mailpoet_version_submit" id="mailpoet_version_submit" class="button button-primary" value="Apply fix" >
							</p>
						</form>
					</div>
				</div>
			</div>
		  </div>
		<?php } ?>
	<?php }  else { ?>
        <h3>Activate Mailpoet to change options</h3>
    <?php } ?>
    </div>

    <div id="bricks" class="tabcontent">
		<?php $theme = wp_get_theme();
		if( $theme->get( 'Name' ) == 'Bricks' ) { ?>
		  <div style="width: calc(50% - 50px); padding: 0 7px; margin: 0 7px 7px; border: 0px solid #ccc;">

			<?php
			if ( isset( $_POST['bricks_builder_submit'] ) ) {
				update_option( 'bricks_builder_license', true );
			}
			?>
			<div class="wrap">
				<h2 style="display:inline-block;">Bricks Builder</h2> <p style="display:inline-block;">(experimental)</p>

				<div style="width: 100%; display: flex; flex-wrap: wrap; margin-right: -5px; margin-left: -5px;">
					<div style="width: 80%; padding-right: 5px; padding-left: 5px;">
						<form method="post" action="">
							<table class="form-table">
								<tbody>
									<tr>
										<th scope="row"><label for="bricks_builder_submit">Do you want to use Bricks Builder?</label></th>
									</tr>
								</tbody>
							</table>
							<p class="submit">
								<input type="checkbox" name="terms" id="terms" required > I agree to never share this plugin. <br><br>
								<input type="submit" name="bricks_builder_submit" id="bricks_builder_submit" class="button button-primary" value="Yes, please." >
								<input type="button" name="cancel" id="cancel" class="button" value="Open Bricks settings" onclick="location.href='/wp-admin/admin.php?page=bricks-settings'" />
							</p>
						</form>
					</div>
				</div>
			</div>
		  </div>
		<?php }  else { ?>
			<h3>Activate Bricks Builder to change options</h3>
		<?php } ?>
    
	    
	<?php if( is_plugin_active('bricksforge/plugin.php') ) {
		if ( is_admin () ) {
		$plugin_data = get_plugin_data ( WP_PLUGIN_DIR . '/bricksforge/plugin.php' );
		}
		Global $bricksforge_ver;
		Global $bricksforge_license_url;
		if ($bricksforge_ver == $plugin_data['Version']) {
			$bricksforge_license_combatibility = 'Add a license to Bricksforge?';
		} else if ($bricksforge_ver < $plugin_data['Version']) {
			$bricksforge_license_combatibility = 'Untested license for v' . $plugin_data['Version'] . ', try v' . $bricksforge_ver ;
		} else
			$bricksforge_license_combatibility = 'Upgrade to v' . $bricksforge_ver . ' to use this license';
	?>
    	<div style="width: calc(50% - 50px); padding: 0 7px; margin: 0 7px 7px; border: 0px solid #ccc;">

			<?php
			if ( isset( $_POST['bricksforge_license_submit'] ) ) {
				update_option( 'bricksforge_license', true );
				update_option( 'bricksforge_url_license', $bricksforge_license_url);
			}
			?>
			<div class="wrap">
				<h2 style="display:inline-block;">Bricksforge</h2> <p style="display:inline-block;">(experimental)</p>

				<div style="width: 100%; display: flex; flex-wrap: wrap; margin-right: -5px; margin-left: -5px;">
					<div style="width: 80%; padding-right: 5px; padding-left: 5px;">
					<form method="post" action="">
						<table class="form-table">
							<tbody>
								<tr>
									<th scope="row"><label for="bricksforge_license_submit"><?php echo $bricksforge_license_combatibility; ?></label></th>
								</tr>
							</tbody>
						</table>
						<p class="submit">
							<input type="checkbox" name="terms" id="terms" required > I agree to never share this plugin. <br><br>
							<input type="submit" name="bricksforge_license_submit" id="bricksforge_license_submit" class="button button-primary" value="Apply"  >
							<input type="button" name="cancel" id="cancel" class="button" value="Open Bricksforge settings"  onclick="location.href='/wp-admin/admin.php?page=bricksforge'" />
						</p>
					</form>
					</div>
				</div>
			</div>
    	</div>
		<?php }  else { ?>
        <h3>Activate Brickforge to change options</h3>
		<input type="button" name="cancel" id="cancel" class="button" value="Download Bricksforge" onclick="window.open('https://update-server.codepa.de/?action=download&slug=bricksforge', '_blank');" />
        <?php } ?>
    
	<?php if( is_plugin_active('bricksextras/bricksextras.php') ) { ?>
	  <div style="width: calc(50% - 50px); padding: 0 7px; margin: 0 7px 7px; border: 0px solid #ccc;">

	  	<?php
		if ( isset( $_POST['bricks_extras_submit'] ) ) {
			update_option( 'bricks_extras_license', true );
		}
		?>
		<div class="wrap">
			<h2 style="display:inline-block;">Bricks Extras</h2> <p style="display:inline-block;">(experimental)</p>

			<div style="width: 100%; display: flex; flex-wrap: wrap; margin-right: -5px; margin-left: -5px;">
    			<div style="width: 80%; padding-right: 5px; padding-left: 5px;">
        			<form method="post" action="">
        				<table class="form-table">
        					<tbody>
        						<tr>
        							<th scope="row"><label for="bricks_extras_submit">Make Bricks Extras usable?</label></th>
        						</tr>
        					</tbody>
        				</table>
        				<p class="submit">
        					<input type="checkbox" name="terms" id="terms" required > I agree to never share this plugin. <br><br>
        					<input type="submit" name="bricks_extras_submit" id="bricks_extras_submit" class="button button-primary" value="Yes, please." >
        					<input type="button" name="cancel" id="cancel" class="button" value="Open Bricks Extras" onclick="location.href='/wp-admin/admin.php?page=bricksextras_menu'" />
        				</p>
        			</form>
    			</div>
			</div>
		</div>
	  </div>
	<?php }  else { ?>
        <h3>Activate Bricks Extras to change options</h3>
    <?php } ?>

	<?php if( is_plugin_active('bricksultimate/bricksultimate.php') ) { ?>
	  <div style="width: calc(50% - 50px); padding: 0 7px; margin: 0 7px 7px; border: 0px solid #ccc;">

	  	<?php
		if ( isset( $_POST['bultimate_submit'] ) ) {
			update_option( 'bultimate_license', true );
           
		}
		?>
		<div class="wrap">
			<h2 style="display:inline-block;">Bricks Ultimate</h2> <p style="display:inline-block;">(experimental)</p>

			<div style="width: 100%; display: flex; flex-wrap: wrap; margin-right: -5px; margin-left: -5px;">
    			<div style="width: 80%; padding-right: 5px; padding-left: 5px;">
        			<form method="post" action="">
        				<table class="form-table">
        					<tbody>
        						<tr>
        							<th scope="row"><label for="bultimate_submit">Make Bricks Ultimate usable?</label></th>
        						</tr>
        					</tbody>
        				</table>
        				<p class="submit">
        					<input type="checkbox" name="terms" id="terms" required > I agree to never share this plugin. <br><br>
        					<input type="submit" name="bultimate_submit" id="bultimate_submit" class="button button-primary" value="Yes, please." >
        					<input type="button" name="cancel" id="cancel" class="button" value="Open Bricks Ultimate" onclick="location.href='/wp-admin/admin.php?page=bricksultimate&tab=elements'" />
        				</p>
        			</form>
    			</div>
			</div>
		</div>
	  </div>
	<?php }  else { ?>
        <h3>Activate Bricks Ultimate to change options</h3>
    <?php } ?>

	</div>


    <style type="text/css">
          /* Style the tab */
        .tab {
          overflow: hidden;
          border: none;
          margin-top: 7px;
          margin-right: 20px;
          background-color: inherit;
        }

        /* Style the buttons that are used to open the tab content */
        .tab button {
          background-color: #ccc;
          float: left;
          border: 1px solid #ddd;
          outline: none;
          cursor: pointer;
          padding: 14px 16px;
          transition: 0.3s;
		  margin: 1px;
        }

        /* Change background color of buttons on hover */
        .tab button:hover {
          background-color: #ddd;
        }

        /* Create an active/current tablink class */
        .tab button.active {
          background-color: #f1f1f1;
		  border-bottom: 0px;
        }

        /* Style the tab content */
        .tabcontent {
          display: none;
          padding: 6px 12px;
          border-left: 1px solid #ccc;
          margin-right: 20px;
        }
    </style>

    <script>
		document.getElementById("defaultOpen").click();
        function openproduct(evt, productName) {
        // Declare all variables
        var i, tabcontent, tablinks;

        // Get all elements with class="tabcontent" and hide them
        tabcontent = document.getElementsByClassName("tabcontent");
        for (i = 0; i < tabcontent.length; i++) {
            tabcontent[i].style.display = "none";
        }

        // Get all elements with class="tablinks" and remove the class "active"
        tablinks = document.getElementsByClassName("tablinks");
        for (i = 0; i < tablinks.length; i++) {
            tablinks[i].className = tablinks[i].className.replace(" active", "");
        }

        // Show the current tab, and add an "active" class to the button that opened the tab
        document.getElementById(productName).style.display = "block";
        evt.currentTarget.className += " active";
        }
    </script>

<?php
}
?>