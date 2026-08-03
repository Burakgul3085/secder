<div id="bkd-live-clock" style="
    position: fixed;
    top: 18px;
    right: 18px;
    z-index: 60;
    border: 1px solid rgba(15, 23, 42, 0.1);
    border-radius: 12px;
    padding: 10px 12px;
    background: rgba(255, 255, 255, 0.88);
    box-shadow: 0 10px 25px rgba(15, 23, 42, 0.08);
    backdrop-filter: blur(8px);
    min-width: 170px;
">
    <div style="font-size: 11px; color: #475569; margin-bottom: 3px;">{{ __('app.auth.live_clock') }}</div>
    <div id="bkd-clock-time" style="font-size: 15px; font-weight: 700; color: #0f172a;">--:--:--</div>
    <div id="bkd-clock-date" style="font-size: 11px; color: #334155; margin-top: 2px;">--/--/----</div>
</div>

<script>
    (() => {
        const timeEl = document.getElementById('bkd-clock-time');
        const dateEl = document.getElementById('bkd-clock-date');

        if (!timeEl || !dateEl) return;

        const tick = () => {
            const now = new Date();

            timeEl.textContent = now.toLocaleTimeString('tr-TR', {
                hour: '2-digit',
                minute: '2-digit',
                second: '2-digit',
            });

            dateEl.textContent = now.toLocaleDateString('tr-TR', {
                weekday: 'short',
                day: '2-digit',
                month: '2-digit',
                year: 'numeric',
            });
        };

        tick();
        setInterval(tick, 1000);
    })();
</script>
