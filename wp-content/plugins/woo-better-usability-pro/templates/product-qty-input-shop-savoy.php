<?php
if ( ! defined( 'ABSPATH' ) ) exit;

if ( function_exists('wbupro_product_input_value') ) {
    $inputValue = wbupro_product_input_value($product);
}
else {
    $inputValue = isset( $_POST['quantity'] ) ? wc_stock_amount( $_POST['quantity'] ) : $product->get_min_purchase_quantity();
}

if ( function_exists('wbupro_product_min_value') ) {
    $minValue = wbupro_product_min_value($product);
}
else {
    $minValue = apply_filters( 'woocommerce_quantity_input_min', $product->get_min_purchase_quantity(), $product );
}
?>
<form class="cart" action="" method="post" enctype="multipart/form-data">    
    <div class="nm-quantity-wrap">
        <label>Quantity</label>
        <label class="nm-qty-label-abbrev">Qty</label>

        <div class="quantity">
            <div class="nm-qty-minus nm-font nm-font-media-play flip wbu-qty-button wbu-btn-sub"></div>

            <input type="number" id="qty_prod_<?php echo $product->get_id(); ?>" class="input-text qty text"
                    step="1" min="<?php echo $minValue; ?>" max="99999" name="quantity" value="<?php echo $inputValue; ?>">
            
            <div class="wbu-qty-button wbu-btn-inc nm-qty-plus nm-font nm-font-media-play"></div>
        </div>
    </div>
    <?php
        $class = implode( ' ', array_filter( array(
            'button',
            'product_type_' . $product->get_type(),
            $product->is_purchasable() && $product->is_in_stock() ? 'add_to_cart_button' : '',
            'ajax_add_to_cart single_add_to_cart_button'
        ) ) );

        $html = sprintf( '<button type="submit" name="add-to-cart" href="%s" data-quantity="%s" data-product_id="%s" value="%s" data-product_sku="%s" class="%s">%s</button>',
            esc_url( $product->add_to_cart_url() ),
            esc_attr( isset( $quantity ) ? $quantity : 1 ),
            esc_attr( $product->get_id() ),
            esc_attr( $product->get_id() ),
            esc_attr( $product->get_sku() ),
            esc_attr($class),
            esc_html( $product->add_to_cart_text() )
        );

        echo $html;
        ?>
</form>