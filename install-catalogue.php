<?php

/**
 * Simple Catalogue
 * Plugin installation
 */
function installDatabaseTables() {

	global $wpdb;
	$charset_collate = $wpdb->get_charset_collate();
	$post_table = $wpdb->prefix . 'sc_post_type';
	if($wpdb->get_var("show tables like '$post_table'") != $post_table) {
		$sql = "CREATE TABLE IF NOT EXISTS $post_table (
		id mediumint(9) NOT NULL AUTO_INCREMENT,
		slug tinytext NOT NULL UNIQUE,
		name tinytext NOT NULL,
		names tinytext NOT NULL,
		icon tinytext NOT NULL,
		PRIMARY KEY id (id)
		) $charset_collate;";

		require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
		dbDelta($sql);
	}
	$tax_table = $wpdb->prefix . 'sc_category';
	if($wpdb->get_var("show tables like '$tax_table'") != $tax_table) {
		$sql = "CREATE TABLE IF NOT EXISTS $tax_table (
		id mediumint(9) NOT NULL AUTO_INCREMENT,
		slug tinytext NOT NULL UNIQUE,
		name tinytext NOT NULL,
		names tinytext NOT NULL,
		post_type mediumtext NOT NULL,
		PRIMARY KEY id (id)
		) $charset_collate;";

		require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
		dbDelta($sql);
	}
}