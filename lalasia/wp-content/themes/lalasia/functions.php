<?php
function child_theme_enqueue_styles() {
    wp_enqueue_style( 'parent-style', get_template_directory_uri() . '/style.css' );
    wp_enqueue_style( 'global-css', get_stylesheet_directory_uri() . '/assets/css/global.css' );

    wp_enqueue_script( 'global-js', get_stylesheet_directory_uri() . '/assets/js/global.js');
    wp_enqueue_style( 'Inter', 'https://fonts.googleapis.com/css2?family=Inter:wght@400;500;700&display=swap' );
}
add_action( 'wp_enqueue_scripts', 'child_theme_enqueue_styles' );

// Get Elements of Woocommerce Product
function add_shordesc() { // gets(short desc)
    the_excerpt();
}
add_action( 'woocommerce_after_shop_loop_item_title', 'add_shordesc', 2 );
function add_category() { //  gets(cat)
    global $product;
    echo wc_get_product_category_list( $product->get_id());
}
add_action( 'woocommerce_shop_loop_item_title', 'add_category', 9 );


 
























