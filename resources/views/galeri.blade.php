<x-layouts.app>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/glightbox/dist/css/glightbox.min.css">

    {{-- ═══════════════════════════════════════════════════════
         HERO ALANI
    ═══════════════════════════════════════════════════════ --}}
    <div style="background: linear-gradient(135deg, #2b3245 0%, #3f4c6b 55%, #5f6f9b 100%); position:relative; overflow:hidden;">
        {{-- Dekoratif daireler --}}
        <div style="position:absolute;top:-80px;right:-80px;width:400px;height:400px;border-radius:50%;background:rgba(255,255,255,0.05);pointer-events:none;"></div>
        <div style="position:absolute;bottom:-60px;left:-60px;width:300px;height:300px;border-radius:50%;background:rgba(255,255,255,0.04);pointer-events:none;"></div>

        <div style="max-width:1280px;margin:0 auto;padding:56px 24px 52px;">
            {{-- Breadcrumb --}}
            <div style="display:flex;align-items:center;gap:8px;margin-bottom:20px;">
                <a href="{{ route('home') }}" style="color:rgba(255,255,255,0.7);font-size:13px;text-decoration:none;transition:color .2s;" onmouseover="this.style.color='#fff'" onmouseout="this.style.color='rgba(255,255,255,0.7)'">{{ __('app.gallery.breadcrumb_home') }}</a>
                <svg style="width:14px;height:14px;color:rgba(255,255,255,0.4);" fill="none" viewBox="0 0 20 20" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M7 7l6 6-6 6"/></svg>
                <span style="color:rgba(255,255,255,0.95);font-size:13px;">{{ __('app.gallery.page_title') }}</span>
            </div>

            {{-- Başlık --}}
            <div style="display:flex;align-items:center;gap:16px;margin-bottom:12px;">
                <div style="width:48px;height:48px;border-radius:14px;background:rgba(255,255,255,0.15);display:flex;align-items:center;justify-content:center;">
                    <svg style="width:26px;height:26px;color:#fff;" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6.827 6.175A2.31 2.31 0 015.186 7.23c-.38.054-.757.112-1.134.175C2.999 7.58 2.25 8.507 2.25 9.574V18a2.25 2.25 0 002.25 2.25h15A2.25 2.25 0 0021.75 18V9.574c0-1.067-.75-1.994-1.802-2.169a47.865 47.865 0 00-1.134-.175 2.31 2.31 0 01-1.64-1.055l-.822-1.316a2.192 2.192 0 00-1.736-1.039 48.774 48.774 0 00-5.232 0 2.192 2.192 0 00-1.736 1.039l-.821 1.316z" />
                        <path stroke-linecap="round" stroke-linejoin="round" d="M16.5 12.75a4.5 4.5 0 11-9 0 4.5 4.5 0 019 0zM18.75 10.5h.008v.008h-.008V10.5z" />
                    </svg>
                </div>
                <h1 style="font-size:2.2rem;font-weight:800;color:#fff;margin:0;letter-spacing:-0.5px;line-height:1.15;">{{ __('app.gallery.page_title') }}</h1>
            </div>
            <p style="color:rgba(255,255,255,0.78);font-size:1.05rem;max-width:560px;margin:0;line-height:1.65;">
                {{ __('app.gallery.subtitle') }}
            </p>

            {{-- İstatistik rozetleri --}}
            @php
                $totalImages = $allProjects->sum(fn($p) => count(is_array($p->gallery_images) ? $p->gallery_images : []));
                $totalVideos = $allProjects->sum(fn($p) => count(is_array($p->gallery_videos) ? $p->gallery_videos : []));
            @endphp
            <div style="display:flex;flex-wrap:wrap;gap:10px;margin-top:28px;">
                <span style="display:inline-flex;align-items:center;gap:6px;background:rgba(255,255,255,0.15);color:#fff;font-size:13px;font-weight:600;padding:6px 14px;border-radius:999px;backdrop-filter:blur(4px);">
                    <svg style="width:14px;height:14px;" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 15.75l5.159-5.159a2.25 2.25 0 013.182 0l5.159 5.159m-1.5-1.5l1.409-1.409a2.25 2.25 0 013.182 0l2.909 2.909m-18 3.75h16.5a1.5 1.5 0 001.5-1.5V6a1.5 1.5 0 00-1.5-1.5H3.75A1.5 1.5 0 002.25 6v12a1.5 1.5 0 001.5 1.5zm10.5-11.25h.008v.008h-.008V8.25zm.375 0a.375.375 0 11-.75 0 .375.375 0 01.75 0z" /></svg>
                    {{ $totalImages }} {{ __('app.gallery.stat_photo') }}
                </span>
                <span style="display:inline-flex;align-items:center;gap:6px;background:rgba(255,255,255,0.15);color:#fff;font-size:13px;font-weight:600;padding:6px 14px;border-radius:999px;backdrop-filter:blur(4px);">
                    <svg style="width:14px;height:14px;" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 10.5l4.72-4.72a.75.75 0 011.28.53v11.38a.75.75 0 01-1.28.53l-4.72-4.72M4.5 18.75h9a2.25 2.25 0 002.25-2.25v-9a2.25 2.25 0 00-2.25-2.25h-9A2.25 2.25 0 002.25 7.5v9a2.25 2.25 0 002.25 2.25z" /></svg>
                    {{ $totalVideos }} {{ __('app.gallery.stat_video') }}
                </span>
                <span style="display:inline-flex;align-items:center;gap:6px;background:rgba(255,255,255,0.15);color:#fff;font-size:13px;font-weight:600;padding:6px 14px;border-radius:999px;backdrop-filter:blur(4px);">
                    <svg style="width:14px;height:14px;" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6A2.25 2.25 0 016 3.75h2.25A2.25 2.25 0 0110.5 6v2.25a2.25 2.25 0 01-2.25 2.25H6a2.25 2.25 0 01-2.25-2.25V6zM3.75 15.75A2.25 2.25 0 016 13.5h2.25a2.25 2.25 0 012.25 2.25V18a2.25 2.25 0 01-2.25 2.25H6A2.25 2.25 0 013.75 18v-2.25zM13.5 6a2.25 2.25 0 012.25-2.25H18A2.25 2.25 0 0120.25 6v2.25A2.25 2.25 0 0118 10.5h-2.25A2.25 2.25 0 0113.5 8.25V6zM13.5 15.75a2.25 2.25 0 012.25-2.25H18a2.25 2.25 0 012.25 2.25V18A2.25 2.25 0 0118 20.25h-2.25A2.25 2.25 0 0113.5 18v-2.25z" /></svg>
                    {{ $allProjects->count() }} {{ __('app.gallery.stat_activity') }}
                </span>
            </div>
        </div>
    </div>

    {{-- ═══════════════════════════════════════════════════════
         ANA İÇERİK
    ═══════════════════════════════════════════════════════ --}}
    <div style="background:#f8fafc;min-height:60vh;">
        <div style="max-width:1280px;margin:0 auto;padding:36px 24px 64px;">

            {{-- Filtre şeridi --}}
            @if($allProjects->isNotEmpty())
            <div style="background:#fff;border:1px solid #e2e8f0;border-radius:16px;padding:16px 20px;margin-bottom:32px;box-shadow:0 1px 4px rgba(0,0,0,0.05);">
                <p style="font-size:11px;font-weight:700;color:#94a3b8;letter-spacing:.08em;text-transform:uppercase;margin:0 0 10px 0;">{{ __('app.gallery.filter_label') }}</p>
                <div style="display:flex;flex-wrap:wrap;gap:8px;">
                    <a href="{{ route('gallery') }}"
                       style="display:inline-block;padding:6px 16px;border-radius:999px;font-size:13px;font-weight:600;text-decoration:none;transition:all .2s;
                              {{ $activeSlug === '' ? 'background:#4d5c83;color:#fff;box-shadow:0 2px 8px rgba(77,92,131,0.32);' : 'background:#f1f5f9;color:#475569;border:1px solid #e2e8f0;' }}"
                    >{{ __('app.gallery.filter_all') }}</a>
                    @foreach($allProjects as $proj)
                    <a href="{{ route('gallery', ['activity' => $proj->slug]) }}"
                       style="display:inline-block;padding:6px 16px;border-radius:999px;font-size:13px;font-weight:600;text-decoration:none;transition:all .2s;
                              {{ $activeSlug === $proj->slug ? 'background:#4d5c83;color:#fff;box-shadow:0 2px 8px rgba(77,92,131,0.32);' : 'background:#f1f5f9;color:#475569;border:1px solid #e2e8f0;' }}"
                    >{{ $proj->getLocalized('title', $proj->title) }}</a>
                    @endforeach
                </div>
            </div>
            @endif

            {{-- Medya yok --}}
            @if($projects->isEmpty())
            <div style="text-align:center;padding:80px 24px;">
                <div style="width:72px;height:72px;border-radius:50%;background:#e8edf6;display:flex;align-items:center;justify-content:center;margin:0 auto 20px;">
                    <svg style="width:34px;height:34px;color:#4d5c83;" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6.827 6.175A2.31 2.31 0 015.186 7.23c-.38.054-.757.112-1.134.175C2.999 7.58 2.25 8.507 2.25 9.574V18a2.25 2.25 0 002.25 2.25h15A2.25 2.25 0 0021.75 18V9.574c0-1.067-.75-1.994-1.802-2.169a47.865 47.865 0 00-1.134-.175 2.31 2.31 0 01-1.64-1.055l-.822-1.316a2.192 2.192 0 00-1.736-1.039 48.774 48.774 0 00-5.232 0 2.192 2.192 0 00-1.736 1.039l-.821 1.316z" />
                        <path stroke-linecap="round" stroke-linejoin="round" d="M16.5 12.75a4.5 4.5 0 11-9 0 4.5 4.5 0 019 0zM18.75 10.5h.008v.008h-.008V10.5z" />
                    </svg>
                </div>
                <p style="font-size:1.1rem;font-weight:700;color:#334155;margin:0 0 8px;">{{ __('app.gallery.empty_title') }}</p>
                <p style="font-size:14px;color:#94a3b8;margin:0 0 20px;">{{ __('app.gallery.empty_desc') }}</p>
                <a href="{{ route('gallery') }}" style="display:inline-block;background:#4d5c83;color:#fff;font-size:14px;font-weight:700;padding:10px 24px;border-radius:999px;text-decoration:none;">{{ __('app.gallery.empty_btn') }}</a>
            </div>

            @else
                @foreach($projects as $project)
                    @php
                        $images = is_array($project->gallery_images) ? array_values(array_filter($project->gallery_images)) : [];
                        $videos = is_array($project->gallery_videos) ? array_values(array_filter($project->gallery_videos)) : [];
                        $projectTitle = $project->getLocalized('title', $project->title);
                    @endphp
                    @if(count($images) > 0 || count($videos) > 0)

                    {{-- Faaliyet bölümü --}}
                    <div style="background:#fff;border:1px solid #e2e8f0;border-radius:20px;padding:28px;margin-bottom:28px;box-shadow:0 2px 12px rgba(0,0,0,0.04);">

                        {{-- Bölüm başlığı --}}
                        <div style="display:flex;align-items:center;gap:12px;margin-bottom:22px;padding-bottom:18px;border-bottom:1px solid #f1f5f9;">
                            <div style="width:38px;height:38px;border-radius:10px;background:linear-gradient(135deg,#4d5c83,#5f6f9b);display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                                <svg style="width:20px;height:20px;color:#fff;" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M6.827 6.175A2.31 2.31 0 015.186 7.23c-.38.054-.757.112-1.134.175C2.999 7.58 2.25 8.507 2.25 9.574V18a2.25 2.25 0 002.25 2.25h15A2.25 2.25 0 0021.75 18V9.574c0-1.067-.75-1.994-1.802-2.169a47.865 47.865 0 00-1.134-.175 2.31 2.31 0 01-1.64-1.055l-.822-1.316a2.192 2.192 0 00-1.736-1.039 48.774 48.774 0 00-5.232 0 2.192 2.192 0 00-1.736 1.039l-.821 1.316z" />
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M16.5 12.75a4.5 4.5 0 11-9 0 4.5 4.5 0 019 0zM18.75 10.5h.008v.008h-.008V10.5z" />
                                </svg>
                            </div>
                            <div style="flex:1;min-width:0;">
                                <h2 style="font-size:1.15rem;font-weight:800;color:#0f172a;margin:0;line-height:1.3;">{{ $projectTitle }}</h2>
                                <div style="display:flex;gap:12px;margin-top:4px;">
                                    @if(count($images) > 0)
                                    <span style="font-size:12px;color:#4d5c83;font-weight:600;display:inline-flex;align-items:center;gap:4px;">
                                        <svg style="width:12px;height:12px;" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 15.75l5.159-5.159a2.25 2.25 0 013.182 0l5.159 5.159m-1.5-1.5l1.409-1.409a2.25 2.25 0 013.182 0l2.909 2.909m-18 3.75h16.5a1.5 1.5 0 001.5-1.5V6a1.5 1.5 0 00-1.5-1.5H3.75A1.5 1.5 0 002.25 6v12a1.5 1.5 0 001.5 1.5zm10.5-11.25h.008v.008h-.008V8.25zm.375 0a.375.375 0 11-.75 0 .375.375 0 01.75 0z" /></svg>
                                        {{ count($images) }} {{ __('app.gallery.photo_count') }}
                                    </span>
                                    @endif
                                    @if(count($videos) > 0)
                                    <span style="font-size:12px;color:#5f6f9b;font-weight:600;display:inline-flex;align-items:center;gap:4px;">
                                        <svg style="width:12px;height:12px;" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 10.5l4.72-4.72a.75.75 0 011.28.53v11.38a.75.75 0 01-1.28.53l-4.72-4.72M4.5 18.75h9a2.25 2.25 0 002.25-2.25v-9a2.25 2.25 0 00-2.25-2.25h-9A2.25 2.25 0 002.25 7.5v9a2.25 2.25 0 002.25 2.25z" /></svg>
                                        {{ count($videos) }} {{ __('app.gallery.video_count') }}
                                    </span>
                                    @endif
                                </div>
                            </div>
                            <a href="{{ route('activities.show', $project->slug) }}"
                               style="flex-shrink:0;font-size:12px;font-weight:600;color:#4d5c83;text-decoration:none;display:inline-flex;align-items:center;gap:4px;padding:6px 12px;border:1px solid #d1dbec;border-radius:999px;transition:all .2s;"
                               onmouseover="this.style.background='#f5f7fb'" onmouseout="this.style.background='transparent'"
                            >
                                {{ __('app.gallery.activity_page') }}
                                <svg style="width:12px;height:12px;" fill="none" viewBox="0 0 20 20" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M7 7l6 6-6 6"/></svg>
                            </a>
                        </div>

                        {{-- Fotoğraf grid --}}
                        @if(count($images) > 0)
                        <div style="margin-bottom:{{ count($videos) > 0 ? '24px' : '0' }};">
                            <p style="font-size:11px;font-weight:700;color:#94a3b8;letter-spacing:.08em;text-transform:uppercase;margin:0 0 12px;">{{ __('app.gallery.photos_heading') }}</p>
                            <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(160px,1fr));gap:10px;">
                                @foreach($images as $index => $image)
                                <a href="{{ asset('storage/' . $image) }}"
                                   class="glightbox"
                                   data-gallery="photos-{{ $project->slug }}"
                                   data-title="{{ $projectTitle }} — {{ $index + 1 }}"
                                   style="display:block;position:relative;aspect-ratio:1/1;border-radius:12px;overflow:hidden;background:#e2e8f0;box-shadow:0 1px 4px rgba(0,0,0,0.08);transition:transform .2s,box-shadow .2s;"
                                   onmouseover="this.style.transform='scale(1.03)';this.style.boxShadow='0 8px 20px rgba(0,0,0,0.15)'"
                                   onmouseout="this.style.transform='scale(1)';this.style.boxShadow='0 1px 4px rgba(0,0,0,0.08)'"
                                >
                                    <img src="{{ asset('storage/' . $image) }}"
                                         alt="{{ $projectTitle }} {{ __('app.gallery.photo_count') }} {{ $index + 1 }}"
                                         style="width:100%;height:100%;object-fit:cover;display:block;"
                                         loading="lazy">
                                    <div style="position:absolute;inset:0;background:linear-gradient(to top,rgba(0,0,0,0.4) 0%,transparent 50%);opacity:0;transition:opacity .2s;"
                                         onmouseover="this.style.opacity='1'" onmouseout="this.style.opacity='0'">
                                        <div style="position:absolute;bottom:8px;left:0;right:0;text-align:center;">
                                            <svg style="width:20px;height:20px;color:#fff;display:inline-block;" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 15.803 7.5 7.5 0 0015.803 15.803zM10.5 7.5v6m3-3h-6"/></svg>
                                        </div>
                                    </div>
                                </a>
                                @endforeach
                            </div>
                        </div>
                        @endif

                        {{-- Video grid --}}
                        @if(count($videos) > 0)
                        <div>
                            <p style="font-size:11px;font-weight:700;color:#94a3b8;letter-spacing:.08em;text-transform:uppercase;margin:0 0 12px;">{{ __('app.gallery.videos_heading') }}</p>
                            <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(280px,1fr));gap:14px;">
                                @foreach($videos as $index => $video)
                                @php $videoLabel = $projectTitle . ' — ' . __('app.gallery.video_label') . ' ' . ($index + 1); @endphp
                                <div
                                    onclick="openVideoModal('{{ asset('storage/' . $video) }}', '{{ addslashes($videoLabel) }}')"
                                    style="border-radius:14px;overflow:hidden;background:#0f172a;box-shadow:0 2px 12px rgba(0,0,0,0.12);cursor:pointer;transition:transform .2s,box-shadow .2s;"
                                    onmouseover="this.style.transform='translateY(-3px)';this.style.boxShadow='0 12px 28px rgba(0,0,0,0.22)'"
                                    onmouseout="this.style.transform='translateY(0)';this.style.boxShadow='0 2px 12px rgba(0,0,0,0.12)'"
                                >
                                    <div style="position:relative;aspect-ratio:16/9;background:#0f172a;">
                                        <video
                                            style="width:100%;height:100%;object-fit:cover;display:block;pointer-events:none;"
                                            preload="metadata"
                                            src="{{ asset('storage/' . $video) }}#t=0.5"
                                        ></video>
                                        {{-- Oynat butonu overlay --}}
                                        <div style="position:absolute;inset:0;display:flex;flex-direction:column;align-items:center;justify-content:center;background:rgba(0,0,0,0.35);transition:background .2s;">
                                            <div style="width:56px;height:56px;border-radius:50%;background:rgba(255,255,255,0.95);display:flex;align-items:center;justify-content:center;box-shadow:0 4px 16px rgba(0,0,0,0.3);transition:transform .2s;">
                                                <svg style="width:22px;height:22px;color:#4d5c83;margin-left:3px;" fill="currentColor" viewBox="0 0 24 24">
                                                    <path d="M8 5.14v14l11-7-11-7z"/>
                                                </svg>
                                            </div>
                                            <span style="color:rgba(255,255,255,0.85);font-size:11px;font-weight:600;margin-top:10px;letter-spacing:.04em;">{{ __('app.gallery.fullscreen') }}</span>
                                        </div>
                                    </div>
                                    <div style="padding:10px 14px;background:#1e293b;">
                                        <p style="font-size:12px;font-weight:600;color:#cbd5e1;margin:0;display:flex;align-items:center;gap:6px;">
                                            <svg style="width:12px;height:12px;color:#8397bd;flex-shrink:0;" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 10.5l4.72-4.72a.75.75 0 011.28.53v11.38a.75.75 0 01-1.28.53l-4.72-4.72M4.5 18.75h9a2.25 2.25 0 002.25-2.25v-9a2.25 2.25 0 00-2.25-2.25h-9A2.25 2.25 0 002.25 7.5v9a2.25 2.25 0 002.25 2.25z" /></svg>
                                            {{ $videoLabel }}
                                        </p>
                                    </div>
                                </div>
                                @endforeach
                            </div>
                        </div>
                        @endif

                    </div>{{-- /faaliyet bölümü --}}
                    @endif
                @endforeach
            @endif

        </div>
    </div>

    {{-- Video Modal --}}
    <div id="videoModal" onclick="closeVideoModal(event)" style="display:none;position:fixed;inset:0;z-index:9999;background:rgba(0,0,0,0.92);backdrop-filter:blur(6px);align-items:center;justify-content:center;padding:20px;">
        <div style="position:relative;width:100%;max-width:1000px;" onclick="event.stopPropagation()">
            {{-- Kapat butonu --}}
            <button onclick="closeVideoModal()" style="position:absolute;top:-48px;right:0;width:40px;height:40px;border-radius:50%;background:rgba(255,255,255,0.15);border:none;cursor:pointer;display:flex;align-items:center;justify-content:center;transition:background .2s;z-index:1;"
                onmouseover="this.style.background='rgba(255,255,255,0.25)'" onmouseout="this.style.background='rgba(255,255,255,0.15)'">
                <svg style="width:20px;height:20px;color:#fff;" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
            {{-- Video başlığı --}}
            <p id="videoModalTitle" style="color:rgba(255,255,255,0.7);font-size:13px;font-weight:600;margin:0 0 12px;"></p>
            {{-- Video --}}
            <div style="border-radius:16px;overflow:hidden;background:#000;box-shadow:0 24px 60px rgba(0,0,0,0.6);">
                <video id="modalVideo" controls autoplay style="width:100%;max-height:75vh;display:block;outline:none;">
                    {{ __('app.gallery.video_no_support') }}
                </video>
            </div>
            <p style="color:rgba(255,255,255,0.35);font-size:11px;text-align:center;margin:12px 0 0;">{{ __('app.gallery.modal_close_hint') }}</p>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/glightbox/dist/js/glightbox.min.js"></script>
    <script>
        GLightbox({ selector: '.glightbox', touchNavigation: true, loop: true });

        const videoModal = document.getElementById('videoModal');
        const modalVideo = document.getElementById('modalVideo');
        const videoModalTitle = document.getElementById('videoModalTitle');

        function openVideoModal(src, title) {
            modalVideo.src = src;
            videoModalTitle.textContent = title;
            videoModal.style.display = 'flex';
            document.body.style.overflow = 'hidden';
        }

        function closeVideoModal(e) {
            if (e && e.target !== videoModal) return;
            modalVideo.pause();
            modalVideo.src = '';
            videoModal.style.display = 'none';
            document.body.style.overflow = '';
        }

        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                modalVideo.pause();
                modalVideo.src = '';
                videoModal.style.display = 'none';
                document.body.style.overflow = '';
            }
        });
    </script>
</x-layouts.app>
