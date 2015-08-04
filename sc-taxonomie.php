<?php
/*
 * Simple Cataloque
 * Class Taxonomies
*/

// Interdire l'acces direct au fichier
if ( ! defined( 'ABSPATH' ) AND !is_user_can(manage_options()) )   exit;

class sc_Taxonomie
{
    private $_TaxName;
    private $_name;
    private $_names;
    private $_post_type;

    public function __construct()
{
    add_filter( 'init', array($this, 'sc_tax_register'));
}
// Déclaration d'une nouvelle taxonomie
    public function sc_tax_register() {
        global $wpdb;
        $results = $wpdb->get_results("SELECT * FROM {$wpdb->prefix}sc_category ", ARRAY_A);
        foreach ($results as $res) {
            if (isset($res['names']) AND isset($res['name']) AND isset($res['post_type'])) {
                $names = $res['names'];
                $name = $res['name'];
                $_post_type = explode(',', $res['post_type']);
                $slug = $res['slug'];
                $labels = array(
                    'name' => _x($names, 'Taxonomy General Name', 'simple-catalogue'),
                    'singular_name' => _x($name, 'Taxonomy Singular Name', 'simple-catalogue'),
                    'menu_name' => __($names, 'simple-catalogue'),
                    'all_items' => __('All', 'simple-catalogue'),
                    'parent_item' => __('Parent', 'simple-catalogue'),
                    'parent_item_colon' => __('Parent:', 'simple-catalogue'),
                    'new_item_name' => __('Name', 'simple-catalogue'),
                    'add_new_item' => __('Add new item', 'simple-catalogue'),
                    'edit_item' => __('Edit', 'simple-catalogue'),
                    'update_item' => __('Update', 'simple-catalogue'),
                    'view_item' => __('View', 'simple-catalogue'),
                    'separate_items_with_commas' => __('Separate width commas', 'simple-catalogue'),
                    'add_or_remove_items' => __('Add or remove', 'simple-catalogue'),
                    'choose_from_most_used' => __('Most used', 'simple-catalogue'),
                    'popular_items' => __('Populars', 'simple-catalogue'),
                    'search_items' => __('Search', 'simple-catalogue'),
                    'not_found' => __('Nothing found', 'simple-catalogue'),
                );
                $args = array(
                    'labels' => $labels,
                    'hierarchical' => true,
                    'public' => true,
                    'show_ui' => true,
                    'show_admin_column' => true,
                    'show_in_nav_menus' => true,
                    'show_tagcloud' => true,
                );
                register_taxonomy($slug, $_post_type, $args);
            }
        }
    }

}