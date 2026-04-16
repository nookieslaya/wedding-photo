import { gsap, prefersReducedMotion } from '../shared/motion';
import { runTextScramble, TEXT_SCRAMBLE_SETTINGS } from '../shared/text-scramble';

export const initEventsListModule = () => {
  const sections = document.querySelectorAll('[data-events-module]');

  sections.forEach((section) => {
    const isMobile = window.matchMedia('(max-width: 1023px)').matches;
    const cards = section.querySelectorAll('[data-events-item]');

    cards.forEach((card) => {
      const imageShell = card.querySelector('[data-events-image-shell]');
      const colorLayer = card.querySelector('[data-events-image-color]');
      const title = card.querySelector('[data-events-title]');

      if (!imageShell || !colorLayer || !title) {
        return;
      }

      const state = {
        radius: 0,
        x: imageShell.clientWidth * 0.5,
        y: imageShell.clientHeight * 0.5,
      };
      let titleUnlockTimer = null;

      const applyReveal = () => {
        colorLayer.style.clipPath = `circle(${state.radius}px at ${state.x}px ${state.y}px)`;
      };

      if (isMobile || prefersReducedMotion) {
        gsap.set(colorLayer, { clearProps: 'all' });
        return;
      }

      gsap.set(colorLayer, { autoAlpha: 1 });
      applyReveal();

      const updatePointer = (event) => {
        const rect = imageShell.getBoundingClientRect();
        state.x = event.clientX - rect.left;
        state.y = event.clientY - rect.top;
      };

      const maxRadius = () => {
        const rect = imageShell.getBoundingClientRect();
        return Math.hypot(Math.max(state.x, rect.width - state.x), Math.max(state.y, rect.height - state.y));
      };

      card.addEventListener('mouseenter', (event) => {
        updatePointer(event);
        gsap.killTweensOf(state);
        state.radius = 0;
        applyReveal();

        if (titleUnlockTimer) {
          clearTimeout(titleUnlockTimer);
        }

        const lockedTitleHeight = Math.ceil(title.getBoundingClientRect().height);
        gsap.set(title, {
          minHeight: lockedTitleHeight,
          maxHeight: lockedTitleHeight,
          overflow: 'hidden',
        });

        runTextScramble(title);

        titleUnlockTimer = window.setTimeout(() => {
          gsap.set(title, { clearProps: 'minHeight,maxHeight,overflow' });
          titleUnlockTimer = null;
        }, TEXT_SCRAMBLE_SETTINGS.duration + 60);

        gsap.to(colorLayer, {
          autoAlpha: 1,
          duration: 0.16,
          ease: 'none',
          overwrite: true,
        });

        gsap.to(state, {
          radius: maxRadius(),
          duration: 2,
          ease: 'power2.out',
          overwrite: true,
          onUpdate: applyReveal,
        });
      });

      imageShell.addEventListener('mousemove', (event) => {
        updatePointer(event);
        applyReveal();
      });

      card.addEventListener('mouseleave', () => {
        if (titleUnlockTimer) {
          clearTimeout(titleUnlockTimer);
          titleUnlockTimer = null;
        }
        gsap.set(title, { clearProps: 'minHeight,maxHeight,overflow' });

        gsap.killTweensOf(state);
        gsap.to(colorLayer, {
          autoAlpha: 0,
          duration: 2,
          ease: 'power2.out',
          overwrite: true,
          onComplete: () => {
            state.radius = 0;
            applyReveal();
          },
        });
      });
    });
  });
};
