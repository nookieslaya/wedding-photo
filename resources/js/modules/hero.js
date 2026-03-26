import { gsap, ScrollTrigger, prefersReducedMotion } from '../shared/motion';

export const initHeroModule = () => {
  const heroes = document.querySelectorAll('[data-hero]');

  heroes.forEach((hero) => {
    const reveals = hero.querySelectorAll('[data-hero-reveal]');
    const image = hero.querySelector('[data-hero-image]');
    const media = hero.querySelector('[data-hero-media]');
    const titleTrack = hero.querySelector('[data-hero-title-track]');

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

    if (!image || !media || !titleTrack || prefersReducedMotion) {
      return;
    }

    const isMobile = window.matchMedia('(max-width: 767px)').matches;
    const imageShift = () => {
      const mediaHeight = media.getBoundingClientRect().height;
      const imageHeight = image.getBoundingClientRect().height;
      const fullAvailableShift = Math.max(0, imageHeight - mediaHeight);

      // On desktop we want full image travel; on mobile keep a slightly softer move.
      return isMobile ? Math.max(mediaHeight * 0.12, fullAvailableShift * 0.75) : fullAvailableShift;
    };

    const titleShift = () => {
      const mediaRect = media.getBoundingClientRect();
      const titleRect = titleTrack.getBoundingClientRect();
      const initialTop = titleRect.top - mediaRect.top;
      const maxTop = mediaRect.height - titleRect.height - 200;

      return Math.max(0, maxTop - initialTop);
    };

    gsap.timeline({
      scrollTrigger: {
        trigger: hero,
        start: 'top top',
        // Finish hero image travel before the next section starts covering it
        end: () => (isMobile ? '+=50%' : '+=120%'),
        scrub: true,
        invalidateOnRefresh: true,
      },
    })
      .fromTo(image, { y: 0 }, { y: () => -imageShift(), ease: 'none', duration: 1 }, 0)
      .fromTo(titleTrack, { y: 0 }, { y: () => titleShift(), ease: 'none', duration: 1 }, 0)
      .fromTo(
        media,
        { clipPath: 'inset(0% 0% 0% 0% round 0px)' },
        {
          clipPath: () => (isMobile ? 'inset(0% 0% 0% 0% round 0px)' : 'inset(0% 6vw 0% 6vw round 14px)'),
          ease: 'none',
          duration: 1,
        },
        0
      );
  });

  ScrollTrigger.refresh();
};
