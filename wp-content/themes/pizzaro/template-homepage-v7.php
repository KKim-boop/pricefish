<?php
/**
 * The template for displaying the homepage v7.
 *
 * This page template will display any functions hooked into the `pizzaro_homepage_v7` action.
 *
 * Template name: Homepage v7
 *
 * @package pizzaro
 */

remove_action( 'pizzaro_content_top', 'pizzaro_breadcrumb', 10 );

do_action( 'pizzaro_before_homepage_v2' );

get_header( 'v1'); ?>




<div id="primary" class="content-area">
	<main id="main" class="site-main" role="main">

		<?php


		/*echo do_shortcode('[rev_slider alias="test1"]'); */

		?>


	<section class="section sectionsitetitle">
		<div class="row">

				<!--Top Title-->
				<div class="col-md-12">
					<h3 class=" pricesSiteTitle"><span>Price's Fish and Chips</span></h3>

					<h2 class="subpricesSiteTitle">Not just any fish and chips</h2>
				</div>
				<!--End Top Title-->
		</div>
	</section>

	<section class="instagram">

	<?php echo do_shortcode('[instagram-feed]') ?>
	</section>




		<div class="stretch-full-width banner-with-post">
			<div class="row belowbannerrow">




				<div class="container">
					
					<div class="col-md-4 ">
						<div class="post-info ">
							<h3 class="smalltileheader text-center">Locally sourced beef</h3>

							<img class="img-responsve center-block" src="https://www.pricesfishandchips.co.uk/wp-content/uploads/2020/01/homeburger.png" />

							<p class="text-center">Locally sourced beef from Crow Farm Ringwood </p>	

						</div>
					</div>
					<div class="col-md-4 ">
						<div class="post-info ">
							<h3 class="smalltileheader text-center">Award winning MUD Pies from Hampshire.</h3>

							<img class="img-responsve center-block" src="https://www.pricesfishandchips.co.uk/wp-content/uploads/2018/06/pie.jpg" />


							<p class="text-center"> Steak &amp; Ale Pie.</p>	

						</div>
					</div>
					<div class="col-md-4 ">
						<div class="post-info ">
							<h3 class="smalltileheader text-center">Made in store</h3>

							<img class="img-responsve center-block" src="https://www.pricesfishandchips.co.uk/wp-content/uploads/2018/06/fishcake.jpg" />


							<p class="text-center"> We make all Fish cakes &amp; Pea fritters in store</p>	

						</div>
					</div>
					
				

				

					
			</div>
		</div>
	</div>

	<section class="section-recent-posts">
		<div class="row">



			<div class="col-md-4 col-xs-4">

				<div class="post-info ">

					<h3 class="smalltileheader text-center">We only use sustainably sourced Cod, Haddock & Plaice.</h3>
					
					<img class="img-responsve center-block" src="https://www.pricesfishandchips.co.uk/wp-content/uploads/2019/03/homepagecod300.jpg" />

					
					<p class="text-center"> </p>
					


					
					
				</div>
			</div>




			<div class="col-md-4 col-xs-4">

				<div class="post-info">

					<h3 class="smalltileheader text-center">We peel &amp; chip the best quality spuds available to us every day</h3>
					
					<img class="img-responsve center-block" src="<?php bloginfo('url'); ?>/wp-content/uploads/2018/01/peelingx300.jpg" />

					
					<p class="text-center"></p>
					


					
					
				</div>

			</div>
			<div class="col-md-4 col-xs-4">

				<div class="post-info">

					<h3 class="smalltileheader text-center">NFFF Members</h3>
					
					<img class="img-responsve center-block" src="<?php bloginfo('url'); ?>/wp-content/uploads/2018/01/nfff300.png" />
					
					<p class="text-center"> </p>
					


					
					
				</div>

			</div>

			<!--<div class="col-md-3 col-xs-3">

				<div class="lastsectiongj">

					<h3 class="smalltileheader text-center "> Food and Hygiene</h3>

				
					
					<p class="text-center"> </p>

				</div>
				
			</div>-->



		</div>		
	</section>	

	<hr>

	<section class="section">
		<div class="row">
			<div class="col-md-12">
				
				<!--<script src="https://apps.elfsight.com/p/platform.js" defer></script>
<div class="elfsight-app-af97b102-9c74-4cde-b103-d741acc6f576"></div>-->
			</div>
		</div>

		<style>
			#CDSWIDSSP {width: 100% !important;}
