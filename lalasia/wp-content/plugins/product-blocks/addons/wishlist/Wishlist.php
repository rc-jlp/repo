<?php
/**
 * Wishlist Addons Core.
 * 
 * @package WOPB\Wishlist
 * @since v.1.1.0
 */

namespace WOPB;

defined('ABSPATH') || exit;

/**
 * Wishlist class.
 */
class Wishlist {

    /**
	 * Setup class.
	 *
	 * @since v.1.1.0
	 */
    public function __construct(){
        add_action('wp_ajax_wopb_wishlist', array($this, 'wopb_wishlist_callback'));
        add_action('wp_ajax_nopriv_wopb_wishlist', array($this, 'wopb_wishlist_callback'));

        add_filter('wopb_settings', array($this, 'get_option_settings'), 10, 1);
        add_action('wp_enqueue_scripts', array($this, 'add_wishlist_scripts'));
        add_shortcode('wopb_wishlist', array($this, 'wishlist_shortcode_callback'));

        if (wopb_function()->get_setting('wishlist_single_enable') == 'yes') {
            if (wopb_function()->get_setting('wishlist_position') == 'before_cart') {
                add_action('woocommerce_before_add_to_cart_button', array($this, 'add_wishlist_html'));
            } else {
                add_action('woocommerce_after_add_to_cart_button', array($this, 'add_wishlist_html'));
            }
        }
    }


    /**
	 * Wishlist JS Script Add
     * 
     * @since v.1.1.0
	 * @return NULL
	 */
    public function add_wishlist_scripts() {
        wp_enqueue_style('wopb-wishlist-style', WOPB_URL.'addons/wishlist/css/wishlist.css', array(), WOPB_VER);
        wp_enqueue_script('wopb-wishlist', WOPB_URL.'addons/wishlist/js/wishlist.js', array('jquery'), WOPB_VER, true);
        wp_localize_script('wopb-wishlist', 'wopb_wishlist', array(
            'ajax' => admin_url('admin-ajax.php'),
            'security' => wp_create_nonce('wopb-nonce'),
            'emptyWishlist' => wopb_function()->get_setting('wishlist_empty')
        ));
    }

    
    /**
	 * Wishlist Action Button Shortcode
     * 
     * @since v.1.1.0
	 * @return STRING | HTML of the shortcode
	 */
    public function add_wishlist_html() {
        $btn_text = wopb_function()->get_setting('wishlist_button');
        $wishlist_page = wopb_function()->get_setting('wishlist_page');
        $action_added = wopb_function()->get_setting('wishlist_action_added');
        $after_text = wopb_function()->get_setting('wishlist_browse');
        $wishlist_data = wopb_function()->get_wishlist_id();
        $wishlist_active = in_array(get_the_ID(), $wishlist_data);

        echo '<a class="wopb-wishlist-add'.($wishlist_active ? ' wopb-wishlist-active' : '').'" data-action="add" '.($wishlist_page && $action_added == 'redirect' ? 'data-redirect="'.esc_url(get_permalink($wishlist_page)).'"' : '').' data-postid="'.esc_attr(get_the_ID()).'">';
            echo '<span>';
                echo '<strong>&#x2661;</strong> '.($btn_text ? esc_html($btn_text) : esc_html__('Add to Wishlist', 'product-blocks'));
            echo '</span>';
            echo '<span>';
                echo '<strong>&#x2665;</strong> '.($after_text ? esc_html($after_text) : esc_html__('Browse Wishlist', 'product-blocks'));
            echo '</span>';    
        echo '</a>';
    }





