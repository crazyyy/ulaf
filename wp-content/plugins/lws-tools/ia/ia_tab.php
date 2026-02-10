<?php
$arr = array('strong' => array());

$is_lws = false;
if (isset($_SERVER['lwscache'])) {
    $is_lws = true;
}
?>

<!-- Beginning main content block -->
<div class="lws_tk_main_bloc">

    <div class="lwstk_title_banner">
        <div class="lwstk_top_banner">
            <img src="<?php echo esc_url(plugins_url('images/plugin_lws_tools_logo.svg', __DIR__)) ?>" alt="LWS Tools Logo" width="80px" height="80px">
            <div class="lwstk_top_banner_text">
                <div class="lwstk_top_title_block">
                    <div style="display: flex; flex-direction: column; gap: 15px;">
                        <div class="lwstk_top_title">
                            <span><?php echo esc_html('LWS Tools'); ?></span>
                            <span><?php esc_html_e('by', 'lws-tools'); ?></span>
                            <span class="logo_lws"></span>
                        </div>

                        <div class="lwstk_top_description">
                            <?php echo esc_html_e('LWS Tools offer toolkits and shortcuts to manage your WordPress website. It lets you secure and optimize  your websites easily and visualize several useful informations about your server, website and database.', 'lws-tools'); ?>
                        </div>
                    </div>
                    <div class="lwstk_rate_block">
                        <div class="lwstk_top_rateus">
                            <?php echo esc_html_e('You like this plugin ? ', 'lws-tools'); ?>
                            <?php echo wp_kses(__('A <a href="https://wordpress.org/support/plugin/lws-tools/reviews/#new-post" target="_blank" class="link_to_rating_with_stars"><div class="lwstk_stars">★★★★★</div> rating</a> will motivate us a lot.', 'lws-tools'), ['a' => ['class' => [], 'href' => [], 'target' => []], 'div' => ['class' => []]]); ?>
                        </div>
                        <div class="lwstk_bottom_rateus">
                            <img src="<?php echo esc_url(plugins_url('images/flamme.svg', __DIR__)) ?>" alt="Flamme Logo" width="16px" height="20px" style="margin-right: 5px;">
                            <?php echo wp_kses(__('<b>-15%</b> on our <a href="https://www.lws.fr/support/" target="_blank" class="link_to_support">WordPress hostings</a> with the code', 'lws-tools'), ['b' => [], 'a' => ['class' => [], 'href' => [], 'target' => []]]); ?>
                            <div class="lwstk_top_code">
                                WPEXT15
                                <img src="<?php echo esc_url(plugins_url('images/copier_new.svg', __DIR__)) ?>" alt="Logo Copy Element" width="15px" height="18px" onclick="lwstktimize_copy_clipboard(this)" readonly text="WPEXT15">
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Home to the tabs + content + ads -->
    <div class="lws_tk_main_content">
        <!-- tabs + content -->
        <div class="lws_tk_list_block_content">
            <!-- Tabs -->
            <div class="tab-pane main-tab-pane">
                <div id="post-body" class="lws_tk_configpage">
                    <div class="lws_tk_div_title_plugins">
                        <h3 class="lws_tk_title_plugins"> <?php esc_html_e('IA Management', 'lws-tools'); ?></h3>
                        <p class="lws_tk_text_base">
                            <?php esc_html_e('', 'lws-tools'); ?>
                        </p>
                    </div>
                <div class="lws_tk_tab_line">
                    <div class="lws_tk_tab" style="width: 100%;">
                        <label class="lws_tk_ia_label" for=''>
                            <?php esc_html_e('Activate the IA Chatbot', 'lws-tools'); ?>
                            <label class="mab_mml_ttbt_td_switch">
                                <input class="mab_mml_ttbt_input" name="ia_chatbot_state" id="ia_chatbot_state" type="checkbox" <?php echo (get_option('lws_tk_ia_chatbot_state', false) || !$is_lws) ? '' : 'checked'; ?>>
                                <span class="mab_mml_ttbt_td_s_slider round"></span>
                            </label>
                        </label>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div id="lws_tk_popup_alerting"></div>

<script>
// Execute the function callback after ms milliseconds unless delay() is called again
function delay(callback, ms) {
    var timer = 0;
    return function() {
        var context = this,
            args = arguments;
        clearTimeout(timer);
        timer = setTimeout(function() {
            callback.apply(context, args);
        }, ms || 0);
    };
}

