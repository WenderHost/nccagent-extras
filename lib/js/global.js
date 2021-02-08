(function($){
  $('.jet-menu').on('click', 'a.top-level-link', function(e){
      var href = $(this).attr('href');
      if( '#' === href )
        e.preventDefault();
    });

    /* HubSpot Chat */
    $('body').on('click', '.chat-link', function(e){
      // Prevent default behavior of the hyperlink
      e.preventDefault();

      // Get the chatflow query parameter from the link
      const chatQueryParameter = $(this).attr('data-chat-query-parameter');

      if (window.HubSpotConversations) {
        const status = window.HubSpotConversations.widget.status();
        console.log(`HS Chat loaded = ${status.loaded}` );

        // Add the $chatQueryParameter to the browser URL
        if (history.pushState) {
            var newurl = window.location.protocol + "//" + window.location.host + window.location.pathname + '?chat=' + chatQueryParameter;
            window.history.pushState({path:newurl},'',newurl);
        }
        // Open the HubSpot Chat Widget
        if( ! status.loaded ){
          window.HubSpotConversations.widget.load();
        } else {
          window.HubSpotConversations.widget.refresh();
        }
      } else {
        console.log('Please install HubSpot WordPress plugin');
      }
    });
})(jQuery);
