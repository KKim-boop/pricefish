<?php
/*
Plugin Name: WooCommerce Better Usability PRO
Plugin URI: https://wordpress.org/plugins/woo-better-usability
Description: Improves overall Woocommerce buyer experience (PREMIUM VERSION)
Version: 1.0.33
Author: Moises Heberle
Author URI: https://pluggablesoft.com/contact/
Text Domain: woo-better-usability
Domain Path: /i18n/languages/
*/

defined('WBU_BASE_FILE') || define('WBU_BASE_FILE', __FILE__);
defined('WBU_PLUGIN') || define('WBU_PLUGIN', plugin_basename( __FILE__));

if ( !class_exists('WBU') ) {
    include_once( 'includes/class-wbu.php' );
    WBU::boot();
}

// init PRO version callbacks
register_activation_hook( __FILE__, 'wbuProCheckLiteVersionInstalled' );

add_action('init', 'wbupro_init');
add_action('admin_notices', 'wbupro_notices');

add_filter('mh_wbu_is_premium', 'wbupro_is_premium');
add_filter('mh_wbu_settings', 'wbupro_settings');

add_filter('wbu_get_template', 'wbupro_get_template');
add_filter('wbu_product_quantity_input', 'wbupro_product_quantity_input');
add_filter('wbu_checkout_quantity_input', 'wbupro_checkout_quantity_input');
add_filter('wbu_shop_quantity_value', 'wbupro_shop_quantity_value', 10, 2);

add_filter('wc_get_template', 'wbupro_get_wc_template', 10, 2);
add_action('wp_enqueue_scripts', 'wbupro_enqueue_scripts');
add_filter('woocommerce_widget_cart_item_quantity', 'wbupro_minicart_quantity', 10, 3 );
add_filter('woocommerce_quantity_input_args', 'wbupro_quantity_input_args', 21, 2 );
add_filter('woocommerce_loop_add_to_cart_link', 'wbupro_show_inline_product_variations');
add_action('woocommerce_add_to_cart', 'wbupro_adding_to_cart', 10, 4);
add_filter('woocommerce_add_to_cart_quantity', 'wbupro_add_to_cart_quantity', 10, 3);
add_filter('woocommerce_add_to_cart_validation', 'wbupro_add_to_cart_validation', 15, 6 );
add_filter('dgwt/wcas/suggestion_details/show_quantity', 'wbupro_wcas_show_quantity');

add_action("wp_ajax_nopriv_wbu_update_product", "wbu_update_product");
add_action("wp_ajax_wbu_update_product", "wbu_update_product");

add_action("wp_ajax_nopriv_wbu_alter_quantity", "wbu_alter_quantity");
add_action("wp_ajax_wbu_alter_quantity", "wbu_alter_quantity");

add_action('wp_ajax_nopriv_wbu_delitem_checkout', 'wbu_delitem_checkout');
add_action('wp_ajax_wbu_delitem_checkout', 'wbu_delitem_checkout');

// override for the Woo Discount Rules plugin 
add_action('wp_ajax_loadWooDiscountStrikeoutPriceOfProduct', 'wbupro_getWooDiscountStrikeoutPriceOfProduct', -9999);
add_action('wp_ajax_nopriv_loadWooDiscountStrikeoutPriceOfProduct', 'wbupro_getWooDiscountStrikeoutPriceOfProduct', -9999);

