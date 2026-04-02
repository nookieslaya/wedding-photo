import { gsap, prefersReducedMotion } from '../shared/motion';

const SCRAMBLE_CHARS = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
const scrambleRafs = new WeakMap();

const stopScramble = (element) => {
  const rafId = scrambleRafs.get(element);
  if (rafId) {
    cancelAnimationFrame(rafId);
    scrambleRafs.delete(element);
  }
};

const runTitleScramble = (element) => {
  if (!element) {
    return;
  }

  const original = element.dataset.originalTitle ?? element.textContent ?? '';
  element.dataset.originalTitle = original;
  stopScramble(element);

  const chars = Array.from(original);
  const start = performance.now();
  const duration = 1000;

  const isScrambleCandidate = (char) => /[A-Za-z0-9]/.test(char);
  const randomChar = () => SCRAMBLE_CHARS[Math.floor(Math.random() * SCRAMBLE_CHARS.length)];

  const tick = (now) => {
    const elapsed = now - start;
    const progress = Math.min(1, elapsed / duration);
    const revealCount = Math.floor(progress * chars.length);

    const scrambled = chars.map((char, index) => {
      if (!isScrambleCandidate(char)) {
        return char;
      }
      return index <= revealCount ? char : randomChar();
    }).join('');

    element.textContent = scrambled;

    if (progress < 1) {
      const rafId = requestAnimationFrame(tick);
      scrambleRafs.set(element, rafId);
    } else {
      element.textContent = original;
      scrambleRafs.delete(element);
    }
  };

  const rafId = requestAnimationFrame(tick);
  scrambleRafs.set(element, rafId);
};

export const initEventsListModule = () => {
  const sections = document.querySelectorAll('[data-events-module]');

  sections.forEach((section) => {
    const isMobile = window.matchMedia('(max-width: 767px)').matches;
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

      const applyReveal = () => {
        colorLayer.style.clipPath = `circle(${state.radius}px at ${state.x}px ${state.y}px)`;
      };

      gsap.set(colorLayer, { autoAlpha: 1 });
      applyReveal();

      if (isMobile || prefersReducedMotion) {
        return;
      }

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

        runTitleScramble(title);

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
