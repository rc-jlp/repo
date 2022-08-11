<?php
/**
 * Notice Action.
 *
 * @package WOPB\Notice
 * @since v.1.0.0
 */
namespace WOPB;

defined('ABSPATH') || exit;

/**
 * Notice class.
 */
class Notice {

	/**
	 * Setup class.
	 *
	 * @since v.1.0.0
	 */
    public function __construct(){
		add_action('admin_init', array($this, 'admin_init_callback'));
		add_action('wp_ajax_wc_install', array($this, 'wc_install_callback'));
		add_action('admin_action_wc_activate', array($this, 'wc_activate_callback'));
		add_action('wp_ajax_wopb_dismiss_notice', array($this, 'set_dismiss_notice_callback'));

		add_action('admin_init', array($this, 'set_promotional_notice_callback'));
	}

	/**
	 * Promotional Dismiss Notice Option Data
	 * @param NULL
	 * @return NULL
	 */
	public function set_promotional_notice_callback() {
		if (!isset($_GET['disable_productx_notice'])) {
			return ;
        }
        if (sanitize_text_field($_GET['disable_productx_notice']) == 'yes') {
            set_transient( 'wopb_get_pro_notice_v8', 'off', 2592000 ); // 30 days notice
        }
	}


	/**
	 * Dismiss Notice Option Data
     *
     * @since v.1.0.0
	 * @param NULL
	 * @return NULL
	 */
	public function set_dismiss_notice_callback() {
		if (!wp_verify_nonce($_REQUEST['wpnonce'], 'wopb-nonce')) {
			return ;
		}
		update_option( 'dismiss_notice', 'yes' );
	}


	/**
	 * Admin Notice Action Add
     *
     * @since v.1.0.0
	 * @param NULL
	 * @return NULL
	 */
	public function admin_init_callback(){
		if (!file_exists(WP_PLUGIN_DIR.'/woocommerce/woocommerce.php')) {
			add_action('admin_notices', array($this, 'wc_installation_notice_callback'));
		} else if (file_exists(WP_PLUGIN_DIR.'/woocommerce/woocommerce.php') && ! is_plugin_active('woocommerce/woocommerce.php')) {
			add_action('admin_notices', array($this, 'wc_activation_notice_callback'));
		} else {
			// if (get_transient('wopb_initial_user_notice') != 'off') {
			// 	add_action('admin_notices', array($this, 'wopb_promotional_notice_callback'));
			// }
		}
	}


	/**
	 * Promotional Banner Notice
	 * @param NULL
	 * @return NULL
	 */
	public function wopb_promotional_notice_callback() {
		if (get_transient('wopb_get_pro_notice_v8') != 'off') {
			if (!wopb_function()->is_lc_active()) {
				$this->wc_notice_css();
				$this->wc_notice_js();
				?>
				<div class="wc-install wopb-pro-notice">
					<div class="wc-install-body">
						<a href="<?php echo esc_url( add_query_arg('disable_productx_notice', 'yes') ); ?>" class="promotional-dismiss-notice"><span class="dashicons dashicons-no-alt"></span> <?php esc_html_e('Dismiss', 'product-blocks'); ?></a>
						<a target="_blank" href="https://www.wpxpo.com/productx/?utm_campaign=go_premium">
							<img src="<?php echo esc_url(WOPB_URL.'assets/img/banner.jpg'); ?>" alt="logo" />
						</a>
					</div>
				</div>
			<?php
			}
		}
	}


	/**
	 * WooCommerce Installation Notice
     *
     * @since v.1.0.0
	 * @param NULL
	 * @return NULL
	 */
	public function wc_installation_notice_callback() {
		if (!get_option('dismiss_notice')) {
			$this->wc_notice_css();
			$this->wc_notice_js();
			?>
			<div class="wc-install">
				<img width="200" src="<?php echo esc_url(WOPB_URL.'assets/img/WordPress.png'); ?>" alt="logo" />
				<div class="wc-install-body">
					<a class="wc-dismiss-notice" data-security=<?php echo wp_create_nonce('wopb-nonce'); ?>  data-ajax=<?php echo admin_url(admin_url('admin-ajax.php')); ?> href="#"><span class="dashicons dashicons-no-alt"></span> <?php esc_html_e('Dismiss', 'product-blocks'); ?></a>
					<h3><?php esc_html_e('Welcome to Product Blocks.', 'product-blocks'); ?></h3>
					<div><?php esc_html_e('WooCommerce Product Blocks is a WooCommerce plugin. To use this plugins you have to install and activate WooCommerce.', 'product-blocks'); ?></div>
					<a class="wc-install-btn button button-primary button-hero" href="<?php echo esc_url(add_query_arg(array('action' => 'wc_install'), admin_url())); ?>"><span class="dashicons dashicons-image-rotate"></span><?php esc_html_e('Install WooCommerce', 'product-blocks'); ?></a>
					<div id="installation-msg"></div>
				</div>
			</div>
			<?php
		}
	}

