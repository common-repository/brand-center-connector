//user ideal state checking
var idleTime = 0;
$( document ).ready( function () {
  idleInterval = setInterval( timerIncrement, 60000 ); // 1 minute
  //Zero the idle timer on mouse movement.
  $( 'body' ).mousemove( function (e) {
   idleTime = 0;
  });

  $( 'body' ).keypress( function (e) {
      idleTime = 0;
  });

  $( 'body' ).click( function() {
     idleTime = 0;
  });
});

function timerIncrement() {
  idleTime = idleTime + 1;
  if ( idleTime > 30 ) {
    var data = {
      'action': 'ajax_setTokenExpire',
      'token_expire': 1        
    };

    jQuery.post( ajaxurl, data, function(response ) {
      //alert(response);
    });
  }
}

//form settings clear
function resetForm() {
  document.getElementById( "settings-form" ).reset();
}

//initial loading
$( document ).ready( function () { 
  $( function() {
      showInitial();
  });  
})

//loading all assets
function showInitial(){
      $( '#asset-loader' ).show();
      $( '#assets-container-show' ).hide();
      $( '#assets-filter-container' ).hide();
      $( '#no-assets-container' ).hide();
      var data = {
        'action': 'ajax_assets',
        'page_number': 1        
      };

      jQuery.post( ajaxurl, data, function( response ) {
        //alert(response);
        $( '#asset-loader' ).hide();
        $( 'input:checkbox' ).prop( 'checked', false );
        $( ".ext-count, .type-count, .grp-count, .tme-count" ).css( {"display": "none"} );
        $( ".clear-filter" ).css( {"display": "none"} );        
        $( '#assets-container-initial' ).show();
        $( '#initialassets' ).html( response );
        document.getElementById( "fkey" ).value = ' ';
        sessionTags();
      });
}

//sort dropdown showing
function showDrop() {
    jQuery( "#myDropdown" ).show();
}

function sessionTags(){
  sessionStorage.setItem( "page", '' );
  sessionStorage.setItem( "sortName", '' );
  sessionStorage.setItem( "filterType", '' );
  sessionStorage.setItem( "filterExtension", '' );
  sessionStorage.setItem( "filterTime", '' );
  sessionStorage.setItem( "filterGroup", '' );
  sessionStorage.setItem( "filterTag", '' );
}
//clearing sessions pagination
window.onload = function() {
  sessionTags();
}

//ajax calling when page scroll to bottom of the page
var attachEvent = function() {
$( window ).scroll( function() {
    if ( $( window ).data( 'ajaxready') === false || sessionStorage.getItem( "filterTag" ) != '' )
      return;

    if ( $( window ).scrollTop() >= $( document ).height() - $( window ).height() - 50) {        
      $( window ).data( 'ajaxready', false );
      $( '#loader' ).show();       
      if( sessionStorage.getItem( "page" ) == null || sessionStorage.getItem( "page" ) == '' ){
          var page_number = 2;
       }else{
          var page_number = sessionStorage.getItem( "page" );
       }
       //alert(sessionStorage.getItem("page"));
       $( window ).off( "scroll" );       
       var data = {
        'action': 'ajax_plugin',
        'id': page_number,
        'sortName': sessionStorage.getItem( "sortName" ),
        'filterType': sessionStorage.getItem( "filterType" ),
        'filterExtension': sessionStorage.getItem( "filterExtension" ),
        'filterGroup': sessionStorage.getItem( "filterGroup" ),
        'filterTime': sessionStorage.getItem( "filterTime" ),
        'filterTag': sessionStorage.getItem( "filterTag" )
      };

      // since 2.8 ajaxurl is always defined in the admin header and points to admin-ajax.php
      jQuery.post( ajaxurl, data, function( response ) {
        //alert(response);
        if( response != '' ){
            $( window ).data( 'ajaxready', true );              
            attachEvent();        
          if( !sessionStorage.getItem( "sortName" ) == '' ){
            $( '#sortassets').append(response);
          }else if( !sessionStorage.getItem( "filterType" ) == '' || !sessionStorage.getItem( "filterExtension" ) == '' || !sessionStorage.getItem( "filterGroup" ) == '' ){
            $( '#assets-filter-container' ).append(response);
          }else{
            $( '#assets-initial-container' ).append(response);
          }        
            $( '#loader' ).hide();
            page_number++;                
            sessionStorage.setItem( "page", page_number );
        }else{
            $( '#loader' ).hide();
            $( window ).data( 'ajaxready', false );
            return;
        }       
      });
    }
  });
};

