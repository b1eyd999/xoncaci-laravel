export function initHero() {
    const el = document.querySelector('[data-hero-viewer]');
    if (!el) return;
    const items = window.XONCA?.items || {};
    import('./xonca3d/viewer.js').then(({ createViewer }) => {
        const v = createViewer(el, { autoRotate: true });
        const cfg = JSON.parse(el.dataset.heroViewer);
        v.load(cfg, cfg.fill, items);

        const chips = [...document.querySelectorAll('[data-hero-switch] [data-config]')];
        chips.forEach((chip) =>
            chip.addEventListener('click', () => {
                if (chip.classList.contains('chip-active')) {
                    location.href = chip.dataset.href;
                    return;
                }
                chips.forEach((c) => c.classList.toggle('chip-active', c === chip));
                const next = JSON.parse(chip.dataset.config);
                v.load(next, next.fill, items, true);
            }),
        );
    });
}
