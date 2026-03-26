import { gsap, ScrollTrigger, prefersReducedMotion } from '../shared/motion';

export const initStoryStatementModule = () => {
  const sections = document.querySelectorAll('[data-story-statement]');

  sections.forEach((section) => {
    const isMobile = window.matchMedia('(max-width: 767px)').matches;
    const lineOne = section.querySelector('[data-story-line="1"]');
    const lineTwo = section.querySelector('[data-story-line="2"]');
    const description = section.querySelector('[data-story-description]');
    const gif = section.querySelector('[data-story-gif]');

    if (!lineOne || !lineTwo || !description) {
      return;
    }

    const items = [lineOne, lineTwo, description];

    if (gif) {
      items.push(gif);
    }

    if (prefersReducedMotion) {
      gsap.set(items, { autoAlpha: 1, y: 0 });
      return;
    }

    gsap.set(items, { autoAlpha: 0, y: 120 });

    if (isMobile) {
      const mobileTimeline = gsap.timeline({
        scrollTrigger: {
          trigger: section,
          start: 'top 68%',
          once: true,
          invalidateOnRefresh: true,
        },
      })
        .to(lineOne, { autoAlpha: 1, y: 0, duration: 0.75, ease: 'power3.out' }, 0)
        .to(lineTwo, { autoAlpha: 1, y: 0, duration: 0.75, ease: 'power3.out' }, 0.16)
        .to(description, { autoAlpha: 1, y: 0, duration: 0.75, ease: 'power3.out' }, 0.32);

      if (gif) {
        mobileTimeline.to(gif, { autoAlpha: 1, y: 0, duration: 0.75, ease: 'power3.out' }, 0.5);
      }

      return;
    }

    const timeline = gsap.timeline({
      scrollTrigger: {
        trigger: section,
        start: 'top 52%',
        end: '+=140%',
        scrub: true,
        invalidateOnRefresh: true,
      },
    });

    timeline.to(lineOne, { autoAlpha: 1, y: 0, ease: 'power3.out' }, 0);
    timeline.to(lineTwo, { autoAlpha: 1, y: 0, ease: 'power3.out' }, 0.45);
    timeline.to(description, { autoAlpha: 1, y: 0, ease: 'power3.out' }, 0.9);

    if (gif) {
      timeline.to(gif, { autoAlpha: 1, y: 0, ease: 'power3.out' }, 1.25);
    }
  });

  ScrollTrigger.refresh();
};
