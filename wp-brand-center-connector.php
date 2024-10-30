<?php
/*
  Plugin Name: Brand Center Connector
  Plugin URI: 
  Description: Get access to all your assets in BrandLibrary that you can publish directly from WordPress without leaving their interface.
  Version: 1.0
  Author: BrandWizard
  Author URI: https://www.brandwizard.ai
  License: GPLv2 or later
  License URI: http://www.gnu.org/licenses/gpl-2.0.html
  Text Domain: brand-center-connector
*/
if ( ! defined( 'ABSPATH' ) ) exit;

define( 'ROOT_PATH', dirname(__FILE__) );

class WP_Brand_Center_Connector {

	// Constructor
  function __construct() {
      //actions to perform ajax requests
      add_action( 'admin_menu', array( $this, 'wpa_add_menu' ) );
      add_action( 'admin_enqueue_scripts', array( $this, 'wpa_styles') );

      add_action( 'wp_ajax_ajax_assets', array( $this, 'ajax_assets' ) );
      add_action( 'wp_ajax_ajax_plugin', array( $this, 'ajax_plugin_callback' ) );
      add_action( 'wp_ajax_ajax_sort', array( $this, 'ajax_sort' ) );
      add_action( 'wp_ajax_ajax_filters', array( $this, 'ajax_filters' ) );
      add_action( 'wp_ajax_ajax_filterType', array( $this, 'ajax_filterType' ) );
      add_action( 'wp_ajax_ajax_filterExtension', array( $this, 'ajax_filterExtension' ) );
      add_action( 'wp_ajax_ajax_filterGroup', array( $this, 'ajax_filterGroup' ) );
      add_action( 'wp_ajax_ajax_saveImage', array( $this, 'ajax_saveImage' ) );
      add_action( 'wp_ajax_ajax_saveToMedia', array( $this, 'ajax_saveToMedia' ) );
      add_action( 'wp_ajax_ajax_getTimeAssets', array( $this, 'ajax_getTimeAssets' ) );
      add_action( 'wp_ajax_ajax_getTagAssets', array( $this, 'ajax_getTagAssets' ) );
      add_action( 'wp_ajax_ajax_setTokenExpire', array( $this, 'ajax_setTokenExpire' ) );

      add_filter( 'media_upload_tabs', array( $this, 'media_upload_tabs__tab_slug' ) );
      add_action( 'media_upload_tab_slug', array( $this, 'media_upload_tab_slug__content' ) );        

      register_activation_hook( __FILE__, array( $this, 'wpa_install' ) );
      register_deactivation_hook( __FILE__, array( $this, 'wpa_uninstall' ) );
  }

  /*
    * Actions perform at loading of admin menu
  */
  function wpa_add_menu() {

      add_menu_page( 'BW Connector', 'BW Connector', 'manage_options', 'brand_library', array( __CLASS__, 'wpa_page_file_path' ), plugins_url( 'images/brand-center-icon.png', __FILE__ ), 65 );
      add_submenu_page( 'brand_library', 'BW Connector' , ' Library', 'manage_options', 'brand_library', array( __CLASS__, 'wpa_page_file_path' ) );
      add_submenu_page( 'brand_library', 'BW Connector' , ' Settings', 'manage_options', 'brand_settings', array( __CLASS__, 'wpa_page_file_path' ) );
  }

  /*
    * Actions perform on loading of menu pages
  */
  static function wpa_page_file_path() {      
      
      $screen = get_current_screen();

     if ( strpos( $screen->base, 'brand_settings' ) !== false ) {
          include( dirname( __FILE__ ) . '/includes/brandcenter-settings.php' );
      } 
      else {
          include( dirname( __FILE__ ) . '/includes/wp-brandcenter-library.php' );
      }
  }

  /*
    * Styling and Scripting: loading stylesheets and scripts for the plugin.
  */
  public function wpa_styles( $page ) {

      wp_enqueue_style( 'wp-brand-center-custom-style', plugins_url( 'css/custom-style.css', __FILE__) );        
      wp_enqueue_script( 'wp-brand-center-custom-scripts', plugins_url( 'js/custom-scripts.js', __FILE__) );
      wp_enqueue_script( 'jquery' ); 
  }

