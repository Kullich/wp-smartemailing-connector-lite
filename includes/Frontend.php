<?php
namespace SmartEmailingConnector;

if (!defined('ABSPATH')) { exit; }

class Frontend {
    public static function init() {
        add_shortcode('smartemailing_form', [__CLASS__, 'shortcode']);
        add_filter('the_content', [__CLASS__, 'auto_insert_form']);
        add_action('wp_enqueue_scripts', [__CLASS__, 'assets']);
        add_action('wp_footer', [__CLASS__, 'maybe_render_popup']);
    }

    public static function assets() {
        wp_register_style('secl-css', SECL_URL . 'assets/css/frontend.css', [], SECL_VERSION);
        wp_register_script('secl-js', SECL_URL . 'assets/js/frontend.js', ['jquery'], SECL_VERSION, true);
        $opts = Admin::get_options();
        wp_localize_script('secl-js', 'SECL', [
            'ajax_url' => admin_url('admin-ajax.php'),
            'nonce'    => wp_create_nonce('secl_submit'),
            'popup'    => [
                'enabled' => !empty($opts['popup_enabled']),
                'delay'   => intval($opts['popup_delay_ms']),
                'freq'    => intval($opts['popup_frequency_hours']),
            ],
        ]);
        wp_enqueue_style('secl-css');
        wp_enqueue_script('secl-js');
    }

    public static function shortcode($atts = [], $content = '') {
        $atts = shortcode_atts([
            'list' => '',
            'title' => __('Get updates', 'smartemailing-connector-lite'),
            'button' => __('Subscribe', 'smartemailing-connector-lite'),
        ], $atts, 'smartemailing_form');

        $list = esc_attr($atts['list']);
        $title = esc_html($atts['title']);
        $button = esc_html($atts['button']);

        ob_start(); ?>
        <form class="secl-form" method="post" novalidate>
            <div class="secl-title"><?php echo $title; ?></div>
            <div class="secl-row">
                <label>
                    <span class="screen-reader-text"><?php esc_html_e('Email', 'smartemailing-connector-lite'); ?></span>
                    <input type="email" name="email" placeholder="<?php esc_attr_e('you@example.com', 'smartemailing-connector-lite'); ?>" required />
                </label>
            </div>
            <input type="hidden" name="list" value="<?php echo $list; ?>" />
            <input type="hidden" name="action" value="secl_submit" />
            <input type="hidden" name="_wpnonce" value="<?php echo wp_create_nonce('secl_submit'); ?>" />
            <button type="submit" class="secl-btn"><?php echo $button; ?></button>
            <div class="secl-message" aria-live="polite"></div>
        </form>
        <?php
        return ob_get_clean();
    }

    public static function auto_insert_form($content) {
        if (!is_singular() || !in_the_loop() || !is_main_query()) return $content;
        $opts = Admin::get_options();
        if (empty($opts['autoinsert'])) return $content;

        $after = max(1, intval($opts['insert_after_paragraph']));
        $form = self::shortcode([], '');
        $paragraphs = explode('</p>', $content);
        $out = '';
        foreach ($paragraphs as $i => $p) {
            if (trim($p) === '') continue;
            $out .= $p . '</p>';
            if ($i + 1 == $after) {
                $out .= $form;
            }
        }
        return $out ?: $content;
    }

    public static function maybe_render_popup() {
        $opts = Admin::get_options();
        if (empty($opts['popup_enabled'])) return;
        echo '<div class="secl-popup" id="secl-popup" hidden>';
        echo '<div class="secl-popup-inner"><button class="secl-close" type="button" aria-label="'. esc_attr__('Close', 'smartemailing-connector-lite') .'">&times;</button>';
        echo self::shortcode([], '');
        echo '</div></div>';
    }
}
