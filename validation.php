<?php
/**
* Simple Catalogue
 * Database queries
 */
//interdire l'acces direct au fichier
if ( ! defined( 'ABSPATH' ) AND !is_user_can(manage_options()) )   exit;

// Requête de creation d'un custom post type
function sc_insert_new_post_type($slug, $name, $names, $icon) {
    require_once( '../wp-load.php' );
    require_once( '../wp-config.php' );
    global $wpdb;
    $name = stripslashes(htmlspecialchars($name));
    $names = stripslashes(htmlspecialchars($names));
    $icon = stripslashes(htmlspecialchars($icon));
        $args = array('slug' => $slug, 'name' => $name, 'names' => $names, 'icon' => $icon);
        $wpdb->insert($wpdb->prefix . 'sc_post_type', $args);
}
// Requête de creation d'une taxonomie
function sc_insert_new_tax_row($slug, $name, $names, $post_type) {
    require_once( '../wp-load.php' );
    require_once( '../wp-config.php' );
    global $wpdb;
    $name = stripslashes(htmlspecialchars($name));
    $names = stripslashes(htmlspecialchars($names));

    $args = array( 'slug' => $slug,'name' => $name, 'names' => $names, 'post_type' => $post_type );
    $wpdb->insert( $wpdb->prefix . 'sc_category', $args );
}
// Selectionne un array des slug dans une table
function sc_insertion_validation($table, $data, $slug, $name, $names, $other)
{
    require_once('../wp-load.php');
    require_once('../wp-config.php');
    global $wpdb;
    $results = $wpdb->get_results("SELECT * FROM {$wpdb->prefix}$table ORDER BY id");
    $array = array();
    foreach ($results as $resultat) {
        $array[] = $resultat->$data;
    }
    if ($table == 'sc_post_type') {
        $icon = $other;
        if (in_array($slug, $array)) {
            $message = __('Error while saving : this slug already exists', 'simple-catalogue');
            header('Location: ' . admin_url('admin.php?page=add_new_item') . '&message=' . $message . '');;
        } else {
            sc_insert_new_post_type($slug, $name, $names, $icon);
            $message = __('Entry was successfully saved', 'simple-catalogue');
            header('Location: ' . admin_url('admin.php?page=add_new_item') . '&message=' . $message . '');
        }
    }
    else if ($table == 'sc_category') {
        $post_type = $other;
        if (in_array($slug, $array)) {
            $message = __('Error while saving : this slug already exists', 'simple-catalogue');
            header('Location: ' . admin_url('admin.php?page=categories') . '&message=' . $message . '');;
        } else {
            sc_insert_new_tax_row($slug, $name, $names, $post_type);
            $message = __('Entry was successfully saved', 'simple-catalogue');
            header('Location: ' . admin_url('admin.php?page=categories') . '&message=' . $message . '');
        }
    }
}
// Suppression d'une rangee dans une table
function sc_delete_entire_row($table, $id) {
    require_once( '../wp-load.php' );
    require_once( '../wp-config.php' );
    global $wpdb;
    $wpdb->delete( $wpdb->prefix . $table, array( 'ID' => $id ) );
}
// Mise à jour des données d'une rangée de post type
function sc_update_post_type($name, $names, $icon, $id) {
    require_once( '../wp-load.php' );
    require_once( '../wp-config.php' );
    global $wpdb;
    $name = stripslashes(htmlspecialchars($name));
    $names = stripslashes(htmlspecialchars($names));
    $icon = stripslashes(htmlspecialchars($icon));
    $args = array('name' => $name, 'names' => $names, 'icon' => $icon );
    $wpdb->update( $wpdb->prefix . 'sc_post_type', $args, array( 'ID' => $id ) );
}
// Mise à jour des données d'une rangée de taxonomie
function sc_update_tax_row($name, $names, $post_type, $id) {
    require_once( '../wp-load.php' );
    require_once( '../wp-config.php' );
    global $wpdb;
    $name = stripslashes(htmlspecialchars($name));
    $names = stripslashes(htmlspecialchars($names));
    if (is_array($post_type)) {
        $post_types = implode(',', $post_type);
        $post_types = mysql_real_escape_string($post_types);
        $args = array('name' => $name, 'names' => $names, 'post_type' => $post_types );
        $wpdb->update( $wpdb->prefix . 'sc_category', $args, array( 'ID' => $id ) );
    } else {
        $post_type = stripslashes(htmlspecialchars($post_type));
        $args = array('name' => $name, 'names' => $names, 'post_type' => $post_type);
        $wpdb->update($wpdb->prefix . 'sc_category', $args, array('ID' => $id));
    }
}
// Selection d'une colonne pour checkbox
function sc_dropdown_pt_row(){
    require_once( '../wp-load.php' );
    require_once( '../wp-config.php' );
    global $wpdb;
    $results = $wpdb->get_results("SELECT slug FROM {$wpdb->prefix}sc_post_type ORDER BY id");
    foreach ($results as $resultat) {
        echo '<fieldset style="width:50%;float:left"><input type="checkbox" name="post-type[]" value="'.$resultat->slug.'">'.$resultat->slug.'</input></fieldset>';
    }
}