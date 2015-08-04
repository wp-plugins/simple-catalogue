<?php
/**
* Simple Catalogue
 * Edit and update views
**/
//interdire l'acces direct au fichier
if ( ! defined( 'ABSPATH' ) AND !is_user_can(manage_options()) )   exit;
// Affichage de la vue generale
function sc_list_item($slug, $table, $id, $name, $names, $other) {
			echo '<form method="post" action="'. admin_url("admin.php?page=edit-simple-catalogue") .'"><tbody><tr><td>' . $slug . '</td><td>';
			echo '<input type="hidden" name="table" value="'.$table.'" />';
			echo '<input type="hidden" name="id" value="' . $id . '" />';
			echo '<input type="hidden" name="slug" value="'.$slug.'" />';
			echo '<input type="hidden" name="name" value="'.$name.'" />';
			echo '<input type="hidden" name="names" value="'.$names.'" />';
			echo '<input type="hidden" name="other" value="'.$other.'" />';
			echo $name;
			echo '</td><td>';
			submit_button( __('Edit'), 'edit', 'edit', false );
            ?>
			</td></tr></tbody></form>
		<?php
}
// Affichage de la page Modifier
function sc_edit_item2($table, $id, $name, $names, $other) {
            if ( $table == 'sc_post_type') {
            echo '<table class="wp-list-table widefat fixed striped"><thead><tr><th>'.__('Singular Name', 'simple-catalogue').'</th><th>'.__('Plural Name', 'simple-catalogue').'</th><th>'.__('Icon').'</th><th>'.__('Actions').'</th></tr></thead>';
            }
            else if ( $table == 'sc_category' ) {
            echo '<table class="wp-list-table widefat fixed striped"><thead><tr><th>'.__('Singular Name', 'simple-catalogue').'</th><th>'.__('Plural Name', 'simple-catalogue').'</th><th>'.__('Items attached', 'simple-catalogue').'</th><th>'.__('Actions').'</th></tr></thead>';
            }
    		//echo '<tbody><tr><td>' . $slug . '</td>';
			echo '<tbody><tr><td><form action="" method="post">';
			echo '<input type="hidden" name="table" value="'.$table.'" />';
			echo '<input type="hidden" name="update" value="' . $id . '" />';
			echo '<input type="text" name="name" value="'.$name.'" /></td>';
			echo '<td><input type="text" name="names" value="'.$names.'" /></td><td>';
        if ( $table == 'sc_post_type') {
			echo '<input type="text" name="other" value="'.$other.'" />';
        } else if ( $table == 'sc_category' ) {
            sc_dropdown_pt_row(); }
			echo '</td><td>';
            echo submit_button( __('Update'), 'update', 'submit', false );
			echo '</form><form action="" method="post">';
			echo '<input type="hidden" name="delete" value="' . $id . '" />';
			echo '<input type="hidden" name="table" value="'.$table.'" />';
			submit_button( __('Delete'), 'delete', 'submit', false ); ?>
			</form></td></tr></tbody>
		<?php
}
// Affichage de la vue simple
	function sc_simple_view($slug, $name, $names, $other) {
	    echo '<tr><td>' . $name . '</td>';
	    echo '<td>' . $names . '</td>';
	    echo '<td>' . $other . '</td>';
	    echo '<td>' . $slug . '</td>';
	}