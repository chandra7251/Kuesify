# ADR-001: GSAP Motion System untuk Participant Pages

**Status:** Accepted
**Date:** 2026-09-30
**Deciders:** Tim Kuesify

## Konteks

Project Kuesify menargetkan lomba web dev dengan kriteria Inovasi & Orisinalitas (20%) dan UI/UX (25%). Animasi purposeful dibutuhkan untuk meningkatkan engagement dan kesan interaktif. Stack: Vue 3 + Inertia + GSAP 3.15.

## Keputusan

Gunakan GSAP (gsap.context + gsap.matchMedia) untuk animasi entrance pada participant dashboard dan quiz catalog, bukan CSS transition atau Vue `<Transition>`.

## Alasan

| Opsi | Pro | Kontra |
|------|-----|--------|
| CSS @keyframes | Zero dependency | Sulit stagger dinamis, tidak ada lifecycle hook |
| Vue TransitionGroup | Built-in | Hanya cover v-for list, tidak cover section-level |
| GSAP (dipilih) | Stagger presisi, scoped context, matchMedia reduced-motion | Bundle +45KB (sudah di-chunked Vite) |

## Implementasi

- gsap.registerPlugin(ScrollTrigger) -- per-file, bukan global.
- gsap.matchMedia('prefers-reduced-motion: no-preference') -- wrap semua animation block.
- gsap.context(fn, rootRef) -- scope selector ke subtree komponen.
- clearProps: 'all' -- bersihkan inline style post-animation.
- onUnmounted: ctx.revert() + ScrollTrigger.getAll().forEach(st => st.kill()) -- prevent leak di Inertia SPA.

## Data-attributes Convention

| Attribute | Tujuan |
|-----------|--------|
| data-motion="[page-id]" | Root motion surface, dipakai E2E test selector |
| data-motion-item | Target individual tween/stagger |

## Konsekuensi

- Positif: Animasi reduced-motion safe, scoped, no leak.
- Negatif (known): Partial Inertia reload (preserveState: true) tidak re-trigger animasi. Upgrade path: listen router.on('navigate') dan re-run context.
- Bundle: ScrollTrigger di-chunk terpisah oleh Vite (45KB gzip 19KB) -- lazy loaded hanya saat halaman participant dibuka.

## Referensi

- resources/js/Pages/participant/dashboard.vue
- resources/js/Pages/participant/quizzes.vue
- resources/js/Pages/AttemptPlay.vue (existing GSAP -- result timeline)
- tests/E2E/motion.spec.ts