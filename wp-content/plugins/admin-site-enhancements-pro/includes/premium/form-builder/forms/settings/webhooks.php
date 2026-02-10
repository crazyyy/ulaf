<?php
defined( 'ABSPATH' ) || die();
$enable_webhooks = isset( $settings['enable_webhooks'] ) ? $settings['enable_webhooks'] : 'on';
?>
<div class="fb-form-container fb-grid-container">
    <div class="fb-form-row fb-grid-container">
        <div class="fb-grid-6">
            <label><?php esc_html_e( 'Send Form Submissions to Webhooks', 'admin-site-enhancements' ); ?></label>
            <div class="fb-setting-fields fb-toggle-input-field">
                <input type="hidden" name="enable_webhooks" value="off">
                <input type="checkbox" id="enable_webhooks" name="enable_webhooks" value="on" <?php checked( $enable_webhooks, 'on', true ); ?>>
            </div>
        </div>
    </div>
    <div class="fb-form-row fb-multiple-rows fb-grid-container">
        <div class="fb-grid-6">
            <label><?php esc_html_e( 'Webhook URLs', 'admin-site-enhancements' ); ?></label>
            <div class="fb-multiple-webhook">
                <?php
                $webhook_urls = isset( $settings['webhook_urls'] ) ? $settings['webhook_urls'] : '';
                if ( ! empty( $webhook_urls ) ) {
                    $webhook_urls = explode( ',', $settings['webhook_urls'] );
                    foreach ( $webhook_urls as $webhook_url ) {
                        ?>
                        <div class="fb-webhook-row">
                            <input type="text" name="webhook_urls[]" value="<?php echo esc_attr( $webhook_url ); ?>" />
                            <span class="fb fb-trash-can-outline fb-delete-webhook-row"><?php echo wp_kses( Form_Builder_Icons::get( 'delete' ), Form_Builder_Common_Methods::get_kses_extended_ruleset() ); ?></span>
                        </div>
                    <?php 
                    } 
                }
                ?>
            </div>
            <button type="button" class="button button-primary fb-add-webhook"><?php esc_html_e( 'Add a Webhook URL', 'admin-site-enhancements' ); ?></button>
            <p class="description"><?php echo wp_kses_post( '<a href="https://www.google.com/search?q=webhook+test" target="_blank">' . __( 'Webhook testing tools', 'admin-site-enhancements' ) . ' &raquo;</a>' ); ?></p>
        </div>
    </div>
    <div class="fb-form-row fb-grid-container">
        <div class="fb-grid-6">
            <label><?php esc_html_e( 'Webhook Payload Type', 'admin-site-enhancements' ); ?></label>
            <select name="webhook_payload_type" data-condition="toggle" id="fb-form-conformation-type">
                <option value="full" <?php selected( $settings['webhook_payload_type'], 'full' ); ?>><?php esc_html_e( 'Full', 'admin-site-enhancements' ); ?> -- <?php esc_html_e( 'Form ID, title, URL and raw data by IDs', 'admin-site-enhancements' ); ?></option>
                <option value="raw_by_field_id" <?php selected( $settings['webhook_payload_type'], 'raw_by_field_id' ); ?>><?php esc_html_e( 'Raw data by IDs', 'admin-site-enhancements' ); ?> -- <?php esc_html_e( 'Field ID => field name, type, webhook key, value', 'admin-site-enhancements' ); ?></option>
                <option value="raw_by_field_webhook_key" <?php selected( $settings['webhook_payload_type'], 'raw_by_field_webhook_key' ); ?>><?php esc_html_e( 'Raw data by webhook keys', 'admin-site-enhancements' ); ?> -- <?php esc_html_e( 'Field webhook key => field ID, name, type, value', 'admin-site-enhancements' ); ?></option>
                <option value="named" <?php selected( $settings['webhook_payload_type'], 'named' ); ?>><?php esc_html_e( 'Named', 'admin-site-enhancements' ); ?>  -- <?php esc_html_e( 'Pairs of field webhook key and field value', 'admin-site-enhancements' ); ?></option>
                <option value="flat_named" <?php selected( $settings['webhook_payload_type'], 'flat_named' ); ?>><?php esc_html_e( 'Flat named', 'admin-site-enhancements' ); ?>  -- <?php esc_html_e( 'Pairs of field webhook key + subfield key and field value', 'admin-site-enhancements' ); ?></option>
            </select>
            <div class="fb-webhook-payload-example" role="region" aria-label="<?php esc_attr_e( 'Webhook payload example', 'admin-site-enhancements' ); ?>">
                <p class="fb-webhook-payload-example__label"><strong><?php esc_html_e( 'Example', 'admin-site-enhancements' ); ?></strong></p>
                <pre class="fb-webhook-payload-example__pre"><code class="fb-webhook-payload-example__code" aria-live="polite">{}</code></pre>
            </div>
        </div>
    </div>
</div>