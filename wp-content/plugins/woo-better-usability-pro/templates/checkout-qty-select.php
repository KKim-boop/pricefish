<?php
if ( ! defined( 'ABSPATH' ) ) exit;

$maxValues = array(
    ( $product->backorders_allowed() ? '' : $product->get_stock_quantity() ),
    wbu()->option('qty_select_items')
);

?>
<div class="quantity">
    <select name="cart[<?php echo $cart_item_key; ?>][qty]" class="input-text qty text">
        <?php for ( $i=0; $i <= max($maxValues); $i++ ): ?>
            <option <?php if ( esc_attr( (int) $cart_item['quantity'] ) == $i ): ?>selected="selected"<?php endif; ?>
                    value="<?php echo $i; ?>">
                <?php echo $i; ?>
            </option>
        <?php endfor; ?>
    </select>
</div>
