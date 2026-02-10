<?php
$arr = array('strong' => array());

$plugins = array(
        'lws-hide-login' => array('LWS Hide Login', __('This plugin <strong>hide your administration page</strong> (wp-admin) and lets you <strong>change your login page</strong> (wp-login). It offers better security as hackers will have more trouble finding the page.', 'lws-tools'), true),
        'lws-optimize' => array('LWS Optimize', __("This plugin lets you boost your website's <strong>loading times</strong> thanks to our tools: caching, media optimisation, files minification and concatenation...", 'lws-tools'), true),
        'lws-cleaner' => array('LWS Cleaner', __('This plugin lets you <strong>clean your WordPress website</strong> in a few clics to gain speed: posts, comments, terms, users, settings, plugins, medias, files.', 'lws-tools'), true),
        // 'lws-sms' => array('LWS SMS', __('This plugin, designed specifically for WooCommerce, lets you <strong>send SMS automatically to your customers</strong>. You will need an account at LWS and enough credits to send SMS. Create personnalized templates, manage your SMS and sender IDs and more!', 'lws-tools'), false),
        'lws-affiliation' => array('LWS Affiliation', __('With this plugin, you can add banners and widgets on your website and use those with your <strong>affiliate account LWS</strong>. Earn money and follow the evolution of your gains on your website.', 'lws-tools'), false),
        'lwscache' => array('LWSCache', __('Based on the Varnich cache technology and NGINX, LWSCache let you <strong>speed up the loading of your pages</strong>. This plugin helps you automatically manage your LWSCache when editing pages, posts... and purging all your cache. Works only if your server use this cache.', 'lws-tools'), false),
        'lws-tools' => array('LWS Tools', __('This plugin provides you with several tools and shortcuts to manage, secure and optimise your WordPress website. Updating plugins and themes, accessing informations about your server, managing your website parameters, etc... Personnalize every aspect of your website!', 'lws-tools'), false)
);

$plugins_activated = array();
$all_plugins = get_plugins();

foreach ($plugins as $slug => $plugin) {
    if (is_plugin_active($slug . '/' . $slug . '.php')) {
        $plugins_activated[$slug] = "full";
    } elseif (array_key_exists($slug . '/' . $slug . '.php', $all_plugins)) {
        $plugins_activated[$slug] = "half";
    }
}

$tabs_list = array(
    array('notifications', __('Notifications', 'lws-tools')),
    array('server', __('Server', 'lws-tools')),
    array('optimisation', __('Optimisations', 'lws-tools')),
    array('security', __('Security', 'lws-tools')),
    //array('antivirus', __('Antivirus', 'lws-tools')),
    array('mysql', __('MySQL Logs', 'lws-tools')),
    array('tools', __('Other Tools', 'lws-tools')),
    array('ia', __('WPilot (AI Assistant)', 'lws-tools')),
    array('plugins', __('Our plugins', 'lws-tools')),
)
// // //
?>

<script>
    var function_ok = true;
