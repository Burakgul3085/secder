import './bootstrap';
import './chad-live-info/index.js';
import './zakat/app.js';
import './testimonials/index.js';
import Alpine from 'alpinejs';

window.Alpine = Alpine;

document.addEventListener('alpine:init', () => {
    Alpine.data('homeHeroSlider', (config = {}) => ({
        slides: Array.isArray(config.slides) ? config.slides : [],
        logoUrl: typeof config.logoUrl === 'string' ? config.logoUrl : '',
        idx: 0,
        outgoingIdx: null,
        busy: false,
        touchStartX: null,
        autoTimer: null,
        autoMs: 4000,
        fadeMs: 900,

        get current() {
            return this.slides[this.idx] ?? {};
        },

        get total() {
            return this.slides.length;
        },

        init() {
            this.preload();
            this.$el?.classList.add('is-ready');
            this._onVisibility = () => {
                if (document.hidden) {
                    this.stopAuto();
                } else {
                    this.startAuto();
                }
            };
            this.startAuto();
            document.addEventListener('visibilitychange', this._onVisibility);
        },

        destroy() {
            this.stopAuto();
            if (this._fadeTimer) {
                window.clearTimeout(this._fadeTimer);
            }
            if (this._onVisibility) {
                document.removeEventListener('visibilitychange', this._onVisibility);
            }
        },

        preload() {
            this.slides.forEach((slide) => {
                ['image', 'image_mobile', 'image_tablet'].forEach((key) => {
                    const src = typeof slide?.[key] === 'string' ? slide[key] : '';
                    if (!src) {
                        return;
                    }
                    const img = new Image();
                    img.decoding = 'async';
                    img.src = src;
                });
            });
        },

        startAuto() {
            this.stopAuto();
            if (this.total < 2) {
                return;
            }
            this.autoTimer = window.setInterval(() => {
                if (document.hidden || this.busy) {
                    return;
                }
                this.next();
            }, this.autoMs);
        },

        stopAuto() {
            if (this.autoTimer) {
                window.clearInterval(this.autoTimer);
                this.autoTimer = null;
            }
        },

        restartAuto() {
            this.startAuto();
        },

        show(i) {
            if (this.total < 2 || this.busy || i === this.idx || i < 0 || i >= this.total) {
                return;
            }
            this.busy = true;
            this.outgoingIdx = this.idx;
            this.idx = i;
            if (this._fadeTimer) {
                window.clearTimeout(this._fadeTimer);
            }
            this._fadeTimer = window.setTimeout(() => {
                this.outgoingIdx = null;
                this.busy = false;
            }, this.fadeMs);
        },

        next() {
            this.show((this.idx + 1) % this.total);
        },

        prev() {
            this.show((this.idx - 1 + this.total) % this.total);
        },

        go(i) {
            this.show(i);
        },

        startTouch(e) {
            this.touchStartX = e.touches[0]?.clientX ?? null;
            this.stopAuto();
        },

        endTouch(e) {
            if (this.touchStartX == null) {
                this.restartAuto();
                return;
            }
            const endX = e.changedTouches[0]?.clientX ?? this.touchStartX;
            const dx = endX - this.touchStartX;
            if (dx < -48) {
                this.next();
            } else if (dx > 48) {
                this.prev();
            }
            this.touchStartX = null;
            this.restartAuto();
        },
    }));
});

Alpine.start();

const enablePageTransition = () => {
    const transitionEl = document.getElementById('page-transition');
    if (!transitionEl) {
        return;
    }

    const showTransition = () => {
        document.body.classList.add('page-transition-active');
    };

    const hideTransition = () => {
        document.body.classList.remove('page-transition-active');
    };

    window.addEventListener('pageshow', hideTransition);

    document.addEventListener('click', (event) => {
        // Panel form butonları: sayfa geçiş animasyonu karışmasın
        const panelBtn = event.target.closest('form[action*="secder-crm"] button, form[action*="secder-panel"] button');
        if (panelBtn) {
            return;
        }

        const link = event.target.closest('a');
        if (!link) {
            return;
        }

        if (event.defaultPrevented || event.button !== 0 || event.metaKey || event.ctrlKey || event.shiftKey || event.altKey) {
            return;
        }

        const href = link.getAttribute('href');
        if (!href || href.startsWith('#') || href.startsWith('mailto:') || href.startsWith('tel:')) {
            return;
        }

        if (link.target === '_blank' || link.hasAttribute('download')) {
            return;
        }

        const url = new URL(link.href, window.location.origin);
        if (url.origin !== window.location.origin || (url.pathname === window.location.pathname && url.search === window.location.search)) {
            return;
        }

        // Panel girişleri: animasyonsuz ve zorunlu yönlendirme
        if (
            url.pathname.startsWith('/secder-crm')
            || url.pathname.startsWith('/secder-panel')
            || url.pathname.startsWith('/crm')
            || url.pathname.startsWith('/bkd-panel')
            || link.dataset.panelLink
        ) {
            event.preventDefault();
            event.stopPropagation();
            window.location.assign(url.href);
            return;
        }

        event.preventDefault();
        try {
            sessionStorage.setItem('secder-soft-nav', '1');
        } catch (e) {
            // private mode vb. — soft-nav bayrağı yoksa yeni sayfada boot splash görünür
        }
        showTransition();
        window.setTimeout(() => {
            window.location.assign(url.href);
        }, 180);
    });

    window.addEventListener('beforeunload', showTransition);
};

/* Proxy modda (translate.goog) sayfa geçiş animasyonunu devre dışı bırak.
   Proxy, window.location.assign() ile native link navigation'ı engelleyebilir;
   devre dışı bırakınca tarayıcı native <a href> navigasyonu kullanır. */
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', enablePageTransition);
} else {
    enablePageTransition();
}
