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
 * Compatibility class.
 */
class Compatibility{

    /**
	 * Setup class.
	 *
	 * @since v.1.1.0
	 */
    public function __construct(){
        add_action( 'upgrader_process_complete', array($this, 'plugin_upgrade_completed'), 10, 2 );
    }

    /**
	 * Compatibility Class Run after Plugin Upgrade
	 *
	 * @since v.1.1.0
	 */
    public function plugin_upgrade_completed( $upgrader_object, $options ) {
        if( $options['action'] == 'update' && $options['type'] == 'plugin' && isset( $options['plugins'] ) ){
            foreach( $options['plugins'] as $plugin ) {
                if( $plugin == WOPB_BASE ) {
                    
                    $set_settings = array(
                        'wopb_compare'    => 'true',
                        'wopb_flipimage'  => 'true',
                        'wopb_quickview'  => 'true',
                        'wopb_templates'  => 'true',
                        'wopb_wishlist'   => 'true',
                        'wopb_builder'    => 'true',
                        'wopb_variation_swatches' => 'true'
                    );
                    $addon_data = get_option('wopb_options', array());
                    foreach ($set_settings as $key => $value) {
                        if (!isset($addon_data[$key])) {
                            wopb_function()->set_setting($key, $value);
                        }
                    }

                    // License Check And Active
                    if (defined('WOPB_PRO_VER')) {
                        $license = get_option( 'edd_wopb_license_key' );
                        $response = wp_remote_post( 
                            'https://www.wpxpo.com',
                            array(
                                'timeout' => 15,
                                'sslverify' => false,
                                'body' => array(
                                    'edd_action' => 'activate_license',
                                    'license'    => $license,
                                    'item_id'    => 1263,
                                    'url'        => home_url()
                                )
                            )
                        );
                        if ( !is_wp_error( $response ) && 200 == wp_remote_retrieve_response_code( $response ) ) {
                            $license_data = json_decode( wp_remote_retrieve_body( $response ) );
                            update_option( 'edd_wopb_license_status', $license_data->license );    
                        }
                    }
                    
                }
            }
        }
    }

}