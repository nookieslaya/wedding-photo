import Lenis from 'lenis';
import { prefersReducedMotion, ScrollTrigger } from './motion';

export const initLenis = () => {
  if (prefersReducedMotion) {
    return;
  }

  const lenis = new Lenis({
    duration: 1.1,
    smoothWheel: true,
  });

  lenis.on('scroll', ScrollTrigger.update);

  const raf = (time) => {
    lenis.raf(time);
    requestAnimationFrame(raf);
  };

  requestAnimationFrame(raf);
};
