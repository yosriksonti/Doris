;(function($, window, document, undefined){
    const AuxinRemoveCartContent = function() {
        // Remove cart content
        $(document).on( 'click', '.aux-remove-cart-content', function(e) {
            e.preventDefault();

            var product_id   = $(this).data("product_id");
            var verify_nonce = $(this).data("verify_nonce");
            var $cartBoxEl   = $(this).closest('.aux-cart-wrapper').addClass('aux-cart-remove-in-progress');

            $.ajax({
                type: 'POST',
                dataType: 'json',
                url: auxin.ajax_url,
                data: {
                    action: "auxels_remove_from_cart",
                    product_id: product_id,
                    verify_nonce: verify_nonce,
                },
                success: function( response ){
                    // Remove old notification
                    $(".woocommerce-message, .woocommerce-error").remove();
                    // Start Notifications
                    if( response.success ) {
                        $('.aux-hidden-blocks').append( response.data.notif );

                        if( parseInt(response.data.total) === 0 ) {
                            $('.aux-card-dropdown').html(response.data.empty);
                            $('.aux-cart-contents').find('span').remove();
                        } else {
                            $('.aux-card-item').filter(function(){
                                return $(this).data('product-id') == product_id;
                            }).remove();
                            $('.aux-cart-contents').find('span').text(response.data.count);
                        }
                        $(".aux-cart-subtotal").each(function() {
                            $(this).find('.woocommerce-Price-amount').contents().filter(function(){
                                return this.nodeType == 3;
                            })[0].nodeValue = response.data.total;
                        });
                        $cartBoxEl.removeClass('aux-cart-remove-in-progress');
                    } else {
                        $('.aux-hidden-blocks').append( response.data );
                    }
                }
            });

        });
    };

    const AuxinAjaxAddToCart = function() {
        // Add Content to Cart
        $(document).on( 'click', '.aux-ajax-add-to-cart', function(e) {
            var productType  = $(this).data("product-type");

            if ( productType !== 'simple') {
                return;
            }

            e.preventDefault();

            var product_id   = $(this).data("product_id");
            var quantity     = $(this).data("quantity");
            var verify_nonce = $(this).data("verify_nonce");
            var $cartBoxEl   = $('.aux-cart-wrapper');
            var hasAnimation = $cartBoxEl.hasClass('aux-basket-animation') ? true : false;

            $cartBoxEl.trigger('AuxCartInProgress');

            if ( $(this).parents('.aux-shop-quicklook-modal') ) {
                quantity = $(this).parents('.aux-shop-quicklook-modal').find('.quantity input').val();
            }

            $.ajax({
                type: 'POST',
                dataType: 'json',
                url: auxin.ajax_url,
                data: {
                    action      : "auxels_add_to_cart",
                    args        : auxin_cart_options,
                    product_id  : product_id,
                    quantity    : quantity,
                    verify_nonce: verify_nonce
                },
                success: function( response ){
                    // Remove old notification
                    $(".woocommerce-message, .woocommerce-error").remove();
                    // Start Notifications
                    if( response.success ) {
                        $('.aux-hidden-blocks').append( response.data.notif );

                        setTimeout( function(){
                            if ( hasAnimation ) {
                                $cartBoxEl.on('AuxCartProgressAnimationDone', function(e) {
                                    $cartBoxEl.find('.aux-card-dropdown').html( response.data.items );
                                    $cartBoxEl.find('.aux-shopping-basket').html( response.data.total );
                                    $cartBoxEl.trigger('AuxCartUpdated');
                                });
                            } else {
                                $cartBoxEl.find('.aux-card-dropdown').html( response.data.items );
                                $cartBoxEl.find('.aux-shopping-basket').html( response.data.total );
                                $cartBoxEl.trigger('AuxCartUpdated');
                            }
                        }, 150);


                    } else {
                        $('.aux-hidden-blocks').append( response.data );
                    }

                }

            });

        });
    };

    $(document).ready(function(){
        AuxinRemoveCartContent();
        AuxinAjaxAddToCart();
    });
})(jQuery,window,document);