  /*
    * Actions perform on activation of plugin
  */
  function wpa_install() {     

    add_option( 'brandcenter_url', '' );
    add_option( 'brandcenter_user', '' );
    add_option( 'brandcenter_pwd', '' );
    add_option( 'authData', '' );
    add_option( 'brandcenter_error_auth', '' );

  }

  /*
    * Actions perform on de-activation of plugin
  */
  function wpa_uninstall() {
    
    delete_option( 'brandcenter_url' );
    delete_option( 'brandcenter_user' );
    delete_option( 'brandcenter_pwd' );
    delete_option( 'authData' );
    delete_option( 'brandcenter_error_auth' );

    return true;
  }

  function plugin_scripts(){
      wp_enqueue_style( 'wp-brand-center-bootstrap-style', plugins_url( 'css/bootstrap.min.css', __FILE__) );
      wp_enqueue_script( 'wp-brand-center-bootstrap-scripts', plugins_url( 'js/bootstrap.min.js', __FILE__) );
  }

  /*
    * Actions to perform token expiry checking
  */
  public function ajax_setTokenExpire() {
    update_option( 'authData', '' );
    echo 'BW Connector token expired, Please re-connect';
    wp_die();
  }

  /*
    * Actions to loading assets from BrandCenter
  */
  public function ajax_assets() {
    if ( isset( $_POST["page_number"] ) ) {
      $page_number = esc_attr($_POST["page_number"]);
      $assets = json_decode( $this->wpb_get_assets($page_number, '', '') );
      $acount = $this->ajax_html($assets->data);
      if( !empty($acount) ){
        $aheader = $this->initial_header($assets->meta->count, '');
        $ast = $aheader;
        $ast .= '<div class="col-md-12 library-container" id="assets-initial-container" style="background-color: #f3f5f7; margin-left: 10px;">';
        $ast .= $acount;
        print_r($ast);
      }else{
        echo $this->issueAssets();
      }
      wp_die();
    }
  }    

  /*
    * Actions to loading assets from BrandCenter when user scroll page to bottom
  */
  public function ajax_plugin_callback() {      
    if ( isset( $_POST["id"] ) ) {
      $id = $_POST["id"];
      if ( !is_numeric($id) ) {
        return false;
      }

      if ( !empty($_POST["sortName"]) ) {
        $sort = sanitize_text_field($_POST["sortName"]);
      }else{
        $sort = '';
      } 
      
      if( !empty( $_POST["filterType"]) ){
        $filterType = explode(",", $_POST["filterType"]);
        $fstring .= $this->ftypeString($filterType);        
      }
      
      if( !empty($_POST["filterExtension"]) ){
        $filterExtension = explode(",", $_POST["filterExtension"]);
        $fstring .= $this->ftypeExtension($filterExtension);
      }
      
      if( !empty($_POST["filterGroup"]) ){
        $filterGroup = explode(",", $_POST["filterGroup"]);          
        $fgstring = $this->ftypeGroup($filterGroup);
        $fgstring .= '&filter[collection][operator]=IN'.$fgstring;
      }

      if( !empty($fgstring) ){
        $fstring = $fgstring;          
      }elseif( !empty($fstring) ){
        $fstring = '&filter[metadataFormat][operator]=IN'.$fstring;          
      }else{
        $fstring = '';
      }
              
      $assets = json_decode( $this->wpb_get_assets($id, $sort, $fstring) );        
      $acount = $this->ajax_html($assets->data);
      $asort = '<div class="col-md-12 library-container" id="assets-initial-container" style="background-color: #f3f5f7; margin-left: 10px; padding-left: 0px; padding-right: 0px;">';
      $asort .= $acount;
      print_r($asort);           
      wp_die();
    }
  }

