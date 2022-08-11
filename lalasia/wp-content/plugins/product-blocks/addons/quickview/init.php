<?php
defined( 'ABSPATH' ) || exit;

/**
 * Quickview Addons Initial Configuration
 * @since v.1.1.0
 */
add_filter('wopb_addons_config', 'wopb_quickview_config');
function wopb_quickview_config( $config ) {
	$configuration = array(
		'name' => __( 'Quickview', 'product-blocks' ),
		'desc' => __( 'Add Quickview to Your Blocks.', 'product-blocks' ),
		'img' => WOPB_URL.'/assets/img/addons/quickview.svg',
		'is_pro' => false
	);
	$config['wopb_quickview'] = $configuration;
	return $config;
}

/**
 * Require Main File
 * @since v.1.1.0
 */
add_action('wp_loaded', 'wopb_quickview_init');
function wopb_quickview_init(){
	$settings = wopb_function()->get_setting();
	if ( isset($settings['wopb_quickview']) ) {
		if ($settings['wopb_quickview'] == 'true') {
			require_once WOPB_PATH.'/addons/quickview/Quickview.php';
			$obj = new \WOPB\Quickview();
			if( !isset($settings['quickview_heading']) ){
				$obj->initial_setup();
			}
		}
	}
}