attachEvent();
 
//sorting
function sortAssets( sortName ){
  $( "#myDropdown" ).hide();
  $( '#loader' ).show();
  $( '#assets-initial-container' ).hide();
  $( '#assets-container-show' ).hide();  
  var data = {
    'action': 'ajax_sort',
    'sort': sortName
  };

  jQuery.post( ajaxurl, data, function( response ) {
    $( '#loader' ).hide();
    $( '#assets-container-show' ).show();
    $( '#sortassets' ).html(response);
    sessionStorage.setItem( "sortName", sortName );
  });
}

//filters accordian
$( document ).ready( function () {
    //toggle the component with class accordion_body
    $( ".accordion_head" ).click( function () {
        if ( $('.accordion_body').is(':visible') ) {
            $(".accordion_body").slideUp(300);
            $(".plusminus").html('<span class="glyphicon glyphicon-chevron-down"></span>');
        }
        if ( $(this).next(".accordion_body").is(':visible') ) {
            $(this).next(".accordion_body").slideUp(300);
            $(this).children(".plusminus").html('<span class="glyphicon glyphicon-chevron-down"></span>');
        } else {
            $(this).next(".accordion_body").slideDown(300);
            $(this).children(".plusminus").html('<span class="glyphicon glyphicon-chevron-up"></span>');
        }
    });
});

//filters ajax calling
function showFilters(){
  var data = {
    'action': 'ajax_filters',
    'title': 'extensions',
    'sort': 'name'
  };

  jQuery.post( ajaxurl, data, function( response ) {
    $( '#ac-extension' ).html(response);
  });
}

$( document ).ready( function () {
  var data = {
    'action': 'ajax_filters',
    'title': 'asset-groups',
    'sort': 'title'
  };

  jQuery.post( ajaxurl, data, function( response ) {    
    $( '#ac-groups' ).html(response);
  });

});

//filters type
function filterAssetsType( event, type ){
  var estr = "checkbox-asset-"+ type;
  $( "[id="+ estr + "]" ).unbind().click( function() {
    event.stopPropagation? event.stopPropagation() : event.cancelBubble = true;
    var a = "" + type;
    var arrStr = sessionStorage.getItem("filterType"); 
    var ft = elemPush(a, arrStr);
    [...new Set(ft)];
    $( '#loader' ).show();
    $( '#assets-initial-container' ).hide();
    $( '#assets-container-show' ).hide();
    $( '#assets-filter-container' ).hide();
    $( '#initialheader' ).hide();
    var data = {
      'action': 'ajax_filterType',
      'type': ft,
    };

    jQuery.post( ajaxurl, data, function( response ) {
        $( '#loader' ).hide();
        $( '#assets-filter-container' ).show();    
        $( '#assets-filter-container' ).html(response);            
        sessionStorage.setItem( "filterType", ft );
        $( ".ext-count, .type-count" ).css( {"display": "inline-block", "background-color": "#e0e0e0"} );
        $( ".clear-filter" ).css( {"display": "block"} );
        tcount = sessionStorage.getItem("filterType").replace(/,\s*$/, "");
        ecount = sessionStorage.getItem("filterExtension").replace(/,\s*$/, "");
        tarr = tcount.split(",");
        earr = ecount.split(",");
        if( tcount != '' ){ tlen = tarr.length; }else{ tlen = ''; $( ".ext-count, .type-count" ).css( {"background-color": "#ffffff"} ); }
        if( ecount != '' ){ elen = earr.length; }else{ elen = ''; $( ".ext-count, .type-count" ).css( {"background-color": "#ffffff"} ); }        
        $( '.ext-count' ).html(elen);
        $( '.type-count' ).html(tlen);
        filterTagCheck();
    });
  });
}

