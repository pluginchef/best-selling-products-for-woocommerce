<?php

if(!class_exists('WCBSP_Plugin_Settings')){
	
	class WCBSP_Plugin_Settings{
		
		public function __construct(){}
		
		public function wcbsp_update_plugin_settings(){
			
			if(isset($_POST['wcbsp_submit']) && $_POST['wcbsp_submit'] == 'submit'){
				
				// #1 Check Authorization
				if(!current_user_can('manage_options')){
					wp_die('You don\'t have permissions to make changes');
				}
				// #2 Check Authentication				
				if ( ! isset( $_POST['wcbsp_nonce'] ) 
				 || ! wp_verify_nonce( $_POST['wcbsp_nonce'], 'update_settings_process' ) 
				) {
				   wp_die('You don\'t have permissions to make changes');
				   
				} else {
					
					// #3 Sanitize Posted Values & Save Settings
					$plugin_settings = array();
					if(isset($_POST['best_seller_shop_text']))
					$plugin_settings['best_seller_shop_text'] = sanitize_text_field($_POST['best_seller_shop_text']);
					if(isset($_POST['top_seller_heading_on_top']))
					$plugin_settings['top_seller_heading_on_top'] = wp_kses_post($_POST['top_seller_heading_on_top']);
					if(isset($_POST['display_top_seller_on_bottom']))
					$plugin_settings['display_top_seller_on_bottom'] = sanitize_text_field($_POST['display_top_seller_on_bottom']);
					if(isset($_POST['top_best_seller_quantity']))
					$plugin_settings['top_best_seller_quantity'] = sanitize_text_field($_POST['top_best_seller_quantity']);
					if(isset($_POST['best_seller_product_text']))
					$plugin_settings['best_seller_product_text'] = sanitize_text_field($_POST['best_seller_product_text']);
					if(isset($_POST['best_seller_category_text']))
					$plugin_settings['best_seller_category_text'] = sanitize_text_field($_POST['best_seller_category_text']);
					if(isset($_POST['badge_bg_color']))
					$plugin_settings['badge_bg_color'] = sanitize_text_field($_POST['badge_bg_color']);
					if(isset($_POST['badge_text_color']))
					$plugin_settings['badge_text_color'] = sanitize_text_field($_POST['badge_text_color']);
					if(isset($_POST['show_on_product_single_image']))
					$plugin_settings['show_on_product_single_image'] = sanitize_text_field($_POST['show_on_product_single_image']);
					if(isset($_POST['display_top_seller_on_top']))
					$plugin_settings['display_top_seller_on_top'] = sanitize_text_field($_POST['display_top_seller_on_top']);
					if(isset($_POST['top_seller_heading_on_bottom']))
					$plugin_settings['top_seller_heading_on_bottom'] = wp_kses_post($_POST['top_seller_heading_on_bottom']);
					if(isset($_POST['show_on_product_shop_image']))
					$plugin_settings['show_on_product_shop_image'] = sanitize_text_field($_POST['show_on_product_shop_image']);
					if(isset($_POST['show_on_product_archive_image']))
					$plugin_settings['show_on_product_archive_image'] = sanitize_text_field($_POST['show_on_product_archive_image']);
					if(isset($_POST['show_on_product_single_content']))
					$plugin_settings['show_on_product_single_content'] = sanitize_text_field($_POST['show_on_product_single_content']);
					if(isset($_POST['text_for_single_before_content']))
					$plugin_settings['text_for_single_before_content'] = sanitize_text_field($_POST['text_for_single_before_content']);
					if(isset($_POST['text_for_single_before_content']))
					$plugin_settings['text_for_single_before_content'] = sanitize_text_field($_POST['text_for_single_before_content']);
					if(isset($_POST['display_best_in_category_product']))
					$plugin_settings['display_best_in_category_product'] = sanitize_text_field($_POST['display_best_in_category_product']);
					if(isset($_POST['text_best_selling_category']))
					$plugin_settings['text_best_selling_category'] = sanitize_text_field($_POST['text_best_selling_category']);
					if(isset($_POST['display_list_best_seller']))
					$plugin_settings['display_list_best_seller'] = sanitize_textarea_field($_POST['display_list_best_seller']);
					if(isset($_POST['image_design']))
					$plugin_settings['image_design'] = sanitize_textarea_field($_POST['image_design']);
					if(isset($_POST['no_of_best_sellers']))
					$plugin_settings['no_of_best_sellers'] = sanitize_textarea_field($_POST['no_of_best_sellers']);
					if(isset($_POST['best_seller_text_rankwise']))
					$plugin_settings['best_seller_text_rankwise'] = sanitize_textarea_field($_POST['best_seller_text_rankwise']);
					if(isset($_POST['same_category_best_sellers_heading']))
					$plugin_settings['same_category_best_sellers_heading'] = sanitize_textarea_field($_POST['same_category_best_sellers_heading']);
					update_option('wcbsp_plugin_settings',$plugin_settings);
					return true;				  
				   	
				}
				
				
			}
			
		}
		
	}
	
}
