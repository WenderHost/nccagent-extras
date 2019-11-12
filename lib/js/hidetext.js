/* Hide text with a link to Read More... */
(function($){
    function hideText( textselector, strlen, moretext ){
      strlen = typeof strlen !== 'undefined' ? strlen : 100;
      moretext = typeof moretext !== 'undefined' ? moretext : 'Read More';

      var sections = $( textselector );
      for(var i = 0; i < sections.length; i++ ){
          var textToHide = $( sections[i] ).html();
          var textToCheck = $( sections[i] ).text().substring(strlen);
          if( '' == textToCheck )
              continue;
          var visibleText = $( sections[i] ).text().substring(0, strlen);
          // ('<span class="visible-text">' + visibleText + '</span>') +
          $( sections[i] )
              .html(('<span class="hidden-text">' + textToHide + '</span>'))
              .append('<span class="read-more"><a href="#" title="' + moretext + '" style="cursor: pointer;">' + moretext + '</a></spam>')
              .click(function(e) {
                e.preventDefault();
                $(this).find('span.hidden-text').slideDown();
                $(this).find('span.read-more').hide();
                $(this).find('span.visible-text').hide();
              });
          $( sections[i] ).find( 'span.hidden-text' ).hide();
      }
    }

    hideText( 'div.hidetext', 200, `Read More about ${hideTextVars.carrier} &hellip;` );
})(jQuery);