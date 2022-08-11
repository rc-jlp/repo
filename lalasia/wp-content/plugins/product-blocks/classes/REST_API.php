<?php
/**
 * REST API Action.
 * 
 * @package WOPB\REST_API
 * @since v.1.0.0
 */
namespace WOPB;

defined('ABSPATH') || exit;

/**
 * Styles class.
 */
class REST_API{
    
    /**
	 * Setup class.
	 *
	 * @since v.1.0.0
	 */
    public function __construct() {
        add_action( 'rest_api_init', array($this, 'wopb_register_route') );
    }


    /**
	 * REST API Action
     * 
     * @since v.1.0.0
	 * @return NULL
	 */
    public function wopb_register_route() {
        register_rest_route( 'wopb', 'posts', array(
                'methods' => \WP_REST_Server::READABLE,
                'args' => array(
                    'queryNumber'=>[], 'queryType'=>[], 'queryCat'=>[], 'queryOrderBy'=>[], 'queryOrder'=>[], 'queryOffset'=>[], 'queryInclude'=>[], 'queryExclude'=>[], 'queryStatus'=>[], 'queryQuick'=>[], 'filterCat'=>[], 'filterTag'=>[], 'filterShow'=>[], 'filterType'=>[], 'filterAction'=>[], 'filterText'=>[], 'queryExcludeStock'=>[], 'wpnonce' => []
                ),
                'callback' => array($this, 'wopb_route_post_data'),
                'permission_callback' => '__return_true'
            )
        );
        register_rest_route( 'wopb', 'category', array(
                'methods' => \WP_REST_Server::READABLE,
                'args' => array('queryCat' => [], 'queryNumber' => [], 'queryType' => [], 'wpnonce' => []),
                'callback' => array($this, 'wopb_route_category_data'),
                'permission_callback' => '__return_true'
            )
        );
        register_rest_route( 'wopb', 'taxonomy', array(
                'methods' => \WP_REST_Server::READABLE,
                'args' => array('taxonomy' => [], 'wpnonce' => []),
                'callback' => array($this, 'wopb_route_taxonomy_data'),
                'permission_callback' => '__return_true'
            )
        );
        register_rest_route( 'wopb', 'imagesize', array(
                'methods' => \WP_REST_Server::READABLE,
                'args' => array('taxonomy' => [], 'wpnonce' => []),
                'callback' => array($this, 'wopb_route_imagesize_data'),
                'permission_callback' => '__return_true'
            )
        );
        register_rest_route( 'wopb', 'tax_info', array(
                'methods' => \WP_REST_Server::READABLE,
                'args' => array('taxonomy' => [], 'wpnonce' => []),
                'callback' => array($this, 'wopb_route_taxonomy_info_data'),
                'permission_callback' => '__return_true'
            )
        );
        register_rest_route( 'wopb', 'preview', array(
                'methods' => \WP_REST_Server::READABLE,
                'args' => array('previews'=>[], 'type'=>[], 'wpnonce' => []),
                'callback' => array($this, 'wopb_route_preview_data'),
                'permission_callback' => '__return_true'
            )
        );
    }