  /*
    * Actions to loading assets based on sorting options
  */
  public function ajax_sort() {      
    if ( isset( $_POST["sort"] ) ) {
      $sort = sanitize_text_field($_POST["sort"]);
      $assets = json_decode($this->wpb_get_assets(1, $sort, '')); 
      $acount = $this->ajax_html($assets->data);
      $asort = '<div class="col-md-12 library-container" id="assets-initial-container" style="background-color: #f3f5f7; margin-left: 10px;">';
      $asort .= $acount;
      print_r($asort);            
      wp_die();
    }
  }

  /*
    * Actions to loading assets based on fliter options
  */
  public function ajax_filters() {
    if ( isset( $_POST["title"] ) ) {
        $title = sanitize_title($_POST["title"]);

        if ( !empty($sort) ) {
          $sort = sanitize_text_field($_POST["sort"]);
        }

        $result = get_option('authData');
        $token = $result->access_token;

        $api_url = get_option('brandcenter_url').'/api/v1.0/'.$title.'?sort='.$sort;
        $args = array(
          'headers' => array(
            'access-token' => $token
          )
        );
        $response = wp_remote_get( $api_url, $args );
        $response = wp_remote_retrieve_body( $response );

        $res = json_decode($response);
        foreach ( $res->data as $ext ) {
          if( $title == 'extensions' ){
            $name = $ext->attributes->name;
            $fcontent .= '<p><label class="checkbox-style" onclick=filterExtension("'.$name.'")><input type="checkbox" class="checkbox-input" data-id="checkbox-ext-'.$name.'"><span class="checkbox-span">'.$name.'</span></label></p>';
          }else{
            $name = $ext->attributes->title;
            $id = $ext->id;
            $fcontent .= '<p><label class="checkbox-style" onclick=filterGroup("'.$id.'")><input type="checkbox" class="checkbox-input"><span class="checkbox-span">'.$name.'</span></label></p>';
          }        
        }
      echo $fcontent;
      wp_die();
    }
  }

  /*
    * Actions to loading assets based on filter types
  */
  public function ajax_filterType() {
    if( isset($_POST['type']) ){
      $type = array_map( 'esc_attr', $_POST['type'] );                            
      $fstring = $this->ftypeString($type);
      $fstring = '&filter[metadataFormat][operator]=IN'.$fstring;
      $assets = json_decode($this->wpb_get_assets(1, '', $fstring));
    }else{
      $assets = json_decode($this->wpb_get_assets(1, '', ''));            
    }

    $acount = $this->ajax_html($assets->data);
    $aheader = $this->initial_header($assets->meta->count, 'filter-header');
    $ares = $aheader;
    $ares .= '<div class="col-md-12 library-container" id="assets-initial-container" style="background-color: #f3f5f7;">';
    $ares .= $acount;
    print_r($ares);
    wp_die();
  }

 /*
  * Actions to loading assets based on filter extensions
 */
  public function ajax_filterExtension() {
    if( isset($_POST['extension']) ){
        $extension = array_map( 'esc_attr', $_POST['extension'] );        
        $fstring = $this->ftypeExtension($extension);
        if( !empty($fstring) ){
          $fstring = '&filter[metadataFormat][operator]=IN'.$fstring;
          $assets = json_decode($this->wpb_get_assets(1, '', $fstring));
        } 
    }else{
      $assets = json_decode($this->wpb_get_assets(1, '', ''));
    }
    $acount = $this->ajax_html($assets->data);
    $aheader = $this->initial_header($assets->meta->count, 'filter-header');
    $ares = $aheader;
    $ares .= $acount;
    print_r($ares);
    wp_die();
  }

  /*
    * Actions to loading assets based on filter groups
  */
  public function ajax_filterGroup() {
    if( isset($_POST['group']) ){
      $group = array_map( 'esc_attr', $_POST['group'] );             
      $fstring = $this->ftypeGroup($group);
      if( !empty($fstring) ){
        $fstring = '&filter[collection][operator]=IN'.$fstring;
        $assets = json_decode($this->wpb_get_assets(1, '', $fstring));
      }                       
    }else{
      $assets = json_decode($this->wpb_get_assets(1, '', ''));            
    }

    $acount = $this->ajax_html($assets->data);
    $aheader = $this->initial_header($assets->meta->count, 'filter-header');
    $ares = $aheader;
    $ares .= '<div class="col-md-12 library-container" id="assets-initial-container" style="background-color: #f3f5f7;">';
    $ares .= $acount;
    print_r($ares);
    wp_die();
  }

