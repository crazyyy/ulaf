<?php
/**
 * Login form
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/global/form-login.php.
 *
 * HOWEVER, on occasion WooCommerce will need to update template files and you
 * (the theme developer) will need to copy the new files to your theme to
 * maintain compatibility. We try to do this as little as possible, but it does
 * happen. When this occurs the version of the template file will be bumped and
 * the readme will list any important changes.
 *
 * @see         https://woocommerce.com/document/template-structure/
 * @package     WooCommerce\Templates
 * @version     9.2.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

if ( is_user_logged_in() ) {
	return;
}

?>
<form class="woocommerce-form woocommerce-form-login login" method="post" <?php echo esc_attr( $hidden ? 'style="display:none;"' : '' ); ?>>

	<div class="card card--lg">
		<div class="card__content">
			<?php do_action( 'woocommerce_login_form_start' ); ?>

			<?php echo wp_kses_post( $message ? wpautop( wptexturize( $message ) ) : '' ); // @codingStandardsIgnoreLine ?>

			<div class="row">
				<div class="col-lg-6">
					<div class="form-group">
						<label for="username"><?php esc_html_e( 'Username or email', 'alchemists' ); ?>&nbsp;<span class="required" aria-hidden="true">*</span><span class="screen-reader-text"><?php esc_html_e( 'Required', 'alchemists' ); ?></span></label>
						<input type="text" class="input-text" name="username" id="username" autocomplete="username" required aria-required="true" />
					</div>
				</div>
				<div class="col-lg-6">
					<div class="form-group">
						<label for="password"><?php esc_html_e( 'Password', 'alchemists' ); ?>&nbsp;<span class="required" aria-hidden="true">*</span><span class="screen-reader-text"><?php esc_html_e( 'Required', 'alchemists' ); ?></span></label>
						<input class="input-text" type="password" name="password" id="password" autocomplete="current-password" required aria-required="true" />
					</div>
				</div>
			</div>

			<?php do_action( 'woocommerce_login_form' ); ?>

			<div class="form-group form-group--password-forgot">
				<label class="woocommerce-form__label woocommerce-form__label-for-checkbox woocommerce-form-login__rememberme checkbox checkbox-inline">
					<input class="woocommerce-form__input woocommerce-form__input-checkbox" name="rememberme" type="checkbox" id="rememberme" value="forever" /> <span><?php esc_html_e( 'Remember me', 'alchemists' ); ?></span>
					<span class="checkbox-indicator"></span>
				</label>
				<span class="password-reminder woocommerce-LostPassword lost_password">
					<?php esc_html_e( 'Lost your password?', 'alchemists' ); ?> <a href="<?php echo esc_url( wp_lostpassword_url() ); ?>"><?php esc_html_e( 'Click Here', 'alchemists' ); ?></a>
				</span>
			</div>

			<div class="form-group form-group--sm">
				<?php wp_nonce_field( 'woocommerce-login', 'woocommerce-login-nonce' ); ?>
				<input type="hidden" name="redirect" value="<?php echo esc_url( $redirect ) ?>" />
				<button type="submit" class="btn btn-primary-inverse btn-lg btn-block<?php echo esc_attr( wc_wp_theme_get_element_class_name( 'button' ) ? ' ' . wc_wp_theme_get_element_class_name( 'button' ) : '' ); ?>" name="login" value="<?php esc_attr_e( 'Login', 'alchemists' ); ?>"><?php esc_html_e( 'Login', 'alchemists' ); ?></button>
			</div>

			<?php do_action( 'woocommerce_login_form_end' ); ?>
		</div>
	</div>

</form>
