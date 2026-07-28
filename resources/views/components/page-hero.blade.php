@props([
    'title',
])

<section
    class="relative isolate overflow-hidden py-10 text-white md:py-12"
    style="background-color:#3f4c6b;background-image:linear-gradient(rgba(43,50,69,.82),rgba(51,60,85,.86)),url('data:image/svg+xml,%3Csvg xmlns=%22http://www.w3.org/2000/svg%22 width=%22240%22 height=%22120%22 viewBox=%220 0 240 120%22%3E%3Cg fill=%22none%22 stroke=%22%23ffffff%22 stroke-opacity=%220.07%22 stroke-width=%222%22%3E%3Cpath d=%22M0 24h240M0 60h240M0 96h240%22/%3E%3Cpath d=%22M30 0v120M90 0v120M150 0v120M210 0v120%22/%3E%3C/g%3E%3C/svg%3E');background-size:cover,240px 120px;background-position:center,center;"
>
    <div class="relative mx-auto max-w-7xl px-4 text-center md:px-6">
        <h1 class="text-3xl font-extrabold tracking-tight md:text-4xl">{{ $title }}</h1>
        <div class="mt-3 inline-flex items-center gap-2 text-sm font-semibold text-cyan-100">
            <a href="{{ route('home') }}" class="transition hover:text-white">{{ __('app.nav.home') }}</a>
            <span aria-hidden="true">›</span>
            <span class="text-white">{{ $title }}</span>
        </div>
    </div>
</section>
