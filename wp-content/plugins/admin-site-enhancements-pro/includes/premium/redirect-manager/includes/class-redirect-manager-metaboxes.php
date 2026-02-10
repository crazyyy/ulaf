<?php
/**
 * Redirect Manager Metaboxes
 *
 * Handles metaboxes for the redirect edit screen
 *
 * @since 8.1.0
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Class for managing redirect metaboxes
 */
class ASENHA_Redirect_Manager_Metaboxes {

	/**
	 * Initialize the class
	 *
	 * @since 8.1.0
	 */
	public function init() {
		add_action( 'add_meta_boxes', array( $this, 'add_meta_boxes' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_assets' ) );
	}

	/**
	 * Add metaboxes
	 *
	 * @since 8.1.0
	 */
	public function add_meta_boxes() {
		add_meta_box(
			'asenha_redirect_configuration',
			__( 'Redirect Configuration', 'admin-site-enhancements' ),
			array( $this, 'render_configuration_metabox' ),
			'asenha_redirect',
			'normal',
			'high'
		);
		
		add_meta_box(
			'asenha_redirect_tips',
			__( 'Tips', 'admin-site-enhancements' ),
			array( $this, 'render_tips_metabox' ),
			'asenha_redirect',
			'normal',
			'default'
		);
	}

	/**
	 * Render redirect configuration metabox
	 *
	 * @since 8.1.0
	 * @param WP_Post $post The post object
	 */
	public function render_configuration_metabox( $post ) {
		// Get current values
		$redirect_from = get_post_meta( $post->ID, '_redirect_from', true );
		$redirect_to = get_post_meta( $post->ID, '_redirect_to', true );
		$status_code = get_post_meta( $post->ID, '_redirect_http_status_code', true ) ?: 302;
		$message = get_post_meta( $post->ID, '_redirect_message', true );
		$regex_enabled = get_post_meta( $post->ID, '_redirect_from_regex', true );
		$strip_query_params = get_post_meta( $post->ID, '_redirect_strip_query_params', true );
		$group = get_post_meta( $post->ID, '_redirect_group', true );
		$notes = get_post_meta( $post->ID, '_redirect_notes', true );
		
		// Determine action type from status code
		$redirect_codes = array( 301, 302, 303, 304, 307, 308 );
		$error_codes = array( 400, 401, 403, 404, 410, 500, 501, 503 );
		
		if ( in_array( $status_code, $error_codes ) ) {
			$action_type = 'error';
		} else {
			$action_type = 'redirect';
		}
		
		// Get available groups
		$options_extra = get_option( ASENHA_SLUG_U . '_extra', array() );
		$groups = isset( $options_extra['redirect_groups'] ) ? $options_extra['redirect_groups'] : array();
		
		// Nonce field
		wp_nonce_field( 'asenha_redirect_meta_save', 'asenha_redirect_meta_nonce' );
		
		?>
		<div class="asenha-redirect-fields">
			<!-- Redirect From -->
			<div class="asenha-field-row redirect-from-field">
			<label for="redirect_from" class="asenha-field-label">
				<?php echo esc_html__( 'Redirect From', 'admin-site-enhancements' ); ?>
				<span class="required">*</span>
			</label>
			<div class="asenha-url-input-wrapper">
				<span class="asenha-url-prefix"><?php echo esc_html( untrailingslashit( home_url() ) ); ?></span>
				<input type="text" 
					   id="redirect_from" 
					   name="redirect_from" 
					   value="<?php echo esc_attr( $redirect_from ); ?>" 
					   class="asenha-field-input large-text" 
					   required
					   placeholder="<?php echo esc_attr__( 'e.g. /old-page, /blog/*, or ^/news-(\d+)$', 'admin-site-enhancements' ); ?>"
					   autocomplete="off" />
			</div>
			<p class="description">
				<?php esc_html_e( 'Use * for wildcard matching.', 'admin-site-enhancements' ); ?>
			</p>
			<div class="asenha-redirect-duplicate-warning">
				<p></p>
			</div>
			</div>

			<!-- Enable Regular Expression -->
			<div class="asenha-field-row">
				<div class="asenha-checkbox-group">
					<label>
						<input type="checkbox" 
							   id="redirect_rule_from_regex" 
							   name="redirect_rule_from_regex" 
							   value="1" 
							   <?php checked( $regex_enabled, true ); ?> />
						<?php esc_html_e( 'Enable regular expression (regex)', 'admin-site-enhancements' ); ?>
					</label>
				</div>
			</div>

			<!-- Action Type and HTTP Status Code -->
			<div class="asenha-field-row when-matched-field">
				<label class="asenha-field-label">
					<?php esc_html_e( 'When Matched', 'admin-site-enhancements' ); ?>
					<span class="required">*</span>
				</label>
				<div class="asenha-inline-field-wrapper">
					<select id="redirect_action_type" name="redirect_action_type" class="asenha-field-select asenha-action-type-select">
						<option value="redirect" <?php selected( $action_type, 'redirect' ); ?>><?php esc_html_e( 'Redirect to URL', 'admin-site-enhancements' ); ?></option>
						<option value="error" <?php selected( $action_type, 'error' ); ?>><?php esc_html_e( 'Show error message', 'admin-site-enhancements' ); ?></option>
					</select>
					<span class="asenha-inline-text"><?php esc_html_e( 'with HTTP status code', 'admin-site-enhancements' ); ?></span>
					<select id="redirect_http_status_code" name="redirect_http_status_code" class="asenha-field-select asenha-status-code-select">
						<!-- Redirect status codes -->
						<option value="301" <?php selected( $status_code, 301 ); ?> data-action-type="redirect">301 <?php esc_html_e( 'Moved Permanently', 'admin-site-enhancements' ); ?></option>
						<option value="302" <?php selected( $status_code, 302 ); ?> data-action-type="redirect">302 <?php esc_html_e( 'Found', 'admin-site-enhancements' ); ?></option>
						<option value="303" <?php selected( $status_code, 303 ); ?> data-action-type="redirect">303 <?php esc_html_e( 'See Other', 'admin-site-enhancements' ); ?></option>
						<option value="304" <?php selected( $status_code, 304 ); ?> data-action-type="redirect">304 <?php esc_html_e( 'Not Modified', 'admin-site-enhancements' ); ?></option>
						<option value="307" <?php selected( $status_code, 307 ); ?> data-action-type="redirect">307 <?php esc_html_e( 'Temporary Redirect', 'admin-site-enhancements' ); ?></option>
						<option value="308" <?php selected( $status_code, 308 ); ?> data-action-type="redirect">308 <?php esc_html_e( 'Permanent Redirect', 'admin-site-enhancements' ); ?></option>
						<!-- Error status codes -->
						<option value="400" <?php selected( $status_code, 400 ); ?> data-action-type="error">400 <?php esc_html_e( 'Bad Request', 'admin-site-enhancements' ); ?></option>
						<option value="401" <?php selected( $status_code, 401 ); ?> data-action-type="error">401 <?php esc_html_e( 'Unauthorized', 'admin-site-enhancements' ); ?></option>
						<option value="403" <?php selected( $status_code, 403 ); ?> data-action-type="error">403 <?php esc_html_e( 'Forbidden', 'admin-site-enhancements' ); ?></option>
						<option value="404" <?php selected( $status_code, 404 ); ?> data-action-type="error">404 <?php esc_html_e( 'Not Found', 'admin-site-enhancements' ); ?></option>
						<option value="410" <?php selected( $status_code, 410 ); ?> data-action-type="error">410 <?php esc_html_e( 'Gone', 'admin-site-enhancements' ); ?></option>
						<option value="500" <?php selected( $status_code, 500 ); ?> data-action-type="error">500 <?php esc_html_e( 'Internal Server Error', 'admin-site-enhancements' ); ?></option>
						<option value="501" <?php selected( $status_code, 501 ); ?> data-action-type="error">501 <?php esc_html_e( 'Not Implemented', 'admin-site-enhancements' ); ?></option>
						<option value="503" <?php selected( $status_code, 503 ); ?> data-action-type="error">503 <?php esc_html_e( 'Service Unavailable', 'admin-site-enhancements' ); ?></option>
					</select>
				</div>
			</div>

			<!-- Default Message (for error status codes) -->
			<div class="asenha-field-row asenha-default-message-field hidden">
				<p class="asenha-default-message-info">
					<?php esc_html_e( 'Default message:', 'admin-site-enhancements' ); ?>
					<span class="asenha-default-message-text"></span>
				</p>
			</div>

			<!-- Message (for error status codes) -->
			<div class="asenha-field-row asenha-message-field<?php echo ( $action_type === 'error' ) ? '' : ' hidden'; ?>">
				<label for="redirect_message" class="asenha-field-label">
					<?php esc_html_e( 'Custom Message', 'admin-site-enhancements' ); ?>
				</label>
				<textarea id="redirect_message" 
						  name="redirect_message" 
						  rows="3" 
						  class="asenha-field-textarea large-text"
						  placeholder=""><?php echo esc_textarea( $message ); ?></textarea>
				<p class="description">
					<?php esc_html_e( 'Optional custom message to display for error status codes.', 'admin-site-enhancements' ); ?>
				</p>
			</div>

			<!-- Redirect To -->
			<div class="asenha-field-row redirect-to-field<?php echo ( $action_type === 'error' ) ? ' hidden' : ''; ?>">
				<label for="redirect_to" class="asenha-field-label">
					<?php echo esc_html__( 'Redirect To', 'admin-site-enhancements' ); ?>
					<span class="required">*</span>
				</label>
				<input type="text" 
					   id="redirect_to" 
					   name="redirect_to" 
					   value="<?php echo esc_attr( $redirect_to ); ?>" 
					   class="asenha-field-input large-text" 
					   <?php echo ( $action_type === 'redirect' ) ? 'required' : ''; ?>
					   placeholder="<?php echo esc_attr__( 'e.g. /new-page/, /articles/*, /news/$1, or https://www.google.com', 'admin-site-enhancements' ); ?>"
					   autocomplete="off" />
				<p class="description">
					<?php esc_html_e( 'Enter the path, pattern, or full URL. Start typing to see suggestions from existing posts/pages.', 'admin-site-enhancements' ); ?>
				</p>
			</div>

			<!-- Do not pass URL parameters -->
			<div class="asenha-field-row redirect-strip-params-field<?php echo ( $action_type === 'error' ) ? ' hidden' : ''; ?>">
				<div class="asenha-checkbox-group">
					<label>
						<input type="checkbox" 
							   id="redirect_strip_query_params" 
							   name="redirect_strip_query_params" 
							   value="1" 
							   <?php checked( $strip_query_params, true ); ?> />
						<?php esc_html_e( 'Do not pass URL / query parameters', 'admin-site-enhancements' ); ?>
					</label>
				</div>
			</div>

			<!-- Group -->
			<div class="asenha-field-row">
				<label for="redirect_group" class="asenha-field-label">
					<?php esc_html_e( 'Group', 'admin-site-enhancements' ); ?>
				</label>
				<div class="asenha-group-field-wrapper">
					<select id="redirect_group" name="redirect_group" class="asenha-field-select">
						<option value=""><?php esc_html_e( 'No Group', 'admin-site-enhancements' ); ?></option>
						<?php foreach ( $groups as $group_option ) : ?>
							<option value="<?php echo esc_attr( $group_option ); ?>" <?php selected( $group, $group_option ); ?>>
								<?php echo esc_html( $group_option ); ?>
							</option>
						<?php endforeach; ?>
					</select>
					<button type="button" class="button asenha-add-group-btn">
						<?php esc_html_e( 'Add', 'admin-site-enhancements' ); ?>
					</button>
					<button type="button" class="button asenha-edit-group-btn">
						<?php esc_html_e( 'Edit', 'admin-site-enhancements' ); ?>
					</button>
					<button type="button" class="button asenha-delete-group-btn">
						<?php esc_html_e( 'Delete', 'admin-site-enhancements' ); ?>
					</button>
				</div>
				<p class="description">
					<?php esc_html_e( 'Organize redirects into groups for easier management.', 'admin-site-enhancements' ); ?>
				</p>
			</div>

			<!-- Notes -->
			<div class="asenha-field-row">
				<label for="redirect_notes" class="asenha-field-label">
					<?php esc_html_e( 'Notes', 'admin-site-enhancements' ); ?>
				</label>
				<textarea id="redirect_notes" 
						  name="redirect_notes" 
						  rows="3" 
						  class="asenha-field-textarea large-text"
						  placeholder=""><?php echo esc_textarea( $notes ); ?></textarea>
				<p class="description">
					<?php esc_html_e( 'Optional notes for your reference (not visible to visitors).', 'admin-site-enhancements' ); ?>
				</p>
			</div>

		</div>
		<?php
	}

	/**
	 * Render tips metabox
	 *
	 * @since 8.1.0
	 * @param WP_Post $post The post object
	 */
	public function render_tips_metabox( $post ) {
		?>
		<div class="asenha-tips-accordion">
			<!-- Wildcard Accordion -->
			<div class="asenha-tips-accordion__item">
				<div class="asenha-tips-accordion__header" role="button" tabindex="0" aria-expanded="false">
					<span class="asenha-tips-accordion__title"><?php esc_html_e( 'How to Use Wildcards', 'admin-site-enhancements' ); ?></span>
					<span class="asenha-tips-accordion__indicator" aria-hidden="true"></span>
				</div>
				<div class="asenha-tips-accordion__content">
					<p><?php esc_html_e( 'Wildcards allow you to match multiple URLs with a single redirect rule. Use the asterisk (*) symbol to match any characters in the URL path.', 'admin-site-enhancements' ); ?></p>
					
					<div class="asenha-tips-example">
						<p>
							<strong><?php esc_html_e( 'Redirect From:', 'admin-site-enhancements' ); ?></strong>
							<code class="asenha-tips-url-pattern">/blog/*</code>
						</p>
						<p>
							<strong><?php esc_html_e( 'Redirect To:', 'admin-site-enhancements' ); ?></strong>
							<code class="asenha-tips-url-pattern">/news/*</code>
						</p>
						<p class="description">
							<?php
							/* translators: 1: example source URL 1, 2: example destination URL 1, 3: example source URL 2, 4: example destination URL 2 */
							echo wp_kses_post( sprintf( __( 'This matches %1$s and redirects to %2$s, and also matches %3$s and redirects to %4$s.', 'admin-site-enhancements' ), '<code>/blog/first-article</code>', '<code>/news/first-article</code>', '<code>/blog/second-article</code>', '<code>/news/second-article</code>' ) );
							?>
						</p>
					</div>
					
				</div>
			</div>
			
			<!-- Regular Expression Accordion -->
			<div class="asenha-tips-accordion__item">
				<div class="asenha-tips-accordion__header" role="button" tabindex="0" aria-expanded="false">
					<span class="asenha-tips-accordion__title"><?php esc_html_e( 'How to Use Regular Expression', 'admin-site-enhancements' ); ?></span>
					<span class="asenha-tips-accordion__indicator" aria-hidden="true"></span>
				</div>
				<div class="asenha-tips-accordion__content">
					<p><?php esc_html_e( 'Regular expressions (regex) provide powerful pattern matching for complex redirect scenarios. Use capturing groups with parentheses () and reference them in the redirect-to URL with $1, $2, etc.', 'admin-site-enhancements' ); ?></p>
					
					<p class="description">
						<?php esc_html_e( 'Remember to check the "Enable regular expression (regex)" checkbox when using regex patterns.', 'admin-site-enhancements' ); ?>
					</p>
					
					<p class="asenha-tips-note">
						<strong><?php esc_html_e( 'Important:', 'admin-site-enhancements' ); ?></strong>
						<?php
						/* translators: 1: caret symbol, 2: dollar symbol, 3: URL of a reference website */
						echo wp_kses_post( sprintf( __( 'Regex patterns must start with %1$s and end with %2$s as <a href="%3$s">anchors</a> that matches the patterns to the start and end of the path or URL being evaluated. If you generate a pattern using the tools listed below the following examples, make sure to include these anchors.', 'admin-site-enhancements' ), '<code>^</code>', '<code>$</code>', 'https://www.regular-expressions.info/anchors.html' ) );
						?>
					</p>
					
					<div class="asenha-tips-example">
						<h4><?php esc_html_e( 'Example 1: Match Numbers', 'admin-site-enhancements' ); ?></h4>
						<p>
							<strong><?php esc_html_e( 'Redirect From:', 'admin-site-enhancements' ); ?></strong>
							<code class="asenha-tips-url-pattern">^/news-(\d+)$</code>
						</p>
						<p>
							<strong><?php esc_html_e( 'Redirect To:', 'admin-site-enhancements' ); ?></strong>
							<code class="asenha-tips-url-pattern">/news/$1</code>
						</p>
						<p class="description">
							<?php
							/* translators: 1: example source URL, 2: example destination URL, 3: explanation of regex pattern */
							echo wp_kses_post( sprintf( __( 'This matches %1$s and redirects to %2$s. The %3$s pattern captures one or more digits. The first and only match is represented by %4$s in the redirect-to URL.', 'admin-site-enhancements' ), '<code>/news-123</code>', '<code>/news/123</code>', '<code>\d+</code>', '<code>$1</code>' ) );
							?>
						</p>
					</div>

					<div class="asenha-tips-example">
						<h4><?php esc_html_e( 'Example 2: Date-Based URL Structure', 'admin-site-enhancements' ); ?></h4>
						<p>
							<strong><?php esc_html_e( 'Redirect From:', 'admin-site-enhancements' ); ?></strong>
							<code class="asenha-tips-url-pattern">^/blog/(\d{4})/(\d{2})/(\d{2})/(.*)$</code>
						</p>
						<p>
							<strong><?php esc_html_e( 'Redirect To:', 'admin-site-enhancements' ); ?></strong>
							<code class="asenha-tips-url-pattern">/articles/$4</code>
						</p>
						<p class="description">
							<?php
							/* translators: 1: example source URL, 2: example destination URL, 3: regex pattern, 4: part of the regex pattern, 5: part of the regex pattern, 6: part of the regex pattern, 7: part of the regex pattern, 8: matching group for use in target URL */
							echo wp_kses_post( sprintf( __( 'This matches %1$s and redirects to %2$s. The pattern %3$s captures the year with %4$s as the first match, month with %5$s as the second match, day with %6$s as the third match, and post slug with %7$s as the fourth match, then uses only the slug (%8$s) in the redirect-to URL.', 'admin-site-enhancements' ), '<code>/blog/2024/01/15/post-name</code>', '<code>/articles/post-name</code>', '<code>(\d{4})/(\d{2})/(\d{2})/(.*)</code>', '<code>(\d{4})</code>', '<code>(\d{2})</code>', '<code>(\d{2})</code>', '<code>(.*)</code>', '<code>$4</code>' ) );
							?>
						</p>
					</div>
										
					<div class="asenha-tips-example">
						<h4><?php esc_html_e( 'Example 3: Match Alternatives', 'admin-site-enhancements' ); ?></h4>
						<p>
							<strong><?php esc_html_e( 'Redirect From:', 'admin-site-enhancements' ); ?></strong>
							<code class="asenha-tips-url-pattern">^/category/(product|service)s?/(.*)$</code>
						</p>
						<p>
							<strong><?php esc_html_e( 'Redirect To:', 'admin-site-enhancements' ); ?></strong>
							<code class="asenha-tips-url-pattern">/$1/$2</code>
						</p>
						<p class="description">
							<?php
							/* translators: 1: example source URL 1, 2: example destination URL 1, 3: example source URL 2, 4: example destination URL 2, 5: regex pattern, 6: first matching group for use in target URL, 7: part of the regex pattern, 8: second matching group for use in target URL */
							echo wp_kses_post( sprintf( __( 'This matches %1$s to %2$s or %3$s to %4$s. %5$s matches both singular and plural forms, i.e. product, products, service and services as the first match (%6$s), while %7$s matches any arbitraty string, e.g. item-name, as the second match (%8$s).', 'admin-site-enhancements' ), '<code>/category/products/item-name</code>', '<code>/product/item-name</code>', '<code>/category/service/item-name</code>', '<code>/service/item-name</code>',  '<code>(product|service)s?</code>', '<code>$1</code>', '<code>(.*)</code>','<code>$2</code>' ) );
							?>
						</p>
					</div>
					
					<div class="asenha-tips-resources">
						<h4><?php esc_html_e( 'Tools to Generate Regex Pattern from URL(s):', 'admin-site-enhancements' ); ?></h4>
						<ul>
							<li>
								<a href="https://wand.tools/regexurl/" target="_blank" rel="noopener noreferrer">https://wand.tools/regexurl/</a>
							</li>
							<li>
								<a href="https://workik.com/ai-powered-regex-generator" target="_blank" rel="noopener noreferrer">https://workik.com/ai-powered-regex-generator</a>
							</li>
							<li>
								<a href="https://app.formulabot.com/ai-regex-generator" target="_blank" rel="noopener noreferrer">https://app.formulabot.com/ai-regex-generator</a>
							</li>
							<li>
								<a href="https://regex-generator.olafneumann.org/" target="_blank" rel="noopener noreferrer">https://regex-generator.olafneumann.org/</a>
							</li>
							<li>
								<a href="https://www.google.com/search?q=generate+regex+pattern+from+URLs+with+AI" target="_blank" rel="noopener noreferrer">https://www.google.com/search?q=generate+regex+pattern+from+URLs+with+AI</a>
							</li>
						</ul>
					</div>
				</div>
			</div>
		</div>
		<?php
	}

	/**
	 * Get default error messages for HTTP status codes
	 *
	 * @since 8.1.0
	 * @return array Array of status codes mapped to their default messages
	 */
	private function get_default_error_messages() {
		return array(
			400 => __( 'The server cannot process the request due to a client error.', 'admin-site-enhancements' ),
			401 => __( 'Authentication is required to access this resource.', 'admin-site-enhancements' ),
			403 => __( 'You do not have permission to access this page.', 'admin-site-enhancements' ),
			410 => __( 'The requested resource is no longer available.', 'admin-site-enhancements' ),
			500 => __( 'The server encountered an unexpected condition that prevented it from fulfilling the request.', 'admin-site-enhancements' ),
			501 => __( 'The server does not support the functionality required to fulfill the request.', 'admin-site-enhancements' ),
			503 => __( 'The server is currently unable to handle the request. Please try again later.', 'admin-site-enhancements' ),
		);
	}

	/**
	 * Enqueue admin assets
	 *
	 * @since 8.1.0
	 * @param string $hook The current admin page hook
	 */
	public function enqueue_assets( $hook ) {
		// Only load on redirect edit/new screens
		if ( ! in_array( $hook, array( 'post.php', 'post-new.php', 'edit.php' ) ) ) {
			return;
		}
		
		global $post_type;
		if ( 'asenha_redirect' !== $post_type ) {
			return;
		}
		
		// Enqueue jQuery UI Autocomplete
		wp_enqueue_script( 'jquery-ui-autocomplete' );
		
		// Enqueue custom CSS
		wp_enqueue_style(
			'asenha-redirect-manager-admin',
			ASENHA_URL . 'includes/premium/redirect-manager/assets/redirect-manager-admin.css',
			array(),
			ASENHA_VERSION
		);
		
		// Enqueue custom JS
		wp_enqueue_script(
			'asenha-redirect-manager-admin',
			ASENHA_URL . 'includes/premium/redirect-manager/assets/redirect-manager-admin.js',
			array( 'jquery', 'jquery-ui-autocomplete' ),
			ASENHA_VERSION,
			true
		);
		
		// Localize script
		wp_localize_script(
			'asenha-redirect-manager-admin',
			'asenhaRedirectManager',
			array(
				'ajaxUrl'              		=> admin_url( 'admin-ajax.php' ),
				'nonce'                		=> wp_create_nonce( 'asenha_redirect_manager' ),
				'postId'               		=> isset( $post_type ) ? get_the_ID() : 0,
		        /* translators: %s is a placeholder will be replaced with the link to the edit screen for the redirect */
				'duplicateWarning'     		=> __( 'Warning: A redirect from this path already exists. <a href="%s" target="_blank">Edit existing redirect</a>', 'admin-site-enhancements' ),
				'groupAddPrompt'       		=> __( 'Enter a name for the new group:', 'admin-site-enhancements' ),
				'groupAddError'		   		=> __( 'Error adding group.', 'admin-site-enhancements' ),
				'groupEditPrompt'      		=> __( 'Enter a new name for the group:', 'admin-site-enhancements' ),
				'groupEditError'       		=> __( 'Error editing group.', 'admin-site-enhancements' ),
				'groupDeleteConfirm'   		=> __( 'Are you sure you want to delete this group? Redirects in this group will not be deleted.', 'admin-site-enhancements' ),
				'groupDeleteError'     		=> __( 'Error deleting group.', 'admin-site-enhancements' ),
				'groupTryAgain'		   		=> __( 'Please try again.', 'admin-site-enhancements' ),
				'groupSelectFirstDelete'    => __( 'Please select a group to delete.', 'admin-site-enhancements' ),
				'groupSelectFirstEdit' 		=> __( 'Please select a group to edit.', 'admin-site-enhancements' ),
				'defaultMessagePrefix' 		=> __( 'Default message:', 'admin-site-enhancements' ),
				'hideDescriptions'     		=> __( 'Hide field descriptions', 'admin-site-enhancements' ),
				'defaultErrorMessages' 		=> $this->get_default_error_messages(),
				'default404Message'    		=> __( 'The 404 page or template will be shown.', 'admin-site-enhancements' ),
			)
		);
	}
}

