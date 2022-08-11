<?php
/**
 * Initialization Action.
 * 
 * @package WOPB\Notice
 * @since v.1.1.0
 */
namespace WOPB;

defined('ABSPATH') || exit;

/**
 * Initialization class.
 */
class Initialization{

    /**
	 * Setup class.
	 *
	 * @since v.1.1.0
	 */
    public function __construct(){
        $this->compatibility_check();
        $this->requires();
        $this->include_addons(); // Include Addons

        add_action('wp_head', array($this, 'popular_posts_tracker_callback')); // Popular Post Callback
        add_filter('block_categories_all', array($this, 'register_category_callback'), 10, 2); // Block Category Register
        add_action('enqueue_block_editor_assets', array($this, 'register_scripts_back_callback')); // Only editor
        add_action('admin_enqueue_scripts', array($this, 'register_scripts_option_panel_callback')); // Option Panel
        add_action('wp_enqueue_scripts', array($this, 'register_scripts_front_callback')); // Both frontend
        register_activation_hook(WOPB_PATH.'product-blocks.php', array($this, 'install_hook')); // Initial Activation Call
        add_action('activated_plugin', array($this, 'activation_redirect')); // Plugin Activation Call
        add_action('wp_footer', array($this, 'footer_modal_callback')); // Footer Text Added
        add_action('wp_ajax_wopb_addon', array($this, 'addon_settings_callback')); // Next Previous AJAX Call

        add_action('admin_init', array($this, 'check_theme_compatibility'));
        add_action('after_switch_theme', array($this, 'swithch_thememe'));

        add_action('template_redirect', array($this, 'track_product_view'), 20);
    }

    /**
	 * Recent Views Set Cookies
     * 
     * @since v.2.1.0
	 * @return NULL
	 */
    public function track_product_view() {
        if ( ! is_singular( 'product' ) ) {
            return;
        }
    
        global $post;
    
        if (empty($_COOKIE['__wopb_recently_viewed'])) {
            $viewed_products = array();
        } else {
            $viewed_products = (array) explode('|', sanitize_text_field($_COOKIE['__wopb_recently_viewed']));
        }

        if ( ! in_array($post->ID, $viewed_products) ) {
            $viewed_products[] = $post->ID;
        }
    
        if ( sizeof( $viewed_products ) > 30 ) {
            array_shift( $viewed_products );
        }
    
        wc_setcookie( '__wopb_recently_viewed', implode( '|', $viewed_products ) );
    }
    

    /**
	 * Theme Switch Callback
     * 
     * @since v.1.1.0
	 * @return NULL
	 */
    public function swithch_thememe() {
        $this->check_theme_compatibility();   
    }

    /**
	 * Theme Compatibility Action
     * 
     * @since v.1.1.0
	 * @return NULL
	 */
    public function check_theme_compatibility() {
        $licence = apply_filters( 'wopb_theme_integration' , FALSE);
        $theme = get_transient( 'wopb_theme_enable' );

        if( $licence ) {
            if( $theme != 'integration' ) {
                $themes = wp_get_theme();
                $api_params = array(
                    'wpxpo_theme_action' => 'theme_license',
                    'slug'      => $themes->get('TextDomain'),
                    'author'    => $themes->get('Author'),
                    'item_id'    => 1263,
                    'url'        => home_url()
                );
                
                $response = wp_remote_post( 'https://www.wpxpo.com', array( 'timeout' => 15, 'sslverify' => false, 'body' => $api_params ) );

                if ( !is_wp_error( $response ) || 200 === wp_remote_retrieve_response_code( $response ) ) {
                    $license_data = json_decode( wp_remote_retrieve_body( $response ) );
                    if(isset($license_data->license)) {
                        if ( $license_data->license == 'valid' ) {
                            set_transient( 'wopb_theme_enable', 'integration', 2592000 ); // 30 days time
                        }
                    }
                }
            }
        } else {
            if ( $theme == 'integration' ) {
                delete_transient('ulpt_theme_enable');
            }
        }
    }

    /**
	 * Addons Settings Enable Action
     * 
     * @since v.1.1.0
	 * @return NULL
	 */
    public function addon_settings_callback() {
        if (!wp_verify_nonce($_REQUEST['wpnonce'], 'wopb-nonce') && $local){
            return ;
        }
        $addon_name = sanitize_text_field($_POST['addon']);
        $addon_value = sanitize_text_field($_POST['value']);
        if ($addon_name && current_user_can('administrator')) {
            $settings = wopb_function()->get_setting();
            $settings[$addon_name] = $addon_value;
            $GLOBALS['wopb_settings'][$addon_name] = $addon_value;
            update_option('wopb_options', $settings);
        }
    }