    /**
	 * Builder Preview Data
     * 
     * @since v.1.0.0
     * @param ARRAY | Parameters of the REST API
	 * @return ARRAY | Response as Array
	 */
    public function wopb_route_preview_data($prams){
        if (!wp_verify_nonce($_REQUEST['wpnonce'], 'wopb-nonce')){
            return rest_ensure_response([]);
        }

        global $product;
        $post_id = isset($prams['previews']) ? $prams['previews'] : '';
        if ($post_id) {
            $post_data = array();
            $products = wc_get_product($post_id);
            switch ($prams['type']) {
                case 'title':
                    $post_data['title'] = $products->get_title();
                    break;
                case 'description':
                    $post_data['description'] = $products->get_description();
                    break;
                case 'short':
                    $post_data['short'] = $products->get_short_description();
                    break;
                case 'image':
                    $url = $this->get_images($products);
                    $sales = $products->get_sale_price();
                    $regular = $products->get_regular_price();
                    $post_data['images'] = $url;
                    $post_data['images_thumb'] = $url;
                    $post_data['percentage'] = ($regular && $sales) ? round((($regular - $sales) / $regular) * 100) : 0;
                    break;
                case 'meta':
                    $post_data['sku'] = $products->get_sku();
                    $post_data['category'] = '<div className="meta-block__cat">'.wp_kses_post($this->list_items($products->get_category_ids(), 'product_cat')).'</div>';
                    $post_data['tag'] = '<div className="meta-block__tag">'.wp_kses_post($this->list_items($products->get_tag_ids(), 'product_tag')).'</div>';
                    break;
                case 'price':
                    $sales = $products->get_sale_price();
                    $regular = $products->get_regular_price();
                    $post_data['sales_price'] = $sales;
                    $post_data['regular_price'] = $regular;
                    $post_data['percentage'] = ($regular && $sales) ? round((($regular - $sales) / $regular) * 100) : 0;
                    $post_data['symbol'] = get_woocommerce_currency_symbol();
                    break;
                case 'rating':
                    $post_data['sales'] = $products->get_total_sales();
                    $post_data['rating'] = $products->get_review_count();
                    $post_data['average'] = ( ( $products->get_average_rating() / 5 ) * 100 ).'%';
                    break;
            }
            return array('type' => 'data', 'data' => $post_data);
        }

		$data = array( 
            array(
                'value' => '', 
                'label' => '-- Select Product --'
            )
        );
        $products = wc_get_products(['posts_per_page' => 10, 'post_status' => 'publish']);
		foreach ($products as $key => $val) {
			$data[] = array('value' => $val->get_id(), 'label' => $val->get_title());
		}
        return array('type' => 'list', 'list' => $data);
    }


    public function get_images($val) {
        $data = array();
        $feature_image = $val->get_image_id();
        if (!empty($feature_image)) {
            $data[] = esc_url(wp_get_attachment_image_url($feature_image, 'full'));
        }
        $galleries = $val->get_gallery_image_ids();
        if (!empty($galleries)) {
            foreach ($galleries as $key => $val) {
                $data[] = esc_url(wp_get_attachment_image_url($val, 'full'));
            }
        }
        return $data;
    }


    public function list_items($terms, $type) {
        $inc = 1;
        $content = '';
        foreach ($terms as $term_id) {
            $term = get_term_by('id', $term_id, $type);
            if ($inc > 1) {
                $content .= ', ';
            }
            $content .= '<a class="meta-block__value" href="#">'.esc_html($term->name).'</a>';
            $inc++;
        }
        return $content;
    }


    /**
	 * REST API Action
     * 
     * @since v.1.0.0
     * @param ARRAY | Parameters of the REST API
	 * @return ARRAY | Response as Array
	 */
    public function wopb_route_category_data($prams){
        if (!wp_verify_nonce($_REQUEST['wpnonce'], 'wopb-nonce')){
            return rest_ensure_response([]);
        }

        $data = wopb_function()->get_category_data(json_decode($prams['queryCat']), $prams['queryNumber'], $prams['queryType']);
        return rest_ensure_response( $data );
    }


    /**
	 * Image Size Data
     * 
     * @since v.1.0.0
     * @param NULL
	 * @return ARRAY | Response Image Size as Array
	 */
    public function wopb_route_imagesize_data() {
        if (!wp_verify_nonce($_REQUEST['wpnonce'], 'wopb-nonce') && $local){
            return ;
        }
        
        return wopb_function()->get_image_size();
    }