//filters extension
function filterExtension( ext ){
    var a = "" + ext;
    var arrStrext = sessionStorage.getItem("filterExtension");
    var ftext = elemPush(a, arrStrext); 
    
    [...new Set(ftext)];
    $( '#loader' ).show();
    $( '#assets-initial-container' ).hide();
    $( '#assets-container-show' ).hide();
    $( '#assets-filter-container' ).hide();
    $( '#initialheader' ).hide();

    var data = {
      'action': 'ajax_filterExtension',
      'extension': ftext,
      'filterType': sessionStorage.getItem("filterType"),
      'filterTime': sessionStorage.getItem("filterTime"),
      'filterTag': sessionStorage.getItem("filterTag")
    };

    jQuery.post( ajaxurl, data, function( response ) {
        //alert(response);
        $( '#loader' ).hide();
        $( '#assets-filter-container' ).show();    
        $( '#assets-filter-container' ).html(response);
        sessionStorage.setItem( "filterExtension", ftext );
        $( ".clear-filter" ).css( {"display": "block"} );
        $( ".ext-count" ).css( {"display": "inline-block", "background-color": "#e0e0e0"} );
        ecount = sessionStorage.getItem( "filterExtension" ).replace(/,\s*$/, "");
        earr = ecount.split(",");
        if( ecount != '' ){ elen = earr.length; }else{ elen = ''; $( ".ext-count, .type-count" ).css( {"background-color": "#ffffff"} ); }        
        $( '.ext-count' ).html(elen);
        filterTagCheck();
    });
}

//filters group
function filterGroup( gid ){
    var a = "" + gid;
    var arrStrgid = sessionStorage.getItem( "filterGroup" );
    var ftgid = elemPush(a, arrStrgid);    
    
    [...new Set(ftgid)];
    $( '#loader' ).show();
    $( '#assets-initial-container' ).hide();
    $( '#assets-container-show' ).hide();
    $( '#assets-filter-container' ).hide();
    $( '#initialheader' ).hide();

    var data = {
      'action': 'ajax_filterGroup',
      'group': ftgid,
    };

    jQuery.post( ajaxurl, data, function( response ) {
        $( '#loader' ).hide();
        $( '#assets-filter-container' ).show();    
        $( '#assets-filter-container' ).html(response);
        sessionStorage.setItem( "filterGroup", ftgid );
        $( ".clear-filter" ).css( {"display": "block"} );
        $( ".grp-count" ).css( {"display": "inline-block", "background-color": "#e0e0e0"} );
        gcount = sessionStorage.getItem( "filterGroup" ).replace(/,\s*$/, "");
        garr = gcount.split(",");
        if( gcount != '' ){ elen = garr.length; }else{ elen = ''; $( ".grp-count" ).css( {"background-color": "#ffffff"} ); }        
        $( '.grp-count' ).html(elen);
        filterTagCheck();
    });
}

//asset adding to editor
function savefile(url){
  $( "div#divLoading" ).addClass('show');
  $( "div#divLoading" ).removeClass('hideLoader');
  var tinyMCE = parent.tinymce;
  var e = parent.jQuery;
  if( tinyMCE === undefined ){
    saveMedia(url);
    return;
  }

  var data = {
    'action': 'ajax_saveImage',
    'imageURL': url,
  };

  jQuery.post( ajaxurl, data, function( response ) {
      $('.divLoading').hide();
      $("div#divLoading").addClass( 'hideLoader' );
      tinyMCE.activeEditor.execCommand( 'mceInsertContent', false, response );
      e( '.supports-drag-drop' ).css( 'display', 'none' );
  });
}

//filter by time period
function filterAssetsTime( id ){
  $( '#loader' ).show();
  $( '#assets-initial-container' ).hide();
  $( '#assets-container-show' ).hide();
  $( '#assets-filter-container' ).hide();
  $( '#initialheader' ).hide();
  var data = {
    'action': 'ajax_getTimeAssets',
    'time': id,
  };

  jQuery.post( ajaxurl, data, function( response ) {
      //alert(response);
      $( '#loader' ).hide();
      $( '#assets-filter-container' ).show();    
      $( '#assets-filter-container' ).html(response);
      sessionStorage.setItem( "filterTime", id );
      $( ".clear-filter" ).css( {"display": "block"} );
      $( ".tme-count" ).css( {"display": "inline-block", "background-color": "#e0e0e0"} );
      tcount = sessionStorage.getItem( "filterTime" ).replace(/,\s*$/, "");
      tarr = tcount.split(",");        
      $( '.tme-count' ).html(tarr.length);
  });
}

//filter by Tag
$( document).ready( function () {
  $( "#fkey" ).keypress( function(e) {
    if ( e.which == '13' ) {
      var x = document.getElementById("fkey");
      fkey = x.value;

      $( '#loader' ).show();
      $( '#assets-initial-container' ).hide();
      $( '#assets-container-show' ).hide();
      $( '#assets-filter-container' ).hide();
      $( '#initialheader' ).hide();
      var data = {
        'action': 'ajax_getTagAssets',
        'tag': fkey,
      };

      jQuery.post( ajaxurl, data, function(response) {
          //alert(response);
          $( '#loader' ).hide();
          $( '#assets-filter-container' ).show();    
          $( '#assets-filter-container' ).html(response);
          $( ".clear-filter" ).css( {"display": "block"} );
          sessionStorage.setItem( "filterTag", fkey );
      });
    }
  });
});

