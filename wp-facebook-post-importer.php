<?php

/**
 * The plugin bootstrap file
 *
 * This file is read by WordPress to generate the plugin information in the plugin
 * admin area. This file also includes all of the dependencies used by the plugin,
 * registers the activation and deactivation functions, and defines a function
 * that starts the plugin.
 *
 * @link              http://example.com
 * @since             1.0.0
 * @package           Plugin_Name
 *
 * @wordpress-plugin
 * Plugin Name:       WP Facebook Post Importer
 * Plugin URI:        http://example.com/wp-facebook-post-importer/
 * Description:       This is a short description of what the plugin does. It's displayed in the WordPress admin area.
 * Version:           1.0.0
 * Author:            –
 * Author URI:        http://example.com/
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       wp-facebook-post-importer
 * Domain Path:       /languages
 */

//require plugin_dir_path( __FILE__ ) . 'includes/class-facebook-page-post-importer.php';


class WPFPI_INIT {

	private $options;

	public function __construct() {

		$this->load_fb_skd();
		$this->facebook_import();
		$this->import_templates();

		if ( is_admin() ) { // load admin functions if backend ist loaded
			$this->admin_init();
		}

		add_action('init', array( $this, 'callback') );

		/*if( ! is_admin() && current_user_can( 'manage_options' ) ) { // load callback functions
			$this->callback();
		}*/

		if ( $_GET['attachement'] = 1) {
			var_dumpt( wp_get_attachment_metadata( 1753 ) );
		}

	}


	private function admin_init() {

		// Include Options Pge
		if ( ! class_exists( 'RationalOptionPages' ) ) {
			require plugin_dir_path( __FILE__ ) . 'vendor/RationalOptionPages.php';
		}
		require plugin_dir_path( __FILE__ ) . 'admin/options-page.php';

	}

	public function callback() {
		if( ! is_admin() && current_user_can( 'manage_options' ) ) { // load callback functions
			require plugin_dir_path( __FILE__ ) . 'includes/callback.php';
		}

	}

	private function load_fb_skd(){
		if ( !class_exists('Facebook\Facebook')) {
			require plugin_dir_path( __FILE__ ) . 'fb-sdk/autoload.php';
		}
	}

	public function facebook_import(){
		require plugin_dir_path( __FILE__ ) . 'includes/import-job.php';
	}

	private function import_templates(){
		require plugin_dir_path( __FILE__ ) . 'includes/import-templates.php';
	}


	

}

new WPFPI_INIT();
