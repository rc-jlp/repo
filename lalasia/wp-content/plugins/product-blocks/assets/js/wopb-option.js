(function($) {
    'use strict';

    // Shortcode OnClick Copy
    $(".wopb-shortcode-copy").click(function(e) {
        e.preventDefault();
        const that = $(this);
        navigator.clipboard.writeText(that.text());
        that.append("<span>Copied!</span>");
        setTimeout( function(){ that.find('span').remove(); }, 500 );
    });

    // WooCommerce Tab
    $(document).on('click', '.wc-tabs li', function(e){
        e.preventDefault();
        $('.wc-tabs li').removeClass('active');
        $(this).addClass('active');
        const selectId = $(this).attr('aria-controls');
        $('.woocommerce-Tabs-panel').hide();
        $('.woocommerce-Tabs-panel#'+selectId).show();
    });

    $( '.wopb-color-picker' ).wpColorPicker();

    // Add target blank for upgrade button
    $('.toplevel_page_wopb-settings ul > li > a').each(function (e) {
        if($(this).attr('href').indexOf("?wopb=plugins") > 0) {
            $(this).attr('target', '_blank');
        }
    });

    if($('body').hasClass('block-editor-page')){
        $('body').addClass('wopb-editor-'+wopb_option.width);
    }

    $(document).on('click', '.wopb-addons-enable', function(e){
        const that = this
        $.ajax({
            url: wopb_option.ajax,
            type: 'POST',
            data: {
                action: 'wopb_addon', 
                addon: $(that).data('addon'),
                value: this.checked,
                wpnonce: wopb_option.security
            },
            success: function(data) {
                if ( $(that).data('addon') == 'wopb_templates' || $(that).data('addon') == 'wopb_builder' ) {
                    location.reload();
                }
            },
            error: function(xhr) {
                console.log('Error occured.please try again' + xhr.statusText + xhr.responseText );
            },
        });
    });

    const actionBtn = $('.page-title-action');
    const savedBtn = $(".wopb-saved-templates-action");
    if ( savedBtn.length > 0 ) {
        if(savedBtn.data())
        actionBtn.addClass('wopb-save-templates-pro').text( savedBtn.data('text') )
        actionBtn.attr( 'href', savedBtn.data('link') )
        actionBtn.attr( 'target', '_blank' )
    }


    // ------------------------
    // Upload Flip Feature Image 
    // ------------------------
	var file_frame;
	function upload_feature_image( button ) {
		const button_id = button.attr('id');
		const field_id = button_id.replace( '_button', '' );
		if ( file_frame ) {
            file_frame.open();
            return;
		}
		file_frame = wp.media.frames.file_frame = wp.media({
            title: $(this).data( 'uploader_title' ),
            button: { text: $(this).data( 'uploader_button_text' ) },
            multiple: false
		});
		file_frame.on( 'select', function() {
            const attachment = file_frame.state().get('selection').first().toJSON();
            $('#'+field_id).val(attachment.id);
            $('#flipimage-feature-image img').attr('src',attachment.url);
            $('#flipimage-feature-image img').show();
            $('#' + button_id).attr('id', 'remove_feature_image_button');
            $('#remove_feature_image_button').text('Remove Flip Image');
		});
		file_frame.open();
	};

	$('#flipimage-feature-image').on( 'click', '#upload_feature_image_button', function(e) {
        e.preventDefault();
		upload_feature_image($(this));
	});

	$('#flipimage-feature-image').on( 'click', '#remove_feature_image_button', function(e) {
		e.preventDefault();
		$( '#upload_feature_image' ).val( '' );
		$( '#flipimage-feature-image img' ).attr( 'src', '' );
		$( '#flipimage-feature-image img' ).hide();
		$( this ).attr( 'id', 'upload_feature_image_button' );
		$( '#upload_feature_image_button' ).text( 'Set Flip Image' );
    });
    
    // Add URL for Gutenberg Post Blocks
    $(document).on('click', '#plugin-information-content ul > li > a', function(e){
        const URL = $(this).attr('href');
        if (URL.includes('downloads/product-blocks-pro')) {
            e.preventDefault();
            window.open("https://www.wpxpo.com/productx/");
        }
    });

    //productx tab script in woocommerce product page in admin
    $('.wopb-productx-options-tab-wrap .wopb-tab-title-wrap .wopb-tab-title').click(function () {
        $(this).closest('.wopb-productx-options-tab-wrap').find('.wopb-tab-title').removeClass('active').eq(jQuery(this).index()).addClass('active');
        $(this).closest('.wopb-productx-options-tab-wrap').find('.wopb-tab-content').removeClass('active').eq(jQuery(this).index()).addClass('active');
    });

    $('.wopb-productx-options-tab-wrap .wopb-tab-title-wrap .wopb-tab-title:first-child').each(function () {
        $(this).closest('.wopb-productx-options-tab-wrap').find('.wopb-tab-title').removeClass('active').eq(jQuery(this).index()).addClass('active');
        $(this).closest('.wopb-productx-options-tab-wrap').find('.wopb-tab-content').removeClass('active').eq(jQuery(this).index()).addClass('active');
    })


    /* ---- variation swatches --- */
    //open media library
    $('#wopb-term-upload-img-btn').click(function (e) {
        e.preventDefault();
        let object = $(this);
        let mdeia = wp.media({
            title: 'Attribute Term Image',
            multiple: false
        }).open().on('select', function (e) {
            let selectedImage = mdeia.state().get('selection').first().toJSON();
            object.parent().prev("#wopb-term-img-thumbnail").find("img").attr("src", selectedImage.sizes.thumbnail.url);
            object.parent().find("#wopb-term-img-remove-btn").removeClass('d-none');
            object.parent().find('#wopb-term-img-input').val(selectedImage.id);
        });
    });

    //remove image from attribute
    $('#wopb-term-img-remove-btn').click(function (e) {
        $(this).parent().prev("#wopb-term-img-thumbnail").find("img").attr("src", wopb_option.url + 'assets/img/wopb-placeholder.jpg');
        $(this).parent().find('#wopb-term-img-input').val('');
    })
    /* ---- end variation swatches --- */

})( jQuery );