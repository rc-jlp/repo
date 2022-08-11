<?php
defined('ABSPATH') || exit;

$wraper_after .= '<div class="wopb-loadmore">';
    $wraper_after .= '<span class="wopb-loadmore-action" data-pages="'.esc_attr($pageNum).'" data-pagenum="1" data-blockid="'.esc_attr($attr['blockId']).'" data-blockname="product-blocks_'.esc_attr($block_name).'" data-postid="'.esc_attr($page_post_id).'" '.wopb_function()->get_builder_attr().'>'.esc_html( isset($attr['loadMoreText']) ? $attr['loadMoreText'] : 'Load More' ).' <span class="wopb-spin">'.wopb_function()->svg_icon('refresh').'</span></span>';
$wraper_after .= '</div>';