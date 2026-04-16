import { runTextScramble } from '../shared/text-scramble';

export const initPostsModule = () => {
  const titles = document.querySelectorAll('[data-post-card-title]');

  titles.forEach((title) => {
    if (title.dataset.postsBound === '1') {
      return;
    }

    const trigger = title.closest('[data-post-card]') ?? title;
    const run = () => runTextScramble(title, { duration: 1100 });

    trigger.addEventListener('mouseenter', run);
    trigger.addEventListener('focusin', run);
    title.dataset.postsBound = '1';
  });
};
