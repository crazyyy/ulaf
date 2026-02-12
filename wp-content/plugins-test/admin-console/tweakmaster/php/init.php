<?php

declare(strict_types = 1);

namespace Nextgenthemes\TweakMaster;

add_action( 'init', __NAMESPACE__ . '\init_public', 9 );
add_action( 'admin_init', __NAMESPACE__ . '\init_admin', 9 );

function init_public(): void {

	require_once __DIR__ . '/fn-settings.php';

	$settings = settings_data();
	$settings->remove( 'scroll-progress-bar' ); // uses options api

	foreach ( $settings->get_all() as $key => $setting ) {

		if ( 'boolean' === $setting->type && option_is_set( $key ) ) {
			require_once TWEAKS_DIR_SA . "/$key.php";
		}
	}

	if ( any_option_is_set( [ 'jpeg-compression', 'webp-compression', 'avif-compression' ] ) ) {
		require_once TWEAKS_DIR . '/image-quality.php';
	}

	if ( ! defined( 'EMPTY_TRASH_DAYS' ) && option_is_set( 'trash-keep-days' ) ) {
		require_once TWEAKS_DIR . '/set-trash-keep-days.php';
	}

	require_once TWEAKS_DIR . '/revisions-to-keep.php';

	require_file_if_option_is_set( 'admin-bar-greeting' );
	require_file_if_option_is_set( 'scroll-progress-bar' );
	require_file_if_option_is_set( 'user-agent' );
	require_file_if_option_is_set( 'admin-footer-text' );
	require_file_if_option_is_set( 'admin-email-check-interval' );
}

function require_file_if_option_is_set( string $key ): void {

	if ( option_is_set( $key ) ) {
		require_once TWEAKS_DIR . "/$key.php";
	}
}

function init_admin(): void {
	add_action( 'nextgenthemes/tweakmaster/admin/settings/sidebar', __NAMESPACE__ . '\code_info' );
	add_action( 'nextgenthemes/tweakmaster/admin/settings/content', __NAMESPACE__ . '\revisions_tab_info' );
}

function code_info(): void {

	?>
	<div class="ngt-sidebar-box">
		<p>
			<?php
				echo wp_kses(
					sprintf(
						// translators: %s: Github URL.
						__( 'See the <a href="%s" target="_blank">code</a> and add tweaks you are missing.', 'tweakmaster' ),
						'https://github.com/nextgenthemes/tweakmaster/tree/master/php/tweaks-standalone'
					),
					[
						'a' => [
							'href'   => [],
							'target' => [],
						],
					],
					[ 'https' ]
				);
			?>
		</p>
	</div>
	<?php
}

function revisions_tab_info(): void {

	?>
	<p data-wp-bind--hidden="!context.activeTabs.revisions">
		<?php echo wp_kses( __( '<code>-1</code> = Unlimited revisions', 'tweakmaster' ), [ 'code' => [] ] ); ?><br>
		<?php echo wp_kses( __( '<code>0</code> = Disable revisions', 'tweakmaster' ), [ 'code' => [] ] ); ?><br>
		<?php echo wp_kses( __( 'Positive integer = Number of revisions', 'tweakmaster' ), [ 'code' => [] ] ); ?>
	</p>
	<?php
}