	/**
	 * WooCommerce Activation Notice
     *
     * @since v.1.0.0
	 * @param NULL
	 * @return NULL
	 */
	public function wc_activation_notice_callback() {
		if (!get_option('dismiss_notice')) {
			$this->wc_notice_css();
			$this->wc_notice_js();
			?>
			<div class="wc-install">
				<img width="200" src="<?php echo esc_url(WOPB_URL.'assets/img/WordPress.png'); ?>" alt="logo" />
				<div class="wc-install-body">
					<a class="wc-dismiss-notice" data-security=<?php echo wp_create_nonce('wopb-nonce'); ?>  data-ajax=<?php echo esc_url(admin_url('admin-ajax.php')); ?> href="#"><span class="dashicons dashicons-no-alt"></span> <?php esc_html_e('Dismiss', 'product-blocks'); ?></a>
					<h3><?php esc_html_e('Welcome to Product Blocks.', 'product-blocks'); ?></h3>
					<div><?php esc_html_e('WooCommerce Product Blocks is a WooCommerce plugin. To use this plugins you have to install and activate WooCommerce.', 'product-blocks'); ?></div>
					<a class="button button-primary button-hero" href="<?php echo esc_url(add_query_arg(array('action' => 'wc_activate'), admin_url())); ?>"><?php esc_html_e('Activate WooCommerce', 'product-blocks'); ?></a>
				</div>
			</div>
			<?php
		}
	}


	/**
	 * WooCommerce Notice Styles
     *
     * @since v.1.0.0
	 * @param NULL
	 * @return NULL
	 */
	public function wc_notice_css() {
		?>
		<style type="text/css">
            .wc-install {
                display: flex;
                align-items: center;
                background: #fff;
                margin-top: 40px;
                width: calc(100% - 50px);
                border: 1px solid #ccd0d4;
                padding: 12px 15px;
                border-radius: 4px;
                border-left: 4px solid #46b450;
            }
            .wc-install img {
                margin-right: 10px;
            }
            .wc-install-body {
                -ms-flex: 1;
                flex: 1;
            }
            .wc-install-body > div {
                max-width: 450px;
                margin-bottom: 20px;
            }
            .wc-install-body h3 {
                margin-top: 0;
                font-size: 24px;
                margin-bottom: 15px;
            }
            .wc-install-btn {
                margin-top: 15px;
                display: inline-block;
            }
			.wc-install .dashicons{
				display: none;
				animation: dashicons-spin 1s infinite;
				animation-timing-function: linear;
			}
			.wc-install.loading .dashicons {
				display: inline-block;
				margin-top: 12px;
				margin-right: 5px;
			}
			@keyframes dashicons-spin {
				0% {
					transform: rotate( 0deg );
				}
				100% {
					transform: rotate( 360deg );
				}
			}
			.wc-dismiss-notice {
				position: relative;
				text-decoration: none;
				float: right;
				right: 26px;
			}
			.wc-dismiss-notice .dashicons{
				display: inline-block;
    			text-decoration: none;
				animation: none;
			}
			.wopb-pro-notice {
				position: relative;
			}
			.wopb-pro-notice .wc-install-body h3 {
                font-size: 20px;
                margin-bottom: 5px;
            }
            .wopb-pro-notice .wc-install-body > div {
                max-width: 800px;
                margin-bottom: 10px;
            }
            .wopb-pro-notice .button-hero {
                padding: 8px 14px !important;
                min-height: inherit !important;
                line-height: 1 !important;
                box-shadow: none;
                border: none;
                transition: 400ms;
                background: #46b450;
            }
            .wopb-pro-notice .button-hero:hover,
            .wp-core-ui .wopb-pro-notice .button-hero:active {
                background: #389e41;
            }
            .wopb-pro-notice .wopb-btn-notice-pro {
                background: #e5561e;
                color: #fff;
            }
            .wopb-pro-notice .wopb-btn-notice-pro:hover,
            .wopb-pro-notice .wopb-btn-notice-pro:focus {
                background: #ce4b18;
            }
            .wopb-pro-notice .button-hero:hover,
            .wopb-pro-notice .button-hero:focus {
                border: none;
                box-shadow: none;
            }
			.wopb-pro-notice img {
                width: 100%;
            }
			.wopb-pro-notice .promotional-dismiss-notice {
				background-color: #fff;
                padding-top: 0px;
                position: absolute;
                right: 0;
                top: 6px;
    			padding: 10px;
    			border-radius: 0 0 0 5px;
            }
		</style>
		<?php
	}