    /**
	 * Include Addons Main File
     * 
     * @since v.1.1.0
	 * @return NULL
	 */
	public function include_addons() {
        if (wopb_function()->is_wc_ready()) {
            $addons_dir = array_filter(glob(WOPB_PATH.'addons/*'), 'is_dir');
            if (count($addons_dir) > 0) {
                foreach( $addons_dir as $key => $value ) {
                    $addon_dir_name = str_replace(dirname($value).'/', '', $value);
                    $file_name = WOPB_PATH . 'addons/'.$addon_dir_name.'/init.php';
                    if ( file_exists($file_name) ) {
                        include_once $file_name;
                    }
                }
            }
        }
    }


    /**
	 * Footer Modal Callback
     * 
     * @since v.1.1.0
	 * @return NULL
	 */
    public function footer_modal_callback(){
        echo '<div class="wopb-modal-wrap">';
            echo '<div class="wopb-modal-overlay"></div>';
            echo '<div class="wopb-modal-body-wrap">';
                echo '<div class="wopb-modal-body"></div>';
                echo '<div class="wopb-modal-loading"><div class="wopb-loading"></div></div>';
            echo '</div>';
        echo '</div>';
    }


    /**
	 * Option Panel Enqueue Script 
     * 
     * @since v.1.0.0
	 * @return NULL
	 */
    public function register_scripts_option_panel_callback($screen){
        wp_enqueue_media();
        wp_enqueue_style('wp-color-picker');
        wp_enqueue_script('wp-color-picker');
        wp_enqueue_script('wopb-option-script', WOPB_URL.'assets/js/wopb-option.js', array('jquery'), WOPB_VER, true);
        wp_enqueue_style('wopb-option-style', WOPB_URL.'assets/css/wopb-option.css', array(), WOPB_VER);
        wp_localize_script('wopb-option-script', 'wopb_option', array(
            'url' => WOPB_URL,
            'width' => wopb_function()->get_setting('editor_container'),
            'security' => wp_create_nonce('wopb-nonce'),
            'ajax' => admin_url('admin-ajax.php')
        ));
    }


    /**
	 * Enqueue Common Script for Both Frontend and Backend
     * 
     * @since v.1.0.0
	 * @return NULL
	 */
    public function register_scripts_common(){
        wp_enqueue_style('dashicons');
        wp_enqueue_style('wopb-slick-style', WOPB_URL.'assets/css/slick.css', array(), WOPB_VER);
        wp_enqueue_style('wopb-slick-theme-style', WOPB_URL.'assets/css/slick-theme.css', array(), WOPB_VER);
        if(is_rtl()){ 
            wp_enqueue_style('wopb-blocks-rtl-css', WOPB_URL.'assets/css/rtl.css', array(), WOPB_VER); 
        }
        wp_enqueue_script('wopb-slick-script', WOPB_URL.'assets/js/slick.min.js', array('jquery'), WOPB_VER, true);
        $this->register_main_scripts();
    }

    public function register_main_scripts() {
        wp_enqueue_style('wopb-style', WOPB_URL.'assets/css/blocks.style.css', array(), WOPB_VER );
        wp_enqueue_style('wopb-css', WOPB_URL.'assets/css/wopb.css', array(), WOPB_VER );
        wp_enqueue_script('wopb-flexmenu-script', WOPB_URL.'assets/js/flexmenu.min.js', array('jquery'), WOPB_VER, true);
        wp_enqueue_script('wopb-script', WOPB_URL.'assets/js/wopb.js', array('jquery','wopb-flexmenu-script'), WOPB_VER, true);
        wp_localize_script('wopb-script', 'wopb_core', array(
            'url' => WOPB_URL,
            'ajax' => admin_url('admin-ajax.php'),
            'security' => wp_create_nonce('wopb-nonce'),
            'isActive' => wopb_function()->isActive()
        ));
    }


    /**
	 * Checking if Our Blocks Used or Not
     * 
     * @since v.1.0.0
	 * @return NULL
	 */
    public function register_scripts_front_callback() {
        $call_common = false;
        $isWC = function_exists('is_shop');
        if ('yes' == get_post_meta((($isWC && is_shop()) ? wc_get_page_id('shop') : get_the_ID()), '_wopb_active', true)) {
            $call_common = true;
            $this->register_scripts_common();
        }  else if (apply_filters ('productx_common_script', false)) {
            $call_common = true;
            $this->register_scripts_common();
        } else if ($isWC && wopb_function()->is_builder()) {
            $call_common = true;
            $this->register_scripts_common();
            add_filter( 'postx_common_script', '__return_true' );
        } else if ($isWC && is_product()) {
            $this->register_main_scripts();
        }

        // For WidgetWidget
        $has_block = false;
        $widget_blocks = array();
        global $wp_registered_sidebars, $sidebars_widgets;
        foreach ($wp_registered_sidebars as $key => $value) {
            if (is_active_sidebar($key)) {
                foreach ($sidebars_widgets[$key] as $val) {
                    if (strpos($val, 'block-') !== false) {
                        if (empty($widget_blocks)) { 
                            $widget_blocks = get_option( 'widget_block' );
                        }
                        foreach ( (array) $widget_blocks as $block ) {
                            if ( isset( $block['content'] ) && strpos($block['content'], 'wp:product-blocks') !== false ) {
                                $has_block = true;
                                break;
                            }
                        }
                        if ($has_block) {
                            break;
                        }
                    }
                }
            }
        }
        if ($has_block) {
            if (!$call_common) {
                $this->register_scripts_common();
            }
            $css = get_option('wopb-widget', true);
            if ($css) {
                echo  wopb_function()->esc_inline($css);
            }
        }
    }


