<?php

class GSO_Shortcodes {

    public function __construct() {
        add_shortcode('gutsparen_banner', [$this, 'render_banner_shortcode']);
        add_shortcode('gutsparen_offers_overview', [$this, 'render_offers_overview_shortcode']);
        add_action('wp_ajax_gso_filter_offers', [$this, 'ajax_filter_offers']);
        add_action('wp_ajax_nopriv_gso_filter_offers', [$this, 'ajax_filter_offers']);
    }

    public function render_banner_shortcode($atts) {
        $atts = shortcode_atts([
            'id' => 0,
            'category' => '',
        ], $atts, 'gutsparen_banner');

        $offer_id = intval($atts['id']);
        $category = sanitize_text_field($atts['category']);

        if ($offer_id) {
            $post = get_post($offer_id);

            if (!$post || $post->post_type !== 'gso_offer') {
                return '';
            }

            if (!$this->is_offer_valid($offer_id)) {
                return '';
            }

            return $this->render_banner_html($offer_id);
        }

        $valid_offers = $this->get_filtered_banner_offers($category);

        if (empty($valid_offers)) {
            return '';
        }

        if (count($valid_offers) === 1) {
            return $this->render_banner_html($valid_offers[0]['id']);
        }

        return $this->render_banner_results_html($valid_offers);
    }

    public function render_offers_overview_shortcode($atts) {
        $atts = shortcode_atts([
            'category' => '',
        ], $atts, 'gutsparen_offers_overview');

        $selected_category = !empty($_GET['gso_category'])
            ? sanitize_text_field($_GET['gso_category'])
            : sanitize_text_field($atts['category']);

        $search_term = !empty($_GET['gso_search'])
            ? sanitize_text_field($_GET['gso_search'])
            : '';

        $valid_offers = $this->get_filtered_overview_offers($selected_category, $search_term);

        $terms = get_terms([
            'taxonomy'   => 'gso_offer_category',
            'hide_empty' => false,
        ]);

        $current_path = isset($_SERVER['REQUEST_URI']) ? wp_unslash($_SERVER['REQUEST_URI']) : '/';
        $form_action = home_url(strtok($current_path, '?'));
        $preserved_params = $_GET;

        unset($preserved_params['gso_search'], $preserved_params['gso_category']);

        ob_start();
        ?>

        <div class="gso-overview-section">
            <form
                method="get"
                action="<?php echo esc_url($form_action); ?>"
                class="gso-overview-filters"
                data-gso-overview-form
            >
                <?php foreach ($preserved_params as $param_key => $param_value): ?>
                    <?php if (is_array($param_value)) {
                        continue;
                    } ?>
                    <input
                        type="hidden"
                        name="<?php echo esc_attr(sanitize_key($param_key)); ?>"
                        value="<?php echo esc_attr(wp_unslash($param_value)); ?>"
                    >
                <?php endforeach; ?>

                <input
                    type="text"
                    name="gso_search"
                    value="<?php echo esc_attr($search_term); ?>"
                    placeholder="Unternehmen suchen..."
                    class="gso-filter-search"
                >

                <select name="gso_category" class="gso-filter-select">
                    <option value="">Alle Kategorien</option>
                    <?php if (!empty($terms) && !is_wp_error($terms)): ?>
                        <?php foreach ($terms as $term): ?>
                            <option value="<?php echo esc_attr($term->slug); ?>" <?php selected($selected_category, $term->slug); ?>>
                                <?php echo esc_html($term->name); ?>
                            </option>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </select>

                <button type="submit" class="gso-filter-button">Suchen</button>
            </form>

            <div class="gso-overview-results" data-gso-overview-results>
                <?php echo $this->render_overview_results_html($valid_offers); ?>
            </div>
        </div>

        <?php
        return ob_get_clean();
    }

    public function ajax_filter_offers() {
        check_ajax_referer('gso_filter_offers', 'nonce');

        $selected_category = isset($_POST['gso_category'])
            ? sanitize_text_field(wp_unslash($_POST['gso_category']))
            : '';

        $search_term = isset($_POST['gso_search'])
            ? sanitize_text_field(wp_unslash($_POST['gso_search']))
            : '';

        $valid_offers = $this->get_filtered_overview_offers($selected_category, $search_term);

        wp_send_json_success([
            'html' => $this->render_overview_results_html($valid_offers),
        ]);
    }

