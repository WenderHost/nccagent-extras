(function($){
  /**
   * Loads an Agent Resources directory via our API.
   *
   * @param      {string}  endpoint     The endpoint
   * @param      {string}  path         The path
   */
  function loadDirectory( endpoint, path ){
    $('div#dirlister ul').empty().append(`<li class="message loading">One moment...</li>`);
    var loadDirXHR = $.ajax({
      url: endpoint,
      data: {path: path, _wpnonce: wpvars.nonce},
      success: function( response ){
        //----//
        if( 0 < response.data.length ){
          var data = response.data;
          var dirItems = [];
          var pathItems = [];

          // Generate a path breadcrumb
          if( 0 < response.path_array.length && '' != response.path_array[0] ){
            var pathLink = '';
            $.each(response.path_array,function(i,item){
              pathLink+= '/' + item;
              pathItems.push( `<a class="doc-link" href="${pathLink}">` + decodeURI(item) + `</a>` );
            });
            $('div#dirlister h5').html( `<a class="doc-link" href="/">All Carriers</a> / ` + pathItems.join(' / ') );
            $('#back-button').addClass('doc-link');
            $('#back-button').removeClass('disabled');
          } else {
            $('div#dirlister h5').html( 'All Carriers' );
            $('#back-button').removeClass('doc-link');
            $('#back-button').addClass('disabled');
          }

          // Add each directory listing item to our `ul`
          $.each(data,function(i,item){
            if( ('http://vpn.ncc-agent.com/' != item.link) ){
              if( -1 < item.text.indexOf('Back') ){
                $('#back-button').attr('href', item.link);
              } else {
                var cssClasses = ( -1 < item.text.indexOf('Back') )? 'button' : '';
                var linkText = item.text.toLowerCase();
                if( 2 === data.length && -1 < linkText.indexOf('product by state')  ){
                  dirItems.push(`<li class="state-link"><a class="doc-link ${cssClasses}" href="${item.link}" aria-type="${item.type}"><span>${response.carrier} Products by State</span></a></li>`);
                } else {
                  // <i class="far fa-file"></i>
                  var icon = ( 'file' === item.type )? 'fa-file-alt' : 'fa-folder' ;
                  dirItems.push(`<li><a class="doc-link ${cssClasses}" href="${item.link}" aria-type="${item.type}"><i class="fas ${icon}"></i> ${item.text}</a></li>`);
                }

              }
            }
          });
          $('div#dirlister ul').empty().append(dirItems.join(''));
        }
        //----//
      },
      error: function( response ){
        console.log('🚨 Error. response = ', response );
        $('div#dirlister ul').empty().append(`<li class="message">${response.responseJSON.message}</li>`);
      }
    });
  }

  // Attach a click handler to our Directory listing links:
  $('#dirlister').on('click', 'a.doc-link', function(e){
    e.preventDefault();
    var link = $(this).attr('href');
    var type = $(this).attr('aria-type');
    if( 'file' === type ){
      window.open(link, '_blank');
    } else {
      $('div#dirlister ul').empty().append(`<li class="message loading">One moment...</li>`);
      $('html, body').animate({scrollTop: 0}, 800,function(){
        loadDirectory( wpvars.endpoint, link );
      });
    }
  });

  $('#dirlister').on('click', 'a.disabled', function(e){
    e.preventDefault();
  });

  // Initialize the Directory Lister
  loadDirectory( wpvars.endpoint, wpvars.path );
})(jQuery);