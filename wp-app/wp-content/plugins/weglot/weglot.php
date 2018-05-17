<?php
/**
 * @package Weglot
 * @version 1.11
 */

/*
Plugin Name: Weglot Translate
Plugin URI: http://wordpress.org/plugins/weglot/
Description: Translate your website into multiple languages in minutes without doing any coding. Fully SEO compatible.
Author: Weglot Translate team
Author URI: https://weglot.com/
Text Domain: weglot
Domain Path: /languages/
Version: 1.11
*/

/*
  Copyright 2015  Remy Berda  (email : remy@weglot.com)

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License, version 2, as
    published by the Free Software Foundation.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/

/**
 * Exit if absolute path
 */

if (! defined('ABSPATH')) {
    exit;
}


define('WEGLOT_VERSION', '1.11');
define('WEGLOT_DIR', dirname(__FILE__));
define('WEGLOT_BNAME', plugin_basename(__FILE__));
define('WEGLOT_DIRURL', plugin_dir_url(__FILE__));
define('WEGLOT_INC', WEGLOT_DIR . '/includes');
define('WEGLOT_RESURL', WEGLOT_DIRURL . 'resources/');

require_once WEGLOT_DIR . "/vendor/autoload.php";
require_once WEGLOT_DIR . '/simple_html_dom.php';
require_once WEGLOT_DIR . '/WeglotWidget.php';

use Weglot\Helpers\WeglotUtils;
use Weglot\Helpers\WeglotLang;
use Weglot\Notices\AdminNotices;
use Weglot\Third\Yoast\RedirectHandler;

$dirYoastPremum = plugin_dir_path(__DIR__) . "wordpress-seo-premium/premium/";
if(!function_exists('is_plugin_active')){
    include_once(ABSPATH . 'wp-admin/includes/plugin.php');
}

// Override yoast redirect
if (
    ! is_admin() && 
    is_plugin_active("wordpress-seo-premium/wp-seo-premium.php") && 
    file_exists($dirYoastPremum) &&
    file_exists($dirYoastPremum . 'classes/redirect/redirect-handler.php') && 
    file_exists($dirYoastPremum . 'classes/redirect/redirect-util.php')
){ 

    require_once $dirYoastPremum . 'classes/redirect/redirect-handler.php';
    require_once $dirYoastPremum . 'classes/redirect/redirect-util.php';

    $redirectHandler = new RedirectHandler();
    $redirectHandler->load();
}


/**
 * Singleton class Weglot */
class Weglot
{
    private $original_l;
    private $destination_l;

    private $request_uri;
    private $home_dir;
    private $network_paths;
    private $currentlang;
    private $allowed;
    private $userInfo;
    private $translator;

    /*
     * constructor
     *
     * @since 0.1
     */
    private function __construct()
    {
        $this->services = array(
            "AdminNotices" => new AdminNotices()
        );

        if (function_exists('apache_get_modules') && ! in_array('mod_rewrite', apache_get_modules())) {
            $this->services["AdminNotices"]->wgRequireRewriteModule();
            return;
        }

        $this->services["AdminNotices"]->thirdNotices();


        add_action('plugins_loaded', array( $this, 'wg_load_textdomain' ));
        add_action('init', array( $this, 'init_function' ), 11);
        add_action('wp', array( $this, 'rr_404_my_event' ));
        add_filter('plugin_action_links_' . plugin_basename(__FILE__), array( $this, 'wg_plugin_action_links' ));

        $this->original_l    = get_option('original_l');
        $this->destination_l = get_option('destination_l');

        $this->home_dir      = $this->getHomeDirectory();
        $this->request_uri   = $this->getRequestUri($this->home_dir);
        $this->network_paths =  $this->getListOfNetworkPath();

        $this->noredirect = false;
        if (strpos($this->request_uri, '?no_lredirect=true') !== false) {
            $this->noredirect = true;
            if (isset($_SERVER['REQUEST_URI'])) {
                $_SERVER['REQUEST_URI'] = str_replace(
                    '?no_lredirect=true',
                    '',
                    $_SERVER['REQUEST_URI']
                );
            }
        }

        $this->request_uri             = str_replace('?no_lredirect=true', '', $this->request_uri);
        $curr                          = $this->getLangFromUrl($this->request_uri);
        $this->currentlang             = $curr ? $curr : $this->original_l;
        $this->request_uri_no_language = ($this->currentlang != $this->original_l) ? substr($this->request_uri, 3) : $this->request_uri;

        if ($this->currentlang != $this->original_l) {
            $_SERVER['REQUEST_URI'] = str_replace(
                '/' . $this->currentlang .
                '/',
                '/',
                $_SERVER['REQUEST_URI']
            );
        }

        if (WeglotUtils::isLanguageRTL($this->currentlang)) {
            $GLOBALS['text_direction'] = 'rtl';
        } else {
            $GLOBALS['text_direction'] = 'ltr';
        }

        add_filter('woocommerce_get_cart_url', array( $this,'filter_woocommerce_get_cart_url'));
        add_filter('woocommerce_get_checkout_url', array( $this,'filter_woocommerce_get_cart_url'));
        add_filter('woocommerce_get_checkout_order_received_url', array( $this, 'filter_woocommerce_get_checkout_order_received_url'));
        add_action('woocommerce_reset_password_notification', array( $this, 'redirectUrlLostPassword'));

        add_filter('woocommerce_login_redirect', array( $this,'wg_log_redirect'));
        add_filter('woocommerce_registration_redirect', array( $this,'wg_log_redirect'));
        add_filter('login_redirect', array( $this,'wg_log_redirect'));
        add_filter('logout_redirect', array( $this,'wg_log_redirect'));

        $activeTranslateEmail = apply_filters("weglot_active_translate_email", false);
        if ($activeTranslateEmail) {
            add_filter('wp_mail', array( $this, 'translate_emails'), 10, 1);
        }

        $apikey           = get_option('project_key');
        $this->translator = $apikey ? new \Weglot\Client\WeglotClient($apikey) : null;

        $this->allowed = $apikey ? get_option('wg_allowed') : true;

        if (is_admin()) {
            if (strpos($this->request_uri, 'page=Weglot') !== false) {
                if ($this->translator) {
                    try {
                        $this->userInfo = $this->translator->getUserInfo();
                        if ($this->userInfo) {
                            $this->allowed = $this->userInfo['allowed'];
                            update_option('wg_allowed', $this->allowed ? 1 : 0);
                        }
                    } catch (\Exception $e) {
                        // If an exception occurs, do nothing, keep wg_allowed.
                        ;
                    }
                }
            } elseif ($this->allowed == 0) {
                $this->services["AdminNotices"]->wgExceededFreeLimit();
            } elseif (!$apikey) {
                $this->services["AdminNotices"]->wgNeedConfiguration();
            }
        }


        $isURLOK = $this->isEligibleURL($this->request_uri_no_language);
        if ($isURLOK) {
            add_action('wp_head', array( $this, 'add_alternate' ));
            add_action('widgets_init', array( $this, 'addWidget' ));
            add_shortcode('weglot_switcher', array( $this, 'wg_switcher_creation' ));
            if (get_option('is_menu') == 'on') {
                add_filter('wp_nav_menu_items', 'your_custom_menu_item', 10, 2);
                function your_custom_menu_item($items, $args)
                {
                    $button = Weglot::Instance()->returnWidgetCode();
                    $items .= $button;

                    return $items;
                }
            }
        } else {
            add_shortcode('weglot_switcher', array( $this, 'wg_switcher_creation_empty' ));
        }
    }

