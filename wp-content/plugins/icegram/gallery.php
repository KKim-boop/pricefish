<style type="text/css">
	.ig-gallery-wrap .theme-browser .theme{
		border-radius: 5px;
		border: none;
		margin-bottom: 5%;
	    box-shadow: 1px 3px 10px 0 rgba(0,0,0,0.15);
	}
	.ig-gallery-wrap .theme-browser .theme:hover{
		    box-shadow: 1px 3px 10px 0 rgba(0,0,0,0.2);
	}
	.ig-gallery-wrap.wrap > h2:first-child{
		padding-left: 0 !important; 
	}
	.ig-gallery-wrap .theme-browser .theme .theme-screenshot{
		border-radius: 5px;
	}
	.ig-gallery-wrap .theme-browser .theme .theme-screenshot img{
		height: 100%;
	}
	.ig-gallery-wrap .theme-browser .theme .theme-installed{
		width: 2em;
  		padding: 0;
  		height: 2em;
	}
	.ig-gallery-wrap .theme-browser .theme .theme-installed:before{
		font-size: 30px;
		top:-2px;
		left:-4px;
	}
	.expanded .wp-full-overlay-footer.ig-get-pro-footer{
		position: fixed;
	    bottom: 60px;
	    left: 0;
	    height: 24px;
	    background: #dcdcdc;
	    text-align: center;
	    padding-top: 0.2em;
	    border-top: 1px dashed #ddd;
	    /*border-bottom: 1px dashed #ddd;*/
	}
	.expanded .wp-full-overlay-footer.ig-get-pro-footer span{
		/*color: #900101;*/
	}
	.wp-full-overlay-connect{
		background-color: #fff;
		width: 100%;
		height: 100%;
	}

	/* New CSS*/
	.igg-sidebar {
		float: left;
		width: 12.9%;
		margin: 0 2% 0 0;
		border-right: 1px solid #d2d2d2;
	}
	.igg-content {
		float: left;
		width: 85%;
	}
	.igg-sidebar .category > h2{
		/*margin-bottom: 0.5em;*/
		margin-top: 0.5em;
		margin-right: 0.5em;
		margin-bottom: 0;
		padding: 0.5em;
		cursor: pointer;
		transition: all 0.1s;
		border-radius: 3px;
		/*background-color: #e0e0e0;*/
		color: #909090;
		font-size: 1.15em;
	}
	
	/*.igg-sidebar .category > h2:hover{
		background-color: orange;
		color: #ffffff;
	}*/

	.igg-sidebar .category > ul{
		/*margin-left: 1em;*/
		padding-bottom: 0.5em;
		margin-right: 1em;
		margin-top:0.5em;
		/*margin-bottom:1em;*/
		cursor: pointer;
		border-bottom: 1px solid #dedede;
	}

	.igg-sidebar .category > ul > li {
		padding: 0.25em 1em;
		margin: 0;
		border-radius: 2px;
		transition: all 0.1s;
	}
	.igg-sidebar .category > ul > li:hover{
		background-color: #5f3af1;
		color: white;
	}

	.igg-content .theme {
		border: 7px solid white !important;
	}

	.igg-content .theme .title {
		white-space: nowrap;
		overflow: hidden;
		text-overflow: ellipsis;
		max-width: 75%;
		color: #b7b7b7;
		/*font-weight:200;*/
		font-size: 0.85em;
	}

	.pills {
		padding: 0.3em 0.6em;
	    background-color: #c5c5c5;
	    color: #ffffff;
	    font-size:1em;
	}
	.sq.pills {
		border-radius: 3px;
	}
	.pills.sm{
		font-size: 0.75em;
	}
	.pills.md{
		font-size: 1em;
	}
	.pills.lg{
		font-size: 1.3em;
		padding: 0.4em 0.75em;
	}
	.pills.upc{
		text-transform: uppercase;
	}
	.pills.cap{
		text-transform: capitalize;
	}

	.btn {
		padding: 0.75em 1em;
		color: #ffffff;
		border-radius: 3px;
		text-decoration: none;
	}
	.btn:hover {
		color: #ffffff;
		background-color
	}

	.btn.purple {
		background-color: hsl(244, 70%, 60%);
	}
	.btn.purple:hover {
		background-color: hsl(244, 70%, 45%);

	}
	.btn.green {
		background-color: hsl(169, 79%, 40%);;
	}
	.btn.green:hover {
		background-color: hsl(169, 79%, 25%);;
	}

	.igg-content .theme .pills.free,
	.igg-content .theme .pills.plus {
		display: none;
	}

	.igg-content .theme .pills.pro {
		background-color: #16b99a;
	}

	.igg-content .theme .pills.max {
		background-color: #5f3af1;
	}

	.igg-content .theme .locked {
		color: #e4951b;
	}
	
	.igg-content .theme .unlocked {
		color: #bdbdbd;
	}

	.ig-gallery-wrap .float-left {
		float: left;
	}
	.ig-gallery-wrap .float-right {
		float: right;
	}
	
	.ig-gallery-wrap .theme-browser {
		clear:both;
	}
	.ig-gallery-wrap .theme-browser .themes{
		clear:both;
	}

	.igg-content .filter-header {
		color: #888888;
	    margin: 0 0 2em 0;
	    /* display: none; */
	    border-bottom: 1px solid #e6e6e6;
	    padding-bottom: 1em;
	}
	.igg-content .filter-header .meta {
		font-size: 1em;
	    color: #bbbbbb;
	    /*margin-top: 1em;
	    padding-top: 0.5em;*/
	    display: inline-block;
	}

	/*.igg-content .category-title-def,*/
	.igg-content .category-type,
	.igg-content .category-title{
		background-color: #cacaca;
		cursor:pointer;
	}
	.igg-content .filter-header .dashicons-arrow-right {``
		color: #cacaca;
	}
		
	.igg-content .sub-category-title {
		background-color: #7971f1;
		cursor:pointer;
	}

	.igg-preview-sidebar .wp-full-overlay-sidebar-content .theme-screenshot {
		padding: 0.5em;
    	border-radius: 3px;
    	border: 1px solid #e4e4e4;
    	margin-bottom: 0 !important;
	}
	.igg-preview-sidebar .wp-full-overlay-sidebar-content .theme-description {
		float: none;
	    margin-top: 0.25em;
	    margin-bottom: 0.5em;
	    border-bottom: 1px solid #e6e6e6;
	    padding-bottom: 0.5em;
	}
	.igg-preview-sidebar .wp-full-overlay-sidebar-content .theme-name {
		line-height: 18px;
		margin-bottom: 0.25em;
	}
	.igg-preview-sidebar .wp-full-overlay-sidebar-content .theme-info {
		display: unset;
	}
	.igg-preview-sidebar .wp-full-overlay-header .theme-install {
		float: right;
		line-height: 10px;
		margin:8px 10px 0 0;
	}
	.igg-preview-sidebar .wp-full-overlay-sidebar-content .theme-by a {
		text-decoration: none;
		color: #2a25de;
    	font-style: italic;
	}

	.igg-preview-sidebar .wp-full-overlay-sidebar-content .tags {
		margin: 1em 0;
	}
	.igg-preview-sidebar .wp-full-overlay-sidebar-content .tags .plan {
		background-color: #7971f1;
	}

	.igg-preview-sidebar .wp-full-overlay-sidebar-content .tags .category {
		background-color: #16b99a;
	}

	.igg-preview-sidebar .wp-full-overlay-sidebar-content .tags .meta {
		margin-top:0.5em;
		font-size: 0.95em;
	}
	.igg-preview-sidebar .wp-full-overlay-sidebar-content .tags .meta .icon {
		color: #d0d0d0;
	}
	.igg-preview-sidebar .wp-full-overlay-sidebar-content .tags .meta .lbl {
		color: #9a9a9a;
		font-style: italic;
	}
	.igg-sidebar .search-form #wp-filter-search-input{
		width:13em;
		/*float: left;*/
		/*display: inline;*/
		/*border-bottom: 1px solid #ddd;*/
	}
	
		