//filter by search
function fsearch( e ) {
  if( event.key === 'Enter' ) {           
      var x = document.getElementById("fsearch");
      fkey = x.value; 
      $( '#loader' ).show();
      $( '#assets-initial-container' ).hide();
      $( '#assets-container-show' ).hide();
      $( '#assets-filter-container' ).hide();
      $( '#initialheader' ).hide();
      var data = {
        'action': 'ajax_getTagAssets',
        'tag': e
      };

      jQuery.post( ajaxurl, data, function(response) {
          $( '#loader' ).hide();
          $( '#assets-filter-container' ).show();    
          $( '#assets-filter-container' ).html(response);
          sessionStorage.setItem( "filterTag", e );
          skey = sessionStorage.getItem( "filterTag" );
          $( '.search-field' ).val(skey);
          $( ".close-search" ).css( {"display": "block"} );
      });   
  }
}

//asset adding to media library
function saveMedia( url ){
  var data = {
    'action': 'ajax_saveToMedia',
    'asset_url': url        
  };

  jQuery.post( ajaxurl, data, function(response) {
    alert(response);
    $( '.divLoading' ).hide();
    $( "div#divLoading" ).addClass( 'hideLoader' );
  });
}

//elements push to array
function elemPush( a, arrStr ){

  var ft = [];
  if( arrStr != "" ){
    var arr = arrStr.split(",");
    for ( var i = 0; i < arr.length; i++ ){
        ft.push(arr[i]);
    }     
    var index = ft.indexOf(a);
    if ( index > -1 ) {
      ft.splice(index, 1);
      extpush(a,'false');      
    }else{
      ft.push(a);
      extpush(a,'true');
    }      
  }else{
    ft.push(a);
    extpush(a,'true');
  }

  return ft;
}

//extensions pushing
function extpush( type, res ){
  if( type == 'image' ){
    var ext = ['ai', 'bmp', 'gif', 'jpg', 'png', 'eps', 'psd', 'wmf', 'tif', 'tiff'];        
  }else if( type == 'video' ){
    var ext = ['avi', 'flv', 'mov', 'mp3', 'mp4', 'mpeg', 'wav', 'wmv'];
  }else if( type == 'other' ){
    var ext = ['doc', 'docx', 'dot', 'fla', 'indd', 'indl', 'pdf', 'ppt', 'pptx', 'qxd', 'txt', 'xls', 'xlsx', 'zip']
  }else{
    return;
  }
  
  if( ext != '' ){      
    var arrStrext = sessionStorage.getItem( "filterExtension" );
    var elem;
    if( arrStrext != "" ){
      elem = sliceExt(arrStrext, ext);
      elem = elem.filter(function(e){return e});
    }else{      
      elem = ext;
    }
    
    sessionStorage.setItem( "filterExtension", elem );
    if( res == 'true' ){
      extcheck(ext, 'true');
    }else{
      extcheck(ext, 'false');
    }
        
  }

  return true;
}

//extension checkbox checkbox
function extcheck( echk, res ){
  for ( var i = 0; i < echk.length; i++ ){
    var estr = "checkbox-ext-"+ echk[i];
    if( res == 'true' ){            
      $( "[data-id="+ estr + "]" ).prop( 'checked', true );
    }else{      
      $( "[data-id="+ estr + "]" ).prop( 'checked', false );
    }         
  }

  return true;  
}

function sliceExt( arrStrext, ext ){
  var ft = [];
  var arr = arrStrext.split(",");
  for ( var i = 0; i < arr.length; i++ ){
      ft.push(arr[i]);
  }

  for ( var i = 0; i <= ext.length; i++ ){
    var index = ft.indexOf(ext[i]);
    if ( index > -1 ) {
      ft.splice(index, 1);     
    }else{
      ft.push(ext[i]);
    }
  }  
  return ft;
}

//filter checking if condition empty
function filterTagCheck() {
  if( sessionStorage.getItem("filterType") == '' && sessionStorage.getItem("filterExtension") == '' && sessionStorage.getItem("filterGroup") == '' ){
    $( ".clear-filter" ).css( {"display": "none"} );
  }
}