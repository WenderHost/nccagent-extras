// Listen for click on the document
document.addEventListener('click', function (event) {

  //Bail if our clicked element doesn't have the class
  if (!event.target.classList.contains('accordion-toggle')) return;

  // Get the target content
  var content = document.querySelector(event.target.hash);
  if (!content) return;

  // Prevent default link behavior
  event.preventDefault();

  // If the content is already expanded, collapse it and quit
  if (content.classList.contains('active')) {
    content.classList.remove('active');
    event.target.classList.remove('active');
    return;
  }

  // Get all open accordion content, loop through it, and close it
  var accordions = document.querySelectorAll('.accordion-content.active');
  for (var i = 0; i < accordions.length; i++) {
    accordions[i].classList.remove('active');
  }

  // Get all open accodion toggles, loop through them, and close them.
  var accordionToggles = document.querySelectorAll('.accordion-toggle.active');
  for(var i = 0; i < accordionToggles.length; i++ ){
    accordionToggles[i].classList.remove('active');
  }

  // Toggle our content
  content.classList.toggle('active');
  event.target.classList.toggle('active');
})