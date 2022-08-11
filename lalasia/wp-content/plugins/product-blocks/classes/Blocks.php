<?php
/**
 * Compatibility Action.
 * 
 * @package WOPB\Notice
 * @since v.1.1.0
 */
namespace WOPB;

defined('ABSPATH') || exit;

/**
 * Blocks class.
 */
class Blocks {

    /**
	 * Setup class.
	 *
	 * @since v.1.1.0
	 */
    private $all_blocks;
    private $api_endpoint = 'https://demo.wpxpo.com/wp-json/restapi/v2/';

    /**
	 * Setup class.
	 *
	 * @since v.1.1.0
	 */
    public function __construct(){
        $this->blocks();
		add_action('wp_ajax_wopb_load_more',        array($this, 'wopb_load_more_callback'));       // Next Previous AJAX Call
		add_action('wp_ajax_nopriv_wopb_load_more', array($this, 'wopb_load_more_callback'));       // Next Previous AJAX Call
        add_action('wp_ajax_wopb_filter',           array($this, 'wopb_filter_callback'));          // Next Previous AJAX Call
        add_action('wp_ajax_nopriv_wopb_filter',    array($this, 'wopb_filter_callback'));          // Next Previous AJAX Call
        add_action('wp_ajax_wopb_pagination',       array($this, 'wopb_pagination_callback'));      // Page Number AJAX Call
        add_action('wp_ajax_nopriv_wopb_pagination',array($this, 'wopb_pagination_callback'));      // Page Number AJAX Call
        add_action('wp_ajax_get_all_layouts',       array($this, 'get_all_layouts_callback'));      // All Layout AJAX Call
        add_action('wp_ajax_nopriv_get_all_layouts',array($this, 'get_all_layouts_callback'));      // All Layout AJAX Call
        add_action('wp_ajax_get_all_sections',      array($this, 'get_all_sections_callback'));     // All Section AJAX Call
        add_action('wp_ajax_nopriv_get_all_sections',array($this, 'get_all_sections_callback'));    // All Section AJAX Call
        add_action('wp_ajax_get_single_section',    array($this, 'get_single_section_callback'));   // Page Number AJAX Call
        add_action('wp_ajax_nopriv_get_single_section',array($this, 'get_single_section_callback'));// Page Number AJAX Call
        add_action('wp_ajax_wopb_addcart',          array($this, 'wopb_addcart_callback'));         // Add To Cart
        add_action('wp_ajax_nopriv_wopb_addcart',          array($this, 'wopb_addcart_callback'));         // Add To Cart

        
    }

    public function wopb_addcart_callback() {
        if (!wp_verify_nonce($_REQUEST['wpnonce'], 'wopb-nonce') && $local) {
            return ;
        }
        $postid = sanitize_text_field($_POST['postid']);
        if ($postid) {
            global $woocommerce;
            WC()->cart->add_to_cart( $postid );
        }
    }


