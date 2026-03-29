<?php
/**
 * Plugin Name: GutSparen Offers
 * Description: Manage and display GutSparen discount offers as banners.
 * Version: 1.0
 * Author: Anouar Ben Hamza
 */

if (!defined('ABSPATH')) {
    exit; // prevent direct access
}

define('GSO_PATH', plugin_dir_path(__FILE__));
define('GSO_URL', plugin_dir_url(__FILE__));

require_once GSO_PATH . 'includes/class-gso-cpt.php';
require_once GSO_PATH . 'includes/class-gso-admin-columns.php';
require_once GSO_PATH . 'includes/class-gso-admin-help.php';
require_once GSO_PATH . 'includes/class-gso-assets.php';
require_once GSO_PATH . 'includes/class-gso-meta-boxes.php';
require_once GSO_PATH . 'includes/class-gso-shortcodes.php';

function gso_init_plugin() {
    new GSO_CPT();
    new GSO_Meta_Boxes();
    new GSO_Shortcodes();
    new GSO_Admin_Columns();
    new GSO_Assets();
    new GSO_Admin_Help();
}

gso_init_plugin();