</script>

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
            <div class="tab_lws_tk" id='tab_lws_tk_block'>
                <div id="tab_lws_tk" role="tablist" aria-label="Onglets_lws_tk">
                    <?php foreach ($tabs_list as $tab) : ?>
                    <button
                        id="<?php echo esc_attr('nav-' . $tab[0]); ?>"
                        class="tab_nav_lws_tk <?php echo $tab[0] == 'notifications' ? esc_attr('active') : ''; ?>"
                        data-toggle="tab" role="tab"
                        aria-controls="<?php echo esc_attr($tab[0]);?>"
                        aria-selected="<?php echo $tab[0] == 'notifications' ? esc_attr('true') : esc_attr('false'); ?>"
                        tabindex="<?php echo $tab[0] == 'notifications' ? esc_attr('0') : '-1'; ?>">
                        <?php if ($tab[0] == "ia") : ?>
                            <img src="<?php echo esc_url(plugins_url('images/lws_ia.svg', __DIR__)) ?>" alt="IA Icon" width="20px" height="20px">
                        <?php endif; ?>
                        <?php echo esc_html($tab[1]); ?>
                    </button>
                    <?php endforeach ?>
                    <div id="selector" class="selector_tab">&nbsp;</div>
                </div>

                <div class="tab_lws_tk_select hidden">
                    <select name="tab_lws_tk_select" id="tab_lws_tk_select" style="text-align:center">
                        <?php foreach ($tabs_list as $tab) : ?>
                        <option
                            value="<?php echo esc_attr("nav-" . $tab[0]); ?>">
                            <?php echo esc_html($tab[1]); ?>
                        </option>
                        <?php endforeach?>
                    </select>
                </div>
            </div>

            <?php foreach ($tabs_list as $tab) : ?>
            <div class="tab-pane main-tab-pane"
                id="<?php echo esc_attr($tab[0])?>" role="tabpanel"
                aria-labelledby="nav-<?php echo esc_attr($tab[0])?>"
                <?php echo $tab[0] == 'notifications' ? esc_attr('tabindex="0"') : esc_attr('tabindex="-1" hidden')?>>
                <div id="post-body"
                    class="<?php echo $tab[0] == 'plugins' ? esc_attr('lws_tk_configpage_plugin') : esc_attr('lws_tk_configpage'); ?> ">
                    <?php include plugin_dir_path(__FILE__) . $tab[0] . '.php'; ?>
                </div>
            </div>
            <?php endforeach?>
        </div>
    </div>
</div>

<div id="lws_tk_popup_alerting"></div>

