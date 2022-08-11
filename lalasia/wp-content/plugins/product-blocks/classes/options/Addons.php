<?php
namespace WOPB;

defined('ABSPATH') || exit;

class Options_Addons{
    public function __construct() {
        add_submenu_page('wopb-settings', __('Addons', 'product-blocks'), __('Addons', 'product-blocks'), 'manage_options', 'wopb-addons', array( $this, 'create_admin_page'), 10);

        if (!wopb_function()->is_lc_active()) {
            add_filter('woocommerce_product_data_tabs', [$this, 'wopb_productx_tab_data'], 10, 1);
            add_action('woocommerce_product_data_panels', [$this,'wopb_productx_custom_field_simple']);
        }
    }

    // For Preview Data
    public function wopb_productx_tab_data( $product_data_tabs ) {
        $product_data_tabs['productx'] = array(
            'label'  => __( 'ProductX Preorder', 'product-blocks' ),
            'class'  => array( 'show_if_simple', 'hidden' ),
            'target' => 'productx_tab_data',
        );
        return $product_data_tabs;
    }
    public function wopb_productx_custom_field_simple(){
        global $post;
        echo '<div class="panel woocommerce_options_panel hidden" id="productx_tab_data">';
            echo '<div class="wopb-productx-options-tab-wrap">';
                echo '<div id="wopb-preorder-field-group-pro-instruction">';
                    echo '<a href="https://www.wpxpo.com/productx" target="_blank">';
                        echo '<img style="max-width: 100%;" src="'.esc_url(WOPB_URL.'/assets/img/addons/preorder-pro.png').'">';
                    echo '</a>';
                echo '</div>';
            echo '</div>';
        echo '</div>';
    }


    public static function all_addons(){
        $all_addons = array(
            'wopb_builder' => array(
                'name' => __( 'Builder', 'product-blocks' ),
                'desc' => __( 'Design template for Archive, Category, Custom Taxonomy, Date and Search Page.', 'product-blocks' ),
                'img' => WOPB_URL.'assets/img/addons/builder.svg',
                'is_pro' => false
            ),
            'wopb_compare' => array(
                'name' => __( 'Compare', 'product-blocks' ),
                'desc' => __( 'Add Compare to Your Blocks.', 'product-blocks' ),
                'img' => WOPB_URL.'/assets/img/addons/compare.svg',
                'is_pro' => false
            ),
            'wopb_flipimage' => array(
                'name' => __( 'Flip Image', 'product-blocks' ),
                'desc' => __( 'Add Flip Image Option for Blocks.', 'product-blocks' ),
                'img' => WOPB_URL.'/assets/img/addons/imageFlip.svg',
                'is_pro' => false
            ),
            'wopb_quickview' => array(
                'name' => __( 'Quickview', 'product-blocks' ),
                'desc' => __( 'Add Quickview to Your Blocks.', 'product-blocks' ),
                'img' => WOPB_URL.'/assets/img/addons/quickview.svg',
                'is_pro' => false
            ),
            'wopb_templates' => array(
                'name' => __( 'Saved Templates', 'product-blocks' ),
                'desc' => __( 'Create Short-codes and use them inside your page or page builder.', 'product-blocks' ),
                'img' => WOPB_URL.'/assets/img/addons/saved-template.svg',
                'is_pro' => false
            ),
            'wopb_wishlist' => array(
                'name' => __( 'Wishlist', 'product-blocks' ),
                'desc' => __( 'Add Wishlist to Your Blocks.', 'product-blocks' ),
                'img' => WOPB_URL.'/assets/img/addons/wishlist.svg',
                'is_pro' => false
            ),
            'wopb_preorder' => array(
                'name' => __( 'Pre-order', 'product-blocks' ),
                'desc' => __( 'Add Pre-order to WooCommerce.', 'product-blocks' ),
                'img' => WOPB_URL.'/assets/img/addons/pre-order.svg',
                'is_pro' => true
            ),
            'wopb_stock_progress_bar' => array(
                'name' => __( 'Stock Progress Bar', 'product-blocks' ),
                'desc' => __( 'Add Stock Progress Bar to WooCommerce.', 'product-blocks' ),
                'img' => WOPB_URL.'/assets/img/addons/stock-progress-bar.svg',
                'is_pro' => true
            ),
            'wopb_call_for_price' => array(
                'name' => __( 'Call for Price', 'product-blocks' ),
                'desc' => __( 'Add Call for Price to WooCommerce.', 'product-blocks' ),
                'img' => WOPB_URL.'/assets/img/addons/call-for-price.svg',
                'is_pro' => true
            ),
            'wopb_backorder' => array(
                'name' => __( 'Backorder', 'product-blocks' ),
                'desc' => __( 'Add Backorder Option to WooCommerce.', 'product-blocks' ),
                'img' => WOPB_URL.'/assets/img/addons/backorder.svg',
                'is_pro' => true
            ),
            'wopb_partial_payment' => array(
                'name' => __( 'Partial Payment', 'product-blocks' ),
                'desc' => __( 'Add Partial Payment to WooCommerce.', 'product-blocks' ),
                'img' => WOPB_URL.'/assets/img/addons/partial-payment.svg',
                'is_pro' => true
            )
        );
        return apply_filters('wopb_addons_config', $all_addons);
    }

    

    /**
     * Settings page output
     */
    public function create_admin_page() { ?>
        <style>
            .style-css{
                background-color: #f2f2f2;
                -webkit-font-smoothing: subpixel-antialiased;
            }
        </style>

        <div class="wopb-option-body">

            <?php require_once WOPB_PATH . 'classes/options/Heading.php'; ?>

            <div class="wopb-content-wrap wopb-addons-wrap">
                <h2 class="wopb-settings-heading"><?php esc_html_e('All Addons', 'product-blocks'); ?></h2>
                <div class="wopb-addons-items">
                    <?php
                        $option_value = wopb_function()->get_setting();
                        $addons_data = self::all_addons();
                        foreach ($addons_data as $key => $val) {
                            echo '<div class="wopb-addons-item wopb-admin-card">';
                                echo '<div class="wopb-addons-item-content">';
                                    echo '<img src="'.esc_url($val['img']).'" />';
                                    echo '<h4>'.esc_html($val['name']).'</h4>';
                                    echo '<div class="wopb-addons-desc">'.esc_html($val['desc']).'</div>';
                                echo '</div>';
                            if( $val['is_pro'] && !defined('WOPB_PRO_VER') ){
                                echo '<div class="wopb-addons-btn">';
                                    echo '<a class="wopb-btn wopb-btn-default" target="_blank" href="'.esc_url(wopb_function()->get_premium_link()).'">'.esc_html__("Get Pro", "product-blocks").'</a>';
                                echo '</div>';

                            } else {
                                echo '<div class="wopb-addons-btn">';
                                    echo '<label class="wopb-switch">';
                                        echo '<input class="wopb-addons-enable" '.(($val['is_pro'] && (!defined('WOPB_PRO_VER'))) ? 'disabled' : '').' data-addon="'.esc_attr($key).'" type="checkbox" '.( isset($option_value[$key]) && $option_value[$key] == 'true' ? 'checked' : '' ).'>';
                                        echo '<span class="wopb-slider wopb-round"></span>';
                                    echo '</label>';
                                echo '</div>';
                            }
                            echo '</div>';
                        }
                    ?>
                </div>
            </div>
        </div>

    <?php }

}