    /**
     * Redirect URL Lost password for WooCommerce
     */
    public function redirectUrlLostPassword($url)
    {
        if ($this->currentlang === $this->original_l) {
            return;
        }

        $urlRedirect = add_query_arg('reset-link-sent', 'true', wc_get_account_endpoint_url('lost-password'));
        $urlRedirect = $this->replaceUrl($urlRedirect, $this->currentlang);

        wp_redirect($urlRedirect);
        exit;
    }

    // Get our only instance of Weglot class
    public static function Instance()
    {
        static $inst = null;
        if ($inst == null) {
            $inst = new Weglot();
        }
        return $inst;
    }

    public static function plugin_activate()
    {
        add_option('with_flags', 'on');
        add_option('with_name', 'on');
        add_option('is_dropdown', 'on');
        add_option('is_fullname', 'off');
        add_option('override_css', '');
        add_option('is_menu', 'off');
        update_option('wg_allowed', 1);
        if (get_option('permalink_structure') == '') {
            add_option('wg_old_permalink_structure_empty', 'on');
            update_option('permalink_structure', '/%year%/%monthnum%/%day%/%postname%/');
        }
    }

    public static function plugin_deactivate()
    {
        flush_rewrite_rules();
        if (get_option('wg_old_permalink_structure_empty') == 'on') {
            delete_option('wg_old_permalink_structure_empty');
            update_option('permalink_structure', '');
        }
    }

    public static function plugin_uninstall()
    {
        flush_rewrite_rules();
        delete_option('project_key');
        delete_option('original_l');
        delete_option('destination_l');
        delete_option('show_box');
    }

    public function wg_load_textdomain()
    {
        load_plugin_textdomain('weglot', false, dirname(WEGLOT_BNAME) . '/languages/');
    }

    public function wg_plugin_action_links($links)
    {
        $links[] = '<a href="' . esc_url(get_admin_url(null, 'admin.php?page=Weglot')) . '">' . __('Settings', 'weglot') . '</a>';
        return $links;
    }

    public function filter_woocommerce_get_cart_url($wc_get_page_permalink)
    {
        if ($this->currentlang != $this->original_l) {
            return $this->replaceUrl($wc_get_page_permalink, $this->currentlang);
        } else {
            return $wc_get_page_permalink;
        }
    }

    public function filter_woocommerce_get_checkout_order_received_url($order_received_url)
   {
       if ($this->currentlang != $this->original_l) {
           if (substr(get_option('permalink_structure'), -1) != '/') {
               return str_replace('/?key', '?key', $this->replaceUrl($order_received_url, $this->currentlang));
           } else {
               return str_replace('//?key', '/?key', str_replace('?key', '/?key', $this->replaceUrl($order_received_url,  $this->currentlang)));
           }
       } else {
           if (isset($_SERVER['HTTP_REFERER'])) {
               $l = $this->getLangFromUrl($this->URLToRelative($_SERVER['HTTP_REFERER']));
               if ($l && $l != $this->original_l) {
                   if (substr(get_option('permalink_structure'), -1) != '/') {
                       return str_replace('/?key', '?key', $this->replaceUrl($order_received_url, $l));
                   } else {
                       return str_replace('//?key', '/?key', str_replace('?key', '/?key', $this->replaceUrl($order_received_url, $l)));
                   }
               }
           }
           return $order_received_url;
       }
   }

    public function wg_log_redirect($redirect_to)
    {
        if ($this->currentlang != $this->original_l) {
            return $this->replaceUrl($redirect_to, $this->currentlang);
        } else {
            if (isset($_SERVER['HTTP_REFERER'])) {
                $l = $this->getLangFromUrl($this->URLToRelative($_SERVER['HTTP_REFERER']));
                if ($l && $l != $this->original_l) {
                    return $this->replaceUrl($redirect_to, $l);
                }
            }
            return $redirect_to;
        }
    }

