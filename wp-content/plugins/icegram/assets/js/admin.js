jQuery(function() {
	var home_url = icegram_writepanel_params['home_url'];

	function display_message_themes(this_data) {
		var message_type = jQuery(this_data).find('.message_type').val();
		jQuery(this_data).closest('.message-edit-row').prev()
					.find('.message_header_label').text(message_type)
					.removeClass().addClass('message_header_label ig_' + message_type);

		var message_theme 	= jQuery(this_data).find('.message_row.ig_'+message_type).find('.message_theme').val();
		var form_style 	= jQuery(this_data).find('.message_row.ig_'+message_type).find('#message_form_style').val();
		var message_thumb 	= jQuery(this_data).find('#message_theme_ig_'+message_type).find('.'+message_theme).attr('style');
		var form_thumb 	= jQuery(this_data).find('#message_form_style').find('.'+form_style).attr('style');
		jQuery(this_data).find('.message_row, .location').hide();
		jQuery(this_data).find('.ig_' + message_type).show();
		jQuery(this_data).find('.message_row.ig_' + message_type).find('.message_theme').next().find('.chosen-single span').attr('style',message_thumb);
		jQuery(this_data).find('.message_row.ig_' + message_type).find('#message_form_style').next().find('.chosen-single span').attr('style',form_thumb)
					 	 .text(function(){return jQuery(this).text().substr(0, jQuery(this).text().indexOf(' '))||jQuery(this).text() ; });
		if( jQuery(this_data).find('.message_body').parent().css('display') !== 'block' ) {
			jQuery(this_data).find('.message_body').parent().next('.wp-editor-wrap').hide();
		} else {
			jQuery(this_data).find('.message_body').parent().next('.wp-editor-wrap').show();
		}
		
		if(!jQuery(this_data).find('.message_form_layout:checked').is(':visible')){
			jQuery(this_data).find('.message_form_layout:visible').first().prop('checked', true);
		}
		if(jQuery(this_data).find('.show_form_options').prop('checked') == true){
	    	jQuery(this_data).find('.message_link').parent().hide();
		}
		jQuery('.message_form_layout').change();

	}

	function get_random_int(current, min, max) {
		var random_int = Math.floor(Math.random() * (max - min + 1)) + min;
		if ( random_int == current ) {
			return get_random_int( random_int, min, max );
		} else {
			return random_int;
		}
	}
	// Type box
	// jQuery('.campaign_data').find('h3.handle').hide();
	// jQuery('.target_rules_desc').appendTo('.campaign_target_rules h3.handle span');

	jQuery(document).ready(function() {
		var tabs = jQuery('#ig-admin-tabs');

		// Show Main Tab, By default
		tabs.find('.ig-admin-nav-main').addClass('current');
		tabs.find('#ig-admin-tab-main').show();

		// show gallery first:start
		if(adminpage === "post-new-php"){
			// jQuery('#poststuff').find('.ig-gallery-wrap').addClass('ig-gallery-position')

			jQuery('#wpbody').on('click', '#ig-add-new-campaign', function(){
				jQuery('#poststuff').css('position', 'static');
				jQuery('#postbox-container-1 #side-sortables').removeClass('empty-container').addClass('gal-toggled');
				jQuery('.gallery-heading, .ig-gallery-wrap').hide();
				jQuery('.postbox-container').show()
				jQuery('.wrap h1.wp-heading-inline').not('.gallery-heading').show();
				jQuery('.ig-gallery-wrap').siblings().not('.gallery-heading').show()
				jQuery('.postbox-container').siblings().show()
			});
		}
		// show gallery first:end

		jQuery('#ig-admin-tabs').on('click', '.ig-admin-tabs-nav li', function(event) {
			event.preventDefault();
	        if(!jQuery(this).hasClass('ig-admin-nav-notab')){
		        tabs.find('.ig-admin-tabs-nav li').not(this).removeClass('current');
		        jQuery('.ig-admin-tab').fadeOut('fast');
		        jQuery(jQuery(this).addClass('current').find('a').attr('href')).fadeIn('fast');

		        if(jQuery(this).hasClass('new-variation')){
		        	jQuery(this).removeClass('current');
		        	jQuery('.ig-admin-nav-variations').addClass('current');
		        }
	        }
	    });


		var original_send_to_editor = window.send_to_editor;

		//Adding a preview button in side bar widget
		var prvw_button = jQuery('.ig_preview_button');
		jQuery('#submitdiv .submitbox #minor-publishing-actions').after(prvw_button)
		prvw_button.fadeIn('fast');

		// jQuery('#postdivrich').hide();
		jQuery('.color-field').wpColorPicker().each(function(index){
			var colorPicker_label = jQuery(this).data('color-label') || '';
			if(colorPicker_label !== ''){
				jQuery(this).closest('.wp-picker-container').find('a.wp-color-result').attr('title', colorPicker_label);
			}
		});

		//hide Colors container
		jQuery('.campaign_data, #message-settings').on('change', '.show_color_options', function() {
		    jQuery(this).closest('p').next('.message_colors_options_container').toggle(!this.checked);
		}).change();

		hide_empty_campaign_message();

		jQuery('.message_edit:first').trigger('click');

		this_data = jQuery('.message_type').closest('.message-setting-fields');
		for (var i = 0; i < this_data.length; i++) {
			display_message_themes(this_data[i]);
		};

		// jQuery('.campaign_data, #message-settings').on('change', '.message_type', function(e) {
		// 	var t = jQuery(e.target).parents('.message-setting-fields');
		// 	display_message_themes(t);
		// });

		jQuery('.campaign_data, #message-settings').on('change', '.message_theme', function(e) {
			var t = jQuery(e.target).parents('.message-setting-fields');
			var message_type = jQuery(t).find('.message_type').val();
			var message_theme = jQuery(t).find('.message_row.ig_'+message_type).find('.message_theme').val();
			var message_thumb = jQuery(t).find('#message_theme_ig_'+message_type).find('.'+message_theme).attr('style');
			jQuery(t).find('.message_row.ig_'+message_type).find('.message_theme').next().find('.chosen-single span').attr('style',message_thumb);
		});

		//for rainmaker_form
		jQuery('.campaign_data, #message-settings').on('change', '.rainmaker_form_list', function(e) {
			if((jQuery(e.target).val() || 'null') !== 'null'){
				jQuery(e.target).parent().siblings('.message_form_html_original').hide();
			}
		});

	    jQuery('.campaign_data, #message-settings').on('click', '.message_image_button', function(event) {
			var that = this;
			window.send_to_editor = function(html) {
				imgurl = jQuery('img', html).attr('src');
				jQuery(that).parent().find('#upload_image').val(imgurl);
				tb_remove();
				window.send_to_editor = original_send_to_editor;
			};
			return false;
		});

		jQuery('.campaign_data, #message-settings').on('click', '.message_headline_button', function() {
			var headline_key = jQuery(this).prev().attr('data-headline');
			var headline_max = icegram_writepanel_params.available_headlines.length;
			var new_headline_key = get_random_int( headline_key, 0, headline_max );
			var new_headline = icegram_writepanel_params.available_headlines[ new_headline_key ];
			jQuery(this).prev().val( new_headline );
		});

		jQuery(".tips, .help_tip").tipTip({'attribute' : 'data-tip'});

		jQuery('span.test_class').hover(function(){
			jQuery(this).next().show();
		}, function(){
			jQuery(this).next().hide();
		});

		// Disable closing message list
		jQuery('.campaign_data .handle, .campaign_data .handlediv').unbind('click');
		jQuery('.campaign_data .handlediv').hide();
		jQuery('#poststuff').on('click', '#publish', function(event){
			if(jQuery('.campaign_data').find('.message_header_label.ig_unknown').length){
				alert('Please select Message type');
				event.preventDefault();
			}
		});

	});

    jQuery('.campaign_data, #message-settings').on('click','.message_delete', function() {
		jQuery(this).parent().parent().next().remove();
		jQuery(this).parent().parent().remove();
		hide_empty_campaign_message();

	});

	jQuery('.campaign_data, #message-settings').on( 'click', '.message_edit', function() {
		jQuery(this).parent().parent().next().toggle();
		jQuery(this).parent().parent().find('.message-title-text, .message-title-input').toggle();	
	});

	jQuery('.campaign_data, #message-settings').on( 'click', '.embed_form_code_toggle', function() {
		jQuery(this).parent().parent().siblings('.message_form_html_original').toggle();	
	});

	jQuery('.campaign_data, #message-settings').on( 'change', '.message-title-input',function() {
		jQuery(this).prev().text(jQuery(this).val());
	});

	jQuery("select.ajax_chosen_select_messages").ajaxChosen({
		type: 'GET',
		url: icegram_writepanel_params.ajax_url,
		dataType: 'json',
		afterTypeDelay: 100,
		data: {
			action: 'icegram_json_search_messages',
			security: icegram_writepanel_params.search_message_nonce
		}
	}, function(data) {
		var terms = {};
		jQuery.each(data, function(i, val) {
			terms[i] = val;
		});
		return terms;
	});
	
	// Embed Form Controls and navigations 
	jQuery('.campaign_data, #message-settings').on('change', '.show_form_options', function(event) {
		var parent_node = jQuery(this).closest('p');
	    jQuery(parent_node).siblings('.message_form_options').slideToggle(this.checked);
	    if(!this.checked){
			jQuery(parent_node).siblings('p.cta-actions').find('select option').removeAttr('disabled');
			// TODO :: Hiding position but not add shortcode in msg body
		  	// var visiblePosition = jQuery(parent_node).siblings('.message_form_options').find('.form_radio_group span:visible');
		  	// if(visiblePosition.length == 1){
		  	// 	visiblePosition.hide();
		  	// }
			var msg_editor = jQuery(parent_node).siblings('.message_form_options').siblings('.wp-editor-wrap').find('.wp-editor-area');
			var msg_editor_text = jQuery(msg_editor).val().trim().replace("[ig_form]", '');
			jQuery(msg_editor).val(msg_editor_text);
	    	jQuery(parent_node).closest('.thickbox_edit_message').find('.message_link').parent().show();
	    }else {
	    	// jQuery(parent_node).parent().find('.message_link').parent().hide();
	    	//thickbox_edit_message
	    	jQuery(parent_node).closest('.thickbox_edit_message').find('.message_link').parent().hide();
			jQuery(parent_node).siblings('p.cta-actions').find('select option').removeAttr('disabled');
			jQuery(parent_node).siblings('p.cta-actions').find('select')
							   .find('option[value="url"], option[value="hide"], option[value="cta_another_message"]')
							   .attr("disabled", true)
							   .attr("selected", false)
							   .end()
							   .find('option[value="form"]')
							   .attr("selected", true)
							   .trigger('change');

	    	jQuery(parent_node).siblings('.message_form_options').find('.message_form_layout').change();
	    	 // TODO:: test this. // msg_editor_text = msg_editor_text + "[ig_form]";
	    }
	}).change();

	jQuery('.campaign_data, #message-settings').on('change', '.message_form_style', function(e) {
			var t = jQuery(e.target).parents('.message-setting-fields');
			var form_style 	= jQuery(t).find('#message_form_style').val();
			var form_style_thumb = jQuery(t).find('#message_form_style').find('.'+form_style).attr('style');
			jQuery(t).find('.message_form_style').next().find('.chosen-single span').attr('style',form_style_thumb)
					 .text(function(){return jQuery(this).text().substr(0, jQuery(this).text().indexOf(' '))||jQuery(this).text() ; });

	});
	jQuery('.campaign_data, #message-settings').on('change', '.message_form_layout ' , function() {
		if(jQuery(this).is(':visible')){
			var that = this;
			var msg_editor = jQuery(this).closest('.message_form_options')
									.siblings('.wp-editor-wrap')
									.find('.wp-editor-area');
			var msg_editor_text = jQuery(msg_editor).val().trim();
			jQuery(that).closest('.form_radio_group').siblings('.form_inline_shortcode').hide();
			jQuery(that).closest('.form_radio_group').siblings('.message_form_color').show();
			if(jQuery(that).is(':checked') && jQuery(that).closest('.message_form_options').prev('p.message_form_options_check').find('.show_form_options').is(':checked')){
				if(jQuery(that).val() == 'inline'){
					if(msg_editor_text.indexOf('[ig_form]') == -1){
						msg_editor_text = msg_editor_text + "[ig_form]";
					}
					jQuery(that).closest('.form_radio_group').siblings('.message_form_color, .form_inline_shortcode').toggle();
				}else if(jQuery(that).val() != 'inline'){
					msg_editor_text = msg_editor_text.replace("[ig_form]", '');
				}
			}
			jQuery(msg_editor).val(msg_editor_text);
		}
	});

	jQuery('.campaign_data, #message-settings').on('blur', '.message_form_html_original' , function(event) {
		//change the button colors and CTA options HERE.
		var that = this;
		var buttons = jQuery('<div/>').html(jQuery(that).val()).find('input[type=submit], button, input[type=button]').not('*:disabled');
		// var parent_node = jQuery(that).closest('.message_form_options');
		// jQuery(parent_node).parent().find('p.cta-actions select option').removeAttr('disabled');
		// if(jQuery(that).val() != undefined && jQuery(that).val() != ''){
		// 	jQuery(parent_node).parent().find('p.cta-actions select')
		// 					   .find('option[value="url"], option[value="hide"], option[value="cta_another_message"]')
		// 					   .attr("disabled", true)
		// 					   .attr("selected", false)
		// 					   .end()
		// 					   .find('option[value="form"]')
		// 					   .attr("selected", true)
		// 					   .trigger('change');
		// }
		if(buttons.length > 0){
			var button = jQuery(buttons[buttons.length-1]);
			var button_text = button.is('button') ? button.not('br, span, div').text() : button.val();
			jQuery(that).closest('.message_form_options').siblings('p').find('#message_label').val(button_text.trim());
		}
	});
	// Embed Form - end

	// show/hide custom code insert boxes
	jQuery('.campaign_data, #message-settings').on('change', '.show_custom_code_options' , function(event) {
		var parent_node = jQuery(this).closest('p');
	    jQuery(parent_node).siblings('.message_custom_code_options').slideToggle(this.checked);
	});
	
	//var message_rows = jQuery(this).parent().siblings('.campaign_target_rules_panel').find('.message-row').length;
	jQuery('.ajax_chosen_select_messages').chosen();
	jQuery('.campaign_data, #message-settings').on('change', '.ajax_chosen_select_messages' , function() {
		var params = {};
		var selected_tab = jQuery('#ig-admin-tabs li.current').attr('variation_id');
		if(typeof(selected_tab) !== 'undefined'){
			jQuery.extend(params, {'selected_tab':selected_tab});
		}
		var newSettings = jQuery.extend( {}, tinyMCEPreInit.mceInit[ 'content' ] );
		
		var newQTS = jQuery.extend( {}, tinyMCEPreInit.qtInit[ 'content' ] );
		var parent_campaign_box = jQuery(this).parent().siblings('.campaign_target_rules_panel');
		if(typeof(parent_campaign_box) !== 'undefined'){
			params['parent_campaign_box'] = parent_campaign_box;
	    }
		var message_rows = jQuery(parent_campaign_box).find('.message-row').length;
		var message_id = jQuery(this).val();
		if( message_id == '' ) {
			jQuery(".ajax_chosen_select_messages").val('').trigger("chosen:updated");
			return;
		}

		jQuery('.message-edit-row').hide();
		jQuery('.message-title-text').show();
		jQuery('.message-title-input').hide();
		jQuery.ajax({
			type: 'POST',
			url: icegram_writepanel_params.ajax_url,
			dataType: 'json',
			data: {
				action: 'get_message_action_row',
				message_id: message_id,
				row: message_rows
			},
			success: function(response) {	
				message_rows++;
				//jQuery('.messages-list .messages_list_table tbody').append(response.main);
				jQuery(parent_campaign_box).find('.messages-list .messages_list_table tbody').append(response.main);
				jQuery('.color-field').wpColorPicker().each(function(index){
					var colorPicker_label = jQuery(this).data('color-label') || '';
					if(colorPicker_label !== ''){
						jQuery(this).closest('.wp-picker-container').find('a.wp-color-result').attr('title', colorPicker_label);
					}
				});
				//hide Colors container
				jQuery('.campaign_data, #message-settings').on('change', '.show_color_options' , function() {
				    jQuery(this).closest('p').next('.message_colors_options_container').toggle(!this.checked);
				}).change();
				
				display_message_themes(jQuery('#'+response.id));				
				jQuery(".ajax_chosen_select_messages").val('').trigger("chosen:updated");
				//add get more themes link
				jQuery('.campaign_data, #message-settings').find('.message_theme').append('<option value="ig_get_more_theme" class="ig_get_more">Get more Themes</option>');
				jQuery('.campaign_data, #message-settings').find('.message_animation').append('<option value="ig_get_more_animation" class="ig_get_more">Get more Animations</option>');
				jQuery("select.icegram_chosen_page").chosen({
					disable_search_threshold: 10
				});
				hide_empty_campaign_message();
				jQuery('.message-setting-fields').trigger('change');
				jQuery(".tips, .help_tip").tipTip({'attribute' : 'data-tip'});
				// text editor issue fix
				if ( typeof( tinyMCEPreInit.mceInit[ 'edit'+response.id ] ) === 'undefined' ) {
					for ( _prop in newSettings ) {
						if ( 'string' === typeof( newSettings[_prop] ) ) {
							if(_prop !== 'content_css'){
								newSettings[_prop] = newSettings[_prop].replace( new RegExp( 'content', 'g' ), 'edit'+response.id );
							}
						}
					}
					tinyMCEPreInit.mceInit[ 'edit'+response.id ] = newSettings;
				}
				if ( typeof( tinyMCEPreInit.qtInit[ 'edit'+response.id ] ) === 'undefined' ) {
					for ( _prop in newQTS ) {
						if ( 'string' === typeof( newQTS[_prop] ) ) {
							if(_prop !== 'content_css'){
								newQTS[_prop] = newQTS[_prop].replace( new RegExp( 'content', 'g' ), 'edit'+response.id );
							}
						}
					}
					tinyMCEPreInit.qtInit[ 'edit'+response.id ] = newQTS;
				}
				tinyMCE.init({ id : tinyMCEPreInit.mceInit[ 'edit'+response.id ]});
            	quicktags({id : 'edit'+response.id});
				QTags._buttonsInit();
				if(jQuery('#wp-edit'+response.id+'-wrap').hasClass('tmce-active')){
					jQuery('#edit'+response.id+'-tmce').click();
				}else{
					jQuery('#edit'+response.id+'-html').click();
				}
				jQuery( window ).trigger( "icegram_message_added_ajax" ,[params]);
			}
		});
	});
    //add local url
	jQuery('.campaign_target_rules').on('click', '#add_local_url_row' ,function(e) {
		e.preventDefault();
		var row = add_url_row();
		if(jQuery('.local_url').find('.url_input_field').length){
			jQuery(row).insertAfter(jQuery('.local_url').find('.url_input_field').last().parent('span'));
		}else{
			jQuery(row).insertBefore(jQuery('.local_url').find('#add_local_url_row_label'));
			
		}

	});
	jQuery('.campaign_target_rules').on('click', '.delete-url',function(e) {
		jQuery(this).parent().remove();
	});
	
	function add_url_row(){
		var row = '<span><label class="options_header"><span id="valid-field"> </span></label> <input  type="text" class="url_input_field" data-option="local_url"  name="campaign_target_rules[local_urls][]" value="'+home_url+'*"/><span class="delete-url"></span></span>';
		return row;
	}
	function hide_empty_campaign_message() {
		if( jQuery('.message-row').length == 0 ) {
			jQuery('.empty_campaign').show();
		} else {
			jQuery('.empty_campaign').hide();
		}
	}

	// jQuery('select.ajax_chosen_select_messages').next('div').on('click', 'div.chosen-drop', function() {
	// 	jQuery(this).closest('h3.handle').trigger('click');
	// });

	// jQuery('.campaign_data').on( 'click','.campaign_preview' ,function(event) {
	// jQuery('#ig-admin-tabs').on( 'click', '.campaign_preview', function(event) {
	jQuery(document).on( 'click', '.campaign_preview', function(e) {
		// jQuery(this).closest('h3.handle').trigger('click');
		// if( jQuery('.message-row').length == 0 )
		// 	return;
		// trigger event for saving visual content
		e.preventDefault();
		tinyMCE.triggerSave();
		params = jQuery("#post").serializeArray();
		params.push( {name: 'action', value: 'save_campaign_preview' });

		jQuery.ajax({
			type: 'POST',
			async: false,
			url: icegram_writepanel_params.ajax_url,
			data: params,
			success: function(response) {
				if (response != '') {
					window.open(response, 'preview_window');
				}
			}
		});
	});
	//add get more themes link
	jQuery('.campaign_data, #message-settings').find('.message_theme').append('<option value="ig_get_more_theme" class="ig_get_more">Get more themes</option>');
	jQuery('.campaign_data, #message-settings').find('.message_animation').append('<option value="ig_get_more_animation" class="ig_get_more">Get more Animations</option>');
	
	jQuery('.campaign_data, #message-settings').on('change' ,'.message_theme, .message_animation' ,function(){
		if(jQuery(this).val() == 'ig_get_more_theme'){
			window.open('https://www.icegram.com/product-category/themes-addons/?utm_source=icegram&utm_medium=admin&utm_campaign=theme_packs');
		}
		if(jQuery(this).val() == 'ig_get_more_animation'){
			window.open('https://www.icegram.com/animation-effects/?utm_source=icegram&utm_medium=admin&utm_campaign=animation_pack');
		}
	});
	jQuery("select.icegram_chosen_page").chosen({
		disable_search_threshold: 10
	});

	jQuery('input#users_logged_in, input#users_all ,input#users_not_logged_in').on('change', function() {
		if (jQuery(this).val() == 'logged_in') {
		    jQuery('select#users_roles').parent('p').show();
			jQuery('#users_roles_chosen').find('input').trigger('click');
		}else{
		    jQuery('select#users_roles').parent('p').hide();
		}
	});
	
	jQuery('.schedule_rule').on('change', function() {
		if (jQuery(this).attr('id') == "when_schedule") {
			jQuery('#date_picker').show();
		} else {
			jQuery('#date_picker').hide();
		}
	});

	jQuery('input#where_other_page').on('change', function() {
		jQuery('select#where_page_id').parent('p').slideToggle();
		if (jQuery(this).is(':checked')) {
			jQuery('#where_page_id_chosen').find('input').trigger('click');
		}
	});
	jQuery('input#where_sitewide').on('change', function() {
		jQuery('select#exclude_page_id').parent('p').slideToggle();
	});
	jQuery('input#where_local_url').on('change', function() {
		jQuery('.local_url').slideToggle();
	});

	jQuery('.date-picker').datepicker({
		dateFormat: 'yy-mm-dd',
		defaultDate: 0,
		showOtherMonths: true,
		selectOtherMonths: true,
		changeMonth: true,
		changeYear: true,
		showButtonPanel: false,
		beforeShow: function(input, inst) {
	       jQuery('#ui-datepicker-div').addClass('ig-date-picker');
	   }
	});
	
	jQuery('.campaign_target_rules').on('focusout', 'input.url_input_field', function() {
        var url = this;
		jQuery(url).parent().find('span#valid-field').removeClass('error');	
		if(jQuery(url).data("option") !== 'undefine' && jQuery(url).data("option") == 'local_url' && jQuery(url).val() != '*'){
			var url_val = url.value;
			if(url_val.indexOf(home_url) < 0){
				jQuery(url).val(home_url + url_val);	
				return;	
			}
		}
	});
});