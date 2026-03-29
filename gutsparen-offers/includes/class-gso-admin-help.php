<?php

class GSO_Admin_Help {

    public function __construct() {
        add_action('admin_notices', [$this, 'show_shortcode_help']);
    }

    public function show_shortcode_help() {
        $screen = get_current_screen();

        if (!$screen || $screen->post_type !== 'gso_offer') {
            return;
        }

        ?>
        <div class="notice notice-info">
            <p><strong>GutSparen Shortcodes</strong></p>

            <p><code>[gutsparen_banner id="123"]</code> -> Show a specific offer banner</p>
            <p><code>[gutsparen_banner]</code> -> Show the highest-priority active offer</p>
            <p><code>[gutsparen_banner category="technik"]</code> -> Show the highest-priority active offer from a category</p>

            <hr>

            <p><code>[gutsparen_offers_overview]</code> -> Show all active, non-expired offers in the overview grid</p>
            <p><code>[gutsparen_offers_overview category="technik"]</code> -> Show only offers from one category in the overview grid</p>

            <p><strong>Recommended usage:</strong> Create the GutSparen page in Divi, then place <code>[gutsparen_offers_overview]</code> inside a Code or Text module where the offers should appear.</p>
        </div>
        <?php
    }
}