    public function translate_emails($args)
    {
        $messageAndSubject = "<p>".$args['subject']."</p>".$args['message'];

        if ($this->currentlang != $this->original_l) {
            $messageAndSubjectTranslated = $this->translateEmail($messageAndSubject, $this->currentlang);
        } elseif (isset($_SERVER['HTTP_REFERER'])) {
            $l = $this->getLangFromUrl($this->URLToRelative($_SERVER['HTTP_REFERER']));
            if ($l && $l != $this->original_l) { //If language in referer
                $messageAndSubjectTranslated = $this->translateEmail($messageAndSubject, $l);
            } elseif (strpos($_SERVER['HTTP_REFERER'], 'wg_language=') !== false) { //If language in parameter
                $pos   = strpos($_SERVER['HTTP_REFERER'], 'wg_language=');
                $start = $pos + strlen('wg_language=');
                $l     = substr($_SERVER['HTTP_REFERER'], $start, 2);
                if ($l && $l != $this->original_l) {
                    $messageAndSubjectTranslated = $this->translateEmail($messageAndSubject, $l);
                }
            }
        }

        if (strpos($messageAndSubjectTranslated, '</p>') !== false) {
            $pos             = strpos($messageAndSubjectTranslated, '</p>') + 4;
            $args['subject'] = substr($messageAndSubjectTranslated, 3, $pos - 7);
            $args['message'] = substr($messageAndSubjectTranslated, $pos);
        }
        return $args;
    }

    public function wg_switcher_creation()
    {
        $button = Weglot::Instance()->returnWidgetCode();
        echo wp_kses($button, $this->getAllowedTags());
    }

    public function wg_switcher_creation_empty()
    {
        echo wp_kses("", $this->getAllowedTags());
    }



    public function init_function()
    {
        add_action('admin_menu', array( $this, 'plugin_menu' ));
        add_action('admin_init', array( $this, 'plugin_settings' ));

        $dest = explode(',', $this->destination_l);

        if ($this->request_uri == '/' && ! $this->noredirect && ! WeglotUtils::is_bot()) { // front_page

            if (get_option('wg_auto_switch') == 'on' && isset($_SERVER['HTTP_ACCEPT_LANGUAGE'])) {
                /* Redirects to browser L */
                $lang = substr($_SERVER['HTTP_ACCEPT_LANGUAGE'], 0, 2);
                // exit(print_r($dest));
                if (in_array($lang, $dest)) {
                    wp_safe_redirect($this->home_dir . "/$lang/");
                    exit();
                }
            }
        }

        /* prevent homepage redirect in canonical.php in case of show */
        $request_uri = $this->request_uri;
        foreach ($dest as $d) {
            if ($request_uri == '/' . $d . '/') {
                $thisL = $d;
            }
        }
        $url = (isset($thisL) && $thisL != '') ? substr($request_uri, 3) : $request_uri;

        if ($url == '/' && (isset($thisL) && $thisL != '') && 'page' == get_option('show_on_front')) {
            add_action('template_redirect', array( $this, 'kill_canonical_wg_92103' ), 1);
        }

        if (! is_admin() || (is_admin() && strpos($this->request_uri, 'page=Weglot') !== false)) {
            // Add JS
            wp_register_script('wp-weglot-js', WEGLOT_RESURL . 'wp-weglot-js.js', false, WEGLOT_VERSION, false);
            wp_enqueue_script('wp-weglot-js');

            // Add CSS
            wp_register_style('wp-weglot-css', WEGLOT_RESURL . 'wp-weglot-css.css', false, WEGLOT_VERSION, false);
            wp_enqueue_style('wp-weglot-css');

            wp_add_inline_style('wp-weglot-css', $this->getInlineCSS());
            
            if (is_admin()) {
                // Add Admin JS
                wp_register_script('wp-weglot-admin-js', WEGLOT_RESURL . 'wp-weglot-admin-js.js', array( 'jquery' ), WEGLOT_VERSION, true);
                wp_enqueue_script('wp-weglot-admin-js');

                // Add Admin CSS
                wp_register_style('wp-weglot-admin-css', WEGLOT_RESURL . 'wp-weglot-admin-css.css', false, WEGLOT_VERSION, false);
                wp_enqueue_style('wp-weglot-admin-css');

                // Add Selectize JS
                wp_enqueue_script('jquery-ui', WEGLOT_RESURL . 'selectize/js/jquery-ui.min.js', array( 'jquery' ), WEGLOT_VERSION, true);
                wp_enqueue_script('jquery-selectize', WEGLOT_RESURL . 'selectize/js/selectize.min.js', array( 'jquery' ), WEGLOT_VERSION, true);
                // wp_enqueue_style( 'selectize-css',     WEGLOT_RESURL . 'selectize/css/selectize.css', array(),          $ver );
                wp_enqueue_style('selectize-defaut-css', WEGLOT_RESURL . 'selectize/css/selectize.default.css', array(), WEGLOT_VERSION);

                if (! function_exists('is_plugin_active')) { // Need to refactor enqueue script
                    require_once(ABSPATH . '/wp-admin/includes/plugin.php');
                }

                if ($this->services["AdminNotices"]->hasGTranslatePlugin()) {
                    $customCss = "
                        .gt-admin-notice{
                            display:none !important;
                        }
                    ";
      
                    wp_add_inline_style('wp-weglot-css', $customCss);
                }
            }
        }

        /* Putting it in init makes that buffer deeper than caching ob */
        ob_start(array($this, 'treatPage' ));
    }

