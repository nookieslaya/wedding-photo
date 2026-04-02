import { gsap, ScrollTrigger, prefersReducedMotion } from '../shared/motion';

const observedStorySections = new WeakSet();
const initializedStorySections = new WeakSet();
const initializedStoryCarousels = new WeakSet();

const initStoryCarousel = (section, isMobile) => {
  const carousel = section.querySelector('[data-story-carousel]');
  const track = section.querySelector('[data-story-carousel-track]');

  if (!carousel || !track || initializedStoryCarousels.has(carousel)) {
    return;
  }

  const originalSlides = Array.from(track.querySelectorAll('[data-story-carousel-slide]'));
  const originalCount = originalSlides.length;

  if (originalCount === 0) {
    initializedStoryCarousels.add(carousel);
    return;
  }

  originalSlides.forEach((slide) => {
    const clone = slide.cloneNode(true);
    clone.setAttribute('data-story-carousel-clone', '1');
    track.appendChild(clone);
  });

  const imageShells = track.querySelectorAll('[data-story-image-shell]');
  imageShells.forEach((shell) => {
    const colorLayer = shell.querySelector('[data-story-image-color]');
    if (!colorLayer) {
      return;
    }

    const state = { radius: 0, x: shell.clientWidth * 0.5, y: shell.clientHeight * 0.5 };
    const applyReveal = () => {
      colorLayer.style.clipPath = `circle(${state.radius}px at ${state.x}px ${state.y}px)`;
    };
    gsap.set(colorLayer, { autoAlpha: 1 });
    applyReveal();

    if (isMobile) {
      const centerX = shell.clientWidth * 0.5;
      const centerY = shell.clientHeight * 0.5;
      let revealed = false;

      shell.addEventListener('click', () => {
        const rect = shell.getBoundingClientRect();
        state.x = centerX;
        state.y = centerY;
        const maxRadius = Math.hypot(rect.width * 0.5, rect.height * 0.5);
        revealed = !revealed;

        gsap.to(state, {
          radius: revealed ? maxRadius : 0,
          duration: revealed ? 0.55 : 0.42,
          ease: revealed ? 'power2.out' : 'power2.in',
          overwrite: true,
          onUpdate: applyReveal,
        });
      });

      return;
    }

    const updatePointer = (event) => {
      const rect = shell.getBoundingClientRect();
      state.x = event.clientX - rect.left;
      state.y = event.clientY - rect.top;
    };

    const getMaxRadius = () => {
      const rect = shell.getBoundingClientRect();
      return Math.hypot(Math.max(state.x, rect.width - state.x), Math.max(state.y, rect.height - state.y));
    };

    shell.addEventListener('mouseenter', (event) => {
      updatePointer(event);
      gsap.killTweensOf(state);
      state.radius = 0;
      applyReveal();
      gsap.to(colorLayer, {
        autoAlpha: 1,
        duration: 0.16,
        ease: 'none',
        overwrite: true,
      });
      gsap.to(state, {
        radius: getMaxRadius(),
        duration: 2,
        ease: 'power2.out',
        overwrite: true,
        onUpdate: applyReveal,
      });
    });

    shell.addEventListener('mousemove', (event) => {
      updatePointer(event);
      applyReveal();
    });

    shell.addEventListener('mouseleave', () => {
      gsap.killTweensOf(state);
      gsap.to(colorLayer, {
        autoAlpha: 0,
        duration: 2,
        ease: 'power2.out',
        overwrite: true,
        onComplete: () => {
          state.radius = 0;
          applyReveal();
        },
      });
    });
  });

  let offsetX = 0;
  let loopWidth = 0;
  let inView = true;
  const speedPxPerSec = 56;

  const recalc = () => {
    loopWidth = 0;
    const slides = Array.from(track.querySelectorAll('[data-story-carousel-slide]'));
    for (let i = 0; i < originalCount; i += 1) {
      const slide = slides[i];
      if (!slide) {
        break;
      }
      loopWidth += slide.getBoundingClientRect().width;
    }
  };

  const tick = () => {
    if (!inView || !loopWidth) {
      return;
    }

    const dt = gsap.ticker.deltaRatio(60);
    offsetX -= (speedPxPerSec / 60) * dt;
    if (Math.abs(offsetX) >= loopWidth) {
      offsetX += loopWidth;
    }

    gsap.set(track, { x: offsetX });
  };

  const observer = new IntersectionObserver(
    (entries) => {
      entries.forEach((entry) => {
        inView = entry.isIntersecting;
      });
    },
    { threshold: 0.08 },
  );

  recalc();
  observer.observe(carousel);
  window.addEventListener('resize', recalc);
  gsap.ticker.add(tick);
  initializedStoryCarousels.add(carousel);
};

