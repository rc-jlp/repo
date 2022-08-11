<?php
defined( 'ABSPATH' ) || exit;

/**
 * Flip Image Addons Initial Configuration
 * @since v.1.1.0
 */
add_filter('wopb_addons_config', 'wopb_flipimage_config');
function wopb_flipimage_config( $config ) {
	$configuration = array(
		'name' => __( 'Flip Image', 'product-blocks' ),
		'desc' => __( 'Add Flip Image Option for Blocks.', 'product-blocks' ),
		'img' => WOPB_URL.'/assets/img/addons/imageFlip.svg',
		'is_pro' => false
	);
	$config['wopb_flipimage'] = $configuration;
	return $config;
}

/**
 * Require Main File
 * @since v.1.1.0
 */
add_action('wp_loaded', 'wopb_flipimage_init');
function wopb_flipimage_init() {
	$settings = wopb_function()->get_setting();
	if ( isset($settings['wopb_flipimage']) ) {
		if ($settings['wopb_flipimage'] == 'true') {
			require_once WOPB_PATH.'/addons/flipimage/FlipImage.php';
			$obj = new \WOPB\FlipImage();
			if( !isset($settings['flipimage_heading']) ){
				$obj->initial_setup();
			}
		}
	}
}