    public function add_alternate()
    {
        if ($this->destination_l != '') {

            // $thisL = $this->currentlang;
            $dest = explode(',', $this->destination_l);

            $full_url = ($this->currentlang != $this->original_l) ? str_replace('/' . $this->currentlang . '/', '/', $this->full_url($_SERVER)) : $this->full_url($_SERVER);
            $output   = '<link rel="alternate" hreflang="' . $this->original_l . '" href="' . $full_url . '" />' . "\n";
            foreach ($dest as $d) {
                $output .= '<link rel="alternate" hreflang="' . $d . '" href="' . $this->replaceUrl($full_url, $d) . '" />' . "\n";
            }

            echo wp_kses($output, array(
                'link' => array(
                    'rel' => array(),
                    'hreflang' => array(),
                    'href' => array())
            ));
        }
    }

    public function getCurrentLang()
    {
        return $this->currentlang;
    }

    public function rr_404_my_event()
    {

        // regex logic here
        $isURLOK = $this->isEligibleURL($this->request_uri_no_language);
        if (! $isURLOK && $this->currentlang != $this->original_l) {
            global $wp_query;
            $wp_query->set_404();
            status_header(404);
        }
    }

    public function kill_canonical_wg_92103()
    {
        add_action('redirect_canonical', '__return_false');
    }

    public function plugin_menu()
    {
        $hook = add_menu_page('Weglot', 'Weglot', 'administrator', 'Weglot', array( $this, 'plugin_settings_page' ), WEGLOT_DIRURL . '/images/weglot_fav_bw.png');

        // add_action('load-'.$hook,array($this, 'updateRewriteRule'));
        if (isset($this->request_uri_no_language)
           // && isset( $_POST['settings-updated-nonce'] )
            && $this->request_uri_no_language
            && strpos($this->request_uri_no_language, 'page=Weglot') !== false
            && strpos($this->request_uri_no_language, 'settings-updated=true') !==
            false) {
            $d        = explode(',', preg_replace('/\s+/', '', trim($this->destination_l, ',')));
            $accepted = WeglotLang::getCodeLangs();
            foreach ($d as $k => $l) {
                if (! in_array($l, $accepted) || $l == $this->original_l) {
                    unset($d[ $k ]);
                }
            }
            update_option('destination_l', implode(',', $d));
            $this->destination_l = implode(',', $d);

            /* Display Box */
            if (! get_option('show_box')) {
                add_option('show_box', 'on');
            }

            if ($this->userInfo['plan'] <= 0 || in_array($this->userInfo['plan'], array( 18, 19, 1001, 1002 ))) {
                $d                   = explode(',', preg_replace('/\s+/', '', trim($this->destination_l, ',')));
                $this->destination_l = $d[0];
                update_option('destination_l', $this->destination_l);
            }
        }
    }

    public function plugin_settings()
    {
        register_setting('my-plugin-settings-group', 'project_key');
        register_setting('my-plugin-settings-group', 'original_l');
        register_setting('my-plugin-settings-group', 'destination_l');
        register_setting('my-plugin-settings-group', 'wg_auto_switch');
        register_setting('my-plugin-settings-group', 'override_css');
        register_setting('my-plugin-settings-group', 'flag_css');
        register_setting('my-plugin-settings-group', 'with_flags');
        register_setting('my-plugin-settings-group', 'type_flags');
        register_setting('my-plugin-settings-group', 'with_name');
        register_setting('my-plugin-settings-group', 'is_dropdown');
        register_setting('my-plugin-settings-group', 'is_fullname');
        register_setting('my-plugin-settings-group', 'is_menu');
        register_setting('my-plugin-settings-group', 'exclude_url');
        register_setting('my-plugin-settings-group', 'exclude_blocks');
        register_setting('my-plugin-settings-group', 'rtl_ltr_style');
    }

    public function plugin_settings_page()
    {
        include(WEGLOT_DIR . '/includes/wg-settings-page.php');
    }

    public function addWidget()
    {
        return register_widget('WeglotWidget');
    }

