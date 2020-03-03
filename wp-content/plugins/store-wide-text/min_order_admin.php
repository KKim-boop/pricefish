<?php

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

$storeWide = "storeWide";

//set show price variable for show price checkbox
$storeWideOn = "storeWideOn";

 //if the form is submitted
if(isset($_POST["submit"])){ 

    if (!isset($_POST['min_order_update_setting'])) die('<div id="message" class="updated fade"><p>Not allowed</p></div>');

    if (!wp_verify_nonce($_POST['min_order_update_setting'],'min-order-update-setting')) die( '<div id="message" class="updated fade"><p>Not allowed</p></div>');



    //GRAB ALL THE INPUTS//////////////////

    //Grab min order and sanitize
    $storeWideShow = sanitize_text_field( $_POST[$storeWide] );

     //Grab theon option
    $storeWideOnShow =  isset($_POST[$storeWideOn]);
 



    ///////VALIDATE ALL INPUTS///////////////////////////


 
    
    //check min order amount is a number
    //$number = is_numeric($minOrderShow);

   

    //check not empty
    if($storeWideShow == "") 
    {

    $errorMsg=  "error : You did not enter any text";
    
    } else {


    


     
    update_option($storeWide, $storeWideShow);
    update_option($storeWideOn, $storeWideOnShow);
   
     //Success message
    echo '<div id="message" class="updated fade"><p>Options Updated</p></div>';
}
}

else{
    //If post not submitted, echo out the current status
    $storeWideShow  = get_option($storeWide);
  
    $storeWideOnShow = get_option($storeWideOn);


}
?>
<div class="wrap">
    
    <h2>Welcome to store wide text page</h2>
    <br />
    <br />
   <?php if (isset($errorMsg)) { echo "<div id='message' class='error fade'>" .$errorMsg. "</div>" ;} ?>
    <div class="">
        <fieldset>
            <legend>Store wide text settings</legend>
            <form method="post" action=""> 
             <input name="min_order_update_setting" type="hidden" value="<?php echo wp_create_nonce('min-order-update-setting'); ?>" />

             <table class="form-table" width="100%" cellpadding="10">
                <tbody>
                    <tr>
                        <td scope="row" align="left">

                            <label>Add Store Text</label>
                            <input type="text" 
                            name="<?php echo $storeWide; ?>" 
                            value="<?php echo esc_attr ($storeWideShow) ?>" /></input>
                        </td>
                    </tr>
                   
                    
                   
                    <tr>
                        <td scope="row" align="left">

                            <label>Do you want to turn on store wide text</label>
                            <input type="checkbox" name="<?php echo $storeWideOn; ?>" 
                            <?php echo $storeWideOnShow?"checked='checked'":""; ?> />
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

<?php


?>


