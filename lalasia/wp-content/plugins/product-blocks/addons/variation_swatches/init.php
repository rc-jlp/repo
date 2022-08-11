<?php
defined( 'ABSPATH' ) || exit;

/**
 * Variation Swatches Initial Configuration
 * @since v.2.2.7
 */
add_filter('wopb_addons_config', 'wopb_variation_swatches_config');
function wopb_variation_swatches_config( $config ) {
	$configuration = array(
		'name' => __( 'Variation Swatches', 'product-blocks' ),
		'desc' => __( 'Variation Swatches to Your Blocks.', 'product-blocks' ),
		'img' => WOPB_URL.'/assets/img/addons/variation_switcher.svg',
		'is_pro' => false
	);
	$config['wopb_variation_swatches'] = $configuration;
	return $config;
}

/**
 * Require Main File
 * @since v.2.2.7
 */
add_action('wp_loaded', 'wopb_variation_swatches_init');
function wopb_variation_swatches_init(){
	$settings = wopb_function()->get_setting();
	if ( isset($settings['wopb_variation_swatches']) ) {
		if ($settings['wopb_variation_swatches'] == 'true') {
			require_once WOPB_PATH.'/addons/variation_swatches/VariationSwatches.php';
			$obj = new \WOPB\VariationSwatches();
			if( !isset($settings['variation_switch_heading']) ){
				$obj->initial_setup();
			}
		}
	}
}