    public function translateEmail($body, $l)
    {
        $translatedEmail = $this->translator->translateDomFromTo($body, $this->original_l, $l);
        return $translatedEmail;
    }
    public function treatPage($final)
    {
        $request_uri = $this->request_uri;
        if (! is_admin() && strpos($request_uri, 'jax') === false && $this->original_l != '' && $this->destination_l != '') {

            // $final = file_get_contents(__DIR__.'/content.html'); //Testing purpose.
            // Get the original request
            $url = $this->request_uri_no_language;
            
            if ($this->isEligibleURL($url) && WeglotUtils::is_HTML($final)) {

                // If a language is set, we translate the page & links.
                if ($this->currentlang != $this->original_l) {
                    try {
                        $l     = $this->currentlang;
                        $final = $this->translatePageTo($final, $l);
                    } catch (\Weglot\Exceptions\WeglotException $e) {
                        $final .= '<!--Weglot error : ' . $e->getMessage() . '-->';
                        if (strpos($e->getMessage(), 'NMC') !== false) {
                            update_option('wg_allowed', 0);
                        }
                    } catch (\Exception $e) {
                        $final .= '<!--Weglot error : ' . $e->getMessage() . '-->';
                    }
                }

                // Place the button if we see short code
                if (strpos($final, '<div id="weglot_here"></div>') !== false) {
                    $button = $this->returnWidgetCode();
                    $final  = str_replace('<div id="weglot_here"></div>', $button, $final);
                }
                // Place the button if we see short code
                if (strpos($final, '<div class="weglot_here"></div>') !== false) {
                    $button = $this->returnWidgetCode();
                    $final  = str_replace(
                        '<div class="weglot_here"></div>',
                        $button,
                        $final
                    );
                }

                // Place the button if not in the page
                if (strpos($final, 'class="wgcurrent') === false) {
                    $button = $this->returnWidgetCode(true);
                    $button = WeglotUtils::str_lreplace('<aside data-wg-notranslate class="', '<aside data-wg-notranslate class="wg-default ', $button);
                    $final  = (strpos($final, '</body>') !== false) ? WeglotUtils::str_lreplace('</body>', $button . ' </body>', $final) : WeglotUtils::str_lreplace('</footer>', $button . ' </footer>', $final);
                }
                {
                    return $final;
                }
            } elseif ($this->isEligibleURL($url) && isset($final[0]) && $final[0] == '{' || ($final[0] == '[' && (isset($final[1]) && $final[1] == '{'))) {
                $thisL = $this->getLangFromUrl(
                    $this->URLToRelative(
                        $_SERVER['HTTP_REFERER']
                    )
                );
                if (isset($thisL) && $thisL != '') {
                    try {
                        if ($final[0] == '{' || ($final[0] == '[' && $final[1] == '{')) {
                            $json = json_decode($final, true);
                            if (json_last_error() == JSON_ERROR_NONE) {
                                $jsonT = $this->translateArray($json, $thisL);
                                return wp_json_encode($jsonT);
                            } else {
                                return $final;
                            }
                        } elseif (WeglotUtils::is_AJAX_HTML($final)) {
                            return $this->translatePageTo($final, $thisL);
                        } else {
                            return $final;
                        }
                    } catch (\Weglot\Exceptions\WeglotException $e) {
                        return $final;
                    } catch (\Exception $e) {
                        return $final;
                    }
                } else {
                    return $final;
                }
            } else {
                return $final;
            }
        } elseif ((strpos($request_uri, 'jax') !== false) &&
            $this->destination_l != '' && $this->original_l != '' && isset(
                $_SERVER['HTTP_REFERER']
            ) && strpos($_SERVER['HTTP_REFERER'], 'admin') === false) {
            $thisL = $this->getLangFromUrl(
                $this->URLToRelative(
                    $_SERVER['HTTP_REFERER']
                )
            );
            if (isset($thisL) && $thisL != '') {
                try {
                    if ($final[0] == '{' || ($final[0] == '[' && $final[1] == '{')) {
                        $json = json_decode($final, true);
                        if (json_last_error() == JSON_ERROR_NONE) {
                            $jsonT = $this->translateArray($json, $thisL);
                            return wp_json_encode($jsonT);
                        } else {
                            return $final;
                        }
                    } elseif (WeglotUtils::is_AJAX_HTML($final)) {
                        return $this->translatePageTo($final, $thisL);
                    } else {
                        return $final;
                    }
                } catch (\Weglot\Exceptions\WeglotException $e) {
                    return $final;
                } catch (\Exception $e) {
                    return $final;
                }
            } else {
                return $final;
            }
        } else {
            return $final;
        }
    }

    /* translation of the page */
    public function translateArray($array, $to)
    {
        foreach ($array as $key => $val) {
            if (is_array($val)) {
                $array[ $key ] = $this->translateArray($val, $to);
            } else {
                if (WeglotUtils::is_AJAX_HTML($val)) {
                    $array[ $key ] = $this->translatePageTo($val, $to);
                } elseif (in_array($key, array('redirecturl', 'url'))) {
                    $array[ $key] = $this->replaceUrl($val, $to);
                }
            }
        }
        return $array;
    }

    public function translatePageTo($final, $l)
    {
        if ($this->allowed == 0) {
            return $final . '<!--Not allowed-->';
        }
        
        $translatedPage = $this->translator->translateDomFromTo($final, $this->original_l, $l); // $page is your html page

        $this->modifyLink('/<a([^\>]+?)?href=(\"|\')([^\s\>]+?)(\"|\')([^\>]+?)?>/', $translatedPage, $l, 'A');
        $this->modifyLink('/<([^\>]+?)?data-link=(\"|\')([^\s\>]+?)(\"|\')([^\>]+?)?>/', $translatedPage, $l, 'DATALINK');
        $this->modifyLink('/<([^\>]+?)?data-url=(\"|\')([^\s\>]+?)(\"|\')([^\>]+?)?>/', $translatedPage, $l, 'DATAURL');
        $this->modifyLink('/<([^\>]+?)?data-cart-url=(\"|\')([^\s\>]+?)(\"|\')([^\>]+?)?>/', $translatedPage, $l, 'DATACART');
        $this->modifyLink('/<form([^\>]+?)?action=(\"|\')([^\s\>]+?)(\"|\')/', $translatedPage, $l, 'FORM');
        $this->modifyLink(
            '/<option (.*?)?(\"|\')([^\s\>]+?)(\"|\')(.*?)?>/',
            $translatedPage,
            $l,
            'OPTION'
        );
        $this->modifyLink('/<link rel="canonical"(.*?)?href=(\"|\')([^\s\>]+?)(\"|\')/', $translatedPage, $l, 'LINK');
        $this->modifyLink('/<meta property="og:url"(.*?)?content=(\"|\')([^\s\>]+?)(\"|\')/', $translatedPage, $l, 'META');




        $translatedPage = preg_replace('/<html (.*?)?lang=(\"|\')(\S*)(\"|\')/', '<html $1lang=$2' . $l . '$4', $translatedPage);
        $translatedPage = preg_replace('/property="og:locale" content=(\"|\')(\S*)(\"|\')/', 'property="og:locale" content=$1' . $l . '$3', $translatedPage);
        return $translatedPage;
    }