function wbupro_settings($arr) {
    // General tab
    $arr['checkout_make_ajax_request_delete'] = array(
        'label' => __('Make AJAX request to delete product without page reload', 'woo-better-usability'),
        'tab' => __('General', 'woo-better-usability'),
        'type' => 'checkbox',
        'default' => 'yes',
        'depends_on' => 'checkout_allow_delete',
    );
    $arr['qty_as_select_checkout'] = array(
        'label' => __('Show item quantity as select instead of numeric field', 'woo-better-usability'),
        'tab' => __('General', 'woo-better-usability'),
        'type' => 'checkbox',
        'default' => 'no',
        'depends_on' => 'checkout_allow_change_qty',
    );
    $arr['show_checkout_quantity_buttons'] = array(
        'label' => __('Show -/+ buttons around item quantity', 'woo-better-usability'),
        'tab' => __('General', 'woo-better-usability'),
        'type' => 'checkbox',
        'default' => 'no',
        'depends_on' => 'checkout_allow_change_qty',
    );
    $arr['minicart_allow_change_qty'] = array(
        'label' => __('Allow to change product quantity on Mini Cart Widget', 'woo-better-usability'),
        'tab' => __('General', 'woo-better-usability'),
        'type' => 'checkbox',
        'default' => 'yes',
    );
    $arr['minicart_qty_buttons'] = array(
        'label' => __('Show -/+ buttons around item quantity', 'woo-better-usability'),
        'tab' => __('General', 'woo-better-usability'),
        'type' => 'checkbox',
        'default' => 'yes',
        'depends_on' => 'minicart_allow_change_qty',
    );
    $arr['minicart_sync_block'] = array(
        'label' => __('Block screen while updating quantity', 'woo-better-usability'),
        'tab' => __('General', 'woo-better-usability'),
        'type' => 'checkbox',
        'default' => 'yes',
        'depends_on' => 'minicart_allow_change_qty',
    );

    // Shop tab
    $arr['shop_sync_qty_with_cart'] = array(
        'label' => __('Synchronize products automatically with cart when change quantity', 'woo-better-usability'),
        'tab' => __('Shop', 'woo-better-usability'),
        'type' => 'checkbox',
        'default' => 'no',
        'depends_on' => 'enable_quantity_on_shop',
    );
    $arr['qty_sync_block_screen'] = array(
        'label' => __('Block screen while updating quantity', 'woo-better-usability'),
        'tab' => __('Shop', 'woo-better-usability'),
        'type' => 'checkbox',
        'default' => 'yes',
        'depends_on' => 'enable_quantity_on_shop',
    );
    $arr['hide_qty_when_zero'] = array(
        'label' => __('Smart display quantity buttons only when added to cart', 'woo-better-usability'),
        'tab' => __('Shop', 'woo-better-usability'),
        'type' => 'checkbox',
        'default' => 'no',
        'depends_on' => 'enable_quantity_on_shop',
    );
    $arr['make_cart_work_on_shop'] = array(
        'label' => __('Make cart works on shop page when using [woocommerce_cart] shortcode', 'woo-better-usability'),
        'tab' => __('Shop', 'woo-better-usability'),
        'type' => 'checkbox',
        'default' => 'no',
    );
    $arr['shop_inline_variations'] = array(
        'label' => __('Allow to add variable products directly on Shop', 'woo-better-usability'),
        'tab' => __('Shop', 'woo-better-usability'),
        'type' => 'checkbox',
        'default' => 'no',
    );
    $arr['inline_variation_mode'] = array(
        'label' => __('Display variation options section as', 'woo-better-usability'),
        'tab' => __('Shop', 'woo-better-usability'),
        'type' => 'select',
        'options' => array(
            'always_visible' => __('Always visible, below product', 'woo-better-usability'),
            'manual_expand' => __('Expand when `Select options` button was clicked', 'woo-better-usability'),
        ),
        'default' => 'always_visible',
        'depends_on' => 'shop_inline_variations',
    );
    
    // Product tab
    $arr['product_addcart_ajax_variable'] = array(
        'label' => __('Enable support for variable products', 'woo-better-usability'),
        'tab' => __('Product', 'woo-better-usability'),
        'type' => 'checkbox',
        'default' => 'yes',
        'depends_on' => 'product_ajax_add_to_cart',
    );
    $arr['product_sync_qty_with_cart'] = array(
        'label' => __('Synchronize quantity automatically with minicart', 'woo-better-usability'),
        'tab' => __('Product', 'woo-better-usability'),
        'type' => 'checkbox',
        'default' => 'no',
        'depends_on' => 'product_ajax_add_to_cart',
    );
    $arr['enable_auto_update_product'] = array(
        'label' => __('Update price automatically when change quantity', 'woo-better-usability'),
        'tab' => __('Product', 'woo-better-usability'),
        'type' => 'checkbox',
        'default' => 'yes',
    );
    $arr['product_update_show_calc'] = array(
        'label' => __('Display quantity x price calculation', 'woo-better-usability'),
        'tab' => __('Product', 'woo-better-usability'),
        'type' => 'checkbox',
        'default' => 'yes',
        'depends_on' => 'enable_auto_update_product',
    );
    $arr['price_consider_tax'] = array(
        'label' => __('Consider taxes to calculate the product price', 'woo-better-usability'),
        'tab' => __('Product', 'woo-better-usability'),
        'type' => 'checkbox',
        'default' => 'yes',
        'depends_on' => 'enable_auto_update_product',
    );
    $arr['product_updating_text'] = array(
        'label' => __('Text to display when remote updating product price', 'woo-better-usability'),
        'tab' => __('Product', 'woo-better-usability'),
        'type' => 'text',
        'default' => __('Updating...'),
        'size' => 20,
        'depends_on' => 'enable_auto_update_product',
    );
    $arr['show_product_quantity_buttons'] = array(
        'label' => __('Show -/+ buttons around item quantity', 'woo-better-usability'),
        'tab' => __('Product', 'woo-better-usability'),
        'type' => 'checkbox',
        'default' => 'yes',
    );
    $arr['hide_product_addtocart_button'] = array(
        'label' => __('Hide `Add to cart` button', 'woo-better-usability'),
        'tab' => __('Product', 'woo-better-usability'),
        'type' => 'checkbox',
        'default' => 'no',
    );
    $arr['product_ajax_lock'] = array(
        'label' => __('Lock screen while doing AJAX updates', 'woo-better-usability'),
        'tab' => __('Product', 'woo-better-usability'),
        'type' => 'checkbox',
        'default' => 'yes',
    );

    // Cart tab
    $arr['cart_update_message_fadeout'] = array(
        'label' => __('Add fadeout effect to hide the message after update cart prices', 'woo-better-usability'),
        'tab' => __('Cart', 'woo-better-usability'),
        'type' => 'checkbox',
        'default' => 'no',
        'depends_on' => 'enable_auto_update_cart',
    );
    $arr['item_remove_fadeout'] = array(
        'label' => __('Add fadeout effect when remove item from the cart', 'woo-better-usability'),
        'tab' => __('Cart', 'woo-better-usability'),
        'type' => 'checkbox',
        'default' => 'no',
    );

    return $arr;
}

