<?php
/**
Plugin Name: Best Selling Products For WooCommerce
Plugin URI: https://www.pluginchef.com
Description: A free WooCommerce plugin that highlights the best selling products of your store on shop page with help of beautiful and customisable badges.
Author: pluginchef
Version: 1.0.0
Text Domain: wcbsp-best-selling-products
Domain Path: /lang/
*/

if(!class_exists('WCBSP_Best_Selling_Products')){


	class WCBSP_Best_Selling_Products{

		private $pluginUrl;
		private $pluginDir;
		private $dbSettings;
		private $bestInSameCategory;
		private $bestSellingProductsInAllCategory = array();
		private $currentProductRank = '';
		private $currentProductId = '';
		private $settingsSaved = false;
		private $bestSellingData = array();
		private $inCategory = '';
		private $all_product_categories = array();
		private $initial_products_fetched = array();
		
		public function __construct(){
		
			$wooInstalled = in_array( 'woocommerce/woocommerce.php', get_option( 'active_plugins' ) );
			if ( ! function_exists( 'is_plugin_active_for_network' ) ) {
				require_once( ABSPATH . '/wp-admin/includes/plugin.php' );
			}
			$networkActive = ( is_plugin_active_for_network( 'woocommerce/woocommerce.php' ) ) ? true : false;
			$is_woocommerce_not_installed = ( ! $wooInstalled && ! $networkActive ) ? true : false;
			if ( $is_woocommerce_not_installed ) {
				 add_action( 'admin_notices', array( $this, 'wcbsp_woocommerce_missing' ) );
			} else {

				$this->pluginUrl =  plugin_dir_url( __FILE__ );
				$this->pluginDir =  plugin_dir_path( __FILE__ );
				$this->dbSettings = maybe_unserialize( get_option( 'wcbsp_plugin_settings' ) );
				$this->wcbsp_register_plugin_hooks();
			
			}

		}

		
		
		function wcbsp_register_plugin_hooks(){

			add_action( 'init',               array($this,'wcbsp_plugin_initialise'));
			add_action( 'wp_enqueue_scripts', array($this,'wcbsp_enqueue_scripts') );
			add_action( 'wp_head',            array($this,'wcbsp_hook_frontend_head'));
			
			if(!is_admin()) {
				
			   add_filter( 'woocommerce_product_get_image',array($this,'wcbsp_wrap_product_image_loop'), 10, 6 );	
		    }

			add_action( 'plugins_loaded',                  array( $this, 'wcbsp_load_plugin_languages' ) );
			
			if(is_admin()){
				
				add_action( 'admin_init',                  array($this, 'wcbsp_update_settings') );
				add_action( 'admin_menu',                  array($this, 'wcbsp_register_settings') );
				add_action( 'product_cat_add_form_fields', array($this, 'wcbsp_product_term_badge_field') , 10, 2 );
				add_action( 'product_cat_edit_form_fields',array($this, 'wcbsp_product_term_edit_badge_field'), 10, 2 );
				add_action( 'edited_product_cat',          array($this, 'wcbsp_save_taxonomy_custom_meta'), 10, 2 );  
				add_action( 'create_product_cat',          array($this, 'wcbsp_save_taxonomy_custom_meta'), 10, 2 );
				add_action( 'admin_enqueue_scripts',       array($this, 'wcbsp_badge_field_initialize') );
				add_filter( 'plugin_action_links_'. plugin_basename(__FILE__), array($this, 'wcbsp_go_pro_link'), 10, 1 );
				
			}
			
		}
		
		function wcbsp_save_taxonomy_custom_meta( $term_id ) {
			
			$category_badge_color = filter_input(INPUT_POST, 'category_badge_color');
			update_term_meta($term_id, 'category_badge_color', sanitize_text_field($category_badge_color));
			$category_badge_text_color = filter_input(INPUT_POST, 'category_badge_text_color');
			update_term_meta($term_id, 'category_badge_text_color',sanitize_text_field($category_badge_text_color));
			
			
		} 
		
		function wcbsp_get_product_categories_name( $term_id ){
			
			 $term = get_term( $term_id, 'product_cat' );
			 return esc_html($term->name);
			     
		}
		
		function wcbsp_get_product_categories_link( $term_id ){
			
			 $this->inCategory = $term_id;
			 $term = get_term( $term_id, 'product_cat' );
			 $terms_link = '<a href="'.get_term_link($term->term_id).'" target="_blank">'.esc_html($term->name).'</a>';
			 return wp_kses_post( $terms_link );
			     
		}
		
		function wcbsp_get_ribbon_badge($text){
			
			return wp_kses_post('<div class="bsbtext-wrapper"><div class="bsbtext">'.esc_html($text).'</div></div>');
			
		}
		
		function wcbsp_plugin_initialise(){
			
			$this->dbSettings = maybe_unserialize( get_option( 'wcbsp_plugin_settings' ) );
			$this->wcbsp_get_best_selling_products();
			
		}
		
		function wcbsp_get_best_seller_category_for_product( $product_id ){
			
			$term_id = '';
			$categories = get_the_terms( $product_id, 'product_cat');
			if(count($categories) == 1){
				 $term_id = $categories[0]->term_id;
			}
			else{
				
				if(!empty($categories)) {
					
					foreach($categories as $category){
						
						$args = array();	
						$total_sales = get_post_meta( $product_id , 'total_sales', true);
						$visibility = get_term_by('slug', 'exclude-from-catalog', 'product_visibility');
						$product_category = array($category->slug);
						$args['post_type'] = 'product';
						$args['post_status'] = 'publish';
						$args['post__not_in'] = array($product_id);
						$args['ignore_sticky_posts'] = 1;
						$args['no_found_rows'] = 1;
						$args['orderby'] = 'meta_value_num';
						$args['order'] = 'DESC';
						$args['posts_per_page'] = -1;
						$args['tax_query'] = array();
						$args['meta_query'] = array();
						$args['tax_query'][] = array
									(
										'taxonomy' => 'product_visibility',
										'field' => 'term_taxonomy_id',
										'terms' => array($visibility->term_id),
										'operator' => 'NOT IN'
									);
						$args['tax_query'][] = array
									(
										'taxonomy' => 'product_cat',
										'terms' => $product_category,
										'field' => 'slug',
										'operator' => 'IN',
										'include_children' => '1'
									);
										 
						$args['meta_query'][] = array
											(
											'key'     => 'total_sales',
											'value'   => $total_sales,
											'type'    => 'numeric',
											'compare' => '>='
											);
											
						$products = new WP_Query($args);
						if($products->post_count == 0){
							$term_id = $category->term_id;
						}
						wp_reset_postdata();
						
						if(!empty($term_id)){	break;	}
				
					}
				
			  }
				
			}
			
			return $term_id;
				
		}
		
		function wcbsp_get_best_selling_products(){
				
			$args = array();
			$visibility = get_term_by('slug', 'exclude-from-catalog', 'product_visibility');
			$categories = get_terms( ['taxonomy' => 'product_cat'] );
			
			if(!empty($categories)) {
				
				foreach($categories as $term){
					
					if ( count( get_term_children( $term->term_id, 'product_cat' ) ) > 0 ) { continue; }
										
					$this->initial_products_fetched = array_merge( $this->initial_products_fetched, get_posts( array( 
						'posts_per_page' => 1, 
						'post_type' => 'product',
						'fields' => 'ids',
						'orderby' => 'meta_value_num',
						'order' => 'DESC',
						'meta_key' => 'total_sales', 
						'tax_query' => array( array( 'taxonomy' => $term->taxonomy, 'field' => 'term_id', 'terms' => $term->term_id ) ),
						'meta_query' => array( array( 'key' => 'total_sales', 'value' => 0,'type' => 'numeric',	'compare' => '>' ) ) ) ) 
					);
					
					$this->all_product_categories[] = $term->term_id;
			
				}
			
			}
			
			$this->initial_products_fetched = array_unique($this->initial_products_fetched);
			if( !empty($this->initial_products_fetched ) ) {
				
				foreach($this->initial_products_fetched as $id){
					
					$bs_cat = $this->wcbsp_get_best_seller_category_for_product($id);
					$this->bestSellingData[$bs_cat] = $id;
					
				}
			
			}
			
			$initial_fetched_for = array_keys($this->bestSellingData);
			$result = array_diff( $this->all_product_categories, $initial_fetched_for );
			
			if(count($result) > 0){
				
				$remaining_post_ids = array();
				
				foreach($result as $term){
										
				    $newargs = array( 
						'posts_per_page' => 1, 
						'post_type' => 'product',
						'fields' => 'ids',
						'orderby' => 'meta_value_num',
						'order' => 'DESC',
						'meta_key' => 'total_sales', 
						'post__not_in' => $this->initial_products_fetched,
						'tax_query' => array( array( 'taxonomy' => 'product_cat', 'field' => 'term_id', 'terms' => $term ) ),
						'meta_query' => array( array( 'key' => 'total_sales', 'value' => 0,'type' => 'numeric',	'compare' => '>' ) ) );
												
					$remaining_post_ids = array_merge( $remaining_post_ids, get_posts( $newargs ) );
					$this->initial_products_fetched = array_merge( $this->initial_products_fetched, $remaining_post_ids );
					
					if(!empty($remaining_post_ids)) {
						foreach($remaining_post_ids as $id){
					
							$bs_cat = $this->wcbsp_get_best_seller_category_for_product($id);
							$this->bestSellingData[$bs_cat] = $id;
							
						}
					}
					
				}
				
				
			}
			
			$args['post_type'] = 'product';
			$args['post_status'] = 'publish';
			$args['ignore_sticky_posts'] = 1;
			$args['no_found_rows'] = 1;
			$args['post__in'] = $this->initial_products_fetched;
            $args['orderby'] = 'post__in';
			$args['posts_per_page'] = -1;
			$args = apply_filters('wcbsp_best_seller_each_category_args',$args);
			$result = new WP_Query( $args );
			$bestSellingPBC = array();
		    if($result->have_posts()) :
				while($result->have_posts()) : $result->the_post();
				    
				    $best_selling_in = get_term_by('id', get_the_ID(), 'product_cat');
				    $bestSellingPBC[] = get_the_ID();
				    
				endwhile;
				wp_reset_postdata();
			endif;
			$this->bestSellingProductsInAllCategory = $bestSellingPBC;

			
		}
		
		function wcbsp_wrap_product_image_loop( $image, $object, $size, $attr, $placeholder, $imagenew ){
			
			$enable_shop_page = (isset($this->dbSettings['show_on_product_shop_image']) && $this->dbSettings['show_on_product_shop_image'] == 'yes') ? true : false;
						
			$enable_archive_page = (isset($this->dbSettings['show_on_product_archive_image']) && $this->dbSettings['show_on_product_archive_image'] == 'yes') ? true : false;
			
			if(is_product_category() && $enable_archive_page){
				
				$total_sales = get_post_meta( get_the_ID(), 'total_sales', true);
				$badgeLabel = $this->dbSettings['best_seller_category_text'];
				if( in_array($object->get_id(),$this->bestSellingProductsInAllCategory) && $total_sales > 0 ) {
				
				$term_id = array_search( $object->get_id(), $this->bestSellingData );
				$terms_names = $this->wcbsp_get_product_categories_name($term_id);
				$badge = $this->wcbsp_get_best_selling_design($image,'category-archive',$badgeLabel);
				$badge = str_replace('{categories}',$terms_names, $badge );
				return wp_kses_post($badge);
				
				}
				
			}else if( is_shop() && $enable_shop_page){ 
				$badgeLabel = $this->dbSettings['best_seller_shop_text'];
				$total_sales = get_post_meta( get_the_ID(), 'total_sales', true);
				if( in_array($object->get_id(),$this->bestSellingProductsInAllCategory) && $total_sales > 0){
					
					$term_id = array_search( $object->get_id(), $this->bestSellingData );
					$terms_names = $this->wcbsp_get_product_categories_name($term_id);
					$badge = $this->wcbsp_get_best_selling_design($image,'shop',$badgeLabel);
					$badge = str_replace('{categories}',$terms_names, $badge );
					return wp_kses_post($badge);
				}
			}else if( is_product() ){ 
				
				$total_sales = get_post_meta( get_the_ID(), 'total_sales', true);
				if( $total_sales > 0) {
					
					$badgeLabel = $this->dbSettings['best_seller_text_rankwise'];
					if( in_array($this->currentProductId,$this->bestSellingData) && !empty($this->bestInSameCategory) ){
						$rank = array_search($object->get_id(),$this->bestInSameCategory) + 2;
					}else{
						
						if(!empty($this->bestInSameCategory) && array_search($object->get_id(),$this->bestInSameCategory) < $this->currentProductRank) { 
							$rank = array_search($object->get_id(),$this->bestInSameCategory) + 1;
						}
						else {
						   if(!empty($this->bestInSameCategory))	
						   $rank = array_search($object->get_id(),$this->bestInSameCategory) + 2;
						}	
						
					}
					$term_id = array_search( $object->get_id(), $this->bestSellingData );	
					$terms_names = $this->wcbsp_get_product_categories_name($term_id);
					$badge = $this->wcbsp_get_best_selling_design($image,'single',$badgeLabel);
					$badge = str_replace('{rank}',$rank, $badge );
					$badge = str_replace('{categories}',$terms_names, $badge );
					return wp_kses_post($badge);
				
				}else{
					return wp_kses_post($image);
				}
				
			}
			
			return wp_kses_post($image);
			
		}
		
		
		function wcbsp_register_settings(){
		    

		    global $submenu;

		    $menu = add_menu_page( 
		        esc_html__( 'WC Best Selling Products', 'wcbsp-best-selling-products' ),
		        esc_html__( 'WC Best Selling Products', 'wcbsp-best-selling-products' ),
		        'manage_options',
		        'wcbsp_settings',
		        array($this,'wcbsp_custom_menu_page'),
		        esc_url($this->pluginUrl.'assets/images/logo.png')
		    ); 

			add_action( 'load-' . $menu, array($this,'wcbsp_load_admin_resources') );

		    add_submenu_page( 'wcbsp_settings', 'WC Best Selling Products', '<span class="fc-fire-sale"><img draggable="false" role="img" class="emoji" alt="🔥" src="https://s.w.org/images/core/emoji/14.0.0/svg/1f525.svg"></span>'.esc_html__(' Go Pro', 'wcbsp-best-selling-products'), 'manage_options', 'wbsp-go-pro', array($this,'wcbsp_go_pro'));
	

		}

		function wcbsp_go_pro() {

			 $go_pro_link = 'https://codecanyon.net/item/woocommerce-best-selling-products/27901017';
			 wp_redirect( $go_pro_link ); 
  			 exit;

		}
		
		function wcbsp_enqueue_scripts(){
		
		   wp_enqueue_style( 'wcbsp-style',  plugin_dir_url( __FILE__ ) . 'assets/css/wcbsp-style.css',array(),time() );
			
		}
		
		function wcbsp_load_admin_resources(){ 
			
			add_action( 'admin_enqueue_scripts', array($this,'wcbsp_enqueue_admin_assets') );
			
		}

		function wcbsp_enqueue_admin_assets(){

			wp_enqueue_style(  'wp-color-picker' );
			wp_enqueue_style(  'wcbsp-responsive-style', plugin_dir_url( __FILE__ ). 'assets/css/bootstrap.css' );
			wp_enqueue_style(  'wcbsp-backend-style',    plugin_dir_url( __FILE__ ). 'assets/css/wcbsp-backend.css' );
			wp_enqueue_script( 'wcbsp-custom-script',    plugin_dir_url( __FILE__) . 'assets/js/wcbsp-script.js', array( 'jquery', 'wp-color-picker' ), '', true  );			
       		
   		 }

		function wcbsp_custom_menu_page(){

			$this->dbSettings = maybe_unserialize( get_option( 'wcbsp_plugin_settings' ) );
			require_once( $this->pluginDir.'inc/view.settings.php' );
			
		}
		
		function wcbsp_load_plugin_languages() {
			
			load_plugin_textdomain( 'wcbsp-best-selling-products', false, basename( dirname( __FILE__ ) ) . '/lang/' );
			
		}
		
		function wcbsp_hook_frontend_head(){
			
			$style = '';
			$custom_css = '';
			if( is_product() || is_shop() || is_product_category() ){
			
			   $common = '';
			   if(!empty($this->dbSettings['badge_bg_color'])){
				   $common .= '.bsbtext,.notify-badge{background:'.esc_html($this->dbSettings['badge_bg_color']).';}';	
			   }
			   if(!empty($this->dbSettings['badge_text_color'])){
				 $common .= '.bsbtext,.notify-badge{color:'.esc_html($this->dbSettings['badge_text_color']).';}';	
			   }
			   $style = '<style>'.$custom_css.$common.'</style>';
			   echo wp_kses( $style, array('style' => array()) ); 
			   
			}
				
			if(is_product()){ $this->wcbsp_check_if_product_best_seller(); }	
		}
		
		function wcbsp_check_if_product_best_seller(){
			
			$args = array();	
			$terms = get_the_terms( get_the_ID() , 'product_cat' );
			$total_sales = get_post_meta( get_the_ID(), 'total_sales', true);
			$visibility = get_term_by('slug', 'exclude-from-catalog', 'product_visibility');
			$product_category = array($terms[0]->slug);
			$args['post_type'] = 'product';
			$args['post_status'] = 'publish';
			$args['post__not_in'] = array(get_the_ID());
			$args['ignore_sticky_posts'] = 1;
			$args['no_found_rows'] = 1;
			$args['orderby'] = 'meta_value_num';
			$args['order'] = 'DESC';
			$args['posts_per_page'] = -1;
			$args['tax_query'] = array();
			$args['meta_query'] = array();
			$args['tax_query'][] = array
						(
							'taxonomy' => 'product_visibility',
							'field' => 'term_taxonomy_id',
							'terms' => array($visibility->term_id),
							'operator' => 'NOT IN'
						);
			$args['tax_query'][] = array
						(
							'taxonomy' => 'product_cat',
							'terms' => $product_category,
							'field' => 'slug',
							'operator' => 'IN',
							'include_children' => '1'
						);
							 
			$args['meta_query'][] = array
								(
								'key'     => 'total_sales',
								'value'   => $total_sales,
								'type'    => 'numeric',
								'compare' => '>='
								);
								
			$products = new WP_Query($args);
			$this->currentProductRank = $products->post_count;
			wp_reset_postdata();
			
		}
		
		function wcbsp_update_settings(){
			
			require_once( $this->pluginDir.'inc/modal.settings.php' );
			if( class_exists('WCBSP_Plugin_Settings') ){
				$settingsObj = new WCBSP_Plugin_Settings();
				$this->settingsSaved = $settingsObj->wcbsp_update_plugin_settings();
			}
		}
		
		function wcbsp_get_best_selling_design( $image,$context,$badgeLabel){
					
			$term_id = (!empty($this->inCategory)) ? $this->inCategory : $this->wcbsp_get_best_seller_category_for_product(get_the_ID());	
			$category_badge_color = get_term_meta($term_id, 'category_badge_color', true);
			$category_badge_text_color = get_term_meta($term_id, 'category_badge_text_color', true);
			$default_bg = ''; $default_color = '';
			$default_bg = (!empty($this->dbSettings['badge_bg_color'])) ? $this->dbSettings['badge_bg_color'] : '#4abc03';
			$color = (!empty($this->dbSettings['badge_text_color'])) ? $this->dbSettings['badge_text_color'] : '#ffffff';
		    $bgcolor = (!empty($category_badge_color)) ? $category_badge_color : $default_bg;
			$color = (!empty($category_badge_text_color)) ? $category_badge_text_color : $color;
			$badgeLabel = (!empty($badgeLabel)) ? $badgeLabel : esc_html__('Best Seller','wcbsp-best-selling-products');
			return wp_kses_post('<div class="item '.esc_attr($context).' design-'.esc_attr($this->dbSettings['image_design']).'"><span class="notify-badge" style="background-color:'.esc_attr($bgcolor).';color:'.esc_attr($color).';">'.esc_html($badgeLabel).'</span>'.wp_kses_post($image).'</div>');
			
		}
		
		function wcbsp_woocommerce_missing() {
			?>
			<div class="notice notice-error">
				<p><?php esc_html_e( 'WooCommerce is required for WC Best Selling Products plugin to work. Please install and configure latest WooCommerce plugin first.', 'wcbsp-best-selling-products' ); ?>
				</p>
			</div>
			<?php
		}
		
		function wcbsp_product_term_edit_badge_field($term){
				
				$term_id = $term->term_id;
				$category_badge_color = get_term_meta($term_id, 'category_badge_color', true);
				$category_badge_text_color = get_term_meta($term_id, 'category_badge_text_color', true);
				
				?>
				<tr class="form-field">
					<th scope="row" valign="top"><label for="category_badge_color"><?php esc_html_e('Choose Best Seller Badge Color', 'wcbsp-best-selling-products'); ?></label></th>
					<td>
						<input type="text" name="category_badge_color" id="category_badge_color" class="cpa-color-picker" value="<?php echo esc_attr($category_badge_color); ?>">
						<p class="description"><?php esc_html_e('Please choose best selling badge color for product of this category.', 'wcbsp-best-selling-products'); ?></p>
					</td>
				</tr>
				<tr class="form-field">
					<th scope="row" valign="top"><label for="category_badge_text_color"><?php esc_html_e('Choose Best Seller Badge Text Color', 'wcbsp-best-selling-products'); ?></label></th>
					<td>
						<input type="text" name="category_badge_text_color" id="category_badge_text_color" class="cpa-color-picker" value="<?php echo esc_attr($category_badge_text_color); ?>">
						<p class="description"><?php esc_html_e('Please choose best selling badge text color for product of this category.', 'wcbsp-best-selling-products'); ?></p>
					</td>
				</tr>
				
				<?php
			
		}

		function wcbsp_go_pro_link($actions) {
			
			
			$actions['settings'] = '<a href="' . admin_url( 'admin.php?page=wcbsp_settings' ) . '">'.esc_html__( 'Settings', 'wcbsp-best-selling-products' ).'</a>';
			$actions['go_pro'] = '<a style="color:#2ea100;" target = "_blank" href="https://codecanyon.net/item/woocommerce-best-selling-products/27901017">'.esc_html__( 'Pro Version', 'wcbsp-best-selling-products' ).'</a>';
			
			return $actions;
		}

		
		
		function wcbsp_badge_field_initialize($hook_suffix){
			
			if($hook_suffix == 'edit-tags.php' || $hook_suffix == 'term.php'){
				
				wp_enqueue_style( 'wp-color-picker' );
				wp_enqueue_script( 'wcbsp-backend-script', plugin_dir_url( __FILE__) . 'assets/js/wcbsp-script.js', array( 'jquery', 'wp-color-picker' ), '', true  );
				
			}
		}
	
	    function wcbsp_product_term_badge_field(){
			
			?>
			<div class="form-field">
				<label for="category_badge_color"><?php esc_html_e('Choose Best Seller Badge Color', 'wcbsp-best-selling-products'); ?></label>
				<input type="text" name="category_badge_color" id="category_badge_color" class="cpa-color-picker" value="#4abc03">
				<p class="description"><?php esc_html_e('Please choose the badge color for best selling product of this category.', 'wcbsp-best-selling-products'); ?></p>
			</div>
			
			<div class="form-field">
				<label for="category_badge_text_color"><?php esc_html_e('Choose Best Seller Badge Text Color', 'wcbsp-best-selling-products'); ?></label>
				<input type="text" name="category_badge_text_color" id="category_badge_text_color" class="cpa-color-picker" value="#ffffff">
				<p class="description"><?php esc_html_e('Please choose the badge text color for the best selling product of this category.', 'wcbsp-best-selling-products'); ?></p>
			</div>
			<?php

		}

	}

	return new WCBSP_Best_Selling_Products();

}
