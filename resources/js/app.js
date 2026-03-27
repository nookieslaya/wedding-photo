import { initLenis } from './shared/lenis';
import { initHeroModule } from './modules/hero';
import { initWeddingPhotoModule } from './modules/p-wedding-photo';
import { initStoryStatementModule } from './modules/story-statement';

const safeInit = (name, fn) => {
  try {
    fn();
  } catch (error) {
    console.error(`[animated-theme] ${name} init failed`, error);
  }
};

const boot = () => {
  safeInit('lenis', initLenis);
  safeInit('hero', initHeroModule);
  safeInit('wedding-photo', initWeddingPhotoModule);
  safeInit('story-statement', initStoryStatementModule);
  window.__animatedThemeBooted = true;
};

if (document.readyState === 'loading') {
  document.addEventListener('DOMContentLoaded', boot, { once: true });
} else {
  boot();
}