function wbupro_init() {    
    // this hack is needed because when adding variable product using AJAX html5 convert
    // data-add-to-cart to camelCase and woocommerce not recognize the product add to cart handler (class-wc-form-handler.php)
    if ( !empty($_REQUEST['addToCart']) && empty($_REQUEST['add-to-cart']) ) {
        $_REQUEST['add-to-cart'] = $_REQUEST['addToCart'];
        
        // unset wc-ajax handler to not be triggered by class-wc-ajax (method add_to_cart)
        unset($_REQUEST['wc-ajax']);
        unset($_GET['wc-ajax']);
    }

    // hack needed to prevent out of stock validation error when syncing product quantity
    if ( !empty($_REQUEST['is_wbu_quantity_sync']) &&
         !empty($_REQUEST['wc-ajax']) &&
         ( $_REQUEST['wc-ajax'] == 'add_to_cart' ) &&
         !empty($_REQUEST['product_id']) ) {
            $productId = absint($_REQUEST['product_id']);
            $product = wc_get_product($productId);

            if ( $product->managing_stock() && !$product->backorders_allowed() ) {
                $_REQUEST['wbupro_prev_stock_quantity'] = $product->get_stock_quantity();
                
                $product->set_stock_quantity(99999);
                $product->save();
            }

            // make it works with "YITH WooCommerce Minimum Maximum Quantity Premium" plugin validations
            if ( !empty($_POST['quantity']) && class_exists( 'YITH_WC_Min_Max_Qty' ) ) {
                $item = wbupro_get_cart_item($productId);

                // reset the item from cart so the plugin will validate correct Min/Max quantities based in the quantity being added
                if ( !empty($item) ) {
                    WC()->cart->set_quantity($item['key'], 0);
                }
            }
    }

    // add compatibility with Savoy theme
    $theme = get_option('stylesheet');

    if ( $theme == 'savoy' ) {
        add_action('woocommerce_shop_loop_item_title', 'wbupro_savoy_compat');
        
    }
}

function wbupro_add_to_cart_validation($passed, $product_id, $quantity, $variation_id = 0, $variation = null, $cart_item_data = array()) {
    // make it works with "YITH WooCommerce Minimum Maximum Quantity Premium" plugin validations
    // this routine back to previously defined quantity value, instead of zero
    if ( !$passed && class_exists( 'YITH_WC_Min_Max_Qty' ) ) {
        $oldQtyForce = ( $quantity - 1 );
        $_REQUEST['wbu_force_change_quantity'] = $oldQtyForce;
        WC()->cart->add_to_cart($product_id, $oldQtyForce);
    }

    return $passed;
}

function wbupro_get_cart_item($productId, $variationId = null) {
    foreach( WC()->cart->get_cart() as $cart_item ){
        $cartProductId = $cart_item['product_id'];
        $cartVariationId = !empty($cart_item['variation_id']) ? $cart_item['variation_id'] : null;

        if ( $cartProductId == $productId && ( !$variationId || ( $variationId == $cartVariationId )) ) {
            return $cart_item;
        }
    }

    return array();
}


function wbupro_is_premium() {
    return true;
}

function wbupro_get_template($located) {
    if ( ( basename($located) == 'checkout-product-qty.php' ) && ( wbu()->option('qty_as_select_checkout') == 'yes' ) ) {
        $located = wbu()->template_path('checkout-qty-select.php');
    }

    return $located;
}


