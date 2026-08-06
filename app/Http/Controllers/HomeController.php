<?php

namespace App\Http\Controllers;

use App\Models\BankAccount;
use App\Models\ContactMessage;
use App\Models\ActivitySectionSetting;
use App\Models\HeroSlide;
use App\Models\News;
use App\Models\Page;
use App\Models\Project;
use App\Models\Setting;
use App\Models\Testimonial;
use App\Models\VolunteerApplication;
use App\Support\DonationQrService;
use App\Support\Mailer;
use App\Support\MediaGallerySync;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;
use PHPMailer\PHPMailer\Exception;

class HomeController extends Controller
{
    /**
     * @param  Collection<int, HeroSlide>  $heroSlides
     * @return array<int, array<string, mixed>>
     */
    private function heroSlidesPayload(Collection $heroSlides): array
    {
        $defaultImage = asset('images/default-logo.svg');

        return $heroSlides->map(function (HeroSlide $slide) use ($defaultImage) {
            // Cihaz bandlarına oturtulmuş varyantlar; yoksa yüklenen orijinale düşer.
            $set = $slide->imageSet();

            return [
                'image' => $set['image'] ?? $defaultImage,
                'image_mobile' => $set['mobile'] ?? $set['image'] ?? $defaultImage,
                'image_tablet' => $set['tablet'] ?? $set['image'] ?? $defaultImage,
                'desktop_srcset' => $set['desktop_srcset'] ?? '',
            ];
        })->values()->all();
    }

    public function index(): View
    {
        $heroSlides = HeroSlide::query()->active()->orderBy('sort_order')->get();
        $activities = Project::query()->active()->orderBy('sort_order')->get();

        return view('home', [
            'heroSlides' => $heroSlides,
            'heroSlidesPayload' => $this->heroSlidesPayload($heroSlides),
            'projects' => $activities,
            'activitySection' => ActivitySectionSetting::current(),
            'newsItems' => News::query()->active()->latest('published_at')->get(),
            'bankAccounts' => BankAccount::query()->active()->orderBy('sort_order')->get(),
            'testimonials' => Testimonial::query()
                ->approved()
                ->orderByDesc('approved_at')
                ->orderByDesc('created_at')
                ->get(),
            'testimonialStats' => Testimonial::approvedStats(),
        ]);
    }

    public function activities(Request $request): View
    {
        $query = Project::query()->active();

        if ($request->filled('q')) {
            $query->where('title', 'like', '%' . $request->q . '%');
        }

        if ($request->filled('status') && in_array($request->status, ['devam_ediyor', 'tamamlandi'])) {
            if ($request->status === 'devam_ediyor') {
                $query->where('status', '!=', 'tamamlandi');
            } else {
                $query->where('status', 'tamamlandi');
            }
        }

        $sort = $request->input('sort', 'default');
        match ($sort) {
            'amount_asc'  => $query->orderByRaw('CAST(donation_amount AS DECIMAL(15,2)) ASC'),
            'amount_desc' => $query->orderByRaw('CAST(donation_amount AS DECIMAL(15,2)) DESC'),
            default       => $query->orderBy('sort_order'),
        };

        return view('activities', [
            'activities' => $query->get(),
            'filters'    => [
                'q'      => $request->input('q', ''),
                'status' => $request->input('status', ''),
                'sort'   => $sort,
            ],
        ]);
    }

    public function news(Request $request): View
    {
        $query = News::query()->active();

        if ($request->filled('q')) {
            $query->where(function ($q) use ($request) {
                $q->where('title', 'like', '%' . $request->q . '%')
                  ->orWhere('summary', 'like', '%' . $request->q . '%');
            });
        }

        $sort = $request->input('sort', 'newest');
        match ($sort) {
            'oldest' => $query->oldest('published_at'),
            default  => $query->latest('published_at'),
        };

        return view('news', [
            'newsItems' => $query->paginate(12)->withQueryString(),
            'filters'   => [
                'q'    => $request->input('q', ''),
                'sort' => $sort,
            ],
        ]);
    }

    public function newsShow(News $news): View
    {
        abort_unless($news->is_active, 404);

        return view('news-show', [
            'news' => $news,
            'relatedNews' => News::query()
                ->active()
                ->where('id', '!=', $news->id)
                ->latest('published_at')
                ->take(4)
                ->get(),
        ]);
    }

