// Smooth scroll
;
(function (window, document) {
  // Add scrolling animation
  document.querySelectorAll('a[href^="#"]').forEach(function (anchor) {
    anchor.addEventListener('click', function (e) {
      e.preventDefault()
      removeCurrentSelectedClass()
      if (this.id === 'logo-link') {
        document.getElementById('top-link').classList.add('selected');
      } else {
        this.classList.add('selected')
      }
    })
  })

  var scroll = new SmoothScroll('a[href*="#"]', {
    offset: 150
  })

  function removeCurrentSelectedClass() {
    document.querySelectorAll('a[href^="#"]').forEach(function (anchor) {
      anchor.classList.remove('selected')
    })
  }

})(window, document)