function wbupro_get_wc_template($located, $template_name) {
    global $product;
    
    // force to use ajax template when show_product_quantity_buttons enabled to hook actions
    if ( is_product() && ( wbu()->option('show_product_quantity_buttons') == 'yes' ) ) {
        switch ( $template_name ) {
            case 'single-product/add-to-cart/simple.php':
            if ( wbu()->option('product_ajax_add_to_cart') == 'yes' ) {
                $located = wbu()->template_path('product-add-cart-ajax.php');
            }
            else {
                $located = wbu()->template_path('product-add-cart-noajax.php');
            }
            break;

            case 'single-product/add-to-cart/variation-add-to-cart-button.php':
                $located = wbu()->template_path('product-add-cart-ajax-variable.php');
            break;
        }
    }

    // add special class attributes on add to cart button for variable products ajax: add_to_cart_button ajax_add_to_cart
    if ( $template_name == 'single-product/add-to-cart/variation-add-to-cart-button.php' ) {
        $isFlatsomeQuickView = !empty($_REQUEST['action']) && ( $_REQUEST['action'] == 'flatsome_quickview' );
        $inlineVariationsEnabled = wbu()->is_shop_loop() && ( wbu()->option('shop_inline_variations') == 'yes' );
        
        if ( $isFlatsomeQuickView || $inlineVariationsEnabled ) {
            $located = wbu()->template_path('variation-inline-addtocart.php');
        }
        else if ( wbu()->option('product_ajax_add_to_cart') === 'yes' && wbu()->option('product_addcart_ajax_variable') === 'yes' ) {
            $located = wbu()->template_path('variation-add-to-cart-button.php');
        }
    }
    
    return $located;
}

function wbupro_minicart_sync_enabled() {
    return ( wbu()->option('product_ajax_add_to_cart') === 'yes' ) && ( wbu()->option('product_sync_qty_with_cart') === 'yes' );
}

function wbupro_product_min_value($product) {
    // when minicart synchronization activated, define minimum value as zero
    if ( wbupro_minicart_sync_enabled() ) {
        return 0;
    }
    
    // woocommerce default code
    return apply_filters( 'woocommerce_quantity_input_min', $product->get_min_purchase_quantity(), $product );
}

function wbupro_product_input_value($product) {
    // when minicart synchronization activated, define current quantity = same as cart
    if ( wbupro_minicart_sync_enabled() ) {
        foreach( WC()->cart->get_cart() as $cart_item ){
            $product_id = $cart_item['product_id'];

            if ( $product_id == $product->get_id() ) {
                return $cart_item['quantity'];
            }
        }

        return 0;
    }

    // woocommerce default code
    return isset( $_POST['quantity'] ) ? wc_stock_amount( $_POST['quantity'] ) : $product->get_min_purchase_quantity();
}

function wbupro_quantity_input_args($args, $product) {
    // minicart synchronization feature
    if ( wbupro_minicart_sync_enabled() ) {
        $foundInCart = false;

        foreach( WC()->cart->get_cart() as $cart_item ){
            // ignore variable products sync
            if ( !empty($cart_item['variation_id']) ) {
                continue;
            }

            $product_id = $cart_item['product_id'];

            if ( $product_id == $product->get_id() ) {
                $args['input_value'] = $cart_item['quantity'];
                $foundInCart = true;
            }
        }
    }

    $args['input_id'] = 'qty_prod_' . $product->get_id();

    return $args;
}

function wbupro_product_quantity_input($inputDiv) {
    if ( ( wbu()->option('show_product_quantity_buttons') !== 'yes' ) || preg_match('/wbu-qty-button/', $inputDiv) ) {
        return $inputDiv;
    }

    return wbupro_handle_quantity_buttons($inputDiv);
}

function wbupro_checkout_quantity_input($inputDiv) {
    if ( ( wbu()->option('show_checkout_quantity_buttons') !== 'yes' ) || preg_match('/wbu-qty-button/', $inputDiv) ) {
        return $inputDiv;
    }

    return wbupro_handle_quantity_buttons($inputDiv);
}

function wbupro_handle_quantity_buttons($inputDiv) {
    $theme = get_option('stylesheet');

    if ( $theme == 'savoy' ) {
        return $inputDiv;
    }

    // pass this vars to template
    $input = str_replace(array('<div class="quantity">', '</div>'), array('', ''), $inputDiv);

    return wbu()->get_template('qty-buttons-shop.php', array(
        'inputDiv' => $inputDiv,
        'input' => $input,
    ));
}

function wbupro_notices() {
    if ( defined('WBU_LITE_INSTALLED') ) {
        ?>
        <div id="message" class="error notice is-dismissible">
            <p>
                <?= __('Warning! The plugin <b>WooCommerce Better Usability PRO</b> requires that the lite version <b>WooCommerce Better Usability</b> be deactivated to works correctly.') ?>
            </p>
            <button type="button" class="notice-dismiss">
                <span class="screen-reader-text"></span>
            </button>
        </div>
        <?php
    }
}

function wbupro_menu_title() {
    return __( 'Better Usability PRO' );
}

function wbupro_lite_box() {
    return false;
}

function wbupro_tab_filter($tab) {
    if ( preg_match('/tab-buy/', $tab)) {
        return null;
    }
    
    return $tab;
}

