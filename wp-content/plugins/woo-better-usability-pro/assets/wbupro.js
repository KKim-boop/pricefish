jQuery(document).ready(function($){
    var handledAddedToCartEvents = false;
    var wbuUpdateSyncTimeout = null;

    wbuQtyChangeProduct = function(qtyElement) {

        vProductId = $('input[name="product_id"]').val();
        variationId = $('input[name="variation_id"]').val();
        isVariable = $('input[name="variation_id"]').length;
        productId = ( vProductId > 0 ? vProductId : $('button[name="add-to-cart"]').val() );
        newQty = qtyElement.val();

        if ( typeof productId == 'undefined' ) {
            addToCartBtn = qtyElement.parent().parent().find('.add_to_cart_button');
            productId = addToCartBtn.attr('data-product_id');
        }

        if ( isVariable && !(variationId > 0 ) ) {
            return;
        }
        
        // add text on button when updating
        if ( typeof btnTextBefore == 'undefined' ) {
            btnTextBefore = $('button[name="add-to-cart"]').html();
        }

        $('button[name="add-to-cart"]').html( wbuSettings.product_updating_text );

        var opts = {
            action: 'wbu_update_product',
            quantity: newQty,
            product_id: productId,
            variation_id: variationId
        };

        // pass extra checked WooCommerce Product Addons
        var sumAddons = wbuSumProductAddons();
        if ( sumAddons > 0 ) {
            opts.sum_addons = sumAddons;
        }

        wbuEnqueueAjax({
            data: opts,
            type: 'post',
            url: wbuInfo.ajaxUrl,
            beforeSend: function() {
                if ( wbuSettings.product_ajax_lock == 'yes' ) {
                    wbuBlock( $('.product') );
                }
            },
            success: function(resp) {
                wbuPriceBoxUpdate(resp);
                $('button[name="add-to-cart"]').html( btnTextBefore );
                wbuUnblock( $('.product') );
            }
        });
    };

    wbuSumProductAddons = function() {
        var amount = 0;

        $('.addon:checked').each(function(){
            var price = parseFloat(jQuery(this).data('raw-price'));

            if ( price > 0 ) {
                amount += price;
            }
        });

        return amount;
    };

    wbuPriceBoxUpdate = function(html) {
        if ( isVariable ) {
            var priceBox = $('.variations_form').find('.woocommerce-variation-price');
        }
        else {
            var priceBox = $('.summary p.price');
        }

        if ( priceBox.length === 0 ) {
            var priceBox = $('.summary .price');
        }

        priceBox.html(html);
    };

    wbuMiniCartAjaxQuantityChange = function(cartItemKey, inputId, newQuantity) {
        wbuEnqueueAjax({
            data: {
                action: 'wbu_alter_quantity',
                quantity: newQuantity,
                cart_item_key: cartItemKey
            },
            type: 'post',
            dataType: 'json',
            url: wbuInfo.ajaxUrl,
            beforeSend: function() {
                if ( wbuSettings.minicart_sync_block == 'yes' ) {
                    wbuBlockProductsUI( $('.widget_shopping_cart') );
                }
            },
            success: function(resp) {
                if ( wbuSettings.minicart_sync_block == 'yes' ) {
                    wbuUnblockProductsUI();
                }
                
                if ( !$('.xt_woofc-list').length ) {
                    // tell do WC reload widget contents
                    $( document.body ).trigger( 'updated_wc_div' );

                    // trigger for 3rd plugins event listeners
                    $( document.body ).trigger( 'wbu_minicart_updated', [ resp.product_id ] );

                    // trigger Added to cart
                    $( document.body ).trigger( 'added_to_cart' );
                }

                // find the <li> for the respective product on shop/category page
                var productId = resp.product_id;
                var liProduct = $('.post-' + productId + ',.elementor-page-' + productId);

                // make it works with shortcodes, eg.: [add_to_cart id="XX"]
                if ( !liProduct.length ) {
                    liProduct = $('[data-product_id="'+productId+'"]').parent();
                }

                // update the quantity input to keep in sync with minicart
                if ( liProduct.length ) {
                    liProduct.find('.qty').val( newQuantity );
                }

                // update shop product quantity if exists, for the cases where qty changed to zero in minicart
                if ( newQuantity == 0 ) {
                    wbuProReplicateMiniCartQtyWithShop();
                }

                wbuProHideQtyWhenNotAddedToCart();
            }
        });
    };

    // function extracted from WAC PRO plugin
    wbuProReplicateMiniCartQtyWithShop = function() {
        $('.widget_shopping_cart').find('.qty').each(function(){
            var input = $(this);
            var qty = $(this).val();
            var name = $(this).attr('id');

            $('.qty').each(function(){
                if ( $(this).attr('id') == name ) {
                    $(this).val( qty );
                } 
            });
        });

        $('.products').find('.qty').each(function(){
            var input = $(this);
            var qty = $(this).val();
            var name = $(this).attr('id');

            if ( !$('.widget_shopping_cart').find('#' + name).length ) {
                $(this).val(0);
            }
        });
    };

    wbuProReplicateMiniCartQtyWithShopV2 = function() {
        if ( jQuery('.widget_shopping_cart').find('.qty').length ) {
            jQuery('.widget_shopping_cart').find('.qty').each(function(){
                var input = jQuery(this);
                var qty = jQuery(this).val();
                var name = jQuery(this).attr('id');
    
                jQuery('.qty').each(function(){
                    if ( jQuery(this).attr('id') == name ) {
                        jQuery(this).val( qty );
                    } 
                });
            });
        }
        else {
            // clear AJAX search input quantities
            $('.dgwt-wcas-pd-addtc').find('.qty').val(0);

            jQuery('.widget_shopping_cart').find('.mini_cart_item').each(function(){
                var qty = parseInt( jQuery(this).find('.quantity').html() );
                var productId = jQuery(this).find('.remove').data('product_id');
                var prodQtyInput = $('#qty_prod_' + productId);

                // supports for premium ajax search plugin
                if ( !prodQtyInput.length ) {
                    prodQtyInput = $('.dgwt-wcas-pd-addtc').find('a[data-product_id="'+productId+'"]').parent().find('.qty');
                }

                if ( prodQtyInput.length ) {
                    prodQtyInput.val(qty);
                }
            });
        }

        // check items in cart page (when cart widget not available)
        jQuery('.woocommerce-cart-form').find('.cart_item').each(function(){
            var qty = parseInt( jQuery(this).find('.qty').val() );
            var productId = jQuery(this).find('.remove').data('product_id');
            var prodQtyInput = $('.dgwt-wcas-pd-addtc').find('a[data-product_id="'+productId+'"]').parent().find('.qty');

            if ( prodQtyInput.length ) {
                prodQtyInput.val(qty);
            }
        });

        // other stuff
        jQuery('.products').find('.cart').each(function(){
            var form = jQuery(this);
            var inputQty = form.find('.qty');
            var qtyDiv = form.find('.quantity');
            var btnAddCart = form.find('.add_to_cart_button');

            if ( !inputQty.length ) {
                return;
            }

            var name = inputQty.attr('id');

            if ( name == null ) {
                return;
            }

            var productId = name.replace('qty_prod_', '');

            if ( !jQuery('.widget_shopping_cart').find('[data-product_id="'+productId+'"]').length ) {
                jQuery(this).val(0);

                if ( wbuSettings.hide_qty_when_zero == 'yes' ) {
                    qtyDiv.hide();
                    btnAddCart.show();
                }
            }
        });
    };

    wbuAddItemRemoveEffect = function() {
        if ( wbuSettings.item_remove_fadeout !== 'yes' ) {
            return;
        }
        
        $('.product-remove a').on('click.wbupro_remove_click', function(evt){

            if ( ( wbuSettings.item_remove_fadeout !== 'yes' ) || $(this).attr('bypass_effect') ) {

                // call jquery ajax instead window.location refresh page
                jQuery.get($(this).attr( 'href' ), function( response ) {
                    if ( wbuInfo.isShop ) {
                        wbuProReloadEntireCart();
                    }
                });

                // when in shop page, return FALSE to not reload page when remove
                // but in the cart, keep the normal woocommerce flux
                continueFlux = !wbuInfo.isShop;

                return continueFlux;
            }

            evt.preventDefault();

            productTR = $(this).closest('tr');
            link = $(this);
            //timeout = parseInt(wbuSettings.item_remove_time_fadeout);
            timeout = 2000;

            productTR.fadeOut(timeout, function() {
                link.attr('bypass_effect', 1).click();
                productTR.remove();
                wbuAddItemRemoveEffect();
            });

            return false;
        });
    };
    
    // override the lite version function
    wbuAfterCallUpdateCart = function(qtyElement) {
       // fadeout effect on "Cart updated" message
        if ( wbuInfo.isCart && wbuSettings.cart_update_message_fadeout == 'yes' ) {
            setTimeout(function(){ fadeoutCartUpdateMessage(1); }, 500);
        }
    };
    
    fadeoutCartUpdateMessage = function(times) {
        if ( $('.woocommerce-message').length ) {
            
            // var delay = parseInt( wbuSettings.cart_update_time_fadeout );
            var delay = 2000;
            
            $('.woocommerce-message').stop(true, true).show().fadeOut(delay, function(){
                $('.woocommerce-message').remove();
            });
        }
        else if ( times <= 15 ) {
            setTimeout(function(){ fadeoutCartUpdateMessage( times++ ); }, 500);
        }
    };
    
    wbuProListenQtyChangeForPriceUpdate = function() {
        if ( wbuInfo.isSingleProduct && wbuSettings.enable_auto_update_product == 'yes' ) {

            if ( $('.single_variation_wrap').length ) {
                var qtySelector = '.single_variation_wrap .qty';
            }
            else {
                var qtySelector = '.summary .qty';
            }
            
            // compatibility with UNI CPO plugin
            if ( $('.uni-cpo-calculate-btn').length ) {
                wbuProListenUniCPOEvents(qtySelector);
            }
            else {
                $(document.body).on('change', qtySelector, function(){
                    return wbuQtyChangeProduct( $(this) );
                });

                $(document.body).on('keyup', qtySelector, function(){
                    if ( parseInt($(this).val()) > 0 ) {
                        return wbuQtyChangeProduct( $(this) );
                    }
                });
    
                $(document).on('found_variation', function(){
                    wbuQtyChangeProduct( $(qtySelector) );
                });

                // listen WooCommerce Product Addons clicks
                $(document.body).on('click', '.addon', function(){
                    wbuQtyChangeProduct( $(qtySelector) );
                });
            }
        }
    };

    wbuProListenUniCPOEvents = function(qtySelector) {
        $(qtySelector).bind('change keyup', function(e) {
            $('.uni-cpo-calculate-btn').trigger('click');
            return false;
        });

        $(document.body).on('uni_cpo_options_data_ajax_success', function(x, req, resp){
            if ( 'price_vars' in resp ) {
                var pv = resp.price_vars;
				var price = pv.currency + parseFloat(pv.raw_price_tax_rev).toFixed(2);
				var total = pv.currency + parseFloat(pv.raw_total).toFixed(2);
				// var calculation = ' <span class="product-price-explain">(' + pv.quantity + ' × ' + price + ')</span>';

                $('.woocommerce-Price-amount').html(total);
                // $('.woocommerce-price-suffix').hide();
            }
        });

        $(document.body).on('change', '.js-uni-cpo-field', function(){
            $('.uni-cpo-calculate-btn').trigger('click');
        });
    };

    wbuListenMiniCartQtyChange = function() {
        if ( wbuSettings.minicart_allow_change_qty != 'yes' ) {
            return;
        }

        $(document.body).on('change', '.widget_shopping_cart_content .qty', function(){
            return wbuChangeCartItemQuantity( $(this) );
        });

        $(document.body).on('change', '.minicart-content-wrapper .qty', function(){
            return wbuChangeCartItemQuantity( $(this) );
        });

        // make compatibility with Woo Floating Cart plugin
        if ( $('.xt_woofc-list').length ) {
            $( document ).off('xt_woofc_product_update.wbufc1').on( 'xt_woofc_product_update.wbufc1', function(obj, cartKey){
                var findQty = $('input[name="cart[' + cartKey + '][qty]');
                wbuChangeCartItemQuantity( findQty );
            });

            $( document ).off('xt_woofc_undo_product_remove.wbufc2').on( 'xt_woofc_undo_product_remove.wbufc2', function(obj, cartKey){
                var findQty = $('input[name="cart[' + cartKey + '][qty]');
                wbuChangeCartItemQuantity( findQty );
            });

            $( document ).off('xt_woofc_product_removed.wbufc3').on( 'xt_woofc_product_removed.wbufc3', function(obj, cartKey){
                var checkFn = function() {
                    var prodId = $('.xt_woofc-deleted').data('id');
                    $('.post-' + prodId).find('.qty').val(0);
                }

                setTimeout(checkFn, 100);
            });
        }
    };

    // synchronization from minicart quantity input to shop/single product page
    wbuChangeCartItemQuantity = function(qtyElement){
        var matches = qtyElement.attr('name').match(/cart\[(\w+)\]/);
        var cartItemKey = matches[1];
        var inputId = qtyElement.attr('id');

        // run code with timeout
        wbuProClearUpdateSyncTimeout();

        wbuUpdateSyncTimeout = setTimeout(function(){
            $('.woocommerce-mini-cart__total').html( wbuSettings.product_updating_text );
            wbuMiniCartAjaxQuantityChange( cartItemKey, inputId, qtyElement.val() );
        }, wbuSettings.ajax_timeout);
    };
    
    wbuProCheckoutDeleteAjax = function() {
        if ( !wbuInfo.isCheckout || wbuSettings.checkout_make_ajax_request_delete !== 'yes' ) {
            return;
        }
        
        $( document ).on('click', 'a.remove', function(evt){
            evt.preventDefault();

            wbuEnqueueAjax({
                data: {
                    action: 'wbu_delitem_checkout',
                    security: wc_checkout_params.update_order_review_nonce,
                    data_remove_link: $(this).attr('href')
                },
                type: 'post',
                url: wbuInfo.ajaxUrl,
                beforeSend: function() {
                    wbuBlockUI( $( '.woocommerce-checkout-payment, .woocommerce-checkout-review-order-table' ) );
                },
                success: function(response) {
                    // $( '.woocommerce-checkout-payment, .woocommerce-checkout-review-order-table' ).unblock();

                    $( 'body' ).trigger( 'update_checkout' );
                }
            });
        });
    };

    wbuProRefreshMiniCartOnCheckout = function() {
        $( document.body ).on('update_checkout', function(){
            if ( $('.woocommerce-mini-cart').length ) {
                $( document.body ).trigger( 'updated_wc_div' );
            }
        });
    };

    wbuProProductAddCartAjaxVariable = function() {
        if ( !wbuInfo.isSingleProduct || wbuSettings.product_ajax_add_to_cart !== 'yes' || wbuSettings.product_addcart_ajax_variable !== 'yes' ) {
            return;
        }

        wbuProHandleVariableAjaxAddToCart(null);
    };

    wbuProHandleVariableAjaxAddToCart = function(eventAlias) {
        eventAlias = eventAlias != null ? eventAlias : 'wbupro_hvaatc';

        $('.single_add_to_cart_button_variable').on('click.' + eventAlias, function(){
            var form = $(this).closest('.variations_form');

            if ( $(this).hasClass('disabled') || !form.attr('action') ) {
                return true;
            }

            if ( form.length ) {
                // make the button display loading as single product
                $(this).addClass('add_to_cart_button ajax_add_to_cart');

                var formData = form.serializeArray();
                var hasSpecialCond = ( $('.rightpress_live_product_price').length || $('.wf-lato-n7-active').length );
                var btnAddToCart = $(this);

                formData.push({ name: 'wbu_variable_adding', value: 'yes' });

                if ( hasSpecialCond ) {
                    $.ajax({
                        url: form.attr('action'),
                        type: 'POST',
                        data: form.serialize(),
                        beforeSend: function() {
                            btnAddToCart.addClass('loading');
                        },
                        success: function(result) {
                            btnAddToCart.removeClass('loading');

                            $( document.body ).trigger( 'updated_wc_div' );
                            $( document.body ).trigger( 'updated_cart_totals' );
                            $( '.cart-icon' ).click();
                        }
                    });
 
                    return false;
                }
                else if ( !$('.jsn-master').length ) {
                    for ( key in formData ) {
                        obj = formData[key];

                        $(this).removeAttr('data-' + obj.name);
                        $(this).attr('data-' + obj.name, obj.value);

                        $(this).removeData(obj.name);
                        $(this).data(obj.name, obj.value);
                    }
                }
            }

            return true;
        });
    };

    wbuProMakeCartWorkOnShop = function() {
        if ( !wbuInfo.isShop || ( wbuSettings.make_cart_work_on_shop !== 'yes' ) ) {
            return;
        }

        wbuProReloadEntireCart();
        
        // hide empty cart message div
        $('.cart-empty').hide();
    };
    
    wbuProReloadEntireCart = function() {
        var page = window.location.toString().replace( 'add-to-cart', 'added-to-cart' );

        // get entire shop page and reload only page-description div contents (cart)
        $( '.page-description' ).load( page + ' .page-description:eq(0) > *', function() {
            $( document.body ).trigger( 'cart_page_refreshed' );
            $( document.body ).trigger( 'cart_totals_refreshed' );
            $( document.body ).trigger( 'updated_wc_div' );
        });
    };
    
    wbuProSyncQtyWithCartOnShop = function() {
        // this function works also on Related products

        // !wbuInfo.isShop && !wbuInfo.isSingleProduct &&
        if ( wbuSettings.enable_quantity_on_shop !== 'yes' || wbuSettings.shop_sync_qty_with_cart !== 'yes' ) {
            return;
        }

        wbuProListenQtyChangeForSync('.products');
        wbuProListenQtyChangeForSync('.product-items');
        wbuProListenQtyChangeForSync('.elementor-section-wrap');
        wbuProListenQtyChangeForSync('.product-list-owl');
        wbuProListenQtyChangeForSync('.search-results-wrap');
        wbuProListenQtyChangeForSync('.dgwt-wcas-pd-addtc');

        wbuProMiniCartSyncWithQuantityInput();
    };

    wbuProSyncQtyWithCartOnProduct = function() {
        if ( !wbuProSyncQtyWithCartProductEnabled() ) {
            return;
        }

        wbuProListenQtyChangeForSync('.summary');
        wbuProListenQtyChangeForSync('.elementor-add-to-cart');
        
        wbuProMiniCartSyncWithQuantityInput();
    };

    wbuProSyncQtyWithCartProductEnabled = function() {
        return ( wbuInfo.isSingleProduct && wbuSettings.product_ajax_add_to_cart === 'yes' && wbuSettings.product_sync_qty_with_cart === 'yes' );
    };

    wbuProClearUpdateSyncTimeout = function() {
        // clear previous timeout, if exists
        if ( wbuUpdateSyncTimeout !== null ) {
            clearTimeout(wbuUpdateSyncTimeout);
        }
    };
    
    // function used to single product pages and shop synchronization with minicart
    // 1: make ajax to remove the product from cart, 2: trigger add to cart button with new quantity to add
    wbuProListenQtyChangeForSync = function(productSelector) {
        var qtySelector = productSelector + ' .qty';

        $(document.body).on('change', qtySelector, function(){
            var _qtyInput = $(this);
            var isInsideSearchBox = (_qtyInput.closest('.dgwt-wcas-pd-addtc').length > 0);
            var callFunc = function() {
                var qtyInput = wbuProDealWithQtyInput(_qtyInput);
                var btnAddCart = wbuProFindBtnAddCart(qtyInput);
    
                if ( !btnAddCart || !btnAddCart.hasClass('product_type_simple') ) {
                    return;
                }
    
                if ( wbuSettings.qty_sync_block_screen == 'yes' ) {
                    wbuBlockProductsUI(null);
                }
    
                newQuantity = parseInt(qtyInput.val());
                miniCartIsPresent = ( $('.widget_shopping_cart_content,.elementor-menu-cart__main').length > 0 );
                productId = btnAddCart.data('product_id');
    
                $(document).trigger('wbu_before_qtychange_ajax');
                
                btnAddCart.attr('data-quantity', newQuantity);
                btnAddCart.data('quantity', newQuantity);
                btnAddCart.data('is_wbu_quantity_sync', 1);
                btnAddCart.trigger('click');
                
                $(document).trigger('wbu_after_qtychange_ajax');
                
                if ( !(qtyInput.val() > 0 ) ) {
                    wbuProHideQtyWhenNotAddedToCart();
                }
            };

            if ( isInsideSearchBox ) {
                callFunc();
            }
            else {
                // run code with timeout
                wbuProClearUpdateSyncTimeout();

                wbuUpdateSyncTimeout = setTimeout(function(){
                    callFunc();
                }, wbuSettings.ajax_timeout);
            }
        });
    };

    wbuProDealWithQtyInput = function(qtyInput) {
        // find the right quantity input
        if ( ( qtyInput.prop('nodeName') != 'INPUT' ) && ( qtyInput.prop('nodeName') != 'SELECT' ) ) {
            qtyInput = qtyInput.find('.qty');
        }

        // protection to avoid multiple ajax calls
        // if ( qtyInput.hasClass('wbu-processing') ) {
        //     return;
        // }

        qtyInput.addClass('wbu-processing');

        setTimeout(function(){
            qtyInput.removeClass('wbu-processing');
        }, 500);

        return qtyInput;
    };

    wbuProFindBtnAddCart = function(qtyInput) {
        var btnAddCart = null;

        // add support for Salient theme
        if ( qtyInput.closest('li.classic').length ) {
            btnAddCart = qtyInput.closest('li.classic').find('.add_to_cart_button:first');
        }
        else {
            btnAddCart = qtyInput.parent().parent().find('.add_to_cart_button:first');
        }

        // support for GoMarket theme
        if ( !btnAddCart.length ) {
            btnAddCart = qtyInput.parent().parent().parent().find('.add_to_cart_button:first');
        }

        return btnAddCart;
    };

    wbuProMiniCartSyncWithQuantityInput = function() {
        // handle mini cart remove button
        // when remove item from mini cart, then sets quantity value to zero
        
        // not sync when in variable product
        if ( $('.variation_id').length ) {
            return;
        }

        $(document.body).on('click', '.remove_from_cart_button,.delete', function(){
            thisbutton = $( this ),
            product_id = thisbutton.data('product_id');
            btnAddCart = $("[data-product_id='"+ product_id +"']");
            
            qtyElement = btnAddCart.parent().find('.qty');
            qtyElement.val(0);
            
            wbuProHideQtyWhenNotAddedToCart();
        });
    };

    wbuProHideQtyWhenNotAddedToCart = function() {
        if ( wbuSettings.hide_qty_when_zero != 'yes' ) {
            return;
        }

        var fnHideQtyWhenNotAdded = function(){
            var productDiv = $(this);
            var qtyDiv = productDiv.find('.quantity:first');
            var qtyInput = qtyDiv.find('.qty');
            var qtyCount = parseInt(qtyInput.val());
            var btnAddCart = productDiv.find('.add_to_cart_button');
            
            if ( !btnAddCart.hasClass('product_type_simple') ) {
                return;
            }

            if ( qtyCount > 0 ) {
                btnAddCart.hide();
            }
            else {
                btnAddCart.show();
                btnAddCart.addClass('is_autohide_mode');
                btnAddCart.removeClass('added');
                btnAddCart.parent().find('.added_to_cart').detach();

                // sets timeout to apply the quantity after wbuProListenQtyChangeForSync() call
                setTimeout(function(){
                    btnAddCart.data('quantity', '1');
                    btnAddCart.attr('data-quantity', '1');
                }, 100);

                qtyDiv.hide();

                btnAddCart.off('click.wbupro_click238').on('click.wbupro_click238', function(){
                    if ( !$(this).hasClass('is_autohide_mode') ) {
                        return;
                    }

                    $(this).hide();
                    $(this).removeClass('is_autohide_mode');

                    $("<style type='text/css'>.added_to_cart { display: none; }</style>").appendTo('head');

                    if ( wbuSettings.minicart_sync_block == 'yes' ) {
                        wbuBlockProductsUI(null);
                    }

                    qtyDiv.show();
                    qtyInput.val(1);
                });
            }
        };

        $('.product').each(fnHideQtyWhenNotAdded);
        $('.dgwt-wcas-product-details').each(fnHideQtyWhenNotAdded);
    };

    wbuBlockUI = function(element) {
        element.block({
            message: null,
            overlayCSS: {
                background: '#fff',
                opacity: 0.6
            }
        });
    };

    wbuUnblockUI = function(element) {
        element.unblock();
    };

    wbuBlockProductsUI = function(element) {
        if ( element != null ) {
            wbuBlockUI( element );
        }
        else if ( $('.product').length && !wbuInfo.isShop ) {
            wbuBlockUI( $('.product') );
        }
        else {
            wbuBlockUI( $('.products') );
        }
    };

    wbuUnblockProductsUI = function() {
        wbuUnblockUI( $('.products') );
        wbuUnblockUI( $('.product') );
        wbuUnblockUI( $('.widget_shopping_cart') );
    };

    wbuProShopInlineVariations = function() {
        if ( wbuSettings.shop_inline_variations != 'yes' ) {
            return;
        }

        // handle Add To Cart button for the inline variable feature
        $('.single_add_to_cart_button').on('click', function(){
            if ( !$(this).hasClass('product_type_variable') ) {
                return true;
            }

            if ( $(this).hasClass('disabled') ) {
                alert(wc_add_to_cart_variation_params.i18n_make_a_selection_text);
                return false;
            }

            return true;
        });

        wbuProCommonAfterAddCartEvents();
        wbuProHandleInlineVariationsDisplay();
        wbuProHandleVariableAjaxAddToCart(null);
    };

    wbuProCommonAfterAddCartEvents = function() {
        if ( handledAddedToCartEvents ) {
            return;
        }
        else {
            handledAddedToCartEvents = true;
        }

        $(document).on('added_to_cart', function(){
            $( document.body ).trigger( 'updated_wc_div' );
            $( document.body ).trigger( 'updated_cart_totals' );
        });
    };

    wbuProHandleInlineVariationsDisplay = function() {
        if ( wbuSettings.inline_variation_mode == 'manual_expand' ) {
            $('.product_type_variable').each(function(){
                if ( !$(this).hasClass('single_add_to_cart_button') ) {
                    $(this).on('click', function(){
                        var variationsForm = $(this).parent().find('form');
                        variationsForm.find('.variations,.single_variation_wrap').slideToggle();

                        return false;
                    }).trigger('click');
                }
            });
        }
        else if ( wbuSettings.inline_variation_mode == 'always_visible' ) {
            // hide all Select options buttons on Shop page
            $('.product_type_variable').each(function(){
                if ( !$(this).hasClass('single_add_to_cart_button') ) {
                    $(this).hide();
                }
            });
        }

        setTimeout(function(){
            $('.product_type_variable').show();
        }, 1000);
    };

    wbuProHandleCommonCartEvents = function() {
        $(document).on('removed_from_cart', function(){
            wbuAddItemRemoveEffect();
        });
        
        $(document).on('added_to_cart', function(){
            // trigger to update top cart on variable product
            if ( wbuInfo.isSingleProduct && wbuSettings.product_ajax_add_to_cart === 'yes' && wbuSettings.product_addcart_ajax_variable === 'yes' ) {
                wbuProCommonAfterAddCartEvents();
                $( '.cart-icon' ).trigger('click');
            }
    
            wbuProMakeCartWorkOnShop();
            wbuAddItemRemoveEffect();
            wbuProHideQtyWhenNotAddedToCart();

            if ( wbuSettings.qty_sync_block_screen == 'yes' || wbuSettings.minicart_sync_block == 'yes' ) {
                wbuUnblockProductsUI();
            }

            // added custom code for a client
            setTimeout(function(){
                $('.product_type_variable').show();
            }, 1000);
        });

        $(document).on('updated_wc_div', function(){
            wbuProHideQtyWhenNotAddedToCart();

            if ( $('.dgwt-wcas-pd-addtc').length ) {
                wbuProReplicateMiniCartQtyWithShopV2();
            }
        });
    
        // add CSS rule to hide "Add to cart" and "Added to cart" buttons
        if ( wbuInfo.isSingleProduct && wbuSettings.hide_product_addtocart_button === 'yes' ) {
            $('.ajax_add_to_cart').hide();
            $("<style type='text/css'>.ajax_add_to_cart,.added_to_cart { display: none; }</style>").appendTo('head');
        }
    };

    wbuProFlatsomeQuickViewCompat = function() {
        $(document.body).bind('DOMSubtreeModified', function(e) {
            if ( $('.product-quick-view-container').length && !$('.product-quick-view-container').hasClass('wbupro') ) {
                $('.product-quick-view-container').addClass('wbupro');

                var btAddCart = $('.product-quick-view-container').find('.single_add_to_cart_button');
                var variationsForm = $('.product-quick-view-container').find('.variations_form');
    
                if ( variationsForm.length ) {
                    // $('.product-quick-view-container').find('.is-form').detach();

                    var prodUrl = $('.product-quick-view-container').find('a.plain').attr('href');
                    variationsForm.attr('action', prodUrl);
                }

                $('.product-quick-view-container').find('.qty').on('change', function(){
                    var newQty = $(this).val();
                    btAddCart.data('quantity', newQty);
                    btAddCart.attr('data-quantity', newQty);
                });

                btAddCart.each(function(){
                    $(this).addClass('add_to_cart_button ajax_add_to_cart');
    
                    if ( !$(this).attr('data-product_id') ) {
                        var productId = $(this).val();
                        $(this).data('product_id', productId);
                        $(this).attr('data-product_id', productId);
                    }
                });

                wbuProHandleVariableAjaxAddToCart('wbuflatsome');
            }
        });
    };

    wbuProHandleWcAjaxSearchPremium = function() {
        $(document.body).on('click', '.dgwt-wcas-pd-addtc .add_to_cart_button', function(){
            var qtyInput = $(this).parent().find('.qty');

            if ( qtyInput.length ) {
                if ( qtyInput.hasClass('ignore_add2cart_change') ) {
                    qtyInput.removeClass('ignore_add2cart_change');
                }
                else if ( parseInt(qtyInput.val()) > 1 ) {
                    // qtyInput.val( parseInt(qtyInput.val()) + 1 );
                }
            }

            return true;
        });

        $(document.body).on('change', '.dgwt-wcas-pd-addtc .qty', function(){
            $(this).addClass('ignore_add2cart_change');
        });


        $(document.body).bind('DOMSubtreeModified', function(e) {
            if ( $('.dgwt-wcas-pd-addtc').length ) {
                setTimeout(function(){
                    wbuProHideQtyWhenNotAddedToCart();
                }, 100);

                if ( !$('.dgwt-wcas-pd-addtc').hasClass('wbu-ajxsearch') ) {
                    $('.dgwt-wcas-pd-addtc').addClass('wbu-ajxsearch');
                    wbuProReplicateMiniCartQtyWithShopV2();
                }

                $('.dgwt-wcas-pd-addtc .added_to_cart').detach();
            }
        });
    };
    
    // init calls
    wbuAddItemRemoveEffect();
    wbuListenMiniCartQtyChange();
    wbuProCheckoutDeleteAjax();
    wbuProRefreshMiniCartOnCheckout();
    wbuProProductAddCartAjaxVariable();
    wbuProSyncQtyWithCartOnShop();
    wbuProSyncQtyWithCartOnProduct();
	
	if ( wbuProSyncQtyWithCartProductEnabled() ) {
		wbuProListenQtyChangeForSync('.cart');
	}
    
    wbuProListenQtyChangeForPriceUpdate();
	
	setTimeout(function(){
		wbuProHideQtyWhenNotAddedToCart();
	}, 500);

    $(document).on('ixProductFilterRequestProcessed', function(){
        wbuProHideQtyWhenNotAddedToCart();
    });

    wbuProShopInlineVariations();
    wbuProHandleCommonCartEvents();
    wbuProHandleWcAjaxSearchPremium();

    if ( $('.theme-flatsome').length ) {
        wbuProFlatsomeQuickViewCompat();
    }

    $('.widget_shopping_cart_content').ready(function() {
        setTimeout(function(){
            // custom function was used to specific condition for sync when uses Back button of the browser
            if ( $('#woocommerce_widget_cart-2').length ) {
                wbuProReplicateMiniCartQtyWithShopV2();
            }
        }, 500);
    });
});

