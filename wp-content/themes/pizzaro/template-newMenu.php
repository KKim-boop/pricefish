<?php
/**
 * The template for displaying the homepage.
 *
 * This page template will display any functions hooked into the `pizzaro_contactpage` action.
 * By default this includes a variety of product displays and the page content itself. To change the order or toggle these components
 * use the Homepage Control plugin.
 * https://wordpress.org/plugins/homepage-control/
 *
 * Template name: NewMenu
 *
 * @package pizzaro
 */

remove_action( 'pizzaro_content_top', 'pizzaro_breadcrumb', 10 );


get_header(); ?>



	<div class="row box nmrow">

		<div class="col-md-12 nmboxone">
			<img class="img-responsive" src="https://www.pricesfishandchips.co.uk/wp-content/uploads/2018/06/5.jpg" />

		</div>

		<!--New images -->
		<!--<div class="col-md-4 nmboxone">
			<img class="img-responsive" src="https://www.pricesfishandchips.co.uk/wp-content/uploads/2018/06/3.jpg" />

		</div>
		<div class="col-md-4 nmboxone">
			<img class="img-responsive" src="https://www.pricesfishandchips.co.uk/wp-content/uploads/2018/06/4.jpg" />

		</div>
		<div class="col-md-4 nmboxone">
			<img class="img-responsive" src="https://www.pricesfishandchips.co.uk/wp-content/uploads/2018/06/5-1.jpg" />

		</div>-->
		

		<!--
		<div class="col-md-9  nmboxone bggrey">
			<div class="vcenter"><p class="haveyou"><b>Have you tried</b> one of MUD pies award winning pies yet?</p>
			</div>
		</div>
		<div class="col-md-3  nmboxone bggrey">
			<div class="vcenter textinfo text-center">
				<a href="https://www.pricesfishandchips.co.uk/shop" class="btn btnluke">Order</a>
			</div>
		</div>-->



		<!--<div class="col-md-3 text-center nmboxone">
			
			<img class="img-responsive" src="https://www.pricesfishandchips.co.uk/wp-content/uploads/2018/06/steakandalepie.jpg" />
		</div>
		<div class="col-md-3 text-center nmboxone">
			
			<img class="img-responsive" src="https://www.pricesfishandchips.co.uk/wp-content/uploads/2018/06/chickenhamandleekpie.jpg" />
		</div>
		<div class="col-md-3 text-center nmboxone">
			
			<img class="img-responsive" src="https://www.pricesfishandchips.co.uk/wp-content/uploads/2018/06/shiitakeandasparaguspie.jpg" />
		</div>-->
		
		<!--<div class="col-md-9 text-center nmboxonell ">
			<div class="vcenter"><p class="haveyou">Have you tried one of <br />MUD pies award winning pies yet?</p></div>
		</div>-->
		<div class="col-md-12 nmboxone">
			<img class="img-responsive" alt="fish and chips menu" src="https://www.pricesfishandchips.co.uk/wp-content/uploads/2020/01/Menu_Tri_Fold_Final_Jan.jpg" />

		</div>
		<div class="col-md-12 nmboxone">
			<img class="img-responsive" alt="fish and chips menu" src="https://www.pricesfishandchips.co.uk/wp-content/uploads/2020/01/Menu_Tri_Fold_Final_Jan_2.jpg" />

		</div>

	

		<!--<div class="col-md-12 text-center mbtop">
			<a href="https://www.pricesfishandchips.co.uk/wp-content/uploads/2018/06/Menu-2018-Summer.pdf" class="btn pricebtn">Download Menu</a>
		</div>-->
	</div>

	<style type="text/css">
		.nmbox{
			border: 2px solid black;
		}

		.nmrow{
			margin-bottom: 20px;
		}
		.nmboxone{
			margin-bottom: 10px;
			padding: 0px;
			
		}
		.nmboxonell{
			margin-bottom: 10px;
		}
		.mbtop{margin-top: 10px;}
		.pricebtn{
			border-radius: 5px;
			background-color: #143B7C;
			color: #ffffff;
			width: 50%;
		}
		.haveyou{
			font-size: 20px;
    		line-height: 43px;
    		padding: 30px 10px 0px 10px;
    		color: #143B7C;
    		text-transform: uppercase;
    		font-family: 'Signika', sans-serif;
		}
		.bggrey{
			background-color: #eeeeee;
		}
		.btnluke{
			width: 75%;
			background-color: #023b69;
			color: #ffffff;
			
		}
		.textinfo{
			padding-top: 30px;
			padding-bottom: 29px;
		}
		.vcenter {
    		/*border: 1px solid #143B7C;*/
    		/*min-height: 270px;*/
}

	</style>

	<?php 
get_footer();
