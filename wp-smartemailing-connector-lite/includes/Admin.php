<?php
namespace SmartEmailingConnector;

if (!defined('ABSPATH')) { exit; }

class Admin {
    const OPT_KEY = 'secl_options';

    public static function init() {
        add_action('admin_menu', [__CLASS__, 'menu']);
        add_action('admin_init', [__CLASS__, 'register_settings']);
    }

    public static function defaults() : array {
        return [
            'api_base'   => '',
            'api_key'    => '',
            'api_user'   => '',
            'list_id'    => '',
            'autoinsert' => 0,
            'insert_after_paragraph' => 3,
            'popup_enabled' => 0,
            'popup_delay_ms' => 5000,
            'popup_frequency_hours' => 24,
        ];
    }

    public static function get_options() : array {
        $defaults = self::defaults();
        $opts = get_option(self::OPT_KEY, []);
        if (!is_array($opts)) $opts = [];
        return array_merge($defaults, $opts);
    }

    public static function menu() {
        add_options_page(
            __('SmartEmailing Connector', 'smartemailing-connector-lite'),
            __('SmartEmailing', 'smartemailing-connector-lite'),
            'manage_options',
            'secl-settings',
            [__CLASS__, 'render_page']
        );
    }

    public static function register_settings() {
        register_setting('secl_settings', self::OPT_KEY, [__CLASS__, 'sanitize']);

        add_settings_section('secl_api', __('API Settings', 'smartemailing-connector-lite'), '__return_false', 'secl-settings');
        add_settings_field('api_base', __('API Base URL', 'smartemailing-connector-lite'), [__CLASS__, 'field_text'], 'secl-settings', 'secl_api', ['key' => 'api_base', 'placeholder' => 'https://api.example.com/v3/contacts']);
        add_settings_field('api_user', __('API Username (email)', 'smartemailing-connector-lite'), [__CLASS__, 'field_text'], 'secl-settings', 'secl_api', ['key' => 'api_user']);
        add_settings_field('api_key', __('API Key', 'smartemailing-connector-lite'), [__CLASS__, 'field_text'], 'secl-settings', 'secl_api', ['key' => 'api_key']);
        add_settings_field('list_id', __('Default List ID', 'smartemailing-connector-lite'), [__CLASS__, 'field_text'], 'secl-settings', 'secl_api', ['key' => 'list_id']);

        add_settings_section('secl_ui', __('UI Behaviour', 'smartemailing-connector-lite'), '__return_false', 'secl-settings');
        add_settings_field('autoinsert', __('Auto-insert form after N paragraphs', 'smartemailing-connector-lite'), [__CLASS__, 'field_checkbox'], 'secl-settings', 'secl_ui', ['key' => 'autoinsert']);
        add_settings_field('insert_after_paragraph', __('Insert after paragraph #', 'smartemailing-connector-lite'), [__CLASS__, 'field_number'], 'secl-settings', 'secl_ui', ['key' => 'insert_after_paragraph', 'min' => 1, 'max' => 20]);
        add_settings_field('popup_enabled', __('Enable popup', 'smartemailing-connector-lite'), [__CLASS__, 'field_checkbox'], 'secl-settings', 'secl_ui', ['key' => 'popup_enabled']);
        add_settings_field('popup_delay_ms', __('Popup delay (ms)', 'smartemailing-connector-lite'), [__CLASS__, 'field_number'], 'secl-settings', 'secl_ui', ['key' => 'popup_delay_ms', 'min' => 0, 'step' => 100]);
        add_settings_field('popup_frequency_hours', __('Popup frequency (hours)', 'smartemailing-connector-lite'), [__CLASS__, 'field_number'], 'secl-settings', 'secl_ui', ['key' => 'popup_frequency_hours', 'min' => 1]);
    }

    public static function sanitize($input) {
        $out = self::get_options();
        foreach ($out as $k => $v) {
            if (!isset($input[$k])) continue;
            switch ($k) {
                case 'autoinsert':
                case 'popup_enabled':
                    $out[$k] = !empty($input[$k]) ? 1 : 0;
                    break;
                case 'insert_after_paragraph':
                case 'popup_delay_ms':
                case 'popup_frequency_hours':
                    $out[$k] = max(0, intval($input[$k]));
                    break;
                default:
                    $out[$k] = sanitize_text_field($input[$k]);
            }
        }
        return $out;
    }

    public static function field_text($args) {
        $opts = self::get_options();
        $key = esc_attr($args['key']);
        $val = esc_attr($opts[$key] ?? '');
        $placeholder = isset($args['placeholder']) ? ' placeholder="' . esc_attr($args['placeholder']) . '"' : '';
        echo '<input type="text" class="regular-text" name="'. self::OPT_KEY .'['.$key.']" value="'.$val.'"'.$placeholder.'/>';
    }

    public static function field_number($args) {
        $opts = self::get_options();
        $key = esc_attr($args['key']);
        $val = intval($opts[$key] ?? 0);
        $min = isset($args['min']) ? ' min="'.intval($args['min']).'"' : '';
        $max = isset($args['max']) ? ' max="'.intval($args['max']).'"' : '';
        $step = isset($args['step']) ? ' step="'.intval($args['step']).'"' : '';
        echo '<input type="number" name="'. self::OPT_KEY .'['.$key.']" value="'.$val.'"'.$min.$max.$step.'/>';
    }

    public static function field_checkbox($args) {
        $opts = self::get_options();
        $key = esc_attr($args['key']);
        $checked = !empty($opts[$key]) ? ' checked' : '';
        echo '<label><input type="checkbox" name="'. self::OPT_KEY .'['.$key.']" value="1"'.$checked.'/> ' . esc_html__('Enabled', 'smartemailing-connector-lite') . '</label>';
    }

    public static function render_page() {
        if (!current_user_can('manage_options')) return;
        echo '<div class="wrap">';
        echo '<h1>'. esc_html__('SmartEmailing Connector', 'smartemailing-connector-lite') .'</h1>';
        echo '<form method="post" action="options.php">';
        settings_fields('secl_settings');
        do_settings_sections('secl-settings');
        submit_button();
        echo '</form>';
        echo '</div>';
    }
}
