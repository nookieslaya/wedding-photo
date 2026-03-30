import { gsap, ScrollTrigger, prefersReducedMotion } from '../shared/motion';

export const initWeddingPhotoModule = () => {
  const sections = document.querySelectorAll('[data-wedding-photo]');

  sections.forEach((section) => {
    const titleWrap = section.querySelector('[data-wedding-title-wrap]');
    const titleShell = section.querySelector('[data-wedding-title-shell]');
    const title = section.querySelector('[data-wedding-title]');
    const aperture = section.querySelector('[data-wedding-aperture]');
    const apertureDisc = section.querySelector('[data-wedding-aperture-disc]');
    const apertureHole = section.querySelector('[data-wedding-aperture-hole]');
    const apertureCore = section.querySelector('[data-wedding-aperture-core]');
    const apertureLabel = section.querySelector('[data-wedding-aperture-label]');
    const apertureBlades = section.querySelectorAll('[data-wedding-aperture-blade]');
    const gallery = section.querySelector('[data-wedding-gallery]');
    const items = section.querySelectorAll('[data-wedding-item]');

    if (!titleWrap || !titleShell || !title || !gallery) {
      return;
    }

    const isMobile = () => window.matchMedia('(max-width: 767px)').matches;

    if (prefersReducedMotion) {
      gsap.set(section, { clearProps: 'minHeight' });
      gsap.set([titleWrap, titleShell, title, aperture, apertureDisc, apertureHole, apertureCore, gallery, ...items], { clearProps: 'all' });
      gsap.set(apertureBlades, { clearProps: 'all' });
      return;
    }

    if (isMobile()) {
      gsap.set(section, { clearProps: 'minHeight' });
      gsap.set([titleWrap, titleShell, title, aperture, apertureDisc, apertureHole, apertureCore, gallery], { clearProps: 'all' });
      gsap.set(apertureBlades, { clearProps: 'all' });
      gsap.set(items, { autoAlpha: 0, y: 48 });
      gsap.to(items, {
        autoAlpha: 1,
        y: 0,
        ease: 'power2.out',
        duration: 0.5,
        stagger: 0.08,
      });

      items.forEach((item) => {
        const image = item.querySelector('img');
        if (!image) {
          return;
        }

        item.addEventListener('click', () => {
          image.classList.toggle('grayscale-0');
        });
      });

      return;
    }

    const desktopScrollEnd = Math.max(240, 170 + items.length * 32);
    const desktopMinHeight = Math.max(window.innerHeight * 2.6, window.innerHeight + items.length * window.innerHeight * 0.75);

    gsap.set(section, { minHeight: desktopMinHeight });

    gsap.set(gallery, { autoAlpha: 1, y: 180 });
    gsap.set(items, { autoAlpha: 0, y: 40 });

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

      if (apertureCore) {
        gsap.set(apertureCore, {
          xPercent: -50,
          yPercent: -50,
          scale: 0.42 + lightAmount * 1.08,
        });
      }

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
  });

  ScrollTrigger.refresh();
};