  /*
    * Actions to loading assets based on filter times
  */
  public function ajax_getTimeAssets() {
    if( isset($_POST['time']) ){
      $time = sanitize_text_field($_POST['time']);
      $fstring = $this->getTime($time);
      $assets = json_decode($this->wpb_get_assets(1, '', $fstring));        
      $acount = $this->ajax_html($assets->data);
      $aheader = $this->initial_header($assets->meta->count, 'filter-header');
      $ares = $aheader;
      $ares .= $acount;
      if( !empty($acount) ){
        print_r($ares);
      }else{
        echo $this->noAssets();
      }
      
      wp_die();            
    }
  }

  /*
    * Actions to loading assets based on filter tags
  */
  public function ajax_getTagAssets() {
    if( isset( $_POST['tag'] ) ){
        $tag = esc_attr( $_POST['tag'] );
        $result = get_option('authData');
        $token = $result->access_token;
        $api_url = esc_url_raw(get_option('brandcenter_url').'/api/v1.0/search-lib/'.$tag.'?include=collection,assetCollectionCategory,assetCollectionSubCategory&filter[status]=1');
        $args = array(
        'timeout'     => 30,
        'headers' => array(
          'access-token' => $token
        )
        );
        $response = wp_remote_get( $api_url, $args );        
        $response = wp_remote_retrieve_body( $response );

        $res = json_decode($response);                   
        $acount = $this->ajax_html($res->data);
        $aheader = $this->initial_header($res->count, '');
        $ast = $aheader;
        $ast .= '<div class="col-md-12 library-container" id="assets-initial-container" style="background-color: #f3f5f7;">';            
        $ast .= $acount;
        if( !empty($ast) ) {
          print_r($ast);
        }else{
          echo $this->noAssets();
        }
      
        wp_die();            
     }
  }    

  /*
   * Actions to save data of settings page, To access BrandCenter API's
  */
  public function wpb_save_data( $data ) {

    //Wordpress HTTP request for basic authentication checking 
    $url = esc_url_raw($data['brandcenter_url'].'/api/login-token');  
    $username = $data['username'];
    $password = $data['password'];

    $args = array(
        'headers' => array(
          'Authorization' => 'Basic ' . base64_encode( $username . ':' . $password )
        )
    );
    $response = wp_remote_retrieve_body(wp_remote_get( $url, $args ));
    $result = json_decode($response);
              
    if( !empty($result->access_token) ){
        //options updating in success case
        $this->updateSettings($data['brandcenter_url'], $username, $password, $result, 0);
    }else{
        //options updating in failed case
        $this->updateSettings('', '', '', '', 1);
    }      

    return true;
  }

  /*
   * Actions to get assets from BrandCenter API's
  */
  public function wpb_get_assets( $pageNumber, $sort, $filters ) {
      $result = get_option('authData');
      $token = $result->access_token;
      // Store request params in an array
      $request_params = array(
          'fields' => 'title,metadataFormat,assetThumbnailUrl,created,availableFormats,metadataUrl', 
          'page[size]' => 12, 
          'page[number]' => $pageNumber,
          'sort' => $sort, 
          'filter[status][value]' => 1
      );
      $post_string = '';

      foreach( $request_params as $var=>$val )
      {
          $post_string .= '&'.$var.'='.($val);    
      }
      
      $api_url = urldecode(get_option('brandcenter_url').'/api/v1.0/assets?'.$post_string.$filters);
      $args = array(
        'headers' => array(
          'access-token' => $token
        )
      );
      $response = wp_remote_get( $api_url, $args );
      $response = wp_remote_retrieve_body( $response );
      return $response;
  }

