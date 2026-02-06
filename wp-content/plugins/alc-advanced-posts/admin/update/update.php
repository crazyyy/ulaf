<?php

// Do not allow directly accessing this file.
if ( !defined('ABSPATH') ) {
	exit('Direct script access denied.');
}

class DFWPTheme {
	public $plugin_file=__FILE__;
	public $responseObj;
	public $licenseMessage;
	public $showMessage=false;
	public $slug="alchemists";

	function __construct() {

		$licenseKey=get_option("DF_WPTheme_lic_Key","");
		$liceEmail=get_option( "DF_WPTheme_lic_email","");
		$templateDir=get_template_directory(); //or dirname(__FILE__);

		if ( DFWPThemeActivationBase::CheckWPPlugin($licenseKey,$liceEmail,$this->licenseMessage,$this->responseObj, $templateDir . '/style.css' ) ){

			// Activated
			add_action( 'admin_menu', [$this,'ActiveAdminMenu'],99999);
			add_action( 'admin_post_DFWPTheme_el_deactivate_license', [ $this, 'action_deactivate_license' ] );
			//$this->licenselMessage=$this->mess;
			//***Write you plugin's code here***

			// Add class to admin body
			add_filter( 'alchemists_is_theme_activated', '__return_true' );

			// Enable themes auto-update UI elements.
			add_filter( 'themes_auto_update_enabled', '__return_true' );

		} else {

			// Not Activated
			if (!empty($licenseKey) && !empty($this->licenseMessage)){
				$this->showMessage=true;
			}
			update_option("DF_WPTheme_lic_Key","") || add_option("DF_WPTheme_lic_Key","");
			add_action( 'admin_post_DFWPTheme_el_activate_license', [ $this, 'action_activate_license' ] );
			add_action( 'admin_menu', [$this,'InactiveMenu']);

			// Disable themes auto-update UI elements.
			add_filter( 'themes_auto_update_enabled', '__return_false' );

			function df_admin_notice__error() {
				$class = 'notice notice-warning notice--noregistered';
				$reg_url = admin_url( 'admin.php?page=alchemists' );

				echo '<div class="notice notice-warning is-dismissible notice--noregistered"><p>';
				printf(
					esc_html__( 'Thank you for your purchase! It is necessary to activate your license for using the theme. %1$s', 'alchemists' ),
					sprintf(
						'<a href="%s">%s</a>',
						$reg_url,
						esc_html__( 'Activate now', 'alchemists' )
					)
				);
				echo '</p></div>';
			}
			add_action( 'admin_notices', 'df_admin_notice__error' );

			// Remove Theme Options
			function df_remove_toolbar_theme_options() {
				global $wp_admin_bar;
				$wp_admin_bar->remove_menu('_options');
			}
			add_action( 'wp_before_admin_bar_render', 'df_remove_toolbar_theme_options', 999 );

			// Remove Theme Options Submenu
			function df_remove_theme_options_submenu() {
				remove_menu_page( '_options' );
				remove_submenu_page( 'themes.php', 'pt-one-click-demo-import' );
			}
			add_action( 'admin_menu', 'df_remove_theme_options_submenu', 999 );

			// Remove Customizer
			function df_remove_customizer() {
				require_once get_template_directory() . '/admin/update/wp-crap.php';
			}
			add_action( 'after_setup_theme', 'df_remove_customizer' );

		}
	}

	function ActiveAdminMenu(){
		add_menu_page( "License Info", "License Info", "activate_plugins", $this->slug, [$this,"Activated"], " dashicons-admin-network ", 2 );
	}

	function InactiveMenu() {
		add_menu_page( "Theme Activation", "Activation", 'activate_plugins', $this->slug,  [$this,"LicenseForm"], " dashicons-admin-network ", 2 );
	}

