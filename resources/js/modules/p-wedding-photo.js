import { gsap, ScrollTrigger, prefersReducedMotion } from '../shared/motion';

const observedWeddingSections = new WeakSet();
const initializedWeddingSections = new WeakSet();

const initWeddingCloudLayer = (section, canvas) => {
  const ctx = canvas.getContext('2d');

  if (!ctx) {
    return;
  }

  const pointer = { x: 0, y: 0, active: false, radius: 180 };
  const clouds = [];
  let cssWidth = 1;
  let cssHeight = 1;
  let inView = true;
  let running = true;
  let rafId = null;

  const resizeCanvas = () => {
    const rect = section.getBoundingClientRect();
    cssWidth = Math.max(1, rect.width);
    cssHeight = Math.max(1, rect.height);
    const dpr = Math.min(window.devicePixelRatio || 1, 1.5);
    canvas.width = Math.round(cssWidth * dpr);
    canvas.height = Math.round(cssHeight * dpr);
    ctx.setTransform(dpr, 0, 0, dpr, 0, 0);
  };

  const buildClouds = () => {
    clouds.length = 0;
    const count = Math.max(8, Math.min(14, Math.round((cssWidth * cssHeight) / 150000)));
    const diagonalSlope = cssHeight / Math.max(1, cssWidth);

    for (let i = 0; i < count; i += 1) {
      const baseSize = (90 + Math.random() * 180) * 4;
      const driftSpeed = 0.34 + Math.random() * 0.36;
      clouds.push({
        x: -baseSize + Math.random() * (cssWidth + baseSize * 0.35),
        y: -baseSize + Math.random() * (cssHeight + baseSize * 0.35),
        baseVx: driftSpeed,
        baseVy: driftSpeed * diagonalSlope * 1.08,
        vx: driftSpeed,
        vy: driftSpeed * diagonalSlope * 1.08,
        alpha: 0.06 + Math.random() * 0.07,
        size: baseSize,
        seed: Math.random() * Math.PI * 2,
        age: Math.random(),
        ageSpeed: 0.0012 + Math.random() * 0.0014,
      });
    }
  };

  const drawFrame = (time) => {
    if (!running) {
      return;
    }

    if (inView) {
      const t = time * 0.001;
      ctx.clearRect(0, 0, cssWidth, cssHeight);
      ctx.globalCompositeOperation = 'screen';

      for (let i = 0; i < clouds.length; i += 1) {
        const cloud = clouds[i];
        cloud.age += cloud.ageSpeed;
        cloud.x += cloud.vx;
        cloud.y += cloud.vy;

        // Keep a stable TL -> BR drift with tiny natural jitter.
        cloud.vx += (cloud.baseVx - cloud.vx) * 0.018 + Math.sin(t * 0.18 + cloud.seed) * 0.0008;
        cloud.vy += (cloud.baseVy - cloud.vy) * 0.018 + Math.cos(t * 0.16 + cloud.seed * 0.7) * 0.00055;
        cloud.vx *= 0.992;
        cloud.vy *= 0.992;

        if (cloud.age >= 1 || cloud.x > cssWidth + cloud.size * 1.1 || cloud.y > cssHeight + cloud.size * 1.1) {
          cloud.x = -cloud.size * (0.9 + Math.random() * 0.5);
          cloud.y = -cloud.size * (0.9 + Math.random() * 0.5);
          cloud.age = 0;
        }

        if (pointer.active) {
          const dx = cloud.x - pointer.x;
          const dy = cloud.y - pointer.y;
          const dist = Math.hypot(dx, dy);

          if (dist > 0 && dist < pointer.radius) {
            const force = ((pointer.radius - dist) / pointer.radius) * 0.22;
            cloud.vx += (dx / dist) * force;
            cloud.vy += (dy / dist) * force;
          }
        }

        const fadeIn = Math.min(1, cloud.age / 0.22);
        const fadeOut = Math.min(1, (1 - cloud.age) / 0.22);
        const lifeFade = Math.max(0, Math.min(fadeIn, fadeOut));

        const puffOffsets = [
          { x: -cloud.size * 0.18, y: cloud.size * 0.04, s: 0.56 },
          { x: cloud.size * 0.11, y: -cloud.size * 0.08, s: 0.52 },
          { x: cloud.size * 0.28, y: cloud.size * 0.06, s: 0.47 },
        ];

        for (let j = 0; j < puffOffsets.length; j += 1) {
          const puff = puffOffsets[j];
          const px = cloud.x + puff.x;
          const py = cloud.y + puff.y;
          const radius = cloud.size * puff.s;
          const gradient = ctx.createRadialGradient(px, py, radius * 0.12, px, py, radius);
          gradient.addColorStop(0, `rgba(255,255,255,${cloud.alpha * lifeFade * 1.28})`);
          gradient.addColorStop(1, 'rgba(255,255,255,0)');
          ctx.fillStyle = gradient;
          ctx.beginPath();
          ctx.arc(px, py, radius, 0, Math.PI * 2);
          ctx.fill();
        }
      }

      ctx.globalCompositeOperation = 'source-over';

      if (pointer.active) {
        const halo = ctx.createRadialGradient(pointer.x, pointer.y, 0, pointer.x, pointer.y, 190);
        halo.addColorStop(0, 'rgba(255,255,255,0.11)');
        halo.addColorStop(1, 'rgba(255,255,255,0)');
        ctx.fillStyle = halo;
        ctx.beginPath();
        ctx.arc(pointer.x, pointer.y, 190, 0, Math.PI * 2);
        ctx.fill();
      }
    }

    rafId = requestAnimationFrame(drawFrame);
  };

  const handleMouseMove = (event) => {
    const rect = canvas.getBoundingClientRect();
    pointer.x = event.clientX - rect.left;
    pointer.y = event.clientY - rect.top;
    pointer.active = true;
  };

  const handleMouseLeave = () => {
    pointer.active = false;
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
  buildClouds();
  observer.observe(section);
  section.addEventListener('mousemove', handleMouseMove);
  section.addEventListener('mouseenter', handleMouseMove);
  section.addEventListener('mouseleave', handleMouseLeave);
  window.addEventListener('resize', () => {
    resizeCanvas();
    buildClouds();
  });
  rafId = requestAnimationFrame(drawFrame);

  // Return cleanup for future teardown safety.
  return () => {
    running = false;
    if (rafId) {
      cancelAnimationFrame(rafId);
    }
    observer.disconnect();
    section.removeEventListener('mousemove', handleMouseMove);
    section.removeEventListener('mouseenter', handleMouseMove);
    section.removeEventListener('mouseleave', handleMouseLeave);
  };
};

export const initWeddingPhotoModule = () => {
  const sections = document.querySelectorAll('[data-wedding-photo]');

  sections.forEach((section) => {
    if (initializedWeddingSections.has(section)) {
      return;
    }

    if (section.dataset.weddingVisible !== '1') {
      if (!observedWeddingSections.has(section)) {
        const observer = new IntersectionObserver(
          (entries) => {
            entries.forEach((entry) => {
              if (entry.isIntersecting) {
                section.dataset.weddingVisible = '1';
                observer.disconnect();
                initWeddingPhotoModule();
              }
            });
          },
          {
            threshold: 0.01,
            rootMargin: '220px 0px',
          },
        );

        observer.observe(section);
        observedWeddingSections.add(section);
      }

      return;
    }

    initializedWeddingSections.add(section);

    const titleWrap = section.querySelector('[data-wedding-title-wrap]');
    const titleShell = section.querySelector('[data-wedding-title-shell]');
    const stickyContainer = section.querySelector('[data-wedding-sticky]');
    const title = section.querySelector('[data-wedding-title]');
    const aperture = section.querySelector('[data-wedding-aperture]');
    const apertureDisc = section.querySelector('[data-wedding-aperture-disc]');
    const apertureHole = section.querySelector('[data-wedding-aperture-hole]');
    const apertureCore = section.querySelector('[data-wedding-aperture-core]');
    const apertureLabel = section.querySelector('[data-wedding-aperture-label]');
    const apertureBlades = section.querySelectorAll('[data-wedding-aperture-blade]');
    const gallery = section.querySelector('[data-wedding-gallery]');
    const items = section.querySelectorAll('[data-wedding-item]');
    const imageShells = section.querySelectorAll('[data-wedding-image-shell]');
    const smokeCanvas = section.querySelector('[data-wedding-smoke]');

    if (!titleWrap || !titleShell || !title || !gallery) {
      return;
    }

    const isMobile = () => window.matchMedia('(max-width: 767px)').matches;

    if (smokeCanvas) {
      if (isMobile() || prefersReducedMotion) {
        gsap.set(smokeCanvas, { autoAlpha: 0 });
      } else {
        gsap.set(smokeCanvas, { autoAlpha: 1 });
        initWeddingCloudLayer(section, smokeCanvas);
      }
    }

    if (prefersReducedMotion) {
      gsap.set(section, { clearProps: 'minHeight' });
      gsap.set([titleWrap, titleShell, title, aperture, apertureDisc, apertureHole, apertureCore, gallery, ...items], { clearProps: 'all' });
      gsap.set(apertureBlades, { clearProps: 'all' });
      if (isMobile() && stickyContainer) {
        gsap.set(stickyContainer, {
          position: 'relative',
          top: 'auto',
          minHeight: 'auto',
          overflow: 'visible',
        });
      }
      return;
    }

    if (isMobile()) {
      gsap.set(section, { clearProps: 'minHeight' });
      gsap.set([titleWrap, titleShell, title, aperture, apertureDisc, apertureHole, apertureCore, gallery], { clearProps: 'all' });
      gsap.set(apertureBlades, { clearProps: 'all' });
      if (stickyContainer) {
        gsap.set(stickyContainer, {
          position: 'relative',
          top: 'auto',
          minHeight: 'auto',
          overflow: 'visible',
        });
      }
      gsap.set(items, { autoAlpha: 1, y: 0 });

      imageShells.forEach((shell) => {
        const colorLayer = shell.querySelector('[data-wedding-image-color]');
        if (!colorLayer) {
          return;
        }

        const state = { radius: 0 };
        const applyReveal = (x, y) => {
          colorLayer.style.clipPath = `circle(${state.radius}px at ${x}px ${y}px)`;
        };
        const centerX = shell.clientWidth * 0.5;
        const centerY = shell.clientHeight * 0.5;
        applyReveal(centerX, centerY);

        shell.addEventListener('click', () => {
          const rect = shell.getBoundingClientRect();
          const x = rect.width * 0.5;
          const y = rect.height * 0.5;
          const maxRadius = Math.hypot(rect.width * 0.5, rect.height * 0.5);
          const isOpen = shell.classList.toggle('is-revealed');

          gsap.to(state, {
            radius: isOpen ? maxRadius : 0,
            duration: isOpen ? 0.55 : 0.42,
            ease: isOpen ? 'power2.out' : 'power2.in',
            overwrite: true,
            onUpdate: () => applyReveal(x, y),
          });
        });
      });

      return;
    }

    const desktopScrollEnd = Math.max(240, 170 + items.length * 32);
    const desktopMinHeight = Math.max(window.innerHeight * 2.6, window.innerHeight + items.length * window.innerHeight * 0.75);

    gsap.set(section, { minHeight: desktopMinHeight });

    gsap.set(gallery, { autoAlpha: 1, y: 180 });
    gsap.set(items, { autoAlpha: 0, y: 40 });

    imageShells.forEach((shell) => {
      const colorLayer = shell.querySelector('[data-wedding-image-color]');
      if (!colorLayer) {
        return;
      }

      const state = { radius: 0, x: shell.clientWidth * 0.5, y: shell.clientHeight * 0.5 };
      let lastPointer = null;
      const pointerDir = { x: 1, y: 0 };

      const normalizeVector = (dx, dy) => {
        const length = Math.hypot(dx, dy) || 1;
        return { x: dx / length, y: dy / length };
      };
      const applyReveal = () => {
        colorLayer.style.clipPath = `circle(${state.radius}px at ${state.x}px ${state.y}px)`;
      };
      gsap.set(colorLayer, { autoAlpha: 1 });
      applyReveal();

      const updatePointer = (event) => {
        const rect = shell.getBoundingClientRect();
        state.x = event.clientX - rect.left;
        state.y = event.clientY - rect.top;

        if (lastPointer) {
          const dx = state.x - lastPointer.x;
          const dy = state.y - lastPointer.y;
          const distance = Math.hypot(dx, dy);
          if (distance > 0.35) {
            const normalized = normalizeVector(dx, dy);
            pointerDir.x = normalized.x;
            pointerDir.y = normalized.y;
          }
        } else {
          const cx = rect.width * 0.5;
          const cy = rect.height * 0.5;
          const normalized = normalizeVector(state.x - cx, state.y - cy);
          pointerDir.x = normalized.x;
          pointerDir.y = normalized.y;
        }

        lastPointer = { x: state.x, y: state.y };
      };

      const getMaxRadius = () => {
        const rect = shell.getBoundingClientRect();
        return Math.hypot(Math.max(state.x, rect.width - state.x), Math.max(state.y, rect.height - state.y));
      };

      shell.addEventListener('mouseenter', (event) => {
        gsap.killTweensOf(shell);
        gsap.to(shell, {
          x: pointerDir.x * 5,
          y: pointerDir.y * 5,
          rotation: pointerDir.x * 0.32,
          duration: 0.26,
          ease: 'sine.out',
          transformOrigin: '50% 50%',
          overwrite: true,
        });

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

      shell.addEventListener('mouseleave', (event) => {
        const rect = shell.getBoundingClientRect();
        const leaveX = event.clientX - rect.left;
        const leaveY = event.clientY - rect.top;
        const cx = rect.width * 0.5;
        const cy = rect.height * 0.5;
        const exitDir = normalizeVector(leaveX - cx, leaveY - cy);

        gsap.killTweensOf(shell);
        gsap.to(shell, {
          keyframes: [
            {
              x: exitDir.x * 5,
              y: exitDir.y * 5,
              rotation: exitDir.x * 0.35,
              duration: 0.2,
            },
            {
              x: 0,
              y: 0,
              rotation: 0,
              duration: 0.42,
            },
          ],
          duration: 0.62,
          ease: 'sine.out',
          transformOrigin: '50% 50%',
          overwrite: true,
        });

        gsap.killTweensOf(state);
        gsap.to(colorLayer, {
          autoAlpha: 0,
          duration: 2,
          ease: 'power2.out',
          overwrite: true,
          onComplete: () => {
            state.radius = 0;
            applyReveal();
            lastPointer = null;
          },
        });
      });
    });

    const apertureStops = [
      { label: 'f/1.4', value: 1.4, position: 0, spread: 1, hole: 2.35 },
      { label: 'f/2', value: 2, position: 0.17, spread: 0.88, hole: 1.95 },
      { label: 'f/2.8', value: 2.8, position: 0.32, spread: 0.74, hole: 1.58 },
      { label: 'f/4', value: 4, position: 0.47, spread: 0.6, hole: 1.26 },
      { label: 'f/5.6', value: 5.6, position: 0.62, spread: 0.46, hole: 0.98 },
      { label: 'f/8', value: 8, position: 0.76, spread: 0.34, hole: 0.72 },
      { label: 'f/11', value: 11, position: 0.89, spread: 0.22, hole: 0.44 },
      { label: 'f/16', value: 16, position: 1, spread: 0.14, hole: 0.24 },
    ];

    const setApertureState = (phase) => {
      const clampedPhase = gsap.utils.clamp(0, 1, phase);
      let upperStopIndex = apertureStops.findIndex((stop) => clampedPhase <= stop.position);
      if (upperStopIndex === -1) {
        upperStopIndex = apertureStops.length - 1;
      }
      const lowerStopIndex = Math.max(0, upperStopIndex - 1);
      const lowerStop = apertureStops[lowerStopIndex];
      const upperStop = apertureStops[upperStopIndex];
      const segmentRange = Math.max(0.0001, upperStop.position - lowerStop.position);
      const segmentProgress = gsap.utils.clamp(0, 1, (clampedPhase - lowerStop.position) / segmentRange);

      const spreadAmount = gsap.utils.interpolate(lowerStop.spread, upperStop.spread, segmentProgress);
      const holeAmount = gsap.utils.interpolate(lowerStop.hole, upperStop.hole, segmentProgress);
      const closeAmount = 1 - spreadAmount;
      const innerSpin = clampedPhase * 160;

      const bladeOffset = 18 + spreadAmount * 44;
      const bladeTwist = 12 + closeAmount * 22;
      const bladeScaleX = 0.98 + spreadAmount * 0.44;
      const bladeScaleY = 0.9 + closeAmount * 0.18;
      const bladeShear = 6 - spreadAmount * 12;

      apertureBlades.forEach((blade, index) => {
        const baseAngle = index * (360 / apertureBlades.length);
        const radians = (baseAngle * Math.PI) / 180;
        const x = Math.cos(radians) * bladeOffset;
        const y = Math.sin(radians) * bladeOffset;

        gsap.set(blade, {
          xPercent: -25,
          yPercent: -50,
          x,
          y,
          rotation: baseAngle + bladeTwist + innerSpin * 0.35,
          scaleX: bladeScaleX,
          scaleY: bladeScaleY,
          skewX: bladeShear,
          transformOrigin: '20% 50%',
          autoAlpha: 1,
        });
      });

      if (apertureHole) {
        gsap.set(apertureHole, {
          xPercent: -50,
          yPercent: -50,
          scale: holeAmount,
          rotation: -innerSpin,
        });
      }

      if (apertureLabel) {
        let nearestStopIndex = 0;
        let nearestDistance = Math.abs(clampedPhase - apertureStops[0].position);
        apertureStops.forEach((stop, index) => {
          const distance = Math.abs(clampedPhase - stop.position);
          if (distance < nearestDistance) {
            nearestDistance = distance;
            nearestStopIndex = index;
          }
        });
        apertureLabel.textContent = apertureStops[nearestStopIndex].label;
      }
    };

    if (aperture && apertureDisc && apertureBlades.length) {
      gsap.set(aperture, { autoAlpha: 1 });
      gsap.set(apertureDisc, { autoAlpha: 1 });
      gsap.set(apertureDisc, { rotation: 0 });
      setApertureState(0);
    }

    const timeline = gsap.timeline({
      scrollTrigger: {
        trigger: section,
        start: 'top top',
        end: () => `+=${desktopScrollEnd}%`,
        scrub: 1,
        invalidateOnRefresh: true,
      },
    });

    const titleSettleAt = 0.56;
    const galleryRevealAt = 0.66;
    const itemsRevealAt = 0.5;

    timeline.fromTo(
      titleShell,
      {
        scale: 1.25,
        y: 500,
        opacity: 1,
      },
      {
        scale: 0.42,
        y: 260,
        opacity: 1,
        ease: 'none',
        duration: titleSettleAt,
      },
      0,
    );

    if (aperture && apertureDisc && apertureBlades.length) {
      const apertureTweenState = { phase: 0 };
      timeline.to(
        apertureTweenState,
        {
          phase: 1,
          ease: 'none',
          duration: titleSettleAt,
          onUpdate: () => setApertureState(apertureTweenState.phase),
        },
        0,
      );

      timeline.to(
        apertureDisc,
        {
          rotation: -18,
          ease: 'none',
          duration: titleSettleAt,
        },
        0,
      );

      timeline.to(
        aperture,
        {
          autoAlpha: 0,
          duration: 0.14,
          ease: 'none',
        },
        titleSettleAt,
      );
    }

    timeline.fromTo(
      gallery,
      { y: 180 },
      { y: 0, ease: 'power2.out', duration: 0.44 },
      galleryRevealAt,
    );

    timeline.to(
      items,
      {
        autoAlpha: 1,
        y: 0,
        ease: 'none',
        stagger: 0.08,
        duration: 0.1,
      },
      itemsRevealAt,
    );

    // Subtle "float on scroll": while scrolling, items drift opposite to scroll direction,
    // then softly return to their base position when scrolling stops.
    let settleTimer = null;
    const floatTween = gsap.quickTo(items, 'yPercent', {
      duration: 0.22,
      ease: 'power2.out',
    });

    ScrollTrigger.create({
      trigger: section,
      start: 'top bottom',
      end: 'bottom top',
      onUpdate: (self) => {
        const velocity = self.getVelocity();
        const floatAmount = gsap.utils.clamp(-8, 8, -velocity * 0.008);
        floatTween(floatAmount);

        if (settleTimer) {
          clearTimeout(settleTimer);
        }
        settleTimer = setTimeout(() => {
          floatTween(0);
        }, 110);
      },
      onLeave: () => floatTween(0),
      onLeaveBack: () => floatTween(0),
    });
  });

  ScrollTrigger.refresh();
};
