<?php if ( ! defined( 'ABSPATH' ) ) exit; ?>
<div class="quantity wbu-quantity" style="display: inline;">
    <?php if ( $show_buttons ): ?>
        <a href="" class="wbu-qty-button wbu-btn-sub" style="display: inline;">-</a>
    <?php endif; ?>
    <?php echo woocommerce_quantity_input( array(
        'min_value' => 0,
        'input_value' => $input_value,
        'input_name' => $input_name), $product, false ); ?>
    <?php if ( $show_buttons ): ?>
        <a href="" class="wbu-qty-button wbu-btn-inc" style="display: inline;">+</a>
    <?php endif; ?>
</div>
