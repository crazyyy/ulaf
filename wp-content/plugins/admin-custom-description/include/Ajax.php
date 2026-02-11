<?php

namespace AdminCustomDescription;

class Ajax extends AjaxBase
{
    protected $user_capability;
    protected $nonce_key;

    public function __construct($user_capability, $nonce_key)
    {
        $this->user_capability = $user_capability;
        $this->nonce_key       = $nonce_key;

        add_action(
            'wp_ajax_acd_edit_plugin_description',
            array($this, 'acd_edit_plugin_description')
        );
    }

    public function acd_edit_plugin_description()
    {
        $this->verify_request();
        $this->validate_required_post_params(array('plugin'));
        if( ! isset( $_REQUEST['nonce'] ) || ! wp_verify_nonce(
            sanitize_text_field( wp_unslash($_REQUEST['nonce'])), $this->nonce_key )){
            return;
        }
        $plugin = sanitize_text_field( $_POST['plugin'] );
        $comment = sanitize_textarea_field($_POST['comment']);

        update_option("acd_{$plugin}", $comment);
    }


}