function wbupro_after_cart_tab() {
    echo wbu()->settings->setting_tab('tab-mini-cart.php', __('MiniCart Widget'));
}

// when render cart table page
function wbupro_enqueue_scripts() {
    // enqueue premium assets
    wp_enqueue_style('wbupro', plugins_url('assets/wbupro.css', WBU_PLUGIN), array('wbulite'));
    wp_enqueue_script('wbupro', plugins_url('assets/wbupro.js', WBU_PLUGIN), array('wbulite'));

    // force enqueue of cart.min.js script to works on shop page
    if ( wbu()->option('make_cart_work_on_shop') === 'yes' ) {
        wbupro_force_enqueue_cart_script();
    }
}

function wbupro_force_enqueue_cart_script() {
    $path = 'assets/js/frontend/cart.min.js';
    $url = str_replace( array( 'http:', 'https:' ), '', plugins_url( $path, WC_PLUGIN_FILE ) );

    wp_register_script('wc-cart', $url, array( 'jquery' ), WC_VERSION, TRUE);
    wp_enqueue_script('wc-cart');
}

function wbupro_minicart_quantity($html, $cart_item, $cart_item_key) {
    // here we check if XT WooCommerce Floating Cart was installed
    // if yes, then deactivate minicart quantity change feature because it not current working properly
    $wooFCInstalled = function_exists('xt_woo_floating_cart');
    $isSavoyTheme = ( get_option('stylesheet') == 'savoy' );

    if ( ( wbu()->option('minicart_allow_change_qty') === 'yes' ) && !$wooFCInstalled && !$isSavoyTheme ) {
        $replaced = preg_replace('/(.*) &times; /i', ' &times; ', $html);

        $product = wc_get_product( !empty($cart_item["variation_id"]) ? $cart_item["variation_id"] : $cart_item["product_id"] );

        $qtyDiv = wbu()->get_template('qty-input-minicart.php', array(
            'cart_item_key' => $cart_item_key,
            'input_value' => $cart_item['quantity'],
            'input_name' => "cart[{$cart_item_key}][qty]",
            'product' => $product,
            'show_buttons' => ( wbu()->option('minicart_qty_buttons') === 'yes' )
        ));

        // remove outer div to avoid layout break
        $qtyDiv = str_replace(array('<div class="quantity">', '</div>'), '', $qtyDiv);

        // in some cases the div doesnt have the end of element causing frontend bugs
        if ( !preg_match('/<\/div>/', $qtyDiv) ) {
            $qtyDiv .= '</div>';
        }

        $newHtml = '<span class="quantity">' . $qtyDiv . $replaced;

        return $newHtml;
    }

    return $html;
}

function wbu_update_product() {
    // set as global to use on wbu_format_sale_price()
    global $product;
    
    echo wbu_get_price_html_product($product);
    exit;
}

function wbu_get_price_html_product($product) {
    // apply the filter
    add_filter('woocommerce_format_sale_price', 'wbu_format_sale_price');
    
    $quantity = max(1, filter_var(filter_input(INPUT_POST, 'quantity'), FILTER_VALIDATE_INT));
    $productId = filter_var(filter_input(INPUT_POST, 'product_id'), FILTER_VALIDATE_INT);
    $variationId = filter_var(filter_input(INPUT_POST, 'variation_id'), FILTER_VALIDATE_INT);
    $sumProductAddons = filter_var(filter_input(INPUT_POST, 'sum_addons'), FILTER_VALIDATE_INT);
     
    $product = wc_get_product( ( $variationId > 0 ) ? $variationId : $productId );
    $price = wbu_get_discounted_price($product, $quantity, $variationId);

    // check if has extra WooCommerce Product Addons prices
    if ( $sumProductAddons > 0 ) {
        $price += $sumProductAddons;
    }

    $html = '';
    $salePrice = $product->get_sale_price();
    $originalPrice = wbu_get_inc_or_excl_tax($product, $product->regular_price);

    // show discount price when available (this makes compatible with discounts plugins):
    if ( ( $originalPrice != $price ) && empty($sumProductAddons) && !empty($salePrice) ) {
        $regPrice = $product->get_regular_price(); // added to work with WooCommerce Price Based on Country (Basic). Before uses: $originalPrice
        $html .= '<del>' . wc_price( $quantity * $regPrice ) . '</del> &nbsp;';
    }

    // make compatibility with "WooCommerce Dynamic Pricing & Discounts" plugin
    if ( class_exists('XA_NewCalculationHandler') ) {
        $obj = new XA_NewCalculationHandler();
        $totalPrice = ( $obj->getDiscountedPriceForProduct($price, $product) * $quantity );

        $html .= '<ins>' . ( is_numeric( $totalPrice ) ? wc_price( $totalPrice ) : $totalPrice ) . '</ins>';
    }
    else {
        $html .= wc_price( $quantity * $price ) . $product->get_price_suffix();
    }

    // display calculations
    if ( $quantity > 1 && ( wbu()->option('product_update_show_calc') == 'yes' ) ) {
        $displayPrice = wc_price($price);
        $html .= ' <span class="product-price-explain">( ' . $quantity . ' × ' . $displayPrice . ' )</span>';
    }

    // compatibility with Woo Price Per Unit plugin
    if ( class_exists('mcmp_PPU') ) {
        $mcmp_ppu_obj = mcmp_PPU::get_instance();
        $priceUnit = $mcmp_ppu_obj->custom_price($price, $product);

        preg_match('/<span class="mcmp_recalc_price_row">(.*)<\/span>/i', $priceUnit, $match);

        if ( !empty($match[0]) ) {
            $html .= '<br/>' . $match[0];
        }
    }

    $includeSpanPriceClass = true;

    if ( $includeSpanPriceClass ) {
        return sprintf('<span class="price">%s</span>', $html);
    }
    else {
        return $html;
    }
}

