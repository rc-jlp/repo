<?php
defined( 'ABSPATH' ) || exit;

/**
 * SaveTemplate Addons Initial Configuration
 * @since v.1.1.0
 */
add_filter('wopb_addons_config', 'wopb_templates_config');
function wopb_templates_config( $config ) {
	$configuration = array(
		'name' => __( 'Saved Templates', 'product-blocks' ),
		'desc' => __( 'Create Short-codes and use them inside your page or page builder.', 'product-blocks' ),
		'img' => WOPB_URL.'/assets/img/addons/saved-template.svg',
		'is_pro' => false
	);
	$config['wopb_templates'] = $configuration;
	return $config;
}

/**
 * Require Main File
 * @since v.1.1.0
 */
add_action('init', 'wopb_templates_init');
function wopb_templates_init() {
	$settings = wopb_function()->get_setting();
	if ( isset($settings['wopb_templates']) ) {
		if ($settings['wopb_templates'] == 'true') {
			require_once WOPB_PATH.'/addons/templates/Saved_Templates.php';
			require_once WOPB_PATH.'/addons/templates/Shortcode.php';
			new \WOPB\Saved_Templates();
			new \WOPB\Shortcode();
		}
	}
}