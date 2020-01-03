(function($){
  /**
   * Loads an Agent Resources directory via our API.
   *
   * @param      {string}  endpoint     The endpoint
   * @param      {string}  path         The path
   */
  function loadDirectory( endpoint, path ){
    $.get( endpoint, {path: path}, function( response ){
      console.log('🔔 loadDirectory response',  response );
      if( 0 < response.data.length ){
        var data = response.data;
        var dirItems = [];
        var pathItems = [];

        // Generate a path breadcrumb
        if( 0 < response.path_array.length && '' != response.path_array[0] ){
          var pathLink = '';
          $.each(response.path_array,function(i,item){
            console.log('👉 i = ', i, "\n👉 item = ", item);
            pathLink+= '/' + item;
            pathItems.push( `<a href="${pathLink}">` + decodeURI(item) + `</a>` );
          });
          $('div#dirlister h5').html( './' + pathItems.join('/') );
        } else {
          $('div#dirlister h5').html( './' );
        }

        // Add each directory listing item to our `ul`
        $.each(data,function(i,item){
          if( ('http://vpn.ncc-agent.com/' != item.link) )
            dirItems.push(`<li><a href="${item.link}" aria-type="${item.type}">${item.text}</a></li>`);
        });
        $('div#dirlister ul').empty().append(dirItems.join(''));
      }
    });
  }

  // Attach a click handler to our Directory listing links:
  $('#dirlister').on('click', 'a', function(e){
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