function wbu_get_discounted_price($product, $quantity, $variationId) {
    $productId = $product->get_id();
    $discountedPrice = $product->get_price();

	if ( class_exists('Woo_Bulk_Discount_Plugin_t4m') ) {
        $coeff = wbu_bulk_discounts_plugin($productId, $quantity);
        $discountedPrice = ( $discountedPrice * $coeff );
    }
    else if ( class_exists('RP_WCDPD_Product_Pricing') ) {
    	// make compatibility with WCDPD discounts plugin
        $ppObject = new RP_WCDPD_Product_Pricing();
        $testPrice = $ppObject->test_product_price($product, $quantity);

        if ( !empty($testPrice) ) {
            $discountedPrice = $testPrice;
        }
    }
    else if ( class_exists('WC_Dynamic_Pricing') ) {
        // call to WC_Dynamic_Pricing plugin simulate the discount based on quantity
        // unfortunately WC_Dynamic_Pricing doesn't have a simulator method so we need to add on the cart first
 
        // todo: check better way to do this
        $cartItemsBefore = wbu_clear_cart();

        $wcdp = WC_Dynamic_Pricing::instance();
        $cartItemKey = WC()->cart->add_to_cart($productId, $quantity, $variationId);

        WC()->cart->calculate_totals();
        $wcdp->on_calculate_totals( WC()->cart );

        do_action('woocommerce_cart_loaded_from_session', WC()->cart);

        if ( !empty($cartItemKey) ) {
            $cartItem = WC()->cart->get_cart_item($cartItemKey);
            $discountedPriceBase = null;

            if ( !empty($cartItem['discounts']['price_base']) ) {
                $discountedPriceBase = $cartItem['discounts']['price_base'];
            }

            if ( wbupro_minicart_sync_enabled() ) {
                WC()->cart->set_quantity($cartItemKey, $quantity);
            }
            else {
                WC()->cart->remove_cart_item($cartItemKey);
            }
            
            if ( !empty($discountedPriceBase) ) {
                $discountedPrice = $discountedPriceBase;
            }
        }
        
        wbu_restore_cart($cartItemsBefore);
    }
    else if ( class_exists('YITH_WC_Dynamic_Discounts') ) {
        // compatibility with "YITH WooCommerce Dynamic Pricing and Discounts Premium" plugin
        $frontend = YITH_WC_Dynamic_Pricing_Frontend::get_instance();

        $rules = $frontend->get_table_rules($product);
        $priority = -1;
        $difference = 0;
        
        foreach ( $rules as $rule ) {
            if ( $rule['priority'] < $priority ) {
                continue;
            }
            else {
                $priority = $rule['priority'];
            }

            foreach ( $rule['rules'] as $subRule ) {
                if ( $quantity >= $subRule['min_quantity'] && $quantity <= $subRule['max_quantity'] ) {
                    // switch ( $subRule['type_discount'] ) {
                    //     case 'percentage':
                    //         $difference += ( $discountedPrice * $subRule['discount_amount'] );
                    //         break;
                    // }

                    $discountedPrice = ywdpd_get_discounted_price_table( $discountedPrice, $subRule );
                }
            }
        }
    }
    else if ( class_exists('Wad') ) {
        // compatibility with WooCommerce Advanced Discounts / Woocommerce All Discounts plugin

        global $wad_last_products_fetch;
        $wad_last_products_fetch = array($product->get_id());

        $discount = new WAD_Discount(FALSE);
        $discountedPrice = $discount->get_sale_price( $discountedPrice , $product );
    }
    else if ( class_exists('FlycartWooDiscountRules') ) {
        // override the class
        class MyFlycartWooDiscountRulesPricingRules extends FlycartWooDiscountRulesPricingRules {
            function setCustomQty($qty) {
                $this->custom_qty = $qty;
            }

            function myGetOriginalStrikeoutPriceOfProduct($product){
                return parent::getOriginalStrikeoutPriceOfProduct($product);
            }
        }

        $pRule = new MyFlycartWooDiscountRulesPricingRules();
        $product = FlycartWoocommerceProduct::wc_get_product( !empty($variationId) ? $variationId : $productId );

        if (!empty($product)) {
            $variationPrice = !empty($variationId) ? wc_get_product($variationId)->get_price() : null;
            $pRule->setCustomQty($quantity);
            $discPrice = $pRule->getDiscountPriceForTheProduct($product, $variationPrice);
            
            if ( $discPrice > 0 ) {
                $discountedPrice = $discPrice;
            }
        }
    }

    if ( wbu()->option('price_consider_tax') == 'yes' ) {
        $discountedPrice = wbu_get_inc_or_excl_tax($product, $discountedPrice);
    }

    return $discountedPrice;
}

