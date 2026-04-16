import { prefersReducedMotion } from '../shared/motion';

export const initHomeSectionBarsModule = () => {
  const isHome = document.body.classList.contains('home') || document.body.classList.contains('front-page');

  if (!isHome) {
    return;
  }

  const desktopQuery = window.matchMedia('(min-width: 1024px)');
  const sections = document.querySelectorAll('#main section');
  const instances = [];

  const buildBarsForInstance = (instance) => {
    const { barsContainer } = instance;
    barsContainer.innerHTML = '';
    instance.bars = [];

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
      instance.bars.push(bar);
    }
  };

  const resetBars = (instance) => {
    instance.bars.forEach((bar) => {
      bar.style.height = '5px';
      bar.style.opacity = '0.36';
      bar.style.backgroundColor = '#ffffff';
    });
  };

  const updateBarsByPointer = (instance, clientX) => {
    if (!instance.bars.length) {
      return;
    }

    const rect = instance.barsContainer.getBoundingClientRect();
    const x = Math.max(0, Math.min(rect.width, clientX - rect.left));
    const center = (x / Math.max(1, rect.width)) * (instance.bars.length - 1);
    const sigma = 1.15;
    const baseHeight = 5;
    const peakHeight = 16;

    instance.bars.forEach((bar, index) => {
      const distance = Math.abs(index - center);
      const influence = Math.exp(-(distance * distance) / (2 * sigma * sigma));
      const height = Math.round(baseHeight + influence * (peakHeight - baseHeight));
      const opacity = 0.36 + influence * 0.64;
      bar.style.height = `${height}px`;
      bar.style.opacity = `${opacity}`;
      bar.style.backgroundColor = distance < 0.55 ? '#ef4444' : '#ffffff';
    });
  };

  sections.forEach((section) => {
    const boxedContainer = section.querySelector(':scope > [class*="max-w-"]');

    if (!boxedContainer) {
      return;
    }

    const sectionRect = section.getBoundingClientRect();
    const containerRect = boxedContainer.getBoundingClientRect();

    if (sectionRect.width - containerRect.width < 60) {
      return;
    }

    const barsContainer = document.createElement('div');
    barsContainer.className = 'mt-2 hidden h-5 w-full items-end justify-between md:flex';
    barsContainer.setAttribute('aria-hidden', 'true');
    barsContainer.setAttribute('data-home-bars', '1');
    boxedContainer.appendChild(barsContainer);

    const instance = { section, barsContainer, bars: [] };
    instances.push(instance);

    buildBarsForInstance(instance);
    resetBars(instance);

    section.addEventListener('mousemove', (event) => {
      if (!desktopQuery.matches || prefersReducedMotion) {
        return;
      }
      updateBarsByPointer(instance, event.clientX);
    });

    section.addEventListener('mouseenter', (event) => {
      if (!desktopQuery.matches || prefersReducedMotion) {
        return;
      }
      updateBarsByPointer(instance, event.clientX);
    });

    section.addEventListener('mouseleave', () => {
      resetBars(instance);
    });
  });

  window.addEventListener('resize', () => {
    instances.forEach((instance) => {
      buildBarsForInstance(instance);
      resetBars(instance);
    });
  });
};
