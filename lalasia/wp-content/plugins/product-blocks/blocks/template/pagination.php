<?php
defined('ABSPATH') || exit;

$wraper_after .= '<div class="wopb-pagination-wrap'.($attr["paginationAjax"] ? " wopb-pagination-ajax-action" : "").'" data-paged="1" data-blockid="'.esc_attr($attr['blockId']).'" data-postid="'.esc_attr($page_post_id).'" data-pages="'.esc_attr($pageNum).'" data-blockname="product-blocks_'.esc_attr($block_name).'" '.wopb_function()->get_builder_attr().'>';
    $wraper_after .= wopb_function()->pagination($pageNum, $attr['paginationNav'], $attr['paginationText']);
$wraper_after .= '</div>';