    public function modifyLink($pattern, &$translatedPage, $l, $type)
    {
        $admin_url = admin_url();
        preg_match_all($pattern, $translatedPage, $out, PREG_PATTERN_ORDER);
        for ($i = 0;$i < count($out[0]);$i++) {
            $sometags    = (isset($out[1])) ? $out[1][ $i ] : null;
            $quote1      = (isset($out[2])) ? $out[2][ $i ] : null;
            $current_url = (isset($out[3])) ? $out[3][ $i ] : null;
            $quote2      = (isset($out[4])) ? $out[4][ $i ] : null;
            $sometags2   = (isset($out[5])) ? $out[5][ $i ] : null;

            $lengthLink = apply_filters("weglot_length_replace_a", 1500); // Prevent error on long URL (preg_match_all Compilation failed: regular expression is too large at offset)
            if (strlen($current_url) >= $lengthLink) {
                continue;
            }

            if ($this->checkLink($current_url, $admin_url, $sometags, $sometags2)) {
                $functionName = 'replace' .$type;
                $this->$functionName(
                    $translatedPage,
                    $current_url,
                    $l,
                    $quote1,
                    $quote2,
                    $sometags,
                    $sometags2
                );
            }
        }
    }

    public function checkLink($current_url, $admin_url, $sometags = null, $sometags2 =
    null)
    {
        $parsed_url = parse_url($current_url);

        return (
            (($current_url[0] == 'h' && $parsed_url['host'] == $_SERVER['HTTP_HOST'])
                || ($current_url[0] == '/' && $current_url[1] != '/'))
            && strpos($current_url, $admin_url) === false
            && strpos($current_url, 'wp-login') === false
            && !$this->isLinkAFile($current_url)
            && $this->isEligibleURL($current_url)
            && strpos($sometags, 'data-wg-notranslate') === false
            && strpos($sometags2, 'data-wg-notranslate') === false
        );
    }

    public function replaceA(
        &$translatedPage,
        $current_url,
        $l,
        $quote1,
        $quote2,
        $sometags = null,
        $sometags2 = null
    ) {

        $translatedPage = preg_replace('/<a' . preg_quote($sometags, '/') . 'href=' .
            preg_quote($quote1 . $current_url . $quote2, '/') . '/', '<a' . $sometags . 'href=' . $quote1 . $this->replaceUrl(
                $current_url,
                $l
            ) . $quote2, $translatedPage);
    }

    public function replaceDATALINK(&$translatedPage, $current_url, $l, $quote1, $quote2, $sometags = null, $sometags2 = null)
    {
        $translatedPage = preg_replace('/<' . preg_quote($sometags, '/') . 'data-link=' . preg_quote($quote1 . $current_url . $quote2, '/') . '/', '<' . $sometags . 'data-link=' . $quote1 . $this->replaceUrl(
                $current_url,
                $l
            ) . $quote2, $translatedPage);
    }

    public function replaceDATAURL(&$translatedPage, $current_url, $l, $quote1, $quote2, $sometags = null, $sometags2 = null)
    {
        $translatedPage = preg_replace('/<' . preg_quote($sometags, '/') . 'data-url=' . preg_quote($quote1 . $current_url . $quote2, '/') . '/', '<' . $sometags . 'data-url=' . $quote1 . $this->replaceUrl(
                $current_url,
                $l
            ) . $quote2, $translatedPage);
    }

    public function replaceDATACART(
        &$translatedPage,
        $current_url,
        $l,
        $quote1,
                                    $quote2,
        $sometags = null,
        $sometags2 = null
    ) {
        $translatedPage = preg_replace('/<' . preg_quote($sometags, '/') . 'data-cart-url=' . preg_quote($quote1 . $current_url . $quote2, '/') . '/', '<' . $sometags . 'data-cart-url=' . $quote1 . $this->replaceUrl(
                $current_url,
                $l
            ) . $quote2, $translatedPage);
    }

    public function replaceFORM(
        &$translatedPage,
        $current_url,
        $l,
        $quote1,
                                $quote2,
        $sometags = null,
        $sometags2 = null
    ) {
        $translatedPage = preg_replace('/<form' . preg_quote($sometags, '/') . 'action=' . preg_quote($quote1 . $current_url . $quote2, '/') . '/', '<form ' . $sometags . 'action=' . $quote1 . $this->replaceUrl($current_url, $l) . $quote2, $translatedPage);
    }

    public function replaceOPTION(
        &$translatedPage,
        $current_url,
        $l,
        $quote1,
                                  $quote2,
        $sometags = null,
        $sometags2 = null
    ) {
        $translatedPage = preg_replace('/<option ' . preg_quote(
                $sometags,
            '/'
        ) . preg_quote(
                    $quote1 . $current_url . $quote2,
                    '/'
            ) . '(.*?)?>/', '<option ' . $sometags . $quote1 . $this->replaceUrl(
                $current_url,
                $l
            ) . $quote2 . '$2>', $translatedPage);
    }

    public function replaceLINK(
        &$translatedPage,
        $current_url,
        $l,
        $quote1,
                                $quote2,
        $sometags = null,
        $sometags2 = null
    ) {
        $translatedPage = preg_replace('/<link rel="canonical"' . preg_quote(
                $sometags,
            '/'
        ) . 'href=' . preg_quote($quote1 . $current_url .
                $quote2, '/') . '/', '<link rel="canonical"' . $sometags . 'href=' . $quote1 . $this->replaceUrl($current_url, $l) . $quote2, $translatedPage);
    }

