<?php
/*
Plugin Name: Better Browser
Description: Add front-end notification bar for visitors using IE.
Version: 0.4.2
Author: Pepijn Nichting
Text Domain: betterbrowser
Domain Path: /languages

Author URI: https://gravity.nl
*/
if (!class_exists('BetterBrowser')) {

    class BetterBrowser
    {
        function __construct()
        {
            // Set Plugin Path
            $this->pluginPath = dirname(__FILE__);
            $path_array = explode('/wp-content/', $this->pluginPath);
            $this->pluginRelPath = end($path_array);

            // Set Plugin URL
            $this->pluginUrl = content_url($this->pluginRelPath);

            add_filter('acf/settings/load_json', [$this, 'betterbrowser_acf_load_json'], 20); // make sure no get_field() gets called before this hook or else it wont work.
            add_action('acf/update_field_group', [$this, 'betterbrowser_acf_update_field_group'], 1, 1);

            add_action('init', [$this, 'betterbrowser_add_acf_options_page']);

            // Set browsers array (must be dont after load_json hook)
            $this->browsers = get_field('browsers', 'options');

            if (!empty($this->browsers)):
                add_action('wp_enqueue_scripts', [$this, 'betterbrowser_enqueue']);
                add_action('wp_footer', [$this, 'betterbrowser_load']);
            endif;

        }

        // Add ACF json files for the back-end BetterBrowser settings, this must be done before get_field() gets triggered, hooks are useless afterwards
        public function betterbrowser_acf_load_json($paths)
        {
            //Load plugin acf dir
            $paths[] = $this->pluginPath . '/acf-json';

            // return
            return $paths;
        }

        // Get the current saved group
        public function betterbrowser_acf_update_field_group($group)
        {
            //wp_die($group);
            $this->current_group_being_saved = $group['key'];

            add_action('acf/settings/save_json', [$this, 'betterbrowser_acf_save_json'], 9999);

            // return
            return $group;
        }

        // Save the json file to the plugin folder if this the plugin group
        public function betterbrowser_acf_save_json($path)
        {
            if ($this->current_group_being_saved === 'group_betterbrowser') {
                //Load plugin acf dir
                $path = $this->pluginPath . '/acf-json';
            }

            // return
            return $path;
        }

        // Load the required files
        public function betterbrowser_enqueue()
        {
            //https://unpkg.com/bowser@2.7.0/es5.js
            wp_register_script('bowser-js', $this->pluginUrl . '/assets/js/bundled.js', '', '2.7.0', true);
            wp_enqueue_script('bowser-js');

            wp_register_script('betterbrowser-js', $this->pluginUrl . '/assets/js/betterbrowser.js', 'jQuery', '0.3.1', true);
            wp_enqueue_script('betterbrowser-js');

            wp_register_style('betterbrowser-css', $this->pluginUrl . '/assets/css/betterbrowser.css', '', '0.3.1', 'all');
            wp_enqueue_style('betterbrowser-css');
        }

        // Add a options page to the admin area
        function betterbrowser_add_acf_options_page()
        {
            if (function_exists('acf_add_options_page')) {
                //wp_die( 'pffffff!' );
                acf_add_options_page([
                    'page_title' => 'Better Browser',
                    'menu_title' => 'Better Browser',
                    'menu_slug' => 'better-browser-options',
                    'icon_url' => 'dashicons-admin-site-alt3',
                    'post_id' => 'options',
                    'redirect' => false,
                ]);
            }
        }

        // Load front-end
        public function betterbrowser_load()
        {
            $supported_browsers = get_field('supported_browsers', 'options') ?? [];
            $html = '';
            ob_start();
            ?>
            <div id="betterbrowser" class="betterbrowser">
                <div class="betterbrowser__banner">
                    <p><?php _e('Je gebruikt een verouderde browser. Hierdoor kunnen we de werking en veiligheid van de website niet garanderen. Bekijk <a href="#" class="js-show-browserlist"><span>hier</span></a> de alternatieven.', 'betterbrowser') ?></p>
                </div>
                <div class="betterbrowser__expand js-betterbrowser-list">
                    <ul>
                        <?php if(in_array('chrome', $supported_browsers)): ?>
                        <li id="betterbrowser-chrome">
                            <a href="https://www.google.com/chrome" target="_blank" title="Google Chrome">
                                <div class="icon"></div>
                                <span class="browser-name">Google Chrome</span>
                            </a>
                        </li>
                        <?php endif; ?>
                        <?php if(in_array('firefox', $supported_browsers)): ?>
                        <li id="betterbrowser-firefox">
                            <a href="https://www.firefox.com/" target="_blank" title="Mozilla Firefox">
                                <div class="icon"></div>
                                <span class="browser-name">Mozilla Firefox</span>
                            </a>
                        </li>
                        <?php endif; ?>
                        <?php if(in_array('safari', $supported_browsers)): ?>
                        <li id="betterbrowser-safari">
                            <a href="https://www.apple.com/safari/" target="_blank" title="Apple Safari">
                                <div class="icon"></div>
                                <span class="browser-name">Apple Safari</span>
                            </a>
                        </li>
                        <?php endif; ?>
                        <?php if(in_array('edge', $supported_browsers)): ?>
                        <li id="betterbrowser-edge">
                            <a href="https://www.microsoft.com/en-us/windows/microsoft-edge" target="_blank" title="Microsoft Edge">
                                <div class="icon"></div>
                                <span class="browser-name">Microsoft Edge</span>
                            </a>
                        </li>
                        <?php endif; ?>
                    </ul>
                </div>
            </div>

            <?php

            $acf_browsers = $this->browsers;

            $browsers = [];
            // Must match shortname list: https://github.com/lancedikson/bowser/blob/master/src/constants.js
            foreach ($acf_browsers as $key => $browser) {
                $version = get_field($browser . '_version', 'options');
                $version = is_numeric($version) ? '>=' . $version : $version;
                $browsers[$browser] = !empty($version) ? $version : '>0';
            }
            //pre_print_r($browsers);
            //echo json_encode($browsers);

            ?>
            <script>
                var betterBrowser = <?php echo json_encode($browsers); ?>;
            </script>
            <?php
            $html .= ob_get_clean();
            echo $html;
        }
    }

    new BetterBrowser();

    require('updater.php');
    $repo = 'ionlinenl/better-browser';             // name of your repository. This is either "<user>/<repo>" or "<team>/<repo>".
    $bitbucket_username = 'pepijnnichting';         // your personal BitBucket username
    $bitbucket_app_pass = '98FcvBxa8tbzJPkfCrag';   // the generated app password with read access

    new \BetterBrowser\BitbucketWpUpdater\PluginUpdater(__FILE__, $repo, $bitbucket_username, $bitbucket_app_pass);
}
