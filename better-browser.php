<?php
/*
Plugin Name: Better Browser
Description: Add front-end notification bar for visitors using IE.
Version: 0.1.0
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

            add_action('wp_enqueue_scripts', array($this, 'betterbrowser_enqueue'));

            add_filter('acf/settings/load_json', array($this, 'betterbrowser_acf_json'), 20);

            add_action('wp_footer', array($this, 'betterbrowser_load'));

            add_action('init', array($this, 'betterbrowser_add_acf_options_page'));

        }

        public function betterbrowser_enqueue()
        {
            //https://unpkg.com/bowser@2.7.0/es5.js
            wp_register_script('bowser-js', $this->pluginUrl . '/assets/js/bundled.js', '', '2.7.0', true);
            wp_enqueue_script('bowser-js');

            wp_register_script('betterbrowser-js', $this->pluginUrl . '/assets/js/betterbrowser.js', 'jQuery', '0.0.2', true);
            wp_enqueue_script('betterbrowser-js');

            wp_register_style('betterbrowser-css', $this->pluginUrl . '/assets/css/betterbrowser.css', '', '0.0.2', 'all');
            wp_enqueue_style('betterbrowser-css');
        }


        // Add ACF json files for the back-end BetterBrowser settings, this must be done before get_field() gets triggered, hooks are useless afterwards
        public function betterbrowser_acf_json($paths)
        {
            //Load plugin acf dir
            $paths[] = $this->pluginPath . '/acf-json';

            // return
            return $paths;
        }

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

        public function betterbrowser_load()
        {
            $html = '';
            ob_start();
            ?>
            <div id="betterbrowser" class="betterbrowser">
                <div class="betterbrowser__banner">
                    <p><?php _e('Je gebruikt een verouderde browser. Hierdoor kunnen we de werking en veiligheid van de website niet garanderen. Bekijk <a href="#" class="js-show-browserlist"><span>hier</span></a> de alternatieven.', 'betterbrowser') ?></p>
                </div>
                <div class="betterbrowser__expand js-betterbrowser-list">
                    <ul>
                        <li id="betterbrowser-chrome">
                            <a href="https://www.google.com/chrome" target="_blank" title="Google Chrome">
                                <div class="icon"></div>
                                <span class="browser-name">Google Chrome</span>
                            </a>
                        </li>
                        <li id="betterbrowser-firefox">
                            <a href="https://www.firefox.com/" target="_blank" title="Mozilla Firefox">
                                <div class="icon"></div>
                                <span class="browser-name">Mozilla Firefox</span>
                            </a>
                        </li>
                        <li id="betterbrowser-safari">
                            <a href="https://www.apple.com/safari/" target="_blank" title="Apple Safari">
                                <div class="icon"></div>
                                <span class="browser-name">Apple Safari</span>
                            </a>
                        </li>
                    </ul>
                </div>
            </div>

            <?php
            $acf_browsers = get_field('browsers', 'options');
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
}