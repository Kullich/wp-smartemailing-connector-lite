<?php
if (!function_exists('secl_render_form')) {
    function secl_render_form($atts = []) {
        echo do_shortcode('[smartemailing_form]');
    }
}
