<?php

class GSO_Admin_Columns {

    public function __construct() {
        add_filter('manage_gso_offer_posts_columns', [$this, 'add_columns']);
        add_action('manage_gso_offer_posts_custom_column', [$this, 'render_columns'], 10, 2);
    }

    public function add_columns($columns) {
        $new_columns = [];

        foreach ($columns as $key => $label) {
            if ($key === 'title') {
                $new_columns['title'] = $label;
                $new_columns['gso_offer_id'] = 'ID';
                $new_columns['gso_premium'] = 'Premium';
                $new_columns['gso_active'] = 'Active';
                $new_columns['gso_expiry'] = 'Expiry Date';
                $new_columns['gso_priority'] = 'Priority';
                $new_columns['gso_savings_amount'] = 'Sparen';
                $new_columns['gso_shortcode'] = 'Shortcode';
            } else {
                $new_columns[$key] = $label;
            }
        }

        return $new_columns;
    }

    public function render_columns($column, $post_id) {
        switch ($column) {
            case 'gso_offer_id':
                echo esc_html($post_id);
                break;

            case 'gso_premium':
                echo get_post_meta($post_id, 'gso_is_premium', true) === '1' ? 'Yes' : 'No';
                break;

            case 'gso_active':
                echo get_post_meta($post_id, 'gso_is_active', true) === '1' ? 'Yes' : 'No';
                break;

            case 'gso_expiry':
                echo esc_html(get_post_meta($post_id, 'gso_expiry_date', true) ?: '-');
                break;

            case 'gso_priority':
                echo esc_html(get_post_meta($post_id, 'gso_priority', true) ?: '0');
                break;

            case 'gso_savings_amount':
                $value = get_post_meta($post_id, 'gso_savings_amount', true);
                $euro = html_entity_decode('&#8364;', ENT_QUOTES, 'UTF-8');
                echo esc_html($value !== '' ? $value . ' ' . $euro : '-');
                break;

            case 'gso_shortcode':
                $shortcode = '[gutsparen_banner id="' . $post_id . '"]';
                ?>
                <button
                    type="button"
                    class="button button-secondary gso-copy-shortcode"
                    data-shortcode="<?php echo esc_attr($shortcode); ?>"
                    title="<?php echo esc_attr($shortcode); ?>"
                    aria-label="<?php echo esc_attr('Copy shortcode ' . $shortcode); ?>"
                >
                    Copy shortcode
                </button>
                <?php
                break;
        }
    }
}