    private function is_offer_valid($offer_id) {
        $is_active   = get_post_meta($offer_id, 'gso_is_active', true);
        $expiry_date = get_post_meta($offer_id, 'gso_expiry_date', true);

        if ($is_active !== '1') {
            return false;
        }

        if (!empty($expiry_date) && strtotime($expiry_date) < strtotime(date('Y-m-d'))) {
            return false;
        }

        return true;
    }

    private function render_banner_html($offer_id) {
        return $this->render_offer_card_html($offer_id, false);
    }

    private function render_overview_card_html($offer_id) {
        return $this->render_offer_card_html($offer_id, true);
    }

    private function render_offer_card_html($offer_id, $use_long_description) {
        $company_name       = get_post_meta($offer_id, 'gso_company_name', true);
        $short_description  = get_post_meta($offer_id, 'gso_short_description', true);
        $long_description   = get_post_meta($offer_id, 'gso_long_description', true);
        $discount_code      = get_post_meta($offer_id, 'gso_discount_code', true);
        $show_discount_code = $this->should_show_discount_code($offer_id);
        $target_url         = get_post_meta($offer_id, 'gso_target_url', true);
        $savings_amount     = get_post_meta($offer_id, 'gso_savings_amount', true);

        $description = $use_long_description && !empty($long_description)
            ? $long_description
            : $short_description;

        $terms = get_the_terms($offer_id, 'gso_offer_category');
        $category_names = (!empty($terms) && !is_wp_error($terms))
            ? implode(' & ', wp_list_pluck($terms, 'name'))
            : 'Kategorie';

        $image = get_the_post_thumbnail_url($offer_id, 'medium');
        $cta_text = $this->get_savings_label($savings_amount);
        $show_button = !empty($target_url) || $cta_text !== 'Zum Angebot';

        ob_start();
        ?>
        <article class="gso-overview-card">
            <div class="gso-overview-card-shell">
                <div class="gso-overview-card-media">
                    <?php if ($image): ?>
                        <img src="<?php echo esc_url($image); ?>" alt="<?php echo esc_attr($company_name ?: get_the_title($offer_id)); ?>">
                    <?php else: ?>
                        <div class="gso-overview-card-fallback"><?php echo esc_html($company_name ?: get_the_title($offer_id)); ?></div>
                    <?php endif; ?>
                </div>

                <div class="gso-overview-card-body">
                    <div class="gso-overview-category"><?php echo esc_html($category_names); ?></div>
                    <h3 class="gso-overview-title"><?php echo esc_html($company_name ?: get_the_title($offer_id)); ?></h3>

                    <?php if (!empty($description)): ?>
                        <div class="gso-overview-description"><?php echo wp_kses_post(wpautop($description)); ?></div>
                    <?php endif; ?>
                </div>

                <?php if ($show_discount_code && !empty($discount_code)): ?>
                    <div class="gso-overview-code-group">
                        <div class="gso-overview-code-row">
                            <span class="gso-overview-code-value"><?php echo esc_html($discount_code); ?></span>
                            <button type="button" class="gso-overview-copy-button" data-gso-copy="<?php echo esc_attr($discount_code); ?>">
                                Kopieren
                            </button>
                        </div>
                    </div>
                <?php endif; ?>

                <?php if ($show_button): ?>
                    <?php if (!empty($target_url)): ?>
                        <a class="gso-overview-button" href="<?php echo esc_url($target_url); ?>" target="_blank" rel="noopener noreferrer">
                            <?php echo esc_html($cta_text); ?>
                        </a>
                    <?php else: ?>
                        <span class="gso-overview-button gso-overview-button-static">
                            <?php echo esc_html($cta_text); ?>
                        </span>
                    <?php endif; ?>
                <?php endif; ?>
            </div>
        </article>
        <?php
        return ob_get_clean();
    }

    private function get_filtered_banner_offers($category) {
        $query_args = [
            'post_type'      => 'gso_offer',
            'posts_per_page' => -1,
            'post_status'    => 'publish',
            'meta_query'     => [
                [
                    'key'   => 'gso_is_active',
                    'value' => '1',
                ],
            ],
        ];

        if (!empty($category)) {
            $query_args['tax_query'] = [
                [
                    'taxonomy' => 'gso_offer_category',
                    'field'    => 'slug',
                    'terms'    => $category,
                ],
            ];
        }

        $query = new WP_Query($query_args);
        $valid_offers = [];

        if ($query->have_posts()) {
            while ($query->have_posts()) {
                $query->the_post();
                $current_id = get_the_ID();

                if ($this->is_offer_valid($current_id)) {
                    $valid_offers[] = [
                        'id' => $current_id,
                        'priority' => intval(get_post_meta($current_id, 'gso_priority', true)),
                    ];
                }
            }
        }

        wp_reset_postdata();

        usort($valid_offers, function($a, $b) {
            return $a['priority'] <=> $b['priority'];
        });

        return $valid_offers;
    }

