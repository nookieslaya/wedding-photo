import { prefersReducedMotion } from '../shared/motion';

export const initScrollIndicatorModule = () => {
  const indicator = document.querySelector('[data-scroll-indicator]');
  const barsContainer = document.querySelector('[data-scroll-indicator-bars]');

  if (!indicator || !barsContainer) {
    return;
  }

  const desktopQuery = window.matchMedia('(min-width: 768px)');
  let bars = [];
  let rafId = null;
  const baseWidth = 4;
  const outerSideWidth = 7;
  const sideWidth = 10;
  const peakWidth = 14;
  const barHeight = 2;
  const outerSideBarHeight = 2.6;
  const sideBarHeight = 3;
  const activeBarHeight = 4;

  const resetBars = () => {
    bars.forEach((bar) => {
      bar.style.width = `${baseWidth}px`;
      bar.style.height = `${barHeight}px`;
      bar.style.opacity = '0.34';
      bar.style.backgroundColor = '#ffffff';
    });
  };

  const buildBars = () => {
    barsContainer.innerHTML = '';
    bars = [];

    if (!desktopQuery.matches) {
      return;
    }

    const height = Math.max(1, barsContainer.getBoundingClientRect().height);
    const count = Math.max(34, Math.floor(height / 8));

    for (let i = 0; i < count; i += 1) {
      const bar = document.createElement('span');
      bar.className = 'block rounded-full transition-[width,opacity,background-color] duration-150 ease-out';
      bar.style.width = `${baseWidth}px`;
      bar.style.height = `${barHeight}px`;
      bar.style.opacity = '0.34';
      bar.style.backgroundColor = '#ffffff';
      barsContainer.appendChild(bar);
      bars.push(bar);
    }
  };

  const updateFromScroll = () => {
    rafId = null;

    if (!desktopQuery.matches || !bars.length || prefersReducedMotion) {
      resetBars();
      return;
    }

    const scrollMax = Math.max(1, document.documentElement.scrollHeight - window.innerHeight);
    const progress = Math.min(1, Math.max(0, window.scrollY / scrollMax));
    const center = progress * (bars.length - 1);
    bars.forEach((bar) => {
      bar.style.width = `${baseWidth}px`;
      bar.style.height = `${barHeight}px`;
      bar.style.opacity = '0.34';
      bar.style.backgroundColor = '#ffffff';
    });

    const activeIndex = Math.max(0, Math.min(bars.length - 1, Math.round(center)));
    const outerPrevBar = bars[activeIndex - 2];
    const prevBar = bars[activeIndex - 1];
    const nextBar = bars[activeIndex + 1];
    const outerNextBar = bars[activeIndex + 2];
    const activeBar = bars[activeIndex];

    [outerPrevBar, outerNextBar].forEach((outerSideBar) => {
      if (!outerSideBar) {
        return;
      }
      outerSideBar.style.width = `${outerSideWidth}px`;
      outerSideBar.style.height = `${outerSideBarHeight}px`;
      outerSideBar.style.opacity = '0.56';
    });

    [prevBar, nextBar].forEach((sideBar) => {
      if (!sideBar) {
        return;
      }
      sideBar.style.width = `${sideWidth}px`;
      sideBar.style.height = `${sideBarHeight}px`;
      sideBar.style.opacity = '0.8';
    });

    if (activeBar) {
      activeBar.style.backgroundColor = '#ef4444';
      activeBar.style.height = `${activeBarHeight}px`;
      activeBar.style.width = `${peakWidth}px`;
      activeBar.style.opacity = '1';
    }
  };

  const requestUpdate = () => {
    if (rafId !== null) {
      return;
    }
    rafId = window.requestAnimationFrame(updateFromScroll);
  };

  buildBars();
  resetBars();
  requestUpdate();

  window.addEventListener('scroll', requestUpdate, { passive: true });
  window.addEventListener('resize', () => {
    buildBars();
    requestUpdate();
  });
};
