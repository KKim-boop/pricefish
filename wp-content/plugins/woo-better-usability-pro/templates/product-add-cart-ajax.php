<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

global $product;

if ( ! $product->is_purchasable() ) {
	return;
}

echo wc_get_stock_html( $product );

if ( $product->is_in_stock() ) : ?>

	<?php do_action( 'woocommerce_before_add_to_cart_form' ); ?>

	<form class="cart" method="post" enctype='multipart/form-data'>
		<?php
			do_action( 'woocommerce_before_add_to_cart_button' );
			do_action( 'woocommerce_before_add_to_cart_quantity' );

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

			$qtyInput = woocommerce_quantity_input( array(
				'min_value'   => $minValue,
				'max_value'   => apply_filters( 'woocommerce_quantity_input_max', $product->get_max_purchase_quantity(), $product ),
				'input_value' => $inputValue,
			), null, false );
                        
            echo apply_filters('wbu_product_quantity_input', $qtyInput);

			do_action( 'woocommerce_after_add_to_cart_quantity' );
		?>

		<?php
                /**
                 * Start of wbu plugin overrides
                 */
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

                // echo apply_filters( 'woocommerce_loop_add_to_cart_link', $html, $product );

                // echo WBU()->shop->shop_quantity_input($html, $product);

                /**
                 * End of wbu plugin overrides
                 */
                ?>

		<?php
            do_action( 'woocommerce_after_add_to_cart_button' );
		?>
	</form>

	<?php do_action( 'woocommerce_after_add_to_cart_form' ); ?>

<?php endif; ?>