	function action_activate_license(){
		check_admin_referer( 'el-license' );
		$licenseKey=!empty($_POST['el_license_key'])?$_POST['el_license_key']:"";
		$licenseEmail=!empty($_POST['el_license_email'])?$_POST['el_license_email']:"";
		update_option("DF_WPTheme_lic_Key",$licenseKey) || add_option("DF_WPTheme_lic_Key",$licenseKey);
		update_option("DF_WPTheme_lic_email",$licenseEmail) || add_option("DF_WPTheme_lic_email",$licenseEmail);
		update_option('_site_transient_update_themes','');
		wp_safe_redirect(admin_url( 'admin.php?page='.$this->slug));
	}

	function action_deactivate_license() {
		check_admin_referer( 'el-license' );
		$message="";
		if ( DFWPThemeActivationBase::RemoveLicenseKey(__FILE__,$message) ) {
			update_option("DF_WPTheme_lic_Key","") || add_option("DF_WPTheme_lic_Key","");
			update_option('_site_transient_update_themes','');
		}
		wp_safe_redirect(admin_url( 'admin.php?page='.$this->slug));
	}

	// Activated
	function Activated(){
		$theme         = df_get_theme_info();
		$theme_name    = $theme['name'];
		$theme_version = $theme['v'];
		$theme_slug    = $theme['slug'];
	?>
		<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
			<input type="hidden" name="action" value="DFWPTheme_el_deactivate_license"/>

			<div class="wrap about-wrap df-about-wrap">

				<div class="clearfix">
					<h1><?php printf( esc_html__('%s is activated!', $theme_slug), $theme_name ); ?></h1>
					<div class="about-text">
						<?php printf( esc_html__( '%s is now activated and ready to use! Get ready to build something beautiful. Now you can get automatic theme updates, import sample data demos and customize the theme. Check additional information about your license below. We hope you enjoy using the theme!', $theme_slug ), $theme_name ); ?>
					</div>
					<div class="wp-badge wp-badge--theme"><?php esc_html_e( 'Version', $theme_slug ); ?> <?php echo esc_html( $theme_version ); ?></div>
				</div>

				<div class="welcome-panel welcome-panel--df welcome-panel--df-activated">

					<div class="el-license-container">
						<ul class="el-license-info">
							<li>
								<div>
									<span class="el-license-info-title"><?php _e("Status",$this->slug);?></span>

									<?php if ( $this->responseObj->is_valid ) : ?>
										<span class="el-license-valid"><?php _e("Valid",$this->slug);?></span>
									<?php else : ?>
										<span class="el-license-valid"><?php _e("Invalid",$this->slug);?></span>
									<?php endif; ?>
								</div>
							</li>

							<li>
								<div>
									<span class="el-license-info-title"><?php _e("License Type",$this->slug);?></span>
									<?php echo $this->responseObj->license_title; ?>
								</div>
							</li>

							<li>
								<div>
									<span class="el-license-info-title"><?php _e("License Expired on",$this->slug);?></span>
									<?php echo $this->responseObj->expire_date;
									if(!empty($this->responseObj->expire_renew_link)){
											?>
											<a target="_blank" class="el-blue-btn" href="<?php echo $this->responseObj->expire_renew_link; ?>">Renew</a>
											<?php
									}
									?>
								</div>
							</li>

							<li>
								<div>
									<span class="el-license-info-title"><?php _e("Support Expired on",$this->slug);?></span>
									<?php
									echo $this->responseObj->support_end;
									if(!empty($this->responseObj->support_renew_link)){
										?>
										<a target="_blank" class="el-blue-btn" href="<?php echo $this->responseObj->support_renew_link; ?>">Renew</a>
										<?php
									}
									?>
								</div>
							</li>

							<li>
								<div>
									<span class="el-license-info-title"><?php _e("Your License Key",$this->slug);?></span>
									<span class="el-license-key"><?php echo esc_attr( substr($this->responseObj->license_key,0,9)."XXXXXXXX-XXXXXXXX".substr($this->responseObj->license_key,-9) ); ?></span>
								</div>
							</li>
						</ul>

						<div class="el-license-active-btn">
							<?php wp_nonce_field( 'el-license' ); ?>
							<?php submit_button('Deactivate', 'button button-secondary button-hero button--df-activation'); ?>
						</div>

					</div>


				</div>

			</div>

		</form>
	<?php
	}

