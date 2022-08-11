(function($) {
    'use strict';
    
    // ------------------------
    // Quick Add to Quickview Button
    // ------------------------
    $(document).on('click', '.wopb-quickview-btn', function(e){
        e.preventDefault();
        const _modal = $('.wopb-modal-wrap');
        const _postId = $(this).data('postid');
        const _postList = $(this).data('list');
        if(_postId){
            $.ajax({
                url: wopb_quickview.ajax,
                type: 'POST',
                data: {
                    action: 'wopb_quickview',
                    postid: _postId,
                    postList: _postList,
                    wpnonce: wopb_quickview.security
                },
                beforeSend: function() {
                    _modal.find('.wopb-modal-body').html('');
                    _modal.addClass('active');
                    _modal.find('.wopb-modal-loading').addClass('active');
                },
                success: function(data) {
                    _modal.find('.wopb-modal-body').html(data);
                    const width = $(window).width()-40;
                    quickViewElement(_modal);
                },
                complete:function() {
                    _modal.find('.wopb-modal-loading').removeClass('active');
                },
                error: function(xhr) {
                    console.log('Error occured.please try again' + xhr.statusText + xhr.responseText );
                },
            });
        }
    });

    // ------------------------
    // Quick View Element Initialize
    // ------------------------
    function quickViewElement(_modal) {
        _modal.find(".ct-increase").remove();
        _modal.find(".ct-decrease").remove();
        $('.quickview-slider').slick({
            dots: true,
            infinite: true,
            speed: 300,
            slidesToShow: 1,
            adaptiveHeight: true
        });

        if ($('.wopb-quick-view-modal .wopb-quick-view-image .wopb-thumbnails img').length > 1) {
            $('.wopb-quick-view-modal .wopb-quick-view-image .wopb-thumbnails').slick({
                slidesToShow: 1,
                slidesToScroll: 1,
                dots: true,
                arrows: true,
            });
        }
        const form_variation = _modal.find(".variations_form");
        const wc_product_gallery = _modal.find(".woocommerce-product-gallery");

        wc_product_gallery.each( function() {
            $( this ).trigger( 'wc-product-gallery-before-init', [ this, wc_single_product_params ] );

            $( this ).wc_product_gallery( wc_single_product_params );

            $( this ).trigger( 'wc-product-gallery-after-init', [ this, wc_single_product_params ] );

            $( this ).wc_product_gallery(  );
        } );

        form_variation.each(function () {
            $(this).wc_variation_form();
            if(wopb_quickview.isVariationSwitchActive == 'true') {
                $(this).loopVariationSwitcherForm();
            }
        });
    }

})( jQuery );