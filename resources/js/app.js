import { initLenis } from './shared/lenis';
import { initHeroModule } from './modules/hero';
import { initWeddingPhotoModule } from './modules/p-wedding-photo';
import { initStoryStatementModule } from './modules/story-statement';
import { initEventsListModule } from './modules/events-list';
import { initEventsSidebarContentModule } from './modules/events-sidebar-content';
import { initSiteNavigationModule } from './modules/site-navigation';
import { initFooterEffectsModule } from './modules/footer-effects';
import { initScrollIndicatorModule } from './modules/scroll-indicator';
import { initOfferModule } from './modules/offer';
import { initProductsModule } from './modules/products';

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
  safeInit('events-list', initEventsListModule);
  safeInit('events-sidebar-content', initEventsSidebarContentModule);
  safeInit('site-navigation', initSiteNavigationModule);
  safeInit('footer-effects', initFooterEffectsModule);
  safeInit('scroll-indicator', initScrollIndicatorModule);
  safeInit('offer', initOfferModule);
  safeInit('products', initProductsModule);
  window.__animatedThemeBooted = true;
};

if (document.readyState === 'loading') {
  document.addEventListener('DOMContentLoaded', boot, { once: true });
} else {
  boot();
}