  /*
   * Action to add assets to editor
  */
  public function ajax_saveImage() {
    $url = esc_url($_POST['imageURL']);
    $filename = basename($url);
    $ext = pathinfo($url, PATHINFO_EXTENSION);
    if( $ext == 'jpg' || $ext == 'png' || $ext == 'gif' || $ext == 'jpeg' || $ext == 'bmp' ){
      $content = '<img src="'.$url.'">';
    }elseif( $ext == 'mp4' ){
      $content = '[video width="1920" height="1080" mp4="'.$url.'"][/video]';
    }elseif( $ext == 'mp3' ){
      $content = '[audio mp3="'.$url.'"][/audio]';
    }else{
      $content = '<a href="'.$url.'" target="_blank">'.$filename.'</a>';
    }
    
    print_r($content);
    wp_die();
  }

  /*
   * Action to add assets to Media Library
  */
  public function ajax_saveToMedia() {      

    // Sanitize filename.
    $url = esc_url($_POST['asset_url']);
    $filename = basename($url);
    $filename = sanitize_file_name( $filename );
    global $wpdb;
    $query = "SELECT COUNT(*) FROM {$wpdb->posts} WHERE guid='$url'";
    $count = intval($wpdb->get_var($query));
    if ( $count < 1 ) {
      $prop = getimagesize($url);
      $attachment = array(
        'guid' => $url,
        'post_mime_type' => $prop['mime'],
        'post_title' => preg_replace( '/\.[^.]+$/', '', $filename ),
      );
      $attachment_metadata = array( 'width' => $prop[0], 'height' => $prop[1], 'file' => $filename );
      $attachment_metadata['sizes'] = array( 'full' => $attachment_metadata );
      $attachment_id = wp_insert_attachment( $attachment );
      wp_update_attachment_metadata( $attachment_id, $attachment_metadata );
      echo 'Asset added to Media Library';
    }else{
      echo 'Already added to Media Library';
    }
    
    wp_die();
  }

  /*
   * Action to add color based on asset extention
  */
  public function wpb_get_icon_color($ext) {

      if( $ext == 'png' ){ $color = '#5f6b88'; }
      else if( $ext == 'jpg' ){ $color = '#f35f90'; }
      else if( $ext == 'mp4' ){ $color = '#7487d3'; }
      else if( $ext == 'raw' ){ $color = '#9fb0bb'; }
      else if( $ext == 'txt' ){ $color = '#21b8f1'; }
      else if( $ext == 'gif' ){ $color = '#fcbe4b'; }
      else if( $ext == 'pdf' ){ $color = '#f27070'; }
      else if( $ext == 'tiff' ){ $color = '#4eac8a'; }
      else if( $ext == 'eps' ){ $color = '#5d25a0'; }
      else if( $ext == 'bmp' ){ $color = '#224c5b'; }
      else if( $ext == 'doc' ){ $color = '#4a90e2'; }
      else if( $ext == 'zip' ){ $color = '#7ed1a5'; }
      else if( $ext == 'csv' ){ $color = '#5b6877'; }

      return $color;
  }

  /*
   * Action to update options in database
  */
  function updateSettings( $brand_url, $brand_user, $brand_pwd, $brand_result, $auth ) {

      update_option( 'brandcenter_url', $brand_url );
      update_option( 'brandcenter_user', $brand_user );
      update_option( 'brandcenter_pwd', $brand_pwd );
      update_option( 'authData', $brand_result );
      update_option( 'brandcenter_error_auth', $auth );
  }

