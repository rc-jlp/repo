<?php
defined('ABSPATH') || exit;

$wraper_before .= '<div class="wopb-filter-wrap" data-taxtype='.esc_attr($attr['filterType']).' data-blockid="'.esc_attr($attr['blockId']).'" data-blockname="product-blocks_'.esc_attr($block_name).'" data-postid="'.esc_attr($page_post_id).'">';
    $wraper_before .= wopb_function()->filter($attr['filterText'], $attr['filterType'], $attr['filterCat'], $attr['filterTag'], $attr['filterAction'], $attr['filterActionText'], $noAjax, $attr['filterMobileText'], $attr['filterMobile']);
$wraper_before .= '</div>';