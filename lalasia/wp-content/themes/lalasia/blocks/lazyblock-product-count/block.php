<?php
/**
 * Template for custom Product Grid
 */

 $product_count = $attributes['product-count'];
//  $product_count = 3;
//  $product_query = $attributes['product-query'];

$query = array(
    'posts_per_page' => $product_count,
    'post_type' => 'product',
    // 'tax_query' => array(
    //     array(
    //         'taxonomy' => 'product_visibility',
    //         'field'    => 'name',
    //         'terms'    => 'featured',
    //     )
    // ),
    'order' => 'DESC',
    'post_status' => 'publish'
);

//  include_once get_theme_file_path( '/blocks/defaults.php' );
?>

<style>
.lz-single-product {
    width: 22%;
    height: auto;
}
h3.lz-pd-name {
    margin: 25px 16px 5px;
    font-size: 18px;
    font-weight: 600;
    font-family: 'Roboto';
}
.lz-pd-short {
    font-size: 18px;
    color: #898989;
    font-weight: 300;
    margin: 0px 16px 8px!important;
}
p.lz-pd-price {
    margin: 0px 16px 16px;
    font-size: 24px;
    color: #4C394F;
    font-weight: 500;
}

</style>

<div class="lz-product-grid wrapper">
    <?php
        $get_products = new WP_Query( $query );
        if( $get_products->have_posts() ) :
            while ( $get_products->have_posts() ) : $get_products->the_post();
            $product = wc_get_product( get_the_ID() );
    ?>
        <div class="lz-single-product">
            <?php if( $product->get_sale_price() ) : $percentage = round( 100 - ( $product->get_sale_price() / $product->get_regular_price() * 100) );?>
                <p class="lz-pd-percentage">-<?php echo $percentage; ?>%</p>
            <?php endif; ?>
            <img class="lz-pd-image" onclick="location.href='<?php the_permalink(); ?>';" src="<?php echo get_the_post_thumbnail_url(); ?>" />
            <h3 class="lz-pd-name" onclick="location.href='<?php the_permalink(); ?>';" ><?php the_title();?></h3>
          
            <p class="lz-pd-short"><?php //echo $product->get_attribute( 'brand' ) ? "Brand: ".$product->get_attribute( 'brand' ) : 'Unbranded'; ?></p>
            <p class="lz-pd-price">$ <?php echo $product->get_price(); ?></p>
            <p class="lz-pd-price"><?php the_excerpt(); ?></p>
            <p class="lz-pd-price"><?php  echo wc_get_product_category_list( $product->get_id());  ?></p>

            <div class="lz-pd-overlay">
                <div class="lz-overlay-inner">
                    <a class="lz-cart" href="<?php echo get_the_permalink(); ?>">View Product</a>
                    <?php echo do_shortcode( "[add_to_cart id='".get_the_ID()."' show_price='false' quantity='1']" ); ?>
                    <div class="lz-pd-options">
                        <!-- <div class="lz-share-container">
                            <a class="lz-pd-share">Share</a>
                            <div class="lz-share-options">
                                <?php //echo do_shortcode( '[addtoany url="'. get_the_permalink() .'" title="'. get_the_title() .'" buttons="facebook,twitter,email"]' ); ?>
                            </div> 
                        </div> -->
                        <div class="lz-like-container">
                            <?php //echo do_shortcode( '[yith_wcwl_add_to_wishlist product_id="'. get_the_ID() .'" label="Like"]' ); ?>
                        </div>
                    </div>
                </div>
            </div>
        </div> 
    <?php endwhile; endif; wp_reset_postdata(); ?>
</div>