// get price including or excluding taxes
function wbu_get_inc_or_excl_tax($product, $price) {
    $tax_display_mode = get_option( 'woocommerce_tax_display_shop' );

    if ( $tax_display_mode == 'excl' ) {
        $discountedPrice = function_exists('wc_get_price_excluding_tax') ? wc_get_price_excluding_tax( $product, array('qty' => 1, 'price' => $price ) ) : $product->get_price_excluding_tax( 1, $price );
    }
    else {
        $discountedPrice = function_exists('wc_get_price_including_tax') ? wc_get_price_including_tax( $product, array('qty' => 1, 'price' => $price ) ) : $product->get_price_including_tax( 1, $price );
    }

    return $discountedPrice;
}

function wbu_bulk_discounts_plugin( $product_id, $quantity ) {
    $q = array( 0.0 );
    $d = array( 0.0 );

    $configurer = get_page_by_title( 'wc_bulk_discount_configurer', OBJECT, 'product' );
    if ( $configurer && $configurer->ID && $configurer->post_status == 'private' ) {
        $product_id = $configurer->ID;
    }

    if ( version_compare( WOOCOMMERCE_VERSION, "2.7.0" ) >= 0 ) {
        $product = wc_get_product($product_id);
    } else {
        $product = get_product($product_id);
    }

    if ($product instanceof WC_Product_Variation) {
        $product_id = $product->get_parent_id();
    }
    
    /* Find the appropriate discount coefficient by looping through up to the five discount settings */
    for ( $i = 1; $i <= 5; $i++ ) {
        array_push( $q, get_post_meta( $product_id, "_bulkdiscount_quantity_$i", true ) );
        if ( get_option( 'woocommerce_t4m_discount_type', '' ) == 'flat' ) {
            array_push( $d, get_post_meta( $product_id, "_bulkdiscount_discount_flat_$i", true ) ? get_post_meta( $product_id, "_bulkdiscount_discount_flat_$i", true ) : 0.0 );
        } else if ( get_option( 'woocommerce_t4m_discount_type', '' ) == 'fixed' ) {
            array_push( $d, get_post_meta( $product_id, "_bulkdiscount_discount_fixed_$i", true ) ? get_post_meta( $product_id, "_bulkdiscount_discount_fixed_$i", true ) : 0.0 );
        } else {
            array_push( $d, get_post_meta( $product_id, "_bulkdiscount_discount_$i", true ) ? get_post_meta( $product_id, "_bulkdiscount_discount_$i", true ) : 0.0 );
        }
        if ( $quantity >= $q[$i] && $q[$i] > $q[0] ) {
            $q[0] = $q[$i];
            $d[0] = $d[$i];
        }
    }

    // for percentage discount convert the resulting discount from % to the multiplying coefficient
    if ( get_option( 'woocommerce_t4m_discount_type', '' ) == 'fixed' ) {
        return max( 0, $d[0] * $quantity );
    }

    return ( get_option( 'woocommerce_t4m_discount_type', '' ) == 'flat' ) ? max( 0, $d[0] ) : min( 1.0, max( 0, ( 100.0 - round( $d[0], 2 ) ) / 100.0 ) );
}

function wbu_clear_cart() {
    $items = WC()->cart->get_cart() ;

    WC()->cart->empty_cart();

    return $items;
}

function wbu_restore_cart($items) {
    foreach ( (array) $items as $item ) {
        WC()->cart->add_to_cart(
            $item['product_id'],
            $item['quantity'],
            ( !empty($item['variation_id']) ? $item['variation_id'] : null ),
            ( !empty($item['variation']) ? $item['variation'] : null )
        );
    }
}

function wbu_alter_quantity() {
    $quantity = filter_var(filter_input(INPUT_POST, 'quantity'), FILTER_VALIDATE_INT);
    $cart_item_key = filter_input(INPUT_POST, 'cart_item_key');

    WC()->cart->set_quantity( $cart_item_key, $quantity );
    
    echo json_encode( WC()->cart->get_cart_item($cart_item_key) );
    exit;
}