    /**
	 * Only Backend Enqueue Scripts
     * 
     * @since v.1.0.0
	 * @return NULL
	 */
    public function register_scripts_back_callback() {
        global  $post;
        $this->register_scripts_common();
        if (wopb_function()->is_wc_ready()) {
            $is_active = wopb_function()->is_lc_active();
            wp_enqueue_script( 'wopb-blocks-editor-script', WOPB_URL.'assets/js/editor.blocks.min.js', array('wp-i18n', 'wp-element', 'wp-blocks', 'wp-components', 'wp-editor'), WOPB_VER, true );
            wp_enqueue_style('wopb-blocks-editor-css', WOPB_URL.'assets/css/blocks.editor.css', array(), WOPB_VER);
            
            wp_localize_script('wopb-blocks-editor-script', 'wopb_data', array(
                'url' => WOPB_URL,
                'ajax' => admin_url('admin-ajax.php'),
                'security' => wp_create_nonce('wopb-nonce'),
                'hide_import_btn' => wopb_function()->get_setting('hide_import_btn'),
                'premium_link' => wopb_function()->get_premium_link(),
                'license' => $is_active ? get_option('edd_wopb_license_key') : '',
                'active' => $is_active,
                'isBuilder' => (isset($post->post_type) && $post->post_type == "wopb_builder") ? true : false,
                'isVariationSwitchActive' => wopb_function()->get_setting('wopb_variation_swatches')
            ));

            wp_set_script_translations( 'wopb-blocks-editor-script', 'product-blocks', WOPB_PATH . 'languages/' );
        }
    }

    /**
	 * Fire When Plugin First Installs
     * 
     * @since v.1.0.0
	 * @return NULL
	 */
    public function install_hook() {
        if (!get_option('wopb_options')) {
            wopb_function()->init_set_data();
        }
    }


    /**
	 * After Plugin Install Redirect to Settings
     * 
     * @since v.1.0.0
	 * @return NULL
	 */
    public function activation_redirect($plugin) {
        if( $plugin == 'product-blocks/product-blocks.php' ) {
            exit(wp_redirect(admin_url('admin.php?page=wopb-settings')));
        }
    }


    /**
	 * Compatibility Check Require
     * 
     * @since v.1.0.0
	 * @return NULL
	 */
    public function compatibility_check(){
        require_once WOPB_PATH.'classes/Compatibility.php';
        new \WOPB\Compatibility();
    }


    /**
	 * Require Necessary Libraries
     * 
     * @since v.1.0.0
	 * @return NULL
	 */
    public function requires() {
        require_once WOPB_PATH.'classes/Notice.php';        
        require_once WOPB_PATH.'classes/Options.php';
        new \WOPB\Notice();
        new \WOPB\Options();
        if ( wopb_function()->is_wc_ready() ) {
            require_once WOPB_PATH.'classes/REST_API.php';
            require_once WOPB_PATH.'classes/Blocks.php';
            require_once WOPB_PATH.'classes/Styles.php';
            require_once WOPB_PATH.'classes/Caches.php';
            new \WOPB\REST_API();
            new \WOPB\Styles();
            new \WOPB\Blocks();
            new \WOPB\Caches();

            require_once WOPB_PATH.'classes/Deactive.php';
            new \WOPB\Deactive();
        }
    }

    
    /**
	 * Block Categories Initialization
     * 
     * @since v.1.0.0
	 * @return NULL
	 */
    public function register_category_callback( $categories, $post ) {
        $builder = wopb_function()->is_archive_builder();
        $attr = array(
            array(
                'slug' => 'product-blocks',
                'title' => __( 'WooCommerce Blocks', 'product-blocks' )
            )
        );
        if ($builder) {
            $attr[] = array(
                'slug' => 'single-product', 
                'title' => __( 'Single Product', 'product-blocks' ) 
            );
            $attr[] = array(
                'slug' => 'single-cart', 
                'title' => __( 'Cart', 'product-blocks' ) 
            );
        }
        return array_merge( $attr, $categories );
    }


    /**
	 * Post View Counter for Every Post
     * 
     * @since v.1.0.0
	 * @return NULL
	 */
    public function popular_posts_tracker_callback($post_id) {
        if (!is_single()){ return; }
        global $post;
        if ($post->post_type != 'product') { return; }
        if (empty($post_id)) { $post_id = $post->ID; }
        $count = (int)get_post_meta( $post_id, '__product_views_count', true );
        update_post_meta($post_id, '__product_views_count', $count ? (int)$count + 1 : 1 );
    }
}