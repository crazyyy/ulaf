<?php
$is_lws = false;
if (isset($_SERVER['lwscache'])) {
    $is_lws = true;
}
?>

<div class="lws_tk_div_title_plugins">
    <h3 class="lws_tk_title_plugins"> <?php esc_html_e('AI Management', 'lws-tools'); ?></h3>
    <h4 class="lws_tk_subtitle_plugins"><?php esc_html_e('WPilot, your personal WordPress expert', 'lws-tools'); ?></h4>
    <p class="lws_tk_text_base" style="margin-bottom: 0;">
        <?php esc_html_e('WPilot is your dedicated AI assistant for WordPress. He automatically analyzes your site, plugins, and theme to provide personalized advice. He guides you through creating, updating, and optimizing your site while helping you troubleshoot common issues. Enjoy a true co-pilot for building and evolving your site with ease.', 'lws-tools'); ?>
    </p>
</div>
<div class="lws_tk_tab_line lws_tk_tab_border lws_tk_tab_border_blue">
    <div class="lws_tk_tab" style="width: 100%;">
        <label class="lws_tk_ia_label" for=''>
            <div>
                <span><?php esc_html_e('Activate WPilot, the AI assistant', 'lws-tools'); ?></span>
            </div>
            <label class="mab_mml_ttbt_td_switch">
                <input class="mab_mml_ttbt_input" name="ia_chatbot_state" id="ia_chatbot_state" type="checkbox" <?php echo ((get_option('lws_tk_ia_chatbot_state', false) || !$is_lws) ? '' : 'checked'); ?>>
                <span class="mab_mml_ttbt_td_s_slider round"></span>
            </label>
        </label>
    </div>
</div>

<script>
    document.getElementById('ia_chatbot_state').addEventListener('change', function() {
        var isChecked = this.checked;
        let checkbox = this;

        let is_lws = "<?php echo $is_lws; ?>";

        if (!is_lws) {
            checkbox.checked = false;
            showLWSModal();
            return;
        }

        let ajaxRequest = jQuery.ajax({
                url: ajaxurl,
                type: "POST",
                timeout: 120000,
                context: document.body,
                data: {
                    _ajax_nonce: "<?php echo esc_html(wp_create_nonce("ia_chatbot_nonce")); ?>",
                    action: 'update_ia_chatbot_state',
                    state: isChecked ? 1 : 0,
                },

                success: function(data) {
                    if (data === null || typeof data != 'string') {
                        return 0;
                    }

                    try {
                        var returnData = JSON.parse(data);
                    } catch (e) {
                        console.log(e);
                        returnData = {
                            'code': "NOT_JSON",
                            'data': "FAIL"
                        };
                    }

                    switch (returnData['code']) {
                        case 'SUCCESS':
                            callPopup('success', '<?php echo esc_html__('IA Chatbot state updated successfully.', 'lws-tools'); ?>');
                            break;
                        case "NOT_LWS":
                            callPopup('error', '<?php echo esc_html__('Chatbot is only available on LWS hostings.', 'lws-tools'); ?>');
                            checkbox.checked = false;
                            break;
                        default:
                            callPopup('error', '<?php echo esc_html__('Error updating IA Chatbot state.', 'lws-tools'); ?>');
                            break;
                    }
                },
                error: function(error) {
                    callPopup('error', '<?php echo esc_html__('Unknown error while updating IA Chatbot state.', 'lws-tools'); ?>');
                    console.log(error);
                    return 1;
                }
            });
    });

    function showLWSModal() {
        document.getElementById('lwsModal').style.display = 'block';
    }

    function closeLWSModal() {
        document.getElementById('lwsModal').style.display = 'none';
    }

    // Close modal when clicking outside of it
    window.onclick = function(event) {
        var modal = document.getElementById('lwsModal');
        if (event.target == modal) {
            modal.style.display = 'none';
        }
    }
</script>

<!-- LWS Modal -->
<div id="lwsModal" class="lws-modal">
    <div class="lws-modal-content">
        <span class="lws-modal-close" onclick="closeLWSModal()">&times;</span>
        <div class="lws-modal-body">
            <h2 class="lws-modal-title"><?php esc_html_e('Available only with LWS Hostings', 'lws-tools'); ?></h2>
            <p class="lws-modal-subtitle">
                <?php esc_html_e('The intelligent assistant WPilot is reserved for customers with LWS hosting.', 'lws-tools'); ?>
            </p>

            <div class="lws-modal-features">
                <h3><?php esc_html_e('WPilot is your WordPress co-pilot:', 'lws-tools'); ?></h3>
                <ul>
                    <li><?php esc_html_e('Analyzes your site, extensions and theme', 'lws-tools'); ?></li>
                    <li><?php esc_html_e('Helps you create, maintain and optimize your site', 'lws-tools'); ?></li>
                    <li><?php esc_html_e('Provides personalized advice and solves common problems', 'lws-tools'); ?></li>
                </ul>
            </div>

            <div class="lws-modal-offer">
                <h3><?php esc_html_e('Get WPilot with LWS WordPress hosting', 'lws-tools'); ?></h3>
                <p class="lws-modal-offer-subtitle">
                    <?php esc_html_e('The best WordPress host to create your site easily.', 'lws-tools'); ?>
                </p>

                <div class="lws-modal-benefits">
                    <div class="lws-benefit">
                        <?php esc_html_e('1-click installation – Ultra-fast and reliable WordPress', 'lws-tools'); ?>
                    </div>
                    <div class="lws-benefit">
                        <?php esc_html_e('Free domain (.fr, .com...)', 'lws-tools'); ?>
                    </div>
                    <div class="lws-benefit">
                        <?php esc_html_e('Secure site + 100% SSD storage', 'lws-tools'); ?>
                    </div>
                    <div class="lws-benefit">
                        <?php esc_html_e('AI, Divi Builder, Elegant Themes included', 'lws-tools'); ?>
                    </div>
                </div>

                <div class="lws-modal-price">
                    <span class="lws-price-highlight"><?php esc_html_e('Only €2.99/month instead of €4.99', 'lws-tools'); ?></span>
                </div>
            </div>

            <div style="text-align: center; margin-top: 20px;">
                <a href="https://www.lws.fr/hebergement_wordpress.php" class="lws-modal-cta" target="_blank">
                    <?php esc_html_e('Take advantage of the LWS WordPress offer', 'lws-tools'); ?>
                </a>
            </div>
        </div>
    </div>
</div>