    /**
	 * Require Blocks.
     * 
     * @since v.1.0.0
	 * @return NULL
	 */
    public function blocks() {
        require_once WOPB_PATH.'blocks/Heading.php';
        require_once WOPB_PATH.'blocks/Product_Grid_1.php';
        require_once WOPB_PATH.'blocks/Product_Grid_2.php';
        require_once WOPB_PATH.'blocks/Product_Grid_3.php';
        require_once WOPB_PATH.'blocks/Product_Grid_4.php';
        require_once WOPB_PATH.'blocks/Product_List_1.php';
        require_once WOPB_PATH.'blocks/Product_Category_1.php';
        require_once WOPB_PATH.'blocks/Product_Category_2.php';
        require_once WOPB_PATH.'blocks/Image.php';
        $this->all_blocks['product-blocks_heading'] = new \WOPB\blocks\Heading();
        $this->all_blocks['product-blocks_product-grid-1'] = new \WOPB\blocks\Product_Grid_1();
        $this->all_blocks['product-blocks_product-grid-2'] = new \WOPB\blocks\Product_Grid_2();
        $this->all_blocks['product-blocks_product-grid-3'] = new \WOPB\blocks\Product_Grid_3();
        $this->all_blocks['product-blocks_product-grid-4'] = new \WOPB\blocks\Product_Grid_4();
        $this->all_blocks['product-blocks_product-list-1'] = new \WOPB\blocks\Product_List_1();
        $this->all_blocks['product-blocks_product-category-1'] = new \WOPB\blocks\Product_Category_1();
        $this->all_blocks['product-blocks_product-category-2'] = new \WOPB\blocks\Product_Category_2();
        $this->all_blocks['product-blocks_image'] = new \WOPB\blocks\Image();
        $settings = wopb_function()->get_setting('wopb_builder');
        if ($settings == 'true') {
            require_once WOPB_PATH.'addons/builder/blocks/Archive_Title.php';
            require_once WOPB_PATH.'addons/builder/blocks/Product_Title.php';
            require_once WOPB_PATH.'addons/builder/blocks/Product_Short.php';
            require_once WOPB_PATH.'addons/builder/blocks/Product_Price.php';
            require_once WOPB_PATH.'addons/builder/blocks/Product_Description.php';
            require_once WOPB_PATH.'addons/builder/blocks/Product_Stock.php';
            require_once WOPB_PATH.'addons/builder/blocks/Product_Image.php';
            require_once WOPB_PATH.'addons/builder/blocks/Product_Meta.php';
            require_once WOPB_PATH.'addons/builder/blocks/Product_Additional_Info.php';
            require_once WOPB_PATH.'addons/builder/blocks/Product_Cart.php';
            require_once WOPB_PATH.'addons/builder/blocks/Product_Review.php';
            require_once WOPB_PATH.'addons/builder/blocks/Product_Breadcrumb.php';
            require_once WOPB_PATH.'addons/builder/blocks/Product_Rating.php';
            require_once WOPB_PATH.'addons/builder/blocks/Product_Tab.php';
            require_once WOPB_PATH.'addons/builder/blocks/cart_table/Cart_Table.php';
            require_once WOPB_PATH.'addons/builder/blocks/cart_total/Cart_Total.php';
            require_once WOPB_PATH.'addons/builder/blocks/free_shipping_progress_bar/Free_Shipping_Progress_Bar.php';
            $this->all_blocks['product-blocks_archive-title'] = new \WOPB\blocks\Archive_Title();
            $this->all_blocks['product-blocks_product-title'] = new \WOPB\blocks\Product_Title();
            $this->all_blocks['product-blocks_product-short'] = new \WOPB\blocks\Product_Short();
            $this->all_blocks['product-blocks_product-price'] = new \WOPB\blocks\Product_Price();
            $this->all_blocks['product-blocks_product-description'] = new \WOPB\blocks\Product_Description();
            $this->all_blocks['product-blocks_product-stock'] = new \WOPB\blocks\Product_Stock();
            $this->all_blocks['product-blocks_product-image'] = new \WOPB\blocks\Product_Image();
            $this->all_blocks['product-blocks_product-meta'] = new \WOPB\blocks\Product_Meta();
            $this->all_blocks['product-blocks_product-additional-info'] = new \WOPB\blocks\Product_Additional_Info();
            $this->all_blocks['product-blocks_product-cart'] = new \WOPB\blocks\Product_Cart();
            $this->all_blocks['product-blocks_product-review'] = new \WOPB\blocks\Product_Review();
            $this->all_blocks['product-blocks_product-breadcrumb'] = new \WOPB\blocks\Product_Breadcrumb();
            $this->all_blocks['product-blocks_product-rating'] = new \WOPB\blocks\Product_Rating();
            $this->all_blocks['product-blocks_product-tab'] = new \WOPB\blocks\Product_Tab();
            $this->all_blocks['product-blocks_cart-table'] = new \WOPB\blocks\Cart_Table();
            $this->all_blocks['product-blocks_cart-total'] = new \WOPB\blocks\Cart_Total();
            $this->all_blocks['product-blocks_cart-shipping'] = new \WOPB\blocks\Free_Shipping_Progress_Bar();
        }
    }

    
    /**
	 * Load More Action.
     * 
     * @since v.1.0.0
	 * @return NULL
	 */
    public function wopb_load_more_callback() {
        if (!wp_verify_nonce($_REQUEST['wpnonce'], 'wopb-nonce') && $local){
            return ;
        }

        $paged      = sanitize_text_field($_POST['paged']);
        $blockId    = sanitize_text_field($_POST['blockId']);
        $postId     = sanitize_text_field($_POST['postId']);
        $blockRaw   = sanitize_text_field($_POST['blockName']);
        $builder    = isset($_POST['builder']) ? sanitize_text_field($_POST['builder']) : '';
        $blockName  = str_replace('_','/', $blockRaw);

        if( $paged && $blockId && $postId && $blockName ) {
            $post = get_post($postId); 
            if (has_blocks($post->post_content)) {
                $blocks = parse_blocks($post->post_content);
                $this->block_return($blocks, $paged, $blockId, $blockRaw, $blockName, $builder);
            }
        }
    }

    /**
	 * Filter Callback of the Blocks
     * 
     * @since v.2.1.4
	 * @return STRING
	 */
    public function block_return($blocks, $paged, $blockId, $blockRaw, $blockName, $builder) {
        foreach ($blocks as $key => $value) {
            if($blockName == $value['blockName']) {
                if($value['attrs']['blockId'] == $blockId) {
                    $attr = $this->all_blocks[$blockRaw]->get_attributes(true); 
                    $value['attrs']['paged'] = $paged;
                    if ($builder) {
                        $value['attrs']['builder'] = $builder;
                    }
                    $attr = array_merge($attr, $value['attrs']);
                    echo  $this->all_blocks[$blockRaw]->content($attr, true);
                    die();
                }
            }
            if(!empty($value['innerBlocks'])){
                $this->block_return($value['innerBlocks'], $paged, $blockId, $blockRaw, $blockName, $builder);
            }
        }
    }


