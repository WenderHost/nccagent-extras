(function($){
  /**
   * Loads an Agent Resources directory via our API.
   *
   * @param      {string}  endpoint     The endpoint
   * @param      {string}  path         The path
   */
  function loadDirectory( endpoint, path ){
    $('div#dirlister ul').empty().append(`<li class="message">Loading...</li>`);
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
            $('div#dirlister h5').html( `<a class="doc-link" href="/">All Docs</a> &gt; ` + pathItems.join(' &gt; ') );
          } else {
            $('div#dirlister h5').html( 'All Docs' );
          }

          // Add each directory listing item to our `ul`
          $.each(data,function(i,item){
            if( ('http://vpn.ncc-agent.com/' != item.link) ){
              var cssClasses = ( -1 < item.text.indexOf('Parent Directory') )? 'button' : '';
              dirItems.push(`<li><a class="doc-link ${cssClasses}" href="${item.link}" aria-type="${item.type}">${item.text}</a></li>`);
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
      loadDirectory( wpvars.endpoint, link );
    }
  });

  // Initialize the Directory Lister
  loadDirectory( wpvars.endpoint, wpvars.path );
})(jQuery);