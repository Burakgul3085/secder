<?php

namespace Database\Seeders;

use App\Models\BankAccount;
use App\Models\HeroSlide;
use App\Models\MenuItem;
use App\Models\News;
use App\Models\Page;
use App\Models\Project;
use App\Models\Setting;
use Illuminate\Database\Seeder;

class DemoContentSeeder extends Seeder
{
    public function run(): void
    {
        Setting::query()->updateOrCreate(
            ['id' => 1],
            [
                'site_title' => 'Birlikte Kardeslik Dernegi',
                'site_description' => 'Birlikte iyiligi buyutuyor, ihtiyac sahiplerine umut oluyoruz.',
                'phone' => '+90 555 000 00 00',
                'email' => 'iletisim@birliktekardeslik.org',
                'address' => 'Istanbul / Turkiye',
                'facebook_url' => 'https://facebook.com',
                'instagram_url' => 'https://instagram.com',
                'youtube_url' => 'https://youtube.com',
                'x_url' => 'https://x.com',
                'is_active' => true,
            ]
        );

        $menuItems = [
            ['label' => 'Ana Sayfa', 'url' => '/', 'sort_order' => 1],
            ['label' => 'Projeler', 'url' => '/#projeler', 'sort_order' => 2],
            ['label' => 'Haberler', 'url' => '/#haberler', 'sort_order' => 3],
            ['label' => 'Bağış Hesapları', 'url' => '/#bagis-hesaplari', 'sort_order' => 4],
            ['label' => 'Hakkimizda', 'url' => '/sayfa/hakkimizda', 'sort_order' => 5],
        ];

        foreach ($menuItems as $item) {
            MenuItem::query()->updateOrCreate(
                ['label' => $item['label']],
                [
                    ...$item,
                    'open_in_new_tab' => false,
                    'is_active' => true,
                ]
            );
        }

        $heroSlides = [
            [
                'kicker' => 'Birlikte · Dayanışma · İyilik',
                'headline' => 'Birlikte Daha Gucluyuz',
                'accent_text' => 'sahadayız',
                'subtext' => 'Dayanisma ve kardeslikle daha fazla aileye ulasiyoruz.',
                'button_text' => 'Projeleri Incele',
                'button_url' => '/#projeler',
                'sort_order' => 1,
                'show_site_logo' => true,
            ],
            [
                'kicker' => 'Faaliyetlerimiz',
                'headline' => 'Umut Olan Faaliyetler',
                'accent_text' => 'devam ediyor',
                'subtext' => 'Egitim, gida ve sosyal destek calismalariyla sahadayiz.',
                'button_text' => 'Destek Ol',
                'button_url' => '/#bagis-hesaplari',
                'sort_order' => 2,
                'show_site_logo' => true,
            ],
        ];

        foreach ($heroSlides as $slide) {
            HeroSlide::query()->updateOrCreate(
                ['headline' => $slide['headline']],
                [
                    ...$slide,
                    'is_active' => true,
                ]
            );
        }

        $projects = [
            [
                'title' => 'Ramazan Gida Destegi',
                'slug' => 'ramazan-gida-destegi',
                'description' => 'Ihtiyac sahibi ailelere duzenli gida paketi destegi.',
                'status' => 'devam-ediyor',
                'sort_order' => 1,
            ],
            [
                'title' => 'Kislik Giyim Kampanyasi',
                'slug' => 'kislik-giyim-kampanyasi',
                'description' => 'Cocuklar icin mont, bot ve kislik kiyafet yardimi.',
                'status' => 'tamamlandi',
                'sort_order' => 2,
            ],
            [
                'title' => 'Ogrenci Egitim Destegi',
                'slug' => 'ogrenci-egitim-destegi',
                'description' => 'Kirtasiye ve egitim bursu ile ogrencilerin yanindayiz.',
                'status' => 'devam-ediyor',
                'sort_order' => 3,
            ],
        ];

        foreach ($projects as $project) {
            Project::query()->updateOrCreate(
                ['title' => $project['title']],
                [
                    ...$project,
                    'is_active' => true,
                ]
            );
        }

        $newsItems = [
            [
                'title' => 'Nisan Ayinda 120 Aileye Ulasildi',
                'content' => '<p>Nisan ayi icinde yapilan yardim organizasyonumuzla 120 aileye temel ihtiyac destegi saglandi.</p>',
                'published_at' => now()->subDays(5),
            ],
            [
                'title' => 'Gonullu Kayıtlari Basladi',
                'content' => '<p>Yeni donem gonullu basvurulari acildi. Siz de ekibimize katilabilirsiniz.</p>',
                'published_at' => now()->subDays(2),
            ],
        ];

        foreach ($newsItems as $news) {
            News::query()->updateOrCreate(
                ['title' => $news['title']],
                [
                    ...$news,
                    'is_active' => true,
                ]
            );
        }

        $bankAccounts = [
            [
                'bank_name' => 'Ziraat Bankasi',
                'recipient_name' => 'Birlikte Kardeslik Dernegi',
                'iban' => 'TR120001000000000000000001',
                'currency' => 'TRY',
                'sort_order' => 1,
            ],
            [
                'bank_name' => 'VakıfBank',
                'recipient_name' => 'Birlikte Kardeslik Dernegi',
                'iban' => 'TR120001500000000000000002',
                'currency' => 'USD',
                'sort_order' => 2,
            ],
        ];

        foreach ($bankAccounts as $account) {
            BankAccount::query()->updateOrCreate(
                ['iban' => $account['iban']],
                [
                    ...$account,
                    'is_active' => true,
                ]
            );
        }

        $pages = [
            [
                'title' => 'Hakkimizda',
                'slug' => 'hakkimizda',
                'content' => '<p>Birlikte Kardeslik Dernegi; yardimlasma, dayanisma ve sosyal sorumluluk bilinciyle faaliyet gosteren bir sivil toplum olusumudur.</p>',
            ],
            [
                'title' => 'Vizyon Misyon',
                'slug' => 'vizyon-misyon',
                'content' => '<p>Vizyonumuz daha adil bir toplum; misyonumuz ise ihtiyac sahiplerine hizli, seffaf ve surdurulebilir destek sunmaktir.</p>',
            ],
        ];

        foreach ($pages as $page) {
            Page::query()->updateOrCreate(
                ['slug' => $page['slug']],
                [
                    ...$page,
                    'is_active' => true,
                ]
            );
        }
    }
}
