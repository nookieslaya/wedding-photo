const SCRAMBLE_CHARS = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';

// Single place to tune animation speed globally.
export const TEXT_SCRAMBLE_SETTINGS = {
  duration: 1000,
};

const rafMap = new WeakMap();

const isScrambleCandidate = (char) => /[A-Za-z0-9]/.test(char);
const randomChar = () => SCRAMBLE_CHARS[Math.floor(Math.random() * SCRAMBLE_CHARS.length)];

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
      return index <= revealCount ? char : randomChar();
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