    /**
	 * Wishlist Shortcode, Where Wishlist Shown
     * 
     * @since v.1.1.0
	 * @return STRING | HTML of the shortcode
	 */
    public function wishlist_shortcode_callback($message = '', $ids = array()) {
        $html = '';
        
        $wishlist_data = empty($ids) ? wopb_function()->get_wishlist_id() : $ids;

        if (count($wishlist_data) > 0) {
            $redirect_cart = wopb_function()->get_setting('wishlist_redirect_cart');
            $wishlist_empty = wopb_function()->get_setting('wishlist_empty');
            $wishlist_page = wopb_function()->get_setting('wishlist_page');
            $wishlist_page = $wishlist_page ? get_permalink($wishlist_page) : '#';
            $wishlist_action_added = wopb_function()->get_setting('wishlist_action_added');
            
            $html .= '<div class="'.($wishlist_action_added == 'popup' ? 'wopb-modal-content wopb-wishlist-modal-content' : '' ) .(empty($post_id) ? ' wopb-wishlist-shortcode' : '').'">';
                $html .= '<div class="wopb-wishlist-modal">';
                    if ($message) {
                        $html .= esc_html($message);
                    } else {
                    $html .= '<table>';
                        $html .= '<thead>';
                        $html .= '<tr>';
                            $html .= '<th class="wopb-wishlist-product-remove">&nbsp;</th>';
                            $html .= '<th class="wopb-wishlist-product-image">'.esc_html__('Image', 'product-blocks').'</th>';
                            $html .= '<th class="wopb-wishlist-product-name">'.esc_html__('Name', 'product-blocks').'</th>';
                            $html .= '<th class="wopb-wishlist-product-price">'.esc_html__('Price', 'product-blocks').'</th>'; 
                            $html .= '<th class="wopb-wishlist-product-action">'.esc_html__('Action', 'product-blocks').'</th>';
                            $html .= '<th class="wopb-modal-close"></th>';
                        $html .= '</tr>';
                        $html .= '</thead>';
                        $html .= '<tbody>';
                        
                        foreach ($wishlist_data as $val) {
                            $product = wc_get_product($val);
                            if ($product) {
                                $link = get_permalink($val);
                                $html .= '<tr>';
                                    $html .= '<td><a class="wopb-wishlist-remove" data-action="remove" href="#" data-postid="'.esc_attr($product->get_id()).'">×</a></td>';
                                    $html .= '<td class="wopb-wishlist-product-image">';
                                    $thumbnail = apply_filters( 'single_product_archive_thumbnail_size', 'woocommerce_thumbnail' );
                                    if ( $thumbnail ) {
                                        $html .= sprintf( '<a href="%s">%s</a>', esc_url( $link ), $product->get_image('thumbnail') );
                                    }
                                    $html .= '</td>';
                                    $html .= '<td class="wopb-wishlist-product-name"><a href="'.esc_url($link).'">'.esc_html($product->get_title()).'</a></td>';
                                    $html .= '<td class="wopb-wishlist-product-price">'.wp_kses_post($product->get_price_html()).'</td>';
                                    if ( $product->is_in_stock() ) {
                                        $html .= '<td class="wopb-wishlist-product-action"><span class="wopb-wishlist-product-stock">'.( $product->is_in_stock() ? esc_html__('In Stock', 'product-blocks') : esc_html__('Stock', 'product-blocks') ).'</span><span class="wopb-wishlist-cart-added" data-action="cart_remove" '.($redirect_cart ? 'data-redirect="'.esc_url(wc_get_cart_url()).'"' : '').' data-postid="'.esc_attr($product->get_id()).'">'.do_shortcode('[add_to_cart id="'.esc_attr($val).'" show_price="false"]').'</span></td>';
                                    } else {
                                        $html .= '<td class="wopb-wishlist-product-action"><span class="wopb-wishlist-product-stock">'.( $product->is_in_stock() ? esc_html__('In Stock', 'product-blocks') : esc_html__('Stock', 'product-blocks') ).'</span>'.do_shortcode('[add_to_cart id="'.esc_attr($val).'" show_price="false"]').'</td>';
                                    }
                                $html .= '</tr>';
                            }
                        }
                       
                        $html .= '</tbody>';
                    $html .= '</table>';
                    } 
                    $html .= '<div class="wopb-wishlist-product-footer">';
                        $html .= '<span><a href="'.esc_url($wishlist_page).'">'.esc_html__('Open Wishlist Page', 'product-blocks').'</a></span>';
                        $html .= '<span><a href="#" class="wopb-wishlist-cart-added" data-action="cart_remove_all" '.($redirect_cart ? 'data-redirect="'.esc_url(wc_get_cart_url()).'"' : '').' data-postid="'.implode(",",$wishlist_data).'">'.esc_html__('Add All To Cart', 'product-blocks').'</a></span>';
                        $html .= '<span><a class="wopb-modal-continue" href="'.( $wishlist_action_added == 'redirect' ? esc_url(wp_get_referer()) : '#' ).'">'.esc_html__('Continue Shopping', 'product-blocks').'</span>';
                    $html .= '</div>';
                    
                $html .= '</div>';//wopb-modal-content
            $html .= '</div>';//wopb-modal-content
        }
        else {
            $html .= '<h3> Your Wishlist is empty. </h3>';
            $html .= '<span><a class="wopb-modal-continue" href="'.( wopb_function()->get_setting('wishlist_action_added') == 'redirect' ? esc_url(wp_get_referer()) : '#' ).'">'.esc_html__('Continue Shopping', 'product-blocks').'</span>';
        }
        return $html;
    }


