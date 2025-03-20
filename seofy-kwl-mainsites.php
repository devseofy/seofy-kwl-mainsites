<?php

/**
 * The plugin bootstrap file
 *
 * This file is read by WordPress to generate the plugin information in the plugin
 * admin area. This file also includes all of the dependencies used by the plugin,
 * registers the activation and deactivation functions, and defines a function
 * that starts the plugin.
 *
 * @link              https://seofy.dk
 * @since             1.0.9
 * @package           Seofy_Kwl_Mainsites
 *
 * @wordpress-plugin
 * Plugin Name:       Seofy KWL Mainsites
 * Plugin URI:        https://seofy.dk
 * Description:       The "Seofy Keyword Linking Mainsites" plugin is an essential tool for enhancing your WordPress website's SEO by automatically linking keywords to relevant pages within your content. It simplifies the process of adding internal links to boost your site's SEO performance, increase user engagement, and improve the overall user experience.
 * Version:           1.0.9
 * Author:            Jonard Aragon
 * Author URI:        https://seofy.dk/
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       seofy-kwl-mainsites
 * Domain Path:       /languages
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Currently plugin version.
 * Start at version 1.0.9 and use SemVer - https://semver.org
 * Rename this for your plugin and update it as you release new versions.
 */
define( 'SEOFY_KWL_MAINSITES_VERSION', '1.0.9' );

/**
 * The code that runs during plugin activation.
 * This action is documented in includes/class-seofy-kwl-mainsites-activator.php
 */
function activate_seofy_kwl_mainsites() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-seofy-kwl-mainsites-activator.php';
	Seofy_Kwl_Mainsites_Activator::activate();
}

/**
 * The code that runs during plugin deactivation.
 * This action is documented in includes/class-seofy-kwl-mainsites-deactivator.php
 */
function deactivate_seofy_kwl_mainsites() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-seofy-kwl-mainsites-deactivator.php';
	Seofy_Kwl_Mainsites_Deactivator::deactivate();
}

register_activation_hook( __FILE__, 'activate_seofy_kwl_mainsites' );
register_deactivation_hook( __FILE__, 'deactivate_seofy_kwl_mainsites' );

/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */
require plugin_dir_path( __FILE__ ) . 'includes/class-seofy-kwl-mainsites.php';

/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 *
 * @since    1.0.9
 */
function run_seofy_kwl_mainsites() {

	$plugin = new Seofy_Kwl_Mainsites();
	$plugin->run();

}
run_seofy_kwl_mainsites();
add_action( 'init', 'github_plugin_updater_kwl_main_init' );
function github_plugin_updater_kwl_main_init() {
    if ( is_admin() ) { // note the use of is_admin() to double check that this is happening in the admin
        include_once 'seofy-updater.php';
        define( 'SKWLM_PLUGIN_DIRECTORY',  __FILE__ );
        define( 'SKWLM_PLUGIN_SLUG',  'seofy-kwl-mainsites' );
        define( 'SKWLM_PROPER_FOLDER_NAME',  'seofy-kwl-mainsites' );
        define( 'SKWLM_GITHUB_URL',  'https://api.github.com/repos/devseofy/seofy-kwl-mainsites/releases');
        define( 'SKWLM_GITHUB_TOKEN',  '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');
        new WP_GitHub_Updater_For_SeofyPlugin_KWLM();

    }

}