#CDSWIDSSP.widSSPnarrow .widSSPData .widSSPBranding dd {width: 100% !important;}
		</style>

	</section>

	<section class="facebook">
		<div class="row">
			<div class="col-md-6">
				<!--<img class="img-responsive" src="https://www.pricesfishandchips.co.uk/wp-content/uploads/2018/07/facebook600new.png" />-->
				<div id="TA_selfserveprop494" class="TA_selfserveprop"><ul id="vQ0FTEFh0wrV" class="TA_links 1iyq97GQezRH"><li id="ZLp7oOlDlJT" class="fttQKg8p"><a target="_blank" href="https://www.tripadvisor.co.uk/"><img src="https://www.tripadvisor.co.uk/img/cdsi/img2/branding/150_logo-11900-2.png" alt="TripAdvisor"/></a></li></ul></div><script async src="https://www.jscache.com/wejs?wtype=selfserveprop&amp;uniq=494&amp;locationId=14952132&amp;lang=en_UK&amp;rating=true&amp;nreviews=4&amp;writereviewlink=true&amp;popIdx=false&amp;iswide=true&amp;border=true&amp;display_version=2" data-loadtrk onload="this.loadtrk=true"></script>

			</div>
			<div class="col-md-6 ">
				<!--<div class="parent-container">

					
					<div class="reviews child-container">-->

					<h3 class="facebooktitle">Facebook Reviews</h3>

						<div class="slider">
							<div class="slide1">
						<div class="singlereview">
							<p>
								<i class="far fa-star"></i>
								<i class="far fa-star"></i>
								<i class="far fa-star"></i>
								<i class="far fa-star"></i>
								<i class="far fa-star"></i>
								- 22nd May 2018
							</p>
							<p>Just had something from Prices. Lovely highly recommend.</p>
						</div>
						<div class="singlereview">
							<p>
								<i class="far fa-star"></i>
								<i class="far fa-star"></i>
								<i class="far fa-star"></i>
								<i class="far fa-star"></i>
								<i class="far fa-star"></i>
								- 18th May 2018
							</p>
							<p>The best fish and chip shop in ringwood. The pea fritters and halloumi burgers are incredible and highly recommended.</p>
						</div>
						<div class="singlereview">
							<p>
								<i class="far fa-star"></i>
								<i class="far fa-star"></i>
								<i class="far fa-star"></i>
								<i class="far fa-star"></i>
								<i class="far fa-star"></i>
								- 29th April 2018
							</p>
							<p>Chips are very nice and the burgers are so good keep up the good work love it all Week and every Friday i go there for lunch</p>
						</div>

						<!--<div class="singlereview">
							<a href="https://www.facebook.com/PricesFishandChips/" class="btn btnluke">Review</a>
						</div>-->
						</div>
						<div class="slide2">
								<div class="singlereview">
							<p>
								<i class="far fa-star"></i>
								<i class="far fa-star"></i>
								<i class="far fa-star"></i>
								<i class="far fa-star"></i>
								<i class="far fa-star"></i>
								- 22nd May 2018
							</p>
							<p>The best fish and chips in Ringwood. Friendly staff, great pies and great portion sizes all well priced.</p>
						</div>
						<div class="singlereview">
							<p>
								<i class="far fa-star"></i>
								<i class="far fa-star"></i>
								<i class="far fa-star"></i>
								<i class="far fa-star"></i>
								<i class="far fa-star"></i>
								- 18th May 2018
							</p>
							<p>Authentic fish a chips. The hard graft of homemade pies ect really makes it a stand out chippy!</p>
						</div>
						<div class="singlereview">
							<p>
								<i class="far fa-star"></i>
								<i class="far fa-star"></i>
								<i class="far fa-star"></i>
								<i class="far fa-star"></i>
								<i class="far fa-star"></i>
								- 29th April 2018
							</p>
							<p>Beautiful tasting battered cod, perfectly cooked and flakey👌👌 chips were the best I have had in a very long time!</p>
						</div>
					</div><!--end slide-->
					<!--SLide 3-->
					<div class="slide3">
								<div class="singlereview">
							<p>
								<i class="far fa-star"></i>
								<i class="far fa-star"></i>
								<i class="far fa-star"></i>
								<i class="far fa-star"></i>
								<i class="far fa-star"></i>
								- 22nd May 2018
							</p>
							<p>The chips are delicious with lots of crispy bits. Great chippy!</p>
						</div>
						<div class="singlereview">
							<p>
								<i class="far fa-star"></i>
								<i class="far fa-star"></i>
								<i class="far fa-star"></i>
								<i class="far fa-star"></i>
								<i class="far fa-star"></i>
								- 18th May 2018
							</p>
							<p>Best fish and chips in Ringwood and Poulner...hope they keep it up!</p>
						</div>
						<div class="singlereview">
							<p>
								<i class="far fa-star"></i>
								<i class="far fa-star"></i>
								<i class="far fa-star"></i>
								<i class="far fa-star"></i>
								<i class="far fa-star"></i>
								- 29th April 2018
							</p>
							<p>Truly wonderful fish and chips! Best in Ringwood</p>
						</div>
					</div>


				</div>

			</div>
		</div>
	</section>

	


	<hr>

		<div class="stretch-full-width banner-with-post padded">
			<div class="row text-center">
				<div class="col-md-12">
					<h3 class="">Sign up below, we'll send you emails when we have new deals in-store</h3>

					<?php echo do_shortcode('[mc4wp_form id="1157"]'); ?>
				</div>
			</div>
		</div>

		
    
 

	<style type="text/css">

