<?php
class WP_GitHub_Updater_For_SeofyPlugin_KWLM {
    public function __construct()
    {
        add_action( 'http_request_args', array($this,'http_request_args_filter'), 10, 2);
        add_filter( 'pre_set_site_transient_update_plugins', array($this, 'check_for_plugin_update'));
        add_filter( 'upgrader_post_install', array( $this, 'upgrader_post_install' ), 10, 3 );
        //add_filter( 'upgrader_source_selection', array($this, 'upgrader_webix_source_selection_filter'), 1, 3);

    }
  /**
     * Update plugin from github
     */
    function check_for_plugin_update($transient) {
        
        $plugin_slug = plugin_basename(SKWLM_PLUGIN_DIRECTORY);
       
        $plugin_data = get_plugin_data(SKWLM_PLUGIN_DIRECTORY);
      
        $github_url = SKWLM_GITHUB_URL;

        
        $request = wp_remote_get($github_url, array(
            'headers' => array(
                'Authorization' => 'token ' . SKWLM_GITHUB_TOKEN,
            ),
        ));
        if (is_wp_error($request)) {
            return $transient;
        }

        $request = json_decode(wp_remote_retrieve_body($request));
        if (empty($request)) {
            return $transient;
        }
        if (isset($request[0]) && version_compare($plugin_data['Version'], $request[0]->tag_name, '<')) {
            if (!isset($transient) || !is_object($transient)) {
                $transient = (object) array();
            }
            if (!isset($transient->response) || !is_array($transient->response)) {
                $transient->response = array();
            }
            $headers = array(
                'Authorization' => 'token ' . SKWLM_GITHUB_TOKEN,
            );

            $transient->response[$plugin_slug] = (object) array(
                'slug' => SKWLM_PLUGIN_DIRECTORY,
                'plugin' => SKWLM_PLUGIN_SLUG,
                'new_version' => $request[0]->tag_name,
                'url' => $request[0]->html_url,
                'package' => $request[0]->zipball_url,
            );
          
            $request = wp_remote_get($request[0]->zipball_url, array('headers' => $headers));
    
            
        }

        // Save the update transient for 24 hours
        set_transient('my_plugin_update_transient', time(), 24 * 60 * 60);
        return $transient;
    }

    function http_request_args_filter($args, $url) {
        if (strpos($url, 'https://api.github.com/') !== false) {
            $args['headers']['Authorization'] = 'token ' . SKWLM_GITHUB_TOKEN;
        }

        return $args;
    }

    public function upgrader_post_install( $true, $hook_extra, $result ) {

        global $wp_filesystem;
        
        // Move & Activate
        $proper_destination = WP_PLUGIN_DIR.'/'.SKWLM_PROPER_FOLDER_NAME;
        $wp_filesystem->move( $result['destination'], $proper_destination );
        $result['destination'] = $proper_destination;
        $activate = activate_plugin( WP_PLUGIN_DIR.'/'.SKWLM_PLUGIN_SLUG );
        
        // Output the update message
        $fail  = __( 'The plugin has been updated, but could not be reactivated. Please reactivate it manually.', 'github_plugin_updater' );
        $success = __( 'Plugin reactivated successfully.', 'github_plugin_updater' );
        echo is_wp_error( $activate ) ? $fail : $success;
        return $result;
        
        }

    function upgrader_webix_source_selection_filter($source, $remote_source, $upgrader) {
        // Check if the plugin being installed is ours
        if (!$this->is_plugin_update($upgrader->skin->plugin_info)) {
            return $source;
        }
        if (!is_object($GLOBALS['wp_filesystem'])) {
            return $source;
        }

        // Do not modify the directory name
        return $source;
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