function callPopup(type, content) {
    // Get the element containing all popups
    let alerting = document.getElementById('lws_tk_popup_alerting');
    if (alerting == null) {
        console.log(JSON.stringify({
            'code': "POPUP_FAIL",
            'data': "Failed to find alerting"
        }));
        return -1;
    }

    if (content == null) {
        console.log(JSON.stringify({
            'code': "POPUP_FAIL",
            'data': "Failed to find content"
        }));
        return -1;
    }

    if (type == null) {
        console.log(JSON.stringify({
            'code': "POPUP_FAIL",
            'data': "Failed to find type"
        }));
        return -1;
    }

    // No more than 4 popups at a time. Remove the oldest one
    if (alerting.children.length > 4) {
        let amount_popups = alerting.children;
        let last = amount_popups.item(amount_popups.length - 1);
        if (last != null) {
            jQuery(last).animate({
                'left': '150%'
            }, 500, function() {
                last.remove();
            });
        }
    }

    let number = alerting.children.length ?? 5;

    alerting.insertAdjacentHTML('afterbegin', `<div class="lws_tk_information_popup" style="left: 150%;" id="lws_tk_information_popup_` + number + `"></div>`);
    let popup = document.getElementById('lws_tk_information_popup_' + number);

    if (popup == null) {
        console.log(JSON.stringify({
            'code': "POPUP_NOT_CREATED",
            'data': "Failed to create the popup"
        }));
        return -1;
    }

    animation = ``;
    switch (type) {
        case 'success':
            animation = `<svg class="checkmark" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 52 52"><circle class="checkmark__circle" cx="26" cy="26" r="25" fill="none" /><path class="checkmark__check" fill="none" d="M14.1 27.2l7.1 7.2 16.7-16.8" /></svg>`;
            break;
        case 'error':
            animation = `
            <svg class="crossmark" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 52 52"> <circle class="crossmark__circle" cx="26" cy="26" r="25" fill="none" stroke="red" stroke-width="2"></circle> <path class="crossmark__cross" fill="none" stroke="red" stroke-width="4" stroke-linecap="round" stroke-linejoin="round" stroke-dasharray="36" stroke-dashoffset="36" d="M16 16 36 36 M36 16 16 36"> <animate attributeName="stroke-dashoffset" from="36" to="0" dur="0.5s" fill="freeze" /> </path></svg>`
            break;
        case 'warning':
            animation = `<svg class="exclamation" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 52 52"> <circle class="exclamation__circle" cx="26" cy="26" r="25" fill="none" stroke="#FFD700" stroke-width="2"></circle> <text class="exclamation__mark" x="26" y="30" font-size="26" font-family="Arial" text-anchor="middle" fill="#FFD700" dominant-baseline="middle">!</text> <style> .exclamation__mark { animation: blink 1s ease-in-out 3; } @keyframes blink { 0%, 100% { opacity: 1; } 50% { opacity: 0; } } </style> </svg>`;
            break;
        default:
            animation = `<svg class="checkmark" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 52 52"><circle class="checkmark__circle" cx="26" cy="26" r="25" fill="none" /><path class="checkmark__check" fill="none" d="M14.1 27.2l7.1 7.2 16.7-16.8" /></svg>`;
            break;
    }

    popup.insertAdjacentHTML('beforeend', `
        <div class="lws_tk_information_popup_animation">` + animation + `</div>
        <div class="lws_tk_information_popup_content">` + content + `</div>
        <div id="lws_tk_close_popup_` + number + `" class="lws_tk_information_popup_close"><img src="<?php echo esc_url(plugins_url('images/fermer.svg', __DIR__)) ?>" alt="close button" width="10px" height="10px">
    `)

    jQuery(popup).animate({
        'left': '0%'
    }, 500);

    popup.classList.add('popup_' + type);

    let popup_button = document.getElementById('lws_tk_close_popup_' + number);
    if (popup_button != null) {
        popup_button.addEventListener('click', function() {
            this.parentNode.remove();
        })
    }

    popup.addEventListener('mouseover', delay(function() {
        if (popup.matches(':hover')) {
            return 0;
        }
        jQuery(this).animate({
            'left': '150%'
        }, 500, function() {
            this.remove();
        });
    }, 5000));

    popup.dispatchEvent(new Event('mouseover'));
}
</script>

<script>
    document.getElementById('ia_chatbot_state').addEventListener('change', function() {
        var isChecked = this.checked;

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
                        default:
                            callPopup('error', '<?php echo esc_html__('Error updating IA Chatbot state.', 'lws-tools'); ?>');
                            break;
                    }
                },
                error: function(error) {
                    callPopup('error', '<?php echo esc_html__('AJAX request failed.', 'lws-tools'); ?>');
                    console.log(error);
                    return 1;
                }
            });
    });
</script>