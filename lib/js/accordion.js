var accordion = document.querySelector('.accordion');
accordion.addEventListener('click', function(e){

  // Bail if our clicked element doesn't have the class
  if ( ! event.target.classList.contains('accordion-toggle') ) return;

  var toggleIcon = document.querySelector('#' + e.toElement.id + ' i.fas');
  var content = document.querySelector( '#' + e.toElement.id + '-content' );
  if( ! content ) return;

  e.preventDefault();
  // If the content is already expanded, collapse it and quit
  if (content.classList.contains('active')) {
    content.classList.remove('active');
    toggleIcon.classList.toggle('fa-plus');
    toggleIcon.classList.toggle('fa-minus');
    e.target.classList.remove('active');
    return;
  }
  // Get all open accordion content, loop through it, and close it
  var accordions = document.querySelectorAll('.accordion-content.active');
  for (var i = 0; i < accordions.length; i++) {
    accordions[i].classList.remove('active');
  }

  // Get all open accordion toggles, loop through them, and close them.
  var accordionToggles = document.querySelectorAll('.accordion-toggle.active');
  for(var i = 0; i < accordionToggles.length; i++ ){
    accordionToggles[i].classList.remove('active');
  }

  // Get all open toggle icons and close them
  var toggleIcons = document.querySelectorAll('i.fa-minus');
  for(var i = 0; i < toggleIcons.length; i++ ){
    toggleIcons[i].classList.remove('fa-minus');
    toggleIcons[i].classList.add('fa-plus');
  }

  // Toggle our content
  content.classList.toggle('active');
  toggleIcon.classList.toggle('fa-plus');
  toggleIcon.classList.toggle('fa-minus');
  e.target.classList.toggle('active');
});
