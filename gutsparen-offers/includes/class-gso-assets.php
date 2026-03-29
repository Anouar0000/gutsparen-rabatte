<?php

class GSO_Assets {

    public function __construct() {
        add_action('admin_enqueue_scripts', [$this, 'enqueue_admin_assets']);
        add_action('wp_enqueue_scripts', [$this, 'enqueue_public_assets']);
    }

    public function enqueue_admin_assets($hook) {
        global $post_type;

        if ($post_type === 'gso_offer') {
            $admin_css_path = GSO_PATH . 'admin/css/gso-admin.css';
            $admin_js_path = GSO_PATH . 'admin/js/gso-admin.js';

            wp_enqueue_media();

            wp_enqueue_style(
                'gso-admin-css',
                GSO_URL . 'admin/css/gso-admin.css',
                [],
                file_exists($admin_css_path) ? filemtime($admin_css_path) : null
            );

            wp_enqueue_script(
                'gso-admin-js',
                GSO_URL . 'admin/js/gso-admin.js',
                ['jquery'],
                file_exists($admin_js_path) ? filemtime($admin_js_path) : null,
                true
            );
        }
    }

    public function enqueue_public_assets() {
        $public_css_path = GSO_PATH . 'public/css/gso-public.css';
        $public_js_path = GSO_PATH . 'public/js/gso-public.js';

        wp_enqueue_style(
            'gso-public-css',
            GSO_URL . 'public/css/gso-public.css',
            [],
            file_exists($public_css_path) ? filemtime($public_css_path) : null
        );

        wp_enqueue_script(
            'gso-public-js',
            GSO_URL . 'public/js/gso-public.js',
            [],
            file_exists($public_js_path) ? filemtime($public_js_path) : null,
            true
        );

        wp_localize_script(
            'gso-public-js',
            'gsoPublic',
            [
                'ajaxUrl' => admin_url('admin-ajax.php'),
                'nonce'   => wp_create_nonce('gso_filter_offers'),
            ]
        );
    }
}
