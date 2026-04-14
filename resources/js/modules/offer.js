import { prefersReducedMotion } from '../shared/motion';
import { runTextScramble } from '../shared/text-scramble';

export const initOfferModule = () => {
  const modules = document.querySelectorAll('[data-offer-module]');

  modules.forEach((module) => {
    const cards = module.querySelectorAll('[data-offer-card]');

    if (prefersReducedMotion) {
      return;
    }

    cards.forEach((card) => {
      const title = card.querySelector('[data-offer-card-title]');
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