    public function gallery(Request $request): View
    {
        $sync = app(MediaGallerySync::class);
        $activitySlug = $request->input('activity');
        $albumSlug = $request->input('album');

        $allSections = $sync->gallerySections();
        $sections = $sync->gallerySections(
            activitySlug: filled($activitySlug) ? (string) $activitySlug : null,
            albumSlug: filled($albumSlug) ? (string) $albumSlug : null,
        );

        return view('galeri', [
            'sections' => $sections,
            'allSections' => $allSections,
            'activeActivitySlug' => (string) ($activitySlug ?? ''),
            'activeAlbumSlug' => (string) ($albumSlug ?? ''),
        ]);
    }

    public function activityShow(string $slug): View
    {
        $activity = Project::query()->active()->where('slug', $slug)->firstOrFail();
        $qrRelativePath = app(DonationQrService::class)->generate();

        return view('activity-show', [
            'activity' => $activity,
            'bankAccounts' => BankAccount::query()->active()->orderBy('sort_order')->get(),
            'donationQrPath' => $qrRelativePath,
            'relatedActivities' => Project::query()
                ->active()
                ->where('id', '!=', $activity->id)
                ->orderBy('sort_order')
                ->take(4)
                ->get(),
        ]);
    }

    public function page(string $slug): View
    {
        $normalizedSlug = trim(strtolower(str_replace(' ', '-', $slug)));
        $slugAliases = [$normalizedSlug];

        if ($normalizedSlug === 'resmi-belgiler') {
            $slugAliases[] = 'resmi-bilgiler';
        }

        if ($normalizedSlug === 'resmi-bilgiler') {
            $slugAliases[] = 'resmi-belgiler';
        }

        $page = Page::query()->active()->whereIn('slug', array_unique($slugAliases))->firstOrFail();

        $viewData = ['page' => $page];

        if ($page->slug === 'resmi-bilgiler' || $page->slug === 'resmi-belgiler') {
            $viewData['bankAccounts'] = BankAccount::query()->active()->orderBy('sort_order')->get();
            $viewData['siteSettings'] = Setting::current();
        }

        return view('page', $viewData);
    }

    public function contact(): View
    {
        return view('contact');
    }

    public function volunteer(): View
    {
        return view('volunteer', [
            'volunteerPreferenceOptions' => $this->localizedVolunteerPreferenceOptions(),
        ]);
    }

    public function donations(): View
    {
        $qrRelativePath = app(DonationQrService::class)->generate();

        $presetAmount = request()->query('tutar');
        $presetNote = request()->query('aciklama');

        return view('donations', [
            'bankAccounts' => BankAccount::query()->active()->orderBy('sort_order')->get(),
            'donationQrPath' => $qrRelativePath,
            'presetAmount' => is_numeric($presetAmount) ? (float) $presetAmount : null,
            'presetNote' => filled($presetNote) ? (string) $presetNote : null,
        ]);
    }

