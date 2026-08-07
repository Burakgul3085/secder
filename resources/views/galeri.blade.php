<x-layouts.app>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/glightbox/dist/css/glightbox.min.css">

    @php
        $totalImages = $allSections->sum(fn ($s) => (int) ($s['image_count'] ?? 0));
        $totalVideos = $allSections->sum(fn ($s) => (int) ($s['video_count'] ?? 0));
        $isAllActive = $activeActivitySlug === '' && $activeAlbumSlug === '';
        $pill = fn (bool $active) => $active
            ? 'background:#4d5c83;color:#fff;box-shadow:0 2px 8px rgba(77,92,131,0.32);'
            : 'background:#f1f5f9;color:#475569;border:1px solid #e2e8f0;';
        $pageBtn = fn (bool $active) => $active
            ? 'background:#4d5c83;color:#fff;border:1px solid #4d5c83;'
            : 'background:#fff;color:#334155;border:1px solid #e2e8f0;';
    @endphp

    {{-- ═══════════════════════════════════════════════════════
         HERO
    ═══════════════════════════════════════════════════════ --}}
    <div style="background: linear-gradient(135deg, #2b3245 0%, #3f4c6b 55%, #5f6f9b 100%); position:relative; overflow:hidden;">
        <div style="position:absolute;top:-80px;right:-80px;width:400px;height:400px;border-radius:50%;background:rgba(255,255,255,0.05);pointer-events:none;"></div>
        <div style="position:absolute;bottom:-60px;left:-60px;width:300px;height:300px;border-radius:50%;background:rgba(255,255,255,0.04);pointer-events:none;"></div>

        <div style="max-width:1280px;margin:0 auto;padding:56px 24px 52px;">
            <div style="display:flex;align-items:center;gap:8px;margin-bottom:20px;">
                <a href="{{ route('home') }}" style="color:rgba(255,255,255,0.7);font-size:13px;text-decoration:none;">{{ __('app.gallery.breadcrumb_home') }}</a>
                <svg style="width:14px;height:14px;color:rgba(255,255,255,0.4);" fill="none" viewBox="0 0 20 20" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M7 7l6 6-6 6"/></svg>
                <span style="color:rgba(255,255,255,0.95);font-size:13px;">{{ __('app.gallery.page_title') }}</span>
            </div>

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

            <div style="display:flex;flex-wrap:wrap;gap:10px;margin-top:28px;">
                <span style="display:inline-flex;align-items:center;gap:6px;background:rgba(255,255,255,0.15);color:#fff;font-size:13px;font-weight:600;padding:6px 14px;border-radius:999px;">
                    {{ $totalImages }} {{ __('app.gallery.stat_photo') }}
                </span>
                <span style="display:inline-flex;align-items:center;gap:6px;background:rgba(255,255,255,0.15);color:#fff;font-size:13px;font-weight:600;padding:6px 14px;border-radius:999px;">
                    {{ $totalVideos }} {{ __('app.gallery.stat_video') }}
                </span>
                <span style="display:inline-flex;align-items:center;gap:6px;background:rgba(255,255,255,0.15);color:#fff;font-size:13px;font-weight:600;padding:6px 14px;border-radius:999px;">
                    {{ $allSections->count() }} {{ __('app.gallery.stat_activity') }}
                </span>
            </div>
        </div>
    </div>

    {{-- ═══════════════════════════════════════════════════════
         ANA İÇERİK
    ═══════════════════════════════════════════════════════ --}}
    <div style="background:#f8fafc;min-height:60vh;">
        <div style="max-width:1280px;margin:0 auto;padding:36px 24px 64px;">

            @if($allSections->isNotEmpty())
            <div style="background:#fff;border:1px solid #e2e8f0;border-radius:16px;padding:16px 20px;margin-bottom:32px;box-shadow:0 1px 4px rgba(0,0,0,0.05);">
                <p style="font-size:11px;font-weight:700;color:#94a3b8;letter-spacing:.08em;text-transform:uppercase;margin:0 0 10px 0;">{{ __('app.gallery.filter_label') }}</p>
                <div style="display:flex;flex-wrap:wrap;gap:8px;">
                    <a href="{{ route('gallery') }}"
                       style="display:inline-block;padding:6px 16px;border-radius:999px;font-size:13px;font-weight:600;text-decoration:none;{{ $pill($isAllActive) }}"
                    >{{ __('app.gallery.filter_all') }}</a>
                    @foreach($allSections as $filterSection)
                        @php
                            $filterUrl = $filterSection['kind'] === 'album'
                                ? route('gallery', ['album' => $filterSection['slug']])
                                : route('gallery', ['activity' => $filterSection['slug']]);
                            $isActive = $filterSection['kind'] === 'album'
                                ? $activeAlbumSlug === $filterSection['slug']
                                : $activeActivitySlug === $filterSection['slug'];
                        @endphp
                        <a href="{{ $filterUrl }}"
                           style="display:inline-block;padding:6px 16px;border-radius:999px;font-size:13px;font-weight:600;text-decoration:none;{{ $pill($isActive) }}"
                        >{{ $filterSection['title'] }}</a>
                    @endforeach
                </div>
            </div>
            @endif

            @if($allSections->isEmpty())
                <div style="text-align:center;padding:80px 24px;">
                    <p style="font-size:1.1rem;font-weight:700;color:#334155;margin:0 0 8px;">{{ __('app.gallery.empty_title') }}</p>
                    <p style="font-size:14px;color:#94a3b8;margin:0;">{{ __('app.gallery.empty_desc') }}</p>
                </div>

            {{-- Tümü: sadece başlık kartları --}}
            @elseif($isAllActive)
                <div style="margin-bottom:20px;">
                    <h2 style="font-size:1.15rem;font-weight:800;color:#0f172a;margin:0 0 6px;">{{ __('app.gallery.choose_heading') }}</h2>
                    <p style="font-size:14px;color:#64748b;margin:0;">{{ __('app.gallery.choose_desc') }}</p>
                </div>

                <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(260px,1fr));gap:16px;">
                    @foreach($allSections as $card)
                        @php
                            $cardUrl = $card['kind'] === 'album'
                                ? route('gallery', ['album' => $card['slug']])
                                : route('gallery', ['activity' => $card['slug']]);
                        @endphp
                        <a href="{{ $cardUrl }}"
                           style="display:block;background:#fff;border:1px solid #e2e8f0;border-radius:18px;padding:22px;text-decoration:none;box-shadow:0 2px 10px rgba(0,0,0,0.04);transition:transform .2s,box-shadow .2s;"
                           onmouseover="this.style.transform='translateY(-3px)';this.style.boxShadow='0 10px 24px rgba(0,0,0,0.1)'"
                           onmouseout="this.style.transform='none';this.style.boxShadow='0 2px 10px rgba(0,0,0,0.04)'"
                        >
                            <div style="width:42px;height:42px;border-radius:12px;background:linear-gradient(135deg,#4d5c83,#5f6f9b);display:flex;align-items:center;justify-content:center;margin-bottom:14px;">
                                <svg style="width:22px;height:22px;color:#fff;" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M6.827 6.175A2.31 2.31 0 015.186 7.23c-.38.054-.757.112-1.134.175C2.999 7.58 2.25 8.507 2.25 9.574V18a2.25 2.25 0 002.25 2.25h15A2.25 2.25 0 0021.75 18V9.574c0-1.067-.75-1.994-1.802-2.169a47.865 47.865 0 00-1.134-.175 2.31 2.31 0 01-1.64-1.055l-.822-1.316a2.192 2.192 0 00-1.736-1.039 48.774 48.774 0 00-5.232 0 2.192 2.192 0 00-1.736 1.039l-.821 1.316z" />
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M16.5 12.75a4.5 4.5 0 11-9 0 4.5 4.5 0 019 0z" />
                                </svg>
                            </div>
                            <h3 style="font-size:1.05rem;font-weight:800;color:#0f172a;margin:0 0 10px;line-height:1.3;">{{ $card['title'] }}</h3>
                            <div style="display:flex;flex-wrap:wrap;gap:8px;margin-bottom:14px;">
                                @if(($card['image_count'] ?? 0) > 0)
                                    <span style="font-size:12px;font-weight:600;color:#4d5c83;background:#eef2ff;padding:4px 10px;border-radius:999px;">{{ $card['image_count'] }} {{ __('app.gallery.photo_count') }}</span>
                                @endif
                                @if(($card['video_count'] ?? 0) > 0)
                                    <span style="font-size:12px;font-weight:600;color:#5f6f9b;background:#f1f5f9;padding:4px 10px;border-radius:999px;">{{ $card['video_count'] }} {{ __('app.gallery.video_count') }}</span>
                                @endif
                            </div>
                            <span style="font-size:13px;font-weight:700;color:#4d5c83;">{{ __('app.gallery.open_gallery') }} →</span>
                        </a>
                    @endforeach
                </div>

            {{-- Seçili başlık: sayfalı foto + video --}}
            @elseif($activeSection)
                @php
                    $sectionTitle = $activeSection['title'];
                    $sectionKey = $activeSection['key'];
                @endphp

                <div style="background:#fff;border:1px solid #e2e8f0;border-radius:20px;padding:28px;margin-bottom:28px;box-shadow:0 2px 12px rgba(0,0,0,0.04);">
                    <div style="display:flex;align-items:center;gap:12px;margin-bottom:22px;padding-bottom:18px;border-bottom:1px solid #f1f5f9;">
                        <div style="width:38px;height:38px;border-radius:10px;background:linear-gradient(135deg,#4d5c83,#5f6f9b);display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                            <svg style="width:20px;height:20px;color:#fff;" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M6.827 6.175A2.31 2.31 0 015.186 7.23c-.38.054-.757.112-1.134.175C2.999 7.58 2.25 8.507 2.25 9.574V18a2.25 2.25 0 002.25 2.25h15A2.25 2.25 0 0021.75 18V9.574c0-1.067-.75-1.994-1.802-2.169a47.865 47.865 0 00-1.134-.175 2.31 2.31 0 01-1.64-1.055l-.822-1.316a2.192 2.192 0 00-1.736-1.039 48.774 48.774 0 00-5.232 0 2.192 2.192 0 00-1.736 1.039l-.821 1.316z" />
                                <path stroke-linecap="round" stroke-linejoin="round" d="M16.5 12.75a4.5 4.5 0 11-9 0 4.5 4.5 0 019 0z" />
                            </svg>
                        </div>
                        <div style="flex:1;min-width:0;">
                            <h2 style="font-size:1.15rem;font-weight:800;color:#0f172a;margin:0;line-height:1.3;">{{ $sectionTitle }}</h2>
                            <div style="display:flex;gap:12px;margin-top:4px;">
                                @if(($activeSection['image_count'] ?? 0) > 0)
                                <span style="font-size:12px;color:#4d5c83;font-weight:600;">{{ $activeSection['image_count'] }} {{ __('app.gallery.photo_count') }}</span>
                                @endif
                                @if(($activeSection['video_count'] ?? 0) > 0)
                                <span style="font-size:12px;color:#5f6f9b;font-weight:600;">{{ $activeSection['video_count'] }} {{ __('app.gallery.video_count') }}</span>
                                @endif
                            </div>
                        </div>
                        @if(!empty($activeSection['detail_url']))
                        <a href="{{ $activeSection['detail_url'] }}"
                           style="flex-shrink:0;font-size:12px;font-weight:600;color:#4d5c83;text-decoration:none;padding:6px 12px;border:1px solid #d1dbec;border-radius:999px;">
                            {{ __('app.gallery.activity_page') }}
                        </a>
                        @endif
                    </div>

                    {{-- Fotoğraflar --}}
                    @if($images && $images->total() > 0)
                    <div style="margin-bottom:{{ $videos && $videos->total() > 0 ? '28px' : '0' }};">
                        <div style="display:flex;align-items:center;justify-content:space-between;gap:12px;margin-bottom:12px;flex-wrap:wrap;">
                            <p style="font-size:11px;font-weight:700;color:#94a3b8;letter-spacing:.08em;text-transform:uppercase;margin:0;">{{ __('app.gallery.photos_heading') }}</p>
                            <span style="font-size:12px;color:#64748b;font-weight:600;">{{ __('app.gallery.page_label') }} {{ $images->currentPage() }} / {{ $images->lastPage() }}</span>
                        </div>
                        <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(160px,1fr));gap:10px;">
                            @foreach($images as $index => $item)
                                @php
                                    $path = is_object($item) ? $item->path : $item;
                                    $n = ($images->currentPage() - 1) * $images->perPage() + $index + 1;
                                @endphp
                                <a href="{{ asset('storage/' . $path) }}"
                                   class="glightbox"
                                   data-gallery="photos-{{ $sectionKey }}"
                                   data-title="{{ $sectionTitle }} — {{ $n }}"
                                   style="display:block;position:relative;aspect-ratio:1/1;border-radius:12px;overflow:hidden;background:#e2e8f0;box-shadow:0 1px 4px rgba(0,0,0,0.08);"
                                >
                                    <img src="{{ asset('storage/' . $path) }}"
                                         alt="{{ $sectionTitle }} {{ $n }}"
                                         style="width:100%;height:100%;object-fit:cover;display:block;"
                                         loading="lazy"
                                         decoding="async">
                                </a>
                            @endforeach
                        </div>

                        @if($images->hasPages())
                        <nav aria-label="{{ __('app.gallery.photos_heading') }}" style="display:flex;flex-wrap:wrap;gap:6px;justify-content:center;margin-top:18px;">
                            @foreach($images->getUrlRange(1, $images->lastPage()) as $page => $url)
                                <a href="{{ $url }}#photos"
                                   style="min-width:36px;height:36px;display:inline-flex;align-items:center;justify-content:center;border-radius:10px;font-size:13px;font-weight:700;text-decoration:none;{{ $pageBtn($page === $images->currentPage()) }}"
                                >{{ $page }}</a>
                            @endforeach
                        </nav>
                        @endif
                    </div>
                    @endif

                    {{-- Videolar --}}
                    @if($videos && $videos->total() > 0)
                    <div id="videos">
                        <div style="display:flex;align-items:center;justify-content:space-between;gap:12px;margin-bottom:12px;flex-wrap:wrap;">
                            <p style="font-size:11px;font-weight:700;color:#94a3b8;letter-spacing:.08em;text-transform:uppercase;margin:0;">{{ __('app.gallery.videos_heading') }}</p>
                            <span style="font-size:12px;color:#64748b;font-weight:600;">{{ __('app.gallery.page_label') }} {{ $videos->currentPage() }} / {{ $videos->lastPage() }}</span>
                        </div>
                        <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(280px,1fr));gap:14px;">
                            @foreach($videos as $index => $item)
                                @php
                                    $path = is_object($item) ? $item->path : $item;
                                    $n = ($videos->currentPage() - 1) * $videos->perPage() + $index + 1;
                                    $videoLabel = $sectionTitle . ' — ' . __('app.gallery.video_label') . ' ' . $n;
                                @endphp
                                <div
                                    onclick="openVideoModal('{{ asset('storage/' . $path) }}', {{ \Illuminate\Support\Js::from($videoLabel) }})"
                                    style="border-radius:14px;overflow:hidden;background:#0f172a;box-shadow:0 2px 12px rgba(0,0,0,0.12);cursor:pointer;"
                                >
                                    <div style="position:relative;aspect-ratio:16/9;background:#0f172a;">
                                        <video
                                            style="width:100%;height:100%;object-fit:cover;display:block;pointer-events:none;"
                                            preload="metadata"
                                            muted
                                            playsinline
                                            src="{{ asset('storage/' . $path) }}#t=0.5"
                                        ></video>
                                        <div style="position:absolute;inset:0;display:flex;flex-direction:column;align-items:center;justify-content:center;background:rgba(0,0,0,0.35);">
                                            <div style="width:56px;height:56px;border-radius:50%;background:rgba(255,255,255,0.95);display:flex;align-items:center;justify-content:center;">
                                                <svg style="width:22px;height:22px;color:#4d5c83;margin-left:3px;" fill="currentColor" viewBox="0 0 24 24"><path d="M8 5.14v14l11-7-11-7z"/></svg>
                                            </div>
                                            <span style="color:rgba(255,255,255,0.85);font-size:11px;font-weight:600;margin-top:10px;">{{ __('app.gallery.fullscreen') }}</span>
                                        </div>
                                    </div>
                                    <div style="padding:10px 14px;background:#1e293b;">
                                        <p style="font-size:12px;font-weight:600;color:#cbd5e1;margin:0;">{{ $videoLabel }}</p>
                                    </div>
                                </div>
                            @endforeach
                        </div>

                        @if($videos->hasPages())
                        <nav aria-label="{{ __('app.gallery.videos_heading') }}" style="display:flex;flex-wrap:wrap;gap:6px;justify-content:center;margin-top:18px;">
                            @foreach($videos->getUrlRange(1, $videos->lastPage()) as $page => $url)
                                <a href="{{ $url }}#videos"
                                   style="min-width:36px;height:36px;display:inline-flex;align-items:center;justify-content:center;border-radius:10px;font-size:13px;font-weight:700;text-decoration:none;{{ $pageBtn($page === $videos->currentPage()) }}"
                                >{{ $page }}</a>
                            @endforeach
                        </nav>
                        @endif
                    </div>
                    @endif

                    @if((!$images || $images->total() === 0) && (!$videos || $videos->total() === 0))
                        <div style="text-align:center;padding:40px 16px;">
                            <p style="font-size:1rem;font-weight:700;color:#334155;margin:0 0 8px;">{{ __('app.gallery.empty_title') }}</p>
                            <a href="{{ route('gallery') }}" style="display:inline-block;background:#4d5c83;color:#fff;font-size:14px;font-weight:700;padding:10px 24px;border-radius:999px;text-decoration:none;">{{ __('app.gallery.empty_btn') }}</a>
                        </div>
                    @endif
                </div>
            @else
                <div style="text-align:center;padding:60px 24px;">
                    <p style="font-size:1.1rem;font-weight:700;color:#334155;margin:0 0 8px;">{{ __('app.gallery.empty_title') }}</p>
                    <a href="{{ route('gallery') }}" style="display:inline-block;background:#4d5c83;color:#fff;font-size:14px;font-weight:700;padding:10px 24px;border-radius:999px;text-decoration:none;">{{ __('app.gallery.empty_btn') }}</a>
                </div>
            @endif
        </div>
    </div>

    <div id="videoModal" onclick="closeVideoModal(event)" style="display:none;position:fixed;inset:0;z-index:9999;background:rgba(0,0,0,0.92);align-items:center;justify-content:center;padding:20px;">
        <div style="position:relative;width:100%;max-width:1000px;" onclick="event.stopPropagation()">
            <button type="button" onclick="closeVideoModal()" style="position:absolute;top:-48px;right:0;width:40px;height:40px;border-radius:50%;background:rgba(255,255,255,0.15);border:none;cursor:pointer;color:#fff;font-size:20px;">×</button>
            <p id="videoModalTitle" style="color:rgba(255,255,255,0.7);font-size:13px;font-weight:600;margin:0 0 12px;"></p>
            <div style="border-radius:16px;overflow:hidden;background:#000;">
                <video id="modalVideo" controls style="width:100%;max-height:75vh;display:block;outline:none;"></video>
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
            videoModalTitle.textContent = title || '';
            videoModal.style.display = 'flex';
            document.body.style.overflow = 'hidden';
            modalVideo.play().catch(function () {});
        }

        function closeVideoModal(e) {
            if (e && e.target !== videoModal && e.type === 'click') return;
            modalVideo.pause();
            modalVideo.removeAttribute('src');
            modalVideo.load();
            videoModal.style.display = 'none';
            document.body.style.overflow = '';
        }

        document.addEventListener('keydown', function (e) {
            if (e.key === 'Escape') closeVideoModal();
        });
    </script>
</x-layouts.app>
