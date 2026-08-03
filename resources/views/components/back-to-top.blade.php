<button
    type="button"
    x-data="{
        visible: false,
        sync() {
            this.visible = window.scrollY > 420;
        },
        goTop() {
            window.scrollTo({ top: 0, behavior: 'smooth' });
        }
    }"
    x-init="sync()"
    @scroll.window.passive="sync()"
    @click="goTop()"
    x-show="visible"
    x-transition:enter="transition ease-out duration-300"
    x-transition:enter-start="translate-y-3 opacity-0 scale-90"
    x-transition:enter-end="translate-y-0 opacity-100 scale-100"
    x-transition:leave="transition ease-in duration-200"
    x-transition:leave-start="translate-y-0 opacity-100 scale-100"
    x-transition:leave-end="translate-y-3 opacity-0 scale-90"
    x-cloak
    class="back-to-top fixed bottom-24 right-5 z-[90] flex h-12 w-12 items-center justify-center rounded-full border-2 border-cyan-700 bg-white text-slate-800 shadow-lg transition hover:-translate-y-0.5 hover:border-cyan-600 hover:text-cyan-800 hover:shadow-xl focus:outline-none focus-visible:ring-2 focus-visible:ring-cyan-500 md:bottom-28 md:right-6 md:h-14 md:w-14"
    aria-label="{{ __('app.footer.back_to_top') }}"
    title="{{ __('app.footer.back_to_top') }}"
>
    <svg class="back-to-top__icon h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.25" aria-hidden="true">
        <path stroke-linecap="round" stroke-linejoin="round" d="M5 15l7-7 7 7" />
    </svg>
</button>
