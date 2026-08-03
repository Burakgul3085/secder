<style>
    .fi-simple-layout {
        background:
            radial-gradient(circle at 18% 18%, rgba(8, 145, 178, 0.12), transparent 42%),
            radial-gradient(circle at 82% 12%, rgba(71, 85, 105, 0.14), transparent 46%),
            linear-gradient(155deg, #f8fafc 0%, #f1f5f9 48%, #e8eef5 100%);
        min-height: 100vh;
    }

    .fi-simple-layout .fi-simple-main {
        border-radius: 1.25rem;
        box-shadow: 0 22px 50px rgba(15, 23, 42, 0.09);
        border: 1px solid rgba(15, 23, 42, 0.07);
        overflow: hidden;
    }

    /* Üstteki Filament brand logosu kaldırıldı; logo hero bandında gösteriliyor */
    .fi-simple-layout .fi-simple-header .fi-logo,
    .fi-simple-layout header .fi-logo {
        display: none !important;
    }

    .fi-simple-layout .fi-simple-header-heading {
        font-size: 1.75rem !important;
        letter-spacing: -0.02em;
        color: #0f172a !important;
    }

    .fi-simple-layout .fi-simple-header-subheading {
        color: #475569 !important;
    }

    .fi-simple-layout .bkd-login-hero {
        margin-bottom: 1.15rem;
        border-radius: 1rem;
        padding: 1.1rem 1.15rem;
        background: linear-gradient(135deg, #0f766e 0%, #155e75 45%, #334155 100%);
        color: #fff;
        box-shadow: 0 10px 28px rgba(15, 23, 42, 0.14);
    }

    .fi-simple-layout .bkd-login-hero__row {
        display: flex;
        align-items: center;
        gap: 1rem;
    }

    .fi-simple-layout .bkd-login-hero__logo-wrap {
        flex-shrink: 0;
        width: 4.5rem;
        height: 4.5rem;
        border-radius: 1rem;
        background: #fff;
        border: 1px solid rgba(255, 255, 255, 0.55);
        box-shadow: 0 8px 18px rgba(15, 23, 42, 0.18);
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 0.4rem;
    }

    .fi-simple-layout .bkd-login-hero__logo {
        width: 100%;
        height: 100%;
        object-fit: contain;
        border-radius: 0.55rem;
    }

    .fi-simple-layout .bkd-login-hero__eyebrow {
        margin: 0;
        font-size: 0.72rem;
        font-weight: 700;
        letter-spacing: 0.14em;
        text-transform: uppercase;
        opacity: 0.88;
    }

    .fi-simple-layout .bkd-login-hero__title {
        margin: 0.15rem 0 0;
        font-size: 1.15rem;
        font-weight: 700;
        line-height: 1.25;
    }

    .fi-simple-layout .bkd-login-hero__subtitle {
        margin: 0.2rem 0 0;
        font-size: 0.86rem;
        line-height: 1.4;
        opacity: 0.95;
    }

    .fi-simple-layout .bkd-login-back {
        margin-top: 0.75rem;
        margin-bottom: 0.25rem;
        text-align: center;
    }

    .fi-simple-layout .fi-simple-main .bkd-login-back__btn {
        display: block;
        width: 100%;
        box-sizing: border-box;
        margin: 0;
        padding: 0.65rem 1rem;
        text-align: center;
        font-size: 0.875rem;
        font-weight: 600;
        line-height: 1.25;
        /* İkincil eylem: birincil «Giriş yap» butonuyla yarışmaması için dış çizgili stil */
        color: #3f4c6b !important;
        text-decoration: none;
        border: 1px solid #d1dbec;
        border-radius: 0.75rem;
        background: #ffffff;
        box-shadow: 0 1px 2px rgba(15, 23, 42, 0.05);
        cursor: pointer;
        transition: background 0.15s ease, border-color 0.15s ease, box-shadow 0.15s ease, transform 0.1s ease;
        font-family: inherit;
        appearance: none;
        -webkit-appearance: none;
    }

    .fi-simple-layout .bkd-login-back form {
        margin: 0;
    }

    .fi-simple-layout .fi-simple-main .bkd-login-back__btn:hover {
        background: #f5f7fb;
        border-color: #aebfda;
        box-shadow: 0 4px 12px rgba(77, 92, 131, 0.14);
        transform: translateY(-1px);
    }

    .fi-simple-layout .fi-simple-main .bkd-login-back__btn:focus-visible {
        outline: 2px solid #aebfda;
        outline-offset: 2px;
    }

    .fi-simple-layout .bkd-login-forgot__btn {
        display: inline-flex;
        margin-top: 0.6rem;
        font-size: 0.82rem;
        font-weight: 600;
        color: #3f4c6b;
        text-decoration: none;
        transition: color 0.2s ease;
    }

    .fi-simple-layout .bkd-login-forgot__btn:hover {
        color: #0f172a;
        text-decoration: underline;
    }

    .fi-simple-layout .bkd-values {
        margin-top: 1rem;
        border-radius: 0.9rem;
        border: 1px solid rgba(15, 23, 42, 0.06);
        background: rgba(255, 255, 255, 0.9);
        padding: 0.9rem 1rem;
    }

    .fi-simple-layout .bkd-values h4 {
        margin: 0 0 0.4rem;
        color: #0f172a;
        font-size: 0.92rem;
        font-weight: 700;
    }

    .fi-simple-layout .bkd-values ul {
        margin: 0;
        padding-left: 1rem;
        color: #334155;
        font-size: 0.82rem;
        line-height: 1.5;
    }
</style>
<script>
    // Livewire/Filament tıklamayı yutabiliyor; capture ile panel linklerini zorla aç.
    document.addEventListener('click', function (event) {
        const link = event.target.closest('a[data-panel-link], a[href*="secder-crm"], a[href*="secder-panel"]');
        if (!link) {
            return;
        }

        const href = link.getAttribute('href');
        if (!href || href.startsWith('#')) {
            return;
        }

        // Aynı paneldeki login sayfasındaysak gereksiz yeniden yüklemeyi engelleme
        try {
            const next = new URL(href, window.location.origin);
            if (next.pathname === window.location.pathname) {
                return;
            }
        } catch (e) {
            // ignore
        }

        event.preventDefault();
        event.stopPropagation();
        if (typeof event.stopImmediatePropagation === 'function') {
            event.stopImmediatePropagation();
        }
        window.location.assign(href);
    }, true);
</script>