	// Not activated
	function LicenseForm() {
		$theme         = df_get_theme_info();
		$theme_name    = $theme['name'];
		$theme_version = $theme['v'];
		$theme_slug    = $theme['slug'];

		?>
		<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
			<input type="hidden" name="action" value="DFWPTheme_el_activate_license"/>

			<div class="wrap about-wrap df-about-wrap">

				<div class="clearfix">
					<h1><?php printf( esc_html__('Welcome to %s!', $theme_slug), $theme_name ); ?></h1>
					<div class="about-text">
						<?php printf( esc_html__( '%s is now successfully installed! Please register your purchase to get automatic theme updates, import sample data demos and customize the theme. Read below for additional information about theme activation process.', $theme_slug ), $theme_name ); ?>
					</div>
					<div class="wp-badge wp-badge--theme"><?php esc_html_e( 'Version', $theme_slug ); ?> <?php echo esc_html( $theme_version ); ?></div>
				</div>

				<div class="welcome-panel welcome-panel--df">

					<div class="el-license-container">
						<?php if ( !empty($this->showMessage) && !empty($this->licenseMessage) ) : ?>
						<div class="notice notice-error is-dismissible">
							<p><?php echo $this->licenseMessage; ?></p>
						</div>
						<?php endif; ?>
						<p class="welcome-panel__lead"><?php printf( esc_html__( 'Thank you for choosing %s! Please enter your Purchase Code and Email Address (optional) to activate the theme and import sample data, enable theme auto updates, plugins, and customize the theme. The instructions below must be followed exactly.', $theme_slug ), $theme_name); ?></p>
						<div class="el-license-field">
							<label for="el_license_key"><strong><?php esc_html_e( 'Purchase code *', $theme_slug);?></strong></label>
							<input type="text" class="regular-text code" name="el_license_key" size="50" placeholder="xxxxxxxx-xxxxxxxx-xxxxxxxx-xxxxxxxx" required="required">
						</div>
						<div class="el-license-field">
							<label for="el_license_key"><strong><?php esc_html_e( 'Email Address (optional)', $theme_slug ); ?></strong></label>
							<?php $purchaseEmail = get_option( "DF_WPTheme_lic_email", get_bloginfo( 'admin_email' )); ?>
							<input type="text" class="regular-text code" name="el_license_email" size="50" value="<?php echo $purchaseEmail; ?>" placeholder="">
						</div>
						<div class="el-license-active-btn">
							<?php wp_nonce_field( 'el-license' ); ?>
							<?php submit_button('Activate', 'button button-primary button-hero button--df-activation'); ?>
						</div>
					</div>

					<hr>
					<h3><?php esc_html_e( 'How to find your purchase code', $theme_slug ); ?></h3>
					<ol>
						<li><?php esc_html_e( 'Log into your Envato Market account.', $theme_slug ); ?></li>
						<li><?php esc_html_e( 'Hover the mouse over your username at the top of the screen.', $theme_slug ); ?></li>
						<li><?php esc_html_e( 'Click ‘Downloads’ from the drop-down menu.', $theme_slug ); ?></li>
						<li><?php esc_html_e( 'Click ‘License certificate & purchase code’ (available as PDF or text file).', $theme_slug ); ?></li>
					</ol>
					<p>
						<?php
						$video_url   = 'https://www.youtube.com/watch?v=srghr25uBgc';
						$article_url = 'https://help.market.envato.com/hc/en-us/articles/202822600-Where-Is-My-Purchase-Code-';
						printf(
							esc_html__( 'Watch %1$s or check %2$s for more information.', $theme_slug ),
							sprintf(
								'<a href="%s">%s</a>',
								$video_url,
								esc_html__( 'this short video', $theme_slug )
							),
							sprintf(
								'<a href="%s">%s</a>',
								$article_url,
								esc_html__( 'this article', $theme_slug )
							)
						);
						?>
					</p>
				</div>

			</div>
		</form>
	<?php
	}
}

new DFWPTheme();
