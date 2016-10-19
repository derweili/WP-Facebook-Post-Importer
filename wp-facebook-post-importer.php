<?php

/**
 *
 * @link              http://example.com
 * @since             1.0.0
 * @package           Plugin_Name
 *
 * @wordpress-plugin
 * Plugin Name:       WP Facebook Post Importer
 * Plugin URI:        http://example.com/wp-facebook-post-importer/
 * Description:       This Plugin is used to import posts from any facebook page you manage
 * Version:           1.0.0
 * Author:            –
 * Author URI:        http://derweili.de/
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       wp-facebook-post-importer
 * Domain Path:       /languages
 */

//require plugin_dir_path( __FILE__ ) . 'includes/class-facebook-page-post-importer.php';


class WPFPI_INIT {

	private $options;

	public function __construct() {
		/**
		*
		* initial load:
		* Facebook PHP DSK
		* Facebook import functions
		* Facebook import Templates
		* 
		* load options page if is_admin
		* 
		* 
		*/

		$this->load_fb_skd();
		$this->facebook_import();
		$this->import_templates();

		if ( is_admin() ) { // load admin functions if backend ist loaded
			$this->admin_init();
		}

		// load callback function on init hook
		add_action('init', array( $this, 'callback') );

	}


	private function admin_init() {

		// Include Options Pge
		if ( ! class_exists( 'RationalOptionPages' ) ) { // load RationalOptionPages (library to generate wordpress options pages)
			require plugin_dir_path( __FILE__ ) . 'vendor/RationalOptionPages.php';
		}
		require plugin_dir_path( __FILE__ ) . 'admin/options-page.php'; // load options page class

	}

	public function callback() {
		if( ! is_admin() && current_user_can( 'manage_options' ) ) { // load callback functions
			require plugin_dir_path( __FILE__ ) . 'includes/callback.php';
		}

	}

	private function load_fb_skd(){
		if ( !class_exists('Facebook\Facebook')) {
			require plugin_dir_path( __FILE__ ) . 'fb-sdk/autoload.php'; // include facebook php sdk
		}
	}

	public function facebook_import(){
		require plugin_dir_path( __FILE__ ) . 'includes/import-job.php'; // load import jobs (wp_cron jobs)
	}

	private function import_templates(){
		require plugin_dir_path( __FILE__ ) . 'includes/import-templates.php'; // load import templates to handle facebook post attachments
	}


	

}

new WPFPI_INIT();
