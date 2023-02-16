<?php

$best_seller_shop_text = ( isset($this->dbSettings['best_seller_shop_text']) && !empty($this->dbSettings['best_seller_shop_text']) ) ? $this->dbSettings['best_seller_shop_text'] : esc_html__('Best Seller In {categories}', 'wcbsp-best-selling-products');

			
$badge_bg_color = (isset($this->dbSettings['badge_bg_color']) && !empty($this->dbSettings['badge_bg_color'])) ? $this->dbSettings['badge_bg_color'] : '#41b108';

$badge_text_color = (isset($this->dbSettings['badge_text_color']) && !empty($this->dbSettings['badge_text_color'])) ? $this->dbSettings['badge_text_color'] : '#ffffff';

$show_on_product_shop_image  = (isset($this->dbSettings['show_on_product_shop_image']) && $this->dbSettings['show_on_product_shop_image'] == 'yes' ) ? $this->dbSettings['show_on_product_shop_image'] : '';

$image_design = (isset($this->dbSettings['image_design']) && !empty($this->dbSettings['image_design']) ) ? $this->dbSettings['image_design'] : 'three';

$wcbsp_custom_css = (isset($this->dbSettings['wcbsp_custom_css']) && !empty($this->dbSettings['wcbsp_custom_css']) ) ? $this->dbSettings['wcbsp_custom_css'] : '';

?>
<div class="pc-page-header">	
	<h1 class="product_name"><?php esc_html_e('WooCommerce Best Selling Products Lite','wcbsp-best-selling-products'); ?></h1>	
</div>

<form class="wpm-form pc-form wbsp-form" method="POST" action="<?php echo esc_url( admin_url('admin.php?page=wcbsp_settings') ); ?>">
  
  <?php 
  if($this->settingsSaved){ ?>
	<div class="form-group row"> 
		
		  <div class="alert alert-success settings_saved_msg" style="width:100%;"role="alert">
			<?php esc_html_e('Plugin settings were saved successfully.','wcbsp-best-selling-products'); ?>
		  </div>  
		
	</div>
	
  <?php } ?>
  
  <div class="form-group row">
	 <div class="col-md-12 pc-group">
		<?php esc_html_e('Best Seller Badge Settings','wcbsp-best-selling-products'); ?>
	</div>
  </div>

  <div class="form-group row">
	<label for="badge_bg_color" class="col-md-3 col-form-label"><?php esc_html_e('Badge Background Color','wcbsp-best-selling-products'); ?></label>
	<div class="col-md-6">
	  <input type="text" class="form-control cpa-color-picker" id="badge_bg_color" name="badge_bg_color" placeholder="Enter text for best seller badge on category archive page." value="<?php echo esc_attr($badge_bg_color); ?>"> 
	  <p><?php esc_html_e('Choose background color for badge.','wcbsp-best-selling-products'); ?></p>
	</div>
  </div>
  
  <div class="form-group row">
	<label for="badge_text_color" class="col-md-3 col-form-label"><?php esc_html_e('Badge Text Color','wcbsp-best-selling-products'); ?></label>
	<div class="col-md-6">
	  <input type="text" class="form-control cpa-color-picker" id="badge_text_color" name="badge_text_color" placeholder="Choose text color for badge." value="<?php echo esc_attr($badge_text_color); ?>"> 
	  <p><?php esc_html_e('Choose text color for badge.','wcbsp-best-selling-products'); ?></p>
	</div>
  </div>
  
    <div class="form-group row">
	<div class="col-md-3"><?php esc_html_e('Badge Design','wcbsp-best-selling-products'); ?></div>
	<div class="col-md-9">
	   <div class="custom-control custom-radio cod_designs_listing">
		   
		  <div class="block">
		  <input type="radio" class="custom-control-input" value="one" id="one" name="image_design" <?php checked( esc_attr( $image_design ) , 'one' ); ?>>
		  <label class="custom-control-label" for="one"><img src="<?php echo esc_url($this->pluginUrl.'assets/images/design_one.png'); ?>"></label>
		  </div> 
		  
		  
		  <div class="block">
		  <input type="radio" class="custom-control-input" value="two" id="two" name="image_design" <?php checked( esc_attr( $image_design ), 'two' ); ?> >
		  <label class="custom-control-label" for="two"><img src="<?php echo esc_url($this->pluginUrl.'assets/images/design_two.png'); ?>"></label>
		  </div>
		  
		  <div class="block">
		  <input type="radio" class="custom-control-input" value="three" id="three" name="image_design"<?php checked( esc_attr( $image_design ), 'three' ); ?> >
		  <label class="custom-control-label" for="three"><img src="<?php echo esc_url($this->pluginUrl.'assets/images/design_three.png'); ?>"></label>
		  </div>
		  
		</div>
		
	  </div>
	
  </div>
  
  <div class="form-group row">
	 <div class="col-md-12 pc-group">
		<?php esc_html_e('Shop Page Settings','wcbsp-best-selling-products'); ?>
	</div>
  </div>
  
   <div class="form-group row">
	<div class="col-md-3">&nbsp;</div>
	<div class="col-md-9">
	   <div class="custom-control custom-checkbox">
		<input type="checkbox" class="custom-control-input" id="show_on_product_shop_image" name="show_on_product_shop_image" value="yes" <?php checked( esc_attr( $show_on_product_shop_image ), 'yes' ); ?> >
		<label class="custom-control-label" for="show_on_product_shop_image"><p><?php esc_html_e('Display best seller badge on the product image of best selling products of different categories on the shop page.','wcbsp-best-selling-products'); ?></p></label>
	  </div>
	</div>
  </div>
  	
   <div class="form-group row">
	<label for="best_seller_shop_text" class="col-md-3 col-form-label"><?php esc_html_e('Badge Text For Best Seller\'s Product Image','wcbsp-best-selling-products'); ?></label>
	<div class="col-md-6">
	  <input type="text" class="form-control" id="best_seller_shop_text" name="best_seller_shop_text" placeholder="Enter text for displaying COD available." value= "<?php echo esc_attr($best_seller_shop_text); ?>" >
	  <p><?php esc_html_e('Enter text to display on the badge of best selling products of different categories on the shop page. You an use {categories} to display categories.','wcbsp-best-selling-products'); ?></p>
	</div>
  </div>

  
   <div class="form-group row">
	 <div class="col-md-12 pc-group">
		<?php esc_html_e('Additional Settings','wcbsp-best-selling-products'); ?>
	</div>
  </div>
  
  <!-- Nonce creation within form -->
  <?php wp_nonce_field( 'update_settings_process', 'wcbsp_nonce' ); ?>
  
  <div class="form-group row">
	<div class="col-md-12">
	  <button type="submit" class="btn btn-primary pc-form-submit" id="wcbsp_submit" name="wcbsp_submit" value="submit"><?php esc_html_e('Save Settings','wcbsp-best-selling-products'); ?></button>
	</div>
  </div>
</form>
