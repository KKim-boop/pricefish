jQuery(document).ready(function($){

    wbuGlobalQtyElement = null;
    wbuAjaxQueue = [];
    wbuUpdateTimeout = null;

    wbuEnqueueAjax = function(ajaxOpts) {
        wbuAjaxQueue.push(ajaxOpts);
        wbuRunQueuedAjax();
    };

    // execute AJAX queued list
    wbuRunQueuedAjax = function() {
        if ( wbuAjaxQueue.length ) {
            var ajaxOpts = wbuAjaxQueue[0];

            ajaxOpts.complete = function(){
                wbuAjaxQueue.shift(); // remove this ajax from queue

                wbuRunQueuedAjax();
            };

            var xhr = $.ajax(ajaxOpts);
        }
    };

    wbuQtyChangeCart = function(qtyElement) {

        // ask user if they really want to remove this product
        if ( !wbuZeroQuantityCheck(qtyElement) ) {
            return false;
        }

        // when qty is set to zero, then trigger default woocommerce remove product action
        if ( qtyElement.val() == 0 ) {
            var cartItem = qtyElement.closest('.cart_item');
            var removeLink = cartItem.find('.product-remove a');

            if ( !removeLink.length ) {
                removeLink = cartItem.find('.remove u');
            }
            
            removeLink.trigger('click');

            return false;
        }

        // run refresh callback timeout 
        wbuClearTimedoutQtyChange();
        wbuUpdateTimeout = setTimeout(function(){
            if ( wbuSettings.cart_ajax_method == 'simulate_update_button' ) {
                // new method
                wbuSimulateUpdateCartButtonClick(qtyElement);
            }
            else {
                // old ajax method
                wbuMakeAjaxCartUpdate(qtyElement);
            }
    
            wbuAfterCallUpdateCart(qtyElement);
        }, wbuSettings.ajax_timeout);

        return true;
    };

    wbuClearTimedoutQtyChange = function() {
        // clear previous timeout, if exists
        if ( wbuUpdateTimeout !== null ) {
            clearTimeout(wbuUpdateTimeout);
        }
    };

    wbuMakeAjaxCartUpdate = function(qtyElement) {

        if ( typeof qtyElement.attr('name') == 'undefined' ) {
            return;
        }

        matches = qtyElement.attr('name').match(/cart\[(\w+)\]/);
        
        if ( matches === null ) {
            return;
        }
        
        form = qtyElement.closest('form');

        $("<input type='hidden' name='update_cart' id='update_cart' value='1'>").appendTo(form);
        $("<input type='hidden' name='is_wbu_ajax' id='is_wbu_ajax' value='1'>").appendTo(form);

        wbuGlobalQtyElement = qtyElement;

        cart_item_key = matches[1];
        form.append( $("<input type='hidden' name='cart_item_key' id='cart_item_key'>").val(cart_item_key) );

        // get the form data before disable button
        formData = form.serialize();

        $("button[name='update_cart']").addClass('disabled');

        if ( wbuSettings.cart_updating_display == 'yes' ) {
            if ( wbuSettings.cart_updating_location == 'checkout_btn' ) {
                $("a.checkout-button.wc-forward").addClass('disabled').html( wbuSettings.cart_updating_text );
            }
            else {
                $("button[name='update_cart']").html( wbuSettings.cart_updating_text );
            }
        }

        $.ajax({
            url: form.attr('action'),
            type: 'POST',
            data: formData,
            dataType: 'json',
            beforeSend: function() {
                wbuBlock( $( '.woocommerce-cart-form' ) );
            },
            success: function(resp) {
                wbuAjaxCartUpdateCallback(resp);
                wbuUnblock( $( '.woocommerce-cart-form' ) );
            }
        });
    };

    wbuAjaxCartUpdateCallback = function(resp) {
        $('.cart-collaterals').html(resp.html);
        
        wbuGlobalQtyElement.closest('.cart_item').find('.product-subtotal').html(resp.price);
        
        $('#update_cart').remove();
        $('#is_wbu_ajax').remove();
        $('#cart_item_key').remove();

        $("button[name='update_cart']").removeClass('disabled');
        
        if ( wbuSettings.cart_updating_display == 'yes' ) {
            if ( wbuSettings.cart_updating_location == 'checkout_btn' ) {
                $("a.checkout-button.wc-forward").removeClass('disabled').html(resp.checkout_label);
            }
            else {
                $("button[name='update_cart']").html(resp.update_label);
            }
        }

        // fix to update "Your order" totals when cart is inside Checkout page
        if ( $( '.woocommerce-checkout' ).length ) {
            $( document.body ).trigger( 'update_checkout' );
        }

        $( document.body ).trigger( 'updated_cart_totals' );
        $( document.body ).trigger( 'wc_fragment_refresh' );
    };

    wbuSimulateUpdateCartButtonClick = function(qtyElement) {
        // deal with update cart button
        if ( wbuSettings.cart_updating_display == 'yes' ) {
            // change the Checkout or Update cart button
            if ( wbuSettings.cart_updating_location == 'checkout_btn' ) {
                $("a.checkout-button.wc-forward").html( wbuSettings.cart_updating_text );
            }
            else {
                $("button[name='update_cart']").html( wbuSettings.cart_updating_text );
            }
        }

        // this small delay has been added to fix bug on firefox
        setTimeout(function(){
            $("button[name='update_cart']").trigger('click');
        }, 20);
    };
    
    wbuWhenCartUpdated = function(qtyElement) {
        // fix some event lost because the ajax reload
        wbuLockQtyInput();
        wbuAddItemRemoveEffect();
    };
    
    wbuAfterCallUpdateCart = function(qtyElement) {
        // this is an overridable function
    };
    
    wbuAddItemRemoveEffect = function() {
        // this is an overridable function
    };

    wbuZeroQuantityCheck = function(el_qty) {
        if ( wbuInfo.isCart && ( el_qty.val() == 0 ) && ( wbuSettings.confirmation_zero_qty === 'yes' ) ) {

            if ( !confirm( wbuSettings.zero_qty_confirmation_text ) ) {
                el_qty.val(1);
                return false;
            }
        }

        return true;
    };

    wbuListenChange = function() {

        if ( wbuSettings.enable_auto_update_cart == 'yes' ) {

            $(document.body).on('change', '.woocommerce-cart-form .qty', function(){
                return wbuQtyChangeCart( $(this) );
            });
        }
    };

    wbuCartDeleteEvent = function() {
    };

    wbuQtyButtons = function() {
        
        $(document.body).on('click', '.wbu-btn-inc', function(){
            return wbuQtyButtonClick( $(this), 1 );
        });

        $(document.body).on('click', '.wbu-btn-sub', function(){
            return wbuQtyButtonClick( $(this), -1 );
        });

        wbuLockQtyInput();
    };

    wbuQtyButtonClick = function(element, factor) {
        inputQty = element.parent().find('.qty');
        newQty = parseInt(inputQty.val()) + factor;

        // check disabled
        if ( element.hasClass('disabled') ) {
            return false;
        }

        // respect the minimum value
        minAttr = inputQty.attr('min');
        if ( typeof minAttr !== typeof undefined && minAttr !== false && newQty < parseInt(minAttr) ) {
            return false;
        }

        // respect the max stock limit
        maxAttr = inputQty.attr('max');
        if ( typeof maxAttr !== typeof undefined && maxAttr !== false && newQty > parseInt(maxAttr) ) {
            return false;
        }

        // when using Select, check if new quantity exists in options list
        if ( inputQty.is('select') && ( inputQty.find('option[value="'+newQty+'"]').length === 0 ) ) {
            return false;
        }

        inputQty.val( newQty );
        inputQty.change();

        return false;
    };

    
    wbuLockQtyInput = function() {
        // lock quantity input
        if ( wbuSettings.qty_buttons_lock_input == 'yes' ) {
            $('.qty').attr('readonly', 'readonly')
                     .css('background-color', 'lightgray');
        }
    };
    
    wbuQtyOnShop = function() {
        if ( wbuSettings.enable_quantity_on_shop != 'yes' ) {
            return;
        }
        
        $("form.cart").off('change.wbu_form_cart_qty').on("change.wbu_form_cart_qty", ".qty", function() {
            // loop on each add to cart button to set the correct quantity defined on input
            jQuery('.ajax_add_to_cart').each(function(){
                var qty = jQuery(this).parent().find('.qty').val();

                jQuery(this).data('quantity', qty);
                jQuery(this).attr('data-quantity', qty);
            });
        });

        // prevent bug caused in case the user was navegated using browser back button (so the quantity keep the previous state)
        // issue link: https://wordpress.org/support/topic/quantity-not-working-2/
        $('.add_to_cart_button').off('click.wbu_add_cart_btn').on('click.wbu_add_cart_btn', function(){
            // this was commented because causing excessive ajax requests when adding to cart (on shop page)
            // $('.qty').trigger('change');
            return true;
        });

        // make compatibility with infinite scrollings, forcing re-apply events
        $( document ).ajaxComplete(function() {
            // this was commented because was causing crash on browser, making infinite ajax requests (on shop page)
            // wbuQtyOnShop();
        });
    };
    
    wbuQtyOnCheckout = function() {

        if ( !wbuInfo.isCheckout || ( wbuSettings.checkout_allow_change_qty != 'yes' ) ) {
            return;
        }

        $("form.checkout").on("change", ".qty", function(){


            // run refresh callback timeout 
            wbuClearTimedoutQtyChange();
            wbuUpdateTimeout = setTimeout(function(){
                var data = {
                    action: 'wbu_update_checkout',
                    security: wc_checkout_params.update_order_review_nonce,
                    post_data: $( 'form.checkout' ).serialize()
                };
                
                jQuery.post(wbuInfo.ajaxUrl, data, function( response ) {
                    $( 'body' ).trigger( 'update_checkout' );
                });
            }, wbuSettings.ajax_timeout);
        });
        
        // add Quantity title on table
        $('body').on('updated_checkout', function(){
            quantityTitle = '<div class="checkout-qty-title">' + wbuInfo.quantityLabel + '</div>';
            $('.shop_table th.product-name').append(quantityTitle);
        });
        
    };
    
    wbuProductAddToCartAjax = function() {
        //if ( !wbuInfo.isSingleProduct ) {
        //    return;
        //}

        $('.qty').on('change', function(){
            var newQty = $(this).val();
            $('.add_to_cart_button').data('quantity', newQty).attr('data-quantity', newQty);
            $('[name="add-to-cart"]').data('quantity', newQty).attr('data-quantity', newQty);
        });
    };

    wbuCheckHideUpdateCartBtn = function() {
        // check hide update cart button
        if ( wbuSettings.cart_hide_update == 'yes' ) {
            $('button[name="update_cart"]').hide();
            $('.fusion-update-cart').hide();
        }
    };

    wbuCheckoutInputValidation = function() {
        var checkoutForm = $( 'form.checkout' );

        if ( checkoutForm.length && checkoutForm.find('.qty').length ) {
            checkoutForm.removeAttr( 'novalidate' );
        }
    };
    

    // functions ripped from woocommerce cart.js
    wbuBlock = function( $node ) {
        if ( ! wbuIsBlocked( $node ) ) {
            $node.addClass( 'processing' ).block( {
                message: null,
                overlayCSS: {
                    background: '#fff',
                    opacity: 0.6
                }
            } );
        }
    };

    wbuUnblock = function( $node ) {
        $node.removeClass( 'processing' ).unblock();
    };

    wbuIsBlocked = function( $node ) {
        return $node.is( '.processing' ) || $node.parents( '.processing' ).length;
    };
    // END OF functions ripped from woocommerce cart.js
    
    // onload function calls
    wbuListenChange();
    wbuQtyButtons();
    wbuCartDeleteEvent();
    wbuQtyOnShop();
    wbuQtyOnCheckout();
    wbuProductAddToCartAjax();

    // hide add to cart button
    if ( wbuInfo.isShop && wbuSettings.hide_addtocart_button === 'yes' ) {
        $('.add_to_cart_button').hide();
    }

    // hide update cart button (Avada compatibility)
    if ( wbuInfo.isCart && wbuSettings.cart_hide_update === 'yes' ) {
        $('.fusion-update-cart').hide();
    }
    
    // hide product quantity on cart if needed
    if ( wbuSettings.cart_hide_quantity == 'yes' ) {
        $('.product-quantity').parent().find('th').css('width', '100%');
        $('.product-quantity').hide();
    }

    if ( wbuInfo.isCart ) {
        // fix enter key problem
        if ( wbuSettings.cart_fix_enter_key == 'yes' ) {
            $('.woocommerce-cart-form .qty.text').on('keyup keypress', function(e) {
                var keyCode = e.keyCode || e.which;

                if (keyCode === 13) { 
                    $('.woocommerce-cart-form .qty.text').trigger( "blur" );
                    e.preventDefault();
                    return false;
                }
            });
        }

        wbuCheckHideUpdateCartBtn();
    }

    if ( wbuInfo.isCheckout ) {
        wbuCheckoutInputValidation();    
    }

    $(document).on('updated_checkout', function(){
        setTimeout(function(){
            wbuCheckoutInputValidation();
        }, 500);
    });

    // listen when ajax cart has updated
    $(document).on('updated_wc_div', function(){
        wbuCheckHideUpdateCartBtn();
        wbuWhenCartUpdated();
    });
    
    // listen when product has added to cart
    $(document).on('added_to_cart', function(){

        if ( wbuSettings.hide_viewcart_link === 'yes' ) {
            $('.added_to_cart').hide();
        }
    });

    // listen minicart remove button click
    $(document).on('click', '.mini-cart-box .remove', function(){
        var productId = $(this).data('product_id');

        $('.shop_table').find('.remove').each(function(){
            if ( productId == $(this).data('product_id') ) {
                $(this).trigger('click');
            }
        });

        return false;
    });
});
