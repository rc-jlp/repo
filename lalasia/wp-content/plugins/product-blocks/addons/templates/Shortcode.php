<?php
/**
 * Shortcode Core.
 * 
 * @package WOPB\Shortcode
 * @since v.1.1.0
 */

namespace WOPB;

defined('ABSPATH') || exit;

class Shortcode {
    public function __construct(){
        add_shortcode('product_blocks', array($this, 'shortcode_callback'));
    }

    /**
	 * Shortcode Callback
     * 
     * @since v.1.1.0
	 * @return STRING | HTML of the shortcode
	 */
    function shortcode_callback( $atts = array(), $content = null ) {
        extract(shortcode_atts(array(
         'id' => ''
        ), $atts));

        $content = '';
        $id = is_numeric( $id ) ? (float) $id : false;
        if ($id) {
            $init = new \WOPB\Initialization();
            $init->register_scripts_common();
            $extra = wopb_function()->set_css_style($id);
            $content_post = get_post($id);
            if ($content_post->post_status == 'publish' && $content_post->post_password == '') {
                $content = $content_post->post_content;
                $content = apply_filters('the_content', $content);
                $content = str_replace(']]>', ']]&gt;', $content);
                return $extra.'<div class="wopb-shortcode" data-postid="'.esc_attr($id).'">' . wp_kses_post($content) . '</div>';
            }
        }
        return '';
    }
    
}