    private function get_filtered_overview_offers($selected_category, $search_term) {
        $meta_query = [
            [
                'key'   => 'gso_is_active',
                'value' => '1',
            ],
        ];

        if ($search_term !== '') {
            $meta_query[] = [
                'key'     => 'gso_company_name',
                'value'   => $search_term,
                'compare' => 'LIKE',
            ];
        }

        $query_args = [
            'post_type'      => 'gso_offer',
            'posts_per_page' => -1,
            'post_status'    => 'publish',
            'meta_query'     => $meta_query,
        ];

        if (!empty($selected_category)) {
            $query_args['tax_query'] = [
                [
                    'taxonomy' => 'gso_offer_category',
                    'field'    => 'slug',
                    'terms'    => $selected_category,
                ],
            ];
        }

        $query = new WP_Query($query_args);
        $valid_offers = [];

        if ($query->have_posts()) {
            while ($query->have_posts()) {
                $query->the_post();
                $current_id = get_the_ID();

                if ($this->is_offer_valid($current_id)) {
                    $valid_offers[] = [
                        'id' => $current_id,
                        'priority' => intval(get_post_meta($current_id, 'gso_priority', true)),
                    ];
                }
            }
        }

        wp_reset_postdata();

        usort($valid_offers, function($a, $b) {
            return $a['priority'] <=> $b['priority'];
        });

        return $valid_offers;
    }

    private function render_banner_results_html($valid_offers) {
        ob_start();

        if (empty($valid_offers)) {
            return '';
        }
        ?>
        <div class="gso-overview-slider-wrap" data-gso-slider-wrap>
            <button type="button" class="gso-overview-nav gso-overview-nav-prev" data-gso-slider-prev aria-label="Vorherige Angebote">
                &#8249;
            </button>

            <div class="gso-overview-slider" data-gso-slider>
                <?php foreach ($valid_offers as $offer): ?>
                    <?php echo $this->render_banner_html($offer['id']); ?>
                <?php endforeach; ?>
            </div>

            <button type="button" class="gso-overview-nav gso-overview-nav-next" data-gso-slider-next aria-label="Naechste Angebote">
                &#8250;
            </button>
        </div>
        <?php

        return ob_get_clean();
    }

    private function render_overview_results_html($valid_offers) {
        ob_start();

        if (empty($valid_offers)) {
            ?>
            <div class="gso-overview-empty">No offers available right now.</div>
            <?php

            return ob_get_clean();
        }
        ?>
        <div class="gso-overview-slider-wrap" data-gso-slider-wrap>
            <button type="button" class="gso-overview-nav gso-overview-nav-prev" data-gso-slider-prev aria-label="Vorherige Angebote">
                &#8249;
            </button>

            <div class="gso-overview-slider" data-gso-slider>
                <?php foreach ($valid_offers as $offer): ?>
                    <?php echo $this->render_overview_card_html($offer['id']); ?>
                <?php endforeach; ?>
            </div>

            <button type="button" class="gso-overview-nav gso-overview-nav-next" data-gso-slider-next aria-label="Naechste Angebote">
                &#8250;
            </button>
        </div>
        <?php

        return ob_get_clean();
    }

    private function should_show_discount_code($offer_id) {
        return get_post_meta($offer_id, 'gso_show_discount_code', true) !== '0';
    }

    private function get_savings_label($savings_amount) {
        if ($savings_amount === '' || $savings_amount === null) {
            return 'Zum Angebot';
        }

        $amount = floatval(str_replace(',', '.', (string) $savings_amount));

        if ($amount < 0) {
            return 'Zum Angebot';
        }

        $formatted = floor($amount) == $amount
            ? number_format_i18n($amount, 0)
            : number_format_i18n($amount, 2);

        $euro = html_entity_decode('&#8364;', ENT_QUOTES, 'UTF-8');

        return sprintf('%s %s sparen', $formatted, $euro);
    }
}

