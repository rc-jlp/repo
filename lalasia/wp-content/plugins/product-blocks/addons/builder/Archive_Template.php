<?php
defined( 'ABSPATH' ) || exit;

get_header();

do_action( 'wopb_before_content' );

$page_id = wopb_function()->conditions('return');

$width = get_post_meta($page_id, '__wopb_container_width', true);

if ($width) {
    echo '<div class="wopb-builder-container product" style="max-width: '.esc_attr($width).'px; margin: 0 auto;">';
}

if ($page_id) {
    if ( have_posts() ) :
        the_post();
        if ($page_id) {
            $content_post = get_post($page_id);
            $content = $content_post->post_content;
            if (has_blocks($content)) {
                $blocks = parse_blocks( $content );
                foreach ( $blocks as $block ) {
                    echo do_shortcode(render_block( $block ));
                }
            } else {
                the_content();
            }
        }
    endif;
}

if ($width) {
    echo '</div>';
}

do_action( 'wopb_after_content' );

get_footer();