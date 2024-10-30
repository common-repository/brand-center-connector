<?php 
    if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly 
    $wp_brand_center = new WP_Brand_Center_Connector();
    $wp_brand_center->plugin_scripts();
?>
<style type="text/css">
  .thumbnail-img {
      width: 230px !important;
      height: 265px !important;
  }
  .product,
  .caption,
  .caption_title {
      padding: 5px 15px !important;
  }
  .media-frame-toolbar {
      display: inline-block !important;
  }
  .asset-loader {
      left: 50% !important;
  }
  .modal.left .modal-content,
  .modal.right .modal-content {
      top: 30px;
  }
  #wpbody-content {
      padding-bottom: 0px !important;
  }
  .library-container{
      margin-left: 0px !important;
      padding-right: 15px !important;
      padding-left: 15px !important;
  }
</style>
    <div class="asset-loader" id="asset-loader">
        <img src="<?php echo esc_url(plugins_url("../images/loading.gif", __FILE__)); ?>" />
    </div>
    <div id="divLoading">
    <p class="loader-text">Asset adding to Media Library</p> 
    </div>
    <div class="col-md-12" id="assets-container-initial" style="margin-top:50px; margin-left: -10px;">
      <div id="initialassets"></div>
    </div>
    </div>
    <div class="col-md-12 library-container" id="assets-container-show" style="margin-left: -10px;">
      <div id="sortassets"></div>
    </div>
    <div class="col-md-12 library-container" id="assets-filter-container" style="margin-left: -10px;">
      <div id="sortassets"></div>
    </div>   
    <div class="loader" id="loader">
        <img src="<?php echo esc_url(plugins_url("../images/loading.gif", __FILE__)); ?>" />
    </div>
  </div>
</div> 
<?php $page = 2; ?>
<!--model popup -->
<div class="container demo">
  <!-- Modal -->
  <div class="modal right fade" id="myModal2" tabindex="-1" role="dialog" aria-labelledby="myModalLabel2">
    <div class="modal-dialog" role="document">
      <div class="modal-content">
        <div class="modal-header">
          <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
          <h4 class="modal-title" id="myModalLabel2">Filter By
            <button name="Filters" class="btn btn-primary clear clear-filter" onclick="showInitial()" style="display: none;"><?php _e('Clear Filters') ?></button></h4>
        </div>
        <div class="modal-body">
          <div class="accordion_container">
            <div class="accordion_head">Asset Type<span class="type-count"></span><span class="plusminus"><span class="glyphicon glyphicon-chevron-down"></span></span>
            </div>
            <div class="accordion_body" style="display: none;">
                <p><label class="checkbox-style" onclick="return filterAssetsType(event, 'image')"><input type="checkbox" class="checkbox-input" name ="check-name" id="checkbox-asset-image"><span class="checkbox-span">Image</span></label></p>
                <p><label class="checkbox-style" onclick="return filterAssetsType(event, 'video')"><input type="checkbox" class="checkbox-input" name ="check-name" id="checkbox-asset-video"><span class="checkbox-span">Videos</span></label></p>
                <p><label class="checkbox-style" onclick="return filterAssetsType(event, 'other')"><input type="checkbox" class="checkbox-input" name ="check-name" id="checkbox-asset-other"><span class="checkbox-span">Other</span></label></p>
            </div>
            <div class="accordion_head">File Extension<span class="ext-count"></span><span class="plusminus"><span class="glyphicon glyphicon-chevron-down"></span></span>
            </div>
            <div class="accordion_body" style="display: none;">
                <div id="ac-extension"></div>
            </div>
            <div class="accordion_head">Tags<span class="plusminus"><span class="glyphicon glyphicon-chevron-down"></span></span>
            </div>
            <div class="accordion_body" style="display: none;">
                <p><input type="text" class="input-text-box" id="fkey" placeholder="Enter tag"></p>
                <p></p>
            </div>
            <div class="accordion_head">Asset groups<span class="grp-count"></span><span class="plusminus"><span class="glyphicon glyphicon-chevron-down"></span></span>
            </div>
            <div class="accordion_body" style="display: none;">
                <div id="ac-groups"></div>                
            </div>
            <div class="accordion_head">Advanced Filters<span class="tme-count"></span><span class="plusminus"><span class="glyphicon glyphicon-chevron-down"></span></span>
            </div>
            <div class="accordion_body" style="display: none;">
                <p class="advanced-filters-text">Time</p>
                <p><label class="checkbox-style" onclick="filterAssetsTime('pd')"><input type="radio" name="ctime" class="checkbox-input"><span class="checkbox-span">Past 24 hours</span></label></p>
                <p><label class="checkbox-style" onclick="filterAssetsTime('pw')"><input type="radio" name="ctime" class="checkbox-input"><span class="checkbox-span">Past Week</span></label></p>
                <p><label class="checkbox-style" onclick="filterAssetsTime('pm')"><input type="radio" name="ctime" class="checkbox-input"><span class="checkbox-span">Past Month</span></label></p>
                <p><label class="checkbox-style" onclick="filterAssetsTime('py')"><input type="radio" name="ctime" class="checkbox-input"><span class="checkbox-span">Past Year</span></label></p>
            </div>
        </div>
        </div>
      </div><!-- modal-content -->
    </div><!-- modal-dialog -->
  </div><!-- modal -->
</div><!-- container -->
<!--model popup -->