    /**
	 * Wishlist Addons Intitial Setup Action
     * 
     * @since v.1.1.0
	 * @return NULL
	 */
    public function initial_setup(){
        // Set Default Value
        $initial_data = array(
            'wishlist_heading'      => 'yes',
            'wishlist_page'         => '',
            'wishlist_require_login'=> '',
            'wishlist_empty'        => '',
            'wishlist_redirect_cart'=> 'yes',
            'wishlist_button'       => __('Add to Wishlist', 'product-blocks'),
            'wishlist_browse'       => __('Browse Wishlist', 'product-blocks'),
            'wishlist_single_enable'=> 'yes',
            'wishlist_position'     => 'after_cart',
            'wishlist_action'       => 'show_wishlist',
            'wishlist_action_added' => 'popup'
        );
        foreach ($initial_data as $key => $val) {
            wopb_function()->set_setting($key, $val);
        }
        
        // Insert Wishlist Page
        $wishlist_arr  = array( 
            'post_title'     => 'Wishlist',
            'post_type'      => 'page',
            'post_content'   => '<!-- wp:shortcode -->[wopb_wishlist]<!-- /wp:shortcode -->',
            'post_status'    => 'publish',
            'comment_status' => 'closed',
            'ping_status'    => 'closed',
            'post_author'    => get_current_user_id(),
            'menu_order'     => 0,
        );
        $wishlist_id = wp_insert_post( $wishlist_arr, FALSE );
        if ($wishlist_id) {
            wopb_function()->set_setting('wishlist_page', $wishlist_id);
        }
    }
    

    /**
	 * Wishlist Addons Default Settings Param
     * 
     * @since v.1.1.0
     * @param ARRAY | Default Filter Congiguration
	 * @return ARRAY
	 */
    public static function get_option_settings($config){
        $arr = array(
            'wishlist' => array(
                'label' => __('Wishlist', 'product-blocks'),
                'attr' => array(
                    'wishlist_heading' => array(
                        'type' => 'heading',
                        'label' => __('Wishlist Settings', 'product-blocks'),
                    ),
                    'wishlist_page' => array(
                        'type' => 'select',
                        'label' => __('Wishlist Page', 'product-blocks'),
                        'options' => wopb_function()->all_pages(true),
                        'desc' => '[wopb_wishlist] '.__('Use shortcode inside wishlist page.', 'product-blocks')
                    ),
                    'wishlist_require_login' => array(
                        'type' => 'switch',
                        'label' => __('Enable / Disable', 'product-blocks'),
                        'default' => '',
                        'pro' => true,
                        'desc' => __('Require Login for Wishlist.', 'product-blocks')
                    ),
                    'wishlist_empty' => array(
                        'type' => 'switch',
                        'label' => __('Enable / Disable', 'product-blocks'),
                        'default' => '',
                        'pro' => true,
                        'desc' => __('Empty Wishlist After Click.', 'product-blocks')
                    ),
                    'wishlist_redirect_cart' => array(
                        'type' => 'switch',
                        'label' => __('Enable / Disable', 'product-blocks'),
                        'default' => '',
                        // 'pro' => false,
                        'desc' => __('Redirect to Cart.', 'product-blocks')
                    ),
                    'wishlist_button' => array(
                        'type' => 'text',
                        'label' => __('Button Text', 'product-blocks'),
                        'default' => __('Add to Wishlist', 'product-blocks')
                    ),
                    'wishlist_browse' => array(
                        'type' => 'text',
                        'label' => __('Browse Wishlist Text', 'product-blocks'),
                        'default' => __('Browse Wishlist', 'product-blocks')
                    ),
                    'wishlist_single_enable' => array(
                        'type' => 'switch',
                        'label' => __('Enable / Disable', 'product-blocks'),
                        'default' => 'yes',
                        'desc' => __('Enable Wishlist in Single Page.', 'product-blocks')
                    ),
                    'wishlist_position' => array(
                        'type' => 'radio',
                        'label' => __('Position on Single Page', 'product-blocks'),
                        'options' => array(
                            'before_cart' => __( 'Before Cart','product-blocks' ),
                            'after_cart' => __( 'After Cart','product-blocks' ),
                        ),
                        'default' => 'after_cart'
                    ),
                    'wishlist_action' => array(
                        'type' => 'radio',
                        'label' => __('Action', 'product-blocks'),
                        'options' => array(
                            'show_wishlist' => __( 'Popup Wishlist','product-blocks' ),
                            'add_wishlist' => __( 'Added to Wishlist','product-blocks' ),
                        ),
                        'default' => 'show_wishlist'
                    ),
                    'wishlist_action_added' => array(
                        'type' => 'radio',
                        'label' => __('Action after Added', 'product-blocks'),
                        'options' => array(
                            'popup' => __( 'Popup','product-blocks' ),
                            'redirect' => __( 'Redirect to Page','product-blocks' ),
                        ),
                        'default' => 'popup'
                    ),
                    'wopb_wishlist' => array(
                        'type' => 'hidden',
                        'value' => 'true'
                    )
                )
            )
        );
        
        return array_merge($config, $arr);
    }


