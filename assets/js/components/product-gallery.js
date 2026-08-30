/**
 * Product Gallery & Interactive Preview Component
 */

export function initProductGallery() {
  const mainImage = document.getElementById('main-product-image');
  const thumbs = document.querySelectorAll('.gallery-thumb');

  if (!mainImage || !thumbs.length) return;

  thumbs.forEach(thumb => {
    thumb.addEventListener('click', () => {
      const targetSrc = thumb.getAttribute('data-image-src');
      if (targetSrc && mainImage.src !== targetSrc) {
        mainImage.style.opacity = '0.4';
        setTimeout(() => {
          mainImage.src = targetSrc;
          mainImage.style.opacity = '1';
        }, 150);

        thumbs.forEach(t => t.classList.remove('active'));
        thumb.classList.add('active');
      }
    });
  });
}
