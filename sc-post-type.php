<?php
/*
 * Simple Cataloque
 * Class Post type
*/
//interdire l'acces direct au fichier
if ( ! defined( 'ABSPATH' ) AND !is_user_can(manage_options()) )   exit;

class sc_Post_Type
{
    public function __construct()
    {
        add_action('init', array($this, 'register_post_types'));
    }

    public $_name = 0;
    public $_names = 0;
    public $_icon = 0;
    public $_slug = 0;

    public function register_post_types()
    {
        //cheking options
        $option_menu_link = get_option( 'simple_catalogue_menu_link' );
        if ( $option_menu_link == 1 ) {
            $show_in_menu = true;
        }else {
                $show_in_menu = 'catalogue';
            }
        // Connexion to database
        global $wpdb;
        $results = $wpdb->get_results("SELECT slug, name , names , icon FROM {$wpdb->prefix}sc_post_type ", ARRAY_A);
        foreach ($results as $res) {
            if (isset($res['names']) AND isset($res['name'])) {
                $names = $res['names'];
                $name = $res['name'];
                $slug = $res['slug'];

                if (empty($res['icon'])) {$icon = 'dashicons-format-aside';} else {$icon = $res['icon'];}
                $args = array(
                    'labels' => array('name' => $names, 'singular_name' => $name),
                    'public' => true,
                    'show_in_menu' => $show_in_menu,
                    'menu_position' => 60,
                    'has_archive' => true,
                    'menu_icon' => $icon,
                    'supports' => array('title', 'editor', 'thumbnail', 'custom-fields')
                );
                register_post_type($slug, $args);
            }
        }
    }
}