</style>
<div class="wrap ig-gallery-wrap">
	<div class="wp-clearfix">
		<!--  Sidebar - quick filtering and searching -->
		<div class="description" style="padding-bottom:2em;">
				<h2 style="margin-bottom:0.5em;"><?php esc_html_e( 'Icegram design templates' ); ?></h2>
				<div><?php _e('Here\'s a collection of some ','icegram') ?><strong><?php _e('beautiful, powerful ready-to-use Icegram Campaigns.','icegram') ?></strong></div>
			    <div><?php _e('No coding or special skills required. Simply click to ' ,'icegram')?><strong><?php _e(' Use This ','icegram')?></strong> <?php _e('and the campaign will automatically appear in your Icegram dashboard.','icegram')?></div>
		</div>
		<div class="igg-sidebar">
				<form class="search-form"></form>
				<div class="meta"></div>
				<div class="category reset">
					<ul><li class="category-type" category="reset">Reset</li></ul>
				</div>
			<?php  
				foreach ($cat_list as $category) {
					?>
						<div class="category <?php echo $category['slug']?>">
							<h2><?php echo $category['name']?></h2>
							<ul>
							<?php 
							if(!empty($category['list'])){
								foreach ($category['list'] as $sub_cat) {
									?>
										<li class="category-type" category="<?php echo $category['slug']?>" <?php echo $category['slug']?>="<?php echo $sub_cat['term_id']?>" ><?php echo $sub_cat['name']?></li>
									<?php
								}
							}
						    ?>
							</ul>
						</div>
					<?php
				}

			?>
		</div>
		<div class="igg-content">
			<div class="theme-browser">
				<div class="themes wp-clearfix"></div>
			</div>
		</div>
	</div>
	<div class="theme-install-overlay wp-full-overlay expanded"></div>