    public function filter_block_return($blocks, $blockId, $blockRaw, $blockName, $taxtype, $taxonomy) {
        foreach ($blocks as $key => $value) {
            if($blockName == $value['blockName']) {
                if($value['attrs']['blockId'] == $blockId) {
                    $attr = $this->all_blocks[$blockRaw]->get_attributes(true);
                    $attr['queryTax'] = $taxtype == 'product_cat' ? 'product_cat' : 'product_tag';
                    if($taxtype == 'product_cat' && $taxonomy) {
                        $attr['queryCatAction'] = json_encode(array($taxonomy));
                    }
                    if($taxtype == 'product_tag' && $taxonomy) {
                        $attr['queryTagAction'] = json_encode(array($taxonomy));
                    }
                    if ($taxonomy) {
                        if (strpos($taxonomy, 'custom_action#') !== false) {
                            $attr['custom_action'] = $taxonomy;
                        }
                    }
                    $attr = array_merge($attr, $value['attrs']);
                    echo  $this->all_blocks[$blockRaw]->content($attr, true);
                    die();
                }
            }
            if(!empty($value['innerBlocks'])){
                $this->filter_block_return($value['innerBlocks'], $blockId, $blockRaw, $blockName, $taxtype, $taxonomy);
            }
        }
    }

    /**
	 * Filter Callback.
     * 
     * @since v.1.0.0
	 * @return NULL
	 */
    public function wopb_filter_callback() {
        if (!wp_verify_nonce($_REQUEST['wpnonce'], 'wopb-nonce') && $local){
            return ;
        }
     
        $taxtype    = sanitize_text_field($_POST['taxtype']);
        $blockId    = sanitize_text_field($_POST['blockId']);
        $postId     = sanitize_text_field($_POST['postId']);
        $taxonomy   = sanitize_text_field($_POST['taxonomy']);
        $blockRaw   = sanitize_text_field($_POST['blockName']);
        $blockName  = str_replace('_','/', $blockRaw);

        if( $taxtype ) {
            $post = get_post($postId);
            if (has_blocks($post->post_content)) {
                $blocks = parse_blocks($post->post_content);
                $this->filter_block_return($blocks, $blockId, $blockRaw, $blockName, $taxtype, $taxonomy);
            }
        }
    }


    /**
	 * Pagination Callback.
     * 
     * @since v.1.0.0
	 * @return NULL
	 */
    public function wopb_pagination_callback() {
        if (!wp_verify_nonce($_REQUEST['wpnonce'], 'wopb-nonce') && $local) {
            return ;
        }

        $paged      = sanitize_text_field($_POST['paged']);
        $blockId    = sanitize_text_field($_POST['blockId']);
        $postId     = sanitize_text_field($_POST['postId']);
        $blockRaw   = sanitize_text_field($_POST['blockName']);
        $builder    = isset($_POST['builder']) ? sanitize_text_field($_POST['builder']) : '';
        $blockName  = str_replace('_','/', $blockRaw);

        if($paged) {
            $post = get_post($postId); 
            if (has_blocks($post->post_content)) {
                $blocks = parse_blocks($post->post_content);
                $this->block_return($blocks, $paged, $blockId, $blockRaw, $blockName, $builder);
            }
        }
    }


    /**
	 * All Layout Callback.
     * 
     * @since v.1.0.0
	 * @return NULL
	 */
    public function get_all_layouts_callback() {
        $request_data = wp_remote_post($this->api_endpoint.'layouts', array('timeout' => 150, 'body' => array('request_from' => 'product-blocks' )));
        if (!is_wp_error($request_data)) {
            return wp_send_json_success(json_decode($request_data['body'], true));
        } else {
			wp_send_json_error(array('messages' => $request_data->get_error_messages()));
        }
    }


    /**
	 * All Sections Callback.
     * 
     * @since v.1.0.0
	 * @return NULL
	 */
    public function get_all_sections_callback() {
        $request_data = wp_remote_post($this->api_endpoint.'sections', array('timeout' => 150, 'body' => array('request_from' => 'product-blocks' )));
        if (!is_wp_error($request_data)) {
            return wp_send_json_success(json_decode($request_data['body'], true));
        } else {
			wp_send_json_error(array('messages' => $request_data->get_error_messages()));
        }
    }


    /**
	 * Single Sections REST API Callback
     * 
     * @since v.1.0.0
	 * @param NULL
	 * @return NULL
	 */
    public function get_single_section_callback(){        
        $template_id = (int) sanitize_text_field($_REQUEST['template_id']);
        if (!$template_id) {
			return false;
        }
        $request_data = wp_remote_post( $this->api_endpoint.'single-section', array('timeout' => 150, 'body' => array('request_from' => 'product-blocks', 'template_id' => $template_id)));
        if (!is_wp_error($request_data)) {
            return wp_send_json_success(json_decode($request_data['body'], true));
        } else {
			wp_send_json_error(array('messages' => $request_data->get_error_messages()));
        }
    }


}