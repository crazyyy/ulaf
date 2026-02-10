<?php
/**
 * Admin Class
 *
 * Handles admin menu, pages, and asset enqueuing.
 *
 * @package ASENHA\FileManager
 */

namespace ASENHA\FileManager;

/**
 * Admin class
 */
class Admin {

	/**
	 * Constructor - Initialize admin hooks.
	 */
	public function __construct() {
		add_action( 'admin_menu', array( $this, 'add_admin_menu' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_assets' ) );
		add_filter( 'screen_options_show_screen', array( $this, 'hide_screen_options' ), 10, 2 );
		add_action( 'wp_ajax_asenha_fm_download', array( $this, 'ajax_download_file' ) );
	}

	/**
	 * Add admin menu under Tools.
	 *
	 * @return void
	 */
	public function add_admin_menu() {
		add_management_page(
			__( 'File Manager', 'admin-site-enhancements'),
			__( 'File Manager', 'admin-site-enhancements'),
			'manage_options',
			'asenha-file-manager',
			array( $this, 'render_admin_page' )
		);
	}

	/**
	 * Hide screen options on File Manager page.
	 *
	 * @param bool      $show_screen Whether to show Screen Options.
	 * @param WP_Screen $screen      Current WP_Screen object.
	 * @return bool Whether to show Screen Options.
	 */
	public function hide_screen_options( $show_screen, $screen ) {
		// Hide screen options on File Manager page
		if ( 'tools_page_asenha-file-manager' === $screen->id ) {
			return false;
		}
		
		return $show_screen;
	}

	/**
	 * Render the admin page.
	 *
	 * @return void
	 */
	public function render_admin_page() {
		// Check user capabilities
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die(
				esc_html__( 'You do not have sufficient permissions to access this page.', 'admin-site-enhancements')
			);
		}

		// Get ABSPATH for initial directory
		$abspath = ABSPATH;

		$disallow_file_mods = defined( 'DISALLOW_FILE_MODS' ) && DISALLOW_FILE_MODS;
		$fm_read_only       = defined( 'FM_READ_ONLY' ) && FM_READ_ONLY;
		$disallow_file_edit = defined( 'DISALLOW_FILE_EDIT' ) && DISALLOW_FILE_EDIT;

		$disallow_note_classes = 'asenha-fm-disallow-note';
		$disallow_note_message = '';

		// DISALLOW_FILE_MODS always takes precedence (note is always visible).
		if ( $disallow_file_mods ) {
			$disallow_note_message = __( 'DISALLOW_FILE_MODS is in effect. You are not allowed to modify any folder or file.', 'admin-site-enhancements' );
		} elseif ( $fm_read_only ) {
			// FM_READ_ONLY is a File Manager-specific global read-only mode.
			$disallow_note_message = __( 'FM_READ_ONLY is in effect. You are not allowed to modify any folder or file.', 'admin-site-enhancements' );
		} else {
			// For DISALLOW_FILE_EDIT, the note is toggled live by JS based on current folder.
			$disallow_note_classes .= ' is-hidden';
		}
		
		// Check if user has dismissed the warning
		$user_id = get_current_user_id();
		$dismissed = get_user_meta( $user_id, 'asenha_fm_warning_dismissed', true );

		?>
		<div class="wrap asenha-file-manager-wrap">
			<div class="asenha-fm-page-header">
				<h1><?php echo esc_html( get_admin_page_title() ); ?></h1>
				<div id="asenha-fm-disallow-note" class="<?php echo esc_attr( $disallow_note_classes ); ?>" role="note" aria-live="polite">
					<?php echo esc_html( $disallow_note_message ); ?>
				</div>
			</div>

			<!-- Warning banner -->
			<?php if ( ! $dismissed ) : ?>
			<div class="notice notice-warning is-dismissible" id="asenha-fm-warning-notice" data-dismissible="asenha-fm-warning">
				<p>
					<strong><?php esc_html_e( 'Warning:', 'admin-site-enhancements'); ?></strong>
					<?php esc_html_e( 'You have full access to your WordPress installation files. Be careful when editing or deleting files, as this can break your site.', 'admin-site-enhancements'); ?>
				</p>
			</div>
			<?php endif; ?>

			<!-- File Manager Container -->
			<div class="asenha-fm-container">
			<!-- Toolbar -->
			<div class="asenha-fm-toolbar">
				<div class="asenha-fm-toolbar-left">
					<!-- Action Buttons -->
					<div class="asenha-fm-actions">
						<button type="button" class="button" id="asenha-fm-create-file" style="display:none;">
							<?php esc_html_e( 'Create File', 'admin-site-enhancements'); ?>
						</button>
						<button type="button" class="button" id="asenha-fm-create-folder" style="display:none;">
							<?php esc_html_e( 'Create Folder', 'admin-site-enhancements'); ?>
						</button>
					<button type="button" class="button" id="asenha-fm-upload" style="display:none;">
						<?php esc_html_e( 'Upload', 'admin-site-enhancements'); ?>
					</button>
					<button type="button" class="button" id="asenha-fm-refresh">
						<?php esc_html_e( 'Refresh', 'admin-site-enhancements'); ?>
					</button>
				<button type="button" class="button asenha-fm-action-btn" id="asenha-fm-open" style="display:none;">
					<span class="asenha-fm-btn-text"><?php esc_html_e( 'Open', 'admin-site-enhancements'); ?></span>
					<span class="asenha-fm-btn-spinner spinner" style="display:none;"></span>
					<span class="asenha-fm-btn-checkmark dashicons dashicons-yes" style="display:none;"></span>
				</button>
				<button type="button" class="button asenha-fm-action-btn" id="asenha-fm-copy" style="display:none;">
					<span class="asenha-fm-btn-text"><?php esc_html_e( 'Copy', 'admin-site-enhancements'); ?></span>
					<span class="asenha-fm-btn-spinner spinner" style="display:none;"></span>
					<span class="asenha-fm-btn-checkmark dashicons dashicons-yes" style="display:none;"></span>
				</button>
				<button type="button" class="button asenha-fm-action-btn" id="asenha-fm-cut" style="display:none;">
					<span class="asenha-fm-btn-text"><?php esc_html_e( 'Cut', 'admin-site-enhancements'); ?></span>
					<span class="asenha-fm-btn-spinner spinner" style="display:none;"></span>
					<span class="asenha-fm-btn-checkmark dashicons dashicons-yes" style="display:none;"></span>
				</button>
				<button type="button" class="button" id="asenha-fm-rename" style="display:none;">
					<?php esc_html_e( 'Rename', 'admin-site-enhancements'); ?>
				</button>
				<button type="button" class="button asenha-fm-action-btn" id="asenha-fm-paste" style="display:none;">
					<span class="asenha-fm-btn-text"><?php esc_html_e( 'Paste', 'admin-site-enhancements'); ?></span>
					<span class="asenha-fm-btn-spinner spinner" style="display:none;"></span>
					<span class="asenha-fm-btn-checkmark dashicons dashicons-yes" style="display:none;"></span>
				</button>
				<button type="button" class="button asenha-fm-action-btn" id="asenha-fm-compress" style="display:none;">
					<span class="asenha-fm-btn-text"><?php esc_html_e( 'Compress', 'admin-site-enhancements'); ?></span>
					<span class="asenha-fm-btn-spinner spinner" style="display:none;"></span>
					<span class="asenha-fm-btn-checkmark dashicons dashicons-yes" style="display:none;"></span>
				</button>
				<button type="button" class="button asenha-fm-action-btn" id="asenha-fm-extract" style="display:none;">
					<span class="asenha-fm-btn-text"><?php esc_html_e( 'Extract', 'admin-site-enhancements'); ?></span>
					<span class="asenha-fm-btn-spinner spinner" style="display:none;"></span>
					<span class="asenha-fm-btn-checkmark dashicons dashicons-yes" style="display:none;"></span>
				</button>
				<button type="button" class="button asenha-fm-action-btn" id="asenha-fm-download" style="display:none;">
					<span class="asenha-fm-btn-text"><?php esc_html_e( 'Download', 'admin-site-enhancements'); ?></span>
					<span class="asenha-fm-btn-spinner spinner" style="display:none;"></span>
					<span class="asenha-fm-btn-checkmark dashicons dashicons-yes" style="display:none;"></span>
				</button>
				<button type="button" class="button asenha-fm-action-btn" id="asenha-fm-delete" style="display:none;">
					<span class="asenha-fm-btn-text"><?php esc_html_e( 'Delete', 'admin-site-enhancements'); ?></span>
					<span class="asenha-fm-btn-spinner spinner" style="display:none;"></span>
					<span class="asenha-fm-btn-checkmark dashicons dashicons-yes" style="display:none;"></span>
				</button>
				</div>
			</div>

			<div class="asenha-fm-toolbar-center">
				<!-- Empty for now, flexible for future additions -->
			</div>

		<div class="asenha-fm-toolbar-right">
			<!-- Search Box -->
			<div class="asenha-fm-search">
					<input 
						type="text" 
						id="asenha-fm-search-input" 
						class="asenha-fm-search-input" 
						placeholder="<?php esc_attr_e( 'Search...', 'admin-site-enhancements'); ?>"
					/>
					<span class="dashicons dashicons-search"></span>
				</div>
			</div>
			</div>

				<!-- Main Content Area -->
				<div class="asenha-fm-main">
				<!-- Folder Tree Panel -->
				<div class="asenha-fm-sidebar">
					<div class="asenha-fm-tree-header">
						<h3><?php esc_html_e( 'Folders', 'admin-site-enhancements'); ?></h3>
						
						<!-- Navigation Buttons Group -->
						<div class="asenha-fm-nav-buttons">
							<button type="button" class="button" id="asenha-fm-back" title="<?php esc_attr_e( 'Go back', 'admin-site-enhancements'); ?>" disabled>
								<span class="dashicons dashicons-arrow-left-alt2"></span>
							</button>
							<button type="button" class="button" id="asenha-fm-up" title="<?php esc_attr_e( 'Go to parent folder', 'admin-site-enhancements'); ?>" disabled>
								<span class="dashicons dashicons-arrow-up-alt2"></span>
							</button>
							<button type="button" class="button" id="asenha-fm-forward" title="<?php esc_attr_e( 'Go forward', 'admin-site-enhancements'); ?>" disabled>
								<span class="dashicons dashicons-arrow-right-alt2"></span>
							</button>
						</div>
					</div>
					<div class="asenha-fm-tree" id="asenha-fm-tree">
							<!-- Folder tree will be populated via JavaScript -->
							<div class="asenha-fm-loading">
								<span class="spinner is-active"></span>
								<p><?php esc_html_e( 'Loading...', 'admin-site-enhancements'); ?></p>
							</div>
						</div>
					</div>

					<!-- File List Panel -->
					<div class="asenha-fm-content">
					<!-- Breadcrumb -->
					<div class="asenha-fm-breadcrumb" id="asenha-fm-breadcrumb">
						<!-- Breadcrumb will be populated via JavaScript -->
					</div>

					<!-- Inline message area -->
					<div id="asenha-fm-inline-message" class="asenha-fm-inline-message" style="display:none;"></div>

					<!-- File List Table -->
					<div class="asenha-fm-list-container">
							<table class="asenha-fm-list wp-list-table widefat fixed striped">
								<thead>
									<tr>
										<th class="check-column">
											<input type="checkbox" id="asenha-fm-select-all" />
										</th>
										<th class="column-name sortable" data-sort="name">
											<a href="#">
												<span><?php esc_html_e( 'Name', 'admin-site-enhancements'); ?></span>
												<span class="sorting-indicator"></span>
											</a>
										</th>
										<th class="column-permissions sortable" data-sort="permissions">
											<a href="#">
												<span><?php esc_html_e( 'Permissions', 'admin-site-enhancements'); ?></span>
												<span class="sorting-indicator"></span>
											</a>
										</th>
										<th class="column-size sortable" data-sort="size">
											<a href="#">
												<span><?php esc_html_e( 'Size', 'admin-site-enhancements'); ?></span>
												<span class="sorting-indicator"></span>
											</a>
										</th>
										<th class="column-modified sortable" data-sort="modified">
											<a href="#">
												<span><?php esc_html_e( 'Last Modified', 'admin-site-enhancements'); ?></span>
												<span class="sorting-indicator"></span>
											</a>
										</th>
									</tr>
								</thead>
								<tbody id="asenha-fm-file-list">
									<!-- File list will be populated via JavaScript -->
									<tr class="asenha-fm-loading-row">
										<td colspan="5" style="text-align: center;">
											<span class="spinner is-active"></span>
											<p><?php esc_html_e( 'Loading files...', 'admin-site-enhancements'); ?></p>
										</td>
									</tr>
								</tbody>
							</table>
						</div>
					</div>
				</div>
			</div>

			<!-- Context Menu (hidden by default) -->
			<div class="asenha-fm-context-menu" id="asenha-fm-context-menu" style="display: none;">
				<ul>
					<li data-action="open">
						<?php esc_html_e( 'Open', 'admin-site-enhancements'); ?>
					</li>
					<li data-action="copy">
						<?php esc_html_e( 'Copy', 'admin-site-enhancements'); ?>
					</li>
				<li data-action="cut">
					<?php esc_html_e( 'Cut', 'admin-site-enhancements'); ?>
				</li>
				<li data-action="paste">
					<?php esc_html_e( 'Paste', 'admin-site-enhancements'); ?>
				</li>
				<li data-action="rename">
					<?php esc_html_e( 'Rename', 'admin-site-enhancements'); ?>
				</li>
					<li data-action="permissions">
						<?php esc_html_e( 'Edit Permissions', 'admin-site-enhancements'); ?>
					</li>
				<li data-action="compress">
					<?php esc_html_e( 'Compress', 'admin-site-enhancements'); ?>
				</li>
				<li data-action="extract">
					<?php esc_html_e( 'Extract', 'admin-site-enhancements'); ?>
				</li>
				<li data-action="download">
					<?php esc_html_e( 'Download', 'admin-site-enhancements'); ?>
				</li>
				<li data-action="delete" class="danger">
					<?php esc_html_e( 'Delete', 'admin-site-enhancements'); ?>
				</li>
				</ul>
			</div>

			<!-- Hidden file upload input -->
			<input type="file" id="asenha-fm-file-input" multiple style="display: none;" />
			
			<!-- Upload Modal -->
			<div class="asenha-fm-upload-modal" id="asenha-fm-upload-modal" style="display: none;">
				<div class="asenha-fm-upload-overlay"></div>
				<div class="asenha-fm-upload-content">
					<div class="asenha-fm-upload-header">
						<h2><?php esc_html_e( 'Upload Files', 'admin-site-enhancements'); ?></h2>
						<button type="button" class="asenha-fm-upload-close">
							<span class="dashicons dashicons-no"></span>
						</button>
					</div>
					<div class="asenha-fm-upload-body">
						<div class="asenha-fm-upload-dropzone" id="asenha-fm-upload-dropzone">
							<span class="dashicons dashicons-upload"></span>
							<p class="asenha-fm-upload-instructions">
								<?php esc_html_e( 'Drop files to upload', 'admin-site-enhancements'); ?>
							</p>
							<p class="asenha-fm-upload-or"><?php esc_html_e( 'or', 'admin-site-enhancements'); ?></p>
							<button type="button" class="button button-primary button-large" id="asenha-fm-select-files">
								<?php esc_html_e( 'Select Files', 'admin-site-enhancements'); ?>
							</button>
							<input type="file" id="asenha-fm-upload-input" multiple style="display: none;" />
						</div>
						<div class="asenha-fm-upload-progress" id="asenha-fm-upload-progress" style="display: none;">
							<div class="asenha-fm-upload-progress-bar">
								<div class="asenha-fm-upload-progress-fill" id="asenha-fm-upload-progress-fill"></div>
							</div>
							<p class="asenha-fm-upload-status" id="asenha-fm-upload-status">
								<?php esc_html_e( 'Uploading...', 'admin-site-enhancements'); ?>
							</p>
						</div>
						<div id="asenha-fm-upload-error" class="asenha-fm-upload-error" style="display: none;"></div>
					</div>
				</div>
			</div>

			<!-- Modals -->
			<!-- Rename Modal -->
			<div class="asenha-fm-modal" id="asenha-fm-rename-modal" style="display: none;">
				<div class="asenha-fm-modal-overlay"></div>
				<div class="asenha-fm-modal-content">
					<div class="asenha-fm-modal-header">
						<h2><?php esc_html_e( 'Rename', 'admin-site-enhancements'); ?></h2>
						<button type="button" class="asenha-fm-modal-close">
							<span class="dashicons dashicons-no"></span>
						</button>
					</div>
					<div class="asenha-fm-modal-body">
						<label for="asenha-fm-rename-input">
							<?php esc_html_e( 'New name:', 'admin-site-enhancements'); ?>
						</label>
						<input type="text" id="asenha-fm-rename-input" class="widefat" />
					</div>
				<div class="asenha-fm-modal-footer">
					<button type="button" class="button button-secondary asenha-fm-modal-close">
						<?php esc_html_e( 'Cancel', 'admin-site-enhancements'); ?>
					</button>
					<span class="asenha-fm-rename-spinner spinner" style="display:none; float:none; margin-right: 8px;"></span>
					<button type="button" class="button button-primary" id="asenha-fm-rename-confirm">
						<span class="asenha-fm-rename-text"><?php esc_html_e( 'Rename', 'admin-site-enhancements'); ?></span>
						<span class="asenha-fm-rename-checkmark dashicons dashicons-yes" style="display:none; margin-left: 6px; color: #00a32a;"></span>
					</button>
				</div>
				</div>
			</div>

			<!-- Permissions Modal -->
			<div class="asenha-fm-modal" id="asenha-fm-permissions-modal" style="display: none;">
				<div class="asenha-fm-modal-overlay"></div>
				<div class="asenha-fm-modal-content">
					<div class="asenha-fm-modal-header">
						<h2><?php esc_html_e( 'Change Permissions', 'admin-site-enhancements'); ?></h2>
						<button type="button" class="asenha-fm-modal-close">
							<span class="dashicons dashicons-no"></span>
						</button>
					</div>
					<div class="asenha-fm-modal-body">
						<label for="asenha-fm-permissions-input">
							<?php esc_html_e( 'Permissions (octal):', 'admin-site-enhancements'); ?>
						</label>
						<input type="text" id="asenha-fm-permissions-input" class="widefat" placeholder="755" pattern="[0-7]{3}" />
						<p class="description">
							<?php esc_html_e( 'Common: 644 (files), 755 (folders)', 'admin-site-enhancements'); ?>
						</p>
						<div id="asenha-fm-permissions-error" class="asenha-fm-modal-error" style="display: none;"></div>
					</div>
					<div class="asenha-fm-modal-footer">
						<button type="button" class="button button-secondary asenha-fm-modal-close">
							<?php esc_html_e( 'Cancel', 'admin-site-enhancements'); ?>
						</button>
						<button type="button" class="button button-primary" id="asenha-fm-permissions-confirm">
							<?php esc_html_e( 'Save', 'admin-site-enhancements'); ?>
						</button>
					</div>
				</div>
			</div>

			<!-- Code Editor Modal -->
			<div class="asenha-fm-modal asenha-fm-modal-fullscreen" id="asenha-fm-editor-modal" style="display: none;">
				<div class="asenha-fm-modal-overlay"></div>
				<div class="asenha-fm-modal-content">
					<div class="asenha-fm-modal-header">
						<h2 id="asenha-fm-editor-title">
							<span id="asenha-fm-editor-title-text"><?php esc_html_e( 'Edit File:', 'admin-site-enhancements'); ?></span> 
							<span id="asenha-fm-editor-file-name"></span>
						</h2>
						<button type="button" class="asenha-fm-modal-close">
							<span class="dashicons dashicons-no"></span>
						</button>
					</div>
			<div class="asenha-fm-modal-body">
				<textarea id="asenha-fm-editor-textarea"></textarea>
			</div>
		<div class="asenha-fm-modal-footer">
			<div id="asenha-fm-editor-message" class="asenha-fm-editor-message" style="display:none;"></div>
			<div class="notice notice-warning inline" id="asenha-fm-php-warning" style="display:none;">
				<p>
					<span class="dashicons dashicons-warning"></span>
					<?php esc_html_e( 'Editing PHP files may break your site. Make sure you know what you are doing.', 'admin-site-enhancements'); ?>
				</p>
			</div>
			<div class="asenha-fm-editor-footer-buttons">
				<button type="button" class="button button-secondary asenha-fm-modal-close">
					<?php esc_html_e( 'Close', 'admin-site-enhancements'); ?>
				</button>
				<span class="asenha-fm-editor-save-spinner spinner" style="display:none; float:none; margin-right: 8px;"></span>
				<button type="button" class="button button-primary" id="asenha-fm-editor-save">
					<span class="asenha-fm-editor-save-text"><?php esc_html_e( 'Save File', 'admin-site-enhancements'); ?></span>
				</button>
			</div>
		</div>
				</div>
			</div>

		</div>
		<?php
	}

	/**
	 * Enqueue admin assets.
	 *
	 * @param string $hook Current admin page hook.
	 * @return void
	 */
	public function enqueue_assets( $hook ) {
		// Only load on our admin page
		if ( 'tools_page_asenha-file-manager' !== $hook ) {
			return;
		}

		// Enqueue CSS
		wp_enqueue_style(
			'asenha-file-manager-admin',
			ASENHA_FILE_MANAGER_URL . 'assets/css/admin.css',
			array(),
			ASENHA_FILE_MANAGER_VERSION
		);

		// Enqueue WordPress's built-in CodeMirror
		// WordPress includes CodeMirror since version 4.9
		wp_enqueue_code_editor( array( 'type' => 'application/x-httpd-php' ) );
		
		// Enqueue CodeMirror integration
		wp_enqueue_script(
			'asenha-file-manager-codemirror',
			ASENHA_FILE_MANAGER_URL . 'assets/js/codemirror-integration.js',
			array( 'jquery', 'code-editor' ),
			ASENHA_FILE_MANAGER_VERSION,
			true
		);

		// Enqueue WordPress common scripts for dismissible notices
		wp_enqueue_script( 'common' );
		
		// Enqueue main JavaScript
		wp_enqueue_script(
			'asenha-file-manager-admin',
			ASENHA_FILE_MANAGER_URL . 'assets/js/file-manager.js',
			array( 'jquery', 'wp-api' ),
			ASENHA_FILE_MANAGER_VERSION,
			true
		);

		// Localize script with data
		wp_localize_script(
			'asenha-file-manager-admin',
			'asenhafm',
			array(
				'ajaxUrl'            => admin_url( 'admin-ajax.php' ),
				'restUrl'            => rest_url( 'asenha-file-manager/v1/' ),
				'nonce'              => wp_create_nonce( 'wp_rest' ),
				'downloadNonce'      => wp_create_nonce( 'asenha_fm_download' ),
				'abspath'            => ABSPATH,
				'pluginsDir'         => wp_normalize_path( WP_PLUGIN_DIR ),
				'muPluginsDir'       => wp_normalize_path( WPMU_PLUGIN_DIR ),
				'themesDir'          => wp_normalize_path( get_theme_root() ),
				'disallowFileMods'   => defined( 'DISALLOW_FILE_MODS' ) && DISALLOW_FILE_MODS,
				'fmReadOnly'         => defined( 'FM_READ_ONLY' ) && FM_READ_ONLY,
				'disallowFileEdit'   => defined( 'DISALLOW_FILE_EDIT' ) && DISALLOW_FILE_EDIT,
				'i18n'               => array(
					'confirmDelete'      => __( 'Are you sure you want to delete this item?', 'admin-site-enhancements'),
					'confirmDeleteMulti' => __( 'Are you sure you want to delete these items?', 'admin-site-enhancements'),
					'error'              => __( 'An error occurred. Please try again.', 'admin-site-enhancements'),
					'success'            => __( 'Operation completed successfully.', 'admin-site-enhancements'),
					'noItemsSelected'    => __( 'Please select at least one item.', 'admin-site-enhancements'),
					'enterFileName'      => __( 'Enter file name:', 'admin-site-enhancements'),
					'enterFolderName'    => __( 'Enter folder name:', 'admin-site-enhancements'),
					'enterNewName'       => __( 'Enter new name:', 'admin-site-enhancements'),
					'loading'            => __( 'Loading...', 'admin-site-enhancements'),
					'fileTypeNotEditable' => __( 'This file type cannot be edited in the text editor. Only text-based files (PHP, JS, CSS, HTML, TXT, etc.) can be edited.', 'admin-site-enhancements'),
					'viewFile'           => __( 'View File:', 'admin-site-enhancements'),
					'editFile'           => __( 'Edit File:', 'admin-site-enhancements'),
					'saving'             => __( 'Saving...', 'admin-site-enhancements'),
					'saveFile'           => __( 'Save File', 'admin-site-enhancements'),
					'selectOneToRename'  => __( 'Please select exactly one item to rename', 'admin-site-enhancements'),
					'renaming'           => __( 'Renaming', 'admin-site-enhancements'),
					'renamed'            => __( 'Renamed', 'admin-site-enhancements'),
					'rename'             => __( 'Rename', 'admin-site-enhancements'),
					'selectOneItem'      => __( 'Please select exactly one item', 'admin-site-enhancements'),
					'selectSingleZip'    => __( 'Please select a single ZIP file', 'admin-site-enhancements'),
					'selectSingleFile'   => __( 'Please select a single file', 'admin-site-enhancements'),
					/* translators: %d: number of files */
					'uploadingFiles'     => __( 'Uploading %d file(s)...', 'admin-site-enhancements'),
					'uploadErrors'       => __( 'Upload completed with errors', 'admin-site-enhancements'),
					'uploadComplete'     => __( 'Upload complete!', 'admin-site-enhancements'),
					'uploadFailed'       => __( 'Upload failed', 'admin-site-enhancements'),
					'uploadErrorsPrefix' => __( 'Some files were uploaded successfully, but the following had errors:', 'admin-site-enhancements'),
					'uploadFailedPrefix' => __( 'Upload failed:', 'admin-site-enhancements'),
					'disallowFileModsNote' => __( 'DISALLOW_FILE_MODS is in effect. You are not allowed to modify any folder or file.', 'admin-site-enhancements' ),
					'fmReadOnlyNote'       => __( 'FM_READ_ONLY is in effect. You are not allowed to modify any folder or file.', 'admin-site-enhancements' ),
					'disallowFileEditNote' => __( 'DISALLOW_FILE_EDIT is in effect. You are not allowed to modify theme or plugin files.', 'admin-site-enhancements' ),
					'cannotPasteProtectedFolder' => __( 'Pasting into WordPress core folders is not allowed.', 'admin-site-enhancements' ),
					'cannotModifyProtectedCoreFolder' => __( 'Modifying WordPress core folders is not allowed.', 'admin-site-enhancements' ),
				),
			)
		);
	}

	/**
	 * AJAX handler: force-download a file.
	 *
	 * This is intentionally implemented via admin-ajax (not REST) so a simple
	 * browser navigation / link click can initiate a file download without
	 * needing custom REST auth headers.
	 *
	 * @return void
	 */
	public function ajax_download_file() {
		// Capability check.
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'You do not have sufficient permissions to access this page.', 'admin-site-enhancements' ), 403 );
		}

		// Nonce check.
		check_ajax_referer( 'asenha_fm_download' );

		$path = isset( $_GET['path'] ) ? wp_unslash( $_GET['path'] ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		$path = is_string( $path ) ? $path : '';

		if ( '' === $path ) {
			wp_die( esc_html__( 'Missing file path.', 'admin-site-enhancements' ), 400 );
		}

		$validated_path = File_Operations::validate_path( $path );
		if ( is_wp_error( $validated_path ) ) {
			wp_die( esc_html( $validated_path->get_error_message() ), 400 );
		}

		if ( ! file_exists( $validated_path ) || ! is_file( $validated_path ) ) {
			wp_die( esc_html__( 'File not found.', 'admin-site-enhancements' ), 404 );
		}

		if ( ! is_readable( $validated_path ) ) {
			wp_die( esc_html__( 'The file is not readable.', 'admin-site-enhancements' ), 403 );
		}

		$filename = wp_basename( $validated_path );
		$mime_type = 'application/octet-stream';
		if ( function_exists( 'mime_content_type' ) ) {
			$detected = @mime_content_type( $validated_path ); // phpcs:ignore WordPress.PHP.NoSilencedErrors.Discouraged
			if ( is_string( $detected ) && '' !== $detected ) {
				$mime_type = $detected;
			}
		}

		$file_size = filesize( $validated_path );
		if ( false === $file_size ) {
			$file_size = null;
		}

		// Clean any existing output buffers to prevent corrupt downloads.
		while ( ob_get_level() ) {
			ob_end_clean();
		}

		nocache_headers();
		header( 'X-Content-Type-Options: nosniff' );
		header( 'Content-Description: File Transfer' );
		header( 'Content-Type: ' . $mime_type );
		header(
			'Content-Disposition: attachment; filename="' . str_replace( '"', '', $filename ) . '"; filename*=UTF-8\'\'' . rawurlencode( $filename )
		);
		header( 'Content-Transfer-Encoding: binary' );
		if ( null !== $file_size ) {
			header( 'Content-Length: ' . (string) $file_size );
		}

		$handle = fopen( $validated_path, 'rb' ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_fopen
		if ( false === $handle ) {
			wp_die( esc_html__( 'Failed to open the file for download.', 'admin-site-enhancements' ), 500 );
		}

		// Stream the file in chunks to avoid memory issues.
		$chunk_size = 1024 * 1024; // 1MB.
		while ( ! feof( $handle ) ) { // phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_feof
			// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
			echo fread( $handle, $chunk_size ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_fread
			if ( function_exists( 'flush' ) ) {
				flush();
			}
		}

		fclose( $handle ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_fclose
		exit;
	}
}

