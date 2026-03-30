<?php

class GSO_Meta_Boxes {

    public function __construct() {
        add_action('add_meta_boxes', [$this, 'add_meta_boxes']);
        add_action('save_post', [$this, 'save_meta_boxes']);
    }

    public function add_meta_boxes() {
        add_meta_box(
            'gso_offer_details',
            'Offer Details',
            [$this, 'render_offer_details_box'],
            'gso_offer',
            'normal',
            'default'
        );
    }

    public function render_offer_details_box($post) {
        wp_nonce_field('gso_save_offer_details', 'gso_offer_nonce');

        $offer_title        = $post->post_title;
        $company_name       = get_post_meta($post->ID, 'gso_company_name', true);
        $short_description  = get_post_meta($post->ID, 'gso_short_description', true);
        $long_description   = get_post_meta($post->ID, 'gso_long_description', true);
        $discount_code      = get_post_meta($post->ID, 'gso_discount_code', true);
        $show_discount_code = get_post_meta($post->ID, 'gso_show_discount_code', true);
        $target_url         = get_post_meta($post->ID, 'gso_target_url', true);
        $is_premium         = get_post_meta($post->ID, 'gso_is_premium', true);
        $is_active          = get_post_meta($post->ID, 'gso_is_active', true);
        $expiry_date        = get_post_meta($post->ID, 'gso_expiry_date', true);
        $priority           = get_post_meta($post->ID, 'gso_priority', true);
        $logo_id            = intval(get_post_meta($post->ID, 'gso_logo_id', true));
        $savings_amount     = get_post_meta($post->ID, 'gso_savings_amount', true);
        $logo_preview       = $logo_id ? wp_get_attachment_image($logo_id, 'medium', false, ['class' => 'gso-logo-preview-image']) : '';

        ?>
        <table class="form-table">
            <tr>
                <th><label for="gso_offer_title">Offer Title</label></th>
                <td><input type="text" id="gso_offer_title" name="gso_offer_title" value="<?php echo esc_attr($offer_title); ?>" class="regular-text"></td>
            </tr>

            <tr>
                <th><label for="gso_company_name">Company Name</label></th>
                <td>
                    <input type="text" id="gso_company_name" name="gso_company_name" value="<?php echo esc_attr($company_name); ?>" class="regular-text">
                    <p class="description">Used as the logo fallback in the banner if no logo image is selected.</p>
                </td>
            </tr>

            <tr>
                <th>Banner Logo</th>
                <td>
                    <div class="gso-logo-field">
                        <input type="hidden" id="gso_logo_id" name="gso_logo_id" value="<?php echo esc_attr($logo_id); ?>">
                        <div class="gso-logo-preview<?php echo $logo_preview ? '' : ' is-empty'; ?>">
                            <?php if ($logo_preview) : ?>
                                <?php echo $logo_preview; ?>
                            <?php else : ?>
                                <span class="gso-logo-placeholder">No logo selected</span>
                            <?php endif; ?>
                        </div>
                        <div class="gso-logo-actions">
                            <button type="button" class="button gso-logo-upload">Select logo</button>
                            <button type="button" class="button gso-logo-remove"<?php echo $logo_preview ? '' : ' hidden'; ?>>Remove logo</button>
                        </div>
                        <p class="description">Used in the banner shortcode. The overview card image still uses the normal Featured image box.</p>
                    </div>
                </td>
            </tr>

            <tr>
                <th><label for="gso_short_description">Short Description</label></th>
                <td>
                    <textarea id="gso_short_description" name="gso_short_description" rows="4" class="large-text"><?php echo esc_textarea($short_description); ?></textarea>
                    <p class="description">Used for banners and smaller placements.</p>
                </td>
            </tr>

            <tr>
                <th><label for="gso_long_description">Long Description</label></th>
                <td>
                    <textarea id="gso_long_description" name="gso_long_description" rows="6" class="large-text"><?php echo esc_textarea($long_description); ?></textarea>
                    <p class="description">Used on the overview page cards. If empty, the short description is used as fallback.</p>
                </td>
            </tr>

            <tr>
                <th><label for="gso_discount_code">Discount Code</label></th>
                <td><input type="text" id="gso_discount_code" name="gso_discount_code" value="<?php echo esc_attr($discount_code); ?>" class="regular-text"></td>
            </tr>

            <tr>
                <th>Show Discount Code</th>
                <td>
                    <label>
                        <input type="checkbox" name="gso_show_discount_code" value="1" <?php checked($show_discount_code !== '0', true); ?>>
                        Show the discount code publicly in the banner and overview card
                    </label>
                </td>
            </tr>

            <tr>
                <th><label for="gso_savings_amount">Savings Amount (EUR)</label></th>
                <td>
                    <input type="text" id="gso_savings_amount" name="gso_savings_amount" value="<?php echo esc_attr($savings_amount); ?>" class="small-text" inputmode="decimal">
                </td>
            </tr>

            <tr>
                <th><label for="gso_target_url">Target URL</label></th>
                <td><input type="url" id="gso_target_url" name="gso_target_url" value="<?php echo esc_attr($target_url); ?>" class="regular-text"></td>
            </tr>

            <tr>
                <th><label for="gso_expiry_date">Expiry Date</label></th>
                <td><input type="date" id="gso_expiry_date" name="gso_expiry_date" value="<?php echo esc_attr($expiry_date); ?>"></td>
            </tr>

            <tr>
                <th><label for="gso_priority">Priority</label></th>
                <td><input type="number" id="gso_priority" name="gso_priority" value="<?php echo esc_attr($priority); ?>" min="0" step="1"></td>
            </tr>

            <tr>
                <th>Premium Offer</th>
                <td>
                    <label>
                        <input type="checkbox" name="gso_is_premium" value="1" <?php checked($is_premium, '1'); ?>>
                        Yes
                    </label>
                </td>
            </tr>

            <tr>
                <th>Active</th>
                <td>
                    <label>
                        <input type="checkbox" name="gso_is_active" value="1" <?php checked($is_active, '1'); ?>>
                        Yes
                    </label>
                </td>
            </tr>
        </table>
        <?php
    }