    public function replaceMETA(
        &$translatedPage,
        $current_url,
        $l,
        $quote1,
                                $quote2,
        $sometags = null,
        $sometags2 = null
    ) {
        $translatedPage = preg_replace('/<meta property="og:url"' . preg_quote(
                $sometags,
            '/'
        ) . 'content=' . preg_quote($quote1 . $current_url
                . $quote2, '/') . '/', '<meta property="og:url"' . $sometags . 'content=' . $quote1 . $this->replaceUrl($current_url, $l) . $quote2, $translatedPage);
    }

    public function isLinkAFile($current_url)
    {
        $files = array('pdf','rar','doc','docx','jpg','jpeg','png','ppt','pptx','xls','zip','mp4','xlsx');
        foreach ($files as $file) {
            if (WeglotUtils::endsWith($current_url, '.'.$file)) {
                return true;
            }
        }
        return false;
    }

    /* Urls functions */
    public function replaceUrl($url, $l)
    {
        //$home_dir = $this->home_dir;

        $parsed_url = parse_url($url);
        $scheme     = isset($parsed_url['scheme']) ? $parsed_url['scheme'] . '://' : '';
        $host       = isset($parsed_url['host']) ? $parsed_url['host'] : '';
        $port       = isset($parsed_url['port']) ? ':' . $parsed_url['port'] : '';
        $user       = isset($parsed_url['user']) ? $parsed_url['user'] : '';
        $pass       = isset($parsed_url['pass']) ? ':' . $parsed_url['pass'] : '';
        $pass       = ($user || $pass) ? "$pass@" : '';
        $path       = isset($parsed_url['path']) ? $parsed_url['path'] : '/';
        $query      = isset($parsed_url['query']) ? '?' . $parsed_url['query'] : '';
        $fragment   = isset($parsed_url['fragment']) ? '#' . $parsed_url['fragment'] : '';


        if ($l == '') {
            return $url;
        } else {
            $urlTranslated =  (strlen($path) > 2 && substr($path, 0, 4) ==
                "/$l/") ?
                "$scheme$user$pass$host$port$path$query$fragment" : "$scheme$user$pass$host$port/$l$path$query$fragment";

            foreach ($this->network_paths as $np) {
                if (strlen($np) > 2 && strpos($urlTranslated, $np) !==
                    false) {
                    $urlTranslated = str_replace(
                        '/'.$l.$np,
                        $np.$l.'/',
                        $urlTranslated
                    );
                }
            }

            return $urlTranslated;
        }
    }
    public function url_origin($s, $use_forwarded_host = false)
    {
        $ssl      = (! empty($s['HTTPS']) && $s['HTTPS'] == 'on') ? true : false;
        $sp       = strtolower($s['SERVER_PROTOCOL']);
        $protocol = substr($sp, 0, strpos($sp, '/')) . (($ssl) ? 's' : '');
        $port     = $s['SERVER_PORT'];
        $port     = ((! $ssl && $port == '80') || ($ssl && $port == '443')) ? '' : ':' . $port;
        $host     = ($use_forwarded_host && isset($s['HTTP_X_FORWARDED_HOST'])) ? $s['HTTP_X_FORWARDED_HOST'] : (isset($s['HTTP_HOST']) ? $s['HTTP_HOST'] : null);
        $host     = isset($host) ? $host : $s['SERVER_NAME'] . $port;
        return $protocol . '://' . $host;
    }

    public function getListOfNetworkPath()
    {
        $paths = array();

        if (is_multisite()) {
            $sites = get_sites();
            foreach ($sites as $site) {
                $path = $site->path;
                array_push($paths, $path);
            }
        } else {
            array_push($paths, $this->home_dir.'/');
        }

        return $paths;
    }

    public function full_url($s, $use_forwarded_host = false)
    {
        return $this->url_origin($s, $use_forwarded_host) . $s['REQUEST_URI'];
    }
    public function isEligibleURL($url)
    {
        $url = urldecode($this->URLToRelative($url));
        //Format exclude URL
        $excludeURL = get_option('exclude_url');

        if (!empty($excludeURL)) {
            $excludeURL    = preg_replace('#\s+#', ',', trim($excludeURL));

            $excludedUrls  = explode(',', $excludeURL);
            foreach ($excludedUrls as &$ex_url) {
                $ex_url = $this->URLToRelative($ex_url);
            }
            $excludeURL = implode(',', $excludedUrls);
        }


        $exclusions = preg_replace('#\s+#', ',', $excludeURL);

        $listRegex = null;
        if(!empty($exclusions)) {
            $listRegex  = explode(',', $exclusions);
        }


        $excludeAmp = apply_filters("weglot_exclude_amp", true);
        if ($excludeAmp) {
            $listRegex[] = apply_filters('weglot_regex_amp', '([&,\?,/])?amp(/)?');
        }

        foreach ($listRegex as $regex) {
            $str          = $this->escapeSlash($regex);
            $prepareRegex = sprintf('/%s/', $str);
            if (preg_match($prepareRegex, $url) === 1) {
                return false;
            }
        }

        return true;
    }

    protected function escapeSlash($str)
    {
        return str_replace('/', '\/', $str);
    }