  /*
   * Action to get query string for filter type API
  */
  function ftypeString($type) { 

    foreach ($type as $name) {
      $name = str_replace(',', '', $name) ;
      if( $name == 'image' ){
        $fstring .= '&filter[metadataFormat][value][]=ai&filter[metadataFormat][value][]=bmp&filter[metadataFormat][value][]=eps&filter[metadataFormat][value][]=gif&filter[metadataFormat][value][]=jpg&filter[metadataFormat][value][]=png&filter[metadataFormat][value][]=psd&filter[metadataFormat][value][]=tif&filter[metadataFormat][value][]=tiff';
      }elseif( $name == 'video' ){
        $fstring .= '&filter[metadataFormat][value][]=avi&filter[metadataFormat][value][]=flv&filter[metadataFormat][value][]=mov&filter[metadataFormat][value][]=mp3&filter[metadataFormat][value][]=mp4&filter[metadataFormat][value][]=mpeg&filter[metadataFormat][value][]=wav&filter[metadataFormat][value][]=wmv';
      }elseif( $name == 'other' ){
        $fstring .= '&filter[metadataFormat][value][]=doc&filter[metadataFormat][value][]=docx&filter[metadataFormat][value][]=dot&filter[metadataFormat][value][]=fla&filter[metadataFormat][value][]=indd&filter[metadataFormat][value][]=indl&filter[metadataFormat][value][]=pdf&filter[metadataFormat][value][]=ppt&filter[metadataFormat][value][]=pptx&filter[metadataFormat][value][]=qxd&filter[metadataFormat][value][]=txt&filter[metadataFormat][value][]=wmf&filter[metadataFormat][value][]=xls&filter[metadataFormat][value][]=xlsx&filter[metadataFormat][value][]=zip';
      }else{
        $fstring .= '';
      }
    }     

    return $fstring;
  }

  /*
   * Action to get query string for filter extension API
  */
  function ftypeExtension( $type ) {

    foreach ( $type as $name ) {
      if( !empty($name) ){
          $name = str_replace(',', '', $name) ;
          $fstring .= '&filter[metadataFormat][value][]='.$name;
      }          
    }  

    return $fstring;
  }

  /*
   * Action to get query string for filter group API
  */
  function ftypeGroup( $type ) {

    foreach ($type as $name) {
      if( !empty($name) ) {
          $name = str_replace(',', '', $name) ;
          $fstring .= '&filter[collection][value][]='.$name;
      }          
    }  

    return $fstring;
  }

  /*
   * Action to get query string for filter time API
  */
  function getTime( $time ) {
    $ptime = time();
    if( $time == 'pd' ){        
      $ltime = time()-86400;        
    }elseif( $time == 'pw' ){
      $ltime = time() - 86400 * 7;
    }elseif( $time == 'pm' ){
      $ltime = time() - 86400 * 30;
    }elseif( $time == 'py' ){
      $ltime = time() - 86400 * 365;
    }

    $fstring = "&filter[created][operator]=BETWEEN&filter[created][value][]=".$ltime."&filter[created][value][]=".$ptime;  
    return $fstring;
  }

  /*
   * Action to display 404 result, If authentication expired/failed
  */
  function issueAssets() {
    $nofile = plugins_url('images/no-assets-collections.png', __FILE__);
    $nA  = '<div id="no-assets-container">'; 
    $nA .= '<p style="text-align: center; margin-top: 50px;"><img src="'.esc_url($nofile).'"></p>';
    $nA .= '<h3 class="no-assets" style="text-align: center;">Something went wrong. Please check settings</h3>';
    $nA .= '</div>';
    return $nA;
  }

  /*
   * Action to display 404 result, If assets not found based on query
  */
  function noAssets() {
    $nofile = plugins_url('images/no-assets-collections.png', __FILE__);
    $nA  = '<div id="no-assets-container">'; 
    $nA .= '<p style="text-align: center; margin-top: 50px;"><img src="'.esc_url($nofile).'"></p>';
    $nA .= '<h3 class="no-assets" style="text-align: center;">No Assets Found !</h3>';
    $nA .= '<p style="text-align:center; cursor: pointer;" onclick=showInitial()><a><<< Load All Assets >>></a></p>';
    $nA .= '</div>';
    return $nA;
  }

  /*
   * Action to add 'Insert from BrandCenter' Tab in Media Library
  */
  function media_upload_tabs__tab_slug( $tabs ) {
      $newtab = array ( 'tab_slug' => 'Insert from BrandCenter' );
      return array_merge( $tabs, $newtab );
  }

  /*
   * Action to add iframe for 'Insert from BrandCenter' in Media Library
  */
  function media_upload_tab_slug__content() {
      wp_iframe( array( $this, 'media_upload_tab_slug_content__iframe' ) );
  }

