(() => {
  'use strict';
  // Page is loaded
  const objects = document.getElementsByClassName('async_background');
  Array.from(objects).map((item) => {
    // Start loading image
    const img = new Image();
    if (window.matchMedia("(max-width: 575.98px)").matches) {
      img.src = item.dataset.mobile;
      img.onload = () => {
        item.classList.remove('async_background');
        return item.nodeName === 'IMG' ?
          item.src = item.dataset.src :
          item.style.backgroundImage = `linear-gradient(rgba(0, 0, 0, 0.55), rgba(0, 0, 0, 0.65)), url(${item.dataset.mobile})`
      };
    } else if (window.matchMedia("(max-width: 970px)").matches) {
      // Once image is loaded replace the src of the HTML element
      img.src = item.dataset.tablet;
      img.onload = () => {
        item.classList.remove('async_background');
        return item.nodeName === 'IMG' ?
          item.src = item.dataset.src :
          item.style.backgroundImage = `linear-gradient(rgba(0, 0, 0, 0.55), rgba(0, 0, 0, 0.65)), url(${item.dataset.tablet})`
      };
    } else {
      // Once image is loaded replace the src of the HTML element
      img.src = item.dataset.src;
      img.onload = () => {
        item.classList.remove('async_background');
        return item.nodeName === 'IMG' ?
          item.src = item.dataset.src :
          item.style.backgroundImage = `linear-gradient(rgba(0, 0, 0, 0.55), rgba(0, 0, 0, 0.65)), url(${item.dataset.src})`
      };
    }

  });
})();
