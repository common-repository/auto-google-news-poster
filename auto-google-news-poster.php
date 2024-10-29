<?php
/*
  Plugin Name: Auto Google news poster
  Description: Auto Google news poster post news from Google news feed in one click.
  Author: ifourtechnolab
  Version: 1.0
  Author URI: http://www.ifourtechnolab.com/
  License: GPLv2 or later
  License URI: http://www.gnu.org/licenses/gpl-2.0.html
  Text Domain: agnp-plugin
  Domain Path: /languages
 */

if (!defined('ABSPATH')) {
    header('HTTP/1.0 403 Forbidden');
    exit;
}

/**
 * Plugin Url.
 */
define('AGNP_PLUGIN_URL', plugin_dir_url(__FILE__));

/**
 * Plugin Path.
 */
define('AGNP_PLUGIN_PATH', plugin_dir_path(__FILE__));

if (!class_exists('Auto_google_news_poster')) :

    /**
     * Auto Google news poster Class.
     */
    class Auto_google_news_poster {

        /**
         * Plugin Name.
         * @var string 
         */
        public $name;

        /**
         * Plug-in Domain name.
         * @var type 
         */
        public $domain;

        /**
         * Auto Google news poster menu Page Section
         * @var string 
         */
        public $section;

        /**
         * Auto Google news poster Setting menu Page Configuration option group.
         * @var string 
         */
        public $option;

        /**
         * Auto Google news poster page
         * @var string 
         */
        public $page;

        /**
         * Auto Google news poster Documentation link URL.
         */
        const AGNP_DOCUMENTATION = 'http://socialsharingapp.ifour-consultancy.net/wordpress/auto-google-news-poster.txt';

        /**
         * Google’s Terms of Service link URL.
         */
        const GOOGLE_TERMS = 'https://www.google.com/policies/terms/';

        /**
         * Apply All Hook for Auto Google news poster initialize plugin.
         */
        public function __construct() {
            $this->domain = 'agnp-plugin';
            $this->name = 'Auto Google news poster';
            $this->section = 'agnpSections';
            $this->option = 'agnpOptions';
            $this->page = 'agnp-page';
            $this->register_agnp_hooks();
        }

        protected function register_agnp_hooks() {
            add_action('plugins_loaded', array($this, 'agnp_plugin_textdomain'));
            add_action('admin_menu', array($this, 'agnp_plugin_setup_menu'));
            add_filter('plugin_action_links_' . plugin_basename(__FILE__), array($this, 'agnp_configuration_link'));
            add_action('admin_action_get_agnp', array($this, 'get_all_agnp'));
        }

        /**
         * Auto Google news poster Admin head add styling.
         */
        public function agnp_admin_head() {
            wp_enqueue_style('agnp-genericons', AGNP_PLUGIN_URL . 'assets/css/genericons.css');
        }

        /**
         * Load Text Domain of Auto Google news poster.
         */
        public function agnp_plugin_textdomain() {
            load_plugin_textdomain('agnp-plugin', FALSE, basename(dirname(__FILE__)) . '/languages/');
        }

        /**
         * Auto Google news poster plug-in menu configuration
         */
        public function agnp_plugin_setup_menu() {
            $title = apply_filters('agnp_menu_title', $this->name);
            $page = add_menu_page(__($title, 'agnp-plugin'), __($title, 'agnp-plugin'), 'manage_options', $this->page, array($this, 'agnp_show_admin_page'), AGNP_PLUGIN_URL.'assets/images/agnp-16x16.jpg', 9508);
            add_action('load-' . $page, array($this, 'agnp_page_load'));
        }

        /**
         * Auto Google news poster configuration link create in plug-in manager list callback function.
         * @param array $links
         * @return array $links
         */
        public function agnp_configuration_link($links) {
            $links[] = '<a href="' . esc_url(get_admin_url(null, 'admin.php?page=' . $this->page)) . '">Search</a>';
            return $links;
        }

        /**
         * Auto Google news poster Admin Page.
         */
        public function agnp_show_admin_page() {
            if (!current_user_can('manage_options')) {
                wp_die('You do not have suggicient permission to access this page.');
            }
            include (AGNP_PLUGIN_PATH . 'inc/admin-wrapper.php');
        }

        /**
         * Google news URL.
         * @param string $query
         * @param type $topStory
         * @param string $newstopic
         * @param string $cf
         * @param string $pz
         * @return url
         */
        public function newsUrl($query = null, $topStory = true, $newstopic = 'no', $cf = 'all', $pz = 1) {
            $gn = "https://news.google.com/news";
            if ($topStory) {
                return $gn . "?q=" . $query . "&cf=" . $cf . "&pz=" . $pz . "&output=rss";
            }
            if ($newstopic != 'no') {
                return $gn . "/section?q=" . $query . "&cf=" . $cf . "&pz=" . $pz . "&topic=" . $newstopic . "&output=rss";
            }
            return $gn . "/section?q=" . $query . "&cf=" . $cf . "&pz=" . $pz . "&output=rss";
        }

		/**
         * Get All google news callback function
         */
        public function get_all_agnp() {

            $nonce = $_REQUEST['_wpnonce'];

            if (wp_verify_nonce($nonce, 'get_agnp_action')) {

                $q = esc_html($_POST['news-search']);
                $newstopic = esc_html($_POST['news-topic']);
                $storyflag = ($newstopic != 'no') ? false : true;
                $xml = $this->newsUrl($q, $storyflag, $newstopic);
                $xmlDoc = new DOMDocument();
                $xmlDoc->load($xml);

                $this->getFeeds($xmlDoc);
            }

            wp_redirect(get_admin_url(null, 'admin.php?page=' . $this->page));
        }

        /**
         * Select child from item. 
         * @param object $item
         * @param int $i
         * @param string $tagname
         * @return string
         */
        public function selectChildItem($item, $i, $tagname) {
            return $item->item($i)->getElementsByTagName($tagname)
                            ->item(0)->childNodes->item(0)->nodeValue;
        }

        /**
         * Create Dom form news description.
         * @param type $item_desc
         * @return \DOMXPath
         */
        public function createDescriptionDocument($item_desc) {
            libxml_use_internal_errors(true);
            $descDom = new DOMDocument();
            $descDom->validateOnParse = true;
            $descDom->loadHTML($item_desc);
            libxml_clear_errors();
            return new DOMXPath($descDom);
        }

        /**
         * Get all RSS feed and insert it into database.
         * @param type $xmlDoc
         */
        public function getFeeds($xmlDoc) {
            $x = $xmlDoc->getElementsByTagName('item');

            for ($i = 0; $i < $x->length; $i++) :
                $collection = [];
                $item_title = $this->selectChildItem($x, $i, 'title');
                $item_link = $this->selectChildItem($x, $i, 'link');
                $cate = $x->item($i)->getElementsByTagName('category')->item(0);
                if ($cate) {
                    $item_category = $cate->childNodes->item(0)->nodeValue;
                    $collection['post_category'] = wp_create_category($item_category);
                }

                $item_desc = $this->selectChildItem($x, $i, 'description');
                $descXpath = $this->createDescriptionDocument($item_desc);
                $table = $descXpath->query("//table")->item(0);
                $td = $table->getElementsByTagName("td");
                $from = $td->item(0);

                $divSection = $td->item(1)->getElementsByTagName("div");
                $gtitle = $divSection->item(1)->getElementsByTagName("a")->item(0);
                $psections = $divSection->item(1)->getElementsByTagName("font")->item(2);

                $mixpost = $psections->nodeValue . "<a href='" . $item_link . "' target='_blank'>Read more</a>";

                $collection['post_link'] = $item_link;
                $collection['post_title'] = $item_title;
                $collection['post_from'] = $from->nodeValue;
                $collection['news_title'] = $gtitle->nodeValue;
                $collection['post'] = $mixpost;
                self::submitPost($collection);
				
            endfor;
        }

        public static function submitPost($newsPost) {

            global $wpdb;

            $query = $wpdb->prepare(
                    'SELECT ID FROM ' . $wpdb->posts . '
                        WHERE post_title = %s
                        AND post_type = \'post\'', $newsPost['post_title']
            );
            $wpdb->query($query);
            
            if ($wpdb->num_rows) {
                $post_id = $wpdb->get_var($query);
                $meta = get_post_meta($post_id, 'times', TRUE);
                $meta++;
                update_post_meta($post_id, 'times', $meta);
            } else {

                $insert_post = [];

                $insert_post['post_title'] = $newsPost['post_title'];
                $insert_post['post_content'] = $newsPost['post'];
                $insert_post['post_status'] = 'publish';
                $insert_post['post_date'] = date('Y-m-d H:i:s');
                $insert_post['post_author'] = '1';
                if (array_key_exists('post_category', $newsPost)) {
                    $insert_post['post_category'] = [$newsPost['post_category']];
                }
                $post_id = wp_insert_post($insert_post);
            }
            wp_reset_query();
        }

        /**
         * Auto Google news poster Help tab in top of Screen inside the Setting page callback function.
         */
        public function agnp_page_load() {
            add_action('admin_head', array($this, 'agnp_admin_head'));
            get_current_screen()->add_help_tab(array(
                'id' => 'documentation',
                'title' => __('Documentation', 'agnp-plugin'),
                'content' => "<p><a href='" . self::GOOGLE_TERMS . "' target='blank'>Google’s Terms of Service</a></p><p><a href='" . self::AGNP_DOCUMENTATION . "' target='blank'>" . $this->name . "</a></p>"
                    )
            );
        }
    }
    
    new Auto_google_news_poster();
endif;