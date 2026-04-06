import { prefersReducedMotion } from '../shared/motion';
import { runTextScramble } from '../shared/text-scramble';

export const initEventsSidebarContentModule = () => {
  if (prefersReducedMotion) {
    return;
  }

  const modules = document.querySelectorAll('[data-events-sidebar-content]');

  modules.forEach((module) => {
    const trigger = module.querySelector('[data-events-back-link]');
    const label = module.querySelector('[data-events-back-link-label]');

    if (!trigger || !label) {
      return;
    }

    trigger.addEventListener('mouseenter', () => {
      runTextScramble(label);
    });
  });
};
