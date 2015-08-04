<?php
/*
Plugin Name: Simple Catalogue
Plugin URI: https://wordpress.org/plugins/simple-catalogue/
Description: Manage new items and custom categories with this lite plugin. Coded with native functions and without Javascript for maximum of compatibility.
Version: 0.1
Author: F.BALDO
Text Domain: simple-catalogue
Domain Path: /languages
Author URI: http://www.fb-creation-alwaysdata.com/
*/

// Interdire l'acces direct au fichier
if ( ! defined( 'ABSPATH' ) AND !is_user_can(manage_options()) )   exit;

class SimpleCatalogue {
	/**
	 *
	 */
	public function __construct()
	{
		//Inclusion des fichiers du plugin
		define('PLUGIN_DIR', dirname(__FILE__) . '/');
		include 'install-catalogue.php';
		include 'sc-post-type.php';
		include 'sc-taxonomie.php';
		include 'validation.php';
		include 'edit-catalogue.php';
		new sc_Post_Type();
		new sc_Taxonomie();
		// Intertionalization
		load_plugin_textdomain( 'simple-catalogue', false, basename( dirname( __FILE__ ) ) . '/languages/' );
		// Load the admin menu
		add_action('admin_menu', array($this, 'sc_add_admin_menu'), 20);
		//Init the saving options
		add_action('admin_init',array( $this, 'sc_register_settings' ));
		//Creation des table a l'installation du plugin
		register_activation_hook( __FILE__, array( 'SimpleCatalogue', 'install' ) );
		register_deactivation_hook( __FILE__, 'sc_drop_options' );
		register_uninstall_hook( __FILE__, array( 'SimpleCatalogue', 'deleteDatabaseTables' ) );
	}
	static function install() {
		installDatabaseTables();
	}
	static function sc_drop_options() {
	$menu_link = 'simple_catalogue_menu_link';
    delete_option( $menu_link );
	}
	static function deleteDatabaseTables() {
		//drop a custom db table
		global $wpdb;
		$wpdb->query( "DROP TABLE IF EXISTS {$wpdb->prefix}sc_post_type , {$wpdb->prefix}sc_category" );
	}
// Ajout d'un lien de menu dans l'admin de WP
	public function sc_add_admin_menu() {
		add_menu_page(__('New catalogue item') , 'Simple Catalogue' , 'manage_options' , 'catalogue' , array($this, 'menu_settings'), 'dashicons-index-card', '30');
		$hook2 = add_submenu_page( null, __('Add Categories', 'simple-catalogue'), __('Add Categories', 'simple-catalogue'), 'manage_options', 'categories', array( $this , 'menu_tax' ));
		$hook3 = add_submenu_page( 'options-general.php', __('Simple Catalogue settings', 'simple-catalogue'), 'Simple Catalogue', 'manage_options', 'sc_settings', array( $this , 'menu_settings' ));
        $hook4 = add_submenu_page( null,__( 'Manage items', 'simple-catalogue' ), __( 'Update' ), 'manage_options', 'edit-simple-catalogue', array($this, 'sc_edit_item') );
        $hook1 = add_submenu_page( null, __( 'Add new items', 'simple-catalogue' ), __( 'Add new', 'simple-catalogue' ), 'manage_options' , 'add_new_item', array($this, 'menu_post_type') );

		add_action( 'load-'. $hook1 , array ( $this , 'sc_process_post' ) );
		add_action( 'load-'. $hook2 , array ( $this , 'sc_process_tax' ) );
		//add_action( 'load-'. $hook3 , array ( $this, 'sc_process_settings' ));
		add_action( 'load-'. $hook4 , array( $this, 'sc_process_settings' ) );
	}
// Creation de la page Ajout d'un element
	public function menu_post_type() {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( __( 'You do not have sufficient permissions to access this page.' ) );
		}
		echo '<div id="col-container"><h2>' .  __( 'New Custom Post type creation', 'simple-catalogue' ) . '</h2>';
		echo '<div id="col-right"><div class="col-wrap">';
		echo '<table class="wp-list-table widefat fixed striped"><thead><tr><th>'.__('Name').'</th><th>'.__('Names', 'simple-catalogue').'</th><th>'.__('Icon').'</th><th>'.__('Slug').'</th></tr></thead><tbody id="the-list">';
		global $wpdb;
		$results = $wpdb->get_results("SELECT * FROM {$wpdb->prefix}sc_post_type ORDER BY id");
		foreach ($results as $row) {
		    //$id = $row->id;
		    $slug = $row->slug;
		    $name = $row->name;
		    $names = $row->names;
		    //$table = 'sc_post_type';
		    $other = $row->icon;
		    sc_simple_view($slug, $name, $names, $other);
		    }
		echo '</tbody></table></div></div>';
		echo '<div id="col-left" ><div class="col-wrap"><div class="form-wrap"><h3>' . __( 'Add new item', 'simple-catalogue' ) . '</h3>';
		echo '<p>' . __( 'You can add here a new item for your catalogue. Please fill these fields and validate for creation', 'simple-catalogue' );
		?>
		<form method="post" action="">
			<?php
			echo '<div class="form-field"><label for="slug">'.__('Slug'). ' ' . __('(required)') .'</label><input type="text" name="slug" maxlength="20" />
			<p>' . __('The identifier is the standardized version of the name . It generally contains only unaccented lowercase letters , numbers and underscores', 'simple-catalogue') . '</p></div>';
			echo '<div class="form-field"><label for="name">'.__('Singular Name', 'simple-catalogue'). ' ' . __('(required)') .'</label><input type="text" name="name" /></div>';
			echo '<div class="form-field"><label for="names">'.__('Plural Name', 'simple-catalogue') . ' ' . __('(required)') .'</label><input type="text" name="names" /></div>';
			echo '<div class="form-field"><label for="icon">'.__('Enter your menu icon name', 'simple-catalogue').'</label><input type="text" name="icon" />';
			echo '<p>'.__('This will add a custom icon when you choose to display the item link as primary level admin menu link', 'simple-catalogue').'</p><small>'.__('Please visit ', 'simple-catalogue').'
			<a href="https://developer.wordpress.org/resource/dashicons/#index-card">'.__('This page', 'simple-catalogue').'</a>
			 '.__('to choose your icon and enter the name like this : dashicons-exemple - if empty, dashicons-format-aside will be use.', 'simple-catalogue').'</small></div>';
			?>
			<?php submit_button(); ?>
		</form></div></div>
		<?php
		if (isset($_GET['message'])) {
			echo '<p class="error-message">'.$_GET['message'].'</p>';
			}
		echo '</div></div>';
	}
	//Creation de la page Ajout d'une categorie
	public function menu_tax()
		{
		echo '<div id="col-container"><h2>' . __( 'New Category creation', 'simple-catalogue' ) . '</h2>';
		echo '<div id="col-right"><div class="col-wrap">'; //display list of taxonomies created
		echo '<table class="wp-list-table widefat fixed striped"><thead><tr><th>'.__('Name').'</th><th>'.__('Names').'</th><th>'.__('Items attached', 'simple-catalogue').'</th><th>'.__('Slug').'</th></tr></thead><tbody id="the-list">';
		global $wpdb;
		$results = $wpdb->get_results("SELECT * FROM {$wpdb->prefix}sc_category ORDER BY id");
		foreach ($results as $row) {
		    //$id = $row->id;
		    $slug = $row->slug;
		    $name = $row->name;
		    $names = $row->names;
		    //$table = 'sc_post_type';
		    $other = $row->post_type;
		    sc_simple_view($slug, $name, $names, $other);
		    }
		echo '</tbody></table></div></div>';// Start of nex tax form
		echo '<div id="col-left" ><div class="col-wrap"><div class="form-wrap"><h3>' . __( 'Add new item', 'simple-catalogue' ) . '</h3>';
		echo '<p>' . __( 'You can add here a new category for your items. Please fill these fields and validate for creation', 'simple-catalogue' );
		?>
		<form method="post" action="">
			<?php
			echo '<div class="form-field"><label for="slug">'.__('Slug'). ' ' . __('(required)') .'</label><input type="text" name="slug" maxlength="20" />
			<p>' . __('The identifier is the standardized version of the name . It generally contains only unaccented lowercase letters , numbers and underscores', 'simple-catalogue') . '</p></div>';
			echo '<div class="form-field"><label for="name">'.__('Singular Name', 'simple-catalogue'). ' ' . __('(required)') .'</label><input type="text" name="tax_name" /></div>';
			echo '<div class="form-field"><label for="names">'.__('Plural Name', 'simple-catalogue'). ' ' . __('(required)') .'</label><input type="text" name="tax_names" /></div>';
			echo '<div class="form-field"><label for="post-type">'.__('Catalogue item for this category', 'simple-catalogue').'</label>';
			sc_dropdown_pt_row();
			echo '<div class="clear"></div></div>';
			submit_button(); ?>
		</form></div></div>
		<?php
		if (isset($_GET['message'])) {
			echo '<p class="error-message">'.$_GET['message'].'</p>';
			}
		echo '</div></div>';
	}
	public function menu_settings()
	{
		if ( ! current_user_can( 'manage_options' ) ) {
		wp_die(__('You do not have sufficient permissions to access this page.'));
		}
		echo '<div id="col-container">';
			// Start of categories table
		echo '<div id="col-right" style="width:50%"><div style="height:72px"><p style="float: left">'.__('You like this plugin ? Thank you for supporting developer by making a donation', 'simple-catalogue').'</p>
		<form action="https://www.paypal.com/cgi-bin/webscr" method="post" target="_top">
		<input type="hidden" name="cmd" value="_donations">
		<input type="hidden" name="business" value="f.baldo31@gmail.com">
		<input type="hidden" name="lc" value="FR">
		<input type="hidden" name="item_name" value="FB Creations">
		<input type="hidden" name="no_note" value="0">
		<input type="hidden" name="currency_code" value="EUR">
		<input type="hidden" name="bn" value="PP-DonationsBF:btn_donateCC_LG.gif:NonHostedGuest">
		<input type="image" src="https://www.paypalobjects.com/fr_FR/FR/i/btn/btn_donateCC_LG.gif" border="0" name="submit" alt="PayPal - la solution de paiement en ligne la plus simple et la plus sécurisée !">
		<img alt="" border="0" src="https://www.paypalobjects.com/fr_FR/i/scr/pixel.gif" width="1" height="1">
		</form>
</div><div class="wrap">
		<h2>' . __('Categories list', 'simple-catalogue') . '<a class="add-new-h2" href="'. admin_url("admin.php?page=categories") .'">'.__('Add') .'</a></h2>';
        echo '<table class="wp-list-table widefat fixed striped"><thead><tr><th>'.__('Slug').'</th><th>'.__('Name').'</th><th>'.__('Action').'</th></tr></thead>';
		global $wpdb;
		$tax_results = $wpdb->get_results("SELECT * FROM {$wpdb->prefix}sc_category ORDER BY id");
		foreach ($tax_results as $row2) {
		    $id = $row2->id;
		    $slug = $row2->slug;
		    $name = $row2->name;
		    $names = $row2->names;
		    $table = 'sc_category';
		    $other = $row2->post_type;
		    sc_list_item($slug, $table, $id, $name, $names, $other);
		    }
		echo '</table></div></div>';
			// Start of Items table
		echo '<div id="col-left" style="width:50%"><h2>' . get_admin_page_title() . '</h2>' . __('You can add or modify items and categories', 'simple-catalogue') . '</p><div class="wrap">';
		echo '<table class="wp-list-table widefat fixed striped"><thead><tr><th>'.__('Slug').'</th><th>'.__('Name').'</th><th>'.__('Action').'</th></tr></thead>';
		echo '<h2>' . __('Items list', 'simple-catalogue') . '<a class="add-new-h2" href="'. admin_url("admin.php?page=add_new_item") .'">'.__('Add') .'</a></h2>';
		global $wpdb;
		$results = $wpdb->get_results("SELECT * FROM {$wpdb->prefix}sc_post_type ORDER BY id");
		foreach ($results as $row) {
		    $id = $row->id;
		    $slug = $row->slug;
		    $name = $row->name;
		    $names = $row->names;
		    $table = 'sc_post_type';
		    $other = $row->icon;
		    sc_list_item($slug, $table, $id, $name, $names, $other);
		    }
		echo '</table></div></div><div class="clear"></div>';
		$option_menu_link = get_option( 'simple_catalogue_menu_link' );
		if ( $option_menu_link == null) { $checked = ''; } elseif ( $option_menu_link == 1 ) { $checked = 'checked="checked'; }
		echo '<h2>'.__('Plugin Option', 'simple-catalogue').'</h2><form method ="post" action ="options.php" >';
		echo '<fieldset><input type="checkbox" value="1" name="simple_catalogue_menu_link"' .$checked. '" /><label for="menu_link">' . __('Items as primary level menu', 'simple-catalogue'). '</label></fieldset>';
		settings_fields( 'simple_catalogue_settings' );
		submit_button();
		echo '</form></div>';
		}
		// Saving options
	public function sc_register_settings() {
		register_setting( 'simple_catalogue_settings', 'simple_catalogue_menu_link');
		}
	function sc_process_post()
	{
		if ( isset( $_POST['submit'] ) ) {
             if ( isset($_POST['name']) AND !empty($_POST['name']) AND isset($_POST['names']) AND !empty( $_POST['names'] ) AND isset($_POST['slug']) AND !empty( $_POST['slug'] ) ) {
                 $name = $_POST['name'];
                 $slug = $_POST['slug'];
                 $slug = htmlspecialchars(mb_strtolower(preg_replace('#[-,;: ]#', '_', $slug)));
                 $names = $_POST['names'];
                 $other = $_POST['icon'];
                 $table = 'sc_post_type';
                 $data = 'slug';
                 sc_insertion_validation($table, $data, $slug, $name, $names, $other);
             }
            else {
                $message = __('Please fill the required fields', 'simple-catalogue');
                header('Location: '. admin_url('admin.php?page=add_new_item') .'&message='. $message .'');
                 }
         }
	}
	function sc_process_tax()
	{
	    if ( isset( $_POST['submit'] ) ) {
            if (isset($_POST['tax_name']) AND !empty($_POST['tax_name']) AND isset($_POST['tax_names']) AND !empty($_POST['tax_names'])AND isset($_POST['slug']) AND !empty( $_POST['slug'] )) {
                $name = $_POST['tax_name'];
                $slug = $_POST['slug'];
                $slug = htmlspecialchars(mb_strtolower(preg_replace('#[-,;: ]##', '_', $slug)));
                $names = $_POST['tax_names'];
                $table = 'sc_category';
                $data = 'slug';
                $others = ($_POST['post-type']);
                if (is_array($others)) {
                    $other = implode(',', $others);
                    $other = mysql_real_escape_string($other);

                    sc_insertion_validation($table, $data, $slug, $name, $names, $other);
                } else {
                    $other = $others;
                    sc_insertion_validation($table, $data, $slug, $name, $names, $other);
                }
            }
            else {
                $message = __('Please fill the required fields', 'simple-catalogue');
                header('Location: ' . admin_url('admin.php?page=categories') . '&message=' . $message . '');
            }
        }
	}
	function sc_process_settings(){
        if ( isset( $_POST['update'] ) ) {
            $id = $_POST['update'];
            $name = $_POST['name'];
            $names = $_POST['names'];
                if ($_POST['table'] == 'sc_post_type') {
                    $icon = $_POST['other'];
                    sc_update_post_type($name, $names, $icon, $id);
                    $message = $_POST['name'] . __(' was updated successfully', 'simple-catalogue');
                    header('Location: ' . admin_url('admin.php?page=edit-simple-catalogue') . '&message=' . $message . '');
                } else if ( $_POST['table'] == 'sc_category' ) {
                    $post_type = $_POST['post-type'];
                    sc_update_tax_row($name, $names, $post_type, $id);
                    $message = $_POST['name'] . __(' was updated successfully', 'simple-catalogue');
                    header('Location: ' . admin_url('options-general.php?page=edit-simple-catalogue') . '&message=' . $message . '');
                }
        }
         else if ( isset( $_POST['delete'] ) ) {
         $id    = $_POST['delete'];
         $table = $_POST['table'];
         sc_delete_entire_row($table, $id);
         $message = __( 'Entry was successfully deleted', 'simple-catalogue' );
             header( 'Location: ' . admin_url( 'options-general.php?page=edit-simple-catalogue' ) . '&message=' . $message . '' );
         }
        else {
             return;
	    }
	}
    function sc_edit_item()
    {
        if ( ! current_user_can( 'manage_options' ) ) {
            wp_die(__('You do not have sufficient permissions to access this page.'));
        }
        echo '<div id="col-container"><div class="wrap">';
        echo '<h3>' . __('Catalogue\'s settings') . '</h3>';
        echo '<p>' . __('You can manage your catalogue here by deleting items and and categories', 'simple-catalogue');
        echo '<p>' . __('Be careful when deleting an entry, this operation is not reversible !', 'simple-catalogue');

        if (isset($_POST['edit'])) {
            $id = $_POST['id'];
            $slug = $_POST['slug'];
            $name = $_POST['name'];
            $names = $_POST['names'];
            $table = $_POST['table'];
            $other = $_POST['other'];
            sc_edit_item2($table, $id, $name, $names, $other);
            }
        if (isset($_GET['message'])) {
                echo '<p class="error-message" ;">'.$_GET['message'].'</p>';
                }
		echo '<br /><br /><a class="button" href="' . admin_url('options-general.php?page=sc_settings') . '">' . __('Return to calatogue\'s settings', 'simple-catalogue') .'</a>';
        echo '</div></div>';
    }
}
		new SimpleCatalogue;