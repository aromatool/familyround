/** Inline the editable SVG, then call createFamilyRoundJar(svgElement).
 * Decorative animation only. Does not calculate donations or mutate order data.
 * Use one instance per page; prefix all SVG IDs/references if duplicating it.
 */
export function createFamilyRoundJar(svg) {
  if (!svg || svg.tagName.toLowerCase() !== 'svg') throw new TypeError('Expected inline SVG element.');
  const base = svg.querySelector('#fr-coin-base');
  const referral = svg.querySelector('#fr-coin-referral');
  const fill = svg.querySelector('#fr-fill-window');
  if (!base || !referral || !fill) throw new Error('Missing FamilyRound SVG layers.');
  svg.classList.remove('fr-demo');
  const motion = window.matchMedia('(prefers-reduced-motion: reduce)');
  const active = new Set();
  let busy = false;
  const stop = () => { for (const a of active) a.cancel(); active.clear(); };
  const onMotion = () => { if (motion.matches) stop(); };
  motion.addEventListener('change', onMotion);

  async function drop(coin, delay) {
    const a = coin.animate([
      { offset: 0, opacity: 0, transform: 'translateY(-45px) rotate(-9deg)' },
      { offset: 0.12, opacity: 1, transform: 'translateY(0px) rotate(-7deg)' },
      { offset: 0.46, opacity: 1, transform: 'translateY(145px) rotate(2deg)' },
      { offset: 0.92, opacity: 1, transform: 'translateY(680px) rotate(9deg)' },
      { offset: 1, opacity: 0, transform: 'translateY(690px) rotate(9deg)' }
    ], { duration: 1550, delay, easing: 'ease-in', fill: 'none' });
    active.add(a);
    try { await a.finished; } catch (_) { /* Cancelled for reduced motion or teardown. */ }
    finally { active.delete(a); }
  }

  return {
    setProgress(ratio) {
      if (!Number.isFinite(ratio)) throw new TypeError('Progress must be a finite number.');
      // 0 = empty; 1 = full. Height is symbolic, not a literal token count.
      const value = Math.max(0, Math.min(1, ratio));
      const height = 460 * value;
      fill.setAttribute('y', String(832 - height));
      fill.setAttribute('height', String(height));
    },
    async animateContribution({ withReferral = false } = {}) {
      if (busy || motion.matches || typeof base.animate !== 'function') return;
      busy = true;
      try { await Promise.all([drop(base, 0), ...(withReferral ? [drop(referral, 1050)] : [])]); }
      finally { busy = false; }
    },
    destroy() { stop(); motion.removeEventListener('change', onMotion); }
  };
}