<script>
    function lws_tk_copy_clipboard(input) {
        navigator.clipboard.writeText(input.innerText.trim());
        setTimeout(function() {
            jQuery('#copied_tip').remove();
        }, 500);
        jQuery(input).append("<div class='tip' id='copied_tip'>" +
            "<?php esc_html_e('Copied!', 'lws-tools');?>" +
            "</div>");
    }

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
    var reset_table = (function() {
        var executed_template = false;
        return function() {
            if (!executed_template && jQuery('#lws_tk_mysqltable') != null) {
                executed_template = true;
                jQuery(document).ready(function() {
                    jQuery('#lws_tk_mysqltable').DataTable().columns.adjust();
                });
            }
        };
    })();

    jQuery(document).ready(function() {
        <?php foreach ($plugins_activated as $slug => $activated) : ?>
        <?php if ($activated == "full") : ?>

        /**/
        var button = jQuery(
            "<?php echo esc_attr("#bis_" . $slug); ?>"
        );
        button.children()[3].classList.remove('hidden');
        button.children()[0].classList.add('hidden');
        button.prop('onclick', false);
        button.addClass('lws_tk_button_ad_block_validated');

        <?php elseif ($activated == "half") : ?>
        /**/
        var button = jQuery(
            "<?php echo esc_attr("#bis_" . $slug); ?>"
        );
        button.children()[2].classList.remove('hidden');
        button.children()[0].classList.add('hidden');
        <?php endif ?>
        <?php endforeach ?>
    });

    function install_plugin(button) {
        var newthis = this;
        if (this.function_ok) {
            this.function_ok = false;
            const regex = /bis_/;
            bouton_id = button.id;
            bouton_sec = "";
            if (bouton_id.match(regex)) {
                bouton_sec = bouton_id.substring(4);
            } else {
                bouton_sec = "bis_" + bouton_id;
            }

            button_sec = document.getElementById(bouton_sec);

            button.children[0].classList.add('hidden');
            button.children[3].classList.add('hidden');
            button.children[2].classList.add('hidden');
            button.children[1].classList.remove('hidden');
            button.classList.remove('lws_tk_button_ad_block_validated');
            button.setAttribute('disabled', true);

            if (button_sec !== null) {
                button_sec.children[0].classList.add('hidden');
                button_sec.children[3].classList.add('hidden');
                button_sec.children[2].classList.add('hidden');
                button_sec.children[1].classList.remove('hidden');
                button_sec.classList.remove('lws_tk_button_ad_block_validated');
                button_sec.setAttribute('disabled', true);
            }

            var data = {
                action: "lws_tk_downloadPlugin",
                _ajax_nonce: '<?php echo esc_attr(wp_create_nonce('updates')); ?>',
                slug: button.getAttribute('value'),
            };
            jQuery.post(ajaxurl, data, function(response) {
                if (!response.success) {
                    if (response.data.errorCode == 'folder_exists') {
                        var data = {
                            action: "lws_tk_activatePlugin",
                            ajax_slug: response.data.slug,
                            _ajax_nonce: '<?php echo esc_attr(wp_create_nonce('tools_activate_plugin')); ?>',
                        };
                        jQuery.post(ajaxurl, data, function(response) {
                            jQuery('#' + bouton_id).children()[1].classList.add('hidden');
                            jQuery('#' + bouton_id).children()[2].classList.add('hidden');
                            jQuery('#' + bouton_id).children()[3].classList.remove('hidden');
                            jQuery('#' + bouton_id).addClass('lws_tk_button_ad_block_validated');
                            newthis.function_ok = true;

                            if (button_sec !== null) {
                                jQuery('#' + bouton_sec).children()[1].classList.add('hidden');
                                jQuery('#' + bouton_sec).children()[2].classList.add('hidden');
                                jQuery('#' + bouton_sec).children()[3].classList.remove('hidden');
                                jQuery('#' + bouton_sec).addClass(
                                    'lws_tk_button_ad_block_validated');
                                newthis.function_ok = true;
                            }
                        });

                    } else {
                        jQuery('#' + bouton_id).children()[1].classList.add('hidden');
                        jQuery('#' + bouton_id).children()[2].classList.add('hidden');
                        jQuery('#' + bouton_id).children()[3].classList.add('hidden');
                        jQuery('#' + bouton_id).children()[0].classList.add('hidden');
                        jQuery('#' + bouton_id).children()[4].classList.remove('hidden');
                        jQuery('#' + bouton_id).addClass('lws_tk_button_ad_block_failed');
                        setTimeout(() => {
                            jQuery('#' + bouton_id).removeClass('lws_tk_button_ad_block_failed');
                            jQuery('#' + bouton_id).prop('disabled', false);
                            jQuery('#' + bouton_id).children()[0].classList.remove('hidden');
                            jQuery('#' + bouton_id).children()[4].classList.add('hidden');
                            newthis.function_ok = true;
                        }, 2500);

                        if (button_sec !== null) {
                            jQuery('#' + bouton_sec).children()[1].classList.add('hidden');
                            jQuery('#' + bouton_sec).children()[2].classList.add('hidden');
                            jQuery('#' + bouton_sec).children()[3].classList.add('hidden');
                            jQuery('#' + bouton_sec).children()[0].classList.add('hidden');
                            jQuery('#' + bouton_sec).children()[4].classList.remove('hidden');
                            jQuery('#' + bouton_sec).addClass('lws_tk_button_ad_block_failed');
                            setTimeout(() => {
                                jQuery('#' + bouton_sec).removeClass(
                                    'lws_tk_button_ad_block_failed');
                                jQuery('#' + bouton_sec).prop('disabled', false);
                                jQuery('#' + bouton_sec).children()[0].classList.remove('hidden');
                                jQuery('#' + bouton_sec).children()[4].classList.add('hidden');
                                newthis.function_ok = true;
                            }, 2500);
                        }
                    }
                } else {
                    jQuery('#' + bouton_id).children()[1].classList.add('hidden');
                    jQuery('#' + bouton_id).children()[2].classList.remove('hidden');
                    jQuery('#' + bouton_id).prop('disabled', false);
                    newthis.function_ok = true;

                    if (button_sec !== null) {
                        jQuery('#' + bouton_sec).children()[1].classList.add('hidden');
                        jQuery('#' + bouton_sec).children()[2].classList.remove('hidden');
                        jQuery('#' + bouton_sec).prop('disabled', false);
                        newthis.function_ok = true;
                    }
                }
            });
        }
    }
</script>