<!-- <div class="theme-overlay"></div> -->
</div><!-- .wrap -->
<script id="tmpl-theme" type="text/template">
	<# if ( data.image ) { #>
		<div class="theme-screenshot">
			<div class="wp-clearfix">
				<img src="{{ data.image.guid }}" alt="" />
			</div>
		</div>
	<# } else { #>
		<div class="theme-screenshot blank"></div>
	<# } #>
	<span class="more-details" id="{{ data.id }}-action"><?php _e( 'Preview' ); ?></span>
	<div class="theme-author"><?php printf( __( 'By %s' ), '{{{ data.id }}}' ); ?></div>
	<div class="theme-id-container">
		<div class="theme-name wp-clearfix">
			<!-- <span>Active:</span> -->
			
			<!-- Theme title or description.. -->
			<# if( data.title.rendered ) { #>
				<span class="float-left title" title="{{ data.title.rendered }}">{{ data.title.rendered }}</span>
			<# } #>

			<!--  For logos and labels-->
			<div class="float-right">
				<!-- logo -->
				
				<!-- label -->
				<# if( data.plan_name ) { #>
					<span class="sq pills sm cap {{data.plan_name}}" title="{{ data.plan_name }}">{{ data.plan_name }}</span>
				<# } #>
			</div>
		</div>
	</div>
</script>
<!-- TODO:: Remove it if not required -->

<script id="tmpl-theme-preview" type="text/template">
	<div class="wp-full-overlay-sidebar igg-preview-sidebar">
		<div class="wp-full-overlay-header">
			<a href="#" class="close-full-overlay"><span class="screen-reader-text"><?php _e( 'Close' ); ?></span></a>
			<a href="#" class="previous-theme"><span class="screen-reader-text"><?php _ex( 'Previous', 'Button label for a theme' ); ?></span></a>
			<a href="#" class="next-theme"><span class="screen-reader-text"><?php _ex( 'Next', 'Button label for a theme' ); ?></span></a>
			<a href="?action=fetch_messages&campaign_id={{data.campaign_id}}&gallery_item={{data.slug}}" class="btn purple theme-install" style="display:none"><?php _e( 'Use This', 'icegram' ); ?></a>
			<a href="https://www.icegram.com/pricing/" target="_blank" class="ig-get-pro btn green" style="display:none;">
				<# if(data.plan === '3') { #>
					<span><?php _e("Get The Max Plan", 'icegram') ?></span>
				<# } else if(data.plan === '2') { #>
					<span><?php _e("Get The Pro Plan", 'icegram') ?></span>
				<# } #>	
			</a>
		</div>
		<div class="wp-full-overlay-sidebar-content">
			<div class="install-theme-info">
				<h3 class="theme-name">{{ data.title.rendered }}</h3>
				<span class="theme-by">
					<a href="https://www.icegram.com/"><?php printf( __( '- By %s' ), 'Icegram' ); ?></a>
				</span>

				<img class="theme-screenshot" src="{{ data.image.guid }}" alt="">

				<div class="theme-details">
					<div class="theme-description">{{ data.description }}</div>
					<div class="tags">
						<!-- plan-name -->
						<# if(data.plan_name) { #>
							<span class="sq pills sm cap plan">{{data.plan_name}}</span>
						<# } #>	
						<!-- categories -->
						
						<# _.each(data.category_names,function(cname){ #> 
							<# if(cname != 'no category') { #>
								<span class="sq pills sm cap category">{{cname}}</span>
							<# } #>	
						<# }) #>
					</div>
					<!-- <div class="theme-info">Liked this template? <br/>Here's how you can customize it further </div> -->
					<div class="theme-info" style="padding:0.2em;height:auto;">
						<p style="border-top: 1px solid #f3f3f3;padding: 1em 0;margin:0.5em 0;"><?php _e( 'Would you like to personalize this template to fit your brand?', 'icegram' );?></p>
						<div>
							<a href="https://www.icegram.com/documentation/customize-icegrams-gallery-templates/?utm_source=ig_gallery&utm_medium=ig_inapp_promo&utm_campaign=ig_custom_css" target="_blank" class="btn green" style="margin-top:0.4em;"><?php _e( 'Personalize It Now' , 'icegram'); ?></a>
						</div>
					</div>
				</div>
			</div>
		</div>
		<div class="wp-full-overlay-footer ig-get-pro ig-get-pro-footer " style="display:none">
		    <# if(data.plan === '3') { #>
				<span><?php _e("This template is available in the 'Max Plan'", 'icegram') ?></span>
			<# } else if(data.plan === '2') { #>
				<span><?php _e("This template is available in the 'Pro Plan'", 'icegram') ?></span>
			<# } #>
		</div>
		<div class="wp-full-overlay-footer">
			<button type="button" class="collapse-sidebar button-secondary" aria-expanded="true" aria-label="<?php esc_attr_e( 'Collapse Sidebar' ); ?>">
				<span class="collapse-sidebar-arrow"></span>
				<span class="collapse-sidebar-label"><?php _e( 'Collapse' ); ?></span>
			</button>
		</div>
	</div>
	<div class="wp-full-overlay-main">
		<iframe src="{{ data.link }}?utm_source=ig_inapp&utm_campaign=ig_gallery&utm_medium={{data.campaign_id}}" title="<?php esc_attr_e( 'Preview' ); ?>" />
	</div>
</script>
