<?php if ( ! defined( 'ABSPATH' ) ) exit; ?>
<?php
$max_value = !empty($args['max_value']) && ( $args['max_value'] > 0 ) ?
                $args['max_value'] :
                wbu()->select_max_quantity();
?>
<div class="quantity">
    <select name="<?php echo esc_attr( $input_name ); ?>"
            class="input-text qty text">
        <?php for ( $i=0; $i <= $max_value; $i++ ): ?>
            <option <?php if ( esc_attr( $input_value ) == $i ): ?>selected="selected"<?php endif; ?>
                    value="<?php echo $i; ?>">
                <?php echo $i; ?>
            </option>
        <?php endfor; ?>
    </select>
</div>