function wbupro_adding_to_cart($cart_item_key, $product_id, $quantity) {
    if ( !empty($_POST['is_wbu_quantity_sync']) &&
         isset($_POST['quantity']) ) {
        // when in sync mode, force to not sum the current quantity,
        // just replace with this new quantity changed by user
        $qtyToSet = isset($_REQUEST['wbu_force_change_quantity']) ? $_REQUEST['wbu_force_change_quantity'] : $_POST['quantity'];
        $qtyToSet = intval($qtyToSet);

        if ( $qtyToSet > 0 ) {
            WC()->cart->set_quantity($cart_item_key, $qtyToSet);
            $product_data = wc_get_product( absint($product_id) );

            if ( $product_data->managing_stock() && isset($_REQUEST['wbupro_prev_stock_quantity']) ) {
                $product_data->set_stock_quantity($_REQUEST['wbupro_prev_stock_quantity']);
                $product_data->save();
            }
        }
        else {
            WC()->cart->remove_cart_item($cart_item_key);
        }
        
    }
}

function wbupro_add_to_cart_quantity($quantity, $product_id) {
    if ( !empty($_POST['is_wbu_quantity_sync']) && isset($_POST['quantity']) && !empty($_POST['product_id']) ) {
        // when in sync mode, force to not sum the current quantity,
        // just replace with this new quantity changed by user
        if ( intval($_POST['quantity']) > 0 ) {
            $quantity = 1;
        }
        else {
            // TODO
            // WC()->cart->remove_cart_item($cart_item_key);
        }
    }

    return $quantity;
}

// when in ajax call, do not display the regular price
function wbu_format_sale_price($price) {
    global $product;
    return wc_price($product->get_price());
}

function wbu_delitem_checkout() {
    preg_match('/remove_item=(.*)&/', $_POST['data_remove_link'], $match);
    $cart_item_key = $match[1];
    
    WC()->cart->remove_cart_item( $cart_item_key );
    woocommerce_cart_totals();
    
    exit;
}

function wbupro_remove_cart_item($product_id, $variation_id = null) {
    $cart_item_key = null;

    foreach( WC()->cart->get_cart() as $key => $item ){
        if ( ( $item['product_id'] == $product_id ) &&
             ( empty($variation_id) || ( $item['variation_id'] == $variation_id ) ) ) {
            $cart_item_key = $key;
        }
    }
    
    if ( !empty($cart_item_key) ) {
        WC()->cart->remove_cart_item($cart_item_key);
    }
}

function wbupro_shop_quantity_value($currentValue, $product) {
    
    // shop product sync with cart conditions
    if ( wbu()->option('enable_quantity_on_shop') === 'yes' && wbu()->option('shop_sync_qty_with_cart') === 'yes' ) {
        
        // when sync automatically with cart, the initial value need to be zero
        // check if product is already present on cart, then fill with the current quantity
        
        $cartItem = wbupro_get_hash_by_product($product->get_id());

        return !empty($cartItem) ? $cartItem['quantity'] : 0;
    }

    if ( is_product() && wbupro_minicart_sync_enabled() ) {
        return 0;
    }
    
    return $currentValue;
}

function wbupro_get_hash_by_product($productId) {
    
    static $products = null;
    
    if ( $products === null && !empty(WC()->cart) ) {
        $products = array();
        
        foreach( WC()->cart->get_cart() as $item ){
            $products[ $item['product_id'] ] = $item;
        }
    }
    
    return !empty($products[$productId]) ? $products[$productId] : null;
}

function wbupro_show_inline_product_variations($html) {
    if ( wbu()->option('shop_inline_variations') != 'yes' ) {
        return $html;
    }

    global $product;

    $theme = get_option('stylesheet');
    $ignoredTheme = in_array($theme, array('flatsome'));

    if( !$ignoredTheme && $product->is_type( 'variable' ) ) {
        woocommerce_variable_add_to_cart();
    }

    return $html;
}

function wbuProCheckLiteVersionInstalled() {
    if ( defined('WBU_PLUGIN') ) {
        deactivate_plugins( WBU_PLUGIN );
    }
}

function wbupro_savoy_compat() {
    global $product;

    if ( is_shop() && $product->is_type('simple') && empty($_REQUEST['wc-ajax']) ) {
        echo wbu()->get_template('product-qty-input-shop-savoy.php', array('product' => $product));
    }
}

// override for the Woo Discount Rules plugin to work WBU PRO price calculation
function wbupro_getWooDiscountStrikeoutPriceOfProduct() {
    echo json_encode(array());
    exit;
}

function wbupro_wcas_show_quantity() {
    return false;
}