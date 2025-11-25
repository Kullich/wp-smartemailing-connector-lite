<?php
namespace SmartEmailingConnector;

if (!defined('ABSPATH')) { exit; }

class Ajax {
    public static function init() {
        add_action('wp_ajax_nopriv_secl_submit', [__CLASS__, 'submit']);
        add_action('wp_ajax_secl_submit', [__CLASS__, 'submit']);
    }

    public static function submit() {
        if (!check_ajax_referer('secl_submit', '_wpnonce', false)) {
            wp_send_json_error(['message' => __('Invalid nonce.', 'smartemailing-connector-lite')], 400);
        }

        $email = isset($_POST['email']) ? sanitize_email(wp_unslash($_POST['email'])) : '';
        $list  = isset($_POST['list']) ? sanitize_text_field(wp_unslash($_POST['list'])) : '';

        if (empty($email) || !is_email($email)) {
            wp_send_json_error(['message' => __('Please enter a valid email.', 'smartemailing-connector-lite')], 422);
        }

        $opts = Admin::get_options();
        $api_base = $opts['api_base'];
        $api_user = $opts['api_user'];
        $api_key  = $opts['api_key'];
        $list_id  = $list ?: $opts['list_id'];

        if (empty($api_base) || empty($api_key)) {
            wp_send_json_error(['message' => __('Service not configured.', 'smartemailing-connector-lite')], 500);
        }

        $body = [
            'email' => $email,
            'list'  => $list_id,
        ];

        $args = [
            'method'  => 'POST',
            'headers' => [
                'Content-Type' => 'application/json',
                // Keep it generic: some services use Basic, others use header token. Adjust accordingly on the API side.
                'Authorization' => 'Bearer ' . $api_key,
                'X-User' => $api_user,
            ],
            'timeout' => 10,
            'body'    => wp_json_encode($body),
        ];

        $response = wp_remote_request(trailingslashit($api_base), $args);
        if (is_wp_error($response)) {
            wp_send_json_error(['message' => $response->get_error_message()], 502);
        }

        $code = wp_remote_retrieve_response_code($response);
        $resp_body = wp_remote_retrieve_body($response);
        $ok = $code >= 200 && $code < 300;

        if (!$ok) {
            wp_send_json_error(['message' => __('Subscription failed.', 'smartemailing-connector-lite'), 'code' => $code, 'body' => $resp_body], $code ?: 500);
        }

        wp_send_json_success(['message' => __('Thanks! Please check your inbox.', 'smartemailing-connector-lite')]);
    }
}
