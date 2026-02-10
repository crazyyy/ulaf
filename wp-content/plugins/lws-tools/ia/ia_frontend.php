<?php
$chatbot_data = get_option('lws_tools_chatbot_data', []);
$user_ip = isset($_SERVER['HTTP_X_REAL_IP']) ? $_SERVER['HTTP_X_REAL_IP'] : '127.0.0.1';

$user_data = isset($chatbot_data[$user_ip]) ? $chatbot_data[$user_ip] : ['amount' => 0, 'date' => time()];

// If the last reset happened 30 days ago or more, reset right now
if ($user_data['date'] < time() - (30 * 24 * 60 * 60)) {
    $user_data['amount'] = 0;
    $user_data['date'] = time();
    $chatbot_data[$user_ip] = $user_data;
    update_option('lws_tools_chatbot_data', $chatbot_data);
}

// Refuse access to the chatbot if the user has sent 100+ messages OR if the data in BDD has been badly altered
$accessible_chatbot = true;
if (!isset($user_data['amount']) || $user_data['amount'] >= 100) {
    $accessible_chatbot = false;
}
?>

<div id="lwstools_opengpt_chatbot_block">
    <?php if ($accessible_chatbot && $website_is_lws) : ?>
    <div id="opengpt_chatbot_element">
        <!-- Hide/Show chatbot toggle button -->
        <button id="toggle_chatbot_visibility"
                style="z-index: 10000; margin-right: 6px; margin-bottom: 6px; position: fixed; right: 108px; bottom: 36px; width: 40px; height: 40px; border: 2px solid #e2e8f0; border-radius: 8px; background: white; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06); cursor: pointer; display: flex; align-items: center; justify-content: center; transition: right 0.3s ease;"
                title="<?php esc_html_e('Hide/Show chatbot', 'lws-tools'); ?>">
            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <polyline points="15 18 9 12 15 6"></polyline>
            </svg>
        </button>

        <iframe src="https://www.openassistantgpt.io/embed/cmdol2mhl0003mblafzjcm8c9/button?chatbox=false"
        style="z-index: 10000; margin-right: 6px; margin-bottom: 6px; position: fixed; right: 36px; bottom: 36px; width: 60px; height: 60px; border: 0; border: 2px solid #e2e8f0; border-radius: 50%; color-scheme: none; background: none;box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06); transition: opacity 0.3s ease, transform 0.3s ease;"
        id="openassistantgpt-chatbot-button-iframe"></iframe>


        <!-- This chatbot is build using https://openassistantgpt.io/ -->
        <iframe
            src="https://www.openassistantgpt.io/embed/cmdol2mhl0003mblafzjcm8c9/window?chatbox=false&withExitX=true&clientSidePrompt=<?php echo $encoded_prompt; ?>"
            style="z-index: 10000; margin-right: 6px; margin-bottom: 98px; display: none; position: fixed; right: 0; bottom: 0; pointer-events: none; overflow: hidden; height: 65vh; border: 2px solid #e2e8f0; border-radius: 0.375rem; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06); max-width: 700px; width: stretch;"
            allow="clipboard-read; clipboard-write"
            allowfullscreen id="openassistantgpt-chatbot-iframe">
        </iframe>
    </div>
    <?php elseif (!$accessible_chatbot && $website_is_lws) : ?>
        <div id="opengpt_chatbot_element">
            <div id="opengpt_chatbot_deactivated" class="lws_tools_assistant_button" title="<?php esc_html_e('You have reached your quota (100 messages) for this month.', 'lws-tools'); ?>">
                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-message-square" style="color: rgb(30, 73, 155);"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"></path></svg>
            </div>
        </div>
    <?php else : ?>
        <div id="opengpt_chatbot_element">
            <div id="opengpt_chatbot_deactivated" class="lws_tools_assistant_button deactivated" title="<?php esc_html_e('Chatbot is only available on LWS hostings.', 'lws-tools'); ?>">
                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-message-square" style="color: rgb(30, 73, 155);"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"></path></svg>
            </div>
        </div>
    <?php endif; ?>
</div>

<script>
    // Toggle chatbot visibility
    (function() {
        const toggleButton = document.getElementById('toggle_chatbot_visibility');
        const chatbotButton = document.getElementById('openassistantgpt-chatbot-button-iframe');
        const chatbotIframe = document.getElementById('openassistantgpt-chatbot-iframe');

        if (toggleButton && chatbotButton) {
            const isHidden = localStorage.getItem('lws_chatbot_hidden') === 'true';

            if (isHidden) {
                chatbotButton.style.opacity = '0';
                chatbotButton.style.transform = 'scale(0)';
                chatbotButton.style.pointerEvents = 'none';
                toggleButton.style.right = '6px';
                toggleButton.innerHTML = '<svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="9 18 15 12 9 6"></polyline></svg>';
            }

            toggleButton.addEventListener('click', function() {
                if (chatbotButton.style.opacity === '0' || chatbotButton.style.opacity === '') {
                    chatbotButton.style.opacity = '1';
                    chatbotButton.style.transform = 'scale(1)';
                    chatbotButton.style.pointerEvents = 'auto';
                    toggleButton.style.right = '108px';
                    toggleButton.innerHTML = '<svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="15 18 9 12 15 6"></polyline></svg>';
                    localStorage.setItem('lws_chatbot_hidden', 'false');
                } else {
                    chatbotButton.style.opacity = '0';
                    chatbotButton.style.transform = 'scale(0)';
                    chatbotButton.style.pointerEvents = 'none';
                    if (chatbotIframe) {
                        chatbotIframe.style.display = 'none';
                        chatbotIframe.contentWindow.postMessage("closeChat", "*");
                    }
                    toggleButton.style.right = '6px';
                    toggleButton.innerHTML = '<svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="9 18 15 12 9 6"></polyline></svg>';
                    localStorage.setItem('lws_chatbot_hidden', 'true');
                }
            });
        }
    })();
