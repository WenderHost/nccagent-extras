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
        console.log('Building list items.');
        var data = response.data;
        var items = [];
        $.each(data,function(i,item){
          if( ('http://vpn.ncc-agent.com/' != item.link) )
            items.push(`<li><a href="${item.link}" aria-type="${item.type}">${item.text}</a></li>`);
        });
        //$('div#dirlister #carrier').html( response.carrier );
        $('div#dirlister h5').html( decodeURI(response.path) );
        $('div#dirlister ul').empty().append(items.join(''));
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