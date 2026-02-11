<?php
/**
 * Parent class used to create the admin pages.
 *
 * @package daext-interlinks-manager
 */

/**
 * Parent class used to create the admin pages.
 */
class Daextinma_Menu_Elements {

	/**
	 * The capability required to access the menu.
	 *
	 * @var string
	 */
	public $capability = null;

	/**
	 * Array with general menu data, like toolbar menu items.
	 *
	 * @var self
	 */
	public $config = null;

	/**
	 * An instance of the shared class.
	 *
	 * @var Daextinma_Shared
	 */
	public $shared = null;

	/**
	 * The menu slug.
	 *
	 * @var null
	 */
	public $menu_slug = null;

	/**
	 * The plural version of the slug.
	 *
	 * @var null
	 */
	public $slug_plural = null;

	/**
	 * The singular version of the displayed menu label.
	 *
	 * @var null
	 */
	public $label_singular = null;

	/**
	 * The plural version of the displayed menu label.
	 *
	 * @var null
	 */
	public $label_plural = null;

	/**
	 * The primary key of the database table associated with the managed back-end page.
	 *
	 * @var null
	 */
	public $primary_key = null;

	/**
	 * The name of the database table associated with the managed back-end page.
	 *
	 * @var null
	 */
	public $db_table = null;

	/**
	 * The list of columns to display in the table.
	 *
	 * @var null
	 */
	public $list_table_columns = null;

	/**
	 * The list of database table fields that can be searched using the menu search field.
	 *
	 * @var null
	 */
	public $searchable_fields = null;

	/**
	 * The instance of the class.
	 *
	 * @var null
	 */
	private static $instance = null;

	/**
	 * The constructor.
	 *
	 * @param Daextinma_Shared $shared An instance of the shared class.
	 * @param string           $page_query_param The query parameter used to identify the current page.
	 * @param array            $config The configuration array.
	 */
	public function __construct( $shared, $page_query_param, $config ) {

		// assign an instance of the plugin shared class.
		$this->shared = $shared;

		$this->config = $config;
	}

