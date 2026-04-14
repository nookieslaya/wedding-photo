import { prefersReducedMotion } from '../shared/motion';
import { runTextScramble } from '../shared/text-scramble';

export const initSiteNavigationModule = () => {
  const header = document.querySelector('[data-site-header]');

  if (!header) {
    return;
  }

  const panel = header.querySelector('[data-mobile-nav-panel]');
  const openButton = header.querySelector('[data-nav-toggle]');
  const closeButton = header.querySelector('[data-nav-close]');
  const links = header.querySelectorAll('[data-nav-link]');
  const desktopNav = header.querySelector('[data-desktop-nav]');
  const barsContainer = header.querySelector('[data-nav-bars]');
  const desktopQuery = window.matchMedia('(min-width: 768px)');
  let bars = [];

  const resetBars = () => {
    bars.forEach((bar) => {
      bar.style.height = '5px';
      bar.style.opacity = '0.42';
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
      const opacity = 0.42 + influence * 0.58;
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
      bar.className = 'block w-[2px] rounded-full bg-white transition-[height,opacity] duration-150 ease-out';
      bar.style.height = '5px';
      bar.style.opacity = '0.42';
      barsContainer.appendChild(bar);
      bars.push(bar);
    }
  };

  const lockDesktopLinkWidths = () => {
    links.forEach((link) => {
      link.style.removeProperty('width');
      link.style.removeProperty('display');
      link.style.removeProperty('white-space');
      link.style.removeProperty('text-align');
    });

    if (!desktopQuery.matches) {
      return;
    }

    links.forEach((link) => {
      const width = Math.ceil(link.getBoundingClientRect().width);
      link.style.display = 'inline-block';
      link.style.width = `${width}px`;
      link.style.whiteSpace = 'nowrap';
      link.style.textAlign = 'center';
    });
  };

  const closeMobileNav = () => {
    if (!panel || !openButton) {
      return;
    }

    panel.classList.add('hidden');
    panel.classList.remove('pointer-events-auto');
    panel.classList.add('pointer-events-none');
    panel.setAttribute('aria-hidden', 'true');
    openButton.setAttribute('aria-expanded', 'false');
    document.body.classList.remove('overflow-hidden');
  };

  const openMobileNav = () => {
    if (!panel || !openButton) {
      return;
    }

    panel.classList.remove('hidden');
    panel.classList.remove('pointer-events-none');
    panel.classList.add('pointer-events-auto');
    panel.setAttribute('aria-hidden', 'false');
    openButton.setAttribute('aria-expanded', 'true');
    document.body.classList.add('overflow-hidden');
  };

  openButton?.addEventListener('click', () => {
    const isOpen = openButton.getAttribute('aria-expanded') === 'true';
    if (isOpen) {
      closeMobileNav();
      return;
    }

    openMobileNav();
  });

  closeButton?.addEventListener('click', closeMobileNav);

  links.forEach((link) => {
    link.addEventListener('click', closeMobileNav);

    if (!prefersReducedMotion) {
      link.addEventListener('mouseenter', () => runTextScramble(link, { duration: 650 }));
      link.addEventListener('focus', () => runTextScramble(link, { duration: 650 }));
    }
  });

  if (desktopNav && barsContainer) {
    desktopNav.addEventListener('mousemove', (event) => {
      if (!desktopQuery.matches || prefersReducedMotion) {
        return;
      }
      updateBarsByPointer(event.clientX);
    });

    desktopNav.addEventListener('mouseenter', (event) => {
      if (!desktopQuery.matches || prefersReducedMotion) {
        return;
      }
      updateBarsByPointer(event.clientX);
    });

    desktopNav.addEventListener('mouseleave', resetBars);
  }

  document.addEventListener('keydown', (event) => {
    if (event.key === 'Escape') {
      closeMobileNav();
    }
  });

  lockDesktopLinkWidths();
  buildBars();
  resetBars();
  window.addEventListener('load', lockDesktopLinkWidths, { once: true });
  window.addEventListener('load', buildBars, { once: true });

  window.addEventListener('resize', () => {
    lockDesktopLinkWidths();
    buildBars();
    resetBars();
    if (window.matchMedia('(min-width: 768px)').matches) {
      closeMobileNav();
    }
  });
};