@media only screen and (max-width: 600px) {
    .pricesSiteTitle {
        font-size: 40px;
    }
    .subpricesSiteTitle{
    	font-size: 20px;
    }
}
@media only screen and (min-width: 768px) {
	.pricesSiteTitle{
	font-size: 80px;}
	.subpricesSiteTitle{
		font-size: 40px;
	}
}

	.pricesSiteTitle{
		text-align: center;
		font-family: 'Pathway Gothic One';
		
		text-transform: uppercase;
		color: #023b69;
		margin-bottom: 0px;
	}

	.sectionsitetitle{
		margin-top: 40px;
		margin-bottom: 40px;
	}

	.subpricesSiteTitle{
		text-align: center;
		font-family: 'Pathway Gothic One';
		
		text-transform: uppercase;
		
		color:#bba12a;
	}

	.gjinput{border: 1px solid #000000 !important; margin-bottom: 5px;}

	.parent-container {
		position: relative;
		height:100%;
		width: 100%;
		min-height: 600px;
	}

	.child-container {
		position: absolute;
		top: 50%;
		left: 40%;
		transform: translate(-50%, -50%);
	}

	.btnluke{
		width: 75%;
		background-color: #023b69;
		color: #ffffff;

	}

	.fa-star{
		color: #023b69;
	}

	.facebooktitle{
		color: #023b69;
	}

	@media screen and (max-width: 600px) {
		.facebooktitle {
				margin-top: 10px;
  }
}


	/*Tiles styles */
	.belowbannerrow{margin-top: 15px;}

	.tileheader {font-size: 70px;}
	.titleheaderl{font-size: 50px;}
	.stitleheaderl{font-size: 28px; font-weight: 900; text-shadow: 2px 2px #173D7A;}
	.smalltileheader{font-size: 16px;}
	/*#menu-item-935{border: 1px solid #BBA22A;}*/
	.white {color: #ffffff;}
	.section-recent-post .post-wrap {top: 52%;}
	.menudownload {
		/*background-image: url(http://ac5.c4c.myftpupload.com/wp-content/uploads/2018/01/plainchalk.jpeg);*/
		background-repeat: no-repeat;
		background-color: #2D2B2D;

	}
	.padded{padding: 20px;}
	.centerme{text-align: center !important;}
	.black {color: #000000 !important;}
	.captionl{top: 25% !important;}
	.blue {color: #173D7A;}
	.lastsectiongj{padding-right: 20px;}

	/*BX Slider */
	.bx-next {
		float: right;
		color: #ffffff;
		background-color: #023b69;
		border-radius: 5px;
		padding: 5px;

	}
	.bx-next:hover{
		color: #ffffff;
	}
	.bx-prev{
		float: left;
		color: #ffffff;
		background-color: #023b69;
		border-radius: 5px;
		padding: 5px;

	}
	.bx-prev:active{
		color: #ffffff;
	}


.center-block {padding-bottom: 15px; }
.post-title { color: #bba12a;}
.section-recent-posts{
	padding-bottom: 20px;
}

.facebook{
	padding-bottom: 20px;
}


</style>

	




</main><!-- #main -->
</div><!-- #primary -->
<?php
get_footer( 'v2' );
