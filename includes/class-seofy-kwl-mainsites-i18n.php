<?php

/**
 * Define the internationalization functionality
 *
 * Loads and defines the internationalization files for this plugin
 * so that it is ready for translation.
 *
 * @link       https://seofy.dk
 * @since      1.0.0
 *
 * @package    Seofy_Kwl_Mainsites
 * @subpackage Seofy_Kwl_Mainsites/includes
 */

/**
 * Define the internationalization functionality.
 *
 * Loads and defines the internationalization files for this plugin
 * so that it is ready for translation.
 *
 * @since      1.0.0
 * @package    Seofy_Kwl_Mainsites
 * @subpackage Seofy_Kwl_Mainsites/includes
 * @author     Jonard Aragon <jonardaragon@gmail.com>
 */
class Seofy_Kwl_Mainsites_i18n {


	/**
	 * Load the plugin text domain for translation.
	 *
	 * @since    1.0.0
	 */
	public function load_plugin_textdomain() {

		load_plugin_textdomain(
			'seofy-kwl-mainsites',
			false,
			dirname( dirname( plugin_basename( __FILE__ ) ) ) . '/languages/'
		);

	}



}
