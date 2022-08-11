/**
 *  Custom global scripts
 */

 jQuery(document).ready(function( $ ){

    // For Hover Effect <Reusable>
    const main_container = document.querySelectorAll('.wp-container-21, .wp-container-23, .wp-container-25');
    for (const box of main_container) {
        box.classList.add('box-hover');
    }
    const main_container_2 = document.querySelectorAll('.wp-container-20, .wp-container-22, .wp-container-24');
    for (const box of main_container_2) {
        box.classList.add('box-hover2');
    }

    $('.wc-block-product-search__button').append('<div>Search</div>');    

 });