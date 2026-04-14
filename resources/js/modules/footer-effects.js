import { prefersReducedMotion } from '../shared/motion';
import { runTextScramble } from '../shared/text-scramble';

export const initFooterEffectsModule = () => {
  const footer = document.querySelector('[data-site-footer]');

  if (!footer) {
    return;
  }

  const nav = footer.querySelector('[data-footer-nav]');
  const barsContainer = footer.querySelector('[data-footer-bars]');
  const navLinks = footer.querySelectorAll('[data-footer-link]');
  const animatedLinks = footer.querySelectorAll('[data-footer-anim-link]');
  const desktopQuery = window.matchMedia('(min-width: 768px)');
  let bars = [];

  const lockLinkWidths = () => {
    navLinks.forEach((link) => {
      link.style.removeProperty('width');
      link.style.removeProperty('display');
      link.style.removeProperty('white-space');
      link.style.removeProperty('text-align');
    });

    if (!desktopQuery.matches) {
      return;
    }

    navLinks.forEach((link) => {
      const width = Math.ceil(link.getBoundingClientRect().width);
      link.style.display = 'inline-block';
      link.style.width = `${width}px`;
      link.style.whiteSpace = 'nowrap';
      link.style.textAlign = 'center';
    });
  };

  const resetBars = () => {
    bars.forEach((bar) => {
      bar.style.height = '5px';
      bar.style.opacity = '0.36';
      bar.style.backgroundColor = '#ffffff';
    });
  };

  const updateBarsByPointer = (clientX) => {
    if (!bars.length || !barsContainer) {
      return;
    }

    const rect = barsContainer.getBoundingClientRect();
    const x = Math.max(0, Math.min(rect.width, clientX - rect.left));
    const center = (x / Math.max(1, rect.width)) * (bars.length - 1);
    const sigma = 1.15;
    const baseHeight = 5;
    const peakHeight = 16;

    bars.forEach((bar, index) => {
      const distance = Math.abs(index - center);
      const influence = Math.exp(-(distance * distance) / (2 * sigma * sigma));
      const height = Math.round(baseHeight + influence * (peakHeight - baseHeight));
      const opacity = 0.36 + influence * 0.64;
      bar.style.height = `${height}px`;
      bar.style.opacity = `${opacity}`;
      bar.style.backgroundColor = distance < 0.55 ? '#ef4444' : '#ffffff';
    });
  };

  const buildBars = () => {
    if (!barsContainer) {
      return;
    }

    barsContainer.innerHTML = '';
    bars = [];

    if (!desktopQuery.matches) {
      return;
    }

    const width = Math.max(1, barsContainer.getBoundingClientRect().width);
    const count = Math.max(28, Math.floor(width / 12));

    for (let i = 0; i < count; i += 1) {
      const bar = document.createElement('span');
      bar.className = 'block w-[2px] rounded-full transition-[height,opacity,background-color] duration-150 ease-out';
      bar.style.height = '5px';
      bar.style.opacity = '0.36';
      bar.style.backgroundColor = '#ffffff';
      barsContainer.appendChild(bar);
      bars.push(bar);
    }
  };

  animatedLinks.forEach((link) => {
    if (!prefersReducedMotion) {
      link.addEventListener('mouseenter', () => runTextScramble(link, { duration: 1100 }));
      link.addEventListener('focus', () => runTextScramble(link, { duration: 1100 }));
    }
  });

  if (nav && barsContainer) {
    nav.addEventListener('mousemove', (event) => {
      if (!desktopQuery.matches || prefersReducedMotion) {
        return;
      }
      updateBarsByPointer(event.clientX);
    });

    nav.addEventListener('mouseenter', (event) => {
      if (!desktopQuery.matches || prefersReducedMotion) {
        return;
      }
      updateBarsByPointer(event.clientX);
    });

    nav.addEventListener('mouseleave', resetBars);
  }

  lockLinkWidths();
  buildBars();
  resetBars();

  window.addEventListener('load', () => {
    lockLinkWidths();
    buildBars();
    resetBars();
  }, { once: true });

  window.addEventListener('resize', () => {
    lockLinkWidths();
    buildBars();
    resetBars();
  });
};