	/**
	 * WooCommerce Notice JavaScript
     *
     * @since v.1.0.0
	 * @param NULL
	 * @return NULL
	 */
	public function wc_notice_js() {
		?>
		<script type="text/javascript">
			jQuery(document).ready(function($){
				'use strict';
				$(document).on('click', '.wc-install-btn', function(e){
					e.preventDefault();
					const $that = $(this);
					$.ajax({
						type: 'POST',
						url: ajaxurl,
						data: {install_plugin: 'woocommerce', action: 'wc_install'},
						beforeSend: function(){
                            $that.parents('.wc-install').addClass('loading');
                        },
						success: function (data) {
							$('#installation-msg').html(data);
							$that.parents('.wc-install').remove();
						},
						complete: function () {
							$that.parents('.wc-install').removeClass('loading');
						}
					});
				});

				// Dismiss notice
				$(document).on('click', '.wc-dismiss-notice', function(e){
					e.preventDefault();
					const that = $(this);
					$.ajax({
						url: that.data('ajax'),
						type: 'POST',
						data: {
							action: 'wopb_dismiss_notice',
							wpnonce: that.data('security')
						},
						success: function (data) {
							that.parents('.wc-install').hide("slow", function() { that.parents('.wc-install').remove(); });
						},
						error: function(xhr) {
							console.log('Error occured. Please try again' + xhr.statusText + xhr.responseText );
						},
					});
				});

			});
		</script>
		<?php
	}


	/**
	 * WooCommerce Force Install Action
     *
     * @since v.1.0.0
	 * @param NULL
	 * @return NULL
	 */
	public function wc_install_callback(){
		include(ABSPATH . 'wp-admin/includes/plugin-install.php');
		include(ABSPATH . 'wp-admin/includes/class-wp-upgrader.php');

		if (! class_exists('Plugin_Upgrader')){
			include(ABSPATH . 'wp-admin/includes/class-plugin-upgrader.php');
		}
		if (! class_exists('Plugin_Installer_Skin')) {
			include( ABSPATH . 'wp-admin/includes/class-plugin-installer-skin.php' );
		}

		$plugin = 'woocommerce';

		$api = plugins_api( 'plugin_information', array(
			'slug' => $plugin,
			'fields' => array(
				'short_description' => false,
				'sections' => false,
				'requires' => false,
				'rating' => false,
				'ratings' => false,
				'downloaded' => false,
				'last_updated' => false,
				'added' => false,
				'tags' => false,
				'compatibility' => false,
				'homepage' => false,
				'donate_link' => false,
			),
		) );

		if ( is_wp_error( $api ) ) {
			wp_die( $api );
		}

		$title = sprintf( __('Installing Plugin: %s'), $api->name . ' ' . $api->version );
		$nonce = 'install-plugin_' . esc_html($plugin);
		$url = 'update.php?action=install-plugin&plugin=' . urlencode( $plugin );

		$upgrader = new \Plugin_Upgrader( new \Plugin_Installer_Skin( compact('title', 'url', 'nonce', 'plugin', 'api') ) );
		$upgrader->install($api->download_link);
		die();
	}


	/**
	 * WooCommerce Redirect After Active Action
     *
     * @since v.1.0.0
	 * @param NULL
	 * @return NULL
	 */
	public function wc_activate_callback(){
		activate_plugin('woocommerce/woocommerce.php');
		wp_redirect(admin_url('admin.php?page=wopb-option-settings'));
		exit();
	}

}