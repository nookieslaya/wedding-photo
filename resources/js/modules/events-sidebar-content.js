import { prefersReducedMotion } from '../shared/motion';
import { runTextScramble } from '../shared/text-scramble';

export const initEventsSidebarContentModule = () => {
  const modules = document.querySelectorAll('[data-events-sidebar-content]');

  modules.forEach((module) => {
    const trigger = module.querySelector('[data-events-back-link]');
    const label = module.querySelector('[data-events-back-link-label]');

    if (trigger && label && !prefersReducedMotion) {
      trigger.addEventListener('mouseenter', () => {
        runTextScramble(label);
      });
    }

    const lightbox = module.querySelector('[data-events-lightbox]');
    const items = Array.from(module.querySelectorAll('[data-events-lightbox-item]'));

    if (!lightbox || items.length === 0) {
      return;
    }

    const closeButton = lightbox.querySelector('[data-events-lightbox-close]');
    const prevButton = lightbox.querySelector('[data-events-lightbox-prev]');
    const nextButton = lightbox.querySelector('[data-events-lightbox-next]');
    const image = lightbox.querySelector('[data-events-lightbox-image]');
    const caption = lightbox.querySelector('[data-events-lightbox-caption]');

    if (!image || !caption || !closeButton || !prevButton || !nextButton) {
      return;
    }

    let activeIndex = 0;

    const renderActiveItem = () => {
      const item = items[activeIndex];
      if (!item) {
        return;
      }

      const src = item.getAttribute('data-events-lightbox-src') || '';
      const alt = item.getAttribute('data-events-lightbox-alt') || '';
      const text = item.getAttribute('data-events-lightbox-caption') || '';
      image.setAttribute('src', src);
      image.setAttribute('alt', alt);
      caption.textContent = text;
    };

    const openLightbox = (index) => {
      activeIndex = index;
      renderActiveItem();
      lightbox.classList.remove('hidden');
      lightbox.classList.add('flex');
      lightbox.setAttribute('aria-hidden', 'false');
      document.body.classList.add('overflow-hidden');
    };

    const closeLightbox = () => {
      lightbox.classList.add('hidden');
      lightbox.classList.remove('flex');
      lightbox.setAttribute('aria-hidden', 'true');
      document.body.classList.remove('overflow-hidden');
    };

    const goToPrevious = () => {
      activeIndex = (activeIndex - 1 + items.length) % items.length;
      renderActiveItem();
    };

    const goToNext = () => {
      activeIndex = (activeIndex + 1) % items.length;
      renderActiveItem();
    };

    items.forEach((item, index) => {
      item.addEventListener('click', () => {
        openLightbox(index);
      });
    });

    closeButton.addEventListener('click', closeLightbox);
    prevButton.addEventListener('click', goToPrevious);
    nextButton.addEventListener('click', goToNext);

    lightbox.addEventListener('click', (event) => {
      if (event.target === lightbox) {
        closeLightbox();
      }
    });

    document.addEventListener('keydown', (event) => {
      if (lightbox.classList.contains('hidden')) {
        return;
      }

      if (event.key === 'Escape') {
        closeLightbox();
      } else if (event.key === 'ArrowLeft') {
        goToPrevious();
      } else if (event.key === 'ArrowRight') {
        goToNext();
      }
    });
  });
};