	/**
	 * Get the singleton instance of the class.
	 *
	 * @return self|null
	 */
	public static function get_instance() {

		if ( null === self::$instance ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	/**
	 * Display the header bar.
	 *
	 * @return void
	 */
	public function header_bar() {

		// phpcs:disable WordPress.Security.NonceVerification.Recommended -- Nonce not required for data visualization.
		$action  = isset( $_GET['action'] ) ? sanitize_key( $_GET['action'] ) : 'list';
		$edit_id = isset( $_GET['edit_id'] ) ? absint( $_GET['edit_id'] ) : null;
		// phpcs:enable

		if ( 'new' === $action ) {
			$page_title = __( 'Add New', 'daext-interlinks-manager' ) . ' ' . $this->label_singular;
		} elseif ( null !== $edit_id ) {
			$page_title = __( 'Edit', 'daext-interlinks-manager' ) . ' ' . $this->label_singular;
		} else {
			$page_title = $this->label_plural;
		}

		?>

		<div class="daextinma-header-bar">

			<div class="daextinma-header-bar__left">
				<div class="daextinma-header-bar__page-title"><?php echo esc_html( $page_title ); ?></div>
			</div>

			<div class="daextinma-header-bar__right">
				<?php if ( 'new' === $action || null !== $edit_id ) : ?>
					<a href="#" onclick="document.getElementById('form1').submit()" class="daextinma-btn daextinma-btn-primary"><?php esc_html_e( 'Save Changes', 'daext-interlinks-manager' ); ?></a>
				<?php endif; ?>
			</div>

		</div>

		<?php
	}

	/**
	 * Display the admin toolbar. Which is the top section of the plugin admin menus.
	 *
	 * @return void
	 */
	public function display_admin_toolbar() {

		?>

		<div class="daextinma-admin-toolbar">
			<div class="daextinma-admin-toolbar__left-section">
				<div class="daextinma-admin-toolbar__menu-items">
					<a href="<?php echo esc_url( admin_url( 'admin.php?page=daextinma-dashboard' ) ); ?>" class="daextinma-admin-toolbar__plugin-logo">
						<img src="<?php echo esc_url( $this->shared->get( 'url' ) . 'admin/assets/img/plugin-logo.svg' ); ?>" alt="Interlinks Manager" />
					</a>
					<?php

					foreach ( $this->config['admin_toolbar']['items'] as $key => $item ) {

						?>

						<a href="<?php echo esc_attr( $item['link_url'] ); ?>" class="daextinma-admin-toolbar__menu-item <?php echo 'daextinma-' . $this->menu_slug === $item['menu_slug'] ? 'is-active' : ''; ?>">
							<div class="daextinma-admin-toolbar__menu-item-wrapper">
								<?php $this->shared->echo_icon_svg( $item['icon'] ); ?>
								<div class="daextinma-admin-toolbar__menu-item-text"><?php echo esc_html( $item['link_text'] ); ?></div>
							</div>
						</a>

						<?php

					}

					?>

					<div class="daextinma-admin-toolbar__menu-item daextinma-admin-toolbar__menu-item-more">
						<div class="daextinma-admin-toolbar__menu-item-wrapper">
							<?php $this->shared->echo_icon_svg( 'grid-01' ); ?>
							<div class="daextinma-admin-toolbar__menu-item-text"><?php esc_html_e( 'More', 'daext-interlinks-manager' ); ?></div>
							<?php $this->shared->echo_icon_svg( 'chevron-down' ); ?>
						</div>
						<ul class="daextinma-admin-toolbar__pop-sub-menu">

							<?php

							foreach ( $this->config['admin_toolbar']['more_items'] as $key => $more_item ) {

								?>

								<li>
									<a href="<?php echo esc_attr( $more_item['link_url'] ); ?>" <?php echo 1 === intval( $more_item['pro_badge'], 10 ) ? 'target="_blank"' : ''; ?>>
										<?php echo '<div class="daextinma-admin-toolbar__more-item-item-text">' . esc_html( $more_item['link_text'] ) . '</div>'; ?>
										<?php

										if ( true === isset( $more_item['pro_badge'] ) && $more_item['pro_badge'] ) {
											echo '<div class="daextinma-admin-toolbar__pro-badge">' . esc_html__( 'PRO', 'daext-interlinks-manager' ) . '</div>';
										}

										?>
									</a>
								</li>

								<?php

							}

							?>

						</ul>
					</div>
				</div>
			</div>
			<div class="daextinma-admin-toolbar__right-section">
				<!-- Display the upgrade button in the Free version. -->
				<?php if ( constant( 'DAEXTINMA_EDITION' ) === 'FREE' ) : ?>
				<a href="https://daext.com/interlinks-manager/" target="_blank" class="daextinma-admin-toolbar__upgrade-button">
					<?php $this->shared->echo_icon_svg( 'diamond-01' ); ?>
					<div class="daextinma-admin-toolbar__upgrade-button-text"><?php esc_html_e( 'Unlock Extra Features with IM Pro', 'daext-interlinks-manager' ); ?></div>
				</a>
				<?php endif; ?>
				<a href="https://daext.com" target="_blank" class="daextinma-admin-toolbar__daext-logo-container">
				<img class="daextinma-admin-toolbar__daext-logo" src="<?php echo esc_url( $this->shared->get( 'url' ) . 'admin/assets/img/daext-logo.svg' ); ?>" alt="DAEXT" />
				</a>
			</div>
		</div>

		<?php
	}

	/**
	 * Display a section with that includes information on the Pro version. Note that the Pro Features section is
	 * displayed only in the free version.
	 *
	 * @return void
	 */
	public function display_pro_features() {

		if ( constant( 'DAEXTINMA_EDITION' ) !== 'FREE' ) {
			return;
		}

		?>

		<div class="daextinma-admin-body">

			<div class="daextinma-pro-features">

				<div class="daextinma-pro-features__wrapper">

					<div class="daextinma-pro-features__left">
						<div class="daextinma-pro-features__title">
							<div class="daextinma-pro-features__title-text"><?php esc_html_e( 'Unlock Advanced Features with Interlinks Manager Pro', 'daext-interlinks-manager' ); ?></div>
							<div class="daextinma-pro-features__pro-badge"><?php esc_html_e( 'PRO', 'daext-interlinks-manager' ); ?></div>
						</div>
						<div class="daextinma-pro-features__description">
							<?php
							esc_html_e(
								'Export the internal links data, receive relevant internal link suggestions, automatically create internal links based on the specified keywords, track the clicks on the internal links, find broken links, and more!',
								'daext-interlinks-manager'
							);
							?>
						</div>
						<div class="daextinma-pro-features__buttons-container">
							<a class="daextinma-pro-features__button-1" href="https://daext.com/interlinks-manager/" target="_blank">
								<div class="daextinma-pro-features__button-text">
									<?php esc_html_e( 'Learn More', 'daext-interlinks-manager' ); ?>
								</div>
								<?php $this->shared->echo_icon_svg( 'arrow-up-right' ); ?>
							</a>
							<a class="daextinma-pro-features__button-2" href="https://daext.com/interlinks-manager/#pricing" target="_blank">
								<div class="daextinma-pro-features__button-text">
									<?php esc_html_e( 'View Pricing & Upgrade', 'daext-interlinks-manager' ); ?>
								</div>
								<?php
								$this->shared->echo_icon_svg( 'arrow-up-right' );
								?>
							</a>
						</div>
					</div>
					<div class="daextinma-pro-features__right">

						<?php

						$pro_features_data_a = array(
							array(
								'icon'  => 'link-03',
								'title' => __( 'Automatic Links', 'daext-interlinks-manager' ),
							),
							array(
								'icon'  => 'bar-chart-07',
								'title' => __( 'Detailed Statistics', 'daext-interlinks-manager' ),
							),
							array(
								'icon'  => 'lightbulb-05',
								'title' => __( 'Links Suggestions', 'daext-interlinks-manager' ),
							),
							array(
								'icon'  => 'share-05',
								'title' => __( 'Exportable Data', 'daext-interlinks-manager' ),
							),
							array(
								'icon'  => 'check-circle-broken',
								'title' => __( 'Link Checker', 'daext-interlinks-manager' ),
							),
							array(
								'icon'  => 'cursor-click-02',
								'title' => __( 'Click Tracking', 'daext-interlinks-manager' ),
							),
						);

						foreach ( $pro_features_data_a as $key => $pro_feature_data ) {

							?>

							<div class="daextinma-pro-features__single-feature">
								<div class="daextinma-pro-features__single-feature-wrapper">
									<?php $this->shared->echo_icon_svg( $pro_feature_data['icon'] ); ?>
									<div class="daextinma-pro-features__single-feature-name"><?php echo esc_html( $pro_feature_data['title'] ); ?></div>
								</div>
							</div>

							<?php

						}

						?>

					</div>

				</div>

				<div class="daextinma-pro-features__footer-wrapper">
					<div class="daextinma-pro-features__footer-wrapper-inner">
						<div class="daextinma-pro-features__footer-wrapper-left">
							<?php esc_html_e( 'Built for WordPress creators by the DAEXT team', 'daext-interlinks-manager' ); ?>
						</div>
						<a class="daextinma-pro-features__footer-wrapper-right" href="https://daext.com/products/" target="_blank">
							<div class="daextinma-pro-features__footer-wrapper-right-text">
								<?php esc_html_e( 'More Tools from DAEXT', 'daext-interlinks-manager' ); ?>
							</div>
							<?php $this->shared->echo_icon_svg( 'arrow-up-right' ); ?>
						</a>
					</div>
				</div>

			</div>

		</div>

		<?php
	}

	/**
	 * Verify the provided user capability.
	 *
	 * Die with a message if the user does not have the required capability.
	 *
	 * @return void
	 */
	public function verify_user_capability() {

		if ( ! current_user_can( $this->capability ) ) {
			wp_die( esc_html__( 'You do not have sufficient permissions to access this page.', 'daext-interlinks-manager' ) );
		}
	}

	/**
	 * Displays the content of the admin menu.
	 *
	 * @return void
	 */
	public function display_menu_content() {

		// Verify user capability.
		$this->verify_user_capability();

		// Display the Admin Toolbar.
		$this->display_admin_toolbar();

		// Display the Header Bar.
		$this->header_bar();

		// Custom body content defined in the menu child class.
		$this->display_custom_content();

		// Display the Pro features section.
		$this->display_pro_features();
	}
}
