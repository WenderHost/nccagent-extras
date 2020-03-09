(function($){
  $('.jet-menu').on('click', 'a.top-level-link', function(e){
      var href = $(this).attr('href');
      if( '#' === href )
        e.preventDefault();
    });
})(jQuery);