    public function save_meta_boxes($post_id) {
        if (!isset($_POST['gso_offer_nonce']) || !wp_verify_nonce($_POST['gso_offer_nonce'], 'gso_save_offer_details')) {
            return;
        }

        if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
            return;
        }

        if (!current_user_can('edit_post', $post_id)) {
            return;
        }

        remove_action('save_post', [$this, 'save_meta_boxes']);

        wp_update_post([
            'ID' => $post_id,
            'post_title' => sanitize_text_field($_POST['gso_offer_title'] ?? ''),
        ]);

        add_action('save_post', [$this, 'save_meta_boxes']);

        update_post_meta($post_id, 'gso_company_name', sanitize_text_field($_POST['gso_company_name'] ?? ''));
        update_post_meta($post_id, 'gso_short_description', wp_kses_post(wp_unslash($_POST['gso_short_description'] ?? '')));
        update_post_meta($post_id, 'gso_long_description', wp_kses_post(wp_unslash($_POST['gso_long_description'] ?? '')));
        update_post_meta($post_id, 'gso_discount_code', sanitize_text_field($_POST['gso_discount_code'] ?? ''));
        update_post_meta($post_id, 'gso_show_discount_code', isset($_POST['gso_show_discount_code']) ? '1' : '0');
        update_post_meta($post_id, 'gso_target_url', esc_url_raw($_POST['gso_target_url'] ?? ''));
        update_post_meta($post_id, 'gso_expiry_date', sanitize_text_field($_POST['gso_expiry_date'] ?? ''));
        update_post_meta($post_id, 'gso_priority', intval($_POST['gso_priority'] ?? 0));
        update_post_meta($post_id, 'gso_is_premium', isset($_POST['gso_is_premium']) ? '1' : '0');
        update_post_meta($post_id, 'gso_is_active', isset($_POST['gso_is_active']) ? '1' : '0');
        update_post_meta($post_id, 'gso_logo_id', intval($_POST['gso_logo_id'] ?? 0));

        if (array_key_exists('gso_savings_amount', $_POST)) {
            $savings_amount = sanitize_text_field(wp_unslash($_POST['gso_savings_amount']));
            $savings_amount = str_replace(',', '.', $savings_amount);
            update_post_meta($post_id, 'gso_savings_amount', $savings_amount);
        }
    }
}