    public function submitContact(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'first_name' => ['required', 'string', 'max:120'],
            'last_name' => ['required', 'string', 'max:120'],
            'email' => ['required', 'email', 'max:190'],
            'message' => ['required', 'string', 'min:10', 'max:5000'],
        ], [
            'first_name.required' => 'Lütfen adınızı girin.',
            'last_name.required' => 'Lütfen soyadınızı girin.',
            'email.required' => 'Lütfen e-posta adresinizi girin.',
            'email.email' => 'Geçerli bir e-posta adresi girin.',
            'message.required' => 'Lütfen mesajınızı yazın.',
            'message.min' => 'Mesaj en az 10 karakter olmalıdır.',
        ]);

        $message = ContactMessage::query()->create($validated);

        $settings = Setting::current();
        $notifyEmail = $settings->mailer_notification_email
            ?: $settings->email
            ?: $settings->mailer_from_address
            ?: (string) env('PHPMAILER_FROM_ADDRESS');

        if ($notifyEmail) {
            try {
                $subject = 'Yeni İletişim Formu Mesajı';
                $html = view('emails.contact-notification', [
                    'contactMessage' => $message,
                ])->render();

                Mailer::send(
                    $notifyEmail,
                    $settings->site_title ?? 'Yönetim',
                    $subject,
                    $html,
                    $message->email,
                );
            } catch (Exception $e) {
                Log::error('İletişim bildirimi e-postası gönderilemedi.', [
                    'error' => $e->getMessage(),
                    'contact_message_id' => $message->id,
                ]);
            }
        }

        try {
            $ackSubject = 'Mesajınız Birlikte Kardeşlik Derneği\'ne Ulaştı';
            $ackHtml = view('emails.contact-acknowledgement', [
                'contactMessage' => $message,
                'siteTitle' => $settings->site_title ?? 'Birlikte Kardeşlik Derneği',
            ])->render();

            Mailer::send(
                $message->email,
                trim($message->first_name.' '.$message->last_name),
                $ackSubject,
                $ackHtml,
                $notifyEmail ?: null,
            );
        } catch (Exception $e) {
            Log::error('İletişim otomatik bilgilendirme e-postası gönderilemedi.', [
                'error' => $e->getMessage(),
                'contact_message_id' => $message->id,
            ]);
        }

        return back()->with('success', __('app.messages.contact_success'));
    }

    public function submitVolunteer(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'first_name' => ['required', 'string', 'max:120'],
            'last_name' => ['required', 'string', 'max:120'],
            'email' => ['required', 'email', 'max:190'],
            'phone' => ['required', 'string', 'max:40'],
            'preference' => ['required', 'string', 'max:120'],
            'about' => ['required', 'string', 'min:10', 'max:5000'],
        ], [
            'first_name.required' => 'Lütfen adınızı girin.',
            'last_name.required' => 'Lütfen soyadınızı girin.',
            'email.required' => 'Lütfen e-posta adresinizi girin.',
            'phone.required' => 'Lütfen telefon numaranızı girin.',
            'preference.required' => 'Lütfen gönüllülük tercihinizi seçin.',
            'about.required' => 'Lütfen kendinizden bahsedin.',
        ]);

        $settings = Setting::current();
        $allowedPreferences = $this->localizedVolunteerPreferenceOptions();
        if (! in_array($validated['preference'], $allowedPreferences, true)) {
            return back()
                ->withInput()
                ->withErrors(['preference' => 'Seçilen gönüllülük tercihi geçerli değil.']);
        }

        $application = VolunteerApplication::query()->create($validated);
        $notifyEmail = $settings->mailer_notification_email
            ?: $settings->email
            ?: $settings->mailer_from_address
            ?: (string) env('PHPMAILER_FROM_ADDRESS');

        if ($notifyEmail) {
            try {
                $subject = 'Yeni Gönüllülük Başvurusu';
                $html = view('emails.volunteer-notification', [
                    'application' => $application,
                ])->render();

                Mailer::send(
                    $notifyEmail,
                    $settings->site_title ?? 'Yönetim',
                    $subject,
                    $html,
                    $application->email,
                );
            } catch (Exception $e) {
                Log::error('Gönüllülük bildirimi e-postası gönderilemedi.', [
                    'error' => $e->getMessage(),
                    'volunteer_application_id' => $application->id,
                ]);
            }
        }

        try {
            $ackHtml = view('emails.volunteer-acknowledgement', [
                'application' => $application,
                'siteTitle' => $settings->site_title ?? 'Birlikte Kardeşlik Derneği',
            ])->render();

            Mailer::send(
                $application->email,
                trim($application->first_name.' '.$application->last_name),
                'Gönüllülük Başvurunuz Alındı',
                $ackHtml,
                $notifyEmail ?: null,
            );
        } catch (Exception $e) {
            Log::error('Gönüllülük otomatik bilgilendirme e-postası gönderilemedi.', [
                'error' => $e->getMessage(),
                'volunteer_application_id' => $application->id,
            ]);
        }

        return back()->with('success', __('app.messages.volunteer_success'));
    }

    /**
     * @return array<int, string>
     */
    private function localizedVolunteerPreferenceOptions(): array
    {
        $options = __('app.volunteer.preference_options');

        if (is_array($options)) {
            return collect($options)
                ->map(static fn ($item): string => trim((string) $item))
                ->filter()
                ->values()
                ->all();
        }

        return [];
    }
}
