<?php
  if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

  $wp_brand_center = new WP_Brand_Center_Connector();
  // Save access details
  if ( isset( $_POST["submit"] ) ) {

    //nonce checking
    if ( !isset($_POST['brand_asset_auth_checking'])) die("<br><br>Hmm .. unauthorized access!" );
    if ( !wp_verify_nonce($_POST['brand_asset_auth_checking'],'brand-asset-auth-checking')) die("<br><br>unauthorized access!" );

    $form_data = array(
        'brandcenter_url' => esc_url_raw($_POST['url']),
        'username' => sanitize_text_field($_POST['brand_connector_name']),
        'password' => sanitize_text_field($_POST['brand_connector_password'])
    );
    
    $wp_brand_center->wpb_save_data($form_data);

    $error_code = get_option('brandcenter_error_auth');
    $xmarkImg = plugins_url('../images/xmark.png', __FILE__);
    if($error_code == 1){ $mark = '<img src="'.$xmarkImg.'" width="15px" height="15px">'; }
  }

  if ( isset( $_POST["clear"] ) ) {
      $wp_brand_center->wpa_uninstall();
  }
  $markImg = plugins_url('../images/serve-me-right-logo.png', __FILE__);
  if( !empty(get_option('brandcenter_url')) ){ $mark = '<img src="'.$markImg.'" width="20px" height="20px">'; }
?>
<div class="container">
	<div class="row">        
    <div class="col-md-12">
      <form method="post" class="form-horizontal" id="settings-form" action="<?php echo esc_url( $_SERVER['REQUEST_URI'] ); ?>" autocomplete="off">
        <fieldset>
          <h3>Configuration</h3><br>    
          <!-- Name input-->
          <div class="form-group" style="margin-left: 10px;">
            <label class="col-md-3" for="name">Enter your Brandcenter URL:</label>
            <div class="col-md-9">
              <input id="url" name="url" type="url" class="form-control input-style" value="<?php echo get_option('brandcenter_url'); ?>" placeholder="your-brandcenter-url" autocomplete="off" required>
              <span class="domain small_bottom_margin" style="font-weight:bold;font-size: 20px;">/a/login</span>
              <?php echo $mark; ?>                
            </div>
          </div>
          <h3>Authentication</h3><br>
          <!-- Email input-->
          <div class="form-group" style="margin-left: 10px;">
            <label class="col-md-3" for="email">Enter your Username:</label>
            <div class="col-md-9">
              <input id="brand_connector_name" name="brand_connector_name" type="text" value="<?php echo get_option('brandcenter_user'); ?>" class="form-control input-style" autocomplete="off" required>
              <?php echo $mark; ?>
            </div>
          </div>
  		    <div class="form-group" style="margin-left: 10px;">
            <label class="col-md-3" for="email">Enter your Password:</label>
            <div class="col-md-9">
              <input id="pwd" name="brand_connector_password" type="password" value="<?php echo get_option('brandcenter_pwd'); ?>" class="form-control input-style" autocomplete="new-password" required>
              <?php echo $mark; ?>
            </div>
          </div><br/>
          <!-- Message body -->            
          <?php if( !empty(get_option('authData')) || empty(get_option('brandcenter_url')) ){ $button = 'Save'; }else{  $button = 'Re-Connect'; $style = 'style="display: none;"'; } ?>

          <!-- Form actions -->
          <div class="form-group">
            <div class="col-md-12 text-right submit-button">
              <input name="brand_asset_auth_checking" type="hidden" value="<?php echo wp_create_nonce('brand-asset-auth-checking'); ?>" />
              <?php if( !empty(get_option('brandcenter_url')) ): ?>
                <input type="submit" name="clear" class="btn btn-primary clear" value="<?php _e('Disconnect') ?>" <?php echo $style; ?> >
              <?php else: ?>
                <input type="button" name="clear" class="btn btn-primary clear" value="<?php _e('CLEAR') ?>" onclick="resetForm()">
              <?php endif; ?>
                <input type="submit" name="submit" class="btn btn-primary save" value="<?php echo $button; ?>" style="float: left;">
            </div>
          </div>
        </fieldset>
      </form>
    </div>
    <?php if( $error_code == 1 ){ ?>
      <p class="wrong-details">Please check the details again!</p>
    <?php } ?>
	</div>
</div>