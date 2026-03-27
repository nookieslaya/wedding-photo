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

    gsap.set(gallery, { autoAlpha: 0, y: 70 });
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

    timeline.fromTo(
      title,
      {
        scale: isMobile() ? 1 : 1.25,
        y: 0,
        opacity: 1,
      },
      {
        scale: isMobile() ? 0.72 : 0.42,
        y: isMobile() ? -80 : -220,
        opacity: isMobile() ? 0.2 : 0.14,
        ease: 'none',
        duration: 1,
      },
      0
    );

    timeline.fromTo(
      gallery,
      { autoAlpha: 0, y: 70 },
      { autoAlpha: 1, y: 0, ease: 'none', duration: 0.44 },
      0.34
    );

    timeline.to(
      items,
      {
        autoAlpha: 1,
        y: 0,
        ease: 'none',
        stagger: isMobile() ? 0.05 : 0.08,
        duration: 0.38,
      },
      0.44
    );
  });

  ScrollTrigger.refresh();
};
