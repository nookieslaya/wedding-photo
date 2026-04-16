const UPPERCASE_CHARS = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
const LOWERCASE_CHARS = 'abcdefghijklmnopqrstuvwxyz';
const DIGIT_CHARS = '0123456789';

// Single place to tune animation speed globally.
export const TEXT_SCRAMBLE_SETTINGS = {
  duration: 1000,
  disableOnMobile: true,
  mobileBreakpoint: 1023,
};

const rafMap = new WeakMap();

const isScrambleCandidate = (char) => /[A-Za-z0-9]/.test(char);
const pickRandom = (charset) => charset[Math.floor(Math.random() * charset.length)];
const randomCharFor = (char) => {
  if (/[A-Z]/.test(char)) {
    return pickRandom(UPPERCASE_CHARS);
  }
  if (/[a-z]/.test(char)) {
    return pickRandom(LOWERCASE_CHARS);
  }
  if (/[0-9]/.test(char)) {
    return pickRandom(DIGIT_CHARS);
  }
  return char;
};

export const stopTextScramble = (element) => {
  const rafId = rafMap.get(element);
  if (rafId) {
    cancelAnimationFrame(rafId);
    rafMap.delete(element);
  }
};

export const runTextScramble = (element, options = {}) => {
  if (!element) {
    return;
  }

  const original = element.dataset.scrambleOriginal ?? element.textContent ?? '';
  element.dataset.scrambleOriginal = original;

  stopTextScramble(element);

  const chars = Array.from(original);
  const isMobile =
    typeof window !== 'undefined'
    && window.matchMedia(`(max-width: ${TEXT_SCRAMBLE_SETTINGS.mobileBreakpoint}px)`).matches;
  const disableForViewport = options.disableOnMobile ?? TEXT_SCRAMBLE_SETTINGS.disableOnMobile;

  if (disableForViewport && isMobile) {
    element.textContent = original;
    return;
  }

  const start = performance.now();
  const duration = options.duration ?? TEXT_SCRAMBLE_SETTINGS.duration;

  const tick = (now) => {
    const elapsed = now - start;
    const progress = Math.min(1, elapsed / duration);
    const revealCount = Math.floor(progress * chars.length);

    element.textContent = chars.map((char, index) => {
      if (!isScrambleCandidate(char)) {
        return char;
      }
      return index <= revealCount ? char : randomCharFor(char);
    }).join('');

    if (progress < 1) {
      const rafId = requestAnimationFrame(tick);
      rafMap.set(element, rafId);
      return;
    }

    element.textContent = original;
    rafMap.delete(element);
  };

  const rafId = requestAnimationFrame(tick);
  rafMap.set(element, rafId);
};
