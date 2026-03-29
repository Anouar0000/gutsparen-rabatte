<?php

class GSO_CPT {

    public function __construct() {
        add_action('init', [$this, 'register_offer_cpt']);
        add_action('init', [$this, 'register_offer_taxonomy']);
        add_action('add_meta_boxes', [$this, 'remove_default_title_support']);
    }

    public function register_offer_cpt() {

        register_post_type('gso_offer', [
            'labels' => [
                'name' => 'GutSparen Offers',
                'singular_name' => 'Offer',
                'add_new_item' => 'Add New Offer',
                'edit_item' => 'Edit Offer'
            ],
            'public' => false,
            'show_ui' => true,
            'menu_icon' => 'dashicons-megaphone',
            'supports' => ['title', 'thumbnail'],
        ]);

    }


    public function remove_default_title_support() {
        remove_post_type_support('gso_offer', 'title');
    }
    public function register_offer_taxonomy() {
        register_taxonomy('gso_offer_category', 'gso_offer', [
            'labels' => [
                'name' => 'Offer Categories',
                'singular_name' => 'Offer Category',
            ],
            'public' => false,
            'show_ui' => true,
            'hierarchical' => true,
        ]);
    }

}

