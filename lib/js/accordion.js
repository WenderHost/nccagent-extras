var accordion = document.querySelector('.accordion');
accordion.addEventListener( 'click', accordionClick, false );

function accordionClick(e){
  // Bail if our clicked element doesn't have the class
  var currentToggle = e;
  if( ! currentToggle.target.classList.contains('accordion-toggle') ){
    currentToggle = e.target.parentElement;
    if( ! currentToggle.classList.contains('accordion-toggle') ) return;
  }
  var toggleId = ( currentToggle.toElement )? currentToggle.toElement.id : currentToggle.id ;

  var toggleIcon = document.querySelector('#' + toggleId + ' i.fas');
  var content = document.querySelector( '#' + toggleId + '-content' );
  if( ! content ) return;

  e.preventDefault();
  e.stopPropagation();

  // If the content is already expanded, collapse it and quit
  if (content.classList.contains('active')) {
    content.classList.remove('active');
    toggleIcon.classList.toggle('fa-chevron-down');
    toggleIcon.classList.toggle('fa-chevron-up');
    if( typeof currentToggle.classList == 'undefined' ){
      currentToggle.target.classList.remove('active');
    } else {
      currentToggle.classList.remove('active');
    }
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
  var toggleIcons = document.querySelectorAll('i.fa-chevron-up');
  for(var i = 0; i < toggleIcons.length; i++ ){
    toggleIcons[i].classList.remove('fa-chevron-up');
    toggleIcons[i].classList.add('fa-chevron-down');
  }

  // Toggle our content
  content.classList.toggle('active');
  toggleIcon.classList.toggle('fa-chevron-up');
  toggleIcon.classList.toggle('fa-chevron-down');
  //currentToggle.target.classList.toggle('active');
  if( typeof currentToggle.classList == 'undefined' ){
    currentToggle.target.classList.toggle('active');
  } else {
    currentToggle.classList.toggle('active');
  }
}
