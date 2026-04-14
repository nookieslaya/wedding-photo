import { prefersReducedMotion } from '../shared/motion';
import { runTextScramble } from '../shared/text-scramble';

export const initProductsModule = () => {
  const modules = document.querySelectorAll('[data-products-module]');

  if (prefersReducedMotion || !modules.length) {
    return;
  }

  modules.forEach((module) => {
    const cards = module.querySelectorAll('[data-products-card]');

    cards.forEach((card) => {
      const title = card.querySelector('[data-products-card-title]');
      if (!title) {
        return;
      }

      card.addEventListener('mouseenter', () => {
        runTextScramble(title, { duration: 1100 });
      });
      card.addEventListener('focusin', () => {
        runTextScramble(title, { duration: 1100 });
      });
    });
  });
};
