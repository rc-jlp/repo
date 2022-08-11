<?php
defined( 'ABSPATH' ) || exit;

/**
 * Wishlist Addons Initial Configuration
 * @since v.1.1.0
 */
add_filter('wopb_addons_config', 'wopb_wishlist_config');
function wopb_wishlist_config( $config ) {
	$configuration = array(
		'name' => __( 'Wishlist', 'product-blocks' ),
		'desc' => __( 'Add Wishlist to Your Blocks.', 'product-blocks' ),
		'img' => WOPB_URL.'/assets/img/addons/wishlist.svg',
		'is_pro' => false
	);
	$config['wopb_wishlist'] = $configuration;
	return $config;
}

/**
 * Require Main File
 * @since v.1.1.0
 */
add_action('wp_loaded', 'wopb_wishlist_init');
function wopb_wishlist_init(){
	$settings = wopb_function()->get_setting();
	if ( isset($settings['wopb_wishlist']) ) {
		if ($settings['wopb_wishlist'] == 'true') {
			require_once WOPB_PATH.'/addons/wishlist/Wishlist.php';
			$obj = new \WOPB\Wishlist();
			if( !isset($settings['wishlist_heading']) ){
				$obj->initial_setup();
			}
		}
	}
}