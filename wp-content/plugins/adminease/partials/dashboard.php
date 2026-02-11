<?php
// phpcs:disable WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound -- Template variables, not globals
use AdminEase\Field;
use AdminEase\Plugin;

defined( 'ABSPATH' ) || exit;

$active_tab = isset( $_GET['tab'] ) ? sanitize_key( wp_unslash( $_GET['tab'] ) ) : 'dashboard'; // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Reading GET parameter to determine active tab
$fields     = Plugin::get_plugin_fields();
$classes    = apply_filters( 'adminease_dashboard_section_classes', [ ADMINEASE_SLUG ] );
?>
<section class="<?php echo esc_attr( implode( ' ', $classes ) ); ?>">
	<header class="flex justify-content-between align-center gap-20">
		<div class="site-logo gap-10">
			<img src="<?php echo esc_url( plugin_dir_url( dirname( __FILE__ ) ) . 'assets/img/adminease-logo.png' ); // phpcs:ignore PluginCheck.CodeAnalysis.ImageFunctions.NonEnqueuedImage
			?>" alt="<?php echo esc_html__( 'AdminEase', 'adminease' ); ?>"/>
			<span class="plugin-name"><?php echo esc_html( ADMINEASE_NAME ); ?></span>
			<span class="dashicons dashicons-image-rotate-left adminease-toggle-has-sidebar" title="<?php echo esc_html__( 'Toggle menu to Sidebar', 'adminease' ); ?>" aria-label="<?php echo esc_html__( 'Toggle menu to Sidebar', 'adminease' ); ?>"></span>
		</div>
		
		<div class="tabs" data-tabs="main-tabs">
			<span class="dashicons dashicons-menu" id="adminease-menu-toggle"></span>
			
			<?php do_action( 'adminease_before_dashboard_tabs' ); ?>
			
			<nav class="tabs-nav">
				<ul>
					<?php do_action( 'adminease_dashboard_tabs_nav_start', $active_tab ); ?>
					
					<li class="tabs-nav-item">
						<a href="#tab-dashboard" class="tabs-nav-link<?php echo 'dashboard' === $active_tab ? ' active' : ''; ?>">
							<img src="<?php echo esc_url( ADMINEASE_PLUGIN_URL . 'assets/img/favicon-black.svg' ); ?>" alt="" class="tab-icon"/>
							<span class="text"><?php esc_html_e( 'Dashboard', 'adminease' ); ?></span>
						</a>
					</li>
					<?php
					foreach( $fields as $key => $group ) {
						?>
						<li class="tabs-nav-item">
							<a href="#tab-<?php echo esc_attr( $key ); ?>" class="tabs-nav-link<?php echo $key === $active_tab ? ' active' : ''; ?>">
								<?php echo wp_kses_post( $group['icon'] ); ?>
								<span class="text"><?php echo esc_html( $group['title'] ); ?></span>
							</a>
						</li>
						<?php
					}
					
					do_action( 'adminease_dashboard_tabs_nav_end', $active_tab );
					?>
				</ul>
			</nav>
			
			<?php do_action( 'adminease_after_dashboard_tabs' ); ?>
		</div>
		
		<div class="toggles">
			<span class="dashicons dashicons-image-rotate-left adminease-toggle-has-sidebar" title="<?php echo esc_html__( 'Toggle menu to Sidebar', 'adminease' ); ?>" aria-label="<?php echo esc_html__( 'Toggle menu to Sidebar', 'adminease' ); ?>"></span>
			
			<span class="dashicons dashicons-controls-back" id="adminease-minmax-sidebar" title="<?php echo esc_html__( 'Toggle menu to Sidebar', 'adminease' ); ?>" aria-label="<?php echo esc_html__( 'Toggle menu to Sidebar', 'adminease' ); ?>"></span>
		</div>
	</header>
	
	<main>
		<form class="save-settings">
			<div class="tabs" data-tabs="main-tabs">
				<?php do_action( 'adminease_dashboard_tabs_content_start', $active_tab ); ?>
				
				<div class="tab-content<?php echo 'dashboard' === $active_tab ? ' active' : ''; ?>" id="tab-dashboard">
					<div class="panel">
						<div class="panel-header">
							<div class="row">
								<div class="col-xs-12">
									<h1 class="panel-title">
										<img src="<?php echo esc_url( ADMINEASE_PLUGIN_URL . 'assets/img/favicon-black.svg' ); ?>" alt="" class="tab-icon"/>
										<?php esc_html_e( 'Dashboard', 'adminease' ); ?>
										<span class="adminease-version"><small><?php echo wp_kses_post( apply_filters( 'adminease_version_label', '(v' . esc_html( ADMINEASE_VERSION ) . ')' ) ); ?></small></span>
									</h1>
								</div>
							</div>
						</div>
					</div>
					
					<div class="row">
						<div class="col-lg-4 col-xs-12">
							<div class="panel">
								<div class="panel-header">
									<div class="row">
										<div class="col-xs-12">
											<h2 class="panel-title"><?php esc_html_e( 'Welcome to AdminEase! 🚀', 'adminease' ); ?></h2>
										</div>
									</div>
								</div>
								
								<div class="panel-content">
									<div class="row row-field">
										<div class="col-xs-12">
											<p>
												<?php
												/* translators: %1$s and %2$s are placeholders for the strong tag. */
												$translated = esc_html__( '%1$s🚀 Your WordPress admin just got a major upgrade.%2$s Explore powerful tools, boost your site, and manage everything with ease!%3$s%4$sNo coding required%5$s. Let’s make your workflow a breeze! 😎', 'adminease' );
												
												echo wp_kses_post(
													sprintf(
														$translated,
														'<strong>',
														'</strong>',
														'<br>',
														'<strong>',
														'</strong>',
													)
												);
												?>
											</p>
										</div>
									</div>
								</div>
							</div>
						</div>
						
						<div class="col-lg-4 col-xs-12">
							<div class="panel">
								<div class="panel-header">
									<div class="row">
										<div class="col-xs-12">
											<h2 class="panel-title"><?php esc_html_e( 'Core Settings', 'adminease' ); ?></h2>
										</div>
									</div>
								</div>
								
								<div class="panel-content">
									<div class="row row-field">
										<div class="col-xs-12">
											<p>
												<?php
												/* translators: %1$s and %2$s are placeholders for the strong tag. */
												$translated = esc_html__( '%1$sHeads up! 🧐%2$s Some AdminEase features tweak core WordPress settings for extra power. Double-check your choices and consider testing on a staging site first. Always keep a backup handy, better safe than sorry! 🛡️', 'adminease' );
												
												echo wp_kses_post(
													sprintf(
														$translated,
														'<strong>',
														'</strong>',
													)
												);
												?>
											</p>
										</div>
									</div>
								</div>
							</div>
						</div>
						
						<div class="col-lg-4 col-xs-12">
							<div class="panel">
								<div class="panel-header">
									<div class="row">
										<div class="col-xs-12">
											<h2 class="panel-title"><?php esc_html_e( 'Support', 'adminease' ); ?></h2>
										</div>
									</div>
								</div>
								
								<div class="panel-content">
									<div class="row row-field">
										<div class="col-xs-12">
											<p>
												<?php
												/* translators: %1$s and %2$s are placeholders for the a (href) tag. */
												$translated = esc_html__( 'Need a hand or something’s not working right? Don’t stress! 🙌 %1$sDrop your question in our %2$ssupport forum%3$s and our friendly team will jump in to help. We’ve got your back! 🤝', 'adminease' );
												
												echo wp_kses_post(
													sprintf(
														$translated,
														'<br>',
														'<a href="https://wordpress.org/support/plugin/adminease/" target="_blank">',
														'</a>'
													)
												);
												?>
											</p>
										</div>
									</div>
								</div>
							</div>
						</div>
					</div>
					
					<div class="row">
						<div class="col-lg-8 col-xs-12">
							<div class="panel">
								<div class="panel-header">
									<div class="row row middle-xs">
										<div class="col-sm-6 col-xs-12">
											<h2 class="panel-title"><?php esc_html_e( 'AdminEase Pro Features', 'adminease' ); ?></h2>
										</div>
										<?php
										if( !defined( 'ADMINEASE_PRO_VERSION' ) ) {
											?>
											<div class="col-sm-6 col-xs-12 end-sm start-xs">
												<a href="https://precisionwp.net/product/adminease" class="button button-secondary button-small" target="_blank"><?php esc_html_e( 'Upgrade to Pro', 'adminease' ); ?></a>
											</div>
											<?php
										}
										?>
									</div>
								</div>
								
								<div class="panel-content">
									<div class="row row-field">
										<div class="col-xs-12">
											<h3><?php esc_html_e( 'Pro Features', 'adminease' ); ?></h3>
											
											<ul>
												<li>
													<?php
													/* translators: %1$s and %2$s are placeholders for the strong tag. %3$s is a placeholder for the br tag */
													$translated = esc_html__( '%1$sBlock Specific Countries%2$s%3$sWhitelist IP addresses and bots.', 'adminease' );
													
													echo wp_kses_post(
														sprintf(
															$translated,
															'<strong>',
															'</strong>',
															'<br>'
														)
													);
													?>
												</li>
												
												<li>
													<?php
													/* translators: %1$s and %2$s are placeholders for the strong tag. %3$s is a placeholder for the br tag */
													$translated = esc_html__( '%1$sDisable XML-RPC%2$s%3$sAllowing specific ip addresses.', 'adminease' );
													
													echo wp_kses_post(
														sprintf(
															$translated,
															'<strong>',
															'</strong>',
															'<br>'
														)
													);
													?>
												</li>
												
												<li>
													<?php
													/* translators: %1$s and %2$s are placeholders for the strong tag. %3$s is a placeholder for the br tag */
													$translated = esc_html__( '%1$sDisable REST-API%2$s%3$sAllowing specific ip addresses.', 'adminease' );
													
													echo wp_kses_post(
														sprintf(
															$translated,
															'<strong>',
															'</strong>',
															'<br>'
														)
													);
													?>
												</li>
												
												<li>
													<?php
													/* translators: %1$s and %2$s are placeholders for the strong tag. %3$s is a placeholder for the br tag */
													$translated = esc_html__( '%1$sAuto-logout users%2$s%3$sSelecting which user roles are included in the auto logout feature.', 'adminease' );
													
													echo wp_kses_post(
														sprintf(
															$translated,
															'<strong>',
															'</strong>',
															'<br>'
														)
													);
													?>
												</li>
												
												<li>
													<?php
													/* translators: %1$s and %2$s are placeholders for the strong tag. %3$s is a placeholder for the br tag */
													$translated = esc_html__( '%1$sDrag and drop ordering%2$s%3$sSelecting which post type and taxonomies are included in the drag and drop feature.', 'adminease' );
													
													echo wp_kses_post(
														sprintf(
															$translated,
															'<strong>',
															'</strong>',
															'<br>'
														)
													);
													?>
												</li>
												
												<li>
													<?php
													/* translators: %1$s and %2$s are placeholders for the strong tag. %3$s is a placeholder for the br tag */
													$translated = esc_html__( '%1$sDisable Gutenberg%2$s%3$sAbility to disable Gutenberg editor completely per post type.', 'adminease' );
													
													echo wp_kses_post(
														sprintf(
															$translated,
															'<strong>',
															'</strong>',
															'<br>'
														)
													);
													?>
												</li>
												
												<li>
													<?php
													/* translators: %1$s and %2$s are placeholders for the strong tag. %3$s is a placeholder for the br tag */
													$translated = esc_html__( '%1$sTaxonomy Meta Box%2$s%3$sAbility to control in which taxonomies the improved meta box will appear.', 'adminease' );
													
													echo wp_kses_post(
														sprintf(
															$translated,
															'<strong>',
															'</strong>',
															'<br>'
														)
													);
													?>
												</li>
												
												<li>
													<?php
													/* translators: %1$s and %2$s are placeholders for the strong tag. %3$s is a placeholder for the br tag */
													$translated = esc_html__( '%1$sDisable Comments%2$s%3$sAbility to disable comments per post type.', 'adminease' );
													
													echo wp_kses_post(
														sprintf(
															$translated,
															'<strong>',
															'</strong>',
															'<br>'
														)
													);
													?>
												</li>
												
												<li>
													<?php
													/* translators: %1$s and %2$s are placeholders for the strong tag. %3$s is a placeholder for the br tag */
													$translated = esc_html__( '%1$sBulk Delete Posts%2$s%3$sAbility to bulk delete posts based on various criteria like post type, status, date range, categories, tags, and custom taxonomies.', 'adminease' );
													
													echo wp_kses_post(
														sprintf(
															$translated,
															'<strong>',
															'</strong>',
															'<br>'
														)
													);
													?>
												</li>
												
												<li>
													<?php
													/* translators: %1$s and %2$s are placeholders for the strong tag. %3$s is a placeholder for the br tag */
													$translated = esc_html__( '%1$sImport Export%2$s%3$sAbility to export plugin settings and import to another site.', 'adminease' );
													
													echo wp_kses_post(
														sprintf(
															$translated,
															'<strong>',
															'</strong>',
															'<br>'
														)
													);
													?>
												</li>
											</ul>
											
											<p><?php esc_html_e( 'Ready to supercharge your site? Upgrade to AdminEase Pro and unlock these exclusive features today! 🚀✨', 'adminease' ); ?></p>
										</div>
									</div>
								</div>
							</div>
						</div>
						
						<div class="col-lg-4 col-xs-12">
							<div class="panel">
								<div class="panel-header">
									<div class="row">
										<div class="col-xs-12">
											<h2 class="panel-title"><?php esc_html_e( 'Feature Request 💡', 'adminease' ); ?></h2>
										</div>
									</div>
								</div>
								
								<div class="panel-content">
									<div class="row row-field">
										<div class="col-xs-12">
											<p>
												<?php
												/* translators: %1$s is a placeholder for the br tag. %2$s is a placeholder for the opening a(href) tag. %3$s is a placeholder for the closing a(href) tag */
												$translated = esc_html__( 'Got an idea to make AdminEase even better? We’d love to hear from you! 🚀%1$s%1$s%2$sShare your feature requests%3$s and let us know what tools or improvements would make your WordPress experience smoother.', 'adminease' );
												
												echo wp_kses_post(
													sprintf(
														$translated,
														'<br>',
														'<a href="https://precisionwp.net/contact/" target="_blank">',
														'</a>'
													)
												);
												?>
											</p>
										</div>
									</div>
									
									<p class="text-muted"><?php echo wp_kses_post( __( '⭐ Enjoying AdminEase? We’d love your feedback!<br><a href="https://wordpress.org/support/plugin/adminease/reviews/#new-post" target="_blank">Leave a quick review</a>', 'adminease' ) ); ?></p>
								</div>
							</div>
						</div>
					</div>
				</div>
				<?php
				foreach( $fields as $key => $group ) {
					?>
					<div class="tab-content<?php echo $key === $active_tab ? ' active' : ''; ?>" id="tab-<?php echo esc_attr( $key ); ?>">
						<div class="panel" id="panel-<?php echo esc_attr( $key ); ?>">
							<div class="panel-header">
								<div class="row">
									<div class="col-xs-12">
										<h2 class="panel-title">
											<?php
											echo wp_kses_post( $group['icon'] );
											echo esc_html( $group['title'] );
											?>
										</h2>
									</div>
								</div>
							</div>
							
							<div class="panel-content gap-20">
								<div class="row">
									<div class="col-xs-12">
										<?php
										foreach( $group['fields'] as $field ) {
											?>
											<div class="row row-field">
												<div class="col-lg-5 col-xs-12">
													<?php
													$_field = new Field( $field );
													$_field->render();
													
													if( !empty( $field['child_fields'] ) ) {
														foreach( $field['child_fields'] as $child_field ) {
															$_field = new Field( $child_field );
															$_field->render();
														}
													}
													?>
												</div>
												
												<div class="col-lg-7 col-xs-12 field-explanation">
													<?php
													if( !empty( $field['description'] ) ) {
														printf(
															'<p class="description">%s</p>',
															wp_kses_post( $field['description'] )
														);
													}
													
													$file = ADMINEASE_DIR . 'partials/field-groups/docs/' . $field['id'] . '.php';
													
													if( file_exists( $file ) ) {
														include $file;
													}
													
													if( !empty( $field['child_fields'] ) ) {
														foreach( $field['child_fields'] as $child_field ) {
															$file = ADMINEASE_DIR . 'partials/field-groups/docs/' . $child_field['id'] . '.php';
															
															if( file_exists( $file ) ) {
																?>
																<div class="child-field-description" data-parent="<?php echo esc_attr( $child_field['attributes']['data-parent'] ?? '' ); ?>">
																	<?php include_once $file; ?>
																</div>
																<?php
															}
														}
													}
													?>
												</div>
												
												<?php do_action( 'adminease_after_field_render', $field ); ?>
											</div>
											<?php
										}
										?>
									</div>
								</div>
							</div>
						</div>
					</div>
					<?php
				}
				
				do_action( 'adminease_dashboard_tabs_content_end', $active_tab );
				?>
				<button type="submit" class="button button-primary"><?php esc_html_e( 'Save settings', 'adminease' ); ?></button>
			</div>
			
			<div class="errors"></div>
		</form>
	</main>
</section>