    /**
	 * Wishlist Add Action Callback.
     * 
     * @since v.1.1.0
	 * @return ARRAY | With Custom Message
	 */
    public function wopb_wishlist_callback() {
        if (!wp_verify_nonce($_REQUEST['wpnonce'], 'wopb-nonce') && $local) {
            return ;
        }
        
        $user_id = get_current_user_id();
        $simple_Product = sanitize_text_field( $_POST['simpleProduct'] );
        $type = sanitize_text_field( $_POST['type'] );
        $post_id = sanitize_text_field( $_POST['post_id'] );
        $action = wopb_function()->get_setting('wishlist_action');
        $required_login = wopb_function()->get_setting('wishlist_require_login');

        $user_data = wopb_function()->get_wishlist_id();

        if ( $post_id ) {
            if ($type == 'add') {
                if( !in_array($post_id, $user_data) ) {
                    $user_data[] = $post_id;
                }
                if ($required_login == 'yes' && $user_id) {
                    update_user_meta($user_id, 'wopb_wishlist', $user_data);
                }
                setcookie('wopb_wishlist', maybe_serialize($user_data), time()+604800, '/');
                $data = $action == 'add_wishlist' ? $this->wishlist_shortcode_callback(__('Wishlist Added.', 'product-blocks'), $user_data) : $this->wishlist_shortcode_callback('', $user_data);
                return wp_send_json_success( array('html' => $data, 'message' => __('Wishlist Added.', 'product-blocks')) );
            } else if ($type == 'cart_remove') {
                if (($key = array_search($post_id, $user_data)) !== false && wopb_function()->get_setting('wishlist_empty') ) {
                    if ( $simple_Product !== 'false' ) {
                        unset($user_data[$key]);
                    }
                    if ($required_login == 'yes' && $user_id) {
                        update_user_meta($user_id, 'wopb_wishlist', $user_data);
                    }
                    setcookie('wopb_wishlist', maybe_serialize($user_data), time()+604800, '/');
                }
                return wp_send_json_success( __('Wishlist Added to cart Item Removed.', 'product-blocks') );
            } else if ($type == 'cart_remove_all') {
                if (wopb_function()->get_setting('wishlist_empty')){
                    if ($required_login == 'yes' && $user_id) {
                        update_user_meta($user_id, 'wopb_wishlist', []);
                    }
                    setcookie('wopb_wishlist', maybe_serialize(array()), time()+604800, '/');
                }
                $post_id = explode(",", $post_id);
                foreach ($post_id as $key => $val) {
                    WC()->cart->add_to_cart( $val );
                }
                return wp_send_json_success( __('Wishlist All Item Removed.', 'product-blocks') );
            } else {
                if (($key = array_search($post_id, $user_data)) !== false) {
                    unset($user_data[$key]);
                    if ($required_login == 'yes' && $user_id) {
                        update_user_meta($user_id, 'wopb_wishlist', $user_data);
                    }
                    setcookie('wopb_wishlist', maybe_serialize($user_data), time()+604800, '/');
                    return wp_send_json_success( __('Wishlist Item Removed.', 'product-blocks') );
                }
                return wp_send_json_success( __('Wishlist Already Removed.', 'product-blocks') );
            }
        } else {
            return wp_send_json_error( __('Wishlist Not Added.', 'product-blocks') );
        }
    }
}