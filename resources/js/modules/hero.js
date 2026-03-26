import { gsap, ScrollTrigger, prefersReducedMotion } from '../shared/motion';

export const initHeroModule = () => {
  const heroes = document.querySelectorAll('[data-hero]');

  heroes.forEach((hero) => {
    const reveals = hero.querySelectorAll('[data-hero-reveal]');
    const image = hero.querySelector('[data-hero-image]');
    const media = hero.querySelector('[data-hero-media]');
    const titleTrack = hero.querySelector('[data-hero-title-track]');
    const overlay = hero.querySelector('[data-hero-overlay]');

    if (!prefersReducedMotion) {
      gsap.set(reveals, { autoAlpha: 0, y: 30 });

      gsap.to(reveals, {
        autoAlpha: 1,
        y: 0,
        duration: 1,
        ease: 'power3.out',
        stagger: 0.12,
      });
    }

    if (!image || !media || !titleTrack || !overlay || prefersReducedMotion) {
      return;
    }

    const isMobileViewport = () => window.matchMedia('(max-width: 767px)').matches;
    const imageShift = () => {
      const mediaHeight = media.getBoundingClientRect().height;
      const imageHeight = image.getBoundingClientRect().height;
      const fullAvailableShift = Math.max(0, imageHeight - mediaHeight);
      const desktopTravelRatio = 1 / 2;
      const isMobile = isMobileViewport();

      // Keep bottom crop on desktop by travelling only part of the available range.
      return isMobile
        ? Math.max(mediaHeight * 0.12, fullAvailableShift * 0.75)
        : fullAvailableShift * desktopTravelRatio;
    };

    const titleShift = () => {
      const mediaRect = media.getBoundingClientRect();
      const titleRect = titleTrack.getBoundingClientRect();
      const initialTop = titleRect.top - mediaRect.top;
      const isMobile = isMobileViewport();
      const stopOffset = isMobile ? 120 : 200;
      const maxTop = mediaRect.height - titleRect.height - stopOffset;
      const availableShift = Math.max(0, maxTop - initialTop);

      return availableShift;
    };

    gsap
      .timeline({
        scrollTrigger: {
          trigger: hero,
          start: 'top top',
          // Finish hero image travel before the next section starts covering it
          end: () => (isMobileViewport() ? '+=50%' : '+=58%'),
          scrub: 1.2,
          invalidateOnRefresh: true,
        },
      })
      .fromTo(
        image,
        { y: 0 },
        { y: () => -imageShift(), ease: 'none', duration: 1 },
        0,
      )
      .fromTo(
        titleTrack,
        { y: 0 },
        { y: () => titleShift(), ease: 'none', duration: () => (isMobileViewport() ? 0.9 : 1) },
        0,
      )
      .fromTo(
        media,
        { y: 0, clipPath: 'inset(0% 0% 0% 0% round 0px)' },
        {
          y: () => (isMobileViewport() ? 0 : -90),
          clipPath: () =>
            isMobileViewport()
              ? 'inset(0% 0% 0% 0% round 0px)'
              : 'inset(0% 6vw 0% 6vw round 14px)',
          ease: 'none',
          duration: 1,
        },
        0,
      )
      .fromTo(
        overlay,
        { opacity: () => (isMobileViewport() ? 0 : 0) },
        { opacity: () => (isMobileViewport() ? 0 : 1), ease: 'none', duration: 0.28 },
        0.72,
      );
  });

  ScrollTrigger.refresh();
};
