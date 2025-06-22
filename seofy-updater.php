<?php
class WP_GitHub_Updater_For_SeofyPlugin_KWLM {
    public function __construct()
    {
        add_filter( 'pre_set_site_transient_update_plugins', array($this, 'check_for_plugin_update'));
        add_filter( 'upgrader_post_install', array( $this, 'upgrader_post_install' ), 10, 3 );

    }
  	/**
     * Update plugin from github
     */
	function check_for_plugin_update($transient) {
		$plugin_slug = plugin_basename(SKWLM_PLUGIN_DIRECTORY);

		$plugin_main_file = WP_PLUGIN_DIR . '/' . SKWLM_PROPER_FOLDER_NAME . '/' . basename(SKWLM_PLUGIN_DIRECTORY);
		if (!file_exists($plugin_main_file)) {
			return $transient;
		}

		$plugin_data = get_plugin_data($plugin_main_file);
		$github_url  = SKWLM_GITHUB_URL;

		$response = wp_remote_get($github_url);
		if (is_wp_error($response)) {
			return $transient;
		}

		$code = wp_remote_retrieve_response_code($response);
		if ($code !== 200) {
			return $transient; // GitHub returned 404 or another error
		}

		$body = wp_remote_retrieve_body($response);
		$releases = json_decode($body);

		if (!is_array($releases) || empty($releases[0]) || !isset($releases[0]->tag_name)) {
			return $transient;
		}

		// Only compare version if structure is valid
		if (version_compare($plugin_data['Version'], $releases[0]->tag_name, '<')) {
			if (!isset($transient) || !is_object($transient)) {
				$transient = (object) array();
			}
			if (!isset($transient->response) || !is_array($transient->response)) {
				$transient->response = array();
			}

			$transient->response[$plugin_slug] = (object) array(
				'slug' => SKWLM_PLUGIN_DIRECTORY,
				'plugin' => SKWLM_PLUGIN_SLUG,
				'new_version' => $releases[0]->tag_name,
				'url' => $releases[0]->html_url,
				'package' => $releases[0]->zipball_url
			);
		}

		set_transient('my_plugin_update_transient', time(), 24 * 60 * 60);
		return $transient;
	}


    public function upgrader_post_install( $true, $hook_extra, $result ) {
        global $wp_filesystem;

        $proper_destination = WP_PLUGIN_DIR . '/' . SKWLM_PROPER_FOLDER_NAME;
        $wp_filesystem->move( $result['destination'], $proper_destination );
        $result['destination'] = $proper_destination;
        $activate = activate_plugin( WP_PLUGIN_DIR . '/' . SKWLM_PLUGIN_SLUG );

        $fail = __( 'The plugin has been updated, but could not be reactivated. Please reactivate it manually.', 'github_plugin_updater' );
        $success = __( 'Plugin reactivated successfully.', 'github_plugin_updater' );
        echo is_wp_error( $activate ) ? $fail : $success;

        return $result;
    }


    function is_plugin_update($plugin_info) {
        if (!isset($plugin_info['destination']))
            return false;
        // Get the directory name of the plugin being installed
        $directory = dirname($plugin_info['destination']);

        // Check if it matches the directory of our own plugin
        return $directory === dirname(SKWLM_PLUGIN_DIRECTORY);
    }

   
}