</script>

<script>
    window.addEventListener("message", function(t) {
        var e = document.getElementById("openassistantgpt-chatbot-iframe"),
            s = document.getElementById("openassistantgpt-chatbot-button-iframe"),
            block = document.getElementById("opengpt_chatbot_element");
        "openChat" === t.data && (e && s
            ?
            (
                e.contentWindow.postMessage("openChat", "*"),
                s.contentWindow.postMessage("openChat", "*"),
                e.style.pointerEvents = "auto",
                e.style.display = "block",
                window.innerWidth < 640
                ?
                    (
                        e.style.position = "fixed",
                        e.style.width = "100%",
                        e.style.height = "100%",
                        e.style.top = "0",
                        e.style.left = "0",
                        e.style.zIndex = "9999"
                    )
                :
                    (
                        e.style.position = "fixed",
                        e.style.width = "stretch",
                        e.style.maxWidth = "700px",
                        e.style.height = "65vh",
                        e.style.bottom = "0",
                        e.style.right = "0",
                        e.style.top = "", e.style.left = ""
                    )
            )
            : console.error("iframe not found")
        ),
        "closeChat" === t.data && e && s &&
            (
                e.style.display = "none",
                e.style.pointerEvents = "none",
                e.contentWindow.postMessage("closeChat", "*"),
                s.contentWindow.postMessage("closeChat", "*")
            )

        // Action whenever the user send a message to the chatbot
        if (t.data === "messageSent") {
            let ajaxRequest = jQuery.ajax({
                url: ajaxurl,
                type: "POST",
                timeout: 10000, // 10 seconds timeout
                context: document.body,
                data: {
                    _ajax_nonce: "<?php echo esc_html(wp_create_nonce("lws_tools_ratelimit")); ?>",
                    action: "lws_tools_on_message_sent",
                },
                success: function(data) {
                    if (data === null || typeof data != 'string') {
                        return 0;
                    }

                    try {
                        var returnData = JSON.parse(data);
                    } catch (e) {
                        console.error(e);
                        returnData = {
                            'code': "NOT_JSON",
                            'data': "FAIL"
                        };
                    }

                    // switch (returnData['code']) {
                    //     case "SUCCESS":
                    //         break;
                    //     case "LIMIT_JUST_REACHED":
                    //     case "LIMIT":
                    //         block.innerHTML = '<div class="lws_tools_assistant_button_off" title="<?php esc_html_e('You reached your quota (100 messages) for this month.', 'lws-tools'); ?>"></div>';
                    //         break;
                    //     default:
                    //         console.log(returnData);
                    //         break;
                    // }
                },
                error: function(error) {
                    console.error(error);
                }
            });
        }
    });

    // Show tooltip when clicking on deactivated chatbot
    document.addEventListener('click', function(e) {
        if (e.target.closest('#opengpt_chatbot_deactivated')) {
            const element = e.target.closest('#opengpt_chatbot_deactivated');
            const tooltip = document.createElement('div');
            tooltip.textContent = element.getAttribute('title');
            tooltip.style.cssText = 'position: fixed; background: #333; color: white; padding: 8px 12px; border-radius: 4px; font-size: 14px; z-index: 10000; pointer-events: none; max-width: 250px; height: fit-content;';

            const rect = element.getBoundingClientRect();
            const tooltipWidth = 250; // Approximate tooltip width
            const tooltipHeight = 60; // Approximate tooltip height

            let top = rect.top - tooltipHeight - 5;
            let left = rect.left;

            // Adjust if tooltip would go above viewport
            if (top < 0) {
                top = rect.bottom + 5;
            }

            // Adjust if tooltip would go beyond right edge
            if (left + tooltipWidth > window.innerWidth) {
                left = window.innerWidth - tooltipWidth - 50;
            }

            // Adjust if tooltip would go beyond left edge
            if (left < 0) {
                left = 10;
            }

            tooltip.style.top = top + 'px';
            tooltip.style.left = left + 'px';

            document.body.appendChild(tooltip);

            setTimeout(() => {
                if (tooltip.parentNode) {
                    tooltip.parentNode.removeChild(tooltip);
                }
            }, 3000);
        }
    });
</script>