<!-- Here, need to change id of the selector and tabs -->
<script>
    const tabs = document.querySelectorAll('.tab_nav_lws_tk[role="tab"]');

    // Add a click event handler to each tab
    tabs.forEach((tab) => {
        tab.addEventListener('click', lws_tk_changeTabs);
    });

    <?php if (isset($change_tab)) : ?>
        var element = document.getElementById(
        "<?php echo esc_attr($change_tab); ?>");
        lws_tk_changeTabs(element);
    <?php else : ?>
        lws_tk_selectorMove(document.getElementById('nav-notifications'), document.getElementById('nav-notifications').parentNode);
    <?php endif ?>

    function lws_tk_selectorMove(target, parent) {
        const cursor = document.getElementById('selector');
        var element = target.getBoundingClientRect();
        var bloc = parent.getBoundingClientRect();

        var padding = parseInt((window.getComputedStyle(target, null).getPropertyValue('padding-left')).slice(0, -
            2));
        var margin = parseInt((window.getComputedStyle(target, null).getPropertyValue('margin-left')).slice(0, -2));
        var begin = (element.left - bloc.left) - margin;
        var ending = target.clientWidth + 2 * margin;

        cursor.style.width = ending + "px";
        cursor.style.left = begin + "px";
    }

    function lws_tk_changeTabs(e) {
        var target;
        if (e.target === undefined) {
            target = e;
        } else {
            target = e.target;
        }
        const parent = target.parentNode;
        const grandparent = parent.parentNode.parentNode;

        // Remove all current selected tabs
        parent
            .querySelectorAll('.tab_nav_lws_tk[aria-selected="true"]')
            .forEach(function(t) {
                t.setAttribute('aria-selected', false);
                t.classList.remove("active")
            });

        // Set this tab as selected
        target.setAttribute('aria-selected', true);
        target.classList.add('active');

        // Hide all tab panels
        grandparent
            .querySelectorAll('.tab-pane.main-tab-pane[role="tabpanel"]')
            .forEach((p) => p.setAttribute('hidden', true));

        // Show the selected panel
        grandparent.parentNode
            .querySelector(`#${target.getAttribute('aria-controls')}`)
            .removeAttribute('hidden');


        lws_tk_selectorMove(target, parent);
        if (target.id == 'nav-mysql') {
            reset_table();
        }
    }
</script>

<!-- If need a select -->
<!-- Change lws_tk! -->
<script>
    if (window.innerWidth <= 1780) {
        jQuery('#tab_lws_tk').addClass("hidden");
        jQuery('#tab_lws_tk_select').parent().removeClass("hidden");
    }

    jQuery(window).on('resize', function() {
        if (window.innerWidth <= 1780) {
            jQuery('#tab_lws_tk').addClass("hidden");
            jQuery('#tab_lws_tk_select').parent().removeClass("hidden");
            document.getElementById('tab_lws_tk_select').value = document.querySelector(
                '.tab_nav_lws_tk[aria-selected="true"]').id;
        } else {
            jQuery('#tab_lws_tk').removeClass("hidden");
            jQuery('#tab_lws_tk_select').parent().addClass("hidden");
            const target = document.getElementById(document.getElementById('tab_lws_tk_select').value);
            lws_tk_selectorMove(target, target.parentNode);
        }
    });

    jQuery('#tab_lws_tk_select').on('change', function() {
        const target = document.getElementById(this.value);
        const parent = target.parentNode;
        const grandparent = parent.parentNode.parentNode;

        // Remove all current selected tabs
        parent
            .querySelectorAll('.tab_nav_lws_tk[aria-selected="true"]')
            .forEach(function(t) {
                t.setAttribute('aria-selected', false);
                t.classList.remove("active")
            });

        // Set this tab as selected
        target.setAttribute('aria-selected', true);
        target.classList.add('active');

        // Hide all tab panels
        grandparent
            .querySelectorAll('.tab-pane.main-tab-pane[role="tabpanel"]')
            .forEach((p) => p.setAttribute('hidden', true));

        // Show the selected panel
        grandparent.parentNode
            .querySelector(`#${target.getAttribute('aria-controls')}`)
            .removeAttribute('hidden');
    });
</script>