    /**
	 * Post Data Response of REST API
     * 
     * @since v.1.0.0
     * @param MIXED | Pram (ARRAY), Local (BOOLEAN)
	 * @return ARRAY | Response Image Size as Array
	 */
    public function wopb_route_post_data($prams,$local=false) {
        if (!wp_verify_nonce($_REQUEST['wpnonce'], 'wopb-nonce') && $local){
            return ;
        }
    
        $data = [];
        $loop = new \WP_Query( wopb_function()->get_query( $prams ) );

        if($loop->have_posts()){
            while($loop->have_posts()) {
                $loop->the_post(); 
                $var                = array();
                $post_id            = get_the_ID();
                $product            = wc_get_product($post_id);
                $user_id            = get_the_author_meta('ID');
                $var['title']       = get_the_title();
                $var['permalink']   = get_permalink();
                $var['excerpt']     = $product->get_short_description();
                $var['time']        = get_the_date();
                $var['price_sale']  = $product->get_sale_price();
                $var['price_regular']= $product->get_regular_price();
                $var['discount']    = ($var['price_sale'] && $var['price_regular']) ? round( ( $var['price_regular'] - $var['price_sale'] ) / $var['price_regular'] * 100 ).'%' : '';
                $var['sale']        = $product->is_on_sale();
                $var['price_html']  = $product->get_price_html();
                $var['stock']       = $product->get_stock_status();
                $var['featured']    = $product->is_featured();
                $var['rating_count']= $product->get_rating_count();
                $var['rating_average']= $product->get_average_rating();
                $var['type']= $product->get_type();
                $var['wishlist'] = wopb_function()->get_setting('wopb_wishlist') == 'true' ? true : false;
                $var['flipimage'] = wopb_function()->get_flip_image($post_id, $var['title'], 'full', false);

                $time = current_time('timestamp');
		        $time_to = strtotime($product->get_date_on_sale_to());
                $var['deal'] = ($var['price_sale'] && $time_to > $time) ? date('Y/m/d', $time_to) : '';


                // image
                if( has_post_thumbnail() ){
                    $thumb_id = get_post_thumbnail_id($post_id);
                    $image_sizes = wopb_function()->get_image_size();
                    $image_src = array();
                    foreach ($image_sizes as $key => $value) {
                        $image_src[$key] = esc_url(wp_get_attachment_image_src($thumb_id, $key, false)[0]);
                    }
                    $var['image'] = $image_src;
                } else {
                    $var['image']['full'] = esc_url(wc_placeholder_img_src('full'));
                }

                // tag
                $tag = get_the_terms($post_id, (isset($prams['tag'])?esc_attr($prams['tag']):'product_tag'));
                if(!empty($tag)){
                    $v = array();
                    foreach ($tag as $val) {
                        $v[] = array('slug' => $val->slug, 'name' => $val->name, 'url' => esc_url(get_term_link($val->term_id)));
                    }
                    $var['tag'] = $v;
                }

                // cat
                $cat = get_the_terms($post_id, (isset($prams['cat'])?esc_attr($prams['cat']):'product_cat'));
                if(!empty($cat)){
                    $v = array();
                    foreach ($cat as $val) {
                        $v[] = array('slug' => $val->slug, 'name' => $val->name, 'url' => esc_url(get_term_link($val->term_id)));
                    }
                    $var['category'] = $v;
                }
                $data[] = $var;
            }
            wp_reset_postdata();
        }
        return rest_ensure_response( $data );
    }


    /**
	 * Taxonomy Data Response of REST API
     * 
     * @since v.1.0.0
     * @param ARRAY | Parameter (ARRAY)
	 * @return ARRAY | Response Taxonomy List as Array
	 */
    public function wopb_route_taxonomy_data($prams) {
        if (!wp_verify_nonce($_REQUEST['wpnonce'], 'wopb-nonce')){
            return rest_ensure_response([]);
        }
        return rest_ensure_response(wopb_function()->taxonomy($prams['taxonomy']));
    }

    
    /**
	 * Specific Taxonomy Data Response of REST API
     * 
     * @since v.1.0.0
     * @param ARRAY | Parameter (ARRAY)
	 * @return ARRAY | Response Taxonomy List as Array
	 */
    public function wopb_route_taxonomy_info_data($prams) {
        if (!wp_verify_nonce($_REQUEST['wpnonce'], 'wopb-nonce')){
            return rest_ensure_response([]);
        }

        $data = array();
        $terms = get_terms( $prams, array(
            'hide_empty' => true,
        ));
        if( !empty($terms) ){
            foreach ($terms as $val) {
                $data['name'] = $val->name;
                $data['count'] = $val->count;
                $data['url'] = get_term_link($val->term_id);
                $data['color'] = get_term_meta($val->term_id, '_background', true);
            }
        }
        return rest_ensure_response($data);
    }

}