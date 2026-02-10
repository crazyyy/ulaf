<?php

namespace AdminCustomDescription;

use WP_Error;

use function __;
use function current_user_can;
use function is_wp_error;
use function json_decode;
use function status_header;
use function wp_send_json;
use function wp_verify_nonce;

class AjaxBase
{

    protected $user_capability;
    protected $nonce_key;


    /**
     * Checks whether the request is valid. Verifies the nonce and if capability
     * is set, checks whether the current user has this capability
     *
     * @param boolean $check_nonce whether to check the nonce or not
     * @param string  $capability  (optional) when set, it will check whether the current user
     *                             has this capability
     *
     * @return boolean|WP_Error
     */
    private function is_request_valid($check_nonce = false, $capability = null)
    {
        if ($capability === null) {
            $capability = $this->user_capability;
        }
        new WP_Error();
        if ( ! current_user_can($capability)) {
            return new WP_Error(
                'acd_not_allowed',
                __(
                    'You are not allowed to perform this action',
                    'admin-custom-description'
                )
            );
        }
        if ($check_nonce
            && ! isset( $_REQUEST['nonce'] ) && ! wp_verify_nonce(
                sanitize_text_field( wp_unslash($_REQUEST['nonce'])),
                $this->nonce_key
            )
        ) {
            return new WP_Error(
                'acd_not_allowed',
                __('Nonce did not verify', 'admin-custom-description')
            );
        }

        return true;
    }


    /**
     * Converts array values that contain numbers as strings to integers
     *
     * @param array $arr the array of values
     *
     * @return array
     */
    protected function array_values_to_integer($arr)
    {
        if (empty($arr)) {
            return array();
        }

        $new_arr = array();
        foreach ($arr as $key => $value) {
            $new_arr[$key] = intval($value);
        }

        return $new_arr;
    }


    /**
     * Checks whether the required $_POST params exist. If they don't it
     * responds with an error and stops the execution
     *
     * @param array $required_params the required param keys
     *
     * @return void
     */
    protected function validate_required_post_params($required_params)
    {
        if (! isset( $_REQUEST['nonce'] ) || ! wp_verify_nonce(
                sanitize_text_field( wp_unslash($_REQUEST['nonce'])),$this->nonce_key)){
            return;
        }
        foreach ($required_params as $param) {
            if (empty($_POST[$param])) {
                $this->respond_error(
                    __('Missing required param: ', 'admin-custom-description')
                    . $param
                );
            }
        }
    }

    /**
     * Checks whether the required $_GET params exist. If they don't it
     * responds with an error and stops the execution
     *
     * @param array $required_params the required param keys
     *
     * @return void
     */
    protected function validate_required_get_params($required_params)
    {
        if (! isset( $_REQUEST['nonce'] ) || ! wp_verify_nonce(
                sanitize_text_field( wp_unslash($_REQUEST['nonce'])),$this->nonce_key)){
            return;
        }
        foreach ($required_params as $param) {
            if (empty($_GET[$param])) {
                $this->respond_error(
                    __('Missing required param: ', 'admin-custom-description')
                    . $param
                );
            }
        }
    }

    /**
     * Verifies the current request. If the request is not valid, it responds
     * with an error and stops the execution
     *
     * @param string $capability (optional) when set, it will check whether the current user
     *                           has this capability
     *
     * @return boolean true if the request is valid
     */
    protected function verify_request($capability = null)
    {
        $valid = $this->is_request_valid(true, $capability);
        if (is_wp_error($valid)) {
            $this->respond_error($valid->get_error_message());
        }

        return true;
    }

    /**
     * Responds with an error to the request. Stops the execution.
     *
     * @param string $message (optional) The error message to respond with
     *
     * @return void
     */
    protected function respond_error(
        $message = 'Failed to execute your request',
        $data = null
    ) {
        if ($message == 'Failed to execute your request') {
            $message = __(
                'Failed to execute your request',
                'admin-custom-description'
            );
        }
        status_header(400);
        $res = array('error' => $message);
        if ( ! empty($data)) {
            $res['info'] = $data;
        }
        wp_send_json($res);
    }

    /**
     * Responds with an error to the success. Stops the execution.
     *
     * @param mixed $data (optional) Data to return in the response. If not set,
     *                    a success:true response will be returned
     *
     * @return void
     * @noinspection GrazieInspection
     */
    protected function respond_success($data = array())
    {
        $res = (empty($data) && ! is_array($data)) || $data === true
            ? array('success' => true) : $data;
        wp_send_json($res);
    }

    /**
     * Responds to the request - it could be a success or error response
     *
     * @param mixed $res this defines whether the response is successful depending on its value:
     *                   - WP_Error - responds with an error, setting the message of the error in the response
     *                   - false - responds with an error
     *                   - everything else - responds with success, setting the value of $res in the response
     *
     * @return void
     */
    protected function respond($res)
    {
        if (is_wp_error($res)) {
            $this->respond_error(
                $res->get_error_message(),
                $res->get_error_data()
            );
        } elseif ($res === false) {
            $this->respond_error();
        } else {
            $this->respond_success($res);
        }
    }

}