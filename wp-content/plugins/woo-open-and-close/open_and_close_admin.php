<?php

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly



//set show price variable for show price checkbox
$shopOn = "shopOn";

 //if the form is submitted
if(isset($_POST["submit"])){ 

    if (!isset($_POST['min_order_update_setting'])) die('<div id="message" class="updated fade"><p>Not allowed</p></div>');

    if (!wp_verify_nonce($_POST['min_order_update_setting'],'min-order-update-setting')) die( '<div id="message" class="updated fade"><p>Not allowed</p></div>');



    //GRAB ALL THE INPUTS//////////////////

    

     //Grab theon option
    $shopOnShow =  isset($_POST[$shopOn]);
 



    ///////VALIDATE ALL INPUTS///////////////////////////
    update_option($shopOn, $shopOnShow);
   
     //Success message
    echo '<div id="message" class="updated fade"><p>Options Updated</p></div>';
}

else{
    //If post not submitted, echo out the current status
    
  
    $shopOnShow = get_option($shopOn);


}
?>
<div class="wrap">
    
    <h2>Welcome to Shop open and close settings</h2>
    <br />
    <br />
   <?php if (isset($errorMsg)) { echo "<div id='message' class='error fade'>" .$errorMsg. "</div>" ;} ?>
    <div class="">
        <fieldset>
            <legend>Open and Close Settings</legend>
            <form method="post" action=""> 
             <input name="min_order_update_setting" type="hidden" value="<?php echo wp_create_nonce('min-order-update-setting'); ?>" />

             <table class="form-table" width="100%" cellpadding="10">
                <tbody>
                  
                   
                    
                   
                    <tr>
                        <td scope="row" align="left">

                            <label>Tick to open the shop, untick to close</label>
                            <input type="checkbox" name="<?php echo $shopOn; ?>" 
                            <?php echo $shopOnShow?"checked='checked'":""; ?> />
                        </td>
                    </tr>
                   
                    <tr>
                        <td>
                            <input type="submit" value="Save" class="button button-primary" name="submit" />
                        </br >

                    </td>
                </tr>
                <tr>
                    <td>
                        <h3>Please Double check all data saved</h3>
                    </td>
                </tr>
            </tbody>
        </table>                
    </form>
</fieldset>        
</div>  