    public function URLToRelative($url)
    {
        if ((substr($url, 0, 7) == 'http://') || (substr($url, 0, 8) == 'https://')) {
            // the current link is an "absolute" URL - parse it to get just the path
            $parsed   = parse_url($url);
            $path     = isset($parsed['path']) ? $parsed['path'] : '';
            $query    = isset($parsed['query']) ? '?' . $parsed['query'] : '';
            $fragment = isset($parsed['fragment']) ? '#' . $parsed['fragment'] : '';

            if ($this->home_dir) {
                $relative = str_replace($this->home_dir, '', $path);

                return ($relative == '') ? '/' : $relative;
            } else {
                return $path . $query . $fragment;
            }
        }
        return $url;
    }
    public function getRequestUri($home_dir)
    {
        if ($home_dir) {
            return str_replace($home_dir, '', $_SERVER['REQUEST_URI']);
        } else {
            return  $_SERVER['REQUEST_URI'];
        }
    }
    public function getLangFromUrl($request_uri)
    {
        $l    = null;
        $dest = explode(',', $this->destination_l);
        foreach ($dest as $d) {
            if (substr($request_uri, 0, 4) == '/' . $d . '/') {
                $l = $d;
            }
        }
        return $l;
    }

    /** Returns the subdirectories where WP is installed
     *
     * returns /directories if there is one
     * return empty string otherwise
     *
     */
    public function getHomeDirectory()
    {
        $opt_siteurl = trim(get_option('siteurl'), '/');
        $opt_home    = trim(get_option('home'), '/');
        if ($opt_siteurl != '' && $opt_home != '') {
            if ((substr($opt_home, 0, 7) == 'http://' && strpos(substr($opt_home, 7), '/') !== false) || (substr($opt_home, 0, 8) == 'https://' && strpos(substr($opt_home, 8), '/') !== false)) {
                $parsed_url = parse_url($opt_home);
                $path       = isset($parsed_url['path']) ? $parsed_url['path'] : '/';
                return $path;
            }
        }
        return null;
    }

    /* button function (code and CSS) */
    public function getInlineCSS()
    {
        $css = get_option('override_css');
        if ((WeglotUtils::isLanguageRTL($this->original_l) && ! WeglotUtils::isLanguageRTL($this->currentlang)) ||
            (! WeglotUtils::isLanguageRTL($this->original_l) && WeglotUtils::isLanguageRTL($this->currentlang))) {
            $css .= get_option('rtl_ltr_style');
        }
        if (! is_admin()) {
            $css .= get_option('flag_css');
        }
        return $css;
    }

    public function getAllowedTags()
    {
        return array(
            'a' => array( 'href' => array(), 'title' =>
                array(), 'onclick' => array(), 'target'
            => array(), 'data-wg-notranslate' => array() , 'class' => array()),
            'div' => array('class' => array(), 'data-wg-notranslate' =>
                array()),
            'aside' => array('onclick' => array(), 'class' => array(), 'data-wg-notranslate' => array()),
            'ul' => array('class' => array(), 'data-wg-notranslate' => array()),
            'li' => array('class' => array(), 'data-wg-notranslate' => array())
        );
    }

    public function returnWidgetCode($forceNoMenu = false)
    {
        $full        = get_option('is_fullname') == 'on';
        $withname    = get_option('with_name') == 'on';
        $is_dropdown = get_option('is_dropdown') == 'on';
        $is_menu     = $forceNoMenu ? false : get_option('is_menu') == 'on';
        $flag_class  = (get_option('with_flags') == 'on') ? 'wg-flags ' : '';

        $type_flags = get_option('type_flags') ? get_option('type_flags') : 0;
        $flag_class .= $type_flags == 0 ? '' : 'flag-' . $type_flags . ' ';

        $current = $this->currentlang;
        $list    = $is_dropdown ? '<ul>' : '';
        $destEx  = explode(',', $this->destination_l);
        array_unshift($destEx, $this->original_l);
        foreach ($destEx as $d) {
            if ($d != $current) {
                $link = ($d != $this->original_l) ? $this->replaceUrl($this->home_dir.$this->request_uri_no_language, $d) : $this->home_dir.$this->request_uri_no_language;
                if ($link == $this->home_dir.'/' && get_option('wg_auto_switch') == 'on') {
                    $link = $link . '?no_lredirect=true';
                }
                $list .= '<li class="wg-li ' . $flag_class . $d . '"><a data-wg-notranslate href="' . $link . '">' . ($withname ? ($full ? WeglotUtils::getLangNameFromCode($d, false) : strtoupper($d)) : '') . '</a></li>';
            }
        }
        $list .= $is_dropdown ? '</ul>' : '';
        $tag = $is_dropdown ? 'div' : 'li';

        $moreclass = (get_option('is_dropdown') == 'on') ? 'wg-drop ' : 'wg-list ';

        $aside1 = ($is_menu && ! $is_dropdown) ? '' : '<aside data-wg-notranslate class="' . $moreclass . 'country-selector closed" onclick="openClose(this);" >';
        $aside2 = ($is_menu && ! $is_dropdown) ? '' : '</aside>';

        $button = '<!--Weglot ' . WEGLOT_VERSION . '-->' . $aside1 . '<' . $tag . ' data-wg-notranslate class="wgcurrent wg-li ' . $flag_class . $current . '"><a href="#" onclick="return false;" >' . ($withname ? ($full ? WeglotUtils::getLangNameFromCode($current, false) : strtoupper($current)) : '') . '</a></' . $tag . '>' . $list . $aside2;

        return $button;
    }
}

register_activation_hook(__FILE__, array( 'Weglot', 'plugin_activate' ));
register_deactivation_hook(__FILE__, array( 'Weglot', 'plugin_deactivate' ));
register_uninstall_hook(__FILE__, array( 'Weglot', 'plugin_uninstall' ));

add_action('plugins_loaded', array( 'Weglot', 'Instance' ), 10);
