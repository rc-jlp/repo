<?php
defined( 'ABSPATH' ) || exit;

/**
 * Compare Addons Initial Configuration
 * @since v.1.1.0
 */
add_filter('wopb_addons_config', 'wopb_compare_config');
function wopb_compare_config( $config ) {
	$configuration = array(
		'name' => __( 'Compare', 'product-blocks' ),
		'desc' => __( 'Add Compare to Your Blocks.', 'product-blocks' ),
		'img' => WOPB_URL.'/assets/img/addons/compare.svg',
		'is_pro' => false
	);
	$config['wopb_compare'] = $configuration;
	return $config;
}

/**
 * Require Main File
 * @since v.1.1.0
 */
add_action('wp_loaded', 'wopb_compare_init');
function wopb_compare_init(){
	$settings = wopb_function()->get_setting();
	if ( isset($settings['wopb_compare']) ) {
		if ($settings['wopb_compare'] == 'true') {
			require_once WOPB_PATH.'/addons/compare/Compare.php';
			$obj = new \WOPB\Compare();
			if( !isset($settings['compare_heading']) ){
				$obj->initial_setup();
			}
		}
	}
}