  /*
   * Action to add content for 'Insert from BrandCenter' in Media Library
  */
  function media_upload_tab_slug_content__iframe() {
      include( dirname(__FILE__) . '/includes/brandcenter-asset-library.php' );
  }

  /*
   * Action to add html content for assets page
  */
  function ajax_html( $data ) {
    foreach ( $data as $asset ) {           
      $ext = $asset->attributes->metadataFormat;
      $atitle = $asset->attributes->title;
      if ( strlen($atitle) > 20 ) {
        $atitle = substr($atitle, 0, 20) . '...';
      }else{
        $atitle = $atitle;
      }
      if( !empty($ext) ){ $ext = $ext; }else{ $ext = 'raw'; }
      $icon_color = $this->wpb_get_icon_color($ext);
      if( @getimagesize( $asset->attributes->assetThumbnailUrl ) ){ 
        $style = 'style="width: 100%; height: auto;"';
        $thumbnail = $asset->attributes->assetThumbnailUrl;
      }else { 
        $style = 'style="width: 50%; height: auto; background-color: #000000;"';
        $thumbnail = plugins_url("images/icons/$ext.png", __FILE__);
      }

      $passets .= '<div class="col-sm-6 col-md-3">';
      $passets .= '<div class="thumbnail thumbnail-img">
            <div>
                <span class="label label-info format" style="background-color: '.$icon_color.'">'.esc_html($ext).'</span>
            </div>                               
            <div class="text-center product">
                <div class="product_icon" onclick=savefile("'.esc_url($asset->attributes->metadataUrl).'")>
                  <img src="'.esc_url($thumbnail).'" '.$style.'/>
                </div>
            </div>
            <div class="caption">
              <div class="row">
                  <div class="col-md-12 col-xs-12 caption_title">
                    <p class="item-info">'.esc_html($atitle).'</p>
                    <p class="createdDate">'.date('F j, Y', $asset->attributes->created).'</p>
                  </div>            
              </div>        
          </div>
        </div>
      </div>';
      //echo $passets;
    }

    return $passets;
  }

  /*
   * Action to add html header content for assets page
  */
  function initial_header( $count, $class ) {
    $header = '<div class="container" id="initialheader">
                <div class="row">
                  <div class="col-md-12"></div>
                  <div class="col-md-12 library-header '.$class.'">
                    <div class="libray_bottom">
                      <div class="col-sm-6 col-md-4">
                        <div class="library-header-content"><h3>Library('.$count.')</h3>
                        </div>
                      </div>
                      <div class="col-sm-6 col-md-6">
                        <div class="form-group1">
                            <div class="icon-addon addon-lg">
                                <input type="text" placeholder="Search" style="margin-left: -45px !important;" class="form-control search-field" id="fsearch" value="" onkeydown=fsearch(this.value)>
                                <label for="email" class="glyphicon glyphicon-search" rel="tooltip" title="Search"></label>
                                <span class="close-search" style="display:none;" onclick=showInitial()>X</span>
                            </div>
                        </div>                               
                      </div>
                      <div class="col-sm-6 col-md-2 text-right">
                        <a class="dropbtn" onclick="showDrop()">
                          <img src='.esc_url(plugins_url('images/sort.png', __FILE__)).' width="25px" height="25px" alt="Sort">
                        </a>
                          <div id="myDropdown" class="dropdown-content">
                              <a onclick=sortAssets("title")>Title (A-Z)</a>
                              <a onclick=sortAssets("-title")>Title (Z-A)</a>
                              <a onclick=sortAssets("-created")>Latest</a>
                              <a onclick=sortAssets("created")>Oldest</a>
                              <a onclick=sortAssets("-metadataBytes")>File Size (largest)</a>
                              <a onclick=sortAssets("metadataBytes")>File Size (smallest)</a>
                          </div>
                        <a class="filters" data-toggle="modal" data-target="#myModal2" onclick="showFilters()">
                          <img src='.esc_url(plugins_url('images/filter_list.png', __FILE__)).' width="25px" height="25px" />
                        </a>          
                      </div>
                    </div>
                  </div>';

      return $header;
  }      
}

new WP_Brand_Center_Connector();
?>