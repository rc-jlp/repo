<?php

$product_count = 3;
$query = array(
    'posts_per_page' => $product_count,
    'post_type' => 'product',
    'tax_query' => array(
        array(
            'taxonomy' => 'product_visibility',
            'field'    => 'name',
            'terms'    => 'featured',
        )
    ),
    'order' => 'DESC',
    'post_status' => 'publish'
);
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
.wrapper{
    grid-template-columns: 400px 400px 400px;
    display: grid;
    gap: 21px;
}

.lz-product-grid.wrapper .lz-single-product img {
    width: 400px;
    height: 400px;
    /* padding: 11px; */
    gap: 21px;
    grid-template-columns: 200px 200px 200px;
    display: grid;
    object-fit: cover;
}
.lz-product-grid.wrapper .lz-single-product {
    width: 100%;
    height: auto;
}

@media only screen and (max-width:820px){
    .wrapper{
    grid-template-columns: 384px 384px 384px;
}
}
@media only screen and (max-width:820px){

    .lz-product-grid.wrapper{
        display: flex;
        gap: 21px;
        width: 273px;
    }
    .lz-product-grid.wrapper .lz-single-product {
    width: 100%;
    flex-basis: -3%;
    height: auto;
    }
    .lz-product-grid.wrapper .lz-single-product img {
    width: 239px;
    height: 244px;
    padding: 24px;
    gap: -23px;
    object-fit: cover;
    }
}
@media only screen and (max-width:720px){
    .lz-product-grid.wrapper {
    display: block !important;
    gap: 21px;
    }
    .lz-product-grid.wrapper .lz-single-product img {
    width: 370px;
    padding: 24px;
    gap: -23px;
    object-fit: cover;
    }
}
@media only screen and (max-width:415px){
    .lz-product-grid.wrapper {
    display: block !important;
    gap: 21px;
    }
    .lz-product-grid.wrapper .lz-single-product img {
    width: 370px;
    padding: 24px;
    gap: -23px;
    object-fit: cover;
    }
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
            <?php 
                if( $product->get_sale_price() ) : 
                    $percentage = round( 100 - ( $product->get_sale_price() / $product->get_regular_price() * 100) );
            ?>
                <p class="lz-pd-percentage">-<?php echo $percentage; ?>%</p>
            <?php endif; ?>

            <img class="lz-pd-image" onclick="location.href='<?php the_permalink(); ?>';" src="<?php echo get_the_post_thumbnail_url(); ?>" />
            <p class="lz-pd-short"><?php the_title();?></p>
            <p class="lz-pd-price">$ <?php echo $product->get_price(); ?></p>
            <p class="lz-pd-price"><?php the_excerpt(); ?></p>
            <p class="lz-pd-price"><?php  echo wc_get_product_category_list( $product->get_id());  ?></p>
            <!-- <div class="lz-pd-overlay">
                <div class="lz-overlay-inner">
                    <a class="lz-cart" href="<?php echo get_the_permalink(); ?>">View Product</a>
                    <?php echo do_shortcode( "[add_to_cart id='".get_the_ID()."' show_price='false' quantity='1']" ); ?>
                    <div class="lz-pd-options">
                        <div class="lz-like-container">
                        </div>
                    </div>
                </div>
            </div> -->
        </div> 
    <?php endwhile; endif; wp_reset_postdata(); ?>
</div>