export const initStoryStatementModule = () => {
  const sections = document.querySelectorAll('[data-story-statement]');

  sections.forEach((section) => {
    if (initializedStorySections.has(section)) {
      return;
    }

    if (section.dataset.storyVisible !== '1') {
      if (!observedStorySections.has(section)) {
        const observer = new IntersectionObserver(
          (entries) => {
            entries.forEach((entry) => {
              if (entry.isIntersecting) {
                section.dataset.storyVisible = '1';
                observer.disconnect();
                initStoryStatementModule();
              }
            });
          },
          {
            threshold: 0.01,
            rootMargin: '220px 0px',
          },
        );

        observer.observe(section);
        observedStorySections.add(section);
      }

      return;
    }

    initializedStorySections.add(section);

    const isMobile = window.matchMedia('(max-width: 767px)').matches;
    const lineOne = section.querySelector('[data-story-line="1"]');
    const lineTwo = section.querySelector('[data-story-line="2"]');
    const description = section.querySelector('[data-story-description]');
    const smokeCanvas = section.querySelector('[data-story-smoke]');

    if (!lineOne || !lineTwo || !description) {
      return;
    }

    if (smokeCanvas && !isMobile && !prefersReducedMotion) {
      const ctx = smokeCanvas.getContext('2d');

      if (ctx) {
        const pointer = { x: 0, y: 0, active: false, radius: 140 };
        const particles = [];
        let cssWidth = 0;
        let cssHeight = 0;
        let running = true;
        let inView = true;

        const resizeCanvas = () => {
          const rect = section.getBoundingClientRect();
          cssWidth = Math.max(1, rect.width);
          cssHeight = Math.max(1, rect.height);

          const dpr = Math.min(window.devicePixelRatio || 1, 1.5);
          smokeCanvas.width = Math.round(cssWidth * dpr);
          smokeCanvas.height = Math.round(cssHeight * dpr);
          ctx.setTransform(dpr, 0, 0, dpr, 0, 0);
        };

        const buildParticles = () => {
          particles.length = 0;
          const count = Math.max(24, Math.min(56, Math.round((cssWidth * cssHeight) / 26000)));

          for (let i = 0; i < count; i += 1) {
            const baseX = Math.random() * cssWidth;
            const baseY = Math.random() * cssHeight;
            particles.push({
              baseX,
              baseY,
              x: baseX,
              y: baseY,
              vx: 0,
              vy: 0,
              size: 44 + Math.random() * 70,
              alpha: 0.045 + Math.random() * 0.06,
              drift: Math.random() * Math.PI * 2,
              driftSpeed: 0.002 + Math.random() * 0.003,
            });
          }
        };

        const drawParticle = (particle) => {
          const gradient = ctx.createRadialGradient(
            particle.x,
            particle.y,
            particle.size * 0.12,
            particle.x,
            particle.y,
            particle.size,
          );
          gradient.addColorStop(0, `rgba(255,255,255,${particle.alpha * 1.35})`);
          gradient.addColorStop(1, 'rgba(255,255,255,0)');
          ctx.fillStyle = gradient;
          ctx.beginPath();
          ctx.arc(particle.x, particle.y, particle.size, 0, Math.PI * 2);
          ctx.fill();
        };

        const renderFrame = () => {
          if (!running) {
            return;
          }

          if (inView) {
            ctx.clearRect(0, 0, cssWidth, cssHeight);
            ctx.fillStyle = 'rgba(255,255,255,0.02)';
            ctx.fillRect(0, 0, cssWidth, cssHeight);
            ctx.globalCompositeOperation = 'screen';

            for (let i = 0; i < particles.length; i += 1) {
              const particle = particles[i];
              const pullX = (particle.baseX - particle.x) * 0.008;
              const pullY = (particle.baseY - particle.y) * 0.008;

              particle.vx += pullX;
              particle.vy += pullY;

              particle.drift += particle.driftSpeed;
              particle.vx += Math.cos(particle.drift) * 0.009;
              particle.vy += Math.sin(particle.drift * 0.86) * 0.009;

              if (pointer.active) {
                const dx = particle.x - pointer.x;
                const dy = particle.y - pointer.y;
                const distance = Math.hypot(dx, dy);

                if (distance > 0 && distance < pointer.radius) {
                  const force = ((pointer.radius - distance) / pointer.radius) * 0.35;
                  particle.vx += (dx / distance) * force;
                  particle.vy += (dy / distance) * force;
                }
              }

              particle.vx *= 0.93;
              particle.vy *= 0.93;
              particle.x += particle.vx;
              particle.y += particle.vy;

              drawParticle(particle);
            }

            ctx.globalCompositeOperation = 'source-over';
          }

          requestAnimationFrame(renderFrame);
        };

        const updatePointerPosition = (event) => {
          const rect = smokeCanvas.getBoundingClientRect();
          pointer.x = event.clientX - rect.left;
          pointer.y = event.clientY - rect.top;
          pointer.active = true;
        };

        const observer = new IntersectionObserver(
          (entries) => {
            entries.forEach((entry) => {
              inView = entry.isIntersecting;
            });
          },
          { threshold: 0.1 },
        );

        resizeCanvas();
        buildParticles();
        observer.observe(section);
        requestAnimationFrame(renderFrame);

        window.addEventListener('resize', () => {
          resizeCanvas();
          buildParticles();
        });
        section.addEventListener('mousemove', updatePointerPosition);
        section.addEventListener('mouseenter', updatePointerPosition);
        section.addEventListener('mouseleave', () => {
          pointer.active = false;
        });

        // Keep canvas subtle when pointer is absent.
        section.addEventListener('touchstart', () => {
          pointer.active = false;
        });
      }
    } else if (smokeCanvas) {
      gsap.set(smokeCanvas, { autoAlpha: 0 });
    }

    initStoryCarousel(section, isMobile);

    const items = [lineOne, lineTwo, description];
    const carousel = section.querySelector('[data-story-carousel]');
    if (carousel) {
      items.push(carousel);
    }

    if (prefersReducedMotion) {
      gsap.set(items, { autoAlpha: 1, y: 0 });
      return;
    }

    gsap.set(items, { autoAlpha: 0, y: 120 });

    if (isMobile) {
      const mobileTimeline = gsap.timeline({
        scrollTrigger: {
          trigger: section,
          start: 'top 68%',
          once: true,
          invalidateOnRefresh: true,
        },
      })
        .to(lineOne, { autoAlpha: 1, y: 0, duration: 0.75, ease: 'power3.out' }, 0)
        .to(lineTwo, { autoAlpha: 1, y: 0, duration: 0.75, ease: 'power3.out' }, 0.16)
        .to(description, { autoAlpha: 1, y: 0, duration: 0.75, ease: 'power3.out' }, 0.32);

      if (carousel) {
        mobileTimeline.to(carousel, { autoAlpha: 1, y: 0, duration: 0.75, ease: 'power3.out' }, 0.5);
      }

      return;
    }

    const timeline = gsap.timeline({
      scrollTrigger: {
        trigger: section,
        start: 'top 52%',
        end: '+=140%',
        scrub: true,
        invalidateOnRefresh: true,
      },
    });

    timeline.to(lineOne, { autoAlpha: 1, y: 0, ease: 'power3.out' }, 0);
    timeline.to(lineTwo, { autoAlpha: 1, y: 0, ease: 'power3.out' }, 0.45);
    timeline.to(description, { autoAlpha: 1, y: 0, ease: 'power3.out' }, 0.9);

    if (carousel) {
      timeline.to(carousel, { autoAlpha: 1, y: 0, ease: 'power3.out' }, 1.25);
    }
  });

  ScrollTrigger.refresh();
};
