/* Accordion Menus */
(function($){

  // Slide up all sub-navs
  $('ul.menu > li.menu-item-has-children:not(.current-page-ancestor):not(.current-menu-ancestor):not(.current-menu-item) ul.sub-menu').hide();

  // Add the .active class for any sub-navs related to our current page
  //$('li.current-page-ancestor, li.current-menu-ancestor, ul.sub-menu li.menu-item-has-children').addClass('active'); // , ul.sub-menu li.menu-item-has-children

  // Add the .active class to any Parent Links with open sub-navs
  const parentLinks = $('li.current-page-ancestor, li.current-menu-ancestor, ul.sub-menu li.menu-item-has-children');
  $.each( parentLinks, function(){
    var childNav = $(this).find('> ul.sub-menu');
    if( $(childNav[0]).is(":visible") )
      $(this).addClass('active');
  });

  // Make 1st level sub-nav parent buttons clickable
  $('.elementor-element.subnav').on('click', 'ul.menu li.menu-item-has-children > a', function(e){
    e.preventDefault();
    const parent = $(this).parent();
    $(parent).toggleClass('active');

    const parentId = $(parent).attr('id');
    $('#' + parentId + ' ul.sub-menu').slideToggle();
  });
})(jQuery);