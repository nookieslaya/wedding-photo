import { gsap, ScrollTrigger, prefersReducedMotion } from '../shared/motion';

export const initWeddingPhotoModule = () => {
  const sections = document.querySelectorAll('[data-wedding-photo]');

  sections.forEach((section) => {
    const titleWrap = section.querySelector('[data-wedding-title-wrap]');
    const title = section.querySelector('[data-wedding-title]');
    const gallery = section.querySelector('[data-wedding-gallery]');
    const items = section.querySelectorAll('[data-wedding-item]');

    if (!titleWrap || !title || !gallery) {
      return;
    }

    const isMobile = () => window.matchMedia('(max-width: 767px)').matches;

    if (prefersReducedMotion) {
      gsap.set([titleWrap, gallery, ...items], { clearProps: 'all' });
      return;
    }

    if (isMobile()) {
      gsap.set([titleWrap, gallery], { clearProps: 'all' });
      gsap.set(items, { autoAlpha: 0, y: 48 });
      gsap.to(items, {
        autoAlpha: 1,
        y: 0,
        ease: 'power2.out',
        duration: 0.5,
        stagger: 0.08,
      });

      items.forEach((item) => {
        const image = item.querySelector('img');
        if (!image) {
          return;
        }

        item.addEventListener('click', () => {
          image.classList.toggle('grayscale-0');
        });
      });

      return;
    }

    gsap.set(gallery, { autoAlpha: 1, y: isMobile() ? 120 : 180 });
    gsap.set(items, { autoAlpha: 0, y: 40 });

    const timeline = gsap.timeline({
      scrollTrigger: {
        trigger: section,
        start: 'top top',
        end: () => (isMobile() ? '+=160%' : '+=220%'),
        scrub: 1,
        invalidateOnRefresh: true,
      },
    });

    const titleSettleAt = 0.56;
    const galleryRevealAt = 0.66;
    const itemsRevealAt = 0.5;

    timeline.fromTo(
      title,
      {
        scale: isMobile() ? 1 : 1.25,
        y: 0,
        opacity: 1,
      },
      {
        scale: isMobile() ? 0.72 : 0.42,
        y: isMobile() ? 170 : 260,
        opacity: isMobile() ? 0 : 1,
        ease: 'none',
        duration: titleSettleAt,
      },
      0,
    );

    timeline.fromTo(
      gallery,
      { y: () => (isMobile() ? 120 : 180) },
      { y: 0, ease: 'power2.out', duration: 0.44 },
      galleryRevealAt,
    );

    timeline.to(
      items,
      {
        autoAlpha: 1,
        y: 0,
        ease: 'none',
        stagger: isMobile() ? 0.05 : 0.08,
        duration: 0.1,
      },
      itemsRevealAt,
    );
  });

  ScrollTrigger.refresh();
};
