<?php
defined('ABSPATH') || exit;

$wraper_after .= '<div class="wopb-slick-nav" style="display:none">';
    $nav = explode('#', $attr['arrowStyle']);
    $wraper_after .= '<div class="wopb-slick-prev"><div class="slick-arrow slick-prev">'.wopb_function()->svg_icon($nav[0]).'</div></div>';
    $wraper_after .= '<div class="wopb-slick-next"><div class="slick-arrow slick-next">'.wopb_function()->svg_icon($nav[1]).'</